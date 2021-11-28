<?php

namespace App\Http\Controllers;

use App\Models\Tracker;
use Illuminate\Http\Request;

class PathfinderController extends Controller
{
    public function get_tracker(Request $Request, Tracker $Tracker) {
        $this->authorize('view', $Tracker->Organization);

        $hosts = $Tracker->getHosts();

        return view('_pages.pathfinder.hosts', [
            'Tracker' => $Tracker,
            'hosts' => $hosts,
        ]);
    }

    public function get_tracker_host(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('view', $Tracker->Organization);

        return view('_pages.pathfinder.tracker_host', [
            'Tracker' => $Tracker,
            'host' => $host,
        ]);
    }


    // =========================================================================
    // Ajax.
    // =========================================================================

    public function ajax_get_next_pages(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('view', $Tracker->Organization);

        $previous_pages = $Request->query('previous_pages', []);

        $pageviews = iterator_to_array(
            $Tracker->getUniquePageviews($host, now()->subDays(40), now(), $previous_pages)
        );

        return [
            'paths' => $pageviews,
        ];
    }

    public function ajax_get_funnel(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('view', $Tracker->Organization);

        $pages = $Request->query('pages', []);

        $page_views = $pages ? $Tracker->getFunnelViews2($host, now()->subDays(40), now(), $pages) : null;

        return [
            'page_views' => $page_views,
        ];
    }
}
