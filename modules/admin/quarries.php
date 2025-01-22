<?php
// Database queries utility functions
function getDonationAnalytics($conn) {
    $query = "
        SELECT 
            DATE(created_at) as date,
            SUM(amount) as daily_amount,
            COUNT(*) as count
        FROM donations
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

function getMembershipStats($conn) {
    $query = "
        SELECT 
            mp.name as plan_name,
            COUNT(*) as member_count,
            SUM(mp.price) as total_revenue
        FROM user_memberships um
        JOIN membership_plans mp ON um.plan_id = mp.id
        WHERE um.status = 'active'
        GROUP BY mp.name
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

function getProgramParticipation($conn) {
    $query = "
        SELECT 
            p.name as program_name,
            COUNT(pt.id) as participant_count,
            p.capacity as total_capacity,
            p.start_date
        FROM programs p
        LEFT JOIN participants pt ON p.id = pt.program_id
        GROUP BY p.id
        ORDER BY p.start_date DESC
        LIMIT 10
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

function getRecentOrders($conn) {
    $query = "
        SELECT 
            o.id,
            o.created_at,
            u.username,
            SUM(oi.quantity * oi.price) as total_amount,
            COUNT(oi.id) as items_count,
            GROUP_CONCAT(p.name) as products
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}
?>