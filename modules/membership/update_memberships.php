<?php
require_once '../../db/db.php';

$sql = "UPDATE user_memberships 
        SET status = 'expired' 
        WHERE status = 'active' AND end_date < NOW()";
$conn->query($sql);
