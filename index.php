<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();

// --- LÓGICA DE LOGOUT ---
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// --- 1. CONFIGURACIÓN ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'prestashop_db');
define('DB_USER', 'usuario_db');
define('DB_PASS', 'contraseña_db');
define('DB_PREFIX', 'ps_');
define('SCRIPT_PASSWORD', 'TuContraseñaSecreta');
define('SHOP_NAME', 'Nombre de la Tienda');

// Cargar el autoloader de Composer usando una ruta absoluta
require __DIR__ . '/vendor/autoload.php';

// --- 2. LÓGICA DE AUTENTICACIÓN ---
if (isset($_POST['password'])) {
    if ($_POST['password'] === SCRIPT_PASSWORD) {
        $_SESSION['loggedin'] = true;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }
}

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo '<!DOCTYPE html><html lang="es"><head><title>Acceso Protegido</title><style>body { font-family: sans-serif; text-align: center; margin-top: 100px; }</style></head><body>';
    echo '<h2>Acceso a Informes</h2><form method="post"><label>Contraseña:</label><br><input type="password" name="password" /><br><br><input type="submit" value="Entrar"></form>';
    echo '</body></html>';
    exit;
}

// --- 3. LÓGICA PRINCIPAL: PROCESAMIENTO DEL FORMULARIO ---
if (isset($_POST['generate_report'])) {
    generarInforme();
    exit;
}

