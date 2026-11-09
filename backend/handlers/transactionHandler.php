<?php

class TransactionHandler
{
    private $conn;
    private $transaction_table = "transaction";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getTotalAmount()
    {
        try {
            $query = "SELECT COALESCE(SUM(sd.total_amount), 0) as total 
                     FROM service_details sd 
                     WHERE sd.total_amount > 0";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return floatval($row['total']);
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting total amount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getTotalServices()
    {
        try {
            $query = "SELECT COUNT(*) as total FROM service_reports";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return intval($row['total']);
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting total services: ' . $e->getMessage());
            return 0;
        }
    }

    public function getMonthlyTransactions($limit = 6)
    {
        try {
            $query = "SELECT 
                        DATE_FORMAT(sr.date_in, '%Y-%m') as month,
                        COUNT(*) as transaction_count,
                        COALESCE(SUM(sd.total_amount), 0) as total_amount
                    FROM service_reports sr
                    LEFT JOIN service_details sd ON sr.report_id = sd.report_id
                    WHERE sr.date_in >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                    GROUP BY DATE_FORMAT(sr.date_in, '%Y-%m')
                    ORDER BY month DESC
                    LIMIT ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $limit, $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $monthlyData = [];
            while ($row = $result->fetch_assoc()) {
                $monthlyData[] = $row;
            }

            return array_reverse($monthlyData); // Return in chronological order
        } catch (Exception $e) {
            error_log('Error getting monthly transactions: ' . $e->getMessage());
            return [];
        }
    }

    public function getServiceTypeBreakdown()
    {
        try {
            $query = "WITH RECURSIVE numbers AS (
                SELECT 0 as n UNION ALL SELECT n + 1 FROM numbers WHERE n < (
                    SELECT MAX(JSON_LENGTH(service_types)) - 1 FROM service_details
                )
            ),
            service_type_counts AS (
                SELECT 
                    JSON_UNQUOTE(JSON_EXTRACT(sd.service_types, CONCAT('$[', n, ']'))) as service_type,
                    COUNT(*) as count
                FROM service_details sd
                CROSS JOIN numbers
                WHERE JSON_UNQUOTE(JSON_EXTRACT(sd.service_types, CONCAT('$[', n, ']'))) IS NOT NULL
                GROUP BY JSON_UNQUOTE(JSON_EXTRACT(sd.service_types, CONCAT('$[', n, ']')))
            )
            SELECT
                CASE service_type
                    WHEN 'installation' THEN 'Installation'
                    WHEN 'repair' THEN 'Repair'
                    WHEN 'cleaning' THEN 'Cleaning'
                    WHEN 'checkup' THEN 'Check-up'
                    ELSE service_type
                END as service_type,
                count
            FROM service_type_counts
            ORDER BY count DESC";

            $result = $this->conn->query($query);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[$row['service_type']] = intval($row['count']);
            }
            return $data;
        } catch (Exception $e) {
            error_log('Error getting service type breakdown: ' . $e->getMessage());
            return [];
        }
    }

