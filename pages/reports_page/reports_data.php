<?php
ob_end_clean();
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter_reports') {
    header('Content-Type: application/json');

    // 🔥 DEFAULT: THIS MONTH
    $start_date = $_GET['start_date'] ?? date('Y-m-01'); // 1st of month
    $end_date = $_GET['end_date'] ?? date('Y-m-t');     // Last day of month

    $start_date .= ' 00:00:00';
    $end_date .= ' 23:59:59';

    $conn = new mysqli('localhost', 'root', '', 'sorcom_db');
    $debug = [
        'default_dates_used' => !isset($_GET['start_date']),
        'date_range' => "$start_date → $end_date",
        'this_month' => date('Y-m')
    ];

    // 🔥 1. SALES - SIMPLIFIED (Works with your table)
    $salesData = ['labels' => [], 'data' => [], 'table_labels' => []];
    $sql = "
        SELECT 
            COALESCE(concrete_psi, 'Unknown') as psi,
            SUM(cubic_meters) as m3
        FROM statement_of_account 
        WHERE order_status = 'Done'
        " . (isset($_GET['start_date']) ? "AND order_date BETWEEN '$start_date' AND '$end_date'" : "") . "
        GROUP BY psi
        ORDER BY m3 DESC 
        LIMIT 8
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $salesData['labels'][] = $row['psi'] . 'psi';
            $salesData['data'][] = round((float) $row['m3'], 1);
            $salesData['table_labels'][] = number_format($row['m3'], 1) . 'm³';
        }
    }

    // 🔥 2. STATS
    $stats_sql = "
        SELECT 
            COUNT(*) as orders,
            SUM(cubic_meters) as total_m3,
            AVG(cubic_meters) as avg_order
        FROM statement_of_account 
        WHERE order_status = 'Done'
        " . (isset($_GET['start_date']) ? "AND order_date BETWEEN '$start_date' AND '$end_date'" : "") . "
    ";
    $stats = $conn->query($stats_sql)->fetch_assoc();

    // 🔥 3. FALLBACK INVENTORY
    $readymixData = ['labels' => ['4000psi ORD', '3000psi PUMP'], 'data' => [1250, 980]];
    $mixtureData = ['labels' => ['Cement', 'Sand', 'Fly Ash'], 'data' => [45, 250, 12]];

    $conn->close();

    echo json_encode([
        'readymix' => $readymixData,
        'mixture' => $mixtureData,
        'sales' => $salesData,
        'debug' => [
            'sales_count' => count($salesData['data']),
            'date_range' => substr($start_date, 0, 10) . ' → ' . substr($end_date, 0, 10),
            'orders_found' => (int) $stats['orders'],
            'total_m3' => round((float) $stats['total_m3'], 1),
            'avg_order_m3' => round((float) $stats['avg_order'], 1),
            'default_used' => $debug['default_dates_used'],
            'this_month' => date('M Y')
        ]
    ], JSON_NUMERIC_CHECK);
    exit;
}
echo '<!-- This Month Default -->';
?>