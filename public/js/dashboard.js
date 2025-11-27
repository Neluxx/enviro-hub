
/**
 * Dashboard Chart Manager
 * Handles initialization and updates of environmental data charts
 */
class DashboardChartManager {
    constructor() {
        this.charts = {
            temperature: null,
            humidity: null,
            co2: null
        };
        this.currentRange = 'today';
        this.chartConfigs = {
            temperature: {
                label: 'Temperature (°C)',
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                annotations: {
                    line1: {
                        type: 'line',
                        yMin: 18,
                        yMax: 18,
                        borderColor: 'rgba(75, 192, 192, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    },
                    line2: {
                        type: 'line',
                        yMin: 24,
                        yMax: 24,
                        borderColor: 'rgba(75, 192, 192, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }
                }
            },
            humidity: {
                label: 'Humidity (%)',
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                annotations: {
                    line1: {
                        type: 'line',
                        yMin: 40,
                        yMax: 40,
                        borderColor: 'rgba(75, 192, 192, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    },
                    line2: {
                        type: 'line',
                        yMin: 60,
                        yMax: 60,
                        borderColor: 'rgba(75, 192, 192, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }
                }
            },
            co2: {
                label: 'CO₂ (ppm)',
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                annotations: {
                    line1: {
                        type: 'line',
                        yMin: 1000,
                        yMax: 1000,
                        borderColor: 'rgba(255, 127, 0, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    },
                    line2: {
                        type: 'line',
                        yMin: 2000,
                        yMax: 2000,
                        borderColor: 'rgba(255, 0, 0, 0.8)',
                        borderWidth: 1,
                        borderDash: [5, 5]
                    }
                }
            }
        };
    }

    /**
     * Create a chart instance
     */
    createChart(canvasId, label, borderColor, backgroundColor, annotations = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        const isMobile = window.innerWidth < 768;

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: label,
                    data: [],
                    borderColor: borderColor,
                    backgroundColor: backgroundColor,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                    spanGaps: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: false,
                    decimation: {
                        enabled: true,
                        algorithm: 'lttb',
                        samples: isMobile ? 50 : 100
                    },
                    annotation: {
                        annotations: annotations
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: false,
                        ticks: {
                            maxTicksLimit: isMobile ? 7 : 14,
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 0,
                            color: '#adb5bd',
                            font: {
                                size: isMobile ? 9 : 11
                            },
                            padding: isMobile ? 2 : 5
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    y: {
                        display: true,
                        title: false,
                        ticks: {
                            color: '#adb5bd',
                            font: {
                                size: isMobile ? 9 : 11
                            },
                            maxTicksLimit: isMobile ? 5 : 8,
                            padding: isMobile ? 2 : 5
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    }
                }
            }
        });
    }

    /**
     * Initialize all charts
     */
    initCharts() {
        this.charts.temperature = this.createChart(
            'temperatureChart',
            this.chartConfigs.temperature.label,
            this.chartConfigs.temperature.borderColor,
            this.chartConfigs.temperature.backgroundColor,
            this.chartConfigs.temperature.annotations
        );

        this.charts.humidity = this.createChart(
            'humidityChart',
            this.chartConfigs.humidity.label,
            this.chartConfigs.humidity.borderColor,
            this.chartConfigs.humidity.backgroundColor,
            this.chartConfigs.humidity.annotations
        );

        this.charts.co2 = this.createChart(
            'co2Chart',
            this.chartConfigs.co2.label,
            this.chartConfigs.co2.borderColor,
            this.chartConfigs.co2.backgroundColor,
            this.chartConfigs.co2.annotations
        );
    }

    /**
     * Update all charts with new data
     */
    updateCharts(data) {
        // Update temperature chart
        this.charts.temperature.data.labels = data.labels;
        this.charts.temperature.data.datasets[0].data = data.temperature;
        this.charts.temperature.update('none');

        // Update humidity chart
        this.charts.humidity.data.labels = data.labels;
        this.charts.humidity.data.datasets[0].data = data.humidity;
        this.charts.humidity.update('none');

        // Update CO2 chart
        this.charts.co2.data.labels = data.labels;
        this.charts.co2.data.datasets[0].data = data.co2;
        this.charts.co2.update('none');
    }

    /**
     * Load chart data from API
     */
    async loadChartData(range) {
        try {
            const response = await fetch(`/api/environmental-data/chart/${range}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            this.updateCharts(data);
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    /**
     * Setup time range button event listeners
     */
    setupTimeRangeButtons() {
        const buttons = document.querySelectorAll('.time-range-buttons button');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Load new data
                this.currentRange = button.dataset.range;
                this.loadChartData(this.currentRange);
            });
        });
    }

    /**
     * Initialize the dashboard
     */
    init() {
        this.initCharts();
        this.loadChartData(this.currentRange);
        this.setupTimeRangeButtons();
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new DashboardChartManager();
    dashboard.init();
});
