<?php namespace App\Http\Controllers;


use App\Http\Requests\DataMigrationRequest;
use App\Services\DataMigration\DataMigrationService;

class DataMigrationController extends Controller
{
    private DataMigrationService $dataMigrationService;

    public function store(DataMigrationRequest $request, $partner_id)
    {

    }

}
