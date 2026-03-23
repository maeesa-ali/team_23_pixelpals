<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "cs2team23_db");

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}


$sql = "SELECT * FROM product";
$result = $conn->query($sql);

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = [
        "ProductID" => $row["ProductID"],
        "ProductName" => $row["ProductName"],
        "Category" => $row["Category"],
        "Price" => (float)$row["Price"],
        "Stock" => (int)$row["Stock"],
        "Description" => $row["Description"],
        "ImagePath" => $row["ImagePath"]
        
       
    ];
}

echo json_encode($products);

$conn->close();
?>
