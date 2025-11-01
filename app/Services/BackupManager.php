<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupManager
{
    private string $backupDirectory;
    private int $defaultRetentionDays;

    public function __construct()
    {
        $this->backupDirectory = storage_path('app/backups');
        $this->defaultRetentionDays = config('backup.retention_days', 30);
        
        // Ensure backup directory exists
        if (!File::exists($this->backupDirectory)) {
            File::makeDirectory($this->backupDirectory, 0755, true);
        }
    }

    /**
     * Create a timestamped backup of the specified file
     * 
     * @param string $filePath Path to the file to backup
     * @return string Path to the created backup file
     * @throws Exception If backup creation fails
     */
    public function createBackup(string $filePath): string
    {
        if (!File::exists($filePath)) {
            throw new Exception("Source file does not exist: {$filePath}");
        }

        $filename = basename($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $nameWithoutExtension = pathinfo($filePath, PATHINFO_FILENAME);
        
        $timestamp = Carbon::now()->format('Ymd-His');
        $backupFilename = "{$nameWithoutExtension}.backup.{$timestamp}.{$extension}";
        $backupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . $backupFilename;

        try {
            if (!File::copy($filePath, $backupPath)) {
                throw new Exception("Failed to copy file to backup location");
            }

            Log::info("Backup created successfully", [
                'original_file' => $filePath,
                'backup_file' => $backupPath,
                'timestamp' => $timestamp
            ]);

            return $backupPath;
        } catch (Exception $e) {
            Log::error("Backup creation failed", [
                'original_file' => $filePath,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to create backup: " . $e->getMessage());
        }
    }

    /**
     * Restore a file from its backup
     * 
     * @param string $backupPath Path to the backup file
     * @param string $originalPath Path where the file should be restored
     * @return bool True if restoration was successful
     * @throws Exception If restoration fails
     */
    public function restoreFromBackup(string $backupPath, string $originalPath = null): bool
    {
        if (!File::exists($backupPath)) {
            throw new Exception("Backup file does not exist: {$backupPath}");
        }

        // If no original path provided, derive it from backup filename
        if ($originalPath === null) {
            $originalPath = $this->deriveOriginalPath($backupPath);
        }

        try {
            // Create directory if it doesn't exist
            $directory = dirname($originalPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            if (!File::copy($backupPath, $originalPath)) {
                throw new Exception("Failed to copy backup to original location");
            }

            Log::info("File restored from backup successfully", [
                'backup_file' => $backupPath,
                'restored_to' => $originalPath
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("Backup restoration failed", [
                'backup_file' => $backupPath,
                'target_file' => $originalPath,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to restore from backup: " . $e->getMessage());
        }
    }

    /**
     * Clean up old backup files based on retention policy
     * 
     * @param int|null $retentionDays Number of days to retain backups (null for default)
     * @return int Number of files cleaned up
     */
    public function cleanupOldBackups(int $retentionDays = null): int
    {
        $retentionDays = $retentionDays ?? $this->defaultRetentionDays;
        $cutoffDate = Carbon::now()->subDays($retentionDays);
        
        $backupFiles = File::files($this->backupDirectory);
        $cleanedCount = 0;

        foreach ($backupFiles as $file) {
            $filePath = $file->getPathname();
            
            // Extract timestamp from filename
            if (preg_match('/\.backup\.(\d{8}-\d{6})\./', $file->getFilename(), $matches)) {
                $timestamp = $matches[1];
                
                try {
                    $fileDate = Carbon::createFromFormat('Ymd-His', $timestamp);
                    
                    if ($fileDate->lt($cutoffDate)) {
                        File::delete($filePath);
                        $cleanedCount++;
                        
                        Log::info("Old backup file cleaned up", [
                            'file' => $filePath,
                            'file_date' => $fileDate->toDateTimeString(),
                            'cutoff_date' => $cutoffDate->toDateTimeString()
                        ]);
                    }
                } catch (Exception $e) {
                    Log::warning("Could not parse backup file timestamp", [
                        'file' => $filePath,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info("Backup cleanup completed", [
            'files_cleaned' => $cleanedCount,
            'retention_days' => $retentionDays
        ]);

        return $cleanedCount;
    }

    /**
     * Get list of backup files for a specific original file
     * 
     * @param string $originalFilePath Path to the original file
     * @return array Array of backup file paths
     */
    public function getBackupsForFile(string $originalFilePath): array
    {
        $filename = basename($originalFilePath);
        $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        
        $pattern = "{$nameWithoutExtension}.backup.*.{$extension}";
        $backupFiles = File::glob($this->backupDirectory . DIRECTORY_SEPARATOR . $pattern);
        
        // Sort by timestamp (newest first)
        usort($backupFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return $backupFiles;
    }

    /**
     * Get the backup directory path
     * 
     * @return string
     */
    public function getBackupDirectory(): string
    {
        return $this->backupDirectory;
    }

    /**
     * Derive the original file path from a backup filename
     * 
     * @param string $backupPath Path to the backup file
     * @return string Original file path
     */
    private function deriveOriginalPath(string $backupPath): string
    {
        $filename = basename($backupPath);
        
        // Remove .backup.timestamp from filename
        $originalFilename = preg_replace('/\.backup\.\d{8}-\d{6}/', '', $filename);
        
        // For now, assume files are in their respective project directories
        // This could be enhanced to store original path metadata
        if (str_contains($originalFilename, 'package.json')) {
            return base_path('../front/' . $originalFilename);
        } elseif (str_contains($originalFilename, 'composer.json')) {
            return base_path($originalFilename);
        }
        
        // Default to same directory as backup
        return dirname($backupPath) . DIRECTORY_SEPARATOR . $originalFilename;
    }
}