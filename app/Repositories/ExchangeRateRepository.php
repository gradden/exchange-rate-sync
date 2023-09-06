<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExchangeRateRepository
{
    public function updateOrCreate(string|null $value, string $date): void
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

    public function getLastElement(array $columns): ExchangeRate|null
    {
        return ExchangeRate::select($columns)
            ->orderBy('exchange_rate_date', 'desc')
            ->first();
    }
}
