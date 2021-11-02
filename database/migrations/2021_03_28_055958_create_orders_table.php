<?php

use App\Services\Order\Constants\Statuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->startingValue(config('migration.starting_ids.orders'));
            $table->bigInteger('partner_wise_order_id')->unsigned();
            $table->bigInteger('partner_id')->nullable()->unsigned()->index();
            $table->foreign('partner_id')->references('id')->on('partners')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('customer_id')->nullable()->index();
            $table->enum('status', Statuses::get())->default(Statuses::PENDING);
            $table->bigInteger('sales_channel_id');
            $table->bigInteger('emi_month')->nullable();
            $table->decimal('interest', 11, 2)->nullable();
            $table->decimal('delivery_charge', 11, 2)->nullable();
            $table->decimal('bank_transaction_charge', 11, 2)->nullable();
            $table->string('delivery_name')->nullable();
            $table->string('delivery_mobile')->nullable();
            $table->string('delivery_address')->nullable();
            $table->longText('note')->nullable();
            $table->bigInteger('voucher_id')->unsigned()->nullable();
            commonColumns($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
