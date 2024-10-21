<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    echo 'You are not logged in!';
    exit;
}

$student_id = $_SESSION['student_id'];
$course_id = $_POST['course_id'];

// Insert into course_selections
try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the student has already selected this course
    $checkQuery = "SELECT * FROM course_selections WHERE Student_ID = :student_id AND Course_ID = :course_id";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo 'Already enrolled in this course';
    } else {
        // Insert the new selection
        $query = "INSERT INTO course_selections (Student_ID, Course_ID) VALUES (:student_id, :course_id)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        echo 'Enrolled successfully';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
