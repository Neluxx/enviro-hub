import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button'];

    connect() {
        this.charts = {};
    }

    /**
     * This is called automatically when each chart initializes
     * thanks to the 'chartjs:connect' action in Twig.
     */
    onChartConnect(event) {
        const id = event.target.id;
        // The event contains the chart instance in detail.chart
        this.charts[id] = event.detail.chart;
    }

    async changeRange(event) {
        const range = event.currentTarget.dataset.range;

        // 1. Update UI (active buttons)
        this.buttonTargets.forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');

        try {
            // 2. Fetch new data from your existing API
            const response = await fetch(`/api/environmental-data/chart/${range}`);
            const data = await response.json();

            // 3. Update the charts
            // Symfony UX stores the Chart.js instance on the element
            this.updateChart('temperatureChart', data.labels, data.temperature);
            this.updateChart('humidityChart', data.labels, data.humidity);
            this.updateChart('co2Chart', data.labels, data.co2);

        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    updateChart(id, labels, newData) {
        const chart = this.charts[id];
        if (chart) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = newData;
            chart.update();
        } else {
            console.warn(`Chart ${id} not registered yet.`);
        }
    }
}
