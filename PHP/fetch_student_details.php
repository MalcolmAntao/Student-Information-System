<?php
include 'Connection.php'; // Include database connection
session_start();

header('Content-Type: application/json');

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['student_id'])) {
        $studentId = $_POST['student_id'];

        // Fetch student details
        $stmt = $pdo->prepare("SELECT First_Name, Last_Name, Roll_No, Semester, Year, Contact_Info, Date_of_Birth FROM students WHERE Student_ID = :student_id");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $studentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch course details for the student
        $stmt = $pdo->prepare("
            SELECT c.CourseName, c.course_code, c.Credits, et.Enrollment_Type_Name, g.IT1_Marks, g.IT2_Marks, g.IT3_Marks, g.Sem_Marks
            FROM enrolls_in e
            JOIN courses c ON e.Course_ID = c.Course_ID
            JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
            LEFT JOIN grades g ON g.Student_ID = e.Student_ID AND g.Course_ID = c.Course_ID
            WHERE e.Student_ID = :student_id
        ");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $courseDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Send the response as JSON
        echo json_encode([
            'studentDetails' => $studentDetails,
            'courseDetails' => $courseDetails
        ]);
    } else {
        echo json_encode(['error' => 'Invalid student ID']);
    }

