<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Repositories\ExchangeRateRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseCodes;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    private string $apiUrl;
    private ExchangeRateRepository $exchangeRateRepository;

    public function __construct(ExchangeRateRepository $exchangeRateRepository)
    {
        $this->apiUrl = config('ecb.base_api_path') . 'EXR/D.'
            . config('ecb.currency') . '.'
            . config('ecb.currency_denominator') . '.'
            . config('ecb.exchange_rate_type') . '.'
            . config('ecb.exchange_rate_context');

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

    public function showLatestEXR(): ExchangeRate|null
    {
        return $this->exchangeRateRepository->getLastElement(['value', 'exchange_rate_date', 'refreshed_at']);
    }

    public function getLatestEXR(): array|bool
    {
        $lastElement = $this->exchangeRateRepository->getLastElement(['exchange_rate_date', 'value']) ?? null;
        $params =
            (!empty($lastElement) && $lastElement->exchange_rate_date < Carbon::now()->toDateString()) ?
                [
                    'detail' => 'dataonly',
                    'format' => 'jsondata',
                    'startPeriod' => $lastElement->exchange_rate_date,
                    'endPeriod' => Carbon::now()->toDateString()
                ] : config('ecb.update-params');

        $response = $this->callEcbApi($params);

        if ($response->status() === ResponseCodes::HTTP_OK) {
            $newValues = ExchangeRateService::parseEXRData($response->body());
            $this->syncToDB(
                $lastElement->exchange_rate_date ?? Carbon::now()->toDateString(),
                Carbon::now()->toDateString(),
                $lastElement->value ?? null,
                $newValues
            );
        }

        return false;
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

        $values = $this->parseEXRData($response->body());

        $this->syncToDB(array_keys($values)[0], $endPeriod, array_values($values)[0], $values);

    }

    private static function parseEXRData(string $responseBody): array
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

    private function syncToDB(
        string $startPeriod,
        string $endPeriod,
        string|null $currentValue,
        array $responseData
    ): void {
        $period = CarbonPeriod::create($startPeriod, $endPeriod);

        try {
            DB::beginTransaction();

            foreach ($period as $date) {
                $currentDate = $date->format('Y-m-d');
                if (array_key_exists($currentDate, $responseData)) {
                    $currentValue = $responseData[$currentDate];
                }

                $this->exchangeRateRepository->addValue($currentValue, $currentDate);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
