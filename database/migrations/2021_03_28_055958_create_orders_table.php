<?php

use App\Services\Order\Constants\PaymentStatuses;
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
            $table->bigInteger('previous_order_id')->unsigned()->nullable();
            $table->foreign('previous_order_id')->references('id')->on('orders')->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('partner_wise_order_id')->unsigned();
            $table->bigInteger('partner_id')->nullable()->unsigned()->index();
            $table->foreign('partner_id')->references('id')->on('partners')
                ->onUpdate('cascade')->onDelete('set null');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('set null');
            $table->enum('status', Statuses::get())->default(Statuses::PENDING);
            $table->enum('payment_status', PaymentStatuses::get())->nullable();
            $table->bigInteger('sales_channel_id');
            $table->bigInteger('emi_month')->nullable();
            $table->decimal('interest', 8, 2)->nullable();
            $table->decimal('delivery_charge', 8, 2)->nullable();
            $table->decimal('bank_transaction_charge', 8, 2)->nullable();
            $table->decimal('total_amount', 8, 2)->nullable();
            $table->decimal('paid_amount', 8, 2)->nullable();
            $table->string('delivery_name');
            $table->string('delivery_mobile');
            $table->string('delivery_address');
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
