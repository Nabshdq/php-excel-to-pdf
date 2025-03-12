<?php
// Database configuration on hosting server
// $dbHost = "sql103.infinityfree.com"; 
// $dbUsername = "if0_38330694"; 
// $dbPassword = "hZRVlVNdVAkMg4k"; // Kosongkan jika menggunakan default password
// $dbName = "if0_38330694_excel_to_pdf"; // Nama database Anda

// 
$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "excel_to_pdf";

// Create database connection 
$db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection 
if ($db->connect_error) { 
    die("Connection failed: " . $db->connect_error); 
}
?>
