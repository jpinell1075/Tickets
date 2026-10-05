<?php

namespace App\Models\Configurations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class Log
{
    protected array $appends = [];

    private string $logs_path;
    private string $logs_name;

    private array $filters = [];
    private string $orderByField = 'name';
    private string $orderByDirection = 'asc';

        /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->$key ?? null;
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value): void
    {
        $this->$key = $value;
    }

    /**
     * Check if an attribute is set on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->$key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset(string $key): void
    {
        unset($this->$key);
    }

    public function loadAppends(): void
    {
        foreach ($this->appends as $append) {
            $function = 'get' . ucfirst($append) . 'Attribute';
            $this->$append = $this->$function();
        }
    }

    /**
     * Parse a log line to extract the date and JSON data.
     *
     * @param string $line
     * @return array|null
     */
    public static function parseLogLine(string $line): ?array
    {
        if (preg_match('/^\[([^\]]+)\].*?({.*})$/', $line, $matches)) {
            $date = $matches[1];
            $json = $matches[2];
            $data = json_decode($json, true);
            return [
                'date' => $date,
                'data' => $data,
            ];
        }
        return null;
    }

    /**
     * Create a new Log instance from a log line.
     * @param string|null $logs_name The name of the log file.
     * @param string $data The log line data.
     * @return static The created Log instance.
     */
    public static function create(?string $logs_name = 'user_actions.log', ?string $data): static
    {
        $log = new static($logs_name, $data);
        return $log;
    }

    /**
     * Add a filter to the query.
     *
     * @param string $field The field to filter by.
     * @param string $operator The operator to use (e.g., '=', 'like').
     * @param mixed $value The value to filter by.
     * @return $this
     */
    public function where(string $field, string $operator, $value): self
    {
        $this->filters[] = compact('field', 'operator', 'value');
        return $this;
    }

    /**
     * Set the order by clause for the query.
     *
     * @param string $orderByField The field to order by.
     * @param string $orderByDirection The direction to order (asc or desc).
     * @return $this
     */
    public function orderBy(string $orderByField, string $orderByDirection = 'asc'): self
    {
        $this->orderByField = $orderByField;
        $this->orderByDirection = strtolower($orderByDirection) === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    /**
     * Get all backups with applied filters.
     *
     * @return Collection
     */
    public function get(?int $perPage = null, ?int $page = null): Collection
    {
        $file = $this->logs_path . $this->logs_name;
        if (!File::exists($file)) {
            return collect();
        }

        if($perPage && $page){
            $lines = File::lines($file)->forPage($page, $perPage);
        }
        else{
            $lines = File::lines($file);
        }

        $logs = $lines->map(function ($line) {
            return trim($line);
        })->filter()->map(function ($line) {
            return ['entry' => $line];
        });

        //instantiate Log objects
        $logs = collect($logs->map(function ($item) {
            return self::create($this->logs_name, $item['entry']);
        }));

        // Apply filters
        foreach ($this->filters as $filter) {
            [$field, $operator, $value] = array_values($filter);
            if ($operator === '=') {
                $logs = $logs->filter(fn($log) => isset($log->$field) && $log->$field == $value);
            } 
            elseif ($operator === '!=') {
                $logs = $logs->filter(fn($log) => isset($log->$field) && $log->$field != $value);
            }
            elseif ($operator === 'like') {
                $value = str_replace('%', '', $value);
                $logs = $logs->filter(fn($log) => isset($log->$field) && stripos($log->$field, $value) !== false);
            }
        }

        // Apply ordering
        if ($this->orderByField) {
            $logs = $logs->sortBy(function ($log) {
                return $log->{$this->orderByField} ?? null;
            }, SORT_REGULAR, $this->orderByDirection === 'desc');
        }

        return $logs;
    }

    /**
     * Paginate the backups.
     *
     * @param int $perPage The number of items per page.
     * @param int $page The current page.
     * @return array
     */
    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $allLogs = $this->get($perPage, $page);
        $total = $allLogs->count();

        return new LengthAwarePaginator(
            $allLogs,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Static query method to mimic Eloquent's query builder.
     *
     * @return static
     */
    public static function query(): static
    {
        return new static;
    }

    /**
     * Delete a backup file.
     *
     * @return bool
     * @throws \Exception if the file does not exist.
     */
    public function delete(): bool
    {
        $file = $this->logs_path . $this->logs_name;
        if (!File::exists($file)) {
            throw new \Exception("Log file does not exist: {$this->logs_name}");
        }

        $lines = File::lines($file)->toArray();
        $newLines = array_filter($lines, fn($line) => trim($line) !== trim($this->raw_line));

        if (count($lines) === count($newLines)) {
            return false;
        }

        File::put($file, implode(PHP_EOL, $newLines) . PHP_EOL);
        return true;
    }
}