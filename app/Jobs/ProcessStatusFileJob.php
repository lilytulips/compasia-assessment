<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Imports\StatusImport;

class ProcessStatusFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function handle()
    {
        try {
            if (!Storage::exists($this->path)) {
                throw new \Exception("Uploaded file not found in storage: {$this->path}");
            }

            $fullPath = Storage::path($this->path);

            $import = new StatusImport();
            Excel::import($import, $fullPath);

            $updatedCount = $import->getUpdatedCount();
            $createdCount = $import->getCreatedCount();
            $errors = $import->getErrors();

            Log::info("ProcessStatusFileJob completed", [
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'errors_count' => count($errors),
                'errors' => $errors
            ]);

            Storage::delete($this->path);
        } catch (\Exception $e) {
            Log::error("ProcessStatusFileJob failed: " . $e->getMessage(), [
                'path' => $this->path,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("ProcessStatusFileJob failed permanently", [
            'path' => $this->path,
            'error' => $exception->getMessage()
        ]);
    }
}
