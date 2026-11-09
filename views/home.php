<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../backend/handlers/authHandler.php';
require_once __DIR__ . '/../backend/handlers/Database.php';
require_once __DIR__ . '/../backend/handlers/customersHandler.php';
require_once __DIR__ . '/../backend/handlers/staffsHandler.php';
require_once __DIR__ . '/../backend/handlers/transactionHandler.php';

$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin');

// Initialize variables with default values
$totalCustomers = 0;
$totalAmount = 0;
$totalServices = 0;
$totalTechnicians = 0;
$monthlyData = [];
$serviceBreakdown = [];
$recentActivities = [];

try {
    // Initialize Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Initialize handlers
    $customerHandler = new CustomerHandler($conn);
    $staffsHandler = new StaffsHandler($conn);
    $transactionHandler = new TransactionHandler($conn);

    // Fetch real data
    $totalCustomers = $customerHandler->getTotalCustomers();
    $totalAmount = $transactionHandler->getTotalAmount();
    $totalServices = $transactionHandler->getTotalServices();
    $totalTechnicians = $staffsHandler->getTotalTechnicians();

    // Fetch weekly data
    $weeklyIncome = $transactionHandler->getWeeklyIncome();
    $weeklyServices = $transactionHandler->getWeeklyServices();
    $weeklyCustomers = $transactionHandler->getWeeklyCustomers();

    // Fetch monthly trends
    $monthlyData = $transactionHandler->getMonthlyTransactions(6);

    // Fetch service breakdowns
    $serviceStatusBreakdown = $transactionHandler->getServiceStatusBreakdown();
    $serviceTypeBreakdown = $transactionHandler->getServiceTypeBreakdown();

    // Fetch top performing staff
    $topStaff = $transactionHandler->getTopPerformingStaff(3);

    // Fetch recent activities
    $recentActivities = $transactionHandler->getRecentActivities(5);

    // Fetch daily service trends for the last 7 days
    $dailyTrends = $transactionHandler->getDailyServiceTrends(7);

    // Debug: Print service type breakdown
    error_log('Service Type Breakdown: ' . print_r($serviceTypeBreakdown, true));
    error_log('Top Staff Data: ' . print_r($topStaff, true));
    error_log('Daily Trends Data: ' . print_r($dailyTrends, true));
} catch (Exception $e) {
    error_log('Dashboard Error: ' . $e->getMessage());
}
$labels = [];
$amounts = [];
$counts = [];
$serviceLabels = [];
$serviceCounts = [];

