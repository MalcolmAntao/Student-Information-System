<?php
include('Connection.php');
include('Session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['student_id'];
    $course_id = $_POST['course_id'];
    
    $delete_query = "DELETE FROM course_selections WHERE Student_ID = :student_id AND Course_ID = :course_id";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
    
    echo "Enrollment request deleted";
}
?>
