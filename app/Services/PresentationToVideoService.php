<?php

namespace App\Services;

use App\Models\PresentationVideo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresentationToVideoService
{
    private int $slideDuration = 15;
    private string $resolution = '1920x1080';

    public function process(PresentationVideo $presentationVideo): void
    {
        $presentationVideo->update(['status' => 'processing']);

        try {
            $pptxPath = Storage::path($presentationVideo->stored_path);
            $workDir = Storage::path('presentations/' . $presentationVideo->id);

            if (!is_dir($workDir)) {
                mkdir($workDir, 0755, true);
            }

            $imagesDir = $workDir . '/slides';
            if (!is_dir($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }

            $slideCount = $this->exportSlidesAsImages($pptxPath, $imagesDir);

            if ($slideCount === 0) {
                throw new \RuntimeException('No se pudieron extraer las diapositivas del archivo.');
            }

            $presentationVideo->update(['slide_count' => $slideCount]);

            $videoPath = 'presentations/' . $presentationVideo->id . '/output.mp4';
            $outputFilePath = Storage::path($videoPath);

            $videoGenerated = $this->createVideoFromImages($imagesDir, $outputFilePath, $slideCount);

            if ($videoGenerated) {
                $presentationVideo->update([
                    'video_path' => $videoPath,
                    'status' => 'completed',
                ]);
            } else {
                $this->createFallbackZip($workDir, $imagesDir, $presentationVideo);
            }

            $this->cleanupTemp($imagesDir);

        } catch (\Throwable $e) {
            Log::error('Presentation to video failed: ' . $e->getMessage(), [
                'presentation_id' => $presentationVideo->id,
                'exception' => $e,
            ]);
            $presentationVideo->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function exportSlidesAsImages(string $pptxPath, string $imagesDir): int
    {
        $slideCount = 0;

        // Method 1: COM (PowerPoint.Application) — Windows only
        try {
            $powerpoint = new \COM('PowerPoint.Application');
            $powerpoint->Visible = false;

            $presentation = $powerpoint->Presentations->Open($pptxPath, true, false, false);

            foreach ($presentation->Slides as $index => $slide) {
                $slideNum = $index + 1;
                $imagePath = $imagesDir . '/slide_' . str_pad((string) $slideNum, 4, '0', STR_PAD_LEFT) . '.png';
                $slide->Export($imagePath, 'PNG', 1920, 1080);
                $slideCount++;
            }

            $presentation->Close();
            $powerpoint->Quit();

            // Release COM objects
            unset($slide, $presentation, $powerpoint);

            return $slideCount;
        } catch (\Throwable $e) {
            Log::warning('COM PowerPoint export failed: ' . $e->getMessage());
        }

        // Method 2: PhpOffice/PhpPresentation fallback
        try {
            if (class_exists(\PhpOffice\PhpPresentation\IOFactory::class)) {
                $reader = \PhpOffice\PhpPresentation\IOFactory::createReader('PowerPoint2007');
                $ppt = $reader->load($pptxPath);

                foreach ($ppt->getAllSlides() as $index => $slide) {
                    $slideNum = $index + 1;
                    $imagePath = $imagesDir . '/slide_' . str_pad((string) $slideNum, 4, '0', STR_PAD_LEFT) . '.png';

                    if (function_exists('imagecreatetruecolor')) {
                        $img = imagecreatetruecolor(1920, 1080);
                        $white = imagecolorallocate($img, 255, 255, 255);
                        imagefill($img, 0, 0, $white);

                        $this->renderSlideToImage($slide, $img);

                        imagepng($img, $imagePath);
                        imagedestroy($img);
                        $slideCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('PhpPresentation export failed: ' . $e->getMessage());
        }

        return $slideCount;
    }

    private function renderSlideToImage($slide, $img): void
    {
        $black = imagecolorallocate($img, 0, 0, 0);
        $y = 50;

        foreach ($slide->getShapeCollection() as $shape) {
            if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                foreach ($shape->getParagraphs() as $para) {
                    $text = '';
                    foreach ($para->getRichTextElements() as $element) {
                        if ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\Run) {
                            $text .= $element->getText();
                        } elseif ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\BreakElement) {
                            $text .= "\n";
                        }
                    }
                    if (trim($text) !== '') {
                        $fontSize = 5;
                        $lines = explode("\n", $text);
                        foreach ($lines as $line) {
                            imagestring($img, $fontSize, 20, $y, (string) $line, $black);
                            $y += 20;
                        }
                    }
                }
            }
        }
    }

    private function createVideoFromImages(string $imagesDir, string $outputPath, int $slideCount): bool
    {
        $ffmpeg = $this->findFfmpeg();

        if (!$ffmpeg) {
            return false;
        }

        $concatFile = dirname($imagesDir) . '/concat.txt';
        $lines = [];

        for ($i = 1; $i <= $slideCount; $i++) {
            $slideName = 'slide_' . str_pad((string) $i, 4, '0', STR_PAD_LEFT) . '.png';
            $lines[] = "file 'slides/" . $slideName . "'";
            $lines[] = "duration " . $this->slideDuration;
        }
        $lines[] = "file 'slides/" . 'slide_' . str_pad((string) $slideCount, 4, '0', STR_PAD_LEFT) . ".png'";

        file_put_contents($concatFile, implode("\n", $lines));

        $command = sprintf(
            '%s -y -f concat -safe 0 -i "%s" -vf "scale=1920:1080:force_original_aspect_ratio=decrease,pad=1920:1080:(ow-iw)/2:(oh-ih)/2" -c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p -r 1 -an "%s" 2>&1',
            escapeshellcmd($ffmpeg),
            $concatFile,
            $outputPath
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        @unlink($concatFile);

        return $returnCode === 0 && file_exists($outputPath);
    }

    private function createFallbackZip(string $workDir, string $imagesDir, PresentationVideo $presentationVideo): void
    {
        $zipPath = 'presentations/' . $presentationVideo->id . '/slides.zip';
        $fullZipPath = Storage::path($zipPath);

        $zip = new \ZipArchive();
        if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el archivo ZIP de respaldo.');
        }

        $files = glob($imagesDir . '/*.png');
        if (empty($files)) {
            $zip->close();
            throw new \RuntimeException('No se generaron diapositivas.');
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        $presentationVideo->update([
            'video_path' => $zipPath,
            'status' => 'completed',
        ]);
    }

    private function cleanupTemp(string $imagesDir): void
    {
        if (is_dir($imagesDir)) {
            $files = glob($imagesDir . '/*');
            foreach ($files as $file) {
                @unlink($file);
            }
            @rmdir($imagesDir);
        }
    }

    private function findFfmpeg(): ?string
    {
        $candidates = [
            'ffmpeg',
            'ffmpeg.exe',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
        ];

        foreach ($candidates as $cmd) {
            $output = [];
            $returnCode = 0;
            exec(escapeshellcmd($cmd) . ' -version 2>&1', $output, $returnCode);
            if ($returnCode === 0) {
                return $cmd;
            }
        }

        $which = trim((string) shell_exec('where ffmpeg 2>&1'));
        if ($which !== '' && stripos($which, 'Could not find') === false) {
            return trim(explode("\n", $which)[0]);
        }

        return null;
    }
}
