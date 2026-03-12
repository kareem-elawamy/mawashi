<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// جلب الطلبات مع معلومات الحالة
$stmt = $pdo->query("
    SELECT o.*, os.status_name, os.status_color, os.status_icon 
    FROM orders o
    LEFT JOIN order_statuses os ON o.status = os.status_key
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

// جلب جميع الحالات النشطة
$active_statuses = $pdo->query("
    SELECT * FROM order_statuses 
    WHERE is_active = 1 
    ORDER BY display_order
")->fetchAll();

// إحصائيات الحالات
$status_counts = [];
foreach ($active_statuses as $status) {
    $status_counts[$status['status_key']] = 0;
}

foreach ($orders as $order) {
    if (isset($status_counts[$order['status']])) {
        $status_counts[$order['status']]++;
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | نظام التتبع</title>
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
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            overflow-x: hidden;
        }

        /* Sidebar */
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

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 1rem;
        }

        .stat-card h3 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
        }

        /* Recent Orders */
        .recent-orders {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .recent-orders .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table th {
            font-weight: 600;
            padding: 1rem;
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

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .action-button {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .action-button:hover {
            transform: translateX(-5px);
            color: white;
        }

        .action-button i {
            font-size: 1.5rem;
            margin-left: 1rem;
        }

        /* Mobile Responsive */
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

            .stat-card {
                margin-bottom: 1rem;
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
                <a href="index.php" class="sidebar-menu-link active">
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
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="quick-actions">
                        <h5 class="mb-4">إجراءات سريعة</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <a href="add_order.php" class="action-button bg-primary">
                                    <i class='bx bx-plus'></i>
                                    إضافة طلب جديد
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="reports.php" class="action-button bg-success">
                                    <i class='bx bx-file'></i>
                                    تقرير اليوم
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="settings.php" class="action-button bg-info">
                                    <i class='bx bx-cog'></i>
                                    الإعدادات
                                </a>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="icon bg-primary text-white">
                            <i class='bx bx-package'></i>
                        </div>
                        <h3><?php echo count($orders); ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
                <?php foreach ($active_statuses as $index => $status): 
                    if ($index < 3): // عرض أول 3 حالات فقط ?>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="icon text-white" style="background-color: <?php echo $status['status_color']; ?>">
                                <i class='bx <?php echo $status['status_icon']; ?>'></i>
                            </div>
                            <h3><?php echo $status_counts[$status['status_key']] ?? 0; ?></h3>
                            <p><?php echo $status['status_name']; ?></p>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>

            <!-- Recent Orders -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="recent-orders">
                        <div class="header">
                            <h5 class="mb-0">آخر الطلبات</h5>
                            <a href="orders.php" class="btn btn-primary btn-sm">
                                عرض الكل <i class='bx bx-left-arrow-alt'></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>رقم التتبع</th>
                                        <th>اسم العميل</th>
                                        <th>الحالة</th>
                                        <th>السعر</th>
                                        <th>التاريخ</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recentOrders = array_slice($orders, 0, 5); // آخر 5 طلبات فقط
                                    foreach ($recentOrders as $order): 
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td>
                                            <span class="status-badge" 
                                                  style="background-color: <?php echo htmlspecialchars($order['status_color']); ?>">
                                                <i class='bx <?php echo htmlspecialchars($order['status_icon']); ?>'></i>
                                                <?php echo htmlspecialchars($order['status_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $order['show_price'] ? htmlspecialchars($order['price']) . ' ريال' : '-'; ?></td>
                                        <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_order.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class='bx bx-edit'></i>
                                                </a>
                                                <a href="../track.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   target="_blank">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
    </script>
</body>
</html>