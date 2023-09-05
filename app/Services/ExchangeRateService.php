<?php

namespace App\Services;

use App\Repositories\ExchangeRateRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseCodes;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    private string $apiUrl;
    private ExchangeRateRepository $exchangeRateRepository;

    public function __construct(ExchangeRateRepository $exchangeRateRepository)
    {
        $this->apiUrl = config('ecb-path.base_api_path') . 'EXR/D.'
            . config('ecb-path.currency') . '.'
            . config('ecb-path.currency_denominator') . '.'
            . config('ecb-path.exchange_rate_type') . '.'
            . config('ecb-path.exchange_rate_context');

        $this->exchangeRateRepository = $exchangeRateRepository;
    }

    private function callEcbApi(array $params = []): Response
    {
        return Http::get($this->apiUrl, $params);
    }

    public function getLocalEXRData(): Collection
    {
        return $this->exchangeRateRepository->getAll();
    }

    public function getLatestEXR(): array|bool
    {
        $response = $this->callEcbApi(config('ecb-path.update-params'));

        if ($response->status() === ResponseCodes::HTTP_OK) {
            return $this->parseExrData($response->body());
        }

        return false;
    }

    public function initEXR(): string
    {
        return $this->callEcbApi(config('ecb-path.init-params'));
    }

    public function getIntervalOfExchangeRates(string $from, string $to): void
    {
        $startPeriod = Carbon::parse($from);
        $endPeriod = Carbon::parse($to);

        $response = $this->callEcbApi([
            'detail' => 'dataonly',
            'format' => 'jsondata',
            'startPeriod' => $startPeriod->toDateString(),
            'endPeriod' => $endPeriod->toDateString()
        ]);

        $values = $this->parseExrData($response->body());

        $period = CarbonPeriod::create(array_keys($values)[0], $endPeriod);
        $currentValue = array_values($values)[0];

        foreach ($period as $date) {
            $currentDate = $date->format('Y-m-d');
            if (array_key_exists($currentDate, $values)) {
                $currentValue = $values[$currentDate];
            }

            $this->exchangeRateRepository->addValue($currentValue, $currentDate);
        }
    }

    private function parseExrData(string $responseBody): array
    {
        $data = json_decode($responseBody, true);

        $values = $data['dataSets'][0]['series']["0:0:0:0:0"]["observations"];
        $dates = $data['structure']['dimensions']['observation'][0]['values'];
        $observations = [];
        $i = 0;

        foreach ($values as $observation) {
            $observations[$dates[$i]['id']] = $observation[0];
            $i++;
        }

        return $observations;
    }
}
