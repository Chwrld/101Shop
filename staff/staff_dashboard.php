<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
require __DIR__ . '/../backend/handlers/authHandler.php';
require_once __DIR__ . '/../backend/handlers/Database.php';
require_once __DIR__ . '/../backend/handlers/serviceHandler.php';
require_once __DIR__ . '/../backend/handlers/transactionHandler.php';

$auth = new AuthHandler();
$userSession = $auth->requireAuth('staff'); 

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize handlers
$serviceHandler = new ServiceHandler($conn);
$transactionHandler = new TransactionHandler($conn);

// Get current staff member's username (AuthHandler stores user info in $_SESSION['user'])
$currentStaff = $_SESSION['user']['username'] ?? $_SESSION['user']['full_name'] ?? '';

// Fetch real data for cards
$assignedReports = $serviceHandler->getAssignedReportsForStaff($currentStaff);
$pendingOrders = $serviceHandler->getPendingOrdersForStaff($currentStaff);
$completedServices = $serviceHandler->getCompletedServicesForStaff($currentStaff);

// Fetch data for charts
$dailyTrends = $transactionHandler->getDailyServiceTrendsForStaff($currentStaff, 7);
$workStatusData = $serviceHandler->getWorkStatusForStaff($currentStaff);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary: #6c6e6cff;
            --secondary: #2d3748;
            --highlight: #e53e3e;
            --success: #28a745;
            --warning: #dd6b20;
            --light-bg: #f4f6f9;
            --card-bg: #ffffff;
            --text: #333;
            --text-light: #666;
            --border: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--text);
            line-height: 1.6;
            font-weight: 400;
        }
        
        .wrapper {
            display: flex;
        }

       
        
        /* Main Content */
        #content {
            width: calc(100% - 250px);
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        #content.active {
            width: 100%;
            margin-left: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .content-area {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-box {
            background: #fff;
            border-radius: 5px;
            padding: 15px;
            height: 100%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        .card-box h5 {
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 0px;
            color: #666;
            width: 100%;
        }

        .card-box h2 {
            font-size: 35px;
            font-weight: 700;
            margin: 18px;
            color: #28a745;
            text-align: center;
        }

        .card-icon {
            font-size: 40px;
            margin-right: 16px;
            color: #28a745;
        }

        .growth {
            font-size: 13px;
            font-weight: 500;
            color: #28a745;
            text-align: center;
        }

        .card-content-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
        }

        .card-with-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
            
        /* stats cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            display: flex;
            align-items: center;
        }
        
        /* features section */
        .features-section {
            margin-bottom: 24px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .feature-card {
            padding: 20px;
            border-radius: 14px;
            background: var(--card-bg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
    
        
       
        
        /* Recent Activity */
        .activity-section {
            margin-bottom: 24px;
        }
        
        table {
            font-size: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .table tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .bg-success {
            background-color: var(--success);
            color: white;
        }
        
        .bg-warning {
            background-color: var(--warning);
            color: white;
        }
        
       /* Charts Section - Updated Design */
    .charts-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    
    .chart-card {
        padding: 24px;
        border-radius: 15px;
        background: var(--card-bg);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        
    }

    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--secondary);
    }
    
    .chart-actions {
        display: flex;
        gap: 10px;
    }
    
    .chart-action-btn {
        background: none;
        border: none;
        color: var(--text-light);
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .chart-action-btn:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--text);
    }
    
    .chart-wrapper {
        position: relative;
        height: 400px;
    }
    
    /* Fix chart height issues */
    .chart-card canvas {
        max-height: 400px !important;
        height: 400px !important;
    }
    
    .chart-card div[style*="height: 400px"] {
        height: 400px !important;
        max-height: 400px !important;
        overflow: hidden;
    }
    
    /* Chart-specific styles */
    #serviceReportChart {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.03) 0%, rgba(40, 167, 69, 0.08) 100%);
        border-radius: 12px;
        padding: 15px;
    }
    
    #workOrderChart {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.03) 0%, rgba(255, 193, 7, 0.08) 100%);
        border-radius: 12px;
        padding: 15px;
    }
    
    /* Chart legends */
    .chart-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .charts-container {
            grid-template-columns: 1fr;
        }
        
        .chart-wrapper {
            height: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .chart-card {
            padding: 18px;
        }
        
        .chart-wrapper {
            height: 220px;
        }
        
        .chart-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        
        .chart-actions {
            align-self: flex-end;
        }

        /* Mobile responsive adjustments */
        #sidebar {
            margin-left: -250px;
            position: fixed;
            min-height: 100vh;
            z-index: 999;
        }
        
        #sidebar.active {
            margin-left: 0;
        }
        
        #sidebar.show-nav {
            margin-left: 0;
        }
        
        

        #content {
            width: 100%;
            margin-left: 0;
        }
    }

        /* Mobile menu button */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
       
            .content-area {
                padding: 16px;
            }
            
            .menu-toggle {
                display: block;
            }
            
        }

        /* Header */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-name {
            font-weight: 500;
        }

         .navbar {
            background-color: #363a46ff;
            padding:  10px 40px 45px ;
            height: 50px;
            display: flex;
            align-items: center;
            margin-left: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand h2 {
            color: white;
            font-weight: 600;
            font-size: 22px;
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
       

        <!-- Include Sidebar -->
        <?php include 'staff_sidebar.php'; ?>
       
        <div id="content">
            <!-- Include Navbar -->
            <div class="top-navbar ml-2" >
            <div class="xp-topbar">
                <div class="row align-items-center">
                    <div class="col-2 col-md-1 col-lg-1 order-2 order-md-1 align-self-center">
                        <div class="xp-menubar">
                            <span class="material-icons text-white">signal_cellular_alt</span>
                        </div>
                    </div>
                    <div class="col-10 col-md-11 col-lg-11 order-1 order-md-2">
                        <div class="xp-profilebar text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="d-flex align-items-center text-white">
                                        <span class="material-icons mr-2">account_circle</span>
                                        <span class="username"><?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Staff', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="content-area">
                <div class="d-flex justify-content-end mb-3 dashboard-controls" style="gap:8px; align-items:center;">
                    <button id="dashboard-refresh-btn" class="btn btn-outline-primary btn-sm">
                        <i class="material-icons" style="font-size:18px; vertical-align:middle;">refresh</i>
                        <span style="vertical-align:middle; margin-left:6px;">Refresh</span>
                    </button>
                    <div id="dashboard-loading" style="display:none; align-items:center;">
                        <div class="spinner-border text-primary" role="status" style="width:1.4rem; height:1.4rem;"></div>
                    </div>
                    <div id="dashboard-last-updated" style="font-size:12px; color:#6c757d; margin-left:8px;">Last updated: -</div>
                </div>
               <div class="stats-container">
                    <div class="card-box" data-card="assigned">
                        <div class="card-content-center">
                            <div class="card-with-icon">
                                <span class="material-icons card-icon">assignment</span>
                            </div>
                            <h5>Assigned Service Reports</h5>
                            <h2><?php echo $assignedReports['total'] ?? 0; ?></h2>
                            <div class="growth"><?php echo ($assignedReports['weekly_change'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $assignedReports['weekly_change'] ?? 0; ?> this week</div>
                        </div>
                    </div>
                    
                    <div class="card-box" data-card="pending">
                        <div class="card-content-center">
                            <div class="card-with-icon">
                                <span class="material-icons card-icon">pending_actions</span>
                            </div>
                            <h5>Pending Work Orders</h5>
                            <h2><?php echo $pendingOrders['total'] ?? 0; ?></h2>
                            <div class="growth"><?php echo ($pendingOrders['daily_change'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $pendingOrders['daily_change'] ?? 0; ?> today</div>
                        </div>
                    </div>
                    
                    <div class="card-box" data-card="completed">
                        <div class="card-content-center">
                            <div class="card-with-icon">
                                <span class="material-icons card-icon">check_circle</span>
                            </div>
                            <h5>Completed Services</h5>
                            <h2><?php echo $completedServices['total'] ?? 0; ?></h2>
                            <div class="growth"><?php echo ($completedServices['daily_change'] ?? 0) >= 0 ? '+' : ''; ?><?php echo $completedServices['daily_change'] ?? 0; ?> this week</div>
                        </div>
                    </div>
                </div>
                <!-- charts -->
                <div class="charts-container">
                    <!-- Daily Service Trends -->
                    <div class="chart-card">
                        <div class="section-title">Daily Service Trends</div>
                        <div style="height: 400px; position: relative;">
                            <canvas id="serviceReportChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Service Breakdown -->
                    <div class="chart-card">
                        <div class="section-title">Service Breakdown</div>
                        <div style="height: 400px; position: relative;">
                            <canvas id="workOrderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // Register the datalabels plugin
        Chart.register(ChartDataLabels);
        
        $(document).ready(function() {
            $(".xp-menubar").on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            $(".xp-menubar,.body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').toggleClass('show-nav');
            });
            $(".body-overlay").on('click', function() {
                $('#sidebar,.body-overlay').removeClass('show-nav');
            });
            initializeCharts();

            // wire manual refresh button
            $('#dashboard-refresh-btn').on('click', function() {
                fetchDashboardData();
            });

            // BroadcastChannel for cross-tab communication (better than localStorage)
            let dashboardChannel = null;
            if ('BroadcastChannel' in window) {
                dashboardChannel = new BroadcastChannel('dashboard-refresh');
                dashboardChannel.onmessage = function(event) {
                    console.log('Dashboard refresh signal received via BroadcastChannel:', event.data);
                    fetchDashboardData();
                };
            }

            // Fallback: Listen for localStorage signal (for older browsers)
            window.addEventListener('storage', function(e) {
                if (e.key === 'dashboardRefreshNeeded') {
                    console.log('Dashboard refresh triggered from localStorage event');
                    fetchDashboardData();
                }
            });

            // Refresh when tab becomes visible (user switches back to dashboard)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    console.log('Tab became visible - refreshing dashboard');
                    fetchDashboardData();
                }
            });

            // Refresh when window gains focus
            window.addEventListener('focus', function() {
                console.log('Window gained focus - refreshing dashboard');
                fetchDashboardData();
            });

            // Auto-refresh dashboard every 5 seconds (reduced from 30 for real-time feel)
            setInterval(function() {
                // Only auto-refresh if tab is visible to save resources
                if (!document.hidden) {
                    fetchDashboardData();
                }
            }, 5000);

            // Initial load immediately
            fetchDashboardData();
        });

        function initializeCharts() {
            // Destroy existing chart instances to prevent height increase
            Chart.helpers.each(Chart.instances, function(instance) {
                instance.destroy();
            });

            // Daily Service Trends Chart
            const ctxServiceReport = document.getElementById('serviceReportChart');
            if (ctxServiceReport) {
                // create instance and store globally for later updates
                window.serviceReportChartInstance = new Chart(ctxServiceReport, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dailyTrends['labels'] ?? []); ?>,
                        datasets: [{
                            label: 'Daily Services',
                            data: <?php echo json_encode($dailyTrends['data'] ?? []); ?>,
                            borderColor: '#17a2b8',
                            backgroundColor: 'rgba(23, 162, 184, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { display: true, drawBorder: false }, ticks: { maxTicksLimit: 5, font: { size: 10 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        },
                        layout: { padding: { top: 10, right: 10, bottom: 10, left: 10 } }
                    }
                });
            }

            // Service Breakdown Chart
            const ctxWorkOrder = document.getElementById('workOrderChart');
            if (ctxWorkOrder) {
                const workOrderData = <?php echo json_encode(array_values($workStatusData['data'] ?? [])); ?>;
                const workOrderLabels = <?php echo json_encode(array_keys($workStatusData['data'] ?? [])); ?>;

                // create and store instance for updates
                window.workOrderChartInstance = new Chart(ctxWorkOrder, {
                    type: 'doughnut',
                    data: {
                        labels: workOrderLabels,
                        datasets: [{
                            data: workOrderData,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(108, 117, 125, 0.8)'
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' } },
                            datalabels: {
                                formatter: function(value, ctx) {
                                    const total = ctx.chart.data.datasets[0].data.reduce((s, v) => s + v, 0);
                                    const percentage = total > 0 ? (value * 100 / total).toFixed(1) + '%' : '0.0%';
                                    return value + '\n(' + percentage + ')';
                                },
                                color: '#fff', font: { weight: 'bold', size: 11 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.chart.data.datasets[0].data.reduce((s, v) => s + v, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        animation: { animateScale: true, animateRotate: true }
                    },
                    plugins: [{
                        id: 'centerText',
                        beforeDraw: function(chart) {
                            const ctx = chart.ctx;
                            const centerX = chart.width / 2;
                            const centerY = chart.height / 2;
                            const total = chart.data.datasets[0].data.reduce((s, v) => s + v, 0);

                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#2c3e50';
                            ctx.font = 'bold 24px Arial';
                            ctx.fillText(total, centerX, centerY - 10);
                            ctx.font = '14px Arial';
                            ctx.fillStyle = '#6c757d';
                            ctx.fillText('Total Services', centerX, centerY + 15);
                            ctx.restore();
                        }
                    }]
                });
            }
        }

        // Fetch latest dashboard data and update UI in-place
        function fetchDashboardData() {
            console.log('Fetching staff dashboard data...');
            // show loading indicator and disable refresh button
            $('#dashboard-loading').show();
            $('#dashboard-refresh-btn').prop('disabled', true);

            $.ajax({
                url: '../backend/api/dashboard_api.php?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    console.log('Dashboard API Response:', res);
                    if (res.success && res.data) {
                        updateDashboardUI(res.data);
                        // update last-updated timestamp on successful refresh
                        try {
                            const now = new Date();
                            const fmt = now.toLocaleString();
                            $('#dashboard-last-updated').text('Last updated: ' + fmt);
                        } catch (e) { /* ignore */ }
                    } else {
                        console.warn('Empty dashboard data or failed response', res);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to fetch dashboard data:', xhr);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Status:', xhr.status, xhr.statusText);
                },
                complete: function() {
                    // hide loading indicator and re-enable button
                    $('#dashboard-loading').hide();
                    $('#dashboard-refresh-btn').prop('disabled', false);
                }
            });
        }

        // Update cards and charts with new dashboard payload
        function updateDashboardUI(data) {
            console.log('Updating dashboard UI with data:', data);
            try {
                // Update cards
                const assigned = data.assignedReports || {};
                const pending = data.pendingOrders || {};
                const completed = data.completedServices || {};

                console.log('Assigned:', assigned, 'Pending:', pending, 'Completed:', completed);

                const assignedCard = document.querySelector('[data-card="assigned"]');
                if (assignedCard) {
                    const h2 = assignedCard.querySelector('h2');
                    const growth = assignedCard.querySelector('.growth');
                    console.log('Updating assigned card - h2:', h2, 'total:', assigned.total);
                    if (h2) h2.textContent = assigned.total || 0;
                    if (growth) growth.textContent = ((assigned.weekly_change >= 0) ? '+' : '') + (assigned.weekly_change || 0) + ' this week';
                }

                const pendingCard = document.querySelector('[data-card="pending"]');
                if (pendingCard) {
                    const h2 = pendingCard.querySelector('h2');
                    const growth = pendingCard.querySelector('.growth');
                    const unassigned = data.pendingUnassigned || 0;
                    let pendingGrowthText = ((pending.daily_change >= 0) ? '+' : '') + (pending.daily_change || 0) + ' today';
                    if (unassigned > 0) {
                        pendingGrowthText += ' • +' + unassigned + ' unassigned';
                    }
                    console.log('Updating pending card - h2:', h2, 'total:', pending.total);
                    if (h2) h2.textContent = pending.total || 0;
                    if (growth) growth.textContent = pendingGrowthText;
                }

                const completedCard = document.querySelector('[data-card="completed"]');
                if (completedCard) {
                    const h2 = completedCard.querySelector('h2');
                    const growth = completedCard.querySelector('.growth');
                    console.log('Updating completed card - h2:', h2, 'total:', completed.total);
                    if (h2) h2.textContent = completed.total || 0;
                    if (growth) growth.textContent = ((completed.daily_change >= 0) ? '+' : '') + (completed.daily_change || 0) + ' this week';
                }

                // Update Daily Trends chart
                const daily = data.dailyTrends || { labels: [], data: [] };
                if (window.serviceReportChartInstance) {
                    const chart = window.serviceReportChartInstance;
                    chart.data.labels = daily.labels || [];
                    if (chart.data.datasets && chart.data.datasets[0]) {
                        chart.data.datasets[0].data = daily.data || [];
                    }
                    chart.update();
                }

                // Update Work Order (breakdown) chart
                const work = data.workStatus || { data: {} };
                const labels = Object.keys(work.data || {});
                const values = labels.map(l => work.data[l] || 0);
                if (window.workOrderChartInstance) {
                    const wchart = window.workOrderChartInstance;
                    wchart.data.labels = labels;
                    if (wchart.data.datasets && wchart.data.datasets[0]) {
                        wchart.data.datasets[0].data = values;
                    }
                    wchart.update();
                }
                
                console.log('Dashboard UI updated successfully');
            } catch (err) {
                console.error('Error updating dashboard UI:', err);
            }
        }
    </script>
</body>
</html>