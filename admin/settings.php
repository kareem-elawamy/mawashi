<?php
session_start();
include '../config.php';
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $settings = [];
}
// في بداية الملف بعد session_start() و include
if (isset($_POST['update_statuses'])) {
    try {
        $pdo->beginTransaction();

        // 1. تحديث الحالات الموجودة
        if (isset($_POST['status_names'])) {
            // تحضير الاستعلام مرة واحدة خارج اللوب لتقليل الحمل (Best Practice)
            $stmt = $pdo->prepare("UPDATE order_statuses SET 
                status_name = ?,
                status_icon = ?,
                status_color = ?,
                display_order = ?,
                is_active = ?,
                status_key = ? 
                WHERE id = ?");

            foreach ($_POST['status_names'] as $id => $name) {

                $isActive = isset($_POST['status_active'][$id]) ? 1 : 0;

                // استخدام status_keys (بالجمع) لأن هذا هو الاسم في الـ HTML
                $statusKey = $_POST['status_keys'][$id];

                $stmt->execute([
                    $name,                          // 1. status_name
                    $_POST['status_icons'][$id],    // 2. status_icon
                    $_POST['status_colors'][$id],   // 3. status_color
                    $_POST['status_orders'][$id],   // 4. display_order
                    $isActive,                      // 5. is_active
                    $statusKey,                     // 6. status_key (المضاف حديثاً)
                    $id                             // 7. WHERE id
                ]);
            }
        }

        // 2. إضافة حالات جديدة (كما هي)
        if (isset($_POST['new_status_key']) && !empty($_POST['new_status_key'][0])) {
            $stmt = $pdo->prepare("INSERT INTO order_statuses 
                (status_key, status_name, status_icon, status_color, display_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($_POST['new_status_key'] as $index => $key) {
                // تخطي الحقول الفارغة لتجنب إدخال بيانات ناقصة
                if (empty($key)) continue;

                $isActive = isset($_POST['new_status_active'][$index]) ? 1 : 0;

                $stmt->execute([
                    $key,
                    $_POST['new_status_name'][$index],
                    $_POST['new_status_icon'][$index],
                    $_POST['new_status_color'][$index],
                    $_POST['new_status_order'][$index],
                    $isActive
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "تم تحديث حالات الطلب بنجاح";
    } catch (PDOException $e) {
        $pdo->rollBack();
        // التحقق من خطأ التكرار (Duplicate Entry)
        if ($e->getCode() == 23000) {
            $_SESSION['error_message'] = "خطأ: مفتاح الحالة (Status Key) مستخدم بالفعل، يرجى اختيار مفتاح آخر.";
        } else {
            $_SESSION['error_message'] = "حدث خطأ أثناء تحديث حالات الطلب: " . $e->getMessage();
        }
    }

    header("Location: settings.php");
    exit;
}
// التحقق من تحديث الإعدادات العامة
if (isset($_POST['update_general'])) {
    try {
        // معالجة تحميل الشعار
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
            $uploadDir = '../uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'logo_' . time() . '.' . $fileExtension;
                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $uploadPath)) {
                    $stmt = $pdo->prepare("UPDATE settings SET site_logo = ? WHERE id = 1");
                    $stmt->execute([$newFileName]);
                }
            }
        }

        $user_id = $_SESSION['admin_id'];

        // تحديث كلمة المرور
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            // التحقق من كلمة المرور الحالية باستخدام password_verify
            if (password_verify($_POST['current_password'], $user['password'])) {
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    // التحقق من قوة كلمة المرور الجديدة
                    if (strlen($_POST['new_password']) < 8) {
                        $_SESSION['error_message'] = "يجب أن تكون كلمة المرور الجديدة 8 أحرف على الأقل";
                    } else {
                        // تشفير كلمة المرور الجديدة قبل حفظها
                        $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $user_id]);
                        $_SESSION['success_message'] = "تم تحديث كلمة المرور بنجاح";
                    }
                } else {
                    $_SESSION['error_message'] = "كلمتا المرور الجديدتان غير متطابقتين";
                }
            } else {
                $_SESSION['error_message'] = "كلمة المرور الحالية غير صحيحة";
            }

            header("Location: settings.php");
            exit;
        }

        // تحديث معلومات المستخدم (اسم المستخدم والبريد الإلكتروني)
        if (!empty($_POST['username']) || !empty($_POST['email'])) {
            try {
                $user_id = $_SESSION['admin_id'];

                // تحديث اسم المستخدم
                if (!empty($_POST['username'])) {
                    // التحقق من عدم وجود نفس اسم المستخدم لمستخدم آخر
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
                    $check_stmt->execute([$_POST['username'], $user_id]);

                    if ($check_stmt->fetchColumn() > 0) {
                        $_SESSION['error_message'] = "اسم المستخدم موجود مسبقاً";
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
                        $stmt->execute([$_POST['username'], $user_id]);
                        $_SESSION['admin_username'] = $_POST['username'];
                        $_SESSION['success_message'] = "تم تحديث اسم المستخدم بنجاح";
                    }
                }

                // تحديث البريد الإلكتروني
                if (!empty($_POST['email'])) {
                    // التحقق من عدم وجود نفس البريد الإلكتروني لمستخدم آخر
                    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                    $check_stmt->execute([$_POST['email'], $user_id]);

                    if ($check_stmt->fetchColumn() > 0) {
                        $_SESSION['error_message'] = "البريد الإلكتروني موجود مسبقاً";
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                        $stmt->execute([$_POST['email'], $user_id]);
                        $_SESSION['admin_email'] = $_POST['email'];
                        $_SESSION['success_message'] = "تم تحديث البريد الإلكتروني بنجاح";
                    }
                }

                header("Location: settings.php");
                exit;
            } catch (PDOException $e) {
                $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
                header("Location: settings.php");
                exit;
            }
        }

        if (!isset($_SESSION['error_message'])) {
            $_SESSION['success_message'] = "تم تحديث الإعدادات بنجاح";
        }

        header("Location: settings.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
        header("Location: settings.php");
        exit;
    }
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// معالجة تحديث الإعدادات وتحميل الصور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_footer'])) {
            try {
                $uploadPath = '../uploads/';
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // معالجة صور وسائل الدفع
                $payment_methods_images = [];
                if (isset($_FILES['payment_methods']) && !empty($_FILES['payment_methods']['name'][0])) {
                    foreach ($_FILES['payment_methods']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['payment_methods']['error'][$key] === 0) {
                            $filename = uniqid() . '_' . $_FILES['payment_methods']['name'][$key];
                            if (move_uploaded_file($tmp_name, $uploadPath . $filename)) {
                                $payment_methods_images[] = $filename;
                            }
                        }
                    }
                }

                // معالجة أيقونات وسائل التواصل الاجتماعي
                $social_icons = [];
                foreach (['facebook', 'twitter', 'instagram'] as $platform) {
                    if (isset($_FILES["social_{$platform}_icon"]) && $_FILES["social_{$platform}_icon"]['error'] === 0) {
                        $filename = uniqid() . '_' . $_FILES["social_{$platform}_icon"]['name'];
                        if (move_uploaded_file($_FILES["social_{$platform}_icon"]['tmp_name'], $uploadPath . $filename)) {
                            $social_icons[$platform] = $filename;
                        }
                    }
                }

                // تحضير الاستعلام
                $sql = "UPDATE settings SET 
                        footer_about_title = :footer_about_title,
                        footer_about_description = :footer_about_description,
                        footer_copyright_text = :footer_copyright_text,
                        social_facebook = :social_facebook,
                        social_twitter = :social_twitter,
                        social_instagram = :social_instagram";

                $params = [
                    ':footer_about_title' => $_POST['footer_about_title'],
                    ':footer_about_description' => $_POST['footer_about_description'],
                    ':footer_copyright_text' => $_POST['footer_copyright_text'],
                    ':social_facebook' => $_POST['social_facebook'],
                    ':social_twitter' => $_POST['social_twitter'],
                    ':social_instagram' => $_POST['social_instagram']
                ];

                // إضافة صور وسائل الدفع إذا تم تحميلها
                if (!empty($payment_methods_images)) {
                    $sql .= ", payment_methods_images = :payment_methods_images";
                    $params[':payment_methods_images'] = json_encode($payment_methods_images);
                }

                // إضافة أيقونات وسائل التواصل إذا تم تحميلها
                foreach ($social_icons as $platform => $filename) {
                    $sql .= ", social_{$platform}_icon = :social_{$platform}_icon";
                    $params[":social_{$platform}_icon"] = $filename;
                }

                $sql .= " WHERE id = 1";

                // تنفيذ الاستعلام
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $_SESSION['success_message'] = "تم تحديث إعدادات الفوتر بنجاح";
                header("Location: settings.php");
                exit;
            } catch (Exception $e) {
                $_SESSION['error_message'] = "حدث خطأ: " . $e->getMessage();
                header("Location: settings.php");
                exit;
            }
        }

        if (isset($_POST['update_home'])) {
            // معالجة تحميل الصور
            $hero_image = $settings['hero_image'] ?? '';
            $about_image = $settings['about_image'] ?? '';

            if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === 0) {
                $hero_image = uploadImage($_FILES['hero_image']);
            }
            if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === 0) {
                $about_image = uploadImage($_FILES['about_image']);
            }

            $stmt = $pdo->prepare("UPDATE settings SET 
                home_hero_title = ?,
                home_hero_subtitle = ?,
                hero_image = ?,
                track_section_title = ?,
                track_section_description = ?,
                feature1_title = ?,
                feature1_description = ?,
                feature2_title = ?,
                feature2_description = ?,
                feature3_title = ?,
                feature3_description = ?,
                about_image = ?,
                why_choose_title = ?,
                why_choose_description = ?
                WHERE id = 1");

            $stmt->execute([
                $_POST['home_hero_title'],
                $_POST['home_hero_subtitle'],
                $hero_image,
                $_POST['track_section_title'],
                $_POST['track_section_description'],
                $_POST['feature1_title'],
                $_POST['feature1_description'],
                $_POST['feature2_title'],
                $_POST['feature2_description'],
                $_POST['feature3_title'],
                $_POST['feature3_description'],
                $about_image,
                $_POST['why_choose_title'],
                $_POST['why_choose_description']
            ]);

            $_SESSION['success_message'] = "تم تحديث الإعدادات بنجاح";
        } elseif (isset($_POST['update_track'])) {
            try {
                // إنشاء مجلد التحميل إذا لم يكن موجوداً
                $uploadDir = '../uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // تحميل الأيقونات
                $track_icons = json_decode($settings['track_status_icons'] ?? '{}', true) ?: [];
                $statuses = $pdo->query("SELECT status_key FROM order_statuses")->fetchAll(PDO::FETCH_COLUMN);

                foreach ($statuses as $status_key) {
                    $icon_field = "track_status_icon_" . $status_key;

                    if (isset($_FILES[$icon_field]) && $_FILES[$icon_field]['error'] === 0) {
                        $file = $_FILES[$icon_field];
                        $fileName = $file['name'];
                        $fileTmpName = $file['tmp_name'];

                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

                        if (in_array($fileExtension, $allowedExtensions)) {
                            $newFileName = 'status_icon_' . $status_key . '_' . time() . '.' . $fileExtension;
                            $uploadPath = $uploadDir . $newFileName;

                            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                                $track_icons[$status_key] = $newFileName;
                            }
                        }
                    }
                }

                // تحديث قاعدة البيانات
                $stmt = $pdo->prepare("UPDATE settings SET 
                    track_header_title = ?,
                    track_header_subtitle = ?,
                    track_instructions = ?,
                    track_scheduled_date_text = ?,
                    track_status_icons = ?
                    WHERE id = 1");

                $stmt->execute([
                    $_POST['track_header_title'],
                    $_POST['track_header_subtitle'],
                    $_POST['track_instructions'],
                    $_POST['track_scheduled_date_text'],
                    json_encode($track_icons)
                ]);

                $_SESSION['success_message'] = "تم تحديث إعدادات صفحة التتبع بنجاح";
            } catch (Exception $e) {
                $_SESSION['error_message'] = "حدث خطأ: " . $e->getMessage();
            }

            header("Location: settings.php#track");
            exit;
        } elseif (isset($_POST['update_about'])) {
            $about_story_image = $settings['about_story_image'] ?? '';
            if (isset($_FILES['about_story_image']) && $_FILES['about_story_image']['error'] === 0) {
                $about_story_image = uploadImage($_FILES['about_story_image']);
            }

            $stmt = $pdo->prepare("UPDATE settings SET 
                about_hero_text = ?,
                about_story_title = ?,
                about_story_text = ?,
                about_story_image = ?,
                about_service_reliable_title = ?,
                about_service_reliable_text = ?,
                about_service_professional_title = ?,
                about_service_professional_text = ?,
                about_stats_shipments = ?,
                about_stats_clients = ?,
                about_stats_cities = ?,
                about_stats_vehicles = ?,
                about_service1_title = ?,
                about_service1_text = ?,
                about_service2_title = ?,
                about_service2_text = ?,
                about_service3_title = ?,
                about_service3_text = ?,
                about_timeline_title = ?,
                about_timeline1_year = ?,
                about_timeline1_text = ?,
                about_timeline2_year = ?,
                about_timeline2_text = ?,
                about_timeline3_year = ?,
                about_timeline3_text = ?
                WHERE id = 1");

            $stmt->execute([
                $_POST['about_hero_text'],
                $_POST['about_story_title'],
                $_POST['about_story_text'],
                $about_story_image,
                $_POST['about_service_reliable_title'],
                $_POST['about_service_reliable_text'],
                $_POST['about_service_professional_title'],
                $_POST['about_service_professional_text'],
                $_POST['about_stats_shipments'],
                $_POST['about_stats_clients'],
                $_POST['about_stats_cities'],
                $_POST['about_stats_vehicles'],
                $_POST['about_service1_title'],
                $_POST['about_service1_text'],
                $_POST['about_service2_title'],
                $_POST['about_service2_text'],
                $_POST['about_service3_title'],
                $_POST['about_service3_text'],
                $_POST['about_timeline_title'],
                $_POST['about_timeline1_year'],
                $_POST['about_timeline1_text'],
                $_POST['about_timeline2_year'],
                $_POST['about_timeline2_text'],
                $_POST['about_timeline3_year'],
                $_POST['about_timeline3_text']
            ]);
        } elseif (isset($_POST['update_contact'])) {
            $stmt = $pdo->prepare("UPDATE settings SET 
                contact_hero_subtitle = ?,
                contact_phone_title = ?,
                contact_email_title = ?,
                contact_address_title = ?,
                contact_hours = ?,
                contact_info = ?,
                contact_address = ?,
                contact_email = ?,
                contact_phone = ?
                WHERE id = 1");

            $stmt->execute([
                $_POST['contact_hero_subtitle'],
                $_POST['contact_phone_title'],
                $_POST['contact_email_title'],
                $_POST['contact_address_title'],
                $_POST['contact_hours'],
                $_POST['contact_info'],
                $_POST['contact_address'],
                $_POST['contact_email'],
                $_POST['contact_phone']
            ]);
        }

        $_SESSION['success_message'] = "تم تحديث الإعدادات بنجاح";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ في قاعدة البيانات: " . $e->getMessage();
    }
}

