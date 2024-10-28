<?php
session_start();
include 'Connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

try {
    $dsn = "mysql:host=$host;dbname=studentdb;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the selected course ID from the form submission
    $course_id = $_POST['course_id'];
    
    // Get the list of approved students from the form
    $approved_students = $_POST['approved_students'] ?? [];

    // Start by resetting the "Accepted" field to 0 for all students in this course
    $stmt = $pdo->prepare("UPDATE course_selections SET Accepted = 0 WHERE Course_ID = ?");
    $stmt->execute([$course_id]);

    // Set "Accepted" to 1 for each student that was checked
    if (!empty($approved_students)) {
        $placeholders = implode(',', array_fill(0, count($approved_students), '?'));
        $stmt = $pdo->prepare("UPDATE course_selections SET Accepted = 1 WHERE Course_ID = ? AND Student_ID IN ($placeholders)");
        $stmt->execute(array_merge([$course_id], $approved_students));

        // Set success message
        $_SESSION['message'] = "Data updated successfully.";
    } else {
        // Set message for no students selected
        $_SESSION['message'] = "No students were selected for approval.";
    }

    // Redirect back to enrollment approval page
    header("Location: enrollapproval.php");
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