    public function getServiceStatusBreakdown()
    {
        try {
            $query = "SELECT 
                        status,
                        COUNT(*) as count
                     FROM service_reports
                     GROUP BY status";

            $result = $this->conn->query($query);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[$row['status']] = intval($row['count']);
            }
            return $data;
        } catch (Exception $e) {
            error_log('Error getting service status breakdown: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopPerformingStaff($limit = 3)
    {
        try {
            $query = "SELECT 
                        TRIM(REPLACE(REPLACE(sd.technician, ' (Technician)', ''), ' (Manager)', '')) as technician_name,
                        COUNT(DISTINCT sr.report_id) as service_count
                     FROM service_details sd
                     JOIN service_reports sr ON sd.report_id = sr.report_id
                     WHERE sd.technician IS NOT NULL 
                     AND sd.technician NOT LIKE '%Select Staff%'
                     AND sd.technician NOT IN ('Select Staff', '')
                     AND sd.technician != ''
                     GROUP BY technician_name
                     ORDER BY service_count DESC
                     LIMIT ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $staff = [];
            while ($row = $result->fetch_assoc()) {
                $staff[] = [
                    'name' => $row['technician_name'],
                    'services' => intval($row['service_count'])
                ];
            }
            return $staff;
        } catch (Exception $e) {
            error_log('Error getting top performing staff: ' . $e->getMessage());
            return [];
        }
    }

    public function getWeeklyIncome()
    {
        try {
            $query = "SELECT COALESCE(SUM(sd.total_amount), 0) as total 
                     FROM service_details sd 
                     JOIN service_reports sr ON sd.report_id = sr.report_id
                     WHERE sd.total_amount > 0 
                     AND sr.date_in >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return floatval($row['total']);
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting weekly income: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWeeklyServices()
    {
        try {
            $query = "SELECT COUNT(*) as total 
                     FROM service_reports sr
                     WHERE sr.date_in >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return intval($row['total']);
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting weekly services: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWeeklyCustomers()
    {
        try {
            // Normalize customer names (trim + lower) before distinct count so
            // small differences (case, trailing spaces) don't prevent counting.
            // Also keeps the same date_in filter so NULL dop/date_pulled_out won't affect this.
            $query = "SELECT COUNT(DISTINCT LOWER(TRIM(sr.customer_name))) as total 
                     FROM service_reports sr
                     WHERE sr.date_in >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                return intval($row['total']);
            }
            return 0;
        } catch (Exception $e) {
            error_log('Error getting weekly customers: ' . $e->getMessage());
            return 0;
        }
    }

    public function getDailyServiceTrends($days = 7)
    {
        try {
            $query = "SELECT 
                        DATE(sr.date_in) as service_date,
                        COUNT(*) as service_count
                     FROM service_reports sr
                     WHERE sr.date_in >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                     GROUP BY DATE(sr.date_in)
                     ORDER BY service_date ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $days);
            $stmt->execute();
            $result = $stmt->get_result();

            $trends = [];
            $dates = [];
            $counts = [];

            // Create array for all days in range
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dates[] = date('D', strtotime($date)); // Day abbreviation
                $counts[] = 0; // Default count
            }

            // Fill in actual data
            while ($row = $result->fetch_assoc()) {
                $serviceDate = $row['service_date'];
                $dayIndex = array_search(date('D', strtotime($serviceDate)), $dates);
                if ($dayIndex !== false) {
                    $counts[$dayIndex] = intval($row['service_count']);
                }
            }

            return [
                'labels' => $dates,
                'data' => $counts
            ];
        } catch (Exception $e) {
            error_log('Error getting daily service trends: ' . $e->getMessage());
            return [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }

    public function getRecentActivities($limit = 5)
    {
        try {
            $query = "SELECT 
                        sr.report_id,
                        sr.date_in,
                        sr.status,
                        sr.customer_name,
                        sr.appliance_name,
                        sd.service_types,
                        COALESCE(sd.total_amount, 0) as amount,
                        sd.technician
                    FROM service_reports sr
                    LEFT JOIN service_details sd ON sr.report_id = sd.report_id
                    ORDER BY sr.date_in DESC
                    LIMIT ?";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $activities = [];
            while ($row = $result->fetch_assoc()) {
                $serviceTypesRaw = $row['service_types'];
                $serviceTypes = null;

                if (!empty($serviceTypesRaw)) {
                    $decoded = json_decode($serviceTypesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $serviceTypes = $decoded;
                    } else {
                        error_log('transactionHandler: Failed to decode service_types for report ' . $row['report_id'] . ' - ' . json_last_error_msg());
                    }
                }

                $activities[] = [
                    'id' => $row['report_id'],
                    'date_created' => $row['date_in'],
                    'service_type' => is_array($serviceTypes) && !empty($serviceTypes) ? $serviceTypes[0] : 'N/A',
                    'status' => $row['status'],
                    'customer_name' => $row['customer_name'],
                    'appliance' => $row['appliance_name'],
                    'technician' => $row['technician'] ?? 'Not Assigned',
                    'amount' => floatval($row['amount'])
                ];
            }

            return $activities;
        } catch (Exception $e) {
            error_log('Error getting recent activities: ' . $e->getMessage());
            return [];
        }
    }

    public function getDailyServiceTrendsForStaff($staffName, $days = 7)
    {
        try {
            $cleanStaffName = trim(str_replace([' (Technician)', ' (Manager)'], '', $staffName));
            
            // Show all service reports regardless of technician assignment
            $query = "SELECT 
                        DATE(sr.date_in) as service_date,
                        COUNT(*) as service_count
                     FROM service_reports sr
                     WHERE sr.date_in >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                     GROUP BY DATE(sr.date_in)
                     ORDER BY service_date ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $days);
            $stmt->execute();
            $result = $stmt->get_result();

            $trends = [];
            $dates = [];
            $counts = [];

            // Create array for all days in range
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dates[] = date('D', strtotime($date)); // Day abbreviation
                $counts[] = 0; // Default count
            }

            // Fill in actual data
            while ($row = $result->fetch_assoc()) {
                $serviceDate = $row['service_date'];
                $dayIndex = array_search(date('D', strtotime($serviceDate)), $dates);
                if ($dayIndex !== false) {
                    $counts[$dayIndex] = intval($row['service_count']);
                }
            }

            return [
                'labels' => $dates,
                'data' => $counts
            ];
        } catch (Exception $e) {
            error_log('Error getting daily service trends for staff: ' . $e->getMessage());
            return [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }
}
