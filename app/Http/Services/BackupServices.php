<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class BackupServices
{
    private const BACKUP_PATH = 'backups/';
    private const available_databases = [
        'mysql' => 'createMysqlBackup',
        'sqlite' => 'createSqliteBackup',
    ];

    /**
     * Create a MySQL backup and save it to the specified path.
     *
     * @return string The path to the backup file.
     */
    public static function createMysqlBackup(string $backupPath): string
    {
        $config = Config::get('database.connections')[Config::get('database.default')];
        $backupPath = escapeshellarg($backupPath);

        if($config['username'] == 'root' && $config['password'] == ''){
            $command = "mysqldump -u " . $config['username'] . " " . $config['database'] . " > $backupPath";
        }else{
            $command = "mysqldump -u " . $config['username'] . " -p" . $config['password'] . " " . $config['database'] . " > $backupPath";
        }

        exec($command);

        return $backupPath;
    }

    /**
     * Create a SQLite backup and save it to the specified path.
     *
     * @return string The path to the backup file.
     */
    public static function createSqliteBackup(string $backupPath): string
    {
        $databasePath = Config::get('database.connections')[Config::get('database.default')]['database'];

        $backupPath = escapeshellarg($backupPath);
        $databasePath = escapeshellarg($databasePath);

        $command = "sqlite3 $databasePath .dump > $backupPath";

        exec($command);

        return $backupPath;
    }

    /**
     * Get the actual database connection name based on the environment variable.
     *
     * @return string The name of the actual database connection.
     * @throws \Exception If the database connection is not supported.
     */
    public static function getActualDatabase(): string
    {
        $database = Config::get('database.connections')[Config::get('database.default')]['driver'];
        if (array_key_exists($database, self::available_databases)) {
            return $database;
        }
        
        throw new \Exception("Database not supported: $database");
    }

    /**
     * Create a backup of the database using the appropriate method based on the database type.
     *
     * @return bool True if the backup was created successfully, false otherwise.
     * @throws \Exception If the backup method is not found or if there is an error during the backup process.
     * @throws \Exception If there is an error copying the backup to the remote location.
     */
    public static function createBackup()
    {
        $backupDir = storage_path(self::BACKUP_PATH);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $config = Config::get('database.connections')[Config::get('database.default')];

        $backupFileName = $config['name'] . '_' . date('Y-m-d_H-i-s') . '.sql';
        $backupPath = storage_path(self::BACKUP_PATH . $backupFileName);
        $database = self::getActualDatabase();

        $backupMethod = self::available_databases[$database];

        if (method_exists(__CLASS__, $backupMethod)) {
            self::$backupMethod($backupPath);
            if (file_exists($backupPath)) {
                $disk = config('filesystems.backup.driver', 'local');
                $stream = fopen($backupPath, 'r');
                $result = Storage::disk($disk)->put(self::BACKUP_PATH . $backupFileName, $stream);
                fclose($stream);
                unlink($backupPath);

                if (!$result) {
                    throw new \Exception("Error copying backup to remote disk: $disk");
                }
                return true;
            }
        } else {
            throw new \Exception("Backup method not found: $backupMethod");
        }
        return false;
    }
}