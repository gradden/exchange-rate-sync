<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'exchange_rate_date',
        'refreshed_at'
    ];

    public function getTimezoneRefreshDate(): string
    {
        return Carbon::parse($this->refreshed_at)->setTimezone('Europe/Budapest')->toAtomString();
    }
}
