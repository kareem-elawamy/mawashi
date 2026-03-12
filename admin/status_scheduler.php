<?php
include 'config.php';

// التحقق من الطلبات التي تحتاج لتحديث
$stmt = $pdo->prepare("
    SELECT osu.* 
    FROM order_status_updates osu
    WHERE osu.scheduled_date <= NOW() 
    AND osu.actual_date IS NULL
");
$stmt->execute();
$updates = $stmt->fetchAll();

foreach($updates as $update) {
    // تحديث حالة الطلب
    $pdo->prepare("
        UPDATE orders 
        SET status_id = ? 
        WHERE id = ?
    ")->execute([$update['status_id'], $update['order_id']]);
    
    // تحديث تاريخ التنفيذ الفعلي
    $pdo->prepare("
        UPDATE order_status_updates 
        SET actual_date = NOW() 
        WHERE id = ?
    ")->execute([$update['id']]);
}