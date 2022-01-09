<?php

use App\Services\EventNotification\Events;
use App\Services\EventNotification\Services;
use App\Services\EventNotification\Statuses;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained()
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->enum('service', Services::get())->index();
            $table->enum('event', Events::get())->index();
            $table->enum('status', Statuses::get())->default(Statuses::PENDING)->index();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_notifications');
    }
}
