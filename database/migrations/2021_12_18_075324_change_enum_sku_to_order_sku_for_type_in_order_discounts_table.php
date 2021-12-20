<?php

use App\Services\Discount\Constants\DiscountTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeEnumSkuToOrderSkuForTypeInOrderDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE order_discounts CHANGE `type` `type` ENUM('order', 'order_sku', 'voucher') NOT NULL DEFAULT 'order'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE order_discounts CHANGE `type` `type` ENUM('order', 'sku', 'voucher') NOT NULL DEFAULT 'order'");

    }
}
