<?php

namespace App\Services\Dashboard;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\Carbon;

class DashboardGraphService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Récupère les données de chiffre d'affaires par jour pour une période et un/des centre(s) de distribution donnés.
     *
     * @param  string  $startDate  La date de début de la période (format Y-m-d).
     * @param  string  $endDate  La date de fin de la période (format Y-m-d).
     * @param  string|array|null  $distributionCenterId  L'ID du centre de distribution, un tableau d'IDs, ou null pour tous.
     * @return object Un objet contenant les 'labels' (dates formatées) et 'data' (revenus par jour).
     */
    public function getRevenueByDay(string $startDate, string $endDate, string|array|null $distributionCenterId = null): object
    {
        $results = $this->orderRepository->getAggregatedOrdersByDay(
            $startDate,
            $endDate,
            $distributionCenterId,
            'total_amount',
            'SUM'
        );

        return $this->formatGraphData($startDate, $endDate, $results, 'float');
    }

    /**
     * Récupère le nombre de commandes par jour pour une période et un/des centre(s) de distribution donnés.
     *
     * @param  string  $startDate  La date de début de la période (format Y-m-d).
     * @param  string  $endDate  La date de fin de la période (format Y-m-d).
     * @param  string|array|null  $distributionCenterId  L'ID du centre de distribution, un tableau d'IDs, ou null pour tous.
     * @return object Un objet contenant les 'labels' (dates formatées) et 'data' (nombre de commandes par jour).
     */
    public function getOrdersByDay(string $startDate, string $endDate, string|array|null $distributionCenterId = null): object
    {
        $results = $this->orderRepository->getAggregatedOrdersByDay(
            $startDate,
            $endDate,
            $distributionCenterId,
            '*',
            'COUNT'
        );

        return $this->formatGraphData($startDate, $endDate, $results, 'int');
    }

    /**
     * Formate les résultats bruts du repository pour les graphiques.
     *
     * @param  string  $valueType  'int' ou 'float' pour caster la valeur agrégée.
     */
    private function formatGraphData(string $startDate, string $endDate, \Illuminate\Support\Collection $results, string $valueType = 'float'): object
    {
        $labels = [];
        $data = [];

        $period = Carbon::parse($startDate)->toPeriod(Carbon::parse($endDate));
        $dateMap = $results->keyBy('date');

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $value = $dateMap->has($formattedDate) ? $dateMap[$formattedDate]->value_total : 0;

            $data[] = ($valueType === 'int') ? (int) $value : (float) $value;
        }

        return (object) ['labels' => $labels, 'data' => $data];
    }
}
