<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchDetailColumnInOrderSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_skus', function (Blueprint $table) {
            $table->json('batch_detail')->nullable()->after('unit_price');

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
            $table->dropColumn('batch_detail');
        });
    }
}
