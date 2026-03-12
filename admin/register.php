<?php
session_start();
include '../config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // التحقق من صحة البيانات
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'جميع الحقول مطلوبة';
    } elseif (!validateEmail($email)) {
        $error = 'البريد الإلكتروني غير صالح';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 8) {
        $error = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } else {
        // التحقق من وجود اسم المستخدم أو البريد الإلكتروني
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            $error = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل';
        } else {
            // تشفير كلمة المرور وإضافة المستخدم
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
                $stmt->execute([$username, $email, $hashed_password]);
                
                $success = 'تم إنشاء الحساب بنجاح. يمكنك الآن تسجيل الدخول';
                
                // توجيه المستخدم إلى صفحة تسجيل الدخول بعد 3 ثواني
                header("refresh:3;url=login.php");
            } catch (PDOException $e) {
                $error = 'حدث خطأ أثناء إنشاء الحساب';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب مدير جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            max-width: 500px;
            width: 100%;
            padding: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            background-color: white;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>إنشاء حساب مدير جديد</h1>
                <p class="text-muted">الرجاء إدخال بيانات الحساب الجديد</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success text-center"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-register">إنشاء الحساب</button>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">لديك حساب بالفعل؟ تسجيل الدخول</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>