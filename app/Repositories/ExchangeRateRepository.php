<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use Illuminate\Support\Collection;

class ExchangeRateRepository
{
    public function addValue(string $value, string $date): void
    {
        ExchangeRate::updateOrCreate(['exchange_rate_date' => $date], ['value' => $value]);
    }

    public function getAll(string $orderBy = 'exchange_rate_date', string $direction = 'desc'): Collection
    {
        return ExchangeRate::orderBy($orderBy, $direction)->get();
    }
}
