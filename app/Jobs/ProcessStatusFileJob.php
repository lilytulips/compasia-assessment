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
        Log::info("ProcessStatusFileJob constructor", [
            'path' => $this->path
        ]);
    }

    public function handle()
    {
        try {
            $fullPath = Storage::path($this->path);

            Log::info("Checking file", [
                'stored_path' => $this->path,
                'resolved_full_path' => $fullPath,
                'storage_disk' => config('filesystems.default'),
                'disk_root' => config('filesystems.disks.local.root')
            ]);

            if (!Storage::exists($this->path)) {
                Log::error("File not found in storage: {$this->path}", [
                    'storage_disk' => config('filesystems.default'),
                    'expected_path' => $fullPath,
                    'file_exists_on_fs' => file_exists($fullPath)
                ]);
                throw new \Exception("Uploaded file not found in storage: {$this->path}");
            }

            if (!file_exists($fullPath)) {
                Log::error("File does not exist at filesystem path: {$fullPath}", [
                    'stored_path' => $this->path,
                    'storage_path' => storage_path('app'),
                    'disk_root' => config('filesystems.disks.local.root')
                ]);
                throw new \Exception("File does not exist on filesystem: {$fullPath}");
            }

            Log::info("Processing file", [
                'path' => $this->path,
                'full_path' => $fullPath,
                'file_size' => filesize($fullPath),
                'file_exists' => file_exists($fullPath),
                'is_readable' => is_readable($fullPath)
            ]);

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
