STEP 1 TOLD BY CHATGPT FOR ADMIN DASHBOARD.PHP
Create a db.php file for your database connection.

<?php
// db.php

$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>


STEP 1 TOLD BY CHATGPT FOR COURSESACCESS.PHP
Create a MySQL Database and Table: First, you need to create a table to store the course data. Assuming your database is called school_management, here’s a basic SQL command for the table:

CREATE TABLE courses (
id INT AUTO_INCREMENT PRIMARY KEY,
course_name VARCHAR(100) NOT NULL,
description TEXT,
department VARCHAR(100),
credits INT
);

make a ui change:  add give access to input field where you write the employee id of the teacher who will be doing the Course

STEP 1 TOLD BY CHATGPT FOR NEWENTRY(T).PHP
Step 1: Database Structure (MySQL Example)

CREATE DATABASE employees_db;

CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    employee_id VARCHAR(20) NOT NULL UNIQUE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') NOT NULL,
    dob DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_details VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


DATABSE CONNECTION CODE GIVEN FOR UPDATESPAGE.PHP
<?php
$host = 'localhost';
$db = 'school_management';
$user = 'root'; // or your database user
$pass = ''; // or your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>


by default the joining date is current date fr the tchr the day the enrolment is being done. if needed they cn also change it.