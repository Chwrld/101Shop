<?php
require_once __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../backend/handlers/authHandler.php';
$auth = new AuthHandler();
$userSession = $auth->requireAuth('admin'); // require login; adjust if you want other roles

require __DIR__ . '/../backend/handlers/serviceHandler.php';
require __DIR__ . '/../backend/handlers/Database.php';

$reportId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$reportId) {
    http_response_code(400);
    echo 'Report ID required';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$serviceHandler = new ServiceHandler($db);
$result = $serviceHandler->getById($reportId);

if (!$result['success'] || empty($result['data'])) {
    http_response_code(404);
    echo 'Service report not found.';
    exit;
}

$data = $result['data'];

function esc($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// Format some values
$serviceTypes = [];
if (!empty($data['service_types'])) {
    if (is_array($data['service_types'])) $serviceTypes = $data['service_types'];
    else $serviceTypes = json_decode($data['service_types'], true) ?: [$data['service_types']];
}

$parts = $data['Parts'] ?? $data['parts'] ?? [];

// D.R. format helpers
$drDate = $data['date_delivered'] ?? $data['date_pulled_out'] ?? $data['date_in'] ?? '';
if (!empty($drDate) && $drDate !== '0000-00-00') {
    $ts = strtotime($drDate);
    $drDate = $ts ? date('m/d/Y', $ts) : $drDate;
} else {
    $drDate = '';
}
$modelSerial = trim(($data['appliance_model'] ?? '') . (($data['appliance_model'] ?? '') && ($data['appliance_serial'] ?? '') ? ' / ' : '') . ($data['appliance_serial'] ?? ''));
if ($modelSerial === '' && !empty($data['appliance_name'])) $modelSerial = $data['appliance_name'];
$remarksText = trim(($data['remarks'] ?? '') !== '' ? $data['remarks'] : ($data['findings'] ?? ''));
$preparedBy = $data['receptionist'] ?? $data['staff_name'] ?? '';
$technician = $data['technician'] ?? '';
$receivedBy = $data['released_by'] ?? '';
$labor = number_format((float)($data['labor'] ?? 0), 2);
$others = number_format((float)($data['pullout_delivery'] ?? 0), 2);
$partsAmt = number_format((float)($data['parts_total_charge'] ?? 0), 2);
$totalAmt = number_format((float)($data['total_amount'] ?? 0), 2);
$address = $data['customer_address'] ?? '';
$contact = $data['customer_contact'] ?? '';
$to = $data['customer_name'] ?? '';

// Build item rows (up to 5) for the D.R. table; first row carries appliance info
$itemRows = [];
$itemRows[] = ['qty' => '1', 'model' => $modelSerial, 'remarks' => $remarksText];
if (!empty($parts) && is_array($parts)) {
    foreach ($parts as $p) {
        $pname = $p['part_name'] ?? '';
        $pqty = $p['quantity'] ?? '';
        if ($pname === '' && $pqty === '') continue;
        $itemRows[] = ['qty' => (string)$pqty, 'model' => $pname, 'remarks' => ''];
    }
}
$minRows = 5;
for ($i = count($itemRows); $i < $minRows; $i++) $itemRows[] = ['qty' => '', 'model' => '', 'remarks' => ''];

function drCopy($copyTitle, $to, $address, $contact, $drDate, $itemRows, $partsAmt, $labor, $others, $totalAmt, $preparedBy, $technician, $receivedBy) {
    ob_start();
    ?>
    <div class="dr-copy">
      <table class="dr-head">
        <tr>
          <td class="dr-shop">101 REPAIR SHOP<?php if ($copyTitle): ?><div class="dr-sub"><?php echo $copyTitle; ?></div><?php endif; ?></td>
          <td class="dr-title">DELIVERY / PULL OUT RECEIPT</td>
        </tr>
      </table>
      <table class="dr-info">
        <tr><td class="dr-to">TO: <span class="dr-val"><?php echo esc($to); ?></span></td><td class="dr-date">DATE: <span class="dr-val"><?php echo esc($drDate); ?></span></td></tr>
        <tr><td colspan="2">ADDRESS: <span class="dr-val"><?php echo esc($address); ?></span></td></tr>
        <tr><td colspan="2">CONTACT NO: <span class="dr-val"><?php echo esc($contact); ?></span></td></tr>
      </table>
      <div class="dr-wrty"><span class="box"></span> UNDER WRTY &nbsp;&nbsp;&nbsp; <span class="box"></span> OUT WRTY</div>
      <table class="dr-items">
        <colgroup><col style="width:8%"><col style="width:34%"><col style="width:28%"><col style="width:30%"></colgroup>
        <tr>
          <th>QTY</th><th>MODEL / SERIAL NO.</th><th>REMARKS</th><th>FOR IN-HOUSE REPAIR</th>
        </tr>
        <?php foreach ($itemRows as $idx => $r): ?>
        <tr>
          <td class="c"><?php echo esc($r['qty']); ?></td>
          <td><?php echo esc($r['model']); ?></td>
          <td><?php echo $idx === 0 ? esc($r['remarks']) : esc($r['remarks']); ?></td>
          <?php if ($idx === 0): ?>
            <td class="dr-amt-cell" rowspan="<?php echo count($itemRows); ?>">
              <div class="dr-amt"><span>PARTS</span><span><?php echo $partsAmt; ?></span></div>
              <div class="dr-amt"><span>LABOR</span><span><?php echo $labor; ?></span></div>
              <div class="dr-amt"><span>OTHERS</span><span><?php echo $others; ?></span></div>
              <div class="dr-amt dr-total"><span>TOTAL:</span><span><?php echo $totalAmt; ?></span></div>
            </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </table>
      <table class="dr-sign">
        <tr>
          <td>PREPARED BY: <span class="dr-val"><?php echo esc($preparedBy); ?></span></td>
          <td class="r">RECEIVED BY: <span class="dr-val"><?php echo esc($receivedBy); ?></span></td>
        </tr>
        <tr><td colspan="2">TECHNICIAN: <span class="dr-val"><?php echo esc($technician); ?></span></td></tr>
      </table>
    </div>
    <?php
    return ob_get_clean();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>D.R. - #<?php echo esc($data['report_id']); ?></title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Arial, sans-serif; color: #000; margin: 0; padding: 12px; font-size: 12px; }
  .no-print { margin-bottom: 10px; }
  .print-section { max-width: 800px; margin: 0 auto; }
  .dr-copy { padding: 6px 4px; }
  .dr-head { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
  .dr-shop { font-weight: bold; font-size: 16px; width: 45%; vertical-align: top; }
  .dr-sub { font-size: 9px; font-weight: normal; }
  .dr-title { font-weight: bold; font-size: 18px; text-align: left; vertical-align: top; padding-left: 10px; }
  .dr-info { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
  .dr-info td { padding: 1px 0; }
  .dr-to { width: 60%; }
  .dr-date { width: 40%; }
  .dr-val { font-weight: normal; }
  .dr-wrty { margin: 2px 0 4px 0; }
  .box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; vertical-align: middle; margin-right: 2px; }
  .dr-items { width: 100%; border-collapse: collapse; table-layout: fixed; }
  .dr-items th, .dr-items td { border: 1px solid #000; padding: 3px 4px; vertical-align: top; font-size: 11px; }
  .dr-items th { text-align: center; font-weight: bold; }
  .dr-items td.c { text-align: center; }
  .dr-amt-cell { vertical-align: top; }
  .dr-amt { display: flex; justify-content: space-between; padding: 2px 2px; font-size: 11px; }
  .dr-total { font-weight: bold; border-top: 1px solid #000; margin-top: 4px; padding-top: 3px; }
  .dr-sign { width: 100%; border-collapse: collapse; margin-top: 6px; }
  .dr-sign td { padding: 2px 0; }
  .dr-sign .r { text-align: right; }
  .dr-cut { border: none; border-top: 1px dashed #000; margin: 10px 0; }
  @media print {
    body { padding: 0; }
    .no-print { display: none !important; }
    .print-section { max-width: 100%; }
    .dr-cut { margin: 8px 0; }
  }
</style>
</head>
<body>
  <div class="print-section">
    <div class="no-print"><button id="print-btn" type="button" style="padding:6px 14px;cursor:pointer;">Print</button></div>
    <?php echo drCopy('', $to, $address, $contact, $drDate, $itemRows, $partsAmt, $labor, $others, $totalAmt, $preparedBy, $technician, $receivedBy); ?>
    <hr class="dr-cut">
    <?php echo drCopy('AUTHORIZED SERVICE CENTER', $to, $address, $contact, $drDate, $itemRows, $partsAmt, $labor, $others, $totalAmt, $preparedBy, $technician, $receivedBy); ?>
  </div>

<script>
(function() {
  var btn = document.getElementById('print-btn');
  if (btn) btn.addEventListener('click', function(){ window.print(); });
})();
</script>
</body>
</html>
