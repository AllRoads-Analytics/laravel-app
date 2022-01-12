<?php

use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsolidateOrgTracker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('pixel_id', 20)->after('id')->index();
        });

        Organization::all()->map( function($Organization) {
            $Organization->update([
                'pixel_id' => DB::table('trackers')
                                ->where('organization_id', $Organization->id)
                                ->value('pixel_id')
            ]);
        });

        Schema::dropIfExists('trackers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new \Exception('needta write this, if you wanna do it.');
    }
}
