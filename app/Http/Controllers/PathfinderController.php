<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\Tracker;
use App\Services\PixelData\PixelDataFilterOptions;
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

    public function ajax_get_filter_options(Request $Request, Tracker $Tracker, $host) {
        $filter_options = PixelDataFilterOptions::init()
            ->setTracker($Tracker)
            ->setHost($host)
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )->getFilterOptions();

        return [
            'filter_options' => $filter_options,
        ];
    }

    public function ajax_get_next_pages(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('view', $Tracker->Organization);

        $previous_pages = $Request->query('previous_pages', []);

        $pageviews = PixelDataUniquePageviews::init()
            ->setTracker($Tracker)
            ->setHost($host)
            ->setPreviousPages($previous_pages)
            ->setSearch($Request->input('search') ?? '')
            ->setFilters($Request->only(array_keys(PixelDataFunnel::FILTERABLE_FIELDS)))
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

        $pages = $Request->query('previous_pages', []);

        $page_views = PixelDataFunnel::init()
            ->setTracker($Tracker)
            ->setHost($host)
            ->setPreviousPages($pages)
            ->setFilters($Request->only(array_keys(PixelDataFunnel::FILTERABLE_FIELDS)))
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )->getFunnelViews();

        return [
            'page_views' => $page_views,
        ];
    }

    public function ajax_get_saved_funnel_pages(Request $Request, Funnel $Funnel) {
        $this->authorize('view', $Funnel->Organization);

        $pages = collect($Funnel->steps)
            ->filter( fn($step) => 'pageload' === $step['ev'] )
            ->pluck('path')
            ->toArray();

        return [
            'name' => $Funnel->name,
            'pages' => $pages,
            'organization_id' => $Funnel->Organization->id,
        ];
    }

    public function ajax_post_funnel(Request $Request, Tracker $Tracker, $host) {
        $this->authorize('edit', $Tracker->Organization);

        $pages = $Request->query('pages', []);

        if ($id = $Request->input('id')) {
            $Funnel = Funnel::find($id);
            $Funnel->updatePages($pages);
            $Funnel->name = $Request->input('name');
            $Funnel->save();

        } else {
            $Funnel = Funnel::createFromPages($Tracker->Organization, $host, $Request->input('name'), $pages);
        }

        return [
            'Funnel' => $Funnel,
        ];
    }

    public function post_funnel_delete(Request $Request, Funnel $Funnel) {
        $this->authorize('edit', $Funnel->Organization);

        $name = $Funnel->name;
        $Funnel->delete();

        $Request->session()->flash('alert', [
            'type' => 'success',
            'message' => "Funnel [$name] deleted.",
        ]);

        return [
            'success' => true,
        ];
    }
}
