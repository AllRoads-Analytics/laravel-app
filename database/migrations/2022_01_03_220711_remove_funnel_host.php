<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFunnelHost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funnels', function (Blueprint $table) {
            $table->dropColumn([
                'hostname',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('funnels', function (Blueprint $table) {
            $table->string('hostname')->nullable()->after('organization_id');
        });
    }
}
