<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Funnel extends ModelAbstract
{
    use HasFactory;

    protected $casts = [
        'steps' => 'array',
    ];

    const STEP_TYPE_PAGELOAD_HOST_PATH = 'pageload_host_path';
    const STEP_TYPE_PAGELOAD_HOST_PATH_LIKE = 'pageload_host_path_like';

    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }


    // =========================================================================
    // Public getters.
    // =========================================================================

    public function getRoute() {
        return route('pathfinder.tracker', [
            'tracker' => $this->Organization->getTracker()->pixel_id,
            'funnel' => $this->id,
        ]);
    }


    // =========================================================================
    // Public static functions.
    // =========================================================================

    public static function createFromPages(Organization $Organization, string $name, array $pages) {
        return static::create([
            'organization_id' => $Organization->id,
            'name' => $name,
            'steps' => array_map(function($page) {
                return [
                    'ev' => 'pageload',
                    'path' => $page,
                ];
            }, $pages),
        ]);
    }
}
