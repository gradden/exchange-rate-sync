<?php

namespace App\Services;

use App\Repositories\ExchangeRateRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as ResponseCodes;

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
        return Http::get(
            $this->apiUrl,
            array_merge(['detail' => 'dataonly', 'format' => 'jsondata'], $params)
        );
    }

    public function getLatestEXR(): void
    {
        $lastElement = $this->exchangeRateRepository->getLastElement(['exchange_rate_date', 'value']) ?? null;

        $params = (!empty($lastElement) && $lastElement->exchange_rate_date < Carbon::now()->toDateString()) ?
            ['startPeriod' => $lastElement->exchange_rate_date, 'endPeriod' => Carbon::now()->toDateString()] :
            config('ecb.update-params');

        $response = $this->callEcbApi($params);

        if ($response->status() === ResponseCodes::HTTP_OK) {
            $newValues = ExchangeRateService::parseEXRData($response->body());
            $this->syncToDB(
                $lastElement->exchange_rate_date ?? Carbon::now()->toDateString(),
                Carbon::now()->toDateString(),
                $lastElement->getRawOriginal('value') ?? null,
                $newValues
            );
        }
    }

    public function getIntervalOfExchangeRates(string $from, string $to): void
    {
        $startPeriod = Carbon::parse($from);
        $endPeriod = Carbon::parse($to);

        $response = $this->callEcbApi([
            'startPeriod' => $startPeriod->toDateString(),
            'endPeriod' => $endPeriod->toDateString(),
        ]);

        $values = $this->parseEXRData($response->body());

        $this->syncToDB(array_keys($values)[0], $endPeriod, array_values($values)[0], $values);
    }

    private static function parseEXRData(string $responseBody): array
    {
        $data = json_decode($responseBody, true);

        $values = $data['dataSets'][0]['series']['0:0:0:0:0']['observations'];
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
        string  $startPeriod,
        string  $endPeriod,
        ?string $currentValue,
        array   $newValues
    ): void {
        $period = CarbonPeriod::create($startPeriod, $endPeriod);

        try {
            DB::beginTransaction();

            foreach ($period as $date) {
                $currentDate = $date->format('Y-m-d');
                if (array_key_exists($currentDate, $newValues)) {
                    $currentValue = $newValues[$currentDate];
                }

                $this->exchangeRateRepository->updateOrCreate($currentValue, $currentDate);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
