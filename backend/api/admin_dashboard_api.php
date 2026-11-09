<?php
require_once __DIR__ . '/../../bootstrap.php';
session_start();
require __DIR__ . '/../handlers/authHandler.php';
require_once __DIR__ . '/../handlers/Database.php';
require_once __DIR__ . '/../handlers/customersHandler.php';
require_once __DIR__ . '/../handlers/staffsHandler.php';
require_once __DIR__ . '/../handlers/transactionHandler.php';

header('Content-Type: application/json');

$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin');

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Initialize handlers
$customerHandler = new CustomerHandler($conn);
$staffsHandler = new StaffsHandler($conn);
$transactionHandler = new TransactionHandler($conn);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAll':
            // Fetch all dashboard statistics
            $totalCustomers = $customerHandler->getTotalCustomers() ?? 0;
            $totalAmount = $transactionHandler->getTotalAmount() ?? 0;
            $totalServices = $transactionHandler->getTotalServices() ?? 0;
            $totalTechnicians = $staffsHandler->getTotalTechnicians() ?? 0;
            
            // Fetch weekly data
            $weeklyIncome = $transactionHandler->getWeeklyIncome() ?? 0;
            $weeklyServices = $transactionHandler->getWeeklyServices() ?? 0;
            $weeklyCustomers = $transactionHandler->getWeeklyCustomers() ?? 0;
            
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
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'totalCustomers' => $totalCustomers,
                    'totalAmount' => $totalAmount,
                    'totalServices' => $totalServices,
                    'totalTechnicians' => $totalTechnicians,
                    'weeklyIncome' => $weeklyIncome,
                    'weeklyServices' => $weeklyServices,
                    'weeklyCustomers' => $weeklyCustomers,
                    'monthlyData' => $monthlyData,
                    'serviceStatusBreakdown' => $serviceStatusBreakdown,
                    'serviceTypeBreakdown' => $serviceTypeBreakdown,
                    'topStaff' => $topStaff,
                    'recentActivities' => $recentActivities,
                    'dailyTrends' => $dailyTrends
                ],
                'message' => 'Dashboard data fetched successfully'
            ]);
            break;
        
        default:
            echo json_encode([
                'success' => false,
                'data' => null,
                'message' => 'Invalid action'
            ]);
            break;
    }
} catch (Exception $e) {
    error_log('Admin Dashboard API Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'data' => null,
        'message' => 'Error fetching dashboard data: ' . $e->getMessage()
    ]);
}
