<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// 1. كود الحذف الجماعي (النسخة النهائية)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    if (isset($_POST['order_ids']) && is_array($_POST['order_ids']) && count($_POST['order_ids']) > 0) {
        try {
            $idsToDelete = $_POST['order_ids'];
            $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));

            // نبدأ معاملة (Transaction)
            $pdo->beginTransaction();

            // 1. مسح البيانات من جدول التواريخ (السبب في الخطأ اللي ظهرلك)
            $stmtDates = $pdo->prepare("DELETE FROM order_status_dates WHERE order_id IN ($placeholders)");
            $stmtDates->execute($idsToDelete);

            // 2. مسح البيانات من جدول السجل التاريخي (احتياطي لو موجود)
            $stmtHistory = $pdo->prepare("DELETE FROM order_history WHERE order_id IN ($placeholders)");
            $stmtHistory->execute($idsToDelete);

            // 3. مسح الطلب نفسه أخيراً
            $stmtOrder = $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)");
            $stmtOrder->execute($idsToDelete);

            // اعتماد التغييرات
            $pdo->commit();

            header("Location: orders.php?msg=deleted");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            // لو ظهر خطأ تاني، اعرضه عشان نعرف اسم الجدول الباقي
            die("<div style='background:white; padding:20px; color:red; font-size:20px; text-align:center; direction:ltr;'>
                <strong>Still Error!</strong><br>
                " . $e->getMessage() . "
                </div>");
        }
    } else {
        echo "<script>alert('لم يتم تحديد أي طلبات للحذف');</script>";
    }
}

