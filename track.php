<?php
include 'config.php';

// 1. جلب الإعدادات (دمج الاستعلامات لتقليل الضغط على القاعدة)
try {
    $stmt = $pdo->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $pageSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    $track_icons = json_decode($pageSettings['track_status_icons'] ?? '{}', true);
} catch (PDOException $e) {
    $pageSettings = ['site_logo' => '', 'track_scheduled_date_text' => 'التاريخ المجدول'];
    $track_icons = [];
}

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['search'])) {
    $search = $_POST['search'] ?? $_GET['search'] ?? '';

    // 2. البحث عن الطلب
    $stmt = $pdo->prepare("
        SELECT o.*, os.display_order as current_order
        FROM orders o
        LEFT JOIN order_statuses os ON o.status = os.status_key
        WHERE o.tracking_number = ? OR o.customer_name = ? OR o.barcode = ?
    ");
    $stmt->execute([$search, $search, $search]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // 3. جلب جميع الحالات النشطة (للمقارنة والترتيب)
        $status_stmt = $pdo->prepare("
            SELECT os.*, osd.scheduled_date
            FROM order_statuses os
            LEFT JOIN order_status_dates osd ON osd.status = os.status_key AND osd.order_id = ?
            WHERE os.is_active = 1 
            ORDER BY os.display_order ASC
        ");
        $status_stmt->execute([$result['id']]);
        $active_statuses = $status_stmt->fetchAll();
    } else {
        $error = "لم يتم العثور على الطلب";
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تتبع طلبك | نظام التتبع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        :root {
            --primary-color: #00B074;
            --secondary-color: #162E66;
            --orange-main: #fd7e14;
            --completed-color: #28a745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            background-image: url('https://img.freepik.com/free-vector/abstract-background-with-squares_23-2148995948.jpg');
            background-size: cover;
            background-attachment: fixed;
        }

        /* الكروت والبحث */
        .track-container {
            max-width: 800px;
            margin: 100px auto 30px;
        }

        .track-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .track-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 30px;
            color: white;
            text-align: center;
        }

        .search-form .form-control {
            height: 60px;
            border-radius: 10px;
            padding-right: 20px;
        }

        .search-tab {
            padding: 10px 20px;
            border: none;
            background: none;
            color: #666;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }

        .search-tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        /* التصميم العمودي للـ Timeline */
        .status-timeline-vertical {
            position: relative;
            padding-right: 30px;
            margin-top: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 40px;
            padding-right: 35px;
            border-right: 3px solid #e9ecef;
            /* الخط الرمادي الافتراضي */
        }

        .timeline-item.completed {
            border-right-color: var(--completed-color);
        }

        .timeline-item:last-child {
            border-right-color: transparent;
        }

        .timeline-dot {
            position: absolute;
            right: -13.5px;
            top: 0;
            width: 24px;
            height: 24px;
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }

        /* ألوان الحالات */
        .timeline-item.completed .timeline-dot {
            background-color: var(--completed-color);
            border-color: var(--completed-color);
            color: white;
        }

        .timeline-item.active .timeline-dot {
            border-color: var(--orange-main);
        }

        .pulse-dot {
            width: 12px;
            height: 12px;
            background-color: var(--orange-main);
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
                opacity: 1;
            }

            70% {
                transform: scale(1.5);
                opacity: 0;
            }

            100% {
                transform: scale(0.9);
                opacity: 0;
            }
        }

        .bg-orange-light {
            background-color: #fff3e0;
            color: var(--orange-main);
            padding: 2px 10px;
            border-radius: 5px;
        }

        .text-orange {
            color: var(--orange-main);
        }

        button {
            transition: all 0.2s;
            border-radius: 10px;
            background-color: var(--primary-color);
        }

        button:focus {
            outline: none;
            box-shadow: none;
        }

        button:active {
            transform: scale(0.98);
        }

        button:hover {
            opacity: 0.9;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        button:disabled:hover {
            opacity: 0.6;
        }

        button i {
            transition: transform 0.2s;

        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <?php if (!empty($pageSettings['site_logo'])): ?>
                    <img src="uploads/<?= $pageSettings['site_logo'] ?>" style="height: 40px;">
                <?php else: ?>
                    <i class='bx bx-package'></i> نظام التتبع
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="track-container">
        <div class="track-card">
            <div class="track-header">
                <h2> تتبع طلبك</h2>
                <p>أدخل بيانات الشحنة أدناه</p>
            </div>

            <div class="track-body p-4">
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <button class="search-tab active" onclick="switchTab('text')">بحث نصي</button>
                    <button class="search-tab" onclick="switchTab('barcode')">مسح باركود</button>
                </div>

                <div id="text-search">
                    <form method="POST" class="position-relative mb-4">
                        <input type="text" name="search" class="form-control shadow-sm" placeholder="رقم التتبع أو اسم العميل..." required>
                        <button type="submit" class="btn btn-primary position-absolute start-0 top-0 h-100 px-4" style="border-radius: 10px 0 0 10px;">
                            <i class='bx bx-search fs-4'></i>
                        </button>
                    </form>
                </div>

                <div id="barcode-reader" style="display: none;" class="text-center mb-4">
                    <div id="qr-reader" class="mx-auto rounded shadow-sm"></div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger text-center"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($result): ?>
                    <div class="tracking-result border rounded p-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <div>
                                <span class="badge bg-orange text-white mb-2">شحنة محلية</span>
                                <h6 class="text-muted mb-0 small">رقم التتبع</h6>
                                <h4 class="fw-bold mb-0"><?= $result['tracking_number'] ?></h4>
                            </div>
                            <div class="text-start">
                                <h6 class="text-muted mb-0 small">آخر تحديث</h6>
                                <h5 class="text-primary fw-bold mb-0"><?= $result['latest_status_text'] ?? 'قيد المعالجة' ?></h5>
                                <small class="text-muted"><?= date('d M Y | H:i', strtotime($result['updated_at'])) ?></small>
                            </div>
                        </div>

                        <div class="status-timeline-vertical">
                            <?php
                            $currentStatusKey = $result['status'];
                            $all_keys = array_column($active_statuses, 'status_key');
                            $currentIndex = array_search($currentStatusKey, $all_keys);

                            foreach ($active_statuses as $index => $status):
                                $isActive = ($currentStatusKey === $status['status_key']);
                                $isCompleted = ($currentIndex > $index);
                                $statusClass = $isActive ? 'active' : ($isCompleted ? 'completed' : 'pending');
                            ?>
                                <div class="timeline-item <?= $statusClass ?>">
                                    <div class="timeline-dot">
                                        <?php if ($isCompleted): ?> <i class='bx bx-check'></i>
                                        <?php elseif ($isActive): ?> <div class="pulse-dot"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0 <?= $isCompleted ? 'text-success' : '' ?>">
                                                <?= htmlspecialchars($status['status_name']) ?>
                                            </h6>
                                            <?php if ($isCompleted): ?> <i class='bx bxs-check-circle text-success fs-5'></i> <?php endif; ?>
                                            <?php if ($isActive): ?> <span class="badge bg-orange-light">جاري الآن</span> <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mt-1 mb-0">
                                            <?= $isActive ? 'تحت المعالجة حالياً.' : ($isCompleted ? 'تمت بنجاح.' : 'بانتظار الوصول.') ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // دالة تبديل التبويبات (أكثر بساطة)
        function switchTab(tab) {
            document.querySelectorAll('.search-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');

            document.getElementById('text-search').style.display = (tab === 'text') ? 'block' : 'none';
            document.getElementById('barcode-reader').style.display = (tab === 'barcode') ? 'block' : 'none';

            if (tab === 'barcode') initScanner();
        }

        let scanner = null;

        function initScanner() {
            if (!scanner) {
                scanner = new Html5QrcodeScanner("qr-reader", {
                    fps: 10,
                    qrbox: 250
                });
                scanner.render(res => {
                    let code = res.includes("search=") ? res.split("search=")[1] : res;
                    window.location.href = `track.php?search=${code}`;
                });
            }
        }
    </script>
</body>

</html>