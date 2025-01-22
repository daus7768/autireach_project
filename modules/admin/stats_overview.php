
<?php
function renderStatsOverview($conn) {
    $stats = [
        'total_donations' => $conn->query("SELECT SUM(amount) as total FROM donations")->fetch_assoc()['total'],
        'total_members' => $conn->query("SELECT COUNT(*) as count FROM user_memberships WHERE status = 'active'")->fetch_assoc()['count'],
        'total_programs' => $conn->query("SELECT COUNT(*) as count FROM programs")->fetch_assoc()['count'],
        'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count']
    ];
    
}
?>