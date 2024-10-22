<?php
include('Connection.php');
include('Session.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['student_id'];
    $course_id = $_POST['course_id'];

    // Define limits for enrollment based on course type
    $enroll_limits = [
        'Professional Elective' => 2,
        'Open Elective' => 1,
        'Major' => 1,
        'Minor' => 1
    ];

    // Fetch course type
    $course_type_query = "SELECT et.Enrollment_Type_Name 
                          FROM courses c
                          JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
                          WHERE c.Course_ID = :course_id";
    $type_stmt = $pdo->prepare($course_type_query);
    $type_stmt->execute(['course_id' => $course_id]);
    $course_type = $type_stmt->fetchColumn();

    // Check how many courses the student has already enrolled in for the specific enrollment type
    $enrollment_check_query = "SELECT COUNT(*) as total_courses 
                               FROM course_selections cs
                               JOIN courses c ON cs.Course_ID = c.Course_ID
                               JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
                               WHERE cs.Student_ID = :student_id 
                               AND et.Enrollment_Type_Name = :course_type";
    $check_stmt = $pdo->prepare($enrollment_check_query);
    $check_stmt->execute(['student_id' => $student_id, 'course_type' => $course_type]);
    $total_courses = $check_stmt->fetchColumn();

    // Check if the student is trying to enroll in both a Major and a Minor
    if ($course_type === 'Major' || $course_type === 'Minor') {
        $major_minor_check_query = "SELECT COUNT(*) as major_minor_count 
                                    FROM course_selections cs
                                    JOIN courses c ON cs.Course_ID = c.Course_ID
                                    JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
                                    WHERE cs.Student_ID = :student_id
                                    AND (et.Enrollment_Type_Name = 'Major' OR et.Enrollment_Type_Name = 'Minor')";
        $major_minor_stmt = $pdo->prepare($major_minor_check_query);
        $major_minor_stmt->execute(['student_id' => $student_id]);
        $major_minor_count = $major_minor_stmt->fetchColumn();

        if ($major_minor_count >= 1) {
            echo "You can only enroll in either one Major or one Minor.";
            exit;
        }
    }

    // Check course limit for the enrollment type
    if ($total_courses < $enroll_limits[$course_type]) {
        // Insert the enrollment into course_selections
        $insert_query = "INSERT INTO course_selections (Student_ID, Course_ID) VALUES (:student_id, :course_id)";
        $insert_stmt = $pdo->prepare($insert_query);
        $insert_stmt->execute(['student_id' => $student_id, 'course_id' => $course_id]);
        
        echo "Enrollment successful";
    } else {
        echo "Enrollment limit reached for $course_type";
    }
}
?>
