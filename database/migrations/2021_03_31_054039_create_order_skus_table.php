<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_skus', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->nullable()->unsigned()->index();
            $table->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('set null');
            $table->string('name');
            $table->bigInteger('sku_id')->nullable()->unsigned();
            $table->json('sku_details')->nullable();
            $table->decimal('quantity', 11,2)->unsigned()->default(1);
            $table->decimal('price', 11, 2);
            $table->decimal('vat_percentage', 11, 2)->nullable();
            $table->integer('warranty')->default(0);
            $table->enum('warranty_unit', array_keys(config('pos.warranty_unit')))->default('day');
            $table->string('unit')->nullable();
            $table->decimal('weight', 11, 2)->nullable();
            $table->enum('weight', array_keys(config('pos.warranty_unit')))->nullable();
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
        Schema::dropIfExists('order_skus');
    }
}
