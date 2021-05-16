<?php

use App\Services\Discount\Constants\DiscountTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->nullable()->unsigned()->index();
            $table->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('set null');
            $table->enum('type', DiscountTypes::get())->default(DiscountTypes::ORDER);
            $table->decimal('amount', 11, 2);
            $table->decimal('original_amount', 11, 2);
            $table->boolean('is_percentage')->default(false);
            $table->decimal('cap', 11, 2)->nullable();
            $table->integer('discount_id')->unsigned()->nullable();
            $table->integer('item_id')->unsigned()->nullable();
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
        Schema::dropIfExists('order_discounts');
    }
}
