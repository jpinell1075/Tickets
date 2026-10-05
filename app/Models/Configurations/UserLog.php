<?php

namespace App\Models\Configurations;
use App\Models\Configurations\Log;
use App\Traits\TableFormData\UserLog as TableFormDataTrait;

class UserLog extends Log
{
    use TableFormDataTrait;

    protected array $appends = ['action'];

    public function getActionAttribute()
    {
        // Remove query parameters from the URL
        $cleanedUrl = explode('?', $this->url)[0];

        // Remove the domain part if exists
        $cleanedUrl = str_replace(url('/'), '', $cleanedUrl);

        if($cleanedUrl === ''){
            $cleanedUrl = '/';
        }

        return $cleanedUrl;
    }

    public function __construct(?string $logs_name = 'user_actions.log', ?string $data = null)
    {
        $this->logs_path = storage_path('logs/');
        $this->logs_name = 'user_actions.log';

        if ($data) {
            $this->raw_line = $data;
            $parsedData = self::parseLogLine($data);
            if ($parsedData) {
                $this->date = $parsedData['date'];
                foreach ($parsedData['data'] as $key => $value) {
                    $this->$key = $value;
                }
            }
        }

        // Initialize appends
        $this->loadAppends();
    }
}