<?php

use App\Services\Delivery\Methods;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSdeliveryInDeliveryVendorName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $methods = Methods::get();
        $methods = "'" . implode("', '", $methods) . "'";
        DB::statement("ALTER TABLE orders CHANGE COLUMN `delivery_vendor_name` `delivery_vendor_name` ENUM($methods) DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $methods = ['own_delivery','paperfly'];
        $methods = "'" . implode("', '", $methods) . "'";
        DB::statement("ALTER TABLE orders CHANGE COLUMN `delivery_vendor_name` `delivery_vendor_name` ENUM($methods) DEFAULT NULL;");
    }
}
