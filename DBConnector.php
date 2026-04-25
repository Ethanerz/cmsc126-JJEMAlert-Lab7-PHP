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
    grad_status TINYINT(1) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(student_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'academic_info' ready!<br>";
} else {
    echo "Error creating academic_info table: " . $conn->error . "<br>";
}

//
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'insert') {
    echo "Doing INSERT query";
    //Handle INSERT
    $name   = $_POST['name'];
    $age    = $_POST['age'];
    $email  = $_POST['email'];
    $course = $_POST['course'];
    $year   = $_POST['year'];
    $grad   = isset($_POST['grad']) ? $_POST['grad'] : '0';
 
    // Handle file upload
    if (!is_dir('uploads/')) {
    mkdir('uploads/', 0755, true);
    }
    $pic = 'uploads/' . $_FILES['pic']['name'];
    move_uploaded_file($_FILES['pic']['tmp_name'], $pic);

    $sql = "INSERT INTO students (name, age, email, pic)
                VALUES ('$name', '$age','$email', '$pic')";
    if ($conn->query($sql) === TRUE){
        echo "Insert success <br>";

        $student_id = $conn->insert_id;

        $sql2 = "INSERT INTO academic_info (student_id, course, year_level, grad_status)
                VALUES ('$student_id', '$course', '$year', '$grad')";
        if ($conn->query($sql2) === TRUE) {
            echo "Academic info inserted successfully!<br>";
        } else {
            echo "Error inserting academic info: " . $conn->error . "<br>";
        }
    } else {
        echo "Error inserting value: " . $conn->error."<br>";
    }
}
elseif ($action == 'select') {
    // Handle SEARCH
    echo "Doing SELECT query";
    $search = $_POST['search_term'];
    if (is_numeric($search)){
        $sql = "SELECT * FROM students WHERE student_id = '$search'";
    } else {
        $sql = "SELECT * FROM students WHERE name = '$search'";
    }
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Display the row
        $row = $result->fetch_assoc();
        echo "<h3>Student Found:</h3>";
        echo "ID: " . $row['student_id'] . "<br>";
        echo "Name: " . $row['name'] . "<br>";
        echo "Age: " . $row['age'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
    } else {
        echo "No student found with: " . $search;
    }
}
elseif ($action == 'update') {
    // Handle UPDATE
    echo "Doing UPDATE query<br>";

    $student_id = isset($_POST['update_id']) ? (int) $_POST['update_id'] : 0;
    $new_name   = isset($_POST['new_name']) ? trim($_POST['new_name']) : '';
    $new_age    = isset($_POST['new_age']) ? trim($_POST['new_age']) : '';
    $new_email  = isset($_POST['new_email']) ? trim($_POST['new_email']) : '';

    if ($student_id <= 0) {
        echo "Please enter a valid student ID.<br>";
    } else {
        $check_sql = "SELECT name, age, email FROM students WHERE student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $student_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            echo "No student found with ID: " . $student_id . "<br>";
        } else {
            $current = $result->fetch_assoc();

            $name = $new_name !== '' ? $new_name : $current['name'];
            $age = $new_age !== '' ? (int) $new_age : (int) $current['age'];
            $email = $new_email !== '' ? $new_email : $current['email'];

            $update_sql = "UPDATE students
                           SET name = ?, age = ?, email = ?
                           WHERE student_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sisi", $name, $age, $email, $student_id);

            if ($update_stmt->execute()) {
                if ($update_stmt->affected_rows > 0) {
                    echo "Student updated successfully!<br>";
                } else {
                    echo "Student found, but no changes were made.<br>";
                }

                echo "<h3>Updated Student:</h3>";
                echo "ID: " . $student_id . "<br>";
                echo "Name: " . htmlspecialchars($name) . "<br>";
                echo "Age: " . $age . "<br>";
                echo "Email: " . htmlspecialchars($email) . "<br>";
            } else {
                echo "Error updating student: " . $conn->error . "<br>";
            }
        }
    }
}
elseif ($action == 'delete') {
    // Handle DELETE
    echo "Doing DELETE query<br>";

    $student_id = isset($_POST['delete_id']) ? (int) $_POST['delete_id'] : 0;

    if ($student_id <= 0) {
        echo "Please enter a valid student ID.<br>";
    } else {
        $conn->begin_transaction();

        $delete_academic_sql = "DELETE FROM academic_info WHERE student_id = ?";
        $delete_academic_stmt = $conn->prepare($delete_academic_sql);
        $delete_academic_stmt->bind_param("i", $student_id);

        $delete_student_sql = "DELETE FROM students WHERE student_id = ?";
        $delete_student_stmt = $conn->prepare($delete_student_sql);
        $delete_student_stmt->bind_param("i", $student_id);

        if ($delete_academic_stmt->execute() && $delete_student_stmt->execute()) {
            if ($delete_student_stmt->affected_rows > 0) {
                $conn->commit();
                echo "Student deleted successfully!<br>";
            } else {
                $conn->rollback();
                echo "No student found with ID: " . $student_id . "<br>";
            }
        } else {
            $conn->rollback();
            echo "Error deleting student: " . $conn->error . "<br>";
        }
    }
}

$conn->close();
?>
