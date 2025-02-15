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
elseif ($request_method == "DELETE" && preg_match("/categories\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    deleteCategory($conn, $matches[1]);
}
elseif ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/products") !== false) {
    getProducts($conn);
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

function deleteCategory($conn, $id){
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo json_encode(["message" => "Kategorija uspesno obrisana"]);
    }
    else {
        echo json_encode(["error" => "Brisanje kategorije nije uspelo"]);
    }
}

function getProducts($conn){
    $result = $conn->query("SELECT * FROM products
                            ORDER BY id asc");

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));

}



$conn->close();




?>