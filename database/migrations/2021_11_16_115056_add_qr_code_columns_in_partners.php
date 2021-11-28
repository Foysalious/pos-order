<?php

use App\Services\Partner\Constants\QrAccountTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCodeColumnsInPartners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $account_type = QrAccountTypes::get();
            $table->enum('qr_code_account_type', $account_type)->after('delivery_charge')->nullable();
            $table->string('qr_code_image')->after('qr_code_account_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('qr_code_account_type');
            $table->dropColumn('qr_code_image');
        });
    }
}
