<?php

use App\Services\Order\Constants\WarrantyUnits;
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
            $table->json('details')->nullable();
            $table->decimal('quantity', 11,2)->unsigned()->default(1);
            $table->decimal('unit_price', 11, 2);
            $table->string('unit')->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->integer('warranty')->default(0);
            $table->enum('warranty_unit', WarrantyUnits::get())->default(WarrantyUnits::DAY);
            $table->longText('note')->nullable();
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
