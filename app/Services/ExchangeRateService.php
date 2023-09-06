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
        $response = $this->callEcbApi(config('ecb.update-params'));

        if ($response->status() === ResponseCodes::HTTP_OK) {
            $newValue = ExchangeRateService::parseEXRData($response->body());
            $this->exchangeRateRepository->updateActual(array_values($newValue)[0]);
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

        $period = CarbonPeriod::create(array_keys($values)[0], $endPeriod);
        $currentValue = array_values($values)[0];

        try{
            DB::beginTransaction();

            foreach ($period as $date) {
                $currentDate = $date->format('Y-m-d');
                if (array_key_exists($currentDate, $values)) {
                    $currentValue = $values[$currentDate];
                }

                $this->exchangeRateRepository->addValue($currentValue, $currentDate);
                DB::commit();
            }


        } catch (Exception $e) {
            var_dump($e->getMessage());
            DB::rollBack();
        }
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
}
