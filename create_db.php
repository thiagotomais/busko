<?php
$conn = new mysqli('127.0.0.1', 'root', '', '', 3306);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    exit(1);
}
$sql = "CREATE DATABASE IF NOT EXISTS busko";
if ($conn->query($sql) === TRUE) {
    echo "Database 'busko' created or already exists\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();
?>
