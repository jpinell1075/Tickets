<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrap();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $file = base_path('databases.json');
        $loadedNames = [];

        if (is_readable($file)) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('config/databases.json inválido: ' . json_last_error_msg());
            } elseif (!empty($data['connections']) && is_array($data['connections'])) {
                foreach ($data['connections'] as $cfg) {
                    if (empty($cfg['name'])) {
                        continue;
                    }

                    $name = $cfg['name'];
                    $driver = $cfg['driver'] ?? 'mysql';
                    $conn = [];
                    foreach($cfg as $key => $value){
                        if($key === 'database'){
                            $conn['database'] = ($driver === 'sqlite' ? database_path($value ?? 'database.sqlite') : $value) ?? '';
                            continue;
                        };
                        $conn[$key] = $value;
                    }

                    if ($driver === 'pgsql') {
                        $conn['schema'] = $cfg['schema'] ?? 'public';
                    }

                    foreach (['sslmode', 'options'] as $opt) {
                        if (isset($cfg[$opt])) {
                            $conn[$opt] = $cfg[$opt];
                        }
                    }

                    Config::set("database.connections.{$name}", $conn);
                    $loadedNames[] = $name;
                }
            }
        }

        $currentDefault = Config::get('database.default');
        if (!empty($loadedNames)) {
            if ($currentDefault === null || !in_array($currentDefault, array_keys(Config::get('database.connections', [])), true)) {
                Config::set('database.default', $loadedNames[0]);
            }
        }
    }
}