try {
    // Initialize Database connection
    $db = new Database();
    $conn = $db->getConnection();

    // Initialize handlers
    $customerHandler = new CustomerHandler($conn);
    $staffsHandler = new StaffsHandler($conn);
    $transactionHandler = new TransactionHandler($conn);

    // Fetch real data
    $totalCustomers = $customerHandler->getTotalCustomers() ?? 0;
    $totalAmount = $transactionHandler->getTotalAmount() ?? 0;
    $totalServices = $transactionHandler->getTotalServices() ?? 0;
    $totalTechnicians = $staffsHandler->getTotalTechnicians() ?? 0;
} catch (Exception $e) {
    // Log the error
    error_log('Dashboard Error: ' . $e->getMessage());
    // Continue with default values
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="shortcut Icon" href="../img/Repair.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .card-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: 0.75rem;
            border: 1px solid #e9ecef;
        }

        .metric-card {
            padding: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex !important;
            align-items: center;
            gap: 16px;
            min-height: 80px;
            flex-direction: row !important;
        }

        .div10 {
            padding: 10px;
        }

        .div11,
        .div12 {
            padding: 12px;
        }

        .card-box h5 {
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-box h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 8px 0;
            color: #2c3e50;
            text-align: left;
        }

        .card-icon {
            font-size: 28px;
            color: #fff;
            display: block;
            text-align: center;
        }

        .icon-square {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            order: 1;
        }

        .icon-square.customers {
            background: #ff6b6b;
        }

        .icon-square.income {
            background: #4ecdc4;
        }

        .icon-square.services {
            background: #45b7d1;
        }

        .growth {
            font-size: 12px;
            font-weight: 500;
            color: #6c757d;
            text-align: left;
            margin-top: 4px;
        }

        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            order: 2;
        }

        .card-content h5 {
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-content h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 4px 0;
            color: #2c3e50;
            text-align: left;
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

        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        table {
            font-size: 14px;
            table-layout: fixed;
            width: 100%;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background: #f8f9fa;
            padding: 12px;
            font-weight: 500;
            color: #495057;
        }

        .table td {
            padding: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        /* column width adjustment */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 12%;
        }

        /* date */
        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 25%;
        }

        /* customer */
        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 20%;
        }

        /* service */
        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 15%;
        }

        /* status */
        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 15%;
            text-align: right;
        }

        /* amount */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Staff List Styles */
        .staff-list {
            margin-top: 10px;
        }

        .staff-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .staff-item:last-child {
            border-bottom: none;
        }

        .staff-rank {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            margin-right: 12px;
        }

        .staff-rank-1 {
            background: #ffc107;
        }

        .staff-rank-2 {
            background: #6c757d;
        }

        .staff-rank-3 {
            background: #cd7f32;
        }

        .staff-name {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .staff-stats {
            font-size: 11px;
            color: #666;
            margin-left: auto;
            text-align: right;
        }

        /* New Modern Styles */
        .card-box {
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }


        .section-title {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            padding-bottom: 5px;
            border-bottom: 1px solid #f8f9fa;
            margin-bottom: 8px;
        }

        .low-stock-container {
            height: 100%;
            overflow-y: auto;
        }

        .low-stock-container .table {
            margin-bottom: 0;
        }

        .low-stock-container .badge {
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .low-stock-container .table td {
            vertical-align: middle;
            padding: 0.5rem;
        }

        .trend-item:hover,
        .service-type-item:hover {
            background: #f1f3f5;
        }

        .badge {
            padding: 6px 12px;
            font-weight: 500;
            border-radius: 6px;
        }

        .staff-table-container {
            padding: 0;
            overflow: visible;
        }

        .staff-table-container .table {
            margin: 0;
            font-size: 0.75rem;
        }

        .staff-table-container .table th {
            border-top: none;
            background: #f8f9fa;
            font-weight: 600;
            padding: 4px 6px;
            font-size: 0.7rem;
        }

        .staff-table-container .table td {
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 0.7rem;
        }

        .rank-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.7rem;
        }

        .rank-1 {
            background: #ffd700;
            color: #000;
        }

        .rank-2 {
            background: #c0c0c0;
            color: #000;
        }

        .rank-3 {
            background: #cd7f32;
            color: #fff;
        }

        /* Responsive adjustments */
        /* Chart container heights */
        .card-box {
            margin-bottom: 1rem;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .chart-container {
            position: relative;
            width: 100%;
            margin-top: 5px;
        }

        .chart-container.performance-chart {
            height: 160px;
        }

        .chart-container.small-chart {
            height: 180px;
        }

        .card-box canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* Align cards heights */
        .metrics-card {
            height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0;
        }

        .card-box.metric-card {
            padding: 8px 12px;
        }

        .section-title {
            margin-bottom: 10px;
            flex-shrink: 0;
        }

        /* Dashboard Grid Layout */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr) repeat(3, 1.2fr);
            grid-template-rows: repeat(7, minmax(auto, 100px));
            gap: 12px;
            padding: 12px 15px 15px 5px;
        }

        .div2 {
            grid-column: span 3 / span 3;
            grid-row: span 2 / span 2;
            grid-column-start: 1;
            grid-row-start: 6;
        }

        .div7 {
            grid-column: span 2 / span 2;
            grid-column-start: 3;
            grid-row-start: 1;
        }

        .div15 {
            grid-column: span 2 / span 2;
            grid-column-start: 5;
            grid-row-start: 1;
        }

        .div10 {
            grid-column: span 3 / span 3;
            grid-row: span 3 / span 3;
            grid-column-start: 7;
            grid-row-start: 1;
        }

        .div11 {
            grid-row: span 2 / span 2;
            grid-column-start: 1;
            grid-row-start: 14;
        }

        .div12 {
            grid-row: span 2 / span 2;
            grid-column-start: 2;
            grid-row-start: 14;
        }

        .div13 {
            grid-column: span 3 / span 3;
            grid-row: span 4 / span 4;
            grid-column-start: 7;
            grid-row-start: 4;
        }

        .div14 {
            grid-column: span 2 / span 2;
            grid-column-start: 1;
            grid-row-start: 1;
        }

        .div15 {
            grid-column: span 2 / span 2;
            grid-column-start: 5;
            grid-row-start: 1;
        }

        .div16 {
            grid-column: span 6 / span 6;
            grid-row: span 4 / span 4;
            grid-column-start: 1;
            grid-row-start: 2;
        }

        .div17 {
            grid-column: span 3 / span 3;
            grid-row: span 2 / span 2;
            grid-column-start: 4;
            grid-row-start: 6;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .div1,
            .div2,
            .div7,
            .div10,
            .div11,
            .div12,
            .div13 {
                grid-column: 1;
                grid-row: auto;
            }

            .card-box {
                margin-bottom: 12px;
            }
        }

        /* Additional adjustments for grid layout */
        .card-box {
            margin: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chart-container {
            flex-grow: 1;
            min-height: 0;
        }

        .metrics-card {
            height: 100%;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="body-overlay"></div>

        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <div id="content">
            <!-- Top Navbar -->
            <?php
            $pageTitle = 'View Dashboard';
            $breadcrumb = 'View Dashboard';
            include __DIR__ . '/../layout/navbar.php';
            ?>
            <!-- Main Content -->
            <div class="main-content px-2 pt-2 pb-4">

                <!-- Main Dashboard Layout -->
                <div class="dashboard-grid">
                    <!-- Weekly Customers Card -->
                    <div class="card-box metric-card div14">
                        <div class="icon-square customers">
                            <span class="material-icons card-icon">groups</span>
                        </div>
                        <div class="card-content">
                            <h5>Weekly Customers</h5>
                            <h2><?php echo $weeklyCustomers; ?></h2>
                        </div>
                    </div>

                    <!-- Weekly Service Income Card -->
                    <div class="card-box metric-card div7">
                        <div class="icon-square income">
                            <span class="material-icons card-icon">payments</span>
                        </div>
                        <div class="card-content">
                            <h5>Weekly Service Income</h5>
                            <h2>₱<?php echo number_format($weeklyIncome, 2); ?></h2>
                        </div>
                    </div>

                    <!-- Weekly Total Services Card -->
                    <div class="card-box metric-card div15">
                        <div class="icon-square services">
                            <span class="material-icons card-icon">build_circle</span>
                        </div>
                        <div class="card-content">
                            <h5>Weekly Total Services</h5>
                            <h2><?php echo $weeklyServices; ?></h2>
                        </div>
                    </div>

                    <!-- Popular Service Types -->
                    <div class="card-box div2">
                        <div class="section-title">Popular Service Types</div>
                        <div class="chart-container small-chart">
                            <canvas id="serviceTypesChart"></canvas>
                        </div>
                    </div>

                    <!-- Service Performance Overview -->
                    <div class="card-box div16">
                        <div class="section-title d-flex justify-content-between align-items-center">
                            <span>Service Performance Overview</span>
                            <select id="trendFilter" class="form-control" style="width: auto; font-size: 0.9rem;">
                                <option value="monthly">Monthly Trends</option>
                                <option value="weekly">Weekly Trends</option>
                                <option value="yearly">Yearly Trends</option>
                            </select>
                        </div>
                        <div class="chart-container performance-chart">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="card-box div10">
                        <?php
                        require_once __DIR__ . '/../backend/handlers/partsHandler.php';
                        $partsHandler = new PartsHandler($conn);
                        $lowStockParts = $partsHandler->getLowStockParts(10);
                        $hasLowStock = !empty($lowStockParts);
                        ?>
                        <div class="section-title d-flex align-items-center justify-content-between">
                            <span>Low Stock Alert</span>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($hasLowStock): ?>
                                    <span class="material-icons" style="color: #dc3545; font-size: 28px; animation: pulse 2s infinite;">warning</span>
                                    <span style="color: #dc3545; font-weight: 600; font-size: 0.9rem;">Need to Order</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <style>
                            @keyframes pulse {
                                0% {
                                    transform: scale(1);
                                    opacity: 1;
                                }

                                50% {
                                    transform: scale(1.1);
                                    opacity: 0.8;
                                }

                                100% {
                                    transform: scale(1);
                                    opacity: 1;
                                }
                            }
                        </style>
                        <div class="low-stock-container">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col" width="80%">Part Info</th>
                                        <th scope="col" width="10%">Stock</th>
                                        <th scope="col" width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($lowStockParts)) {
                                        foreach ($lowStockParts as $row) {
                                            $stock = $row['quantity_stock'];
                                            // Critical: stock < 5 = red, Low: stock 6-10 = orange, Out of Stock = red
                                            if ($stock == 0) {
                                                $statusClass = 'text-danger';
                                                $statusText = 'Out of Stock';
                                            } elseif ($stock < 5) {
                                                $statusClass = 'text-danger';
                                                $statusText = 'Critical';
                                            } else {
                                                $statusClass = 'text-warning';
                                                $statusText = 'Low';
                                            }

                                            echo "<tr>";
                                            echo "<td style='font-size: 0.9rem; font-weight: 500;'>" . htmlspecialchars($row['part_no']) . " - " . htmlspecialchars($row['description']) . "</td>";
                                            echo "<td style='font-size: 0.9rem; font-weight: 500;'>" . $stock . "</td>";
                                            echo "<td><span class='badge " . $statusClass . "' style='font-size: 0.85rem;'>" . $statusText . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center text-muted'>No low stock items</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Performing Staff -->
                    <div class="card-box div17">
                        <div class="section-title">Top Performing Staff</div>
                        <div class="staff-table-container">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 12%;">#</th>
                                        <th scope="col" style="width: 75%;">Name</th>
                                        <th scope="col" style="width: 13%;">Services</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($topStaff)): ?>
                                        <?php foreach ($topStaff as $index => $staff): ?>
                                            <tr>
                                                <td>
                                                    <div class="rank-badge rank-<?php echo $index + 1; ?>"><?php echo $index + 1; ?></div>
                                                </td>
                                                <td style="max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.8rem;" title="<?php echo htmlspecialchars($staff['name'] ?? 'Unknown'); ?>"><?php echo htmlspecialchars($staff['name'] ?? 'Unknown'); ?></td>
                                                <td style="font-size: 0.65rem;"><?php echo $staff['services'] ?? 0; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No staff data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Service Breakdown -->
                    <div class="card-box div13">
                        <div class="section-title">Service Breakdown</div>
                        <div class="chart-container small-chart">
                            <canvas id="serviceChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery-3.3.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // Register the datalabels plugin
        Chart.register(ChartDataLabels);

        // Sidebar Toggle
        $(".xp-menubar").on('click', function() {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
        });

        $(".xp-menubar,.body-overlay").on('click', function() {
            $('#sidebar,.body-overlay').toggleClass('show-nav');
        });

        <?php
        // Fetch monthly data
        // Initialize variables with default values
        $labels = [];
        $amounts = [];
        $counts = [];

        foreach ($monthlyData as $data) {
            $labels[] = date('M Y', strtotime($data['month'] . '-01'));
            $amounts[] = $data['total_amount'];
            $counts[] = $data['transaction_count'];
        }

        // Service chart colors
        $serviceColors = ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6c757d'];

        foreach ($serviceBreakdown as $index => $service) {
            $serviceLabels[] = $service['service_type'];
            $serviceCounts[] = $service['count'];
        }
        ?>

        // Transaction Trends - Store chart instance for updates
        const ctxRevenue = document.getElementById('revenueChart');
        let revenueChart = new Chart(ctxRevenue, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Transaction Amount',
                    data: <?php echo json_encode($amounts); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Number of Services',
                    data: <?php echo json_encode($counts); ?>,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#17a2b8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    yAxisID: 'y2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Transaction Amount (₱)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    y2: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Number of Services'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Service Trends',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                }
            }
        });

        // Trend Filter Handler
        document.getElementById('trendFilter').addEventListener('change', function() {
            const trendType = this.value;
            
            // Show loading state
            const canvas = document.getElementById('revenueChart');
            canvas.style.opacity = '0.5';
            
            // Fetch data based on selected trend type
            $.ajax({
                url: '/backend/api/dashboard_api.php',
                method: 'GET',
                data: { action: 'getTrendData', type: trendType },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Update chart title
                        let chartTitle = '';
                        switch(trendType) {
                            case 'weekly':
                                chartTitle = 'Weekly Service Trends';
                                break;
                            case 'yearly':
                                chartTitle = 'Yearly Service Trends';
                                break;
                            default:
                                chartTitle = 'Monthly Service Trends';
                        }
                        
                        // Update chart data
                        revenueChart.data.labels = data.labels;
                        revenueChart.data.datasets[0].data = data.amounts;
                        revenueChart.data.datasets[1].data = data.counts;
                        revenueChart.options.plugins.title.text = chartTitle;
                        revenueChart.update();
                        
                        canvas.style.opacity = '1';
                    } else {
                        console.error('Failed to load trend data:', data.message);
                        alert('Failed to load trend data');
                        canvas.style.opacity = '1';
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching trend data:', xhr.responseText || xhr.statusText);
                    alert('Error loading trend data');
                    canvas.style.opacity = '1';
                }
            });
        });

        // Service Status Breakdown
        const ctxService = document.getElementById('serviceChart');
        const serviceStatusData = <?php echo json_encode(array_values($serviceStatusBreakdown)); ?>;
        const totalServices = serviceStatusData.reduce((a, b) => a + b, 0);

        new Chart(ctxService, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($serviceStatusBreakdown)); ?>,
                datasets: [{
                    data: serviceStatusData,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)', // Completed (green)
                        'rgba(255, 206, 86, 0.8)', // Pending (yellow)
                        'rgba(255, 99, 132, 0.8)' // Cancelled/Other (red)
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    datalabels: {
                        formatter: (value, ctx) => {
                            const percentage = (value * 100 / totalServices).toFixed(1) + '%';
                            return value + '\n(' + percentage + ')';
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                },
                maintainAspectRatio: false,
                cutout: '60%'
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = chart.width / 2;
                    const centerY = chart.height / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#2c3e50';
                    ctx.font = 'bold 24px Arial';
                    ctx.fillText(totalServices, centerX, centerY - 10);
                    ctx.font = '14px Arial';
                    ctx.fillStyle = '#6c757d';
                    ctx.fillText('Total Services', centerX, centerY + 15);
                    ctx.restore();
                }
            }]
        });

        // Auto-refresh dashboard functionality
        function fetchDashboardData() {
            $.ajax({
                url: '../backend/api/admin_dashboard_api.php?action=getAll',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.data) {
                        updateDashboardUI(res.data);
                        console.log('Admin dashboard updated successfully');
                    } else {
                        console.warn('Failed to fetch dashboard data', res);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to fetch dashboard data:', xhr.responseText || xhr.statusText);
                }
            });
        }

        function updateDashboardUI(data) {
            try {
                // Update weekly cards
                const weeklyCustomersCard = document.querySelector('.div14 h2');
                if (weeklyCustomersCard && data.weeklyCustomers !== undefined) {
                    weeklyCustomersCard.textContent = data.weeklyCustomers;
                }

                const weeklyIncomeCard = document.querySelector('.div7 h2');
                if (weeklyIncomeCard && data.weeklyIncome !== undefined) {
                    weeklyIncomeCard.textContent = '₱' + parseFloat(data.weeklyIncome).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }

                const weeklyServicesCard = document.querySelector('.div15 h2');
                if (weeklyServicesCard && data.weeklyServices !== undefined) {
                    weeklyServicesCard.textContent = data.weeklyServices;
                }

                // Update Service Performance Overview chart with latest monthlyData
                if (data.monthlyData && Array.isArray(data.monthlyData) && revenueChart) {
                    const labels = [];
                    const amounts = [];
                    const counts = [];
                    
                    data.monthlyData.forEach(item => {
                        const monthDate = new Date(item.month + '-01');
                        labels.push(monthDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
                        amounts.push(parseFloat(item.total_amount) || 0);
                        counts.push(parseInt(item.transaction_count) || 0);
                    });

                    revenueChart.data.labels = labels;
                    revenueChart.data.datasets[0].data = amounts;
                    revenueChart.data.datasets[1].data = counts;
                    revenueChart.update();
                }

                console.log('Dashboard UI updated with latest data');
            } catch (err) {
                console.error('Error updating dashboard UI:', err);
            }
        }

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

        // Auto-refresh dashboard every 5 minutes to reduce server load
        setInterval(function() {
            // Only auto-refresh if tab is visible to save resources
            if (!document.hidden) {
                fetchDashboardData();
            }
        }, 300000);

        // Initial load immediately
        fetchDashboardData();

        // Service Types Chart
        console.log('Service Type Data:', <?php echo json_encode($serviceTypeBreakdown); ?>);
        console.log('Top Staff Data:', <?php echo json_encode($topStaff); ?>);
        console.log('Daily Trends Data:', <?php echo json_encode($dailyTrends); ?>);
        const ctxServiceTypes = document.getElementById('serviceTypesChart');
        new Chart(ctxServiceTypes, {
            type: 'bar',
            data: {
                labels: ['Check-up', 'Installation', 'Repair', 'Cleaning'],
                datasets: [{
                    data: [
                        <?php echo isset($serviceTypeBreakdown['Check-up']) ? $serviceTypeBreakdown['Check-up'] : 0; ?>,
                        <?php echo isset($serviceTypeBreakdown['Installation']) ? $serviceTypeBreakdown['Installation'] : 0; ?>,
                        <?php echo isset($serviceTypeBreakdown['Repair']) ? $serviceTypeBreakdown['Repair'] : 0; ?>,
                        <?php echo isset($serviceTypeBreakdown['Cleaning']) ? $serviceTypeBreakdown['Cleaning'] : 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)', // Check-up (Blue)
                        'rgba(255, 206, 86, 0.8)', // Installation (Yellow)
                        'rgba(75, 192, 192, 0.8)', // Repair (Green)
                        'rgba(255, 99, 132, 0.8)', // Cleaning (Red)
                    ],
                    borderWidth: 0,
                    borderRadius: 6,
                    maxBarThickness: 75,
                    barThickness: 70
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 10,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                }
            }
        });
    </script>
</body>

</html>

</html>

</html>

</html>