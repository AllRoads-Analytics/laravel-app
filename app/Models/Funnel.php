<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Funnel extends ModelAbstract
{
    use HasFactory;

    protected $casts = [
        'steps' => 'array',
    ];

    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function getPages() {
        return collect($this->steps)
            ->filter( fn($step) => 'pageload' === $step['ev'] )
            ->pluck('path')
            ->toArray();
    }

    public function updatePages(array $pages) {
        return $this->update([
            'steps' => array_map(function($page) {
                return [
                    'ev' => 'pageload',
                    'path' => $page,
                ];
            }, $pages),
        ]);
    }


    // =========================================================================
    // Public static functions.
    // =========================================================================

    public static function createFromPages(Organization $Organization, string $hostname, string $name, array $pages) {
        return static::create([
            'organization_id' => $Organization->id,
            'hostname' => $hostname,
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
