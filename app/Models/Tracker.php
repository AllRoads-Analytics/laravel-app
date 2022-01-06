<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tracker extends ModelAbstract
{
    use HasFactory;

    const PAGEVIEW_EVENTS = [
        'pageload', 'pageview'
    ];

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
        $code =
<<<JS
<!-- Start AllRoads Snippet -->
<script>
! function(e, t, n, a, p, r, s) {
e[a] || ((p = e[a] = function() {
p.process ? p.process.apply(p, arguments) : p.queue.push(arguments)
}).queue = [], p.t = +new Date, (r = t.createElement(n)).async = 1, r.src = "https://events.allroadsanalytics.com/allroads.min.js?t=" + 864e5 * Math.ceil(new Date / 864e5), (s = t.getElementsByTagName(n)[0]).parentNode.insertBefore(r, s))
}(window, document, "script", "allroads"),
allroads("init", "$this->pixel_id", {follow: true}),
allroads("event", "pageload");
</script>
<!-- End AllRoads Snippet -->
JS;

        return $code;
    }


    // =========================================================================
    // Relations.
    // =========================================================================

    public function Organization() {
        return $this->belongsTo(Organization::class);
    }
}
