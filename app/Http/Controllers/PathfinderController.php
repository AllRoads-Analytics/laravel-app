<?php

namespace App\Http\Controllers;

use App\Models\Tracker;
use Illuminate\Http\Request;

class PathfinderController extends Controller
{
    public function index(Request $Request) {
        $Tracker = Tracker::findByPixelId('ID-timcom');
        $pages = $Tracker->getUniquePageviews(now()->subDays(1), now());

        return view('_pages.pathfinder.top', [
            'pages' => $pages,
        ]);
    }
}
