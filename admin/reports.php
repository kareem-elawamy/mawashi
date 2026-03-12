<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// التاريخ الافتراضي هو اليوم
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب الحالات النشطة
$active_statuses = $pdo->query("
    SELECT * FROM order_statuses 
    WHERE is_active = 1 
    ORDER BY display_order
")->fetchAll();

// استعلام الطلبات
$query = "
    SELECT o.*, os.status_name, os.status_color, os.status_icon 
    FROM orders o
    LEFT JOIN order_statuses os ON o.status = os.status_key
    WHERE DATE(o.created_at) BETWEEN ? AND ?
";
$params = [$start_date, $end_date];

if ($status !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// إحصائيات
$total_orders = count($orders);
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
    <title>التقارير | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Chart Card */
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .table th {
            font-weight: 600;
            color: var(--dark-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
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

        /* Print Styles */
        @media print {
            .sidebar, .mobile-toggle, .filter-card, .no-print {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            .stat-card, .chart-card, .table-card {
                break-inside: avoid;
            }
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

            .stat-card {
                margin-bottom: 1rem;
            }

            .filter-card form {
                flex-direction: column;
            }

            .filter-card .form-group {
                width: 100%;
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
                <a href="add_order.php" class="sidebar-menu-link">
                    <i class='bx bx-plus-circle'></i>
                    إضافة طلب
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="reports.php" class="sidebar-menu-link active">
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
            <!-- Filter Section -->
            <div class="filter-card">
                <form method="GET" class="d-flex gap-3 align-items-end">
                    <div class="form-group">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="all">جميع الحالات</option>
                            <?php foreach ($active_statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status['status_key']); ?>"
                                        <?php echo $status['status_key'] == $_GET['status'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-filter-alt'></i>
                            تصفية
                        </button>
                        <button type="button" class="btn btn-success ms-2" onclick="window.print()">
                            <i class='bx bx-printer'></i>
                            طباعة التقرير
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="icon bg-primary text-white">
                            <i class='bx bx-package'></i>
                        </div>
                        <h3><?php echo $total_orders; ?></h3>
                        <p>إجمالي الطلبات</p>
                    </div>
                </div>
                <?php 
                $i = 0;
                foreach ($active_statuses as $status):
                    if ($i < 3): // عرض أول 3 حالات فقط
                ?>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="icon text-white" style="background-color: <?php echo htmlspecialchars($status['status_color']); ?>">
                                <i class='bx <?php echo htmlspecialchars($status['status_icon']); ?>'></i>
                            </div>
                            <h3><?php echo $status_counts[$status['status_key']] ?? 0; ?></h3>
                            <p><?php echo htmlspecialchars($status['status_name']); ?></p>
                        </div>
                    </div>
                <?php 
                    endif;
                    $i++;
                endforeach; 
                ?>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-12">
                    <div class="chart-card">
                        <h5 class="mb-4">توزيع حالات الطلبات</h5>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-card">
                <h5 class="mb-4">تفاصيل الطلبات</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>رقم التتبع</th>
                                <th>اسم العميل</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>آخر تحديث</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>
                                    <span class="status-badge text-white" 
                                          style="background-color: <?php echo htmlspecialchars($order['status_color']); ?>">
                                        <i class='bx <?php echo htmlspecialchars($order['status_icon']); ?>'></i>
                                        <?php echo htmlspecialchars($order['status_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                                <td><?php echo date('Y/m/d H:i', strtotime($order['updated_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

        // Initialize Charts
        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: [<?php echo implode(', ', array_map(function($status) {
                    return "'" . addslashes($status['status_name']) . "'";
                }, $active_statuses)); ?>],
                datasets: [{
                    data: [<?php echo implode(', ', array_map(function($status) use ($status_counts) {
                        return $status_counts[$status['status_key']] ?? 0;
                    }, $active_statuses)); ?>],
                    backgroundColor: [<?php echo implode(', ', array_map(function($status) {
                        return "'" . addslashes($status['status_color']) . "'";
                    }, $active_statuses)); ?>]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Initialize Date Pickers
        flatpickr("input[type=date]", {
            dateFormat: "Y-m-d",
            locale: {
                firstDayOfWeek: 6
            }
        });
    </script>
</body>
</html>