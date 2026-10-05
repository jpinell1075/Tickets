<?php

namespace App\Http\Services;

use Illuminate\Console\Scheduling\Schedule;
use App\Models\Backups\ScheduledBackup;
use App\Http\Services\BackupServices;
use Illuminate\Support\Facades\Log;
use Stringable;

class BackupScheduler
{
    public static function schedule(Schedule $schedule)
    {
        try {
            ScheduledBackup::where('active', true)->get()->each(function ($backup) use ($schedule) {
                foreach ($backup->times as $time) {
                    $schedule->call(function () {
                        Log::channel('backups')->info('Iniciando backup...');
                        BackupServices::createBackup();
                    })
                    ->days($backup->days)
                    ->at($time)
                    ->name("Backup-{$backup->id}-{$time}")
                    ->evenInMaintenanceMode()
                    ->after(function (Stringable $output) {
                        Log::channel('backups')->info('Backup Finalizado...', [
                            'output' => $output,
                        ]);
                    });
                }
            });
        } catch (\Exception $e) {
            Log::error('Error scheduling backups: ' . $e->getMessage());
        }
    }
}
