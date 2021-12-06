<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDeliveryVendorNameToDeliveryVendor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_vendor_name');
            $table->json('delivery_vendor')->after('interest')->nullable();
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
            $table->dropColumn('delivery_vendor');
        });
        $methods = Methods::get();
        $methods = "'" . implode("', '", $methods) . "'";
        DB::statement("ALTER TABLE orders CHANGE COLUMN `delivery_vendor` `delivery_vendor_name` ENUM($methods) DEFAULT NULL;");
    }
}
