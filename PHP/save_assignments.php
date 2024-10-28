<?php
include 'Connection.php'; // Include database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courses'])) {
    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // First, get the department ID of the logged-in instructor
        $stmt = $pdo->prepare("SELECT Department_ID FROM instructors WHERE Instructor_ID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $department_id = $stmt->fetchColumn();

        foreach ($_POST['courses'] as $instructor_id => $course_id) {
            if (!empty($course_id)) {
                // Check if this instructor already has a course assigned within the same department
                $stmt = $pdo->prepare("
                    SELECT teaches.Course_ID 
                    FROM teaches 
                    INNER JOIN courses ON teaches.Course_ID = courses.Course_ID
                    WHERE teaches.Instructor_ID = ? AND courses.Department_ID = ?
                ");
                $stmt->execute([$instructor_id, $department_id]);
                $existing_course_id = $stmt->fetchColumn();

                if ($existing_course_id) {
                    // If the instructor already has a course assigned within the department, update the assignment
                    $stmt = $pdo->prepare("
                        UPDATE teaches 
                        SET Course_ID = ? 
                        WHERE Instructor_ID = ? AND Course_ID = ?
                    ");
                    $stmt->execute([$course_id, $instructor_id, $existing_course_id]);
                } else {
                    // Otherwise, insert a new assignment for the instructor
                    $stmt = $pdo->prepare("
                        INSERT INTO teaches (Instructor_ID, Course_ID) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$instructor_id, $course_id]);
                }
            }
        }

        // After processing, redirect back to the main page to show the updated assignments
        header("Location: hodcourseapproval.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
