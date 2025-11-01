<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the package update backup system
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Backup Retention Days
    |--------------------------------------------------------------------------
    |
    | Number of days to retain backup files before automatic cleanup.
    | Set to 0 to disable automatic cleanup.
    |
    */
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | Directory where backup files will be stored. This path is relative
    | to the storage/app directory.
    |
    */
    'directory' => env('BACKUP_DIRECTORY', 'backups'),

    /*
    |--------------------------------------------------------------------------
    | Automatic Cleanup
    |--------------------------------------------------------------------------
    |
    | Whether to automatically clean up old backup files during the
    | backup creation process.
    |
    */
    'auto_cleanup' => env('BACKUP_AUTO_CLEANUP', true),
];