import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;
document.addEventListener('DOMContentLoaded', () => {
    document.dispatchEvent(new CustomEvent('chart-ready'));
});
