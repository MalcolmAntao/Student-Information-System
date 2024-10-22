<?php 
// Database connection
include('Connection.php');
include('Session.php');

// Fetch the student's enrolled courses and corresponding marks
$student_id = $_SESSION['student_id']; // Assuming the student is logged in

$query = "SELECT C.CourseName, G.Grade_Received
          FROM Courses C
          JOIN Enrolls_In E ON C.Course_ID = E.Course_ID
          LEFT JOIN Grades G ON E.Student_ID = G.Student_ID AND E.Course_ID = G.Course_ID
          WHERE E.Student_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
$marks = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row['CourseName'];
    $marks[] = $row['Grade_Received'] ?? 0; // Default to 0 if no grade is received
}

echo json_encode([
    'courses' => $courses,
    'marks' => $marks
]);

$stmt->close();
$conn->close();
?>
