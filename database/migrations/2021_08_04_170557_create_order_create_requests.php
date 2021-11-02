<?php

use App\Services\Order\Constants\PortalNames;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCreateRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('route')->nullable();
            $table->enum('portal_name', PortalNames::get())->nullable();
            $table->string('portal_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('api_request_id')->after('closed_and_paid_at')->nullable()->unsigned();
            $table->foreign('api_request_id')->references('id')->on('api_requests')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['api_request_id']);
            $table->dropColumn('api_request_id');
        });
        Schema::dropIfExists('api_requests');
    }
}
