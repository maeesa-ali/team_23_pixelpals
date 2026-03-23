<?php
// This admin view focuses on one order so staff can inspect the full breakdown more easily.
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../public/login.php');
    exit();
}

// This file is just a compatibility handoff so older links still land on the newer orders page.
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$destination = '/Team23_PixelPals_Term2_Final/public/admin/orders.php';

// Preserve the selected order id so the main orders page can open the same record straight away.
if ($orderId > 0) {
    $destination .= '?id=' . $orderId;
}

// Finish by sending the admin to the real order management screen.
header('Location: ' . $destination);
exit();
