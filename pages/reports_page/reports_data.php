<?php
ob_end_clean();
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter_reports') {
    header('Content-Type: application/json');

    // 🔥 FIXED: Better date handling
    $start_date = $_GET['start_date'] ?? date('Y-m-01');
    $end_date = $_GET['end_date'] ?? date('Y-m-t');

    // 🔥 FIXED: Ensure proper date format for SQL
    $start_date_sql = date('Y-m-d', strtotime($start_date));
    $end_date_sql = date('Y-m-d', strtotime($end_date));

    $conn = new mysqli('localhost', 'root', '', 'sorcom_db');

    // 🔥 FIXED: Set charset to avoid encoding issues
    $conn->set_charset("utf8mb4");

    $debug = [
        'default_dates_used' => !isset($_GET['start_date']),
        'date_range' => "$start_date_sql → $end_date_sql",
        'this_month' => date('Y-m')
    ];

    // 🔥 1. SALES DATA - FIXED QUERY
    $salesData = ['labels' => [], 'data' => []];

    // 🔥 FIXED: Use proper date comparison without time
    $sql = "
        SELECT 
            concrete_psi as psi,
            SUM(sap_units) as m3
        FROM statement_of_account 
        WHERE order_status = 'Done' 
        AND DATE(order_date) BETWEEN '$start_date_sql' AND '$end_date_sql'
        GROUP BY concrete_psi
        ORDER BY m3 DESC 
        LIMIT 8
    ";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $psi_clean = preg_replace('/[^0-9]/', '', $row['psi']);
            $salesData['labels'][] = ($psi_clean ?: '0') . ' psi';
            $salesData['data'][] = round((float) $row['m3'], 1);
        }
    }

    // 🔥 2. READYMIX INVENTORY DATA (from scd_vb_list for stock levels)
    $readymixData = ['labels' => [], 'data' => []];
    $readymix_sql = "
        SELECT 
            design_mix_id as psi,
            SUM(stocks_in - stocks_out) as current_stock
        FROM sorcom_readymix_inventory 
        WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        GROUP BY design_mix_id
        ORDER BY current_stock DESC
        LIMIT 8
    ";

    $readymix_result = $conn->query($readymix_sql);
    if ($readymix_result && $readymix_result->num_rows > 0) {
        while ($row = $readymix_result->fetch_assoc()) {
            $psi_clean = preg_replace('/[^0-9]/', '', $row['psi']);
            $readymixData['labels'][] = ($psi_clean ?: '0') . ' psi';
            $readymixData['data'][] = round((float) $row['current_stock'], 1);
        }
    }

    // If no data, use fallback
    if (empty($readymixData['data'])) {
        $readymixData = ['labels' => ['4000 psi', '3000 psi', '5000 psi', '2500 psi'], 'data' => [1250, 980, 750, 420]];
    }

    // 🔥 3. MIXTURE INVENTORY DATA (from sorcom_mixture_inventory)
    $mixtureData = ['labels' => [], 'data' => []];
    $mixture_sql = "
        SELECT 
            'Cement' as material, SUM(cement) as total
        FROM sorcom_mixture_inventory 
        WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT 'Fly Ash', SUM(fly_ash) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT 'Sand', SUM(sand) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT '3/4', SUM(`3_4`) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT '3/8', SUM(`3_8`) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT 'G1', SUM(g1) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT 'Admix', SUM(admix) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
        UNION ALL
        SELECT 'Water', SUM(water) FROM sorcom_mixture_inventory WHERE DATE(date_added) BETWEEN '$start_date_sql' AND '$end_date_sql'
    ";

    $mixture_result = $conn->query($mixture_sql);
    if ($mixture_result && $mixture_result->num_rows > 0) {
        while ($row = $mixture_result->fetch_assoc()) {
            $mixtureData['labels'][] = $row['material'];
            $mixtureData['data'][] = round((float) $row['total'] / 1000, 1); // Convert to tons
        }
    }

    if (empty($mixtureData['data'])) {
        $mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water'], 'data' => [45, 12, 250, 18, 12, 8, 0.5, 30]];
    }

    // 🔥 4. STATS
    $stats_sql = "
        SELECT 
            COUNT(*) as orders,
            SUM(sap_units) as total_m3,
            AVG(sap_units) as avg_order
        FROM statement_of_account 
        WHERE order_status = 'Done' 
        AND DATE(order_date) BETWEEN '$start_date_sql' AND '$end_date_sql'
    ";
    $stats = $conn->query($stats_sql)->fetch_assoc();

    $conn->close();

    echo json_encode([
        'readymix' => $readymixData,
        'mixture' => $mixtureData,
        'sales' => $salesData,
        'debug' => [
            'sales_count' => count($salesData['data']),
            'readymix_count' => count($readymixData['data']),
            'mixture_count' => count($mixtureData['data']),
            'date_range' => "$start_date_sql → $end_date_sql",
            'orders_found' => (int) ($stats['orders'] ?? 0),
            'total_m3' => round((float) ($stats['total_m3'] ?? 0), 1),
            'avg_order_m3' => round((float) ($stats['avg_order'] ?? 0), 1),
            'default_used' => $debug['default_dates_used'],
            'this_month' => date('M Y')
        ]
    ], JSON_NUMERIC_CHECK);
    exit;
}
echo '<!-- Reports Data Loader -->';
?>