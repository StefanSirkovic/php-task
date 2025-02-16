<?php

header("Content-Type: application/json");
require_once 'config.php';

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET" && preg_match("/export_csv\/(\d+)/", $_SERVER["REQUEST_URI"], $matches)) {
    exportProductsToCSV($conn, $matches[1]);
}


function exportProductsToCSV($conn, $category_id){
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $stmt->bind_result($category_name);
    $stmt->fetch();
    $stmt->close();


    if(!$category_name){
        echo json_encode(["error" => "Kategorija nije pronadjena."]);
        return;
    }

    $category_name_sanitized = preg_replace("/[^a-zA-Z0-9]+/", "_", strtolower($category_name));
    $timestamp = date("Y_m_d-H_i");
    $filename = "{$category_name_sanitized}_{$timestamp}.csv";

    $stmt = $conn->prepare("SELECT product_number, upc, sku, regular_price, sale_price, description FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$filename");

        $output = fopen("php://output", "w");
        fputcsv($output, ["Product Number", "UPC", "SKU", "Regular Price", "Sale Price", "Description"]);
        

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fclose($output);
    }
    else{
        echo json_encode(["message" => "Nije pronadjen proizvod u toj kategoriji"]);
    }
    $stmt->close();
}

$conn->close();





?>