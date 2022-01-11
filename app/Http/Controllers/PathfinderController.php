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
    const PAGE_SIZE = 10;

    public function get_tracker(Request $Request, Tracker $Tracker) {
        $this->authorize('view', $Tracker->Organization);

        return view('_pages.pathfinder.tracker', [
            'Tracker' => $Tracker,
            'view_days' => $Tracker->Organization->getPlan()->limit_data_view_days,
        ]);
    }


    // =========================================================================
    // Ajax.
    // =========================================================================

    public function ajax_get_filter_options(Request $Request, Tracker $Tracker) {
        $filter_options = PixelDataFilterOptions::init()
            ->setTracker($Tracker)
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )->getFilterOptions();

        return [
            'filter_options' => $filter_options,
        ];
    }

    public function ajax_get_next_pages(Request $Request, Tracker $Tracker) {
        $this->authorize('view', $Tracker->Organization);

        $previous_steps = $Request->query('previous_steps', []);

        $PixelDataUniquePageviews = PixelDataUniquePageviews::init()
            ->setTracker($Tracker)
            ->setPreviousSteps($previous_steps)
            ->setSearch($Request->input('search') ?? '')
            ->setFilters($Request->only(array_keys(PixelDataFunnel::FILTERABLE_FIELDS)))
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )
            ->setLimit($this::PAGE_SIZE)
            ->setOffset($Request->input('page', 0) * $this::PAGE_SIZE);

        if ($host = $Request->input('host')) {
            $PixelDataUniquePageviews->setHost($host);
        }

        $pageviews = iterator_to_array($PixelDataUniquePageviews->getUniquePageviews());

        return [
            'paths' => $pageviews,
            'page_size' => $this::PAGE_SIZE,
        ];
    }

    public function ajax_get_funnel(Request $Request, Tracker $Tracker) {
        $this->authorize('view', $Tracker->Organization);

        $steps = $Request->query('previous_steps', []);

        $page_views = PixelDataFunnel::init()
            ->setTracker($Tracker)
            ->setPreviousSteps($steps)
            ->setFilters($Request->only(array_keys(PixelDataFunnel::FILTERABLE_FIELDS)))
            ->setDateRange(
                Carbon::createFromFormat('Y-m-d', $Request->input('start_date')),
                Carbon::createFromFormat('Y-m-d', $Request->input('end_date'))
            )->getFunnelViews();

        return [
            'page_views' => $page_views,
        ];
    }

    public function ajax_get_saved_funnel_steps(Request $Request, Funnel $Funnel) {
        $this->authorize('view', $Funnel->Organization);

        return [
            'name' => $Funnel->name,
            'steps' => $Funnel->steps,
            'organization_id' => $Funnel->Organization->id,
        ];
    }

    public function ajax_post_funnel(Request $Request, Tracker $Tracker) {
        $this->authorize('edit', $Tracker->Organization);

        $steps = $Request->query('steps', []);

        if ($id = $Request->input('id')) {
            $Funnel = Funnel::find($id);
            $Funnel->update([
                'name' => $Request->input('name'),
                'steps' => $Request->query('steps', [])
            ]);

        } else {
            $Funnel = Funnel::create([
                'organization_id' => $Tracker->Organization->id,
                'name' => $Request->input('name'),
                'steps' => $steps,
            ]);
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
