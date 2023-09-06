<?php

namespace App\Console\Commands;

use App\Exceptions\SyncExchangeRateException;
use App\Services\ExchangeRateService;
use Exception;
use Illuminate\Console\Command;

class LoadExchangeRatesCommand extends Command
{
    private ExchangeRateService $exchangeRateService;

    protected $signature = 'exr:load {--from=} {--to=}';
    protected $description = 'Load exchange rates in date interval to DB';

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        parent::__construct();
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * @throws SyncExchangeRateException
     */
    public function handle(): int
    {
        try {
            $this->exchangeRateService->getIntervalOfExchangeRates(
                $this->option('from'),
                $this->option('to')
            );

        } catch (Exception $e) {
            throw new SyncExchangeRateException();
        }

        $this->info('Sync was successful!');

        return self::SUCCESS;
    }
}
