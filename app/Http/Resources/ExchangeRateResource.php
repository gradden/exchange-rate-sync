<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->resource->exchange_rate_date,
            'observation' => $this->resource->value,
            'refreshedAt' => $this->resource->refreshed_at
        ];
    }
}
