<?php
session_start();
require 'connection.php'; // Assuming connection.php is the file where PDO is set up

// Get student's email from session
$student_email = $_SESSION['student_email'];

// Get the student's semester
$sql_student = "SELECT Semester FROM students WHERE Email = :email";
$stmt = $pdo->prepare($sql_student);
$stmt->execute(['email' => $student_email]);
$student = $stmt->fetch();
$student_semester = $student['Semester'];

// Fetch course details (excluding core courses)
$sql_courses = "
SELECT c.Course_ID, c.course_code, c.CourseName, c.Description, c.Credits, c.Semester, et.Type_Name, i.Name as Instructor
FROM courses c
LEFT JOIN teaches t ON c.Course_ID = t.Course_ID
LEFT JOIN instructors i ON t.Instructor_ID = i.Instructor_ID
LEFT JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
WHERE c.Semester = :semester
AND et.Type_Name != 'Core'";

$stmt = $pdo->prepare($sql_courses);
$stmt->execute(['semester' => $student_semester]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return courses as JSON (optional, if using AJAX)
echo json_encode($courses);
?>
