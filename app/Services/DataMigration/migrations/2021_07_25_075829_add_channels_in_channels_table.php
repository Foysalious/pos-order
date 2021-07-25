<?php namespace App\Services\DataMigration\migrations;

use Illuminate\Support\Facades\DB;

class AddChannelsInChannelsTable extends DataMigrationBase implements DataMigrationInterface
{
    /**
     * Run the migrations.
     *
     * @return void | string
     */
    public function handle()
    {
        $data = [
            [
                'id' => 1,
                'name' => 'pos'
            ],
            [
                'id' => 2,
                'name' => 'webstore'
            ]
        ];
        foreach ($data as $channel) {
            DB::table('channels')->insert($this->withCreateModificationField($channel));
        }
        dump('channels data migrated successfully');
    }
}
