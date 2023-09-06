<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExchangeRateRepository
{
    public function addValue(string|null $value, string $date): void
    {
        ExchangeRate::updateOrCreate(
            [
                'exchange_rate_date' => $date
            ],
            [
                'value' => $value,
                'refreshed_at' => Carbon::now()->toAtomString()
            ]
        );
    }

    public function getAll(string $orderBy = 'exchange_rate_date', string $direction = 'desc'): Collection
    {
        return ExchangeRate::orderBy($orderBy, $direction)->get();
    }

    public function getLastElement(array $columns): ExchangeRate|null
    {
        return ExchangeRate::select($columns)
            ->orderBy('exchange_rate_date', 'desc')->first();
    }

    public function updateActual(string $value): void
    {
        ExchangeRate::updateOrCreate(
            [
                'exchange_rate_date' => Carbon::now()->toDateString()
            ],
            [
                'value' => $value,
                'refreshed_at' => Carbon::now()->toDateTimeString()
            ]
        );
    }
}
