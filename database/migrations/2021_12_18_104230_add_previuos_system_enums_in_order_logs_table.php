<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreviuosSystemEnumsInOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE order_logs CHANGE `type` `type` ENUM('due_bill', 'payments', 'emi', 'products_and_prices', 'order_status', 'customer', 'others', 'partial_return', 'exchange', 'full_return', 'item_quantity_increase') NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE order_logs CHANGE `type` `type` ENUM('due_bill', 'payments', 'emi', 'products_and_prices', 'order_status', 'customer', 'others') NULL");
    }
}
