<?php
include('Connection.php');
include('Session.php');

$student_id = $_SESSION['student_id'];
$department_id = $_SESSION['department_id'];

// Fetch courses along with enrollment status
$query = "SELECT c.Course_ID, c.CourseName, c.Course_Code, c.Description, c.Credits, et.Enrollment_Type_Name, et.Enrollment_Type_ID, 
                 i.First_Name, i.Last_Name, 
                 (SELECT COUNT(*) FROM course_selections cs WHERE cs.Course_ID = c.Course_ID AND cs.Student_ID = :student_id) AS is_enrolled
          FROM courses c
          LEFT JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
          LEFT JOIN teaches t ON t.Course_ID = c.Course_ID
          LEFT JOIN instructors i ON i.Instructor_ID = t.Instructor_ID
          WHERE c.Department_ID = :department_id AND c.Semester = :current_semester";

$stmt = $pdo->prepare($query);
$stmt->execute([
    'department_id' => $department_id, 
    'current_semester' => $_SESSION['current_semester'], 
    'student_id' => $student_id
]);

$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($courses)) {
    echo json_encode(['error' => 'No courses available']);
} else {
    // Separate courses by category (Core, Professional Electives, Open Electives, Major, Minor)
    $core_courses = '';
    $professional_electives = '';
    $open_electives = '';
    $major_courses = '';
    $minor_courses = '';

    foreach ($courses as $course) {
        // Course HTML with dynamic buttons
        $course_html = "
            <div class='sub-course'>
                <h4>{$course['CourseName']}</h4>
                <p><b>Description:</b> {$course['Description']}</p>
                <p><b>Course Code:</b> {$course['Course_Code']}</p>
                <p><b>Credits:</b> {$course['Credits']}</p>
                <p><b>Instructor:</b> {$course['First_Name']} {$course['Last_Name']}</p>
        ";

        // Display buttons only for non-core courses
        if ($course['Enrollment_Type_ID'] != 5) { // Exclude core courses
            if ($course['is_enrolled'] > 0) {
                // If student is enrolled, show delete button
                $course_html .= "<button class='delete-enroll-btn' data-course-id='{$course['Course_ID']}'>Delete Enrollment</button>";
            } else {
                // If not enrolled, show enroll button
                $course_html .= "<button class='enroll-btn' data-course-id='{$course['Course_ID']}'>Enroll</button>";
            }
        }

        $course_html .= "</div>";

        // Group courses by enrollment type
        switch ($course['Enrollment_Type_ID']) {
            case 5: // Core Courses
                $core_courses .= $course_html;
                break;
            case 3: // Professional Electives
                $professional_electives .= $course_html;
                break;
            case 4: // Open Electives
                $open_electives .= $course_html;
                break;
            case 1: // Major
                $major_courses .= $course_html;
                break;
            case 2: // Minor
                $minor_courses .= $course_html;
                break;
        }
    }

    // Return JSON response with separated course sections
    echo json_encode([
        'core_courses' => (!empty($core_courses) ? $core_courses : '<p>No core courses available</p>'),
        'professional_electives' => (!empty($professional_electives) ? $professional_electives : '<p>No professional electives available</p>'),
        'open_electives' => (!empty($open_electives) ? $open_electives : '<p>No open electives available</p>'),
        'major_courses' => (!empty($major_courses) ? $major_courses : '<p>No major courses available</p>'),
        'minor_courses' => (!empty($minor_courses) ? $minor_courses : '<p>No minor courses available</p>')
    ]);
}
?>
