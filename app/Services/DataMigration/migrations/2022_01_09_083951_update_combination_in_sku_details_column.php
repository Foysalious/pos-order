<?php namespace App\Services\DataMigration\migrations;

use App\Models\OrderSku;

class UpdateCombinationInSkuDetailsColumn extends DataMigrationBase implements DataMigrationInterface
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function handle()
    {
        OrderSku::whereNotNull('details')->chunk(500, function ($orderSkus) {
           foreach ($orderSkus as $orderSku) {
               $details = json_decode($orderSku->details);
               $orderSku->update(['details' => json_encode(['combination' => $details])]);
               dump($orderSku->id . " updated");
           }
            dump("Done");
        });
    }
}
