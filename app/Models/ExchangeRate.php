<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'value',
        'exchange_rate_date',
        'refreshed_at',
    ];

    public function getTimezoneRefreshDate(): string
    {
        return Carbon::parse($this->refreshed_at)->setTimezone('Europe/Budapest')->toAtomString();
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value.' '.config('ecb.currency')
        );
    }
}
