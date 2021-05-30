<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\Order\Constants\OrderLogTypes;

class AddTypeToOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_logs', function (Blueprint $table) {
            $table->enum('type', OrderLogTypes::get())->after('order_id')->default(OrderLogTypes::INIT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_logs', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
