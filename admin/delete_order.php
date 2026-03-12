<?php
session_start();
include '../config.php';

// تفعيل عرض الأخطاء للتطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من تسجيل دخول المدير
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// التحقق من وجود معرف الطلب
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];
$error = '';
$success = '';

try {
    // التحقق من وجود الطلب
    $stmt = $pdo->prepare("SELECT tracking_number FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php?error=not_found');
        exit();
    }

    // إذا تم تأكيد الحذف
    if (isset($_POST['confirm_delete'])) {
        try {
            $pdo->beginTransaction();

            // حذف السجلات المرتبطة
            $stmt = $pdo->prepare("DELETE FROM order_status_dates WHERE order_id = ?");
            $stmt->execute([$order_id]);

            $stmt = $pdo->prepare("DELETE FROM order_history WHERE order_id = ?");
            $stmt->execute([$order_id]);

            // حذف الطلب نفسه
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);

            $pdo->commit();

            // إعادة التوجيه بعد الحذف الناجح
            header('Location: orders.php?deleted=success');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "حدث خطأ أثناء حذف الطلب: " . $e->getMessage();
        }
    }
} catch (PDOException $e) {
    $error = "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف الطلب | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #4895ef;
            --dark-color: #240046;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
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

        /* Delete Card */
        .delete-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 2rem auto;
        }

        .delete-icon {
            width: 80px;
            height: 80px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }

        .delete-title {
            text-align: center;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        .delete-message {
            text-align: center;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
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

        @media (max-width: 576px) {
            .main-content {
                padding: 1rem;
            }

            .delete-card {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
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
        <div class="container">
            <div class="delete-card">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class='bx bx-error-circle me-2'></i>
                        <?php echo $error; ?>
                    </div>
                <?php else: ?>
                    <div class="delete-icon">
                        <i class='bx bx-trash'></i>
                    </div>
                    <h4 class="delete-title">حذف الطلب</h4>
                    <p class="delete-message">
                        هل أنت متأكد من حذف الطلب رقم 
                        <strong><?php echo htmlspecialchars($order['tracking_number']); ?></strong>؟
                        <br>
                        هذا الإجراء لا يمكن التراجع عنه.
                    </p>
                    <form method="POST" class="action-buttons">
                        <input type="hidden" name="confirm_delete" value="1">
                        <button type="submit" class="btn btn-danger">
                            <i class='bx bx-trash me-2'></i>
                            نعم، احذف الطلب
                        </button>
                        <a href="orders.php" class="btn btn-light">
                            <i class='bx bx-x me-2'></i>
                            إلغاء
                        </a>
                    </form>
                <?php endif; ?>
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
    </script>
</body>
</html>