<?php

header("Content-Type: application/json");
require_once 'config.php';

$request_method = $_SERVER["REQUEST_METHOD"];
$request_uri = explode("/", trim($_SERVER["REQUEST_URI"], "/"));

if ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/categories") !== false) {
    getCategories($conn);
}
elseif($request_method == "PUT" && preg_match("/categories\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    updateCategory($conn, $matches[1]);
}

function getCategories($conn){
    $result = $conn->query("SELECT * FROM categories
                            ORDER BY id asc");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function updateCategory($conn, $id){
    $input = json_decode(file_get_contents("php://input"),true);
    if (!isset($input["name"]) || empty(trim($input["name"]))) {
        echo json_encode(["error" => "Potrebno je ime kategorije"]);
        return;
    }

    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $input["name"], $id);
    if($stmt->execute()){
        echo json_encode(["message" => "Kategorija uspesno azurirana."]);
    }
    else{
        echo json_encode(["error" => "Azuriranje kategorije nije uspelo."]);
    }
}


$conn->close();




?>