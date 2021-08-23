<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeleteAtColumnInCustomersAndOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes()->after('pro_pic');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes()->after('api_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
