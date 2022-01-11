<?php

use App\Models\Funnel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FunnelPagesRefactor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Funnel::all()->map( function ($Funnel) {
            $new = [];
            foreach ($Funnel->steps as $step) {
                $new[] = [
                    'type' => $Funnel::STEP_TYPE_PAGELOAD_HOST_PATH,
                    'match_data' => $step['path'],
                    'label' => $step['path'],
                ];
            }

            $Funnel->update(['steps' => $new]);
        });
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
