<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

function generateTrackingNumber()
{
    return 'TRK' . date('Ymd') . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
}

function generateBarcode()
{
    return 'BC' . time() . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
}

$active_statuses = $pdo->query("
    SELECT * FROM order_statuses 
    WHERE is_active = 1 
    ORDER BY display_order
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $order_type = $_POST['order_type'] ?? 'single';
        $initial_status = $_POST['status']; // تخزين الحالة الأولية

        if ($order_type === 'single') {
            // معالجة الطلب الفردي
            $customer_name = $_POST['customer_name'] ?? null;
            $tracking_number = $_POST['tracking_number'] ?: generateTrackingNumber();
            $barcode = generateBarcode();
            $status = $initial_status; // استخدام الحالة الأولية
            $price = $_POST['price'];
            $show_price = isset($_POST['show_price']) ? 1 : 0;
            $notes = $_POST['notes'];
            $show_notes = isset($_POST['show_notes']) ? 1 : 0;

            $stmt = $pdo->prepare("INSERT INTO orders (tracking_number, customer_name, barcode, status, price, show_price, notes, show_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tracking_number, $customer_name, $barcode, $status, $price, $show_price, $notes, $show_notes]);

            $order_id = $pdo->lastInsertId();

            // إضافة الحالة الأولية إلى سجل الطلب
            $history_stmt = $pdo->prepare("
                INSERT INTO order_history (order_id, status, icon) 
                SELECT ?, ?, status_icon 
                FROM order_statuses 
                WHERE status_key = ?
            ");
            $history_stmt->execute([$order_id, $status, $status]);

            // إضافة تواريخ الحالات
            foreach ($active_statuses as $status_item) {
                $date_field_name = 'status_date_' . $status_item['status_key'];
                if (!empty($_POST[$date_field_name])) {
                    $date_stmt = $pdo->prepare("INSERT INTO order_status_dates (order_id, status, scheduled_date) VALUES (?, ?, ?)");
                    $date_stmt->execute([$order_id, $status_item['status_key'], $_POST[$date_field_name]]);
                }
            }
        } else {
            // معالجة الطلبات المجمعة
            $customer_name = $_POST['customer_name'] ?? null;
            $order_count = intval($_POST['order_count']);
            $tracking_numbers = $_POST['tracking_numbers'] ?? [];
            $status = $initial_status; // استخدام الحالة الأولية
            $price = $_POST['price'];
            $show_price = isset($_POST['show_price']) ? 1 : 0;
            $notes = $_POST['notes'];
            $show_notes = isset($_POST['show_notes']) ? 1 : 0;

            $created_orders = [];

            for ($i = 0; $i < $order_count; $i++) {
                $tracking_number = !empty($tracking_numbers[$i]) ? $tracking_numbers[$i] : generateTrackingNumber();
                $barcode = generateBarcode();

                $stmt = $pdo->prepare("INSERT INTO orders (tracking_number, customer_name, barcode, status, price, show_price, notes, show_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tracking_number, $customer_name, $barcode, $status, $price, $show_price, $notes, $show_notes]);

                $order_id = $pdo->lastInsertId();

                // إضافة الحالة الأولية إلى سجل الطلب
                $history_stmt = $pdo->prepare("
                    INSERT INTO order_history (order_id, status, icon) 
                    SELECT ?, ?, status_icon 
                    FROM order_statuses 
                    WHERE status_key = ?
                ");
                $history_stmt->execute([$order_id, $status, $status]);

                // إضافة تواريخ الحالات
                foreach ($active_statuses as $status_item) {
                    $date_field_name = 'status_date_' . $status_item['status_key'];
                    if (!empty($_POST[$date_field_name])) {
                        $date_stmt = $pdo->prepare("INSERT INTO order_status_dates (order_id, status, scheduled_date) VALUES (?, ?, ?)");
                        $date_stmt->execute([$order_id, $status_item['status_key'], $_POST[$date_field_name]]);
                    }
                }

                $created_orders[] = [
                    'tracking_number' => $tracking_number,
                    'barcode' => $barcode,
                    'order_id' => $order_id
                ];
            }

            $_SESSION['created_orders'] = $created_orders;
        }

        $pdo->commit();
        $_SESSION['success_message'] = "تم إضافة الطلب/الطلبات بنجاح";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "حدث خطأ أثناء إضافة الطلب: " . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

$success_message = '';
$error_message = '';
$last_order_barcode = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    $last_order_barcode = $_SESSION['last_order_barcode'] ?? '';
    unset($_SESSION['success_message']);
    unset($_SESSION['last_order_barcode']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة طلب جديد | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --dark-color: #240046;
            --light-color: #f8f9fa;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            background: linear-gradient(180deg, var(--dark-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand h3 {
            color: white;
            font-size: 1.5rem;
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-menu-link:hover,
        .sidebar-menu-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar-menu-link i {
            margin-left: 0.8rem;
            font-size: 1.4rem;
        }

        .main-content {
            margin-right: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .form-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .form-control,
        .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .form-switch {
            padding-right: 2.5em;
        }

        .form-switch .form-check-input {
            height: 1.5em;
            width: 2.75em;
            margin-right: -2.5em;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .barcode-container {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }

        .barcode-container #qrcode {
            margin: 0 auto;
            max-width: 200px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-light {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: var(--dark-color);
        }

        .btn-light:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .mobile-toggle {
            display: none;
            position: fixed;
            right: 1rem;
            top: 1rem;
            z-index: 1001;
            background: var(--primary-color);
            border: none;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            font-size: 1.5rem;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                right: -280px;
            }

            .sidebar.active {
                right: 0;
            }

            .main-content {
                margin-right: 0;
            }

            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .card-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        @media print {

            .sidebar,
            .mobile-toggle,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }

            .barcode-container {
                border: none;
            }
        }

        .order-type-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .order-type-tabs button {
            padding: 1rem 2rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-type-tabs button i {
            font-size: 1.2rem;
        }

        .order-type-tabs button.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        #tracking-numbers-container {
            transition: all 0.3s ease;
        }

        .tracking-input[readonly] {
            background-color: #ffffff;
            border-color: #d1d1d1;
            color: #555;
            font-size: 0.8rem;
            cursor: not-allowed;
        }

        #generated-inputs-grid {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }
    </style>
</head>

<body>
    <button class="mobile-toggle" id="sidebarToggle">
        <i class='bx bx-menu'></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h3><i class='bx bx-package'></i> نظام التتبع</h3>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="index.php" class="sidebar-menu-link">
                    <i class='bx bx-grid-alt'></i>
                    لوحة التحكم
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="orders.php" class="sidebar-menu-link">
                    <i class='bx bx-package'></i>
                    إدارة الطلبات
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="add_order.php" class="sidebar-menu-link active">
                    <i class='bx bx-plus-circle'></i>
                    إضافة طلب
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="reports.php" class="sidebar-menu-link">
                    <i class='bx bx-bar-chart'></i>
                    التقارير
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="settings.php" class="sidebar-menu-link">
                    <i class='bx bx-cog'></i>
                    الإعدادات
                </a>
            </li>
            <li class="sidebar-menu-item mt-auto">
                <a href="logout.php" class="sidebar-menu-link text-danger">
                    <i class='bx bx-log-out'></i>
                    تسجيل الخروج
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-card">
                        <div class="card-header">
                            <h4 class="mb-0">إضافة طلب جديد</h4>
                            <a href="orders.php" class="btn btn-light">
                                <i class='bx bx-arrow-back'></i>
                                عودة للطلبات
                            </a>
                        </div>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class='bx bx-check-circle me-2'></i>
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class='bx bx-error-circle me-2'></i>
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- أزرار التبديل بين أنواع الطلبات -->
                        <div class="order-type-tabs mb-4">
                            <button type="button" class="btn btn-outline-primary active" data-type="single">
                                <i class='bx bx-package'></i> طلب فردي
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-type="bulk">
                                <i class='bx bx-packages'></i> طلبات مجمعة
                            </button>
                        </div>

                        <!-- نموذج الطلب الفردي -->
                        <form method="POST" id="singleOrderForm" class="order-form">
                            <input type="hidden" name="order_type" value="single">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">اسم العميل (اختياري)</label>
                                    <input type="text" name="customer_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم التتبع (اختياري)</label>
                                    <input type="text" name="tracking_number" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">حالة الطلب</label>
                                    <select name="status" class="form-select" required>
                                        <?php foreach ($active_statuses as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status['status_key']); ?>">
                                                <?php echo htmlspecialchars($status['status_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">السعر</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                    <span class="input-group-text">ريال</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" name="show_price" class="form-check-input" id="show_price" checked>
                                        <label class="form-check-label" for="show_price">إظهار السعر للعميل</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="4"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="show_notes" class="form-check-input" id="show_notes" checked>
                                        <label class="form-check-label" for="show_notes">إظهار الملاحظات للعميل</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <h5>تواريخ الحالات المجدولة</h5>
                                    <table class="table table-bordered" id="statusesTable">
                                        <thead>
                                            <tr>
                                                <th>الحالة</th>
                                                <th>التاريخ المتوقع</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($active_statuses as $status): ?>
                                                <tr data-status-id="<?php echo $status['id']; ?>">
                                                    <td><?php echo htmlspecialchars($status['status_name']); ?></td>
                                                    <td>
                                                        <input type="datetime-local"
                                                            name="status_date_<?php echo htmlspecialchars($status['status_key']); ?>"
                                                            class="form-control">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class='bx bx-plus'></i> إضافة الطلب
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- نموذج الطلبات المجمعة -->
                        <form method="POST" id="bulkOrderForm" class="order-form" style="display: none;">
                            <input type="hidden" name="order_type" value="bulk">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">اسم العميل (اختياري)</label>
                                    <input type="text" name="customer_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">عدد الطلبات</label>
                                    <input type="number" name="order_count" class="form-control" min="1" required>
                                </div>
                                <div class="col-12" id="tracking-numbers-container">
                                    <!-- سيتم إضافة حقول أرقام التتبع هنا ديناميكياً -->
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">حالة الطلب</label>
                                    <select name="status" class="form-select" required>
                                        <?php foreach ($active_statuses as $status): ?>
                                            <option value="<?php echo htmlspecialchars($status['status_key']); ?>">
                                                <?php echo htmlspecialchars($status['status_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">السعر</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                    <span class="input-group-text">ريال</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" name="show_price" class="form-check-input" id="show_price_bulk" checked>
                                        <label class="form-check-label" for="show_price_bulk">إظهار السعر للعميل</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="4"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="show_notes" class="form-check-input" id="show_notes_bulk" checked>
                                        <label class="form-check-label" for="show_notes_bulk">إظهار الملاحظات للعميل</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <h5>تواريخ الحالات المجدولة</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="statusesTable">
                                            <thead>
                                                <tr>
                                                    <th>الحالة</th>
                                                    <th>التاريخ المتوقع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($active_statuses as $status): ?>
                                                    <tr data-status-id="<?php echo $status['id']; ?>">
                                                        <td><?php echo htmlspecialchars($status['status_name']); ?></td>
                                                        <td>
                                                            <input type="datetime-local"
                                                                name="status_date_<?php echo htmlspecialchars($status['status_key']); ?>"
                                                                class="form-control">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class='bx bx-plus'></i> إضافة الطلبات
                                    </button>
                                </div>
                            </div>
                        </form>

                        <?php if (isset($_SESSION['created_orders'])): ?>
                            <div class="barcode-container">
                                <h4 class="mb-4">الباركود للطلبات المنشأة</h4>
                                <div class="row g-4">
                                    <?php foreach ($_SESSION['created_orders'] as $order): ?>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <div id="qrcode-<?php echo $order['order_id']; ?>"></div>
                                                    <p class="mt-3">رقم التتبع: <?php echo $order['tracking_number']; ?></p>
                                                    <button class="btn btn-sm btn-primary"
                                                        onclick="downloadBarcode('<?php echo $order['order_id']; ?>', '<?php echo $order['tracking_number']; ?>')">
                                                        <i class='bx bx-download'></i> تحميل
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php unset($_SESSION['created_orders']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (window.innerWidth <= 992 &&
                !sidebar.contains(event.target) &&
                !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const orderTypeTabs = document.querySelectorAll('.order-type-tabs button');
            const orderForms = document.querySelectorAll('.order-form');

            orderTypeTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    orderTypeTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    const type = this.dataset.type;
                    orderForms.forEach(form => {
                        form.style.display = form.id === `${type}OrderForm` ? 'block' : 'none';
                    });
                });
            });

            const orderCountInput = document.querySelector('input[name="order_count"]');
            const trackingNumbersContainer = document.getElementById('tracking-numbers-container');

            orderCountInput?.addEventListener('input', function() {
                const count = parseInt(this.value) || 0;
                trackingNumbersContainer.innerHTML = '';

                if (count > 0) {
                    const div = document.createElement('div');
                    div.className = 'mb-3 p-3 border rounded bg-light';
                    div.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">رقم التتبع الأول (بداية التسلسل)</label>
                    <input type="text" id="start_tracking" class="form-control" placeholder="مثلاً: TRK2026001">
                    <small class="text-muted">سيتم توليد بقية الأرقام تلقائياً بناءً على هذا الرقم</small>
                </div>
            </div>
            <label class="form-label">الأرقام المتسلسلة التي سيتم إنشاؤها:</label>
            <div class="row g-2" id="generated-inputs-grid">
                ${Array(count).fill(0).map((_, i) => `
                    <div class="col-md-3">
                        <input type="text" name="tracking_numbers[]" 
                               class="form-control form-control-sm tracking-input" 
                               readonly placeholder="سيتم التوليد...">
                    </div>
                `).join('')}
            </div>
        `;
                    trackingNumbersContainer.appendChild(div);

                    // إضافة حدث عند كتابة أول رقم تتبع
                    document.getElementById('start_tracking').addEventListener('input', function() {
                        const startVal = this.value;
                        const inputs = document.querySelectorAll('.tracking-input');

                        // استخراج الجزء الرقمي من نهاية النص (مثلاً 001 من TRK001)
                        const match = startVal.match(/^(.*?)(\d+)$/);

                        if (match) {
                            const prefix = match[1];
                            const startNum = parseInt(match[2]);
                            const numLength = match[2].length;

                            inputs.forEach((input, index) => {
                                const currentNum = startNum + index;
                                // إضافة الأصفار على اليسار للحفاظ على نفس طول الرقم الأصلي
                                const paddedNum = currentNum.toString().padStart(numLength, '0');
                                input.value = prefix + paddedNum;
                            });
                        } else {
                            // إذا لم يوجد رقم في النهاية، نكرر النص فقط أو نتركه
                            inputs.forEach((input, index) => {
                                input.value = startVal ? startVal + (index + 1) : '';
                            });
                        }
                    });
                }
            });

            initializeQRCodes();
        });

        function initializeQRCodes() {
            document.querySelectorAll('[id^="qrcode-"]').forEach(div => {
                const orderId = div.id.split('-')[1];
                const trackingNumber = div.nextElementSibling.textContent.split(': ')[1];

                new QRCode(div, {
                    text: trackingNumber,
                    width: 128,
                    height: 128
                });
            });
        }

        function downloadBarcode(orderId, trackingNumber) {
            const canvas = document.querySelector(`#qrcode-${orderId} canvas`);
            if (canvas) {
                const link = document.createElement('a');
                link.download = `barcode-${trackingNumber}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            }
        }
    </script>
</body>

</html>