<?php

header("Content-Type: application/json");
require_once 'config.php';

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/categories") !== false) {
    getCategories($conn);
}
elseif($request_method == "PUT" && preg_match("/categories\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    updateCategory($conn, $matches[1]);
}
elseif ($request_method == "DELETE" && preg_match("/categories\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    deleteCategory($conn, $matches[1]);
}
elseif ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/products/") !== false && preg_match("/products\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    getProductsByCategory($conn, $matches[1]);
}
elseif ($request_method == "GET" && strpos($_SERVER["REQUEST_URI"], "/products") !== false) {
    getProducts($conn);
}
elseif ($request_method == "PUT" && preg_match("/products\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    updateProduct($conn, $matches[1]);
}
elseif ($request_method == "DELETE" && preg_match("/products\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    deleteProduct($conn, $matches[1]);
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


function getProductsByCategory($conn, $category_id){
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
}

function updateProduct($conn, $id){
    $input = json_decode(file_get_contents("php://input"),true);
    if (empty($input)) {
        echo json_encode(["error" => "Nisu poslati svi potrebni podaci za azuriranje."]);
        return;
    }

    $fields = [];
    $params = [];
    $types = "";

    foreach($input as $key => $value){
        $fiels[] = "$key = ?";
        $params[] = $value;
        $types .= (is_numeric($value) ? "i" : "s");
    }

    $query = "UPDATE products SET " . implode (", ", $fiels) . " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if($stmt->execute()){
        echo json_encode(["message" => "Proizvod uspesno azuriran."]);
    }
    else{
        echo json_encode(["error" => "Azuriranje proizvoda nije uspelo."]);
    }
}

function deleteProduct($conn, $id){
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo json_encode(["message" => "Proizvod uspesno obrisan."]);
    }
    else{
        echo json_encode(["error" => "Brisanje proizvoda nije uspelo"]);
    }
}




$conn->close();




?>