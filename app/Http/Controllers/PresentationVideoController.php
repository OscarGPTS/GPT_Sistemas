<?php

namespace App\Http\Controllers;

use App\Models\PresentationVideo;
use App\Services\PresentationToVideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PresentationVideoController extends Controller
{
    public function index(Request $request): View
    {
        $query = PresentationVideo::with('user')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        return view('presentation_videos.index', [
            'presentations' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('presentation_videos.form', [
            'presentation' => new PresentationVideo(),
        ]);
    }

    public function store(Request $request, PresentationToVideoService $service): RedirectResponse
    {
        $request->validate([
            'presentation' => ['required', 'file', 'mimes:ppt,pptx', 'max:102400'],
        ]);

        $file = $request->file('presentation');
        $path = $file->store('presentations/' . $request->user()->id);

        $presentation = PresentationVideo::create([
            'user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'status' => 'pending',
        ]);

        $service->process($presentation);

        if ($presentation->fresh()->isFailed()) {
            return redirect()
                ->route('presentation_videos.show', $presentation)
                ->with('error', 'La conversión falló: ' . $presentation->error_message);
        }

        return redirect()
            ->route('presentation_videos.show', $presentation)
            ->with('success', 'Presentación convertida exitosamente. ' . $presentation->slide_count . ' diapositivas procesadas.');
    }

    public function show(PresentationVideo $presentationVideo): View
    {
        abort_unless(
            $presentationVideo->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        $presentationVideo->load('user');

        return view('presentation_videos.show', [
            'presentation' => $presentationVideo,
        ]);
    }

    public function download(PresentationVideo $presentationVideo)
    {
        abort_unless(
            $presentationVideo->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        abort_unless($presentationVideo->isCompleted() && $presentationVideo->video_path, 404);

        if (!Storage::exists($presentationVideo->video_path)) {
            abort(404, 'El archivo ya no está disponible.');
        }

        $extension = pathinfo($presentationVideo->video_path, PATHINFO_EXTENSION);
        $downloadName = pathinfo($presentationVideo->original_name, PATHINFO_FILENAME)
            . ($extension === 'zip' ? '_slides.zip' : '.mp4');

        return Storage::download($presentationVideo->video_path, $downloadName);
    }

    public function destroy(PresentationVideo $presentationVideo): RedirectResponse
    {
        abort_unless(
            $presentationVideo->user_id === auth()->id() || auth()->user()->isAdmin(),
            403
        );

        if ($presentationVideo->stored_path && Storage::exists($presentationVideo->stored_path)) {
            Storage::delete($presentationVideo->stored_path);
        }

        if ($presentationVideo->video_path && Storage::exists($presentationVideo->video_path)) {
            Storage::delete($presentationVideo->video_path);
        }

        Storage::deleteDirectory('presentations/' . $presentationVideo->id);

        $presentationVideo->delete();

        return redirect()
            ->route('presentation_videos.index')
            ->with('success', 'Conversión eliminada.');
    }
}
