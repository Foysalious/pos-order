<?php

use App\Services\Delivery\Methods;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryVendorRelatedColumnsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_vendor_name', Methods::get())->after('delivery_charge')->nullable();
            $table->string('delivery_request_id')->after('delivery_vendor_name')->nullable();
            $table->string('delivery_thana')->after('delivery_request_id')->nullable();
            $table->string('delivery_district')->after('delivery_thana')->nullable();
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
            $table->dropColumn('delivery_vendor_name');
            $table->dropColumn('delivery_request_id');
            $table->dropColumn('delivery_thana');
            $table->dropColumn('delivery_district');
        });
    }
}
