<?php

namespace App\Http\Controllers;

use App\Models\Tracker;
use App\Services\PixelData\PixelDataFunnel;
use App\Services\PixelData\PixelDataHosts;
use App\Services\PixelData\PixelDataUniquePageviews;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PathfinderController extends Controller
{
    const PAGE_SIZE = 5;

    public function get_tracker(Request $Request, Tracker $Tracker) {
        $this->authorize('view', $Tracker->Organization);

        $hosts = PixelDataHosts::init()
            ->setTracker($Tracker)
            ->getHosts();

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

        $pageviews = PixelDataUniquePageviews::init()
            ->setTracker($Tracker)
            ->setHost($host)
            ->setPreviousPages($previous_pages)
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )
            ->setLimit($this::PAGE_SIZE)
            ->setOffset($Request->input('page', 0) * $this::PAGE_SIZE)
            ->getUniquePageviews();

        $pageviews = iterator_to_array($pageviews);

        return [
            'paths' => $pageviews,
            'page_size' => $this::PAGE_SIZE,
        ];
    }

    public function ajax_get_funnel(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('view', $Tracker->Organization);

        $pages = $Request->query('pages', []);

        $page_views = PixelDataFunnel::init()
            ->setTracker($Tracker)
            ->setHost($host)
            ->setPreviousPages($pages)
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )->getFunnelViews();

        return [
            'page_views' => $page_views,
        ];
    }
}
