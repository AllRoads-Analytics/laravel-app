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

    protected $cached_route;

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
        if ( ! isset($this->cached_route)) {
            $this->cached_route = route('pathfinder.tracker', [
                'organization' => $this->Organization->pixel_id,
                'funnel' => $this->id,
            ]);
        }
        return $this->cached_route;
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
