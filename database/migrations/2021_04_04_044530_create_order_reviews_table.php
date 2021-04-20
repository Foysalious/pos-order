<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('customer_id')->nullable()->unsigned()->index();
            $table->bigInteger('order_sku_id')->nullable()->unsigned()->index();
            $table->string('review_title')->nullable();
            $table->text('review_details')->nullable();
            $table->integer('rating')->default(0);

            $table->bigInteger('product_id')->nullable()->unsigned()->index();
            $table->bigInteger('category_id')->nullable()->unsigned()->index();

            $table->bigInteger('partner_id')->nullable()->unsigned()->index();
            $table->foreign('partner_id')->references('id')->on('partners')->onUpdate('cascade')->onDelete('set null');

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
        Schema::dropIfExists('reviews');
    }
}
