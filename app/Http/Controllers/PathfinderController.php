<?php

namespace App\Http\Controllers;

use App\Models\Tracker;
use Illuminate\Http\Request;

class PathfinderController extends Controller
{
    public function get_tracker(Request $Request, $tracker_pixel_id) {
        $Tracker = Tracker::findByPixelId($tracker_pixel_id);

        if ( ! $Tracker) {
            return abort(404);
        }

        $hosts = $Tracker->getHosts();

        return view('_pages.pathfinder.hosts', [
            'Tracker' => $Tracker,
            'hosts' => $hosts,
        ]);
    }

    public function get_tracker_host(Request $Request, $tracker_pixel_id, $host) {
        $Tracker = Tracker::findByPixelId($tracker_pixel_id);
        $previous_pages = $Request->query('previous_pages', []);

        if ( ! $Tracker) {
            return abort(404);
        }

        // $pageviews = $Tracker->getUniquePageviews($host, now()->subDays(40), now(), $previous_pages);

        // $funnel_pages = $previous_pages ? $Tracker->getFunnelViews2($host, now()->subDays(40), now(), $previous_pages) : null;

        return view('_pages.pathfinder.tracker_host', [
            'Tracker' => $Tracker,
            'host' => $host,
            // 'pageviews' => $pageviews,
            // 'funnel_pages' => $funnel_pages,
            'previous_pages' => $previous_pages,
        ]);
    }


    // =========================================================================
    // Ajax.
    // =========================================================================

    public function ajax_get_next_paths(Request $Request, $tracker_pixel_id, $host) {
        $Tracker = Tracker::findByPixelId($tracker_pixel_id);
        $previous_pages = $Request->query('previous_pages', []);

        if ( ! $Tracker) {
            return abort(404);
        }

        $pageviews = iterator_to_array($Tracker->getUniquePageviews($host, now()->subDays(40), now(), $previous_pages));

        // dd($pageviews);

        return [
            'paths' => $pageviews,
        ];
    }
}
