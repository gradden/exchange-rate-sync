<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;

class ExchangeRateRepository
{
    public function updateOrCreate(?string $value, string $date): void
    {
        ExchangeRate::updateOrCreate(
            [
                'exchange_rate_date' => $date,
            ],
            [
                'value' => $value,
                'refreshed_at' => Carbon::now()->toDateTimeString(),
            ]
        );
    }

    public function getLastElement(array $columns): ?ExchangeRate
    {
        return ExchangeRate::select($columns)
            ->orderBy('exchange_rate_date', 'desc')
            ->first();
    }

    public function getCurrent(): ?ExchangeRate
    {
        return ExchangeRate::select(['exchange_rate_date', 'value', 'refreshed_at'])
            ->where('exchange_rate_date', '=', Carbon::now()->toDateString())
            ?->first();
    }
}
