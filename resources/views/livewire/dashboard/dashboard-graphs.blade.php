<div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card bg-white text-black">
                <div class="card-header">
                    <h5 class="mb-0">Chiffre d'affaire par jour</h5>
                </div>
                <div class="card-body" style="height: 370px;">
                    <canvas id="revenueChart" style="max-height: 360px;"></canvas>
                    <div id="revenueNoData" class="text-center mt-3" style="display: none;">
                        Aucune donnée de chiffre d'affaire pour cette période.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card bg-white text-black">
                <div class="card-header">
                    <h5 class="mb-0">Nombre de commandes par jour</h5>
                </div>
                <div class="card-body" style="height: 370px;">
                    <canvas id="ordersChart" style="max-height: 360px;"></canvas>
                    <div id="ordersNoData" class="text-center mt-3" style="display: none;">
                        Aucune donnée de commandes pour cette période.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- chartjs js -->
    <script src="{{asset('assets/vendor/chartjs/chart.js')}}"></script>

    @script
        <script>
            window.revenueChartInstance = null;
            window.ordersChartInstance = null;

            function destroyChart(chart) {
                if (chart) {
                    chart.destroy();
                }
            }

            function renderChart(chartId, chartType, data, options, noDataElementId) {
                const ctx = document.getElementById(chartId);
                const noDataElement = document.getElementById(noDataElementId);

                if (!ctx) {
                    console.error(`Canvas element with ID '${chartId}' not found.`);
                    return;
                }

                if (data.datasets[0].data.length === 0 || data.labels.length === 0) {
                    ctx.style.display = 'none';
                    if (noDataElement) noDataElement.style.display = 'block';
                    return null;
                } else {
                    ctx.style.display = 'block';
                    if (noDataElement) noDataElement.style.display = 'none';
                }

                if (chartId === 'revenueChart') {
                    destroyChart(window.revenueChartInstance);
                    window.revenueChartInstance = new Chart(ctx, { type: chartType, data: data, options: options });
                    return window.revenueChartInstance;
                } else if (chartId === 'ordersChart') {
                    destroyChart(window.ordersChartInstance);
                    window.ordersChartInstance = new Chart(ctx, { type: chartType, data: data, options: options });
                    return window.ordersChartInstance;
                }
            }

            const commonChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                if (value >= 1000) {
                                    return value / 1000 + 'k';
                                }
                                return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            };

            $wire.on('update-dashboard-charts', ({ ordersData, revenueData }) => {
                renderChart('revenueChart', 'line', revenueData, {
                    ...commonChartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return value.toLocaleString('fr-FR') + ' {{ \App\Enums\Currency::make(config('countries.default_currency', 'XAF'))->label }}';
                                }
                            }
                        }
                    }
                }, 'revenueNoData');

                renderChart('ordersChart', 'bar', ordersData, commonChartOptions, 'ordersNoData');
            });

            const initialOrdersData = $wire.ordersGraphData;
            const initialRevenueData = $wire.revenueGraphData;

            renderChart('revenueChart', 'line', initialRevenueData, {
                ...commonChartOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value.toLocaleString('fr-FR') + ' {{ \App\Enums\Currency::make(config('countries.default_currency', 'XAF'))->label }}';
                            }
                        }
                    }
                }
            }, 'revenueNoData');

            renderChart('ordersChart', 'bar', initialOrdersData, commonChartOptions, 'ordersNoData');
        </script>
    @endscript

</div>