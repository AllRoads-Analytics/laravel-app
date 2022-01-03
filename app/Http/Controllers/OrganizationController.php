<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\PaymentCard;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('home');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('_pages.organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'company' => 'string|max:255',
        ]);

        $Organization = Organization::create([
            'name' => $request->input('company'),
        ])->addUser(auth()->user(), User::ROLE_ADMIN);

        return redirect()->route('organizations.show', $Organization->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Organization = Organization::find($id);

        $this->authorize('view', $Organization);

        if ( ! $Organization) {
            return abort(404);
        }

        // dd($Organization->billingPortalUrl());

        return view('_pages.organizations.show', [
            'Organization' => $Organization,
            'Plan' => $Organization->getPlan(),
            'PaymentCard' => $Organization->getPaymentCard(),
            'usages' => $Organization->getPlanUsage()->getAll(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $Organization = Organization::find($id);

        $this->authorize('manage', $Organization);

        $Organization->delete();

        $request->session()->flash('alert', [
            'type' => 'success',
            'message' => "Organization [$Organization->name] deleted.",
        ]);

        return redirect()->route('home');
    }
}