// دالة لتحميل الصور
function uploadImage($file)
{
    $upload_dir = '../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_extension, $allowed_extensions)) {
        throw new Exception('نوع الملف غير مسموح به');
    }

    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_filename;
    }

    throw new Exception('فشل في تحميل الصورة');
}

// استرجاع الإعدادات الحالية

?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات | نظام التتبع</title>
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

        /* Settings Styles */
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .nav-tabs {
            border: none;
            margin-bottom: 1.5rem;
        }

        .nav-tabs .nav-link {
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: var(--dark-color);
            font-weight: 500;
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background: rgba(67, 97, 238, 0.05);
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Image Preview */
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1rem;
            border: 2px solid #eee;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Responsive */
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
                display: block;
            }
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
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

        @media (max-width: 992px) {
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
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
                <a href="reports.php" class="sidebar-menu-link">
                    <i class='bx bx-bar-chart'></i>
                    التقارير
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="settings.php" class="sidebar-menu-link active">
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
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">إعدادات النظام</h4>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Settings Card -->
            <div class="settings-card">
                <!-- Tabs -->
                <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                            <i class='bx bx-cog'></i> الإعدادات العامة
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="home-tab" data-bs-toggle="tab" href="#home" role="tab">
                            <i class='bx bx-home'></i> الصفحة الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="track-tab" data-bs-toggle="tab" href="#track" role="tab">
                            <i class='bx bx-search'></i> صفحة التتبع
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="about-tab" data-bs-toggle="tab" href="#about" role="tab">
                            <i class='bx bx-info-circle'></i> صفحة من نحن
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab">
                            <i class='bx bx-envelope'></i> صفحة اتصل بنا
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="footer-tab" data-bs-toggle="tab" href="#footer" role="tab">
                            <i class='bx bx-layout'></i> الفوتر
                        </a>
                    </li>
                    <!-- أضف تبويباً جديداً لحالات الطلب -->
                    <li class="nav-item">
                        <a class="nav-link" id="order-statuses-tab" data-bs-toggle="tab" href="#order-statuses" role="tab">
                            <i class='bx bx-list-check'></i> حالات الطلب
                        </a>
                    </li>
                </ul>
                <!-- Tab Contents -->
                <div class="tab-content" id="settingsTabContent">
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <!-- قسم شعار الموقع -->
                                    <div class="mb-4">
                                        <h5 class="card-title mb-3">شعار الموقع</h5>
                                        <div class="mb-3">
                                            <?php if (!empty($settings['site_logo'])): ?>
                                                <div class="mb-3">
                                                    <img src="../uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>"
                                                        alt="الشعار الحالي" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            <?php endif; ?>
                                            <label class="form-label">تحميل شعار جديد</label>
                                            <input type="file" class="form-control" name="site_logo" accept="image/*">
                                            <small class="text-muted">الأنواع المسموحة: JPG, PNG, GIF. الحجم الأقصى: 2MB</small>
                                        </div>
                                    </div>

                                    <!-- قسم معلومات المستخدم -->
                                    <div class="mb-4">
                                        <h5 class="card-title mb-3">معلومات المستخدم</h5>
                                        <div class="mb-3">
                                            <label class="form-label">اسم المستخدم</label>
                                            <input type="text" class="form-control" name="username"
                                                value="<?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">البريد الإلكتروني</label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?php echo htmlspecialchars($_SESSION['admin_email'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <!-- قسم تغيير كلمة المرور -->
                                    <div class="mb-4">
                                        <h5 class="card-title mb-3">تغيير كلمة المرور</h5>
                                        <div class="mb-3">
                                            <label class="form-label">كلمة المرور الحالية</label>
                                            <input type="password" class="form-control" name="current_password">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">كلمة المرور الجديدة</label>
                                            <input type="password" class="form-control" name="new_password">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                                            <input type="password" class="form-control" name="confirm_password">
                                        </div>
                                    </div>

                                    <button type="submit" name="update_general" class="btn btn-primary">
                                        <i class='bx bx-save'></i> حفظ التغييرات
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Home Settings -->
                    <div class="tab-pane fade" id="home" role="tabpanel">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- القسم الأول: العنوان الرئيسي والوصف -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">العنوان الرئيسي والوصف</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">العنوان الرئيسي</label>
                                        <input type="text" class="form-control" name="home_hero_title"
                                            value="<?php echo htmlspecialchars($settings['home_hero_title'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">الوصف</label>
                                        <textarea class="form-control" name="home_hero_subtitle" rows="2"><?php echo htmlspecialchars($settings['home_hero_subtitle'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- القسم الثاني: الصورة الرئيسية -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">الصورة الرئيسية</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">الصورة الرئيسية</label>
                                        <input type="file" class="form-control" name="hero_image" accept="image/*">
                                        <?php if (!empty($settings['hero_image'])): ?>
                                            <div class="mt-2">
                                                <img src="../uploads/<?php echo htmlspecialchars($settings['hero_image']); ?>"
                                                    alt="Hero Image" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- القسم الثالث: تتبع شحنتك -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">قسم تتبع الشحنة</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان قسم التتبع</label>
                                        <input type="text" class="form-control" name="track_section_title"
                                            value="<?php echo htmlspecialchars($settings['track_section_title'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">وصف قسم التتبع</label>
                                        <textarea class="form-control" name="track_section_description" rows="2"><?php echo htmlspecialchars($settings['track_section_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- القسم الرابع: البطاقات الثلاث -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">البطاقات التعريفية</h5>
                                </div>
                                <div class="card-body">
                                    <!-- البطاقة الأولى -->
                                    <div class="border rounded p-3 mb-3">
                                        <h6>البطاقة الأولى</h6>
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" class="form-control" name="feature1_title"
                                                value="<?php echo htmlspecialchars($settings['feature1_title'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الوصف</label>
                                            <textarea class="form-control" name="feature1_description" rows="2"><?php echo htmlspecialchars($settings['feature1_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <!-- البطاقة الثانية -->
                                    <div class="border rounded p-3 mb-3">
                                        <h6>البطاقة الثانية</h6>
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" class="form-control" name="feature2_title"
                                                value="<?php echo htmlspecialchars($settings['feature2_title'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الوصف</label>
                                            <textarea class="form-control" name="feature2_description" rows="2"><?php echo htmlspecialchars($settings['feature2_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <!-- البطاقة الثالثة -->
                                    <div class="border rounded p-3 mb-3">
                                        <h6>البطاقة الثالثة</h6>
                                        <div class="mb-3">
                                            <label class="form-label">العنوان</label>
                                            <input type="text" class="form-control" name="feature3_title"
                                                value="<?php echo htmlspecialchars($settings['feature3_title'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">الوصف</label>
                                            <textarea class="form-control" name="feature3_description" rows="2"><?php echo htmlspecialchars($settings['feature3_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- القسم الخامس: صورة لماذا تختارنا -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">صورة قسم لماذا تختارنا</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">الصورة</label>
                                        <input type="file" class="form-control" name="about_image" accept="image/*">
                                        <?php if (!empty($settings['about_image'])): ?>
                                            <div class="mt-2">
                                                <img src="../uploads/<?php echo htmlspecialchars($settings['about_image']); ?>"
                                                    alt="About Image" style="max-width: 200px;">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- القسم السادس: لماذا تختارنا -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">قسم لماذا تختارنا</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">العنوان</label>
                                        <input type="text" class="form-control" name="why_choose_title"
                                            value="<?php echo htmlspecialchars($settings['why_choose_title'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">الوصف</label>
                                        <textarea class="form-control" name="why_choose_description" rows="4"><?php echo htmlspecialchars($settings['why_choose_description'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="update_home" class="btn btn-primary btn-lg">
                                    <i class='bx bx-save'></i> حفظ جميع التغييرات
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Track Settings -->
                    <div class="tab-pane fade" id="track" role="tabpanel">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">عنوان صفحة التتبع</label>
                                <input type="text" class="form-control" name="track_header_title"
                                    value="<?php echo htmlspecialchars($settings['track_header_title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">النص التوضيحي</label>
                                <textarea class="form-control" name="track_header_subtitle" rows="2"><?php echo htmlspecialchars($settings['track_header_subtitle'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تعليمات التتبع</label>
                                <textarea class="form-control" name="track_instructions" rows="4"><?php echo htmlspecialchars($settings['track_instructions'] ?? ''); ?></textarea>
                            </div>
                            <!-- إضافة أيقونات حالات التتبع -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">أيقونات حالات التتبع</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $statuses = $pdo->query("SELECT * FROM order_statuses ORDER BY display_order")->fetchAll();
                                    $track_icons = json_decode($settings['track_status_icons'] ?? '{}', true);
                                    ?>

                                    <div class="row">
                                        <?php foreach ($statuses as $status): ?>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><?php echo htmlspecialchars($status['status_name']); ?></label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control"
                                                        name="track_status_icon_<?php echo $status['status_key']; ?>"
                                                        accept="image/*">
                                                    <?php if (!empty($track_icons[$status['status_key']])): ?>
                                                        <div class="input-group-text">
                                                            <img src="../uploads/<?php echo htmlspecialchars($track_icons[$status['status_key']]); ?>"
                                                                alt="<?php echo htmlspecialchars($status['status_name']); ?>"
                                                                style="height: 30px;">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">نص التاريخ المجدول</label>
                                        <input type="text" class="form-control" name="track_scheduled_date_text"
                                            value="<?php echo htmlspecialchars($settings['track_scheduled_date_text'] ?? 'التاريخ المجدول'); ?>">
                                    </div>
                                </div>
                            </div>


                            <button type="submit" name="update_track" class="btn btn-primary">
                                <i class='bx bx-save'></i> حفظ التغييرات
                            </button>
                        </form>
                    </div>

                    <!-- About Settings -->
                    <div class="tab-pane fade" id="about" role="tabpanel">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">نص الترحيب</label>
                                <input type="text" class="form-control" name="about_hero_text"
                                    value="<?php echo htmlspecialchars($settings['about_hero_text'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عنوان القصة</label>
                                <input type="text" class="form-control" name="about_story_title"
                                    value="<?php echo htmlspecialchars($settings['about_story_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص القصة</label>
                                <textarea class="form-control" name="about_story_text" rows="4"><?php echo htmlspecialchars($settings['about_story_text'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">صورة القصة</label>
                                <input type="file" class="form-control" name="about_story_image">
                                <?php if (!empty($settings['about_story_image'])): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($settings['about_story_image']); ?>"
                                        class="mt-2" style="max-width: 200px">
                                <?php endif; ?>
                            </div>

                            <!-- الخدمات الموثوقة -->
                            <div class="mb-3">
                                <label class="form-label">عنوان الخدمة الموثوقة</label>
                                <input type="text" class="form-control" name="about_service_reliable_title"
                                    value="<?php echo htmlspecialchars($settings['about_service_reliable_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص الخدمة الموثوقة</label>
                                <textarea class="form-control" name="about_service_reliable_text"><?php echo htmlspecialchars($settings['about_service_reliable_text'] ?? ''); ?></textarea>
                            </div>

                            <!-- الفريق المحترف -->
                            <div class="mb-3">
                                <label class="form-label">عنوان الفريق المحترف</label>
                                <input type="text" class="form-control" name="about_service_professional_title"
                                    value="<?php echo htmlspecialchars($settings['about_service_professional_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص الفريق المحترف</label>
                                <textarea class="form-control" name="about_service_professional_text"><?php echo htmlspecialchars($settings['about_service_professional_text'] ?? ''); ?></textarea>
                            </div>

                            <!-- الإحصائيات -->
                            <div class="mb-3">
                                <label class="form-label">عدد الشحنات</label>
                                <input type="text" class="form-control" name="about_stats_shipments"
                                    value="<?php echo htmlspecialchars($settings['about_stats_shipments'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عدد العملاء</label>
                                <input type="text" class="form-control" name="about_stats_clients"
                                    value="<?php echo htmlspecialchars($settings['about_stats_clients'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عدد المدن</label>
                                <input type="text" class="form-control" name="about_stats_cities"
                                    value="<?php echo htmlspecialchars($settings['about_stats_cities'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عدد المركبات</label>
                                <input type="text" class="form-control" name="about_stats_vehicles"
                                    value="<?php echo htmlspecialchars($settings['about_stats_vehicles'] ?? ''); ?>">
                            </div>

                            <!-- الخدمات -->
                            <div class="mb-3">
                                <label class="form-label">عنوان الخدمة الأولى</label>
                                <input type="text" class="form-control" name="about_service1_title"
                                    value="<?php echo htmlspecialchars($settings['about_service1_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص الخدمة الأولى</label>
                                <textarea class="form-control" name="about_service1_text"><?php echo htmlspecialchars($settings['about_service1_text'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عنوان الخدمة الثانية</label>
                                <input type="text" class="form-control" name="about_service2_title"
                                    value="<?php echo htmlspecialchars($settings['about_service2_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص الخدمة الثانية</label>
                                <textarea class="form-control" name="about_service2_text"><?php echo htmlspecialchars($settings['about_service2_text'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">عنوان الخدمة الثالثة</label>
                                <input type="text" class="form-control" name="about_service3_title"
                                    value="<?php echo htmlspecialchars($settings['about_service3_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص الخدمة الثالثة</label>
                                <textarea class="form-control" name="about_service3_text"><?php echo htmlspecialchars($settings['about_service3_text'] ?? ''); ?></textarea>
                            </div>

                            <!-- المسيرة -->
                            <div class="mb-3">
                                <label class="form-label">عنوان المسيرة</label>
                                <input type="text" class="form-control" name="about_timeline_title"
                                    value="<?php echo htmlspecialchars($settings['about_timeline_title'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السنة الأولى</label>
                                <input type="text" class="form-control" name="about_timeline1_year"
                                    value="<?php echo htmlspecialchars($settings['about_timeline1_year'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص السنة الأولى</label>
                                <textarea class="form-control" name="about_timeline1_text"><?php echo htmlspecialchars($settings['about_timeline1_text'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السنة الثانية</label>
                                <input type="text" class="form-control" name="about_timeline2_year"
                                    value="<?php echo htmlspecialchars($settings['about_timeline2_year'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص السنة الثانية</label>
                                <textarea class="form-control" name="about_timeline2_text"><?php echo htmlspecialchars($settings['about_timeline2_text'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">السنة الثالثة</label>
                                <input type="text" class="form-control" name="about_timeline3_year"
                                    value="<?php echo htmlspecialchars($settings['about_timeline3_year'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">نص السنة الثالثة</label>
                                <textarea class="form-control" name="about_timeline3_text"><?php echo htmlspecialchars($settings['about_timeline3_text'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" name="update_about" class="btn btn-primary">
                                <i class='bx bx-save'></i> حفظ التغييرات
                            </button>
                        </form>
                    </div>

                    <!-- Contact Settings -->
                    <div class="tab-pane fade" id="contact" role="tabpanel">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">نص ترحيب صفحة اتصل بنا</label>
                                <input type="text" class="form-control" name="contact_hero_subtitle"
                                    value="<?php echo htmlspecialchars($settings['contact_hero_subtitle'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عنوان قسم الهاتف</label>
                                <input type="text" class="form-control" name="contact_phone_title"
                                    value="<?php echo htmlspecialchars($settings['contact_phone_title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عنوان قسم البريد الإلكتروني</label>
                                <input type="text" class="form-control" name="contact_email_title"
                                    value="<?php echo htmlspecialchars($settings['contact_email_title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">عنوان قسم العنوان</label>
                                <input type="text" class="form-control" name="contact_address_title"
                                    value="<?php echo htmlspecialchars($settings['contact_address_title'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ساعات العمل</label>
                                <textarea class="form-control" name="contact_hours" rows="4"><?php echo htmlspecialchars($settings['contact_hours'] ?? ''); ?></textarea>
                            </div>
                            <!-- الحقول الموجودة مسبقاً -->
                            <div class="mb-3">
                                <label class="form-label">معلومات التواصل</label>
                                <textarea class="form-control" name="contact_info" rows="4"><?php echo htmlspecialchars($settings['contact_info'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">العنوان</label>
                                <input type="text" class="form-control" name="contact_address"
                                    value="<?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" name="contact_email"
                                    value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" class="form-control" name="contact_phone"
                                    value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
                            </div>
                            <button type="submit" name="update_contact" class="btn btn-primary">
                                <i class='bx bx-save'></i> حفظ التغييرات
                            </button>
                        </form>
                    </div>

                    <!-- Footer Settings -->
                    <div class="tab-pane fade" id="footer" role="tabpanel">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <!-- قسم معلومات الفوتر -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">معلومات الفوتر</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان عن الموقع</label>
                                        <input type="text" class="form-control" name="footer_about_title"
                                            value="<?php echo htmlspecialchars($settings['footer_about_title'] ?? 'تراكر'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">وصف الموقع</label>
                                        <textarea class="form-control" name="footer_about_description"><?php echo htmlspecialchars($settings['footer_about_description'] ?? 'نظام متكامل لتتبع الشحنات والطلبات'); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">نص حقوق النشر</label>
                                        <input type="text" class="form-control" name="footer_copyright_text"
                                            value="<?php echo htmlspecialchars($settings['footer_copyright_text'] ?? 'جميع الحقوق محفوظة © 2025'); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- قسم وسائل الدفع -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">وسائل الدفع</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">صور وسائل الدفع</label>
                                        <input type="file" class="form-control" name="payment_methods[]" multiple accept="image/*">
                                        <?php if (!empty($settings['payment_methods_images'])): ?>
                                            <div class="mt-3">
                                                <?php foreach (json_decode($settings['payment_methods_images'], true) ?? [] as $image): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($image); ?>"
                                                        alt="Payment Method" style="height: 40px; margin: 5px;">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- قسم وسائل التواصل الاجتماعي -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">وسائل التواصل الاجتماعي</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Facebook -->
                                    <div class="mb-3">
                                        <label class="form-label">رابط Facebook</label>
                                        <input type="url" class="form-control" name="social_facebook"
                                            value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">أيقونة Facebook</label>
                                        <input type="file" class="form-control" name="social_facebook_icon" accept="image/*">
                                        <?php if (!empty($settings['social_facebook_icon'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($settings['social_facebook_icon']); ?>"
                                                alt="Facebook" class="mt-2" style="height: 40px;">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Twitter -->
                                    <div class="mb-3">
                                        <label class="form-label">رابط Twitter</label>
                                        <input type="url" class="form-control" name="social_twitter"
                                            value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">أيقونة Twitter</label>
                                        <input type="file" class="form-control" name="social_twitter_icon" accept="image/*">
                                        <?php if (!empty($settings['social_twitter_icon'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($settings['social_twitter_icon']); ?>"
                                                alt="Twitter" class="mt-2" style="height: 40px;">
                                        <?php endif; ?>
                                    </div>

                                    <!-- Instagram -->
                                    <div class="mb-3">
                                        <label class="form-label">رابط Instagram</label>
                                        <input type="url" class="form-control" name="social_instagram"
                                            value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">أيقونة Instagram</label>
                                        <input type="file" class="form-control" name="social_instagram_icon" accept="image/*">
                                        <?php if (!empty($settings['social_instagram_icon'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($settings['social_instagram_icon']); ?>"
                                                alt="Instagram" class="mt-2" style="height: 40px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="update_footer" class="btn btn-primary">
                                <i class='bx bx-save'></i> حفظ التغييرات
                            </button>
                        </form>
                    </div>

                    <!-- Order Statuses Settings -->
                    <div class="tab-pane fade" id="order-statuses" role="tabpanel">
                        <!-- ضع هنا النموذج السابق لحالات الطلب -->
                        <!-- قسم إعدادات حالات الطلب -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">إعدادات حالات الطلب</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>الحالة</th>
                                                    <th>الاسم المعروض</th>
                                                    <th>الأيقونة</th>
                                                    <th>اللون</th>
                                                    <th>الترتيب</th>
                                                    <th>الحالة</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $statuses = $pdo->query("SELECT * FROM order_statuses ORDER BY display_order")->fetchAll();
                                                foreach ($statuses as $status):
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="status_keys[<?php echo $status['id']; ?>]"
                                                                value="<?php echo htmlspecialchars($status['status_key']); ?>"
                                                                required>
                                                        </td>

                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="status_names[<?php echo $status['id']; ?>]"
                                                                value="<?php echo htmlspecialchars($status['status_name']); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control"
                                                                name="status_icons[<?php echo $status['id']; ?>]"
                                                                value="<?php echo htmlspecialchars($status['status_icon']); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="color" class="form-control"
                                                                name="status_colors[<?php echo $status['id']; ?>]"
                                                                value="<?php echo htmlspecialchars($status['status_color']); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control"
                                                                name="status_orders[<?php echo $status['id']; ?>]"
                                                                value="<?php echo htmlspecialchars($status['display_order']); ?>">
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input type="checkbox" class="form-check-input"
                                                                    name="status_active[<?php echo $status['id']; ?>]"
                                                                    <?php echo $status['is_active'] ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="deleteStatus(<?php echo $status['id']; ?>)">
                                                                <i class='bx bx-trash'></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-success mt-3 mb-3" onclick="addNewStatus()">
                                        <i class='bx bx-plus'></i> إضافة حالة جديدة
                                    </button>
                                    <button type="submit" name="update_statuses" class="btn btn-primary">
                                        <i class='bx bx-save'></i> حفظ تغييرات الحالات
                                    </button>
                                </form>
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

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Auto-size textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        // Function to handle adding new status
        function addNewStatus() {
            // Implement logic to add new status row dynamically
        }

        // Function to handle deleting status
        function deleteStatus(id) {
            // Implement logic to delete status row
        }
        // Function to handle adding new status
        function addNewStatus() {
            const tbody = document.querySelector('#order-statuses table tbody');
            const newRow = document.createElement('tr');
            const newIndex = document.querySelectorAll('#order-statuses table tbody tr').length;

            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control" 
                           name="new_status_key[${newIndex}]" 
                           placeholder="مفتاح الحالة" required>
                </td>
                <td>
                    <input type="text" class="form-control" 
                           name="new_status_name[${newIndex}]" 
                           placeholder="اسم الحالة" required>
                </td>
                <td>
                    <input type="text" class="form-control" 
                           name="new_status_icon[${newIndex}]" 
                           placeholder="bx-icon-name" required>
                </td>
                <td>
                    <input type="color" class="form-control" 
                           name="new_status_color[${newIndex}]" 
                           value="#000000">
                </td>
                <td>
                    <input type="number" class="form-control" 
                           name="new_status_order[${newIndex}]" 
                           value="${newIndex + 1}">
                </td>
                <td>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" 
                               name="new_status_active[${newIndex}]" checked>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="this.closest('tr').remove()">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            `;

            tbody.appendChild(newRow);
        }

        // Function to handle deleting status
        function deleteStatus(id) {
            if (confirm('هل أنت متأكد من حذف هذه الحالة؟')) {
                // يمكنك إضافة طلب AJAX هنا لحذف الحالة من قاعدة البيانات
                fetch(`delete_status.php?id=${id}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // إزالة الصف من الجدول
                            document.querySelector(`tr[data-status-id="${id}"]`)?.remove();
                        } else {
                            alert('حدث خطأ أثناء حذف الحالة');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ أثناء حذف الحالة');
                    });
            }
        }
    </script>
</body>

</html>