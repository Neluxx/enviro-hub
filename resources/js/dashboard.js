document.addEventListener('alpine:init', () => {
    Alpine.data('chartDashboard', (initialData) => ({
        charts: {},
        hasData: false,
        latestTemp: '',
        latestHumidity: '',
        latestCo2: '',

        init() {
            this.applyData(initialData);
            this.$wire.on('chart-updated', ({ chartData }) => this.applyData(chartData));
        },

        applyData(data) {
            this.hasData = data && data.labels && data.labels.length > 0;

            if (!this.hasData) {
                this.destroyCharts();
                return;
            }

            this.latestTemp = data.temperature[data.temperature.length - 1];
            this.latestHumidity = data.humidity[data.humidity.length - 1];
            this.latestCo2 = data.carbon_dioxide[data.carbon_dioxide.length - 1];

            // Give Alpine a tick to show the containers before Chart.js measures them
            this.$nextTick(() => this.createCharts(data));
        },

        createCharts(data) {
            this.destroyCharts();

            const gridColor = 'rgba(255, 255, 255, 0.08)';
            const tickColor = 'rgba(255, 255, 255, 0.5)';

            const sharedOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { maxTicksLimit: 10, maxRotation: 45, color: tickColor },
                        grid: { color: gridColor },
                    },
                    y: {
                        beginAtZero: false,
                        ticks: { color: tickColor },
                        grid: { color: gridColor },
                    },
                },
                plugins: {
                    legend: { display: false },
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            };

            this.charts.temperature = new Chart(this.$refs.temperatureChart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Temperature (°C)',
                        data: data.temperature,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56, 189, 248, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    }],
                },
                options: sharedOptions,
            });

            this.charts.humidity = new Chart(this.$refs.humidityChart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Humidity (%)',
                        data: data.humidity,
                        borderColor: '#a78bfa',
                        backgroundColor: 'rgba(167, 139, 250, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    }],
                },
                options: sharedOptions,
            });

            this.charts.co2 = new Chart(this.$refs.co2Chart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'CO₂ (ppm)',
                        data: data.carbon_dioxide,
                        borderColor: '#34d399',
                        backgroundColor: 'rgba(52, 211, 153, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    }],
                },
                options: sharedOptions,
            });
        },

        destroyCharts() {
            Object.values(this.charts).forEach(chart => chart.destroy());
            this.charts = {};
        },
    }));
});