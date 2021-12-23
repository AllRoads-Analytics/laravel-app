<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\BigQueryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;

class Tracker extends ModelAbstract
{
    use HasFactory;

    // =========================================================================
    // Finders.
    // =========================================================================

    /**
     * @param string $pixel_id
     * @return static
     */
    public static function findByPixelId($pixel_id) {
        return static::where('pixel_id', $pixel_id)->first();
    }


    // =========================================================================
    // Public instance functions.
    // =========================================================================

    public function getRoute() {
        return route('pathfinder.tracker', $this->pixel_id);
    }

    public function getCodeSnippet() {
        $code = '<!-- Start Pathfinder Snippet -->
<script>
!function(e,t,n,a,p,r,s){e[a]||((p=e[a]=function(){p.process?p.process.apply(p,arguments):p.queue.push(arguments)}).queue=[],p.t=+new Date,(r=t.createElement(n)).async=1,r.src="https://probable-skill-330219.ue.r.appspot.com/pathfinder.min.js?t="+864e5*Math.ceil(new Date/864e5),(s=t.getElementsByTagName(n)[0]).parentNode.insertBefore(r,s))}(window,document,"script","pathfinder"),pathfinder("init","' . $this->pixel_id . '"),pathfinder("event","pageload");
</script>
<!-- End Pathfinder Snippet -->';

        return $code;
    }


    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }
}
