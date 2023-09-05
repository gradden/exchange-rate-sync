<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExchangeRateResource;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

class ExchangeRateController extends Controller
{
    private ExchangeRateService $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * @OA\Get(
     *   tags={"ExchangeRates"},
     *   path="/exchange-rates",
     *   summary="Get local EXR datas",
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Server error"),
     * )
     */
    public function index(): JsonResponse
    {
        return $this->json(ExchangeRateResource::collection($this->exchangeRateService->getLocalEXRData()));
    }

    /**
     * @OA\Get(
     *   tags={"ExchangeRates"},
     *   path="/exchange-rates/current",
     *   summary="Get the current, locally stored EXR",
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=500, description="Server error"),
     * )
     */
    public function show(): JsonResponse
    {
        return $this->json(ExchangeRateResource::make($this->exchangeRateService->showLatestEXR()));
    }
}
