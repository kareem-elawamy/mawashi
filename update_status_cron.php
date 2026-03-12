<?php
include 'config.php';

try {
    // جلب جميع الطلبات التي لديها تواريخ مجدولة
    $orders_stmt = $pdo->query("
        SELECT DISTINCT o.id, o.status, os.display_order as current_order
        FROM orders o
        JOIN order_status_dates osd ON o.id = osd.order_id
        JOIN order_statuses os ON o.status = os.status_key
        WHERE osd.scheduled_date <= NOW()
    ");
    
    $orders = $orders_stmt->fetchAll();

    foreach ($orders as $order) {
        // جلب أحدث حالة مجدولة يجب أن يكون عليها الطلب
        $status_stmt = $pdo->prepare("
            SELECT osd.status, os.display_order
            FROM order_status_dates osd
            JOIN order_statuses os ON osd.status = os.status_key
            WHERE osd.order_id = ?
                AND osd.scheduled_date <= NOW()
            ORDER BY os.display_order DESC
            LIMIT 1
        ");
        $status_stmt->execute([$order['id']]);
        $new_status_data = $status_stmt->fetch();

        // تحديث الحالة إذا كان الترتيب الجديد أعلى من الحالي
        if ($new_status_data && $new_status_data['display_order'] > $order['current_order']) {
            // تحديث حالة الطلب
            $update_stmt = $pdo->prepare("
                UPDATE orders 
                SET status = ?, 
                    updated_at = NOW() 
                WHERE id = ?
            ");
            $update_stmt->execute([$new_status_data['status'], $order['id']]);

            // إضافة سجل في تاريخ الطلب
            $history_stmt = $pdo->prepare("
                INSERT INTO order_history (order_id, status, icon) 
                SELECT ?, ?, status_icon 
                FROM order_statuses 
                WHERE status_key = ?
            ");
            $history_stmt->execute([$order['id'], $new_status_data['status'], $new_status_data['status']]);

            // تحديث التاريخ الفعلي في جدول order_status_dates
            $actual_date_stmt = $pdo->prepare("
                UPDATE order_status_dates 
                SET actual_date = NOW() 
                WHERE order_id = ? AND status = ?
            ");
            $actual_date_stmt->execute([$order['id'], $new_status_data['status']]);
        }
    }

    echo "تم تحديث حالات الطلبات بنجاح";

} catch (PDOException $e) {
    echo "حدث خطأ: " . $e->getMessage();
}