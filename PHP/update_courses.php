<?php
// Assuming you have already established a database connection
require_once 'Connection.php';
require_once 'Session.php';

$student_id = $_SESSION['student_id'];

// Fetch the student's current semester and courses in one go
$query = "
    SELECT s.Current_Semester, c.*
    FROM students s
    JOIN courses c ON s.Current_Semester = c.Semester
    WHERE s.Student_ID = :student_id
";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize courses by type
$core_courses = [];
$prof_courses = [];
$open_courses = [];
$major_minor_courses = [];

// Iterate over courses and organize them
foreach ($courses as $course) {
    switch ($course['Course_Type']) {
        case 'Core':
            $core_courses[] = $course;
            break;
        case 'Professional Elective':
            $prof_courses[] = $course;
            break;
        case 'Open Elective':
            $open_courses[] = $course;
            break;
        case 'Major/Minor':
            $major_minor_courses[] = $course;
            break;
    }
}

// Function to display courses
function displayCourses($courses, $enrollment = true) {
    foreach ($courses as $course) {
        echo "<div class='sub-course'>";
        echo "<h5>" . htmlspecialchars($course['Course_Name']) . "</h5>";
        echo "<p>" . htmlspecialchars($course['Description']) . "</p>";
        
        // Only show enroll button for courses that allow enrollment
        if ($enrollment) {
            echo "<button class='enroll-btn' data-course-id='" . $course['Course_ID'] . "'>Enroll in this course</button>";
        }
        
        echo "</div>";
    }
}
?>
