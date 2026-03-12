<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';

// التحقق من وجود معرف الطلب
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];

// جلب بيانات الطلب
try {
    $stmt = $pdo->prepare("
        SELECT o.*, os.status_name, os.status_color, os.status_icon 
        FROM orders o
        LEFT JOIN order_statuses os ON o.status = os.status_key
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php');
        exit();
    }

    // جلب جميع الحالات النشطة
    $active_statuses = $pdo->query("
        SELECT * FROM order_statuses 
        WHERE is_active = 1 
        ORDER BY display_order
    ")->fetchAll();

    // استرجاع التواريخ المجدولة
    $stmt = $pdo->prepare("
        SELECT status, scheduled_date 
        FROM order_status_dates 
        WHERE order_id = ?
    ");
    $stmt->execute([$order_id]);
    $scheduled_dates = [];
    while ($row = $stmt->fetch()) {
        $scheduled_dates[$row['status']] = $row['scheduled_date'];
    }
} catch (PDOException $e) {
    $error_message = "حدث خطأ في جلب بيانات الطلب";
}

// معالجة تحديث الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $tracking_number = $_POST['tracking_number'];
    $status = $_POST['status'];
    $price = $_POST['price'];
    $show_price = isset($_POST['show_price']) ? 1 : 0;
    $notes = $_POST['notes'];
    $show_notes = isset($_POST['show_notes']) ? 1 : 0;

    try {
        $pdo->beginTransaction();

        // تحديث الطلب الأساسي
        $stmt = $pdo->prepare("UPDATE orders SET 
            customer_name = ?, 
            tracking_number = ?, 
            status = ?, 
            price = ?, 
            show_price = ?, 
            notes = ?, 
            show_notes = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");

        $stmt->execute([$customer_name, $tracking_number, $status, $price, $show_price, $notes, $show_notes, $order_id]);

        // حذف التواريخ القديمة
        $stmt = $pdo->prepare("DELETE FROM order_status_dates WHERE order_id = ?");
        $stmt->execute([$order_id]);

        // إضافة التواريخ الجديدة
        $stmt = $pdo->prepare("INSERT INTO order_status_dates (order_id, status, scheduled_date) VALUES (?, ?, ?)");
        foreach ($active_statuses as $status) {
            $date_field_name = 'status_date_' . $status['status_key'];
            if (!empty($_POST[$date_field_name])) {
                $stmt->execute([$order_id, $status['status_key'], $_POST[$date_field_name]]);
            }
        }

        $pdo->commit();
        $success_message = "تم تحديث الطلب بنجاح";

        // تحديث البيانات المعروضة
        $stmt = $pdo->prepare("
            SELECT o.*, os.status_name, os.status_color, os.status_icon 
            FROM orders o
            LEFT JOIN order_statuses os ON o.status = os.status_key
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        // تحديث التواريخ المجدولة
        $stmt = $pdo->prepare("SELECT status, scheduled_date FROM order_status_dates WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $scheduled_dates = [];
        while ($row = $stmt->fetch()) {
            $scheduled_dates[$row['status']] = $row['scheduled_date'];
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "حدث خطأ أثناء تحديث الطلب: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الطلب | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link href="../css/edit_order.css" rel="stylesheet">
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

        /* Sidebar Styles */
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
            border-bottom: 1px solid rgba(255,255,255,0.1);
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
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-menu-link:hover,
        .sidebar-menu-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-menu-link i {
            margin-left: 0.8rem;
            font-size: 1.4rem;
        }

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .card-header {
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

        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }

        /* Status Timeline */
        .status-timeline {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
            color: white;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-date {
            font-size: 0.875rem;
            color: #6c757d;
        }

        /* Barcode Section */
        .barcode-section {
            margin-top: 2rem;
            text-align: center;
        }

        #qrcode {
            margin: 0 auto;
            max-width: 200px;
        }

        /* Mobile Toggle */
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

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive Design */
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

        /* Additional Styles */
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #eee;
        }

        .input-group-text i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        input[type="datetime-local"] {
            min-height: 45px;
        }
    </style>
</head>

<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="sidebarToggle">
        <i class='bx bx-menu'></i>
    </button>

    <!-- Sidebar -->
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
                <a href="orders.php" class="sidebar-menu-link active">
                    <i class='bx bx-package'></i>
                    إدارة الطلبات
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="add_order.php" class="sidebar-menu-link">
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-card">
                        <div class="card-header">
                            <h4 class="mb-0">تعديل الطلب #<?php echo htmlspecialchars($order['tracking_number']); ?></h4>
                            <div>
                                <a href="../track.php?id=<?php echo $order_id; ?>" class="btn btn-info me-2" target="_blank">
                                    <i class='bx bx-show'></i>
                                    معاينة صفحة التتبع
                                </a>
                                <a href="orders.php" class="btn btn-light">
                                    <i class='bx bx-arrow-back'></i>
                                    عودة للطلبات
                                </a>
                            </div>
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

                        <!-- Status Timeline -->
                        <div class="status-timeline">
                            <h5 class="mb-3">سجل الحالة</h5>
                            <div class="timeline-item">
                                <div class="timeline-icon bg-success">
                                    <i class='bx bx-plus'></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">تم إنشاء الطلب</h6>
                                    <div class="timeline-date">
                                        <i class='bx bx-calendar'></i>
                                        <?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-icon bg-info">
                                    <i class='bx bx-refresh'></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">آخر تحديث</h6>
                                    <div class="timeline-date">
                                        <i class='bx bx-calendar'></i>
                                        <?php echo date('Y/m/d H:i', strtotime($order['updated_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <form method="POST" id="editOrderForm" class="mt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">اسم العميل <small class="text-muted">(اختياري)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                                        <input type="text" name="customer_name" class="form-control"
                                            value="<?php echo htmlspecialchars($order['customer_name']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">رقم التتبع</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-hash'></i></span>
                                        <input type="text" name="tracking_number" class="form-control"
                                            value="<?php echo htmlspecialchars($order['tracking_number']); ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">حالة الطلب</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-list-check'></i></span>
                                        <select name="status" class="form-select" required>
                                            <?php foreach ($active_statuses as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status['status_key']); ?>"
                                                    <?php echo $order['status'] == $status['status_key'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">السعر</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-money'></i></span>
                                        <input type="number" name="price" class="form-control" step="0.01"
                                            value="<?php echo htmlspecialchars($order['price']); ?>" required>
                                        <span class="input-group-text">ريال</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" name="show_price" class="form-check-input" id="show_price"
                                            <?php echo $order['show_price'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="show_price">إظهار السعر للعميل</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">ملاحظات</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='bx bx-note'></i></span>
                                        <textarea name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($order['notes']); ?></textarea>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="show_notes" class="form-check-input" id="show_notes"
                                            <?php echo $order['show_notes'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="show_notes">إظهار الملاحظات للعميل</label>
                                    </div>
                                </div>

                                <!-- تواريخ الحالات المجدولة -->
                                <div class="col-12 mt-4">
                                    <h5>تواريخ الحالات المجدولة</h5>
                                    <div class="row g-3">
                                        <?php foreach ($active_statuses as $status): ?>
                                            <div class="col-md-6">
                                                <label class="form-label">
                                                    تاريخ <?php echo htmlspecialchars($status['status_name']); ?> المتوقع
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                        <i class='bx <?php echo htmlspecialchars($status['status_icon']); ?>'></i>
                                                    </span>
                                                    <input type="datetime-local"
                                                        name="status_date_<?php echo htmlspecialchars($status['status_key']); ?>"
                                                        class="form-control"
                                                        value="<?php
                                                                echo isset($scheduled_dates[$status['status_key']])
                                                                    ? date('Y-m-d\TH:i', strtotime($scheduled_dates[$status['status_key']]))
                                                                    : '';
                                                                ?>">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class='bx bx-save'></i>
                                        حفظ التغييرات
                                    </button>

                                </div>
                            </div>
                        </form>

                        <!-- Barcode Section -->
                        <div class="barcode-section">
                            <h5 class="mb-3">الباركود</h5>
                            <div id="qrcode"></div>
                            <p class="mt-3 mb-3" id="barcode-text"><?php echo htmlspecialchars($order['barcode']); ?></p>
                            <div class="btn-group">

                                <button class="btn btn-info" onclick="downloadBarcode()">
                                    <i class='bx bx-download'></i> تحميل الباركود
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');

            if (window.innerWidth <= 992 &&
                !sidebar.contains(event.target) &&
                !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Initialize QR Code
        document.addEventListener('DOMContentLoaded', function() {
            const qrcodeDiv = document.getElementById("qrcode");
            qrcodeDiv.innerHTML = '';

            new QRCode(qrcodeDiv, {
                text: "<?php echo $order['barcode']; ?>",
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Download Barcode
        function downloadBarcode() {
            const canvas = document.querySelector("#qrcode canvas");
            if (canvas) {
                const pngFile = canvas.toDataURL("image/png");
                const downloadLink = document.createElement("a");
                downloadLink.download = "barcode-<?php echo $order['barcode']; ?>.png";
                downloadLink.href = pngFile;
                downloadLink.click();
            }
        }

        // Form Validation
        document.getElementById('editOrderForm').addEventListener('submit', function(event) {
            const price = document.querySelector('input[name="price"]');
            if (price.value < 0) {
                event.preventDefault();
                alert('السعر يجب أن يكون أكبر من أو يساوي صفر');
            }

            // جمع كل حقول التواريخ
            const dateInputs = document.querySelectorAll('input[type="datetime-local"]');
            const dates = Array.from(dateInputs)
                .map(input => ({
                    name: input.name,
                    value: input.value
                }))
                .filter(date => date.value !== '');

            // التحقق من ترتيب التواريخ
            for (let i = 0; i < dates.length; i++) {
                for (let j = i + 1; j < dates.length; j++) {
                    if (new Date(dates[i].value) > new Date(dates[j].value)) {
                        event.preventDefault();
                        alert('يجب أن تكون التواريخ متسلسلة بشكل صحيح');
                        return;
                    }
                }
            }
        });

        
    </script>
</body>

</html>