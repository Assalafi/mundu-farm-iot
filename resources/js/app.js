import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        document.dispatchEvent(new CustomEvent('chart-ready'));
    });
} else {
    document.dispatchEvent(new CustomEvent('chart-ready'));
}
