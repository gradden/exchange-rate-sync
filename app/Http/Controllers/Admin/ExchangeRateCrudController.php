<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ExchangeRateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class ExchangeRateCrudController extends CrudController
{
    use ListOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\ExchangeRate::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/exchange-rate');
        CRUD::setEntityNameStrings('exchange rate', 'exchange rates');
    }

    protected function setupListOperation(): void
    {
        CRUD::setFromDb(); // set columns from db columns.
        $this->crud->orderBy('exchange_rate_date', 'desc');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(ExchangeRateRequest::class);
        CRUD::setFromDb(); // set fields from db columns.
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
