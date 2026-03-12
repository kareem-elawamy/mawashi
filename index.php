<?php include 'config.php'; ?>

<?php
// استرجاع إعدادات الفوتر
try {
    $stmt = $pdo->prepare("SELECT 
        footer_about_title,
        footer_about_description,
        footer_copyright_text,
        payment_methods_images,
        social_facebook,
        social_twitter,
        social_instagram,
        social_facebook_icon,
        social_twitter_icon,
        social_instagram_icon
    FROM settings WHERE id = 1");
    $stmt->execute();
    $footerSettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $footerSettings = [];
}

// جلب إعدادات الشعار
try {
    $stmt = $pdo->prepare("SELECT site_logo FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $settings = ['site_logo' => ''];
}

// استرجاع إعدادات الصفحة الرئيسية
$homeSettings = [];
try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $homeSettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // استمر مع القيم الافتراضية
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام تتبع الطلبات | الصفحة الرئيسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #00B074;
            --secondary-color: #162E66;
            --light-color: #EFFDF5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 500;
            padding: 10px 15px !important;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link.active {
            background-color: var(--light-color);
            color: var(--primary-color) !important;
        }

        .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color) !important;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 100px 0 50px;
            text-align: center;
            color: white;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-section img {
            max-width: 100%;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .search-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: -50px;
            position: relative;
            z-index: 100;
        }

        .search-box h3 {
            color: var(--primary-color);
            font-weight: 600;
        }

        .search-box .input-group {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .search-box input {
            border: 2px solid #eee;
            padding: 15px 25px;
            font-size: 1.1rem;
        }

        .search-box .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 15px 30px;
            font-size: 1.1rem;
        }

        .search-box .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .features-section {
            padding: 80px 0;
            background-color: var(--light-color);
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            transition: all 0.3s ease;
            height: 100%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--light-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .feature-icon i {
            font-size: 35px;
            color: var(--primary-color);
        }

        .feature-card h3 {
            color: var(--secondary-color);
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .feature-card p {
            color: #666;
            margin-bottom: 0;
            line-height: 1.7;
        }

        .btn-primary {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .why-choose-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--light-color) 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }

        .why-choose-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(0, 176, 116, 0.05) 0%, rgba(22, 46, 102, 0.05) 100%);
            z-index: 0;
        }

        .section-title {
            position: relative;
            margin-bottom: 60px;
        }

        .section-title h2 {
            color: var(--secondary-color);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .title-line {
            width: 80px;
            height: 4px;
            background: var(--primary-color);
            margin: 0 auto;
            position: relative;
        }

        .title-line::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: -3px;
            width: 40px;
            height: 10px;
            background: var(--primary-color);
            border-radius: 5px;
        }

        .why-choose-section p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
            white-space: normal;
        }

        .about-image-wrapper {
            position: relative;
            padding: 20px;
            margin-top: 30px;
        }

        .about-image {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .about-image:hover {
            transform: translateY(-10px);
        }

        .image-shape {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: var(--primary-color);
            opacity: 0.1;
            border-radius: 20px;
            transform: rotate(-3deg);
            z-index: 1;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 80px 0 30px;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
                padding: 0 20px;
            }

            .search-box {
                margin: -30px 15px 0;
                padding: 25px;
            }

            .feature-card {
                margin-bottom: 20px;
            }

            .why-choose-section {
                padding: 60px 0;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .why-choose-section p {
                font-size: 1rem;
                padding: 0 15px;
            }

            .about-image-wrapper {
                padding: 10px;
            }

            .why-choose-section img {
                max-width: 90%;
            }

            .navbar {
                padding: 10px 0;
            }

            .navbar-brand {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 8px 12px !important;
            }
        }

        @media (max-width: 576px) {
            .search-box .input-group {
                flex-direction: column;
            }

            .search-box .btn-primary {
                width: 100%;
                margin-top: 10px;
                border-radius: 10px;
            }

            .search-box input {
                border-radius: 10px;
            }
        }

        /* تنسيق الفوتر */
        footer {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding-top: 3rem;
            margin-top: auto;
        }

        footer h5 {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        footer h5::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: var(--light-color);
        }

        footer p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.8;
        }

        footer .list-unstyled li {
            margin-bottom: 10px;
        }

        footer .list-unstyled a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        footer .list-unstyled a:hover {
            color: white;
            padding-right: 10px;
        }

        /* تنسيق أيقونات وسائل التواصل الاجتماعي */
        footer .social-icons {
            display: flex;
            gap: 15px;
        }

        footer .social-icons a {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        footer .social-icons a:hover {
            background-color: var(--light-color);
            transform: translateY(-3px);
        }

        footer .social-icons img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* تنسيق صور وسائل الدفع */
        .payment-methods {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .payment-methods h6 {
            color: white;
            margin-bottom: 15px;
        }

        .payment-methods img {
            height: 35px;
            width: 35px;
            margin: 5px;
            border-radius: 50%;
            padding: 5px;
            background-color: white;
            transition: all 0.3s ease;
        }

        .payment-methods img:hover {
            transform: scale(1.1);
        }

        /* تنسيق حقوق النشر */
        footer .copyright {
            background-color: rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            margin-top: 3rem;
            text-align: center;
        }

        footer .copyright p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php if (!empty($settings['site_logo'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($settings['site_logo']); ?>"
                        alt="<?php echo htmlspecialchars($settings['site_name'] ?? 'Logo'); ?>"
                        style="height: 40px; width: auto;">
                <?php else: ?>
                    <!-- الشعار الافتراضي إذا لم يتم تحميل شعار -->
                    <i class='bx bx-package me-2'></i>
                    تراكر
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="track.php">تتبع الطلب</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">اتصل بنا</a>
                    </li>
                </ul>

            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><?php echo htmlspecialchars($homeSettings['home_hero_title'] ?? 'تتبع شحنتك بسهولة وأمان'); ?></h1>
            <p><?php echo htmlspecialchars($homeSettings['home_hero_subtitle'] ?? 'نظام متكامل لتتبع الشحنات والطلبات مع تحديثات فورية ومباشرة'); ?></p>
            <?php if (!empty($homeSettings['hero_image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($homeSettings['hero_image']); ?>" alt="Hero Image">
            <?php endif; ?>
        </div>
    </div>

    <!-- قسم البحث عن الشحنة -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="search-box">
                    <h3 class="text-center mb-4">تتبع شحنتك</h3>
                    <form action="track.php" method="GET">
                        <div class="input-group">
                            <input type="text"
                                class="form-control form-control-lg"
                                name="search"
                                placeholder="أدخل رقم التتبع الخاص بك"
                                required>
                            <button class="btn btn-primary" type="submit">
                                <i class='bx bx-search me-2'></i>
                                تتبع الشحنة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- قسم المميزات -->
    <div class="features-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class='bx bx-package'></i>
                        </div>
                        <h3><?php echo htmlspecialchars($homeSettings['feature1_title'] ?? 'تتبع الشحنات'); ?></h3>
                        <p><?php echo htmlspecialchars($homeSettings['feature1_description'] ?? 'تابع شحنتك خطوة بخطوة مع تحديثات فورية للموقع والحالة'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class='bx bx-time'></i>
                        </div>
                        <h3><?php echo htmlspecialchars($homeSettings['feature2_title'] ?? 'توصيل سريع'); ?></h3>
                        <p><?php echo htmlspecialchars($homeSettings['feature2_description'] ?? 'احصل على إشعارات فورية عن حالة شحنتك وموقعها الحالي'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class='bx bx-check-shield'></i>
                        </div>
                        <h3><?php echo htmlspecialchars($homeSettings['feature3_title'] ?? 'خدمة آمنة'); ?></h3>
                        <p><?php echo htmlspecialchars($homeSettings['feature3_description'] ?? 'فريق دعم متخصص متواجد على مدار الساعة لمساعدتك'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="why-choose-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <div class="section-title">
                        <h2 class="mb-3"><?php echo htmlspecialchars($homeSettings['why_choose_title'] ?? 'لماذا تختارنا'); ?></h2>
                        <div class="title-line"></div>
                        <p class="mt-4"><?php
                                        $description = $homeSettings['why_choose_description'] ?? 'نظام تتبع متطور يمكنك من معرفة موقع شحنتك في أي وقت';
                                        echo htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $description));
                                        ?></p>
                    </div>
                </div>
            </div>
            <?php if (!empty($homeSettings['about_image'])): ?>
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="about-image-wrapper">
                            <img src="uploads/<?php echo htmlspecialchars($homeSettings['about_image']); ?>"
                                alt="About Image" class="about-image">
                            <div class="image-shape"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?php echo htmlspecialchars($footerSettings['footer_about_title'] ?? 'تراكر'); ?></h5>
                    <p><?php echo htmlspecialchars($footerSettings['footer_about_description'] ?? 'نظام متكامل لتتبع الشحنات والطلبات'); ?></p>

                    <?php if (!empty($footerSettings['payment_methods_images'])): ?>
                        <div class="payment-methods">
                            <h6>وسائل الدفع المتاحة</h6>
                            <div class="d-flex flex-wrap">
                                <?php
                                $payment_images = json_decode($footerSettings['payment_methods_images'], true);
                                if ($payment_images):
                                    foreach ($payment_images as $image):
                                ?>
                                        <img src="uploads/<?php echo htmlspecialchars($image); ?>"
                                            alt="Payment Method">
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>روابط سريعة</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php">من نحن</a></li>
                        <li><a href="track.php">تتبع شحنتك</a></li>
                        <li><a href="contact.php">اتصل بنا</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>تواصل معنا</h5>
                    <div class="social-icons">
                        <?php if (!empty($footerSettings['social_facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($footerSettings['social_facebook']); ?>" target="_blank">
                                <?php if (!empty($footerSettings['social_facebook_icon'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($footerSettings['social_facebook_icon']); ?>"
                                        alt="Facebook">
                                <?php else: ?>
                                    <i class='bx bxl-facebook'></i>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($footerSettings['social_twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($footerSettings['social_twitter']); ?>" target="_blank">
                                <?php if (!empty($footerSettings['social_twitter_icon'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($footerSettings['social_twitter_icon']); ?>"
                                        alt="Twitter">
                                <?php else: ?>
                                    <i class='bx bxl-twitter'></i>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($footerSettings['social_instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($footerSettings['social_instagram']); ?>" target="_blank">
                                <?php if (!empty($footerSettings['social_instagram_icon'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($footerSettings['social_instagram_icon']); ?>"
                                        alt="Instagram">
                                <?php else: ?>
                                    <i class='bx bxl-instagram'></i>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p><?php echo htmlspecialchars($footerSettings['footer_copyright_text'] ?? 'جميع الحقوق محفوظة © 2025'); ?></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>