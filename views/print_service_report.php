<?php
require_once __DIR__ . '/../bootstrap.php';
session_start();
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

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Service Report Print - #<?php echo esc($data['report_id']); ?></title>
<link rel="stylesheet" href="../css/bootstrap.min.css">
<link rel="stylesheet" href="../css/custom.css">
<style>
@media print {
    body * { visibility: hidden; }
    .print-section, .print-section * { visibility: visible; }
    .print-section { position: absolute; left: 0; top: 0; width: 100%; }
    /* Remove any background colors or shadows when printing so text is fully visible */
    .print-section, .print-section * {
        background: transparent !important;
        background-color: transparent !important;
        box-shadow: none !important;
    }
}

 .print-section { padding: 24px; background: #fff; }
.header-row { margin-bottom: 18px; }
.section { margin-top: 10px; }
.table th, .table td { vertical-align: middle; }
</style>
</head>
<body>
<div class="container print-section">
    <div class="row header-row">
        <div class="col-md-8">
            <h3 class="mb-0">Service Report</h3>
            <div>Report ID: <strong>#<?php echo esc($data['report_id']); ?></strong></div>
            <div>Date In: <strong><?php echo esc($data['date_in']); ?></strong></div>
        </div>
        <div class="col-md-4 text-right">
            <button id="print-btn" class="btn btn-primary mb-2">Print</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h5>Customer</h5>
            <div><?php echo esc($data['customer_name']); ?></div>
            <div>Appliance: <?php echo esc($data['appliance_name']); ?></div>
        </div>
        <div class="col-md-6">
            <h5>Status</h5>
            <div><?php echo esc($data['status']); ?></div>
            <div>Dealer: <?php echo esc($data['dealer']); ?></div>
        </div>
    </div>

    <div class="row section">
        <div class="col-md-12">
            <h5>Service Types</h5>
            <div><?php echo esc(implode(', ', $serviceTypes)); ?></div>
        </div>
    </div>

    <div class="row section">
        <div class="col-md-12">
            <h5>Findings / Remarks</h5>
            <div><?php echo esc($data['findings']); ?></div>
            <div class="text-muted small mt-1"><?php echo esc($data['remarks']); ?></div>
        </div>
    </div>

    <div class="row section">
        <div class="col-md-12">
            <h5>Parts Used</h5>
            <?php if (!empty($parts)) : ?>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Part Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $p) : ?>
                            <tr>
                                <td><?php echo esc($p['part_name']); ?></td>
                                <td><?php echo esc($p['quantity']); ?></td>
                                <td><?php echo number_format((float)($p['unit_price'] ?? 0), 2); ?></td>
                                <td><?php echo number_format(((float)($p['unit_price'] ?? 0) * (int)($p['quantity'] ?? 0)), 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div>No parts used</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row section">
        <div class="col-md-6">
            <h5>Charges</h5>
            <div>Labor: <strong>₱ <?php echo number_format((float)($data['labor'] ?? 0), 2);?></strong></div>
            <div>Pull-out Delivery: <strong>₱ <?php echo number_format((float)($data['pullout_delivery'] ?? 0), 2);?></strong></div>
            <div>Parts Charge: <strong>₱ <?php echo number_format((float)($data['parts_total_charge'] ?? 0), 2);?></strong></div>
            <div>Total: <strong>₱ <?php echo number_format((float)($data['total_amount'] ?? 0), 2);?></strong></div>
        </div>
        <div class="col-md-6">
            <h5>Staff & Dates</h5>
            <div>Repaired: <?php echo esc($data['date_repaired'] ?? ''); ?></div>
            <div>Delivered: <?php echo esc($data['date_delivered'] ?? ''); ?></div>
            <div class="mt-2">Technician: <?php echo esc($data['technician'] ?? ''); ?></div>
            <div>Manager: <?php echo esc($data['manager'] ?? ''); ?></div>
            <div>Receptionist: <?php echo esc($data['receptionist'] ?? ''); ?></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6 text-center">
            <div style="height:80px; border-bottom:1px solid #ccc; margin-bottom:6px;"></div>
            <div class="small">Technician</div>
        </div>
        <div class="col-md-6 text-center">
            <div style="height:80px; border-bottom:1px solid #ccc; margin-bottom:6px;"></div>
            <div class="small">Manager / Released By</div>
        </div>
    </div>

</div>
</div>

<script>
    (function() {
        const containerSelector = '.print-section';

        function applyPrintStyles() {
            const container = document.querySelector(containerSelector);
            if (!container) return;
            const nodes = container.querySelectorAll('*');
            nodes.forEach((el) => {
                // Save previous inline style so we can restore later
                try {
                    if (el.hasAttribute('style')) {
                        el.setAttribute('data-prev-style', el.getAttribute('style'));
                    } else {
                        el.setAttribute('data-prev-style', '');
                    }
                } catch (e) {
                    // ignore
                }

                // Force black text and disable visual filters/shadows for printing
                el.style.setProperty('color', '#000000', 'important');
                el.style.setProperty('-webkit-text-fill-color', '#000000', 'important');
                el.style.setProperty('-webkit-print-color-adjust', 'exact', 'important');
                el.style.setProperty('print-color-adjust', 'exact', 'important');
                el.style.setProperty('filter', 'none', 'important');
                el.style.setProperty('text-shadow', 'none', 'important');
                // Remove any background so it doesn't block text when printing
                el.style.setProperty('background', 'transparent', 'important');
                el.style.setProperty('background-color', 'transparent', 'important');
            });
            // Also ensure container itself
            try {
                const c = document.querySelector(containerSelector);
                if (c) {
                    if (c.hasAttribute('style')) c.setAttribute('data-prev-style', c.getAttribute('style'));
                    else c.setAttribute('data-prev-style', '');
                    c.style.setProperty('color', '#000000', 'important');
                    c.style.setProperty('-webkit-text-fill-color', '#000000', 'important');
                    c.style.setProperty('-webkit-print-color-adjust', 'exact', 'important');
                    c.style.setProperty('print-color-adjust', 'exact', 'important');
                    c.style.setProperty('background', 'transparent', 'important');
                }
            } catch (e){}
        }

        function removePrintStyles() {
            const container = document.querySelector(containerSelector);
            if (!container) return;
            const nodes = container.querySelectorAll('*');
            nodes.forEach((el) => {
                try {
                    const prev = el.getAttribute('data-prev-style');
                    if (prev === null || typeof prev === 'undefined') {
                        el.removeAttribute('style');
                    } else if (prev === '') {
                        el.removeAttribute('style');
                    } else {
                        el.setAttribute('style', prev);
                    }
                    el.removeAttribute('data-prev-style');
                } catch (e) {
                    // ignore
                }
            });
            try {
                const c = document.querySelector(containerSelector);
                if (c) {
                    const prev = c.getAttribute('data-prev-style');
                    if (prev === null || typeof prev === 'undefined' || prev === '') c.removeAttribute('style');
                    else c.setAttribute('style', prev);
                    c.removeAttribute('data-prev-style');
                }
            } catch (e){}
        }

        // Attach handlers for before/after print where supported
        if (window.matchMedia) {
            window.addEventListener('beforeprint', applyPrintStyles);
            window.addEventListener('afterprint', removePrintStyles);
        }

        // Button handler (covers the button click path)
        const printBtn = document.getElementById('print-btn');
        if (printBtn) {
            printBtn.addEventListener('click', function (e) {
                applyPrintStyles();
                // Give the browser a tiny moment to apply styles before opening print dialog
                setTimeout(function() {
                    try {
                        window.print();
                    } finally {
                        // Ensure styles are removed after a reasonable interval
                        setTimeout(removePrintStyles, 1200);
                    }
                }, 60);
            });
        }
    })();
</script>
</body>
</html>
