<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\BigQueryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tracker extends Model
{
    use HasFactory;

    public static function findByPixelId($pixel_id) {
        return static::where('pixel_id', $pixel_id)->first();
    }

    public function getUniquePageviews(Carbon $start_date, Carbon $end_date) {
        $rows = (new BigQueryService())->select(
            ['path', 'count(*) as views'],
            'WHERE DATE(ts) >= "' . $start_date->toDateString() . '"' .
                ' AND DATE(ts) <= "' . $end_date->toDateString() . '"' .
                ' AND id = "' . $this->pixel_id . '"' .
                ' AND ev = "pageload"' .
            ' GROUP BY path ORDER BY views desc'
        )->rows();

        return $rows;
    }
}
