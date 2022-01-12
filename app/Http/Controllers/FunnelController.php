<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\Organization;
use Illuminate\Http\Request;

class FunnelController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Organization $Organization) {
        $this->authorize('view', $Organization);

        return view('_pages.funnels.index', [
            'Organization' => $Organization,
        ]);
    }
}
