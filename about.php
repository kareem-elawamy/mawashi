<?php
include 'config.php';
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

try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $pageSettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pageSettings = [];
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من نحن | نظام تتبع الطلبات</title>
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
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
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

        .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            background-color: var(--light-color);
            color: var(--primary-color) !important;
        }

        .about-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .about-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: #f8f9fa;
            transform: skewY(-3deg);
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }

        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 5px solid var(--light-color);
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .stats-number {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .timeline {
            position: relative;
            padding: 40px 0;
        }

        .timeline-item {
            padding: 20px;
            background: white;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            right: -40px;
            top: 50%;
            transform: translateY(-50%);
        }

        @media (max-width: 768px) {
            .about-header {
                padding: 80px 0 60px;
            }
            
            .timeline-item::before {
                right: 50%;
                top: -30px;
                transform: translateX(50%);
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
    <!-- Navbar -->
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
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">من نحن</a>
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

    <!-- Header Section -->
    <section class="about-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">من نحن</h1>
            <p class="lead mb-0"><?php echo htmlspecialchars($pageSettings['about_hero_text'] ?? 'نحن شركة رائدة في مجال خدمات الشحن والتوصيل'); ?></p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4"><?php echo htmlspecialchars($pageSettings['about_story_title'] ?? 'قصتنا'); ?></h2>
                    <p class="lead mb-4"><?php echo htmlspecialchars($pageSettings['about_story_text'] ?? 'نحن شركة رائدة في مجال خدمات الشحن والتوصيل'); ?></p>
                    <div class="d-flex mb-3">
                        <i class='bx bx-check-circle text-primary' style="font-size: 24px;"></i>
                        <div class="ms-3">
                            <h5><?php echo htmlspecialchars($pageSettings['about_service_reliable_title'] ?? 'خدمة موثوقة'); ?></h5>
                            <p><?php echo htmlspecialchars($pageSettings['about_service_reliable_text'] ?? 'نقدم خدمات شحن آمنة وموثوقة مع تتبع مباشر للشحنات'); ?></p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <i class='bx bx-check-circle text-primary' style="font-size: 24px;"></i>
                        <div class="ms-3">
                            <h5><?php echo htmlspecialchars($pageSettings['about_service_professional_title'] ?? 'فريق محترف'); ?></h5>
                            <p><?php echo htmlspecialchars($pageSettings['about_service_professional_text'] ?? 'فريق عمل متخصص ومدرب على أعلى مستوى'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <?php if (!empty($pageSettings['about_story_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($pageSettings['about_story_image']); ?>" 
                             class="img-fluid rounded" alt="عن الشركة">
                    <?php else: ?>
                        <img src="https://img.freepik.com/free-vector/delivery-service-with-masks-concept_23-2148498576.jpg" 
                             class="img-fluid rounded" alt="عن الشركة">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <i class='bx bx-package feature-icon'></i>
                        <div class="stats-number"><?php echo htmlspecialchars($pageSettings['about_stats_shipments'] ?? '1000+'); ?></div>
                        <div>شحنة يومياً</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <i class='bx bx-user feature-icon'></i>
                        <div class="stats-number"><?php echo htmlspecialchars($pageSettings['about_stats_clients'] ?? '5000+'); ?></div>
                        <div>عميل سعيد</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <i class='bx bx-map feature-icon'></i>
                        <div class="stats-number"><?php echo htmlspecialchars($pageSettings['about_stats_cities'] ?? '50+'); ?></div>
                        <div>مدينة</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card">
                        <i class='bx bx-car feature-icon'></i>
                        <div class="stats-number"><?php echo htmlspecialchars($pageSettings['about_stats_vehicles'] ?? '100+'); ?></div>
                        <div>مركبة توصيل</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">خدماتنا</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <i class='bx bx-package feature-icon'></i>
                        <h4><?php echo htmlspecialchars($pageSettings['about_service1_title'] ?? 'شحن سريع'); ?></h4>
                        <p><?php echo htmlspecialchars($pageSettings['about_service1_text'] ?? 'خدمة توصيل سريعة وآمنة لجميع شحناتك'); ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <i class='bx bx-map feature-icon'></i>
                        <h4><?php echo htmlspecialchars($pageSettings['about_service2_title'] ?? 'تغطية واسعة'); ?></h4>
                        <p><?php echo htmlspecialchars($pageSettings['about_service2_text'] ?? 'نغطي جميع المناطق الرئيسية في المملكة'); ?></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card text-center">
                        <i class='bx bx-search feature-icon'></i>
                        <h4><?php echo htmlspecialchars($pageSettings['about_service3_title'] ?? 'تتبع مباشر'); ?></h4>
                        <p><?php echo htmlspecialchars($pageSettings['about_service3_text'] ?? 'تتبع شحنتك بشكل مباشر ولحظي'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5"><?php echo htmlspecialchars($pageSettings['about_timeline_title'] ?? 'مسيرتنا'); ?></h2>
            <div class="timeline">
                <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($pageSettings['about_timeline1_year'] ?? '2020'); ?></h4>
                            <p><?php echo htmlspecialchars($pageSettings['about_timeline1_text'] ?? 'تأسيس الشركة وبداية العمل في مجال الشحن'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($pageSettings['about_timeline2_year'] ?? '2022'); ?></h4>
                            <p><?php echo htmlspecialchars($pageSettings['about_timeline2_text'] ?? 'توسيع نطاق الخدمات وتغطية المزيد من المناطق'); ?></p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="timeline-item">
                            <h4><?php echo htmlspecialchars($pageSettings['about_timeline3_year'] ?? '2024'); ?></h4>
                            <p><?php echo htmlspecialchars($pageSettings['about_timeline3_text'] ?? 'إطلاق نظام التتبع الإلكتروني المتطور'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                                foreach($payment_images as $image): 
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