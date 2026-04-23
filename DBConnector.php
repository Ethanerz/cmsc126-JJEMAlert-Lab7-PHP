<?php

$servername = "localhost"; // Name of the server
$username = "username"; // Username
$password = "password"; // Password
// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
echo “Connected successfully!”;
// Close the connection
$conn->close();

?>