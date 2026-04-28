<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_cameras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained('assets')->cascadeOnDelete();
            $table->enum('protocol', ['rtsp', 'http', 'mjpeg', 'hls', 'onvif', 'other'])->default('rtsp');
            // Streams
            $table->string('stream_url', 500)->nullable()
                ->comment('RTSP / HLS / WebRTC stream URL');
            $table->string('mjpeg_url', 500)->nullable()
                ->comment('MJPEG HTTP stream — works directly in <img> tag');
            $table->string('snapshot_url', 500)->nullable()
                ->comment('Single JPEG snapshot URL — refreshable');
            $table->string('nvr_url', 500)->nullable()
                ->comment('Web UI of the NVR/DVR for full live view');
            // Specs
            $table->string('resolution', 30)->nullable(); // e.g. 1920x1080
            $table->unsignedInteger('fps')->nullable();
            $table->boolean('has_audio')->default(false);
            $table->boolean('has_motion_detection')->default(false);
            $table->boolean('has_ptz')->default(false); // Pan-Tilt-Zoom
            // Link to credentials in vault (optional)
            $table->foreignId('access_id')->nullable()->constrained('accesses')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_cameras');
    }
};
