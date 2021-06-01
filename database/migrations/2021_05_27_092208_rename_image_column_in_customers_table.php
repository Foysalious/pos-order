<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameImageColumnInCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        DB::statement("ALTER TABLE `payment_gateways` CHANGE `method_name` `name` char(255) NOT NULL");
        DB::statement("ALTER TABLE `customers` CHANGE `image` `pro_pic` char(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `customers` CHANGE `pro_pic` `image` char(255) NULL");
    }
}
