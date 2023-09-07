<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ExchangeRateRequest;
use App\Repositories\ExchangeRateRepository;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Carbon\Carbon;

class ExchangeRateCrudController extends CrudController
{
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;

    private ExchangeRateRepository $exchangeRateRepository;

    public function __construct(ExchangeRateRepository $exchangeRateRepository)
    {
        $this->exchangeRateRepository = $exchangeRateRepository;

        parent::__construct();
    }

    public function setup(): void
    {
        CRUD::setModel(\App\Models\ExchangeRate::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/exchange-rate');
        CRUD::setEntityNameStrings('exchange rate', 'exchange rates');
    }

    protected function setupListOperation(): void
    {
        $current = $this->exchangeRateRepository->getCurrent();
        CRUD::setFromDb(); // set columns from db columns.
        $this->crud->orderBy('exchange_rate_date', 'desc');
        $this->crud->modifyColumn('exchange_rate_date', [
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('exchange_rate_date', 'like', '%' . $searchTerm . '%');
            }
        ]);

        $this->currentWidget(
            $current->value ?? null,
            Carbon::parse($current->refreshed_at ?? null)->toTimeString() .
            '<br>(' . Carbon::parse($current->refreshed_at ?? null)->toFormattedDateString() . ')'
        );
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

    private function currentWidget(?string $value, ?string $refreshDate): void
    {
        if (!empty($value) && !empty($refreshDate)) {
            Widget::add()->to('before_content')->type('div')->class('row align-items-center')->content([
                Widget::make(
                    [
                        'type' => 'card',
                        'class' => 'card border-0 text-white bg-primary',
                        'wrapper' => ['class' => 'col-sm-3 col-md-3 text-center'],
                        'content' => [
                            'header' => __('frontend.current_exchange_rate'),
                            'body' => '<h2>' . $value . '</h2> ' . __('frontend.last_updated')
                                . ': <br>' . $refreshDate,
                        ]
                    ]
                ),
            ]);
        }
    }
}