// --- 4. HTML: FORMULARIO PARA PEDIR LOS DATOS ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Informes - <?php echo SHOP_NAME; ?></title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; max-width: 600px; margin: 40px auto; color: #333; }
        .container { border: 1px solid #ddd; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        h1 { text-align: center; margin-bottom: 5px; }
        .version { text-align: center; margin-top: 0; margin-bottom: 20px; color: #888; font-size: 0.9em; }
        label { display: block; width: 100%; margin-bottom: 8px; font-weight: bold; }
        select, input[type="date"] { display: block; width: 100%; margin-bottom: 15px; box-sizing: border-box; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        input[type="submit"] { background-color: #007bff; color: white; border: none; padding: 12px 20px; cursor: pointer; width: 100%; border-radius: 4px; font-size: 16px; margin-top: 10px; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .date-shortcuts { margin-bottom: 20px; text-align: center; }
        .date-shortcuts button { background-color: #f0f0f0; border: 1px solid #ccc; padding: 5px 10px; margin: 2px; border-radius: 4px; cursor: pointer; }
        .date-shortcuts button:hover { background-color: #e0e0e0; }
        .logout-link { display: block; text-align: center; margin-top: 20px; font-size: 14px; color: #dc3545; text-decoration: none; }
        .logout-link:hover { text-decoration: underline; }
        footer { text-align: center; margin-top: 30px; font-size: 12px; color: #aaa; }
        footer a { color: #aaa; text-decoration: none; }
        footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generador de Informes</h1>
        <p class="version">v1.01</p>
        <p>Tienda: <strong><?php echo SHOP_NAME; ?></strong></p>
        
        <form method="post" action="">
            <label for="report_type">1. Selecciona el tipo de informe:</label>
            <select name="report_type" id="report_type" required>
                <option value="brand">Informe de Ventas por Marca</option>
                <option value="general">Informe de Ventas General</option>
                <option value="customer">Informe de Ventas por Cliente</option>
            </select>

            <label>2. Selecciona un rango de fechas:</label>
            <div class="date-shortcuts">
                <button type="button" data-period="today">Hoy</button>
                <button type="button" data-period="yesterday">Ayer</button>
                <button type="button" data-period="this_week">Esta semana</button>
                <button type="button" data-period="last_7_days">Últimos 7 días</button>
                <button type="button" data-period="this_month">Mes actual</button>
                <button type="button" data-period="last_month">Mes anterior</button>
                <button type="button" data-period="this_year">Año actual</button>
                <button type="button" data-period="last_year">Año anterior</button>
            </div>
            
            <label for="start_date">Fecha de Inicio:</label>
            <input type="date" name="start_date" id="start_date" required>

            <label for="end_date">Fecha de Fin:</label>
            <input type="date" name="end_date" id="end_date" required>

            <label for="output_format">3. Formato de Salida:</label>
            <select name="output_format" id="output_format" required>
                <option value="csv">CSV (Comprimido en .ZIP)</option>
                <option value="xlsx">XLSX (Excel Moderno)</option>
            </select>
            
            <input type="submit" name="generate_report" value="Generar Informe">
        </form>
        
        <a href="?logout=true" class="logout-link">Cerrar Sesión</a>
    </div>
    
    <footer>
        Copyright &copy; <?php echo date('Y'); ?> <a href="https://rlm.llc" target="_blank">Red, Light & Magic Consulting</a>
    </footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    const setDates = (start, end) => {
        startDateInput.value = start.toISOString().slice(0, 10);
        endDateInput.value = end.toISOString().slice(0, 10);
    };

    document.querySelectorAll('.date-shortcuts button').forEach(button => {
        button.addEventListener('click', function () {
            const period = this.getAttribute('data-period');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            let start = new Date(today);
            let end = new Date(today);

            switch (period) {
                case 'today': break;
                case 'yesterday': start.setDate(start.getDate() - 1); end.setDate(end.getDate() - 1); break;
                case 'this_week': const dayOfWeek = start.getDay(); const diff = start.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); start.setDate(diff); break;
                case 'last_7_days': start.setDate(start.getDate() - 6); break;
                case 'this_month': start.setDate(1); break;
                case 'last_month': start = new Date(today.getFullYear(), today.getMonth() - 1, 1); end = new Date(today.getFullYear(), today.getMonth(), 0); break;
                case 'this_year': start = new Date(today.getFullYear(), 0, 1); break;
                case 'last_year': start = new Date(today.getFullYear() - 1, 0, 1); end = new Date(today.getFullYear() - 1, 11, 31); break;
            }
            setDates(start, end);
        });
    });
});
</script>

</body>
</html>
<?php

// --- 5. DEFINICIONES DE FUNCIONES DE EXPORTACIÓN ---

/**
 * Helper function to generate CSV content as a string
 */
function generateCsvContent($reportHeader, $headers, $data, $totals = null) {
    $handle = fopen('php://memory', 'w');
    
    fputcsv($handle, ['Tienda:', $reportHeader['shop_name']]);
    fputcsv($handle, ['Informe:', $reportHeader['report_name']]);
    fputcsv($handle, ['Desde:', $reportHeader['start_date']]);
    fputcsv($handle, ['Hasta:', $reportHeader['end_date']]);
    fputcsv($handle, []);
    fputcsv($handle, $headers);
    foreach ($data as $row) {
        fputcsv($handle, $row);
    }
    if ($totals) {
        fputcsv($handle, $totals);
    }
    
    rewind($handle);
    $content = stream_get_contents($handle);
    fclose($handle);
    return $content;
}

function exportToCsv($reportType, $reportHeader, $mainHeaders, $mainData, $options = []) {
    $zip = new ZipArchive();
    $zipFileName = tempnam(sys_get_temp_dir(), 'report') . '.zip';

    if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
        exit("Cannot create zip file");
    }

    // Main report file
    $mainTotals = $options['totals'] ?? null;
    $mainCsvContent = generateCsvContent($reportHeader, $mainHeaders, $mainData, $mainTotals);
    $zip->addFromString('informe.csv', $mainCsvContent);

    // Secondary report file
    if ($reportType === 'customer' && !empty($options['top_customers'])) {
        $top10Headers = ['ID Cliente', 'Nombre', 'Total Comprado'];
        $top10CsvContent = generateCsvContent($reportHeader, $top10Headers, $options['top_customers']);
        $zip->addFromString('top_10_clientes.csv', $top10CsvContent);
    }
    if ($reportType === 'brand' && !empty($options['brand_summary'])) {
        $summaryHeaders = ['Marca', 'Total Coste', 'Total Venta (s/IVA)', 'Total Venta (c/IVA)', 'Total Beneficio'];
        $summaryData = [];
        foreach($options['brand_summary'] as $brand => $summary) {
            $summaryData[] = array_merge([$brand], $summary);
        }
        $summaryCsvContent = generateCsvContent($reportHeader, $summaryHeaders, $summaryData);
        $zip->addFromString('totales_por_marca.csv', $summaryCsvContent);
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $reportHeader['report_name']) . '.zip"');
    header('Content-Length: ' . filesize($zipFileName));
    readfile($zipFileName);
    unlink($zipFileName);
}

function exportToXlsx($reportType, $reportHeader, $mainHeaders, $mainData, $options = []) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe');

    // Main Sheet Header
    $sheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
    $sheet->setCellValue('B1', $reportHeader['shop_name']);
    $sheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
    $sheet->setCellValue('B2', $reportHeader['report_name']);
    $sheet->setCellValue('A3', 'Desde:')->getStyle('A3')->getFont()->setBold(true);
    $sheet->setCellValue('B3', $reportHeader['start_date']);
    $sheet->setCellValue('A4', 'Hasta:')->getStyle('A4')->getFont()->setBold(true);
    $sheet->setCellValue('B4', $reportHeader['end_date']);
    
    // Main Sheet Data
    $sheet->fromArray($mainHeaders, null, 'A6');
    $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')->getFont()->setBold(true);
    $sheet->fromArray($mainData, null, 'A7');
    $currentRow = 7 + count($mainData);
    if (isset($options['totals'])) {
        $sheet->fromArray($options['totals'], null, 'A'.$currentRow);
        $sheet->getStyle('A'.$currentRow.':' . $sheet->getHighestColumn() . $currentRow)->getFont()->setBold(true);
    }

    // Secondary Sheet
    if ($reportType === 'customer' && !empty($options['top_customers'])) {
        $top10Sheet = $spreadsheet->createSheet();
        $top10Sheet->setTitle('Top 10');
        // Headers and data for Top 10
        $top10Sheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
        $top10Sheet->setCellValue('B1', $reportHeader['shop_name']);
        $top10Sheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
        $top10Sheet->setCellValue('B2', 'Top 10 Clientes');
        $top10Headers = ['ID Cliente', 'Nombre', 'Total Comprado'];
        $top10Sheet->fromArray($top10Headers, null, 'A4');
        $top10Sheet->getStyle('A4:C4')->getFont()->setBold(true);
        $top10Sheet->fromArray($options['top_customers'], null, 'A5');
    }
    if ($reportType === 'brand' && !empty($options['brand_summary'])) {
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Totales por marca');
        // Headers and data for Brand Summary
        $summarySheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
        $summarySheet->setCellValue('B1', $reportHeader['shop_name']);
        $summarySheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
        $summarySheet->setCellValue('B2', 'Resumen por Marca');
        $summaryHeaders = ['Marca', 'Total Coste', 'Total Venta (s/IVA)', 'Total Venta (c/IVA)', 'Total Beneficio'];
        $summarySheet->fromArray($summaryHeaders, null, 'A4');
        $summarySheet->getStyle('A4:E4')->getFont()->setBold(true);
        $brandData = [];
        foreach($options['brand_summary'] as $brand => $summary) {
            $brandData[] = array_merge([$brand], $summary);
        }
        $summarySheet->fromArray($brandData, null, 'A5');
    }

    // Auto-size columns for all sheets
    foreach ($spreadsheet->getAllSheets() as $worksheet) {
        foreach (range('A', $worksheet->getHighestColumn()) as $columnID) {
            $worksheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
    $spreadsheet->setActiveSheetIndex(0);

    $filename = str_replace(' ', '_', $reportHeader['report_name']) . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
}

// --- 6. FUNCIÓN PRINCIPAL QUE EJECUTA LA LÓGICA ---
function generarInforme() {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'] . ' 00:00:00';
    $endDate = $_POST['end_date'] . ' 23:59:59';
    $outputFormat = $_POST['output_format'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Error de conexión: " . $e->getMessage()); }

    $params = [':startDate' => $startDate, ':endDate' => $endDate];
    $data = []; $options = []; $headers = []; $reportName = "";

    switch ($reportType) {
        case 'brand':
            $reportName = "Informe de Ventas por Marca";
            $headers = ['ID Producto', 'Nombre Producto', 'Marca', 'Cantidad', 'Precio Coste', 'Precio Venta (s/IVA)', 'Precio Venta (c/IVA)', 'Beneficio'];
            $sql = "SELECT p.id_product, pl.name, m.name AS manufacturer, SUM(od.product_quantity) AS quantity, p.wholesale_price AS cost_price, od.unit_price_tax_excl AS sale_price_excl, od.unit_price_tax_incl AS sale_price_incl, SUM((od.unit_price_tax_excl - p.wholesale_price) * od.product_quantity) AS profit FROM " . DB_PREFIX . "orders o JOIN " . DB_PREFIX . "order_detail od ON o.id_order = od.id_order JOIN " . DB_PREFIX . "product p ON od.product_id = p.id_product JOIN " . DB_PREFIX . "product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = 1 JOIN " . DB_PREFIX . "manufacturer m ON p.id_manufacturer = m.id_manufacturer WHERE o.date_add BETWEEN :startDate AND :endDate AND o.valid = 1 GROUP BY p.id_product ORDER BY m.name, pl.name;";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $brandSummary = [];
            foreach ($data as $row) {
                $brand = $row['manufacturer'];
                if (!isset($brandSummary[$brand])) { $brandSummary[$brand] = ['cost' => 0, 'sale_excl' => 0, 'sale_incl' => 0, 'profit' => 0]; }
                $brandSummary[$brand]['cost'] += $row['cost_price'] * $row['quantity'];
                $brandSummary[$brand]['sale_excl'] += $row['sale_price_excl'] * $row['quantity'];
                $brandSummary[$brand]['sale_incl'] += $row['sale_price_incl'] * $row['quantity'];
                $brandSummary[$brand]['profit'] += $row['profit'];
            }
            $options['brand_summary'] = $brandSummary;
            break;
        
        case 'general':
            $reportName = "Informe de Ventas General";
            $headers = ['ID Pedido', 'Nº Factura', 'Fecha Factura', 'ID Cliente', 'Nombre Cliente', 'DNI/IVA', 'Email Cliente', 'Total (s/IVA)', 'Total Impuestos', 'Total (c/IVA)', 'Beneficio', 'Forma de Pago', 'Estado'];
            $sql = "SELECT o.id_order, oi.number AS invoice_number, oi.date_add AS invoice_date, c.id_customer, CONCAT(c.firstname, ' ', c.lastname) AS customer_name, a.vat_number, c.email, o.total_paid_tax_excl, (o.total_paid_tax_incl - o.total_paid_tax_excl) AS total_tax, o.total_paid_tax_incl, (SELECT SUM((od_inner.unit_price_tax_excl - p_inner.wholesale_price) * od_inner.product_quantity) FROM " . DB_PREFIX . "order_detail od_inner JOIN " . DB_PREFIX . "product p_inner ON od_inner.product_id = p_inner.id_product WHERE od_inner.id_order = o.id_order) AS profit, o.payment, osl.name AS state_name FROM " . DB_PREFIX . "orders o JOIN " . DB_PREFIX . "order_invoice oi ON o.id_order = oi.id_order JOIN " . DB_PREFIX . "customer c ON o.id_customer = c.id_customer LEFT JOIN " . DB_PREFIX . "address a ON o.id_address_delivery = a.id_address JOIN " . DB_PREFIX . "order_state_lang osl ON o.current_state = osl.id_order_state AND osl.id_lang = 1 WHERE oi.date_add BETWEEN :startDate AND :endDate GROUP BY o.id_order;";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totals = array_fill(0, count($headers), ''); $totals[0] = 'TOTAL';
            $total_excl = 0; $total_tax = 0; $total_incl = 0; $total_profit = 0;
            foreach ($data as $row) { $total_excl += $row['total_paid_tax_excl']; $total_tax += $row['total_tax']; $total_incl += $row['total_paid_tax_incl']; $total_profit += $row['profit']; }
            $totals[7] = $total_excl; $totals[8] = $total_tax; $totals[9] = $total_incl; $totals[10] = $total_profit;
            $options['totals'] = $totals;
            break;

        case 'customer':
            $reportName = "Informe de Ventas por Cliente";
            $headers = ['ID Cliente', 'Nombre', 'Email Cliente', 'Total Ventas (s/IVA)', 'Total Impuestos', 'Total Ventas (c/IVA)'];
            $sql = "SELECT c.id_customer, CONCAT(c.firstname, ' ', c.lastname) AS customer_name, c.email, SUM(o.total_paid_tax_excl) AS total_excl, SUM(o.total_paid_tax_incl - o.total_paid_tax_excl) AS total_tax, SUM(o.total_paid_tax_incl) AS total_incl FROM " . DB_PREFIX . "orders o JOIN " . DB_PREFIX . "customer c ON o.id_customer = c.id_customer WHERE o.date_add BETWEEN :startDate AND :endDate AND o.valid = 1 GROUP BY c.id_customer ORDER BY total_incl DESC;";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $top10Sql = str_replace('ORDER BY total_incl DESC;', 'ORDER BY total_incl DESC LIMIT 10;', $sql);
            $top10Stmt = $pdo->prepare($top10Sql); $top10Stmt->execute($params);
            $options['top_customers'] = array_map(function($c) { return [$c['id_customer'], $c['customer_name'], $c['total_incl']]; }, $top10Stmt->fetchAll(PDO::FETCH_ASSOC));
            $totals = array_fill(0, count($headers), ''); $totals[0] = 'TOTAL';
            $total_excl = 0; $total_tax = 0; $total_incl = 0;
            foreach ($data as $row) { $total_excl += $row['total_excl']; $total_tax += $row['total_tax']; $total_incl += $row['total_incl']; }
            $totals[3] = $total_excl; $totals[4] = $total_tax; $totals[5] = $total_incl;
            $options['totals'] = $totals;
            break;
    }

    $reportHeader = ['shop_name' => SHOP_NAME, 'report_name' => $reportName, 'start_date' => $_POST['start_date'], 'end_date' => $_POST['end_date']];

    switch ($outputFormat) {
        case 'csv':
            exportToCsv($reportType, $reportHeader, $headers, $data, $options);
            break;
        case 'xlsx':
            exportToXlsx($reportType, $reportHeader, $headers, $data, $options);
            break;
    }
}
?>