// ==========================================
// 2. كود تحديث الحالة الجماعي
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update_status'])) {
    if (isset($_POST['order_ids']) && !empty($_POST['new_status'])) {
        try {
            $idsToUpdate = $_POST['order_ids'];
            $newStatus = $_POST['new_status'];
            $placeholders = implode(',', array_fill(0, count($idsToUpdate), '?'));

            $pdo->beginTransaction();

            // تحديث حالة الطلبات
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
            // ندمج الحالة الجديدة مع مصفوفة المعرفات لتمريرها للـ execute
            $stmt->execute(array_merge([$newStatus], $idsToUpdate));

            // إضافة سجل في تاريخ الطلبات (History) لكل طلب تم تحديثه
            $historyStmt = $pdo->prepare("
                INSERT INTO order_history (order_id, status, icon, created_at) 
                SELECT id, status, (SELECT status_icon FROM order_statuses WHERE status_key = ? LIMIT 1), NOW()
                FROM orders WHERE id IN ($placeholders)
            ");
            $historyStmt->execute(array_merge([$newStatus], $idsToUpdate));

            $pdo->commit();
            header("Location: orders.php?msg=updated");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            die("خطأ في التحديث الجماعي: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('يرجى تحديد الطلبات واختيار الحالة الجديدة');</script>";
    }
}
// معالجة البحث
$search_query = '';
$orders = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = $search;

    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            os.status_name,
            os.status_color,
            os.status_icon
        FROM orders o
        LEFT JOIN order_statuses os ON o.status = os.status_key
        WHERE 
            o.tracking_number LIKE ? OR 
            o.customer_name LIKE ? OR 
            o.barcode LIKE ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $orders = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT 
            o.*,
            os.status_name,
            os.status_color,
            os.status_icon
        FROM orders o
        LEFT JOIN order_statuses os ON o.status = os.status_key
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
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

        /* Main Content */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        /* Search Container */
        .search-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .search-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-tab {
            padding: 0.8rem 1.5rem;
            border: none;
            background: none;
            color: #6c757d;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
            cursor: pointer;
        }

        .search-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .search-input {
            position: relative;
        }

        .search-input .form-control {
            padding: 0.75rem 1rem;
            padding-right: 3rem;
            border-radius: 8px;
            border: 2px solid #eee;
        }

        .search-input .search-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Orders Table */
        .orders-table {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table th {
            font-weight: 600;
            color: var(--dark-color);
            border-bottom-width: 1px;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge i {
            font-size: 1.1em;
        }

        /* QR Code Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 2rem;
            text-align: center;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            padding: 1.5rem;
        }

        #qrcode {
            margin: 0 auto;
            max-width: 200px;
        }

        /* Action Buttons */
        .btn-group .btn {
            padding: 0.5rem;
            border-radius: 8px;
            margin: 0 0.2rem;
        }

        .btn-group .btn i {
            font-size: 1.2rem;
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

            .search-tabs {
                flex-direction: column;
            }

            .search-tab {
                width: 100%;
                text-align: center;
            }

            .orders-table {
                padding: 1rem;
            }

            .table-responsive {
                margin: 0 -1rem;
            }
        }

        /* QR Scanner */
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        #qr-reader__scan_region {
            background: white;
        }

        #qr-reader__dashboard {
            padding: 1rem;
        }

        /* Custom Checkbox Style */
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            cursor: pointer;
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

    <div class="main-content">
        <div class="container-fluid">
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    تم تحديث حالة الطلبات المحددة بنجاح.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="search-container">
                <h4 class="mb-4">البحث عن الطلبات</h4>

                <div class="search-tabs">
                    <button class="search-tab active" data-tab="text">
                        <i class='bx bx-search'></i>
                        بحث نصي
                    </button>
                    <button class="search-tab" data-tab="barcode">
                        <i class='bx bx-barcode'></i>
                        مسح الباركود
                    </button>
                </div>

                <div id="text-search" class="search-panel">
                    <form method="GET" class="mb-4">
                        <div class="search-input">
                            <i class='bx bx-search search-icon'></i>
                            <input type="text" name="search" class="form-control"
                                placeholder="ابحث برقم التتبع، اسم العميل، أو الباركود"
                                value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                    </form>
                </div>

                <div id="barcode-reader" class="search-panel" style="display: none;">
                    <div id="qr-reader"></div>
                </div>
            </div>

            <div class="orders-table">
                <form method="POST" id="bulkDeleteForm">

                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <h4 class="mb-0">قائمة الطلبات</h4>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="width: auto;">
                                <select name="new_status" class="form-select" style="min-width: 150px;">
                                    <option value="">اختر حالة جديدة...</option>
                                    <?php
                                    // جلب الحالات المتاحة من قاعدة البيانات
                                    $status_list = $pdo->query("SELECT status_key, status_name FROM order_statuses ORDER BY display_order")->fetchAll();
                                    foreach ($status_list as $st) {
                                        echo "<option value='" . htmlspecialchars($st['status_key']) . "'>" . htmlspecialchars($st['status_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="bulk_update_status" class="btn btn-warning">
                                    <i class='bx bx-refresh'></i> تحديث الحالة
                                </button>
                            </div>

                            <div class="vr mx-2"></div> <button type="submit" name="bulk_delete" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف الطلبات المحددة؟')">
                                <i class='bx bx-trash'></i> حذف المحدد
                            </button>

                            <a href="add_order.php" class="btn btn-primary">
                                <i class='bx bx-plus'></i> إضافة طلب
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>رقم التتبع</th>
                                    <th>اسم العميل</th>
                                    <th>الباركود</th>
                                    <th>الحالة</th>
                                    <th>السعر</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>آخر تحديث</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class='bx bx-package' style="font-size: 40px; color: #ddd;"></i>
                                            <p class="mb-0 mt-2">لا توجد طلبات</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" class="form-check-input order-checkbox">
                                            </td>
                                            <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-light" onclick="showBarcode('<?php echo htmlspecialchars($order['barcode']); ?>')">
                                                    <i class='bx bx-qr'></i>
                                                    عرض الباركود
                                                </button>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColor = $order['status_color'] ?? '#6c757d';
                                                $statusIcon = $order['status_icon'] ?? 'bx-help-circle';
                                                $statusName = $order['status_name'] ?? $order['status'];
                                                ?>
                                                <span class="status-badge" style="background-color: <?php echo htmlspecialchars($statusColor); ?>">
                                                    <i class='bx <?php echo htmlspecialchars($statusIcon); ?>'></i>
                                                    <?php echo htmlspecialchars($statusName); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $order['show_price'] ? htmlspecialchars($order['price']) . ' ريال' : '-'; ?>
                                            </td>
                                            <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo date('Y/m/d H:i', strtotime($order['updated_at'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit_order.php?id=<?php echo $order['id']; ?>"
                                                        class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class='bx bx-edit'></i>
                                                    </a>
                                                    <a href="../track.php?id=<?php echo $order['id']; ?>"
                                                        class="btn btn-sm btn-info"
                                                        target="_blank"
                                                        title="عرض صفحة التتبع">
                                                        <i class='bx bx-show'></i>
                                                    </a>
                                                    <a href="delete_order.php?id=<?php echo $order['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')"
                                                        title="حذف">
                                                        <i class='bx bx-trash'></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="barcodeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">الباركود</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="qrcode"></div>
                    <p class="text-center mt-3" id="barcode-text"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="downloadBarcode()">
                        <i class='bx bx-download'></i> تحميل
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 3. Select All Script: كود تحديد الكل
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

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

        // Search Tabs
        const searchTabs = document.querySelectorAll('.search-tab');
        const searchPanels = document.querySelectorAll('.search-panel');
        let html5QrcodeScanner = null;

        searchTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                searchTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                searchPanels.forEach(panel => panel.style.display = 'none');

                if (this.dataset.tab === 'text') {
                    document.getElementById('text-search').style.display = 'block';
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.clear();
                        html5QrcodeScanner = null;
                    }
                } else {
                    document.getElementById('barcode-reader').style.display = 'block';
                    initBarcodeScanner();
                }
            });
        });

        // Initialize Barcode Scanner
        function initBarcodeScanner() {
            if (!html5QrcodeScanner) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                        aspectRatio: 1.0
                    }
                );

                html5QrcodeScanner.render((decodedText) => {
                    // هنا الذكاء: لو الماسح قرا رابط كامل، هناخد منه الكود بس
                    let searchCode = decodedText;

                    // لو النص المقروء فيه كلمة search= يبقى ده رابط، نفككه وناخد آخره
                    if (decodedText.includes("search=")) {
                        searchCode = decodedText.split("search=")[1];
                    }

                    // التوجيه لصفحة البحث بالكود الصافي
                    window.location.href = `?search=${searchCode}`;
                });
            }
        }

        // Barcode Modal Functions
        const barcodeModal = new bootstrap.Modal(document.getElementById('barcodeModal'));
        let currentBarcode = '';

        function showBarcode(barcode) {
            currentBarcode = barcode;
            const qrcodeDiv = document.getElementById("qrcode");
            qrcodeDiv.innerHTML = '';

            // 1. تحديد الرابط
            let baseUrl = window.location.origin + "/khaledf";

            // 2. الرابط الكامل
            const fullLink = `${baseUrl}/track.php?search=${barcode}`;

            new QRCode(qrcodeDiv, {
                text: fullLink,
                width: 250, // كبرنا الحجم شوية
                height: 250,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.L // <--- غيرنا دي لـ L عشان الرمز يبقى أخف وأسرع في القراءة
            });

            document.getElementById("barcode-text").textContent = barcode;
            barcodeModal.show();
        }

        function downloadBarcode() {
            const canvas = document.querySelector("#qrcode canvas");
            if (canvas) {
                const pngFile = canvas.toDataURL("image/png");
                const downloadLink = document.createElement("a");
                downloadLink.download = `barcode-${currentBarcode}.png`;
                downloadLink.href = pngFile;
                downloadLink.click();
            }
        }

        // Clean up scanner when leaving page
        window.addEventListener('beforeunload', () => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
        });
    </script>
</body>

</html>