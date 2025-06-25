<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class S3FileService
{
    /**
     * Get the current tenant ID for file organization
     */
    protected function getTenantId(): string
    {
        // Assuming you're using Stancl/Tenancy or similar
        return tenant('id') ?? 'default';
    }

    /**
     * Generate tenant-specific file path
     */
    public function getTenantPath(string $folder, string $filename = null): string
    {
        $tenantId = $this->getTenantId();
        $path = "tenants/{$tenantId}/{$folder}";

        return $filename ? "{$path}/{$filename}" : $path;
    }

    /**
     * Upload file to S3 with tenant organization
     */
    public function uploadFile(UploadedFile $file, string $folder, string $filename = null): string
    {
        $filename = $filename ?: $this->generateUniqueFilename($file);
        $path = $this->getTenantPath($folder, $filename);

        // Store file in S3
        Storage::disk('s3')->putFileAs(
            $this->getTenantPath($folder),
            $file,
            $filename,
            'private' // Important for security in multi-tenant apps
        );

        return $path;
    }

    /**
     * Generate unique filename to prevent conflicts
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = Str::slug($name);

        return $sanitizedName . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Get temporary signed URL for secure file access
     */
    public function getTemporaryUrl(string $filePath, int $minutes = 5): string
    {
        return Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes($minutes));
    }

    /**
     * Get permanent URL (only for public files)
     */
    public function getPublicUrl(string $filePath): string
    {
        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $filePath): bool
    {
        return Storage::disk('s3')->exists($filePath);
    }

    /**
     * Delete file from S3
     */
    public function deleteFile(string $filePath): bool
    {
        if ($this->fileExists($filePath)) {
            return Storage::disk('s3')->delete($filePath);
        }

        return false;
    }

    /**
     * Get file size in bytes
     */
    public function getFileSize(string $filePath): int
    {
        return Storage::disk('s3')->size($filePath);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSize(string $filePath): string
    {
        $bytes = $this->getFileSize($filePath);

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Download file as response
     */
    public function downloadFile(string $filePath, string $downloadName = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $downloadName = $downloadName ?: basename($filePath);

        return Storage::disk('s3')->download($filePath, $downloadName);
    }

    /**
     * Copy file within S3 (useful for duplicating assignments)
     */
    public function copyFile(string $sourcePath, string $destinationPath): bool
    {
        if (!$this->fileExists($sourcePath)) {
            return false;
        }

        return Storage::disk('s3')->copy($sourcePath, $destinationPath);
    }

    /**
     * Move file within S3
     */
    public function moveFile(string $sourcePath, string $destinationPath): bool
    {
        if (!$this->fileExists($sourcePath)) {
            return false;
        }

        $copied = $this->copyFile($sourcePath, $destinationPath);

        if ($copied) {
            $this->deleteFile($sourcePath);
            return true;
        }

        return false;
    }

    /**
     * Get file metadata
     */
    public function getFileMetadata(string $filePath): array
    {
        if (!$this->fileExists($filePath)) {
            return [];
        }

        return [
            'size' => $this->getFileSize($filePath),
            'human_size' => $this->getHumanFileSize($filePath),
            'last_modified' => Storage::disk('s3')->lastModified($filePath),
            'mime_type' => Storage::disk('s3')->mimeType($filePath),
            'url' => $this->getTemporaryUrl($filePath),
        ];
    }
}
