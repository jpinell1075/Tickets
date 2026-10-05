<?php

namespace App\Traits;

use App\Models\Configurations\Configuration;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

trait ModelSoftDeleteTrait
{
    use SoftDeletes;

    /**
     * Override delete() method to handle soft deletes with cascade.
     * @return bool|null
     */
    public function delete()
    {
        if ($this->useSoftDelete()) {
            return $this->softDeleteCascade();
        }

        return $this->hardDeleteCascade();
    }

    /**
     * Soft delete with cascade.
     * @return bool|null
     */
    protected function softDeleteCascade()
    {
        foreach ($this->cascadeDeletes() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->get() as $related) {
                $related->delete();
            }
        }

        if (method_exists($this, 'runSoftDelete')) {
            $this->runSoftDelete();
            return true;
        }

        return parent::delete();
    }

    /**
     * Hard delete with cascade.
     * @return bool|null
     */
    protected function hardDeleteCascade()
    {
        foreach ($this->cascadeDeletes() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->get() as $related) {
                if (method_exists($related, 'forceDelete')) {
                    $related->forceDelete();
                } else {
                    $related->delete();
                }
            }
        }
        parent::performDeleteOnModel();

        return true;
    }

    /**
     * forceDelete public — always hard deletes.
     * @return bool|null
     */
    public function forceDelete()
    {
        foreach ($this->cascadeDeletes() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->get() as $related) {
                if (method_exists($related, 'forceDelete')) {
                    $related->forceDelete();
                } else {
                    $related->delete();
                }
            }
        }

        parent::performDeleteOnModel();

        return true;
    }

    /**
     * Define relationships to be cascade deleted.
     * Example: ['posts', 'comments']
     * @return array
     */
    protected function cascadeDeletes(): array
    {
        return [];
    }

    /**
     * Determine if soft deletes should be used based on configuration.
     * @return bool
     */
    protected function useSoftDelete(): bool
    {
        return Cache::remember('global_use_soft_deletes', 60, function () {
            $value = Configuration::where('key', 'global_use_soft_deletes')->value('value');
            return $value === 'true';
        });
    }
}
