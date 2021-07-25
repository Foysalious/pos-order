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
        $channels = [
            [
                'id' => 1,
                'name' => 'pos'
            ],
            [
                'id' => 2,
                'name' => 'webstore'
            ]
        ];
        DB::table('channels')->insert($channels);
        dump('channels data migrated successfully');
    }
}
