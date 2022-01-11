<?php namespace App\Services\DataMigration\migrations;

use App\Models\Partner;

class RemoveRestrictedCharactersFromSubDomain extends DataMigrationBase implements DataMigrationInterface
{
    /**
     * Run the migrations.
     *
     * @return void | string
     */
    public function handle()
    {
        Partner::query()->where('sub_domain','like','%/%')
            ->orWhere('sub_domain','like','% %')
            ->orWhere('sub_domain','like','%?%')
            ->chunk(100,function ($partners) {
                foreach ($partners as $partner) {
                    $partner_sub_domain = $partner->sub_domain;
                    $sub_domain = str_replace(['/', ' ', '?'], '', strtolower($partner_sub_domain));
                    $partner->update(['sub_domain' => $sub_domain]);
                    dump("Partner " . $partner->id . " - Sub Domain Updated. Old: " . $partner_sub_domain . "New: " . $sub_domain);
                }
            });
    }
}
