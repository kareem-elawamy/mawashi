<?php
date_default_timezone_set('Asia/Riyadh');
$host = 'localhost'; // غالباً بتبقى localhost طالما الموقع والقاعدة على نفس السيرفر
$dbname = 'mawashi_atracking';
$username = 'mawashi_atracking';
$password = 'Aa112233@4#';        // الباسورد الافتراضي في XAMPP بيكون فاضي    

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // تجنب عرض تفاصيل الخطأ للمستخدم النهائي
    error_log("Connection failed: " . $e->getMessage());
    die("حدث خطأ في الاتصال");
}

// دالة للحماية من هجمات XSS
function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// دالة للتحقق من صحة البريد الإلكتروني
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// دالة للتحقق من المدخلات الرقمية
function validateInt($input)
{
    return filter_var($input, FILTER_VALIDATE_INT);
}

// دالة للحماية من حقن SQL
function sanitizeSQL($pdo, $string)
{
    return $pdo->quote(strip_tags($string));
}
