
/* ==========================================
   PITO HOTEL - OWNER DASHBOARD
   Monthly Revenue & Payment Methods Charts
========================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ======================================
    // GET DASHBOARD DATA FROM LARAVEL
    // ======================================

    const dataElement = document.getElementById('ownerDashboardData');

    if (!dataElement) {
        console.error('PITO HOTEL: Dashboard data not found.');
        return;
    }

    if (typeof Chart === 'undefined') {
        console.error('PITO HOTEL: Chart.js is not loaded.');
        return;
    }

    let dashboardData;

    try {
        dashboardData = JSON.parse(dataElement.textContent);
    } catch (error) {
        console.error('PITO HOTEL: Invalid chart data.', error);
        return;
    }

    const {
        monthLabels = [],
        monthRevenue = [],
        paymentLabels = [],
        paymentAmounts = []
    } = dashboardData;

    // ======================================
    // MONTHLY REVENUE BAR CHART
    // ======================================

    const revenueCanvas = document.getElementById('revenueChart');

    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'bar',

            data: {
                labels: monthLabels,

                datasets: [{
                    label: 'Paid Revenue ($)',
                    data: monthRevenue,
                    backgroundColor: '#198754',
                    borderRadius: 8,
                    maxBarThickness: 60
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: false
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        ticks: {
                            callback: function (value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // ======================================
    // PAYMENT METHODS DOUGHNUT CHART
    // ======================================

    const paymentCanvas = document.getElementById('paymentChart');

    if (paymentCanvas) {
        new Chart(paymentCanvas, {
            type: 'doughnut',

            data: {
                labels: paymentLabels,

                datasets: [{
                    label: 'Payment Amount ($)',
                    data: paymentAmounts,

                    backgroundColor: [
                        '#198754', // Cash
                        '#0d6efd', // KHQR
                        '#6f42c1', // Visa
                        '#fd7e14', // Mastercard
                        '#adb5bd'  // Unspecified
                    ],

                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',

                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

});
