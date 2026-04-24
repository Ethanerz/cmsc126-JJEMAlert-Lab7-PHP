<?php

$servername = "localhost";
$username = "root";    // default XAMPP username
$password = "";        // default XAMPP password is blank

// Create connection (no database yet)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!<br>";

// Step 3: Create the database
$sql = "CREATE DATABASE IF NOT EXISTS student_db";
if ($conn->query($sql) === TRUE) {
    echo "Database ready!<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database (instead of making a new connection)
$conn->select_db("student_db");

// Step 4: Table 1 - students
$sql = "CREATE TABLE IF NOT EXISTS students (
    student_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL,
    age INT(2) NOT NULL,
    email VARCHAR(40) NOT NULL,
    pic VARCHAR(255)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'students' ready!<br>";
} else {
    echo "Error creating students table: " . $conn->error . "<br>";
}

// Step 4: Table 2 - academic_info
$sql = "CREATE TABLE IF NOT EXISTS academic_info (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT(6) UNSIGNED NOT NULL,
    course VARCHAR(40) NOT NULL,
    year_level INT(1) NOT NULL,
    grad_status BOOLEAN NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'academic_info' ready!<br>";
} else {
    echo "Error creating academic_info table: " . $conn->error . "<br>";
}

$conn->close();
?>