<?php

header("Content-Type: application/json");
require_once 'config.php';

$request_method = $_SERVER["REQUEST_METHOD"];
$request_uri = explode("/", trim($_SERVER["REQUEST_URI"], "/"));

if ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/categories") !== false) {
    getCategories($conn);
}

function getCategories($conn){
    $result = $conn->query("SELECT * FROM categories
                            ORDER BY id asc");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));

}

$conn->close();




?>