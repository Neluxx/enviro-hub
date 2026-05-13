document.addEventListener('alpine:init', () => {
    Alpine.data('chartDashboard', (initialData) => {
        let chart = null;
        let labels = [];
        let series = { temperature: [], humidity: [], carbon_dioxide: [] };

        const metrics = [
            {
                key: 'temperature', label: 'Temperature', unit: '°C', digits: 1,
                color: '#f87171', textClass: 'text-red-400', dotClass: 'bg-red-400',
            },
            {
                key: 'humidity', label: 'Humidity', unit: '%', digits: 1,
                color: '#60a5fa', textClass: 'text-blue-400', dotClass: 'bg-blue-400',
            },
            {
                key: 'carbon_dioxide', label: 'CO₂', unit: 'ppm', digits: 0,
                color: '#4ade80', textClass: 'text-green-400', dotClass: 'bg-green-400',
            },
        ];

        const format = (value, digits) => {
            if (value === null || value === undefined) return '–';
            return Number(value).toFixed(digits);
        };

        const buildGradient = (canvas, color) => {
            const ctx = canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, canvas.clientHeight);
            gradient.addColorStop(0, color + '40');
            gradient.addColorStop(1, color + '00');
            return gradient;
        };

        return {
            hasData: false,
            selectedMetric: 'temperature',
            latest: { temperature: '–', humidity: '–', carbon_dioxide: '–' },
            metrics,

            get currentMetric() {
                return metrics.find((m) => m.key === this.selectedMetric);
            },

            init() {
                this.applyData(initialData);
                this.$wire.on('chart-updated', ({ chartData }) => this.applyData(chartData));
            },

            applyData(data) {
                this.hasData = data && data.labels && data.labels.length > 0;

                if (!this.hasData) {
                    this.destroyChart();
                    this.latest = { temperature: '–', humidity: '–', carbon_dioxide: '–' };
                    return;
                }

                labels = data.labels;
                series = {
                    temperature: data.temperature,
                    humidity: data.humidity,
                    carbon_dioxide: data.carbon_dioxide,
                };

                this.latest = {
                    temperature: format(data.temperature.at(-1), 1),
                    humidity: format(data.humidity.at(-1), 1),
                    carbon_dioxide: format(data.carbon_dioxide.at(-1), 0),
                };

                this.$nextTick(() => this.syncChart());
            },

            select(key) {
                if (this.selectedMetric === key) return;
                this.selectedMetric = key;
                this.syncChart();
            },

            syncChart() {
                const metric = metrics.find((m) => m.key === this.selectedMetric);
                const dataset = series[metric.key] ?? [];

                if (!chart) {
                    this.createChart(metric, dataset);
                    return;
                }

                const ds = chart.data.datasets[0];
                chart.data.labels = labels;
                ds.label = metric.label;
                ds.data = dataset;
                ds.borderColor = metric.color;
                ds.backgroundColor = buildGradient(this.$refs.chart, metric.color);
                ds.pointHoverBackgroundColor = metric.color;
                chart.update();
            },

            createChart(metric, dataset) {
                const getMetric = () => metrics.find((m) => m.key === this.selectedMetric);

                chart = new Chart(this.$refs.chart, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: metric.label,
                            data: dataset,
                            borderColor: metric.color,
                            backgroundColor: buildGradient(this.$refs.chart, metric.color),
                            borderWidth: 1.5,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: metric.color,
                            pointHoverBorderColor: '#09090b',
                            pointHoverBorderWidth: 2,
                            tension: 0.25,
                            fill: true,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                border: { display: false },
                                ticks: {
                                    maxTicksLimit: 8,
                                    color: 'rgba(244, 244, 245, 0.4)',
                                    font: { size: 11 },
                                },
                                grid: { color: 'rgba(244, 244, 245, 0.04)' },
                            },
                            y: {
                                border: { display: false },
                                ticks: {
                                    color: 'rgba(244, 244, 245, 0.4)',
                                    font: { size: 11 },
                                    maxTicksLimit: 6,
                                },
                                grid: { color: 'rgba(244, 244, 245, 0.06)' },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#09090b',
                                borderColor: '#27272a',
                                borderWidth: 1,
                                titleColor: '#a1a1aa',
                                bodyColor: '#fafafa',
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: (ctx) => {
                                        const m = getMetric();
                                        return `${format(ctx.parsed.y, m.digits)} ${m.unit}`;
                                    },
                                },
                            },
                        },
                        interaction: { intersect: false, mode: 'index' },
                    },
                });
            },

            destroyChart() {
                if (chart) {
                    chart.destroy();
                    chart = null;
                }
            },
        };
    });
});
