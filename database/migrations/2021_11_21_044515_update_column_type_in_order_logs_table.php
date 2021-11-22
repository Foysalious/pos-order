<?php

use App\Services\Order\Constants\OrderLogTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateColumnTypeInOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_logs', function (Blueprint $table) {
            $types = OrderLogTypes::get();
            $types = "'" . implode("', '", $types) . "'";
            DB::statement("ALTER TABLE order_logs CHANGE COLUMN `type` `type` ENUM($types) DEFAULT null;");
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
            $old_statuses = "'" . implode("', '", ["products_and_prices", "order_status", "customer", "others"]) . "'";
            DB::statement("ALTER TABLE order_logs CHANGE COLUMN `type` `type` ENUM($old_statuses) DEFAULT null;");
        });
    }
}
