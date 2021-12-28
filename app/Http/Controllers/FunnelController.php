<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\Organization;
use App\Models\Tracker;
use Illuminate\Http\Request;

class FunnelController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Organization $Organization) {
        $this->authorize('view', $Organization);

        return view('_pages.funnels.index', [
            'funnels' => $Organization->Funnels->sortBy('name'),
            'Tracker' => $Organization->getTracker(),
        ]);
    }
}
