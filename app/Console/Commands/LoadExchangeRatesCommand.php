<?php

namespace App\Console\Commands;

use App\Exceptions\SyncExchangeRateException;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
            $now = Carbon::now()->toDateString();
            $from = $this->option('from');
            $to = $this->option('to');

            if ($from > $now || $to > $now)
            {
                throw new SyncExchangeRateException(__('errors.date_limit_reached'));
            }

            $this->exchangeRateService->getIntervalOfExchangeRates(
                $this->option('from'),
                $this->option('to')
            );
        } catch (Exception $e) {
            Log::info('Sync Exception: {errorMsg} : {file} in {line}', [
                'errorMsg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw new SyncExchangeRateException();
        }

        $this->info('Sync was successful!');

        return self::SUCCESS;
    }
}
