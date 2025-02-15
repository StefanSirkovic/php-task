<?php

require_once 'config.php';

$csvFile = 'product_categories.csv';


if (($handle=fopen($csvFile, "r")) != FALSE){
    fgetcsv($handle);

    while(($data = fgetcsv($handle, 1000, ","))!==FALSE){
        list($product_number, $category_name, $department_name, $manufacturer_name, $upc, $sku, $regular_price, $sale_price, $description) = $data;

        $category_id = getOrCreateId($conn, 'categories', 'name', $category_name);
        $department_id = getOrCreateId($conn, 'departments', 'name', $department_name);
        $manufacturer_id = getOrCreateId($conn, 'manufacturers', 'name', $manufacturer_name);

        $stmt = $conn->prepare("INSERT INTO products (product_number, category_id, department_id, manufacturer_id, upc, sku, regular_price, sale_price, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE category_id=VALUES(category_id), department_id=VALUES(department_id), manufacturer_id=VALUES(manufacturer_id), upc=VALUES(upc), sku=VALUES(sku), regular_price=VALUES(regular_price), sale_price=VALUES(sale_price), description=VALUES(description)");
        $stmt->bind_param("siiissdds", $product_number, $category_id, $department_id, $manufacturer_id, $upc, $sku, $regular_price, $sale_price, $description);
        $stmt->execute();
    }
    fclose($handle);
}

    $conn->close();

    echo "Podaci uspesno dodati u bazu.";

    function getOrCreateId($conn, $table, $column, $value){
        $stmt = $conn->prepare("SELECT id FROM $table WHERE $column = ?");
        $stmt->bind_param("s",$value);
        $stmt->execute();
        $stmt->bind_result($id);
        if($stmt->fetch()){
            return $id;
        }
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
        $stmt->bind_param("s",$value);
        $stmt->execute();
        return $stmt->insert_id;
    }



?>