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
    // استرجاع جميع إعدادات الاتصال
    $stmt = $pdo->query("SELECT 
        contact_hero_subtitle,
        contact_phone_title,
        contact_email_title,
        contact_address_title,
        contact_phone,
        contact_email,
        contact_address,
        contact_hours,
        contact_info
    FROM settings WHERE id = 1");
    $contactSettings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $contactSettings = [];
}

// معالجة نموذج الاتصال
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $messageText = $_POST['message'] ?? '';

        if (!empty($name) && !empty($email) && !empty($messageText)) {
            // جلب البريد الإلكتروني من الإعدادات
            $stmt = $pdo->prepare("SELECT contact_email FROM settings WHERE id = 1");
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            $to_email = $settings['contact_email'];

            // تنسيق الرسالة
            $email_subject = "رسالة جديدة من نموذج الاتصال: " . $subject;
            $email_body = "تم استلام رسالة جديدة من نموذج الاتصال:\n\n" .
                         "الاسم: " . $name . "\n" .
                         "البريد الإلكتروني: " . $email . "\n" .
                         "الموضوع: " . $subject . "\n" .
                         "الرسالة:\n" . $messageText;

            // ترويسات البريد
            $headers = "From: " . $email . "\r\n" .
                      "Reply-To: " . $email . "\r\n" .
                      "X-Mailer: PHP/" . phpversion();

            // إرسال البريد
            if(mail($to_email, $email_subject, $email_body, $headers)) {
                $message = '<div class="alert alert-success">تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.</div>';
            } else {
                $message = '<div class="alert alert-danger">عذراً، حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">يرجى ملء جميع الحقول المطلوبة.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.</div>';
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا | نظام تتبع الطلبات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        html {
            height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        main {
            flex: 1 0 auto;
        }

        footer {
            margin-top: auto;
        }

        :root {
            --primary-color: #00B074;
            --secondary-color: #162E66;
            --light-color: #EFFDF5;
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

        .contact-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 120px 0 80px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .contact-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: #f8f9fa;
            transform: skewY(-3deg);
        }

        .contact-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .contact-icon {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 176, 116, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .contact-header {
                padding: 80px 0 60px;
            }
            
            .contact-form {
                padding: 20px;
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
                        <a class="nav-link" href="about.php">من نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="track.php">تتبع الطلب</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">اتصل بنا</a>
                    </li>
                </ul>
                
            </div>
        </div>
    </nav>

    <main>
        <!-- Header Section -->
        <section class="contact-header text-center">
            <div class="container">
                <h1 class="display-4 fw-bold mb-4">اتصل بنا</h1>
                <p class="lead mb-0"><?php echo htmlspecialchars($contactSettings['contact_hero_subtitle'] ?? 'نحن هنا لمساعدتك والإجابة على جميع استفساراتك'); ?></p>
            </div>
        </section>

        <!-- Contact Info Section -->
        <section class="py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="contact-card text-center">
                            <i class='bx bx-phone contact-icon'></i>
                            <h4><?php echo htmlspecialchars($contactSettings['contact_phone_title'] ?? 'اتصل بنا'); ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($contactSettings['contact_phone'] ?? '+966 50 000 0000'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-card text-center">
                            <i class='bx bx-envelope contact-icon'></i>
                            <h4><?php echo htmlspecialchars($contactSettings['contact_email_title'] ?? 'راسلنا'); ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($contactSettings['contact_email'] ?? 'info@example.com'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-card text-center">
                            <i class='bx bx-map contact-icon'></i>
                            <h4><?php echo htmlspecialchars($contactSettings['contact_address_title'] ?? 'موقعنا'); ?></h4>
                            <p class="mb-0"><?php echo htmlspecialchars($contactSettings['contact_address'] ?? 'المملكة العربية السعودية'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="contact-form">
                            <h3 class="mb-4 text-center">أرسل رسالتك</h3>
                            <?php echo $message; ?>
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">الاسم الكامل</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">البريد الإلكتروني</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الموضوع</label>
                                    <input type="text" name="subject" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الرسالة</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class='bx bx-send'></i> إرسال الرسالة
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="mt-4">
                            <div class="contact-info text-center">
                                <?php echo !empty($contactSettings['contact_hours']) ? nl2br(htmlspecialchars($contactSettings['contact_hours'])) : '
                                <h4>ساعات العمل</h4>
                                <p>الأحد - الخميس: 9:00 صباحاً - 6:00 مساءً</p>
                                <p>الجمعة - السبت: مغلق</p>
                                '; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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