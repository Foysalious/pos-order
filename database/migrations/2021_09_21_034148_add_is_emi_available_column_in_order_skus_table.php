<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEmiAvailableColumnInOrderSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_skus', function (Blueprint $table) {
            $table->tinyInteger('is_emi_available')->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_skus', function (Blueprint $table) {
            $table->dropColumn('is_emi_available');
        });
    }
}
