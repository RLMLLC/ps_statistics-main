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

// --- OBTENER PROVEEDORES PARA EL FORMULARIO ---
$suppliers = [];
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT id_supplier, name FROM " . DB_PREFIX . "supplier WHERE active = 1 ORDER BY name ASC");
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // El desplegable de proveedores aparecerá vacío si hay un error
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
        select, input[type="date"], input[type="number"] { display: block; width: 100%; margin-bottom: 15px; box-sizing: border-box; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
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
        .dynamic-field { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generador de Informes</h1>
        <p class="version">v1.03b</p> <p>Tienda: <strong><?php echo SHOP_NAME; ?></strong></p>
        
        <form method="post" action="">
            <label for="report_type">1. Selecciona el tipo de informe:</label>
            <select name="report_type" id="report_type" required>
                <option value="brand">Informe de Ventas por Marca</option>
                <option value="general">Informe de Ventas General</option>
                <option value="customer">Informe de Ventas por Cliente</option>
                <option value="supplier">Productos por Proveedor</option>
                <option value="low_stock">Informe de Existencias Bajas</option>
            </select>

            <div id="supplier-selector" class="dynamic-field">
                <label for="supplier_id">2. Selecciona un Proveedor:</label>
                <select name="supplier_id" id="supplier_id">
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo htmlspecialchars($supplier['id_supplier']); ?>">
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="stock-threshold-selector" class="dynamic-field">
                <label for="stock_threshold">2. Umbral de Stock:</label>
                <input type="number" name="stock_threshold" id="stock_threshold" value="5" min="0" step="1">
            </div>

            <div id="date-range-selector" class="dynamic-field">
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
                <input type="date" name="start_date" id="start_date">

                <label for="end_date">Fecha de Fin:</label>
                <input type="date" name="end_date" id="end_date">
            </div>

            <label for="output_format" id="output_format_label">3. Formato de Salida:</label>
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
    const reportTypeSelect = document.getElementById('report_type');
    const supplierSelector = document.getElementById('supplier-selector');
    const stockThresholdSelector = document.getElementById('stock-threshold-selector');
    const dateRangeSelector = document.getElementById('date-range-selector');
    
    const outputFormatLabel = document.getElementById('output_format_label');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const supplierInput = document.getElementById('supplier_id');
    const stockThresholdInput = document.getElementById('stock_threshold');

    function toggleFormFields() {
        const selectedReport = reportTypeSelect.value;
        
        supplierSelector.style.display = 'none';
        stockThresholdSelector.style.display = 'none';
        dateRangeSelector.style.display = 'none';

        startDateInput.required = false;
        endDateInput.required = false;
        supplierInput.required = false;
        stockThresholdInput.required = false;

        if (selectedReport === 'supplier') {
            supplierSelector.style.display = 'block';
            supplierInput.required = true;
            outputFormatLabel.textContent = '3. Formato de Salida:';
        } 
        else if (selectedReport === 'low_stock') {
            stockThresholdSelector.style.display = 'block';
            stockThresholdInput.required = true;
            outputFormatLabel.textContent = '3. Formato de Salida:';
        } 
        else {
            dateRangeSelector.style.display = 'block';
            startDateInput.required = true;
            endDateInput.required = true;
            dateRangeSelector.querySelector('label').textContent = '2. Selecciona un rango de fechas:';
            outputFormatLabel.textContent = '3. Formato de Salida:';
        }
    }
    
    reportTypeSelect.addEventListener('change', toggleFormFields);
    toggleFormFields(); 

    const setDates = (start, end) => {
        startDateInput.value = start.toISOString().slice(0, 10);
        endDateInput.value = end.toISOString().slice(0, 10);
    };
    document.querySelectorAll('.date-shortcuts button').forEach(button => {
        button.addEventListener('click', function () {
            const period = this.getAttribute('data-period');
            const today = new Date(); today.setHours(0, 0, 0, 0);
            let start = new Date(today); let end = new Date(today);
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
    if (isset($reportHeader['start_date'])) {
        fputcsv($handle, ['Desde:', $reportHeader['start_date']]);
        fputcsv($handle, ['Hasta:', $reportHeader['end_date']]);
    }
    if (isset($reportHeader['stock_threshold'])) {
        fputcsv($handle, ['Umbral de Stock:', '<= ' . $reportHeader['stock_threshold']]);
    }
    fputcsv($handle, []);
    fputcsv($handle, $headers);
    foreach ($data as $row) {
        // Asegurarse de que el orden de las columnas sea el esperado por los headers
        $ordered_row = [];
        foreach($headers as $header_key => $header_name){
             $ordered_row[] = $row[$header_name] ?? ''; // Usar el nombre del header como clave
        }
        fputcsv($handle, $ordered_row);
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

    $mainTotals = $options['totals'] ?? null;
    $mainCsvContent = generateCsvContent($reportHeader, $mainHeaders, $mainData, $mainTotals);
    $zip->addFromString('informe.csv', $mainCsvContent);

    if ($reportType === 'customer' && !empty($options['top_customers'])) {
        $top10Headers = ['ID Cliente', 'Nombre', 'Total Comprado'];
        // Asegurar que los datos para top_customers tengan el formato correcto
         $topCustomersFormatted = array_map(function($c){ return [$c[0], $c[1], $c[2]]; }, $options['top_customers']);
        $top10CsvContent = generateCsvContent($reportHeader, $top10Headers, $topCustomersFormatted);
        $zip->addFromString('top_10_clientes.csv', $top10CsvContent);
    }
    if ($reportType === 'brand' && !empty($options['brand_summary'])) {
        $summaryHeaders = ['Marca', 'Total Coste', 'Total Venta (s/IVA)', 'Total Venta (c/IVA)', 'Total Beneficio'];
        $summaryData = [];
        foreach($options['brand_summary'] as $brand => $summary) {
            // Asegurar el orden correcto de las columnas
             $summaryData[] = [$brand, $summary['cost'], $summary['sale_excl'], $summary['sale_incl'], $summary['profit']];
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
    $currentRow = 6; 

    $sheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
    $sheet->setCellValue('B1', $reportHeader['shop_name']);
    $sheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
    $sheet->setCellValue('B2', $reportHeader['report_name']);
    
    if (isset($reportHeader['start_date'])) {
        $sheet->setCellValue('A3', 'Desde:')->getStyle('A3')->getFont()->setBold(true);
        $sheet->setCellValue('B3', $reportHeader['start_date']);
        $sheet->setCellValue('A4', 'Hasta:')->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('B4', $reportHeader['end_date']);
    }
    if (isset($reportHeader['stock_threshold'])) {
        $sheet->setCellValue('A3', 'Umbral de Stock:')->getStyle('A3')->getFont()->setBold(true);
        $sheet->setCellValue('B3', '<= ' . $reportHeader['stock_threshold']);
    }
    
    // Escribir cabeceras
    $sheet->fromArray($mainHeaders, null, 'A'.$currentRow);
    $sheet->getStyle('A'.$currentRow.':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($mainHeaders)) . $currentRow)->getFont()->setBold(true);
    $currentRow++;
    
    // Escribir datos asegurando el orden
     foreach($mainData as $dataRow) {
        $orderedRow = [];
        foreach($mainHeaders as $header) {
            $orderedRow[] = $dataRow[$header] ?? '';
        }
        $sheet->fromArray($orderedRow, null, 'A'.$currentRow, true);
        $currentRow++;
    }
    //$sheet->fromArray($mainData, null, 'A'.$currentRow); // Método anterior
    //$currentRow += count($mainData); // Ajuste de currentRow ya se hace en el bucle
    
    if (isset($options['totals'])) {
        $sheet->fromArray($options['totals'], null, 'A'.$currentRow);
        $sheet->getStyle('A'.$currentRow.':' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($mainHeaders)) . $currentRow)->getFont()->setBold(true);
    }

    if ($reportType === 'customer' && !empty($options['top_customers'])) {
        $top10Sheet = $spreadsheet->createSheet();
        $top10Sheet->setTitle('Top 10');
        $top10Sheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
        $top10Sheet->setCellValue('B1', $reportHeader['shop_name']);
        $top10Sheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
        $top10Sheet->setCellValue('B2', 'Top 10 Clientes');
        $top10Headers = ['ID Cliente', 'Nombre', 'Total Comprado'];
        $top10Sheet->fromArray($top10Headers, null, 'A4');
        $top10Sheet->getStyle('A4:C4')->getFont()->setBold(true);
         // Asegurar formato correcto para los datos
         $topCustomersFormatted = array_map(function($c){ return [$c[0], $c[1], $c[2]]; }, $options['top_customers']);
        $top10Sheet->fromArray($topCustomersFormatted, null, 'A5');
    }
    if ($reportType === 'brand' && !empty($options['brand_summary'])) {
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Totales por marca');
        $summarySheet->setCellValue('A1', 'Tienda:')->getStyle('A1')->getFont()->setBold(true);
        $summarySheet->setCellValue('B1', $reportHeader['shop_name']);
        $summarySheet->setCellValue('A2', 'Informe:')->getStyle('A2')->getFont()->setBold(true);
        $summarySheet->setCellValue('B2', 'Resumen por Marca');
        $summaryHeaders = ['Marca', 'Total Coste', 'Total Venta (s/IVA)', 'Total Venta (c/IVA)', 'Total Beneficio'];
        $summarySheet->fromArray($summaryHeaders, null, 'A4');
        $summarySheet->getStyle('A4:E4')->getFont()->setBold(true);
        $brandData = [];
        foreach($options['brand_summary'] as $brand => $summary) {
             // Asegurar el orden correcto de las columnas
             $brandData[] = [$brand, $summary['cost'], $summary['sale_excl'], $summary['sale_incl'], $summary['profit']];
        }
        $summarySheet->fromArray($brandData, null, 'A5');
    }

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
    $outputFormat = $_POST['output_format'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) { die("Error de conexión: " . $e->getMessage()); }

    $params = [];
    $data = []; $options = []; $headers = []; $reportName = "";
    $reportHeader = ['shop_name' => SHOP_NAME];

    if ($reportType === 'supplier') {
        $supplierId = $_POST['supplier_id'];
        $params[':supplierId'] = $supplierId;
    } 
    else if ($reportType === 'low_stock') {
        $stockThreshold = (int)$_POST['stock_threshold'];
        $params[':stockThreshold'] = $stockThreshold;
        $reportHeader['stock_threshold'] = $stockThreshold;
    } 
    else {
        $startDate = $_POST['start_date'] . ' 00:00:00';
        $endDate = $_POST['end_date'] . ' 23:59:59';
        $params[':startDate'] = $startDate;
        $params[':endDate'] = $endDate;
        $reportHeader['start_date'] = $_POST['start_date'];
        $reportHeader['end_date'] = $_POST['end_date'];
    }

    switch ($reportType) {
        
        case 'low_stock':
            $reportName = "Informe de Existencias Bajas";
            // Usamos los alias de la consulta SQL como cabeceras directamente
            $headers = ['ID producto', 'ID combinación', 'Nombre del producto', 'Referencia', 'Precio unitario', 'Fabricante', 'Proveedor', 'Stock'];
            
            // --- INICIO DE LA CONSULTA SQL CORREGIDA (v2) ---
            $sql = "
                (SELECT
                    p.id_product AS `ID producto`,
                    0 AS `ID combinación`,
                    pl.name COLLATE utf8mb4_unicode_ci AS `Nombre del producto`,
                    p.reference COLLATE utf8mb4_unicode_ci AS `Referencia`,
                    p.price AS `Precio unitario`,
                    m.name COLLATE utf8mb4_unicode_ci AS `Fabricante`,
                    -- Seleccionar proveedor por defecto si existe, si no, buscar en product_supplier
                    COALESCE(s_default.name, s_assoc.name) COLLATE utf8mb4_unicode_ci AS `Proveedor`, 
                    sa.quantity AS `Stock`
                FROM
                    " . DB_PREFIX . "product p
                LEFT JOIN
                    " . DB_PREFIX . "product_attribute pa ON p.id_product = pa.id_product
                JOIN
                    " . DB_PREFIX . "product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = 1)
                JOIN
                    " . DB_PREFIX . "stock_available sa ON (p.id_product = sa.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = 1)
                LEFT JOIN
                    " . DB_PREFIX . "manufacturer m ON (p.id_manufacturer = m.id_manufacturer)
                LEFT JOIN 
                    " . DB_PREFIX . "supplier s_default ON (p.id_supplier = s_default.id_supplier AND p.id_supplier > 0) -- Proveedor por defecto
                LEFT JOIN 
                    " . DB_PREFIX . "product_supplier ps ON (p.id_product = ps.id_product AND p.id_supplier = 0) -- Solo si no hay por defecto
                 LEFT JOIN 
                    " . DB_PREFIX . "supplier s_assoc ON (ps.id_supplier = s_assoc.id_supplier) -- Nombre del proveedor asociado
                WHERE
                    sa.quantity <= :stockThreshold
                    AND pa.id_product_attribute IS NULL
                GROUP BY p.id_product -- Agrupamos por si hay multiples proveedores en product_supplier
                )
                
                UNION ALL
                
                (SELECT
                    p.id_product AS `ID producto`,
                    pa.id_product_attribute AS `ID combinación`,
                    CONCAT(pl.name, ' - ', GROUP_CONCAT(DISTINCT agl.name, ': ', al.name SEPARATOR ', ')) COLLATE utf8mb4_unicode_ci AS `Nombre del producto`,
                    IF(pa.reference IS NOT NULL AND pa.reference != '', pa.reference, p.reference) COLLATE utf8mb4_unicode_ci AS `Referencia`,
                    (p.price + pa.price) AS `Precio unitario`, -- Nota: Precio puede ser más complejo con reglas de catálogo
                    m.name COLLATE utf8mb4_unicode_ci AS `Fabricante`,
                    -- Misma lógica para proveedor en combinaciones
                    COALESCE(s_default.name, s_assoc.name) COLLATE utf8mb4_unicode_ci AS `Proveedor`,
                    sa.quantity AS `Stock`
                FROM
                    " . DB_PREFIX . "product p
                JOIN
                    " . DB_PREFIX . "product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = 1)
                JOIN
                    " . DB_PREFIX . "product_attribute pa ON (p.id_product = pa.id_product)
                JOIN
                    " . DB_PREFIX . "product_attribute_combination pac ON (pa.id_product_attribute = pac.id_product_attribute)
                JOIN
                    " . DB_PREFIX . "attribute a ON (pac.id_attribute = a.id_attribute)
                JOIN
                    " . DB_PREFIX . "attribute_lang al ON (a.id_attribute = al.id_attribute AND al.id_lang = 1)
                JOIN
                    " . DB_PREFIX . "attribute_group_lang agl ON (a.id_attribute_group = agl.id_attribute_group AND agl.id_lang = 1)
                JOIN
                    " . DB_PREFIX . "stock_available sa ON (pa.id_product_attribute = sa.id_product_attribute AND sa.id_shop = 1)
                LEFT JOIN
                    " . DB_PREFIX . "manufacturer m ON (p.id_manufacturer = m.id_manufacturer)
                 LEFT JOIN 
                    " . DB_PREFIX . "supplier s_default ON (p.id_supplier = s_default.id_supplier AND p.id_supplier > 0) -- Proveedor por defecto del producto base
                LEFT JOIN 
                    " . DB_PREFIX . "product_supplier ps ON (p.id_product = ps.id_product AND p.id_supplier = 0) -- Solo si no hay por defecto
                 LEFT JOIN 
                    " . DB_PREFIX . "supplier s_assoc ON (ps.id_supplier = s_assoc.id_supplier) -- Nombre del proveedor asociado
                WHERE
                    sa.quantity <= :stockThreshold
                GROUP BY
                    pa.id_product_attribute
                )
                
                ORDER BY
                    `Nombre del producto`, `ID combinación`;
            ";
            // --- FIN DE LA CONSULTA SQL CORREGIDA (v2) ---
            
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'supplier':
            $reportName = "Productos por Proveedor";
            $headers = ['Id Producto', 'Nombre producto', 'Referencia', 'Precio unitario', 'Existencias'];
            $sql = "SELECT p.id_product, pl.name, p.reference, p.price AS unit_price, sa.quantity AS stock FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "product_lang pl ON (p.id_product = pl.id_product AND pl.id_lang = 1) JOIN " . DB_PREFIX . "product_supplier ps ON (p.id_product = ps.id_product) LEFT JOIN " . DB_PREFIX . "stock_available sa ON (p.id_product = sa.id_product AND sa.id_product_attribute = 0 AND sa.id_shop = 1) WHERE ps.id_supplier = :supplierId GROUP BY p.id_product ORDER BY pl.name ASC;";
            
            $suppStmt = $pdo->prepare("SELECT name FROM " . DB_PREFIX . "supplier WHERE id_supplier = :supplierId");
            $suppStmt->execute([':supplierId' => $params[':supplierId']]);
            $supplierName = $suppStmt->fetchColumn();
            $reportName = "Productos del Proveedor: " . $supplierName;
            
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
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

    $reportHeader['report_name'] = $reportName;

    switch ($outputFormat) {
        case 'csv':
            // Asegurar que los headers coincidan con las claves de $data
             if (!empty($data)) {
                 $headers = array_keys($data[0]);
             }
            exportToCsv($reportType, $reportHeader, $headers, $data, $options);
            break;
        case 'xlsx':
             // Asegurar que los headers coincidan con las claves de $data
             if (!empty($data)) {
                 $headers = array_keys($data[0]);
             }
            exportToXlsx($reportType, $reportHeader, $headers, $data, $options);
            break;
    }
}
?>
