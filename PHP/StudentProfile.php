<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

$student_id = $_SESSION['student_id']; // Get logged-in student's ID

// Fetch student details
$sql = "SELECT First_Name, Middle_Name, Last_Name, Roll_No, University_No, Date_Of_Birth, Email, PhoneNo, Current_Semester, Bio, Profile_Picture, Department_ID
        FROM Students
        WHERE Student_ID = :student_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

//stored department id in session for course retrieval later on 
$_SESSION['department_id'] = $profile['Department_ID'];
$_SESSION['current_semester'] = $profile['Current_Semester'];
// Full name concatenation
$student_name = $profile['First_Name'] . " " . $profile['Last_Name'];

// Fetch courses the student is enrolled in, including grades (now part of `Grades` table)
$sql = "SELECT c.CourseName, c.Credits, c.Description, g.Semester, g.Year, g.IT1, g.IT2, g.IT3, g.Sem
        FROM Enrolls_In e
        JOIN Courses c ON e.Course_ID = c.Course_ID
        LEFT JOIN Grades g ON e.Course_ID = g.Course_ID AND e.Student_ID = g.Student_ID
        WHERE e.Student_ID = :student_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch recent notices (announcements)
$sql = "SELECT Announcement_ID, Title
        FROM Announcements
        ORDER BY Posting_Date DESC
        LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate grade based on total marks
function calculateGrade($totalMarks)
{
    if ($totalMarks >= 90) {
        return ['O', 10];
    } elseif ($totalMarks >= 80) {
        return ['A', 9];
    } elseif ($totalMarks >= 70) {
        return ['B', 8];
    } elseif ($totalMarks >= 60) {
        return ['C', 7];
    } elseif ($totalMarks >= 50) {
        return ['D', 6];
    } else {
        return ['F', 0];
    }
}

// Initialize variables
$totalCredits = 0;
$totalGradePoints = 0;
$sgpa = 0;
$currentSemesterCredits = 0;
$currentSemesterGradePoints = 0;

// Fetch SGPA and CGPA from the student_sgpa_cgpa view
$sql = "SELECT CGPA, 
               CASE
                   WHEN SGPA_Sem8 IS NOT NULL THEN SGPA_Sem8
                   WHEN SGPA_Sem7 IS NOT NULL THEN SGPA_Sem7
                   WHEN SGPA_Sem6 IS NOT NULL THEN SGPA_Sem6
                   WHEN SGPA_Sem5 IS NOT NULL THEN SGPA_Sem5
                   WHEN SGPA_Sem4 IS NOT NULL THEN SGPA_Sem4
                   WHEN SGPA_Sem3 IS NOT NULL THEN SGPA_Sem3
                   WHEN SGPA_Sem2 IS NOT NULL THEN SGPA_Sem2
                   WHEN SGPA_Sem1 IS NOT NULL THEN SGPA_Sem1
                   ELSE NULL
               END AS Current_SGPA
        FROM student_sgpa_cgpa 
        WHERE Student_ID = :student_id";

// Prepare and execute the statement to fetch SGPA and CGPA
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$sgpa_cgpa = $stmt->fetch(PDO::FETCH_ASSOC);

// // Debug: Check if CGPA and SGPA values are retrieved
// var_dump($sgpa_cgpa); // This will display the array
// exit(); // Stop further execution to review the output


// Format CGPA and SGPA to 2 decimal places
$cgpa = number_format((float)$sgpa_cgpa['CGPA'], 2);
$sgpa = number_format((float)$sgpa_cgpa['Current_SGPA'], 2);



// $cgpa = $totalCredits ? round($totalGradePoints / $totalCredits, 2) : 0;
// $sgpa = $currentSemesterCredits ? round($currentSemesterGradePoints / $currentSemesterCredits, 2) : 0;

$courseNames = [];
$courseMarks = [];

foreach ($courses as &$course) { // Use reference to modify each course
    // Check if IT marks and Sem marks are present
    $it1 = isset($course['IT1']) ? $course['IT1'] : 0;
    $it2 = isset($course['IT2']) ? $course['IT2'] : 0;
    $it3 = isset($course['IT3']) ? $course['IT3'] : 0;
    $semMarks = isset($course['Sem']) ? $course['Sem'] : 0;

    // Calculate average IT marks
    $averageIT = ($it1 + $it2 + $it3) / 3;

    // Total marks = average IT + Sem marks
    $totalMarks = $averageIT + $semMarks;

    // Add course name and total marks to the arrays
    $courseNames[] = $course['CourseName'];
    $courseMarks[] = $totalMarks;

    // Calculate grade and grade point
    list($grade, $gradePoint) = calculateGrade($totalMarks);

    // Add to course array
    $course['Average_IT'] = round($averageIT, 2);
    $course['Total_Marks'] = round($totalMarks, 2); // This line is the source of the error if Total_Marks is undefined.
    $course['Grade'] = $grade;
    $course['Grade_Point'] = $gradePoint;
}
unset($course); // Break the reference

// Prepare the teachers profile image SQL query
$query = "
    SELECT DISTINCT
        Instructors.Instructor_ID,
        Instructors.First_Name,
        Instructors.Middle_Name,
        Instructors.Last_Name,
        Instructors.Profile_Picture,
        Courses.CourseName
    FROM Enrolls_In
    INNER JOIN Courses ON Enrolls_In.Course_ID = Courses.Course_ID
    INNER JOIN Teaches ON Courses.Course_ID = Teaches.Course_ID
    INNER JOIN Instructors ON Teaches.Instructor_ID = Instructors.Instructor_ID
    WHERE Enrolls_In.Student_ID = :student_id;";

// Execute the query
$stmt = $pdo->prepare($query);
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Default profile picture path
$TdefaultImage = '../Assets/Profile.svg';


// Prepare students profile picture
// Prepare a query to fetch the profile picture path
$query = "SELECT Profile_Picture FROM students WHERE Student_ID = :student_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':student_id', $student_id);
$stmt->execute();

// Fetch the profile picture path
$profilePicture = $stmt->fetchColumn();

// Set a default image if no profile picture is found
$defaultImage = '../Assets/Profile.svg';
if (empty($profilePicture) || !file_exists($profilePicture)) {
    $profilePicture = $defaultImage;
}

// Fetch student details
$studentQuery = $pdo->prepare("SELECT Department_ID, Current_Semester FROM students WHERE Student_ID = :student_id");
$studentQuery->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$studentQuery->execute();
$student = $studentQuery->fetch(PDO::FETCH_ASSOC);

if ($student) {
    $department_id = $student['Department_ID'];
    $current_semester = $student['Current_Semester'];

    // Handle course enrollments and deletions via POST method
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_id = $_POST['course_id'];
        $action = $_POST['action'];

        if ($action === 'enroll') {
            // Check current course selections
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM course_selections WHERE Student_ID = :student_id AND Accepted = 0");
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            // Allow enrollment if under the limit
            if ($count < 4) {
                $enrollStmt = $pdo->prepare("INSERT INTO course_selections (Student_ID, Course_ID) VALUES (:student_id, :course_id)");
                $enrollStmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
                $enrollStmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
                $enrollStmt->execute();
                echo "Enrollment successful!";
            } else {
                echo "Enrollment limit reached!";
            }
        } elseif ($action === 'delete') {
            // Remove course selection
            $deleteStmt = $pdo->prepare("DELETE FROM course_selections WHERE Student_ID = :student_id AND Course_ID = :course_id");
            $deleteStmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
            $deleteStmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $deleteStmt->execute();
            echo "Enrollment removed!";
        }
    }

    // Fetch courses based on department, semester, and type
    $coreCoursesQuery = $pdo->prepare("
        SELECT courses.*, instructors.First_Name, instructors.Last_Name
        FROM courses
        LEFT JOIN teaches ON courses.Course_ID = teaches.Course_ID
        LEFT JOIN instructors ON teaches.Instructor_ID = instructors.Instructor_ID
        WHERE courses.Department_ID = :department_id AND courses.Semester = :current_semester
        AND courses.Enrollment_Type_ID = (SELECT Enrollment_Type_ID FROM enrollment_types WHERE Enrollment_Type_Name = 'Core')
    ");
    $coreCoursesQuery->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    $coreCoursesQuery->bindParam(':current_semester', $current_semester, PDO::PARAM_STR);
    $coreCoursesQuery->execute();
    $coreCourses = $coreCoursesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Professional Electives, Open Electives, Majors, and Minors
    $profElectivesQuery = $pdo->prepare("
        SELECT courses.*, instructors.First_Name, instructors.Last_Name
        FROM courses
        LEFT JOIN teaches ON courses.Course_ID = teaches.Course_ID
        LEFT JOIN instructors ON teaches.Instructor_ID = instructors.Instructor_ID
        WHERE courses.Department_ID = :department_id AND courses.Semester = :current_semester
        AND courses.Enrollment_Type_ID = (SELECT Enrollment_Type_ID FROM enrollment_types WHERE Enrollment_Type_Name = 'Professional Elective')
    ");
    $profElectivesQuery->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    $profElectivesQuery->bindParam(':current_semester', $current_semester, PDO::PARAM_STR);
    $profElectivesQuery->execute();
    $profElectives = $profElectivesQuery->fetchAll(PDO::FETCH_ASSOC);

    $openElectivesQuery = $pdo->prepare("
        SELECT courses.*, instructors.First_Name, instructors.Last_Name
        FROM courses
        LEFT JOIN teaches ON courses.Course_ID = teaches.Course_ID
        LEFT JOIN instructors ON teaches.Instructor_ID = instructors.Instructor_ID
        WHERE courses.Semester = :current_semester AND courses.Enrollment_Type_ID = (SELECT Enrollment_Type_ID FROM enrollment_types WHERE Enrollment_Type_Name = 'Open Elective')
    ");
    $openElectivesQuery->bindParam(':current_semester', $current_semester, PDO::PARAM_STR);
    $openElectivesQuery->execute();
    $openElectives = $openElectivesQuery->fetchAll(PDO::FETCH_ASSOC);

    $majorsMinorsQuery = $pdo->prepare("
        SELECT courses.*, instructors.First_Name, instructors.Last_Name
        FROM courses
        LEFT JOIN teaches ON courses.Course_ID = teaches.Course_ID
        LEFT JOIN instructors ON teaches.Instructor_ID = instructors.Instructor_ID
        WHERE courses.Department_ID = :department_id AND courses.Semester = :current_semester
        AND courses.Enrollment_Type_ID IN (SELECT Enrollment_Type_ID FROM enrollment_types WHERE Enrollment_Type_Name IN ('Major', 'Minor'))
    ");
    $majorsMinorsQuery->bindParam(':department_id', $department_id, PDO::PARAM_INT);
    $majorsMinorsQuery->bindParam(':current_semester', $current_semester, PDO::PARAM_STR);
    $majorsMinorsQuery->execute();
    $majorsMinors = $majorsMinorsQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Assets/icon.ico" type="image/x-icon">
    <link rel="icon" href="../Assets/icon.png" type="image/png">
    <link rel="stylesheet" href="../CSS/Preloader.css">
    <title>Profile</title>
    <style>
        /* CSS Variables for Light and Dark Modes */
        :root {
            --background-color: #f0f1f6;
            --dashboard-bg: white;
            --sidebar-bg: #273c75;
            --sidebar-hover-bg: #3c5a99;
            --text-color: #000000;
            --card-bg: white;
            --card-shadow: rgba(0, 0, 0, 0.1);
            --button-bg: #273c75;
            --button-text: white;
            --link-hover-color: #95c0c4;
            --profile-bg: #9dc3e2;
            --profile-border: #273c75;
            --courses-list-border: #273c75;
            --bio-border: #273c75;
            --form-bg: #ffffff;
            --form-text: #000000;
            --enroll-button-bg: #273c75;
            --enroll-button-text: white;
            --overlay-bg: rgba(0, 0, 0, 0.5);
            --expanded-card-bg: rgba(255, 255, 255, 0.95);
            --hover_box: #121212;
            --course_hover: #f0f0f0;
            --course_details: #5a7fa2;

            --icons-color: #ffffff;
            --icons-color-active: #000000;
            --icons-text-color: #ffffff;
        }

        body.dark-mode {
            --background-color: #121212;
            --dashboard-bg: #1e1e1e;
            --sidebar-bg: #2c2c2c;
            --sidebar-hover-bg: #333333;
            --text-color: #ffffff;
            --card-bg: #2c2c2c;
            --button-bg: #555555;
            --button-text: #ffffff;
            --link-hover-color: #b0c4de;
            --profile-bg: #2c2c2c;
            --profile-border: #555555;
            --courses-list-border: #555555;
            --bio-border: #555555;
            --form-bg: #3a3a3a;
            --form-text: #ffffff;
            --enroll-button-bg: #555555;
            --enroll-button-text: #ffffff;
            --overlay-bg: rgba(0, 0, 0, 0.7);
            --expanded-card-bg: rgba(50, 50, 50, 0.95);
            --hover_box: #212121;
            --course_hover: #333333;
            --course_details: #555555;

            --icons-color: #ffffff;
            --icons-color-active: #000000;
            --icons-text-color: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            color: var(--text-color);
            transition: background-color 0.3s, color 0.3s;
        }

        body {
            background: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .dashboard {
            display: flex;
            width: 100vw;
            height: 100vh;
            background-color: var(--dashboard-bg);
            overflow: hidden;
            position: relative;
        }

        /* Dark Mode Toggle Button */
        .toggle-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            cursor: pointer;
            width: 30px;
            height: 30px;
            padding: 0;
            z-index: 1000;
            transition: transform 0.3s;
        }

        .toggle-button img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .toggle-button:focus {
            outline: none;
        }

        /* Sidebar styling */
        .sidebar {
            width: 45px;
            height: 100%;
            background-color: var(--sidebar-bg);
            position: relative;
            transition: width 0.3s ease, background-color 0.3s ease;
            overflow: hidden;
        }


        .sidebar:hover {
            width: 200px;
            /* Expands on hover */
        }

        /* Sidebar hamburger icon */
        .sidebar-icon-container {
            display: flex;
            justify-content: flex-end;
            padding: 3px;
            position: relative;
        }

        /* Sidebar icon (hamburger menu) */
        .hamburger-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 24px;
            /* Adjusted height to fit 3 lines */
        }

        .sidebar-icon {
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 0;
            transition: 0.4s;
        }

        /* Sidebar content */
        .sidebar-content {
            padding: 20px;
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            transition-delay: 0s;
        }

        body.light-mode .sidebar-content h2 {
            color: #ffffff;
        }

        .sidebar:hover .sidebar-content {
            opacity: 1;
            transform: translateX(0);
            transition-delay: 0.3s;
        }

        .sidebar:hover .sidebar-content h2 {
            transition-delay: 0.3s;
        }

        .sidebar:hover .sidebar-content p {
            transition-delay: 0.4s;
        }

        /* Sidebar links */
        .sidebar-links a {
            display: block;
            padding: 10px 0;
            color: #FFFFFF;
            /* Default dark mode text color */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        body.light-mode .sidebar-links a {
            color: #cacaca;
            /* Darker color for light mode */
        }

        .sidebar-links a:hover {
            color: #2F9DFF;
            /* Hover effect for dark mode */
        }

        body.light-mode .sidebar-links a:hover {
            color: #4f8585;
            /* Hover effect for light mode */
        }

        /* Main Content Styles */
        .main-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            position: relative;
            /* For positioning expanded-courses */
        }

        /* Scrollbar Styling */
        .main-content::-webkit-scrollbar {
            width: 10px;
        }

        .main-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-content::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode .main-content::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode .main-content::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }

        /* Scrollbar Styling */
        .expanded-content::-webkit-scrollbar {
            width: 10px;
        }

        .expanded-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .expanded-content::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .expanded-content::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode .expanded-content::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode .expanded-content::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }

        /* Scrollbar Styling */
        .courses::-webkit-scrollbar {
            width: 10px;
        }

        .courses::-webkit-scrollbar-track {
            background: transparent;
        }

        .courses::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .courses::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode .courses::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode .courses::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }

        /* Scrollbar Styling for #edit-form */
        #edit-form::-webkit-scrollbar {
            width: 10px;
        }

        #edit-form::-webkit-scrollbar-track {
            background: transparent;
        }

        #edit-form::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        #edit-form::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode #edit-form::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode #edit-form::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }

        /* Card Styles */
        .card {
            background: var(--card-bg);
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 10px var(--card-shadow);
            margin-bottom: 20px;
            position: relative;
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        .top-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 320px;
            gap: 20px;
        }

        .middle-row {
            display: flex;
            justify-content: space-evenly;
            flex-wrap: wrap;
            flex: 0.5 0.5;
            gap: 20px;
        }

        .bottom-row {
            display: flex;
            justify-content: space-evenly;
            flex-wrap: wrap;
            gap: 20px;
        }

        .time-learning {
            display: flex;
            ;
            justify-content: center;
            align-items: center;
        }

        .marks-obtained,
        .time-learning,
        .courses {
            flex: 1;
            min-width: 250px;
            height: 150px;

        }

        .performance {
            flex: 1;
            min-width: 250px;
            z-index: 1;
            height: 300px;
        }

        /* Courses List with Vertical Colored Lines */
        .courses-list {
            list-style: none;
            padding: 20px 0 0 0;
            margin: 0;
        }

        .courses {
            overflow: auto;
        }

        .courses-list li {
            position: relative;
            padding-left: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            font-size: 1.2em;
        }

        .courses-list li::before {
            content: '';
            position: absolute;
            left: 0;
            height: 100%;
            width: 5px;
            background-color: var(--course-color, #273c75);
            border-radius: 2px;
        }

        .courses-list li:last-child {
            margin-bottom: 0;
        }

        /* Expand Button as SVG in Courses Card */
        .expand-button {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            cursor: pointer;
            width: 24px;
            height: 24px;
            padding: 0;
            transition: transform 0.3s;
        }

        .expand-button svg {
            width: 100%;
            height: 100%;
            display: block;
            fill: var(--text-color);
        }

        .expand-button:hover svg {
            transform: scale(1.1);
        }

        .expand-button:focus {
            outline: none;
        }

        /* Enroll Button in Sub-Courses */
        .sub-course button {
            padding: 5px 10px;
            background-color: var(--enroll-button-bg);
            color: var(--enroll-button-text);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            align-self: flex-start;
            margin-top: 10px;
        }

        .sub-course button:hover {
            background-color: #1e90ff;
        }

        /* Expanded Courses Overlay */
        .expanded-courses {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--overlay-bg);
            padding: 40px;
            z-index: 1001;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding-right: 47vh;
        }

        .expanded-card {
            width: 100%;
            max-width: 800px;
            max-height: 800px;
            margin: 0 auto;
            background-color: var(--expanded-card-bg);
            padding: 20px;
            border-radius: 10px;
            position: absolute;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;

        }

        .expanded-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .expanded-header h3 {
            margin: 0;
            color: var(--text-color);
        }

        .expanded-header svg {
            cursor: pointer;
            fill: #333;
            transition: transform 0.3s, fill 0.3s;
        }

        .expanded-header svg:hover {
            transform: scale(1.1);
            fill: #555;
        }

        .expanded-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 500px;
            /* Adjust this based on your layout */
            overflow-y: auto;
        }

        .course-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .course-nav button {
            padding: 10px 20px;
            background-color: var(--button-bg);
            color: var(--button-text);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .course-nav button:hover {
            background-color: #1e90ff;
        }

        .course-section {
            display: none;
            flex-direction: column;

        }

        .course-section.active {
            display: flex;
        }

        .sub-course {
            padding: 15px;
            margin: 20px;
            background-color: #f1f1f1;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sub-course h4 {
            margin-bottom: 10px;
        }

        body.dark-mode .sub-course {
            background-color: #3a3a3a;
        }

        /* Teacher History Styles */
        .Center-cards {
            height: 90%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .teacher-history {
            display: inline-block;
            height: 300px;
        }

        .teacher-history .teachers {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }

        .teacher-history .teacher {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100px;
        }

        .teacher-history .teacher img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid var(--profile-border);
        }

        .teacher-image-container {
            position: relative;
            display: inline-block;
        }

        .hover-box {
            display: none;
            /* Hidden by default */
            position: absolute;
            bottom: 100%;
            /* Position above the image */
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--hover_box);
            /* Background color with transparency */
            color: #fff;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            white-space: nowrap;
            /* Prevent text wrapping */
            z-index: 1;
        }

        .teacher-image-container:hover .hover-box {
            display: block;
            /* Show on hover */
        }

        .teacher img {
            width: 100px;
            /* Adjust size as needed */
            height: 100px;
            /* Adjust size as needed */
            border-radius: 50%;
            /* Make the image circular */
            object-fit: cover;
        }

        .teacher p {
            text-align: center;
            margin-top: 10px;
            /* Space between image and name */
            font-size: 16px;
            color: var(--text-color);
            /* Adjust color as needed */
        }



        /* Student Profile Card */
        .student-profile {
            width: 350px;
            background-color: var(--profile-bg);
            border-left: 2px solid var(--profile-border);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: background-color 0.3s, border-color 0.3s;
            height: 100%;
            position: relative;
            border-radius: 18px 0 0 18px;
        }

        .profile-top {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--bio-border);
        }

        .profile-top img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .profile-top .toggle-button {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            padding: 0;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 1;
        }

        .profile-top .toggle-button img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .profile-top .toggle-button:focus {
            outline: none;
        }

        .student-profile img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            border: 3px solid var(--profile-border);
            transition: border-color 0.3s;
        }

        .profile-middle {
            padding: 20px 0;
            border-bottom: 1px solid var(--bio-border);
            flex-grow: 1;
            overflow-y: auto;
        }

        .profile-middle p {
            margin: 8px 0;
            font-size: 1em;
        }

        .profile-bio {
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .bio-box {
            width: 100%;
            min-height: 100px;
            border: 1px solid var(--bio-border);
            border-radius: 5px;
            padding: 10px;
            background-color: var(--card-bg);
            transition: background-color 0.3s, border-color 0.3s;
        }

        .edit-profile-btn,
        .save-profile-btn,
        .cancel-profile-btn {
            background-color: var(--button-bg);
            color: var(--button-text);
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s, color 0.3s;
            width: fit-content;
            align-self: flex-end;
        }

        .edit-profile-btn:hover,
        .save-profile-btn:hover,
        .cancel-profile-btn:hover {
            background-color: #1e90ff;
        }

        .edit-profile-btn:focus,
        .save-profile-btn:focus,
        .cancel-profile-btn:focus {
            outline: none;
        }

        /* Edit Form Styles */
        .edit-form {
            display: none;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .edit-form.active {
            display: flex;
        }

        .edit-form label {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="tel"],
        .edit-form textarea {
            padding: 8px;
            border: 1px solid var(--bio-border);
            border-radius: 5px;
            background-color: var(--form-bg);
            color: var(--form-text);
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .edit-form input[type="file"] {
            padding: 5px;
        }

        .popup {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--course_details);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            width: 300px;
        }

        .popup-content {
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 0px;
            right: 0px;
            cursor: pointer;
            width: 24px;
            /* Adjust as needed */
            height: 24px;
            /* Adjust as needed */

            /* Reset any default styles */
            padding: 0;
            margin: 0;
            border: none;
            background: none;
            display: block;
            /* To remove any inline or extra space */
        }


        .close-btn:hover {
            opacity: 0.7;
            /* Optional: add hover effect for close button */
        }



        #marks-popup {
            display: none;
        }

        .courses-list li {
            cursor: pointer;
            /* Changes the cursor to a hand symbol */
            padding: 10px;
            margin: 5px 0;
            transition: background-color 0.3s ease;
        }

        .courses-list li:hover {
            background-color: var(--course_hover);
            /* Optional: Add a hover effect */
        }

        /* Container for the delete button */
        .delete-profile-pic-container {
            display: inline-flex;
            align-items: center;
            /* Align items vertically */
            margin-top: 10px;
            /* Spacing above the button */
        }

        /* Delete Button */
        .delete-profile-pic-btn {
            background-color: rgba(0, 0, 0, 0);
            /* Light gray background */
            color: #333;
            /* Dark text color for contrast */
            border: none;
            /* Remove border */
            border-radius: 50%;
            /* Round button */
            margin-left: 10px;
            /* Space between upload input and delete button */
            display: flex;
            align-items: center;
            /* Align image in center */
            justify-content: center;
            /* Center the icon */
            cursor: pointer;
            /* Pointer cursor on hover */
            transition: transform 0.3s ease, background-color 0.3s ease;
            /* Smooth transition on hover */
            font-family: 'Arial', sans-serif;
            font-size: 14px;
        }

        /* Change background color on hover */
        .delete-profile-pic-btn:hover {
            background-color: rgba(0, 0, 0, 0);
            /* Slightly darker gray */
        }

        /* Style for img icon inside the button */
        .delete-profile-pic-btn .delete-icon {
            width: 15px;
            /* Set the size of the icon */
            height: 15px;
            /* Set the size of the icon */
        }

        /* Optional: make the button slightly larger on hover */
        .delete-profile-pic-btn:hover {
            transform: scale(1.1);
            /* Slight zoom effect */
        }

        .course-section {
            margin-bottom: 40px;
        }

        .course-section h2 {
            color: #2c3e50;
            font-size: 24px;
        }

        .course-section ul {
            list-style-type: none;
            padding: 0;
        }

        .course-section li {
            margin: 10px 0;
        }

        .enroll-form {
            display: inline-block;
            margin-left: 20px;
        }

        .navigation {
            position: relative;

            width: 100px;
            height: 100vh;
            background: var(--sidebar-bg) !important;
            display: flex;
            justify-content: center;
            align-items: center;
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;
            z-index: 2;
        }

        .navigation ul {
            display: flex;
            flex-direction: column;
            width: 68px;

        }

        .navigation ul li {
            position: relative;
            list-style: none;
            width: 70px;
            height: 70px;
            z-index: 1;
        }

        .navigation ul li a {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            text-align: center;
            font-weight: 500;
        }

        .navigation ul li a .icons {
            position: relative;
            display: block;
            line-height: 65px;
            font-size: 1.7em;
            text-align: center;
            transition: 0.3s;
            color: var(--icons-color) !important;
        }

        ion-icon::part(icon) {
            color: var(--icons-color) !important;
        }


        .navigation ul li.active a .icons {
            color: var(--icons-color-active) !important;
            transform: translateX(47px);
        }

        /* Force the color for Ionicons */
        ion-icon {
            color: var(--icons-color) !important;
        }

        .navigation ul li.active ion-icon {
            color: var(--icons-color-active) !important;
        }

        .navigation ul li a .text {
            position: absolute;
            color: var(--icons-text-color);
            font-weight: 75em;
            right: 10px;
            font-size: 1em;
            letter-spacing: 0.05em;
            transition: 0.3s;
            opacity: 0;
            transform: translateX(-20px);
        }

        .navigation ul li.active a .text {
            opacity: 1;
            transform: translateX(-10px);
        }

        .indicator {
            position: absolute;
            left: 68%;
            width: 60px;
            height: 58px;
            background: #29fd53;
            border-radius: 50%;
            border: 6px solid var(--dashboard-bg) !important;

            transition: 0.3s ease;
        }

        /* bottom shadow */
        .indicator::before {
            content: '';
            position: absolute;
            left: 13%;
            /*left right allignment */
            bottom: -48%;
            /*top bottom allignment */
            width: 20px;
            height: 20px;
            background: transparent;
            border-top-right-radius: 20px;
            box-shadow: 10px -1px 0 0 var(--dashboard-bg) !important;
            transition: 0.3s ease;
        }

        /* top shadow */
        .indicator::after {
            content: '';
            position: absolute;
            left: 13%;
            top: -22px;
            width: 20px;
            height: 20px;
            background: transparent;
            border-bottom-right-radius: 20px;
            box-shadow: 10px 1px 0 0 var(--dashboard-bg) !important;

            transition: 0.3s ease;
        }

        .navigation ul li:hover a .text {
            opacity: 1;
            transform: translateX(-10px);
        }

        .navigation ul li:hover a .icons {
            color: var(--icons-color) !important;
            transform: translateX(47px);
        }

        .navigation ul li:nth-child(1).active~.indicator {
            transform: translateY(calc(70px * 0));
        }

        .navigation ul li:nth-child(2).active~.indicator {
            transform: translateY(calc(70px * 1));
        }

        .navigation ul li:nth-child(3).active~.indicator {
            transform: translateY(calc(70px * 2));
        }

        .navigation ul li:nth-child(4).active~.indicator {
            transform: translateY(calc(70px * 3));
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .student-profile {
                display: none;
                /* Hide profile on smaller screens */
            }
        }

        @media (max-width: 768px) {
            .sidebar-content h2 {
                font-size: 1em;
            }

            .sidebar-links a {
                font-size: 0.9em;
            }

            .main-content {
                padding: 10px;
            }

            .courses-list li {
                font-size: 1em;
            }

            .courses-list li::before {
                width: 4px;
            }
        }

        .CourseNavigation {
            position: relative;
            width: 400px;
            height: 64px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
        }

        .CourseNavigation ul {
            display: flex;
            width: 350px;

        }

        .CourseNavigation ul li {
            position: relative;
            list-style: none;
            width: 70px;
            height: 64px;
            z-index: 1;
        }

        .CourseNavigation ul li a {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            text-align: center;
            font-weight: 500;
        }

        .CourseNavigation ul li a .icons {
            position: relative;
            display: block;
            line-height: 70px;
            font-size: 1.5em;
            text-align: center;
            transition: 0.5s;
            color: var(--icons-color) !important;
        }

        ion-icon::part(icon) {
            color: var(--icons-color) !important;
        }


        .CourseNavigation ul li.active a .icons {
            color: var(--icons-color-active) !important;
            transform: translateY(-32px);
        }

        /* Force the color for Ionicons */
        ion-icon {
            color: var(--icons-color) !important;
        }

        .CourseNavigation ul li.active ion-icon {
            color: var(--icons-color-active) !important;
        }

        .CourseNavigation ul li a .text {
            position: absolute;
            color: var(--icons-text-color);
            font-weight: 0.75em;
            letter-spacing: 0.05em;
            transition: 0.5s;
            opacity: 0;
            transform: translateY(20px);
        }

        .CourseNavigation ul li.active a .text {
            opacity: 1;
            transform: translateY(10px);
        }

        .CourseIndicator {
            position: absolute;
            top: -50%;
            width: 70px;
            height: 64px;
            background: #29fd53;
            border-radius: 50%;
            border: 6px solid var(--dashboard-bg) !important;

            transition: 0.3s ease;
        }

        /* bottom shadow */
        .CourseIndicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -22px;
            width: 20px;
            height: 20px;
            background: transparent;
            border-top-right-radius: 20px;
            box-shadow: 1px -10px 0 0 var(--dashboard-bg) !important;
            transition: 0.3s ease;
        }

        /* top shadow */
        .CourseIndicator::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -22px;
            width: 20px;
            height: 20px;
            background: transparent;
            border-top-left-radius: 20px;
            box-shadow: -1px -10px 0 0 var(--dashboard-bg) !important;

            transition: 0.3s ease;
        }

        .CourseNavigation ul li:hover a .text {
            opacity: 1;
            transform: translateY(10px);
        }

        .CourseNavigation ul li:hover a .icons {
            color: var(--icons-color) !important;
            transform: translateY(-30px);
        }

        .CourseNavigation ul li:nth-child(1).active~.CourseIndicator {
            transform: translateX(calc(70px * 0));
        }

        .CourseNavigation ul li:nth-child(2).active~.CourseIndicator {
            transform: translateX(calc(70px * 1));
        }

        .CourseNavigation ul li:nth-child(3).active~.CourseIndicator {
            transform: translateX(calc(70px * 2));
        }

        .CourseNavigation ul li:nth-child(4).active~.CourseIndicator {
            transform: translateX(calc(70px * 3));
        }
    </style>
</head>

<body>
    <div id="preloader">
        <img src="../Assets/Game.svg" alt="Loading..." class="preloader-image" />
        <!-- <div class="spinner"></div> -->
    </div>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="navigation">
            <ul>
                <li class="list ">
                    <a href="../PHP/StudentLanding.php">
                        <span class="icons"><ion-icon name="home-outline" part="icon"></ion-icon></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                <li class="list active">
                    <a href="../PHP/StudentProfile.php">
                        <span class="icons"><ion-icon name="person-outline" part="icon"></ion-icon></span>
                        <span class="text">Profile</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/Announcements.php">
                        <span class="icons"><ion-icon name="notifications-outline" part="icon"></ion-icon></span>
                        <span class="text">Notice</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/Logout.php">
                        <span class="icons"><ion-icon name="log-out-outline" part="icon"></ion-icon></span>
                        <span class="text">Logout</span>
                    </a>
                </li>
                <div class="indicator"></div>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Top Row: Soft Skills, Marks Obtained -->
            <div class="top-row">
                <div class="card performance">
                    <p style="text-align: center;">Performance
                    <p>
                        <canvas id="performanceChart"></canvas> <!-- Radar chart canvas -->
                </div>
                <div class="card teacher-history">
                    <h3>Teachers</h3>

                    <div class="Center-cards">
                        <div class="teachers">
                            <?php foreach ($teachers as $teacher):
                                $fullName = htmlspecialchars(trim($teacher['First_Name'] . ' ' . $teacher['Middle_Name'] . ' ' . $teacher['Last_Name']));
                                $TProfilePath = '../Assets/ProfileImages/' . htmlspecialchars($teacher['Profile_Picture']);
                                $TprofilePicture = (!empty($teacher['Profile_Picture']) && file_exists($TProfilePath)) ? $TProfilePath : $TdefaultImage;
                            ?>
                                <div class="teacher">
                                    <div class="teacher-image-container">
                                        <img style="object-fit: cover;" src="<?php echo $TprofilePicture; ?>" alt="Prof. <?php echo $fullName; ?>">
                                        <div class="hover-box">
                                            <p>Course: <?php echo htmlspecialchars($teacher['CourseName']); ?></p>
                                        </div>
                                    </div>
                                    <p><?php echo $fullName; ?></p> <!-- Teacher's name always visible -->
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Middle Row: CGPA/SGPA, Courses -->
            <div class="middle-row">
                <div class="card time-learning">
                    <canvas id="cgpaChart" width="100" height="100"></canvas>
                </div>
                <div class="card time-learning">
                    <canvas id="sgpaChart" width="100" height="100"></canvas>
                </div>

                <div class="card courses">
                    <h3>Courses</h3>
                    <button class="expand-button" id="expand-courses">
                        <img src="../Assets/Expand.svg" />
                    </button>
                    <div class="courses-container">
                        <ul class="courses-list">
                            <?php foreach ($courses as $course): ?>
                                <li data-course-id="<?= htmlspecialchars($course['CourseName']); ?>"
                                    data-marks='{
                                        "IT1": <?= json_encode($course['IT1']); ?>,
                                        "IT2": <?= json_encode($course['IT2']); ?>,
                                        "IT3": <?= json_encode($course['IT3']); ?>,
                                        "Sem": <?= json_encode($course['Sem']); ?>
                                    }'
                                    data-description="<?= htmlspecialchars($course['Description']); ?>"
                                    data-credits="<?= htmlspecialchars($course['Credits']); ?>">
                                    <?= htmlspecialchars($course['CourseName']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

            </div>
            <!-- Popup structure -->
            <div id="marks-popup" class="popup">
                <div class="popup-content">
                    <button class="close-btn">
                        <img class="close-btn" src="../Assets/CLose.svg" alt="Close">
                    </button>
                    <h3 id="course-name"></h3>
                    <p id="description"></p>
                    <p>Credits: <span id="credits"></span></p>
                    <p>IT1 Marks: <span id="IT1"></span></p>
                    <p>IT2 Marks: <span id="IT2"></span></p>
                    <p>IT3 Marks: <span id="IT3"></span></p>
                    <p>Semester Marks: <span id="Sem"></span></p>
                </div>
            </div>

            <!-- Bottom Row: Teachers teached will be displayed based on the courses taken by the student -->
            <div class="bottom-row">
                <div class="card gemini">
                    <p>I will put something here</p>
                </div>
            </div>
        </div>

        <!-- Student Profile -->
        <div class="student-profile">
            <div class="profile-top">
                <img src="<?= htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
                <!-- Dark Mode Toggle Button -->
                <button class="toggle-button" id="mode-toggle" style="height: 5vh; width: 5vh;">
                    <img src="../Assets/Light_mode.svg" alt="Toggle Dark Mode" id="toggle-icon">
                </button>
            </div>
            <div class="profile-middle">
                <p><strong>Name:</strong> <?= htmlspecialchars($profile['First_Name'] . " " . $profile['Middle_Name'] . " " . $profile['Last_Name']); ?></p>
                <p><strong>Date of Birth:</strong> <?= htmlspecialchars($profile['Date_Of_Birth']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($profile['Email']); ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($profile['PhoneNo']); ?></p>
                <p><strong>Roll No.:</strong> <?= htmlspecialchars($profile['Roll_No']); ?></p>
                <p><strong>University No.:</strong> <?= htmlspecialchars($profile['University_No']); ?></p>
            </div>
            <div class="profile-bio">
                <p><strong>Bio:</strong></p>
                <div class="bio-box" id="bio-text"><?= htmlspecialchars($profile['Bio']); ?></div>
            </div>

            <button class="edit-profile-btn" id="edit-profile-btn">Edit Profile</button>

            <!-- Edit Profile Form -->
            <form class="edit-form" id="edit-form" method="POST" action="update_profile.php" enctype="multipart/form-data">
                <label>
                    Profile Photo:
                    <input type="file" name="profile-photo-input" id="profile-photo-input" accept="image/*" style="display: inline-flex;">
                </label>

                <label>
                    Bio:
                    <textarea name="bio-input" id="bio-input" rows="4" required><?= htmlspecialchars($profile['Bio']); ?></textarea>
                </label>

                <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top:10px;">
                    <button type="button" class="cancel-profile-btn" id="cancel-profile-btn">Cancel</button>
                    <button type="submit" class="save-profile-btn" id="save-profile-btn">Save</button>
                </div>
                <label style="display: flex; gap: 5px; padding-top:10px;">
                    University No:
                    <input type="text" id="university-no-input" value=" <?= htmlspecialchars($profile['University_No']); ?>" readonly>
                </label>
                <label style="display: flex; gap: 5px; padding-top:10px;">
                    Current Semester:
                    <input type="text" id="semester-input" value=" <?= htmlspecialchars($profile['Current_Semester']); ?>" readonly>
                </label>
                <label style="display: flex; gap: 5px; padding-top:10px;">
                    Email:
                    <input type="email" id="email-input" value=" <?= htmlspecialchars($profile['Email']); ?>" readonly>
                </label>
                <label style="display: flex; gap: 5px; padding-top:10px;">
                    Phone:
                    <input type="tel" id="phone-input" value=" <?= htmlspecialchars($profile['PhoneNo']); ?>" readonly>
                </label>
            </form>

        </div>

        <!-- Expanded Courses Overlay -->
        <div id="expanded-courses" class="expanded-courses">
            <div class="expanded-card">
                <div class="expanded-header">
                    <h3>Course Enrollment</h3>
                    <svg id="close-expanded-courses" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="course-nav">
                    <button id="current-courses-btn">Current Courses</button>
                    <button id="prof-electives-btn">Professional Electives</button>
                    <button id="open-electives-btn">Open Electives</button>
                    <button id="majors-minors-btn">Major/Minor</button>
                </div>
                <div class="expanded-content">
                    <!-- Core Courses Section -->
                    <div id="current-courses" class="course-section active">
                        <h4>Core Courses</h4>
                        <div id="core-courses-list">
                            <!-- Dynamically generated core courses will be inserted here -->
                        </div>
                    </div>

                    <!-- Professional Electives Section -->
                    <div id="prof-electives" class="course-section">
                        <h4>Professional Electives</h4>
                        <div id="prof-electives-list">
                            <!-- Dynamically generated professional elective courses will be inserted here -->
                        </div>
                    </div>

                    <!-- Open Electives Section -->
                    <div id="open-electives" class="course-section">
                        <h4>Open Electives</h4>
                        <div id="open-electives-list">
                            <!-- Dynamically generated open elective courses will be inserted here -->
                        </div>
                    </div>

                    <!-- Major/Minor Courses Section -->
                    <div id="majors-minors" class="course-section">
                        <h4>Major/Minor</h4>
                        <div id="majors-minors-list">
                            <!-- Dynamically generated major/minor courses will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script src="../JS/Preloader.js" ></script>
    <!-- navbar script -->
    <script>
        const list = document.querySelectorAll('.list');
        let currentActiveItem = document.querySelector('.list.active');

        function activeLink() {
            list.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
            currentActiveItem = this;
        }

        function hoverLink() {
            list.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
        }

        function leaveLink() {
            list.forEach((item) => item.classList.remove('active'));
            if (currentActiveItem) {
                currentActiveItem.classList.add('active');
            }
        }

        list.forEach((item) => {
            item.addEventListener('click', activeLink);
            item.addEventListener('mouseenter', hoverLink);
            item.addEventListener('mouseleave', leaveLink);
        });
    </script>
    <script
        type="module"
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script
        nomodule
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- // to use charts -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script> <!-- //to display cgpa and sgpa inside the doughnut chart -->
    <!-- profile courses details -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coursesList = document.querySelectorAll('.courses-list li');
            const popup = document.getElementById('marks-popup');
            const closeButton = document.querySelector('.close-btn');

            const courseNameElem = document.getElementById('course-name');
            const descriptionElem = document.getElementById('description');
            const creditsElem = document.getElementById('credits');
            const IT1Elem = document.getElementById('IT1');
            const IT2Elem = document.getElementById('IT2');
            const IT3Elem = document.getElementById('IT3');
            const SemElem = document.getElementById('Sem');

            // Show popup on course click
            coursesList.forEach(course => {
                course.addEventListener('click', function() {
                    // Get course details and marks from data attributes
                    const courseName = course.getAttribute('data-course-id');
                    const description = course.getAttribute('data-description');
                    const credits = course.getAttribute('data-credits');
                    const marks = JSON.parse(course.getAttribute('data-marks'));

                    // Fill in the popup with course details and marks
                    courseNameElem.textContent = courseName;
                    descriptionElem.textContent = description;
                    creditsElem.textContent = credits;
                    IT1Elem.textContent = marks.IT1 !== null ? marks.IT1 : 'N/A';
                    IT2Elem.textContent = marks.IT2 !== null ? marks.IT2 : 'N/A';
                    IT3Elem.textContent = marks.IT3 !== null ? marks.IT3 : 'N/A';
                    SemElem.textContent = marks.Sem !== null ? marks.Sem : 'N/A';

                    // Show the popup
                    popup.style.display = 'block';
                });
            });

            // Close the popup when the close button is clicked
            closeButton.addEventListener('click', function() {
                popup.style.display = 'none';
            });

            // Close the popup when clicking outside of it
            // Handle click outside the expanded card to close it
            pop.addEventListener('click', (e) => {
                if (e.target === popupDiv) {
                    popup.style.display = 'none';
                }
            });
        });
    </script>
    <!-- profile section updation -->
    <script>
        document.getElementById('edit-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Create a FormData object to capture form data, including file uploads
            const formData = new FormData(this);

            // Send the form data via Fetch API
            fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json()) // Parse the JSON response
                .then(data => {
                    if (data.status === 'success') {
                        // Display success message
                        showAlert('Profile updated successfully!', 'success');
                        setTimeout(() => {
                            window.location.reload(); // Refresh the page after 3 seconds
                        }, 3000);
                    } else {
                        // Display error message
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('An error occurred: ' + error.message, 'error');
                });
        });

        // // Handle profile picture deletion
        // document.getElementById('delete-profile-pic-btn').addEventListener('click', function() {
        //     if (confirm('Are you sure you want to delete your profile picture?')) {
        //         const formData = new FormData();
        //         formData.append('delete_profile_pic', 'true');

        //         // Send the request to the server
        //         fetch('update_profile.php', {
        //                 method: 'POST',
        //                 body: formData
        //             })
        //             .then(response => response.json()) // Parse the JSON response
        //             .then(data => {
        //                 if (data.status === 'success') {
        //                     showAlert('Profile picture deleted successfully!', 'success');
        //                     // Optionally, clear the profile picture from the UI
        //                     document.getElementById('profile-picture'); // Adjust based on your img element's ID
        //                 } else {
        //                     showAlert('Error: ' + data.message, 'error');
        //                 }
        //             })
        //             .catch(error => {
        //                 showAlert('An error occurred: ' + error.message, 'error');
        //             });
        //     }
        // });

        // Function to display success/error alerts
        function showAlert(message, type) {
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove(); // Ensure only one alert is shown at a time
            }

            const alertBox = document.createElement('div');
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message;

            // Append the alertBox directly to the body (avoids layout shift)
            document.body.appendChild(alertBox);

            // Remove the alert after 3 seconds
            setTimeout(() => {
                alertBox.remove();
            }, 3000);
        }

        // Styles for alert
        const style = document.createElement('style');
        style.textContent = `
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 9999;
            font-size: 14px;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .alert-success {
            background-color: #28a745; /* Green background */
            color: white;
        }
        .alert-error {
            background-color: #dc3545; /* Red background */
            color: white;
        }
    `;
        document.head.appendChild(style);
    </script>

    <!-- //student enrollment -->
    <script>


    </script>

    <script>
        // Profile Edit Functionality 
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const editForm = document.getElementById('edit-form');
        const bioText = document.getElementById('bio-text');
        const cancelProfileBtn = document.getElementById('cancel-profile-btn');
        const saveProfileBtn = document.getElementById('save-profile-btn');

        editProfileBtn.addEventListener('click', () => {
            editForm.style.display = 'block'; // Show the edit form
            editForm.classList.add('active');

            // Hide the edit button
            editProfileBtn.style.display = 'none'; // Hide the edit button

            // Hide static content
            document.querySelector('.profile-top').style.display = 'none';
            document.querySelector('.profile-middle').style.display = 'none';
            document.querySelector('.profile-bio').style.display = 'none';
        });

        cancelProfileBtn.addEventListener('click', () => {
            editForm.style.display = 'none'; // Hide the edit form
            editForm.classList.remove('active');

            // Show the edit button
            editProfileBtn.style.display = 'block'; // Show the edit button again

            // Show static content
            document.querySelector('.profile-top').style.display = 'flex';
            document.querySelector('.profile-middle').style.display = 'block';
            document.querySelector('.profile-bio').style.display = 'flex';
            // Reset form
            editForm.reset();
        });

        editForm.addEventListener('submit', (e) => {
            e.preventDefault(); // Prevent default form submission

            // Get form values
            // const profilePhotoInput = document.getElementById('profile-photo-input');
            const bioInput = document.getElementById('bio-input').value;

            // Check if a profile photo is already uploaded in the database
            const isProfilePhotoUploaded = <?= json_encode(!empty($profile['Profile_Picture'])); ?>; // Assume 'Profile_Photo' is the column name in the database

            // Update static content
            const profileMiddle = document.querySelector('.profile-middle');
            const profileMiddlePs = profileMiddle.querySelectorAll('p');
            // Update content based on inputs (Student name, Roll No, etc.)
            // Update bio text content
            bioText.textContent = bioInput;

            // Hide edit form and show static content
            editForm.style.display = 'none';
            editForm.classList.remove('active');

            // Show the edit button again
            editProfileBtn.style.display = 'block'; // Show the edit button

            document.querySelector('.profile-top').style.display = 'flex';
            document.querySelector('.profile-middle').style.display = 'block';
            document.querySelector('.profile-bio').style.display = 'flex';
        });
    </script>
    <!-- expanded courses js -->
    <script>
        // Function to generate random color in HEX format
        function getRandomColor() {
            const letters = '0123456789ABCDEF';
            let color = '#';
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Assign random colors to each course's vertical line
        function courseColor() {
            const courses = document.querySelectorAll('.courses-list li');
            courses.forEach(li => {
                const randomColor = getRandomColor();
                li.style.setProperty('--course-color', randomColor);
            })
        };
        courseColor();

        // Expand and Close functionality for Courses
        const expandCoursesBtn = document.getElementById('expand-courses');
        const expandedCoursesDiv = document.getElementById('expanded-courses');
        const closeExpandedCoursesBtn = document.getElementById('close-expanded-courses');

        // Handle expand button click
        expandCoursesBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent event from bubbling up
            expandedCoursesDiv.style.display = 'flex';
        });

        // Handle close button click
        closeExpandedCoursesBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent event from bubbling up
            expandedCoursesDiv.style.display = 'none';
        });

        // Handle click outside the expanded card to close it
        expandedCoursesDiv.addEventListener('click', (e) => {
            if (e.target === expandedCoursesDiv) {
                expandedCoursesDiv.style.display = 'none';
            }
        });

        // Switch between course sections
        const currentCoursesBtn = document.getElementById('current-courses-btn');
        const profElectivesBtn = document.getElementById('prof-electives-btn');
        const openElectivesBtn = document.getElementById('open-electives-btn');
        const majorminorBtn = document.getElementById('majors-minors-btn');

        currentCoursesBtn.addEventListener('click', () => {
            showCourseSection('current-courses');
        });

        profElectivesBtn.addEventListener('click', () => {
            showCourseSection('prof-electives');
        });

        openElectivesBtn.addEventListener('click', () => {
            showCourseSection('open-electives');
        });

        majorminorBtn.addEventListener('click', () => {
            showCourseSection('majors-minors');
        });

        function showCourseSection(sectionId) {
            const sections = document.querySelectorAll('.course-section');
            sections.forEach(section => {
                if (section.id === sectionId) {
                    section.classList.add('active');
                } else {
                    section.classList.remove('active');
                }
            });
        }

        // Enroll Button Functionality
        const enrollButtons = document.querySelectorAll('.enroll-btn');

        enrollButtons.forEach(button => {
            button.addEventListener('click', () => {
                const courseName = button.parentElement.querySelector('h5').innerText;
                alert(`You have successfully enrolled in ${courseName}!`);
                // Here, you can add additional functionality, such as sending data to the server
            });
        });
    </script>
    <!-- perform the insert/update/delete queries -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const profElectivesBtn = document.getElementById('prof-electives-btn');
            const openElectivesBtn = document.getElementById('open-electives-btn');
            const majorMinorBtn = document.getElementById('majors-minors-btn');

            // Fetch and display courses
            function fetchCourses() {
                fetch('fetch_Ecourses.php')
                    .then(response => response.json())
                    .then(data => {
                        // Update the relevant sections with fetched courses
                        document.getElementById('core-courses-list').innerHTML = data.core_courses;
                        document.getElementById('prof-electives-list').innerHTML = data.professional_electives;
                        document.getElementById('open-electives-list').innerHTML = data.open_electives;
                        document.getElementById('majors-minors-list').innerHTML = data.major_courses + data.minor_courses;
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Fetch courses on page load
            fetchCourses();

            // Enroll in a course
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('enroll-btn')) {
                    const courseId = event.target.getAttribute('data-course-id');

                    const formData = new FormData();
                    formData.append('course_id', courseId);

                    fetch('enroll_course.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            fetchCourses(); // Refresh courses after enrollment
                        })
                        .catch(error => console.error('Error:', error));
                }
            });

            // Delete enrollment request
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('delete-enroll-btn')) {
                    const courseId = event.target.getAttribute('data-course-id');

                    const formData = new FormData();
                    formData.append('course_id', courseId);

                    fetch('delete_enrollment.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            fetchCourses(); // Refresh courses after deletion
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>

    <script>
        // Dark mode toggle functionality
        const toggleButton = document.getElementById('mode-toggle');
        const toggleIcon = document.getElementById('toggle-icon');

        // Check local storage for saved mode preference
        const savedMode = localStorage.getItem('mode');
        if (savedMode) {
            document.body.classList.toggle('light-mode', savedMode === 'light');
            toggleIcon.src = savedMode === 'light' ? '../Assets/Light_mode.svg' : '../Assets/Dark_mode.svg';
        }

        // Redraw charts after mode change
        function redrawCharts() {
            cgpaChart.update(); // Redraw CGPA chart
            sgpaChart.update(); // Redraw SGPA chart
            performanceChart.update(); // Redraw Radar chart
        }
        // Function to apply mode
        function applyMode(mode) {
            if (mode === 'dark') {
                document.body.classList.add('dark-mode');
                toggleIcon.src = '../Assets/Dark_mode.svg'; // Path to Dark Mode icon
                toggleIcon.alt = 'Switch to Light Mode';
                courseColor(); // Assuming this function changes course-related colors
            } else {
                document.body.classList.remove('dark-mode');
                toggleIcon.src = '../Assets/Light_mode.svg'; // Path to Light Mode icon
                toggleIcon.alt = 'Switch to Dark Mode';
                courseColor(); // Assuming this function changes course-related colors
            }
            document.querySelectorAll('ion-icon').forEach(function(icon) {
                icon.style.color = getComputedStyle(document.documentElement).getPropertyValue('--icons-color');
            });
            console.log('Current mode:', mode, 'Dark mode class applied:', document.body.classList.contains('dark-mode'));

        }

        // Apply the saved mode on page load
        const currentMode = localStorage.getItem('mode') || 'dark'; // Default to dark mode if no preference is saved
        applyMode(currentMode);

        toggleButton.addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
            const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            applyMode(newMode);
            localStorage.setItem('mode', newMode);
            toggleIcon.src = newMode === 'light' ? '../Assets/Light_mode.svg' : '../Assets/Dark_mode.svg';

            console.log('Mode changed to:', newMode);

            // Check if dark mode class is applied
            console.log('Dark mode applied:', document.body.classList.contains('dark-mode'));
            // Redraw the doughnut charts with new colors
            const chartColors = getChartColors();
            cgpaChart.data.datasets[0].backgroundColor = chartColors.cgpa;
            sgpaChart.data.datasets[0].backgroundColor = chartColors.sgpa;
            cgpaChart.update();
            sgpaChart.update();

            // Redraw the radar chart with new colors
            const radarColors = getRadarChartColors();
            performanceChart.data.datasets[0].backgroundColor = radarColors.backgroundColor;
            performanceChart.data.datasets[0].borderColor = radarColors.borderColor;
            performanceChart.options.scales.r.ticks.color = radarColors.ticksColor;
            performanceChart.options.scales.r.grid.color = radarColors.gridColor;
            performanceChart.update(); // Update the radar chart

            updateRadarChartColors(); // Call to update other radar chart colors, if necessary
        });
    </script>


    <!-- //script for cgpa/sgpa chart -->
    <script>
        // Function to draw the text in the center of the doughnut chart
        function drawCenterText(chart, text) {
            const ctx = chart.ctx;
            const width = chart.width;
            const height = chart.height;

            // Split the text by line breaks if provided
            const lines = text.split('\n');

            // Check if light mode is enabled
            const isLightMode = document.body.classList.contains('light-mode');
            const textColor = isLightMode ? '#000' : '#fff'; // Black for light mode, white for dark mode

            ctx.restore();
            const fontSize = (height / 114).toFixed(2);
            ctx.font = fontSize + "em sans-serif";
            ctx.textBaseline = "middle";
            ctx.fillStyle = textColor; // Set text color based on the current mode

            // Calculate the Y-position for the first line (centered)
            const lineHeight = fontSize * 25; // Adjust this for more or less spacing between lines
            const textYStart = height / 2 - (lines.length - 1) * lineHeight / 2;

            // Draw each line with appropriate Y-coordinate
            lines.forEach((line, index) => {
                const textX = Math.round((width - ctx.measureText(line).width) / 2);
                const textY = textYStart + index * lineHeight;
                ctx.fillText(line, textX, textY);
            });

            ctx.save();
        }

        // Function to get current mode and return the colors for the doughnut chart
        function getChartColors() {
            const isLightMode = document.body.classList.contains('light-mode');

            return {
                // CGPA Chart Colors
                cgpa: isLightMode ? ['#F39C12', '#8E44AD'] // Light Mode
                    :
                    ['#1E90FF', '#FF5A5F'], // Dark Mode

                // SGPA Chart Colors
                sgpa: isLightMode ? ['#9B1B30', '#2C3E50'] // Light Mode
                    :
                    ['#00CED1', '#D5006D'] // Dark Mode
            };
        }



        // Doughnut chart for CGPA
        const cgpaCtx = document.getElementById('cgpaChart').getContext('2d');
        const cgpaColors = getChartColors();
        const cgpaChart = new Chart(cgpaCtx, {
            type: 'doughnut',
            data: {
                labels: ['CGPA', 'Remaining'],
                datasets: [{
                    data: [<?= $cgpa; ?>, 10 - <?= $cgpa; ?>], // Assume max GPA is 10
                    backgroundColor: cgpaColors.cgpa,
                    borderWidth: 0 // Remove the border
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // Hide the legend (labels)
                    }
                },
                cutout: '90%', // Thinner doughnut chart
                responsive: true,
                animation: {
                    animateScale: true
                }
            },
            plugins: [{
                afterDraw: function(chart) {
                    drawCenterText(chart, 'CGPA\n<?= $cgpa; ?>'); // Display CGPA in the center
                }
            }]
        });

        // Doughnut chart for SGPA
        const sgpaCtx = document.getElementById('sgpaChart').getContext('2d');
        const sgpaColors = getChartColors();
        const sgpaChart = new Chart(sgpaCtx, {
            type: 'doughnut',
            data: {
                labels: ['SGPA', 'Remaining'],
                datasets: [{
                    data: [<?= $sgpa; ?>, 10 - <?= $sgpa; ?>], // Assume max GPA is 10
                    backgroundColor: sgpaColors.sgpa,
                    borderWidth: 0 // Remove the border
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false // Hide the legend (labels)
                    }
                },
                cutout: '90%', // Thinner doughnut chart
                responsive: true,
                animation: {
                    animateScale: true
                }
            },
            plugins: [{
                afterDraw: function(chart) {
                    drawCenterText(chart, 'SGPA\n<?= $sgpa; ?>'); // Display SGPA in the center
                }
            }]
        });
    </script>
    <!-- //script for performance chart -->
    <script>
        // Fetch the course names and marks from PHP
        const courseNames = <?= json_encode($courseNames); ?>;
        const courseMarks = <?= json_encode($courseMarks); ?>;

        // Function to fetch updated courses and marks periodically
        function fetchUpdatedData() {
            fetch('fetch_courses_and_marks.php') // PHP file to return updated data in JSON
                .then(response => response.json())
                .then(data => {
                    updatePerformanceChart(data.courses, data.marks);
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // // Poll the server every 30 seconds for updated data
        // setInterval(fetchUpdatedData, 30000);

        // Function to update the radar chart dynamically
        function updatePerformanceChart(courses, marks) {
            // Update radar chart data
            performanceChart.data.labels = courses;
            performanceChart.data.datasets[0].data = marks;

            // Re-render the chart to reflect updated data
            performanceChart.update();
        }
        // Function to get colors for the radar chart based on the mode
        function getRadarChartColors() {
            const isLightMode = document.body.classList.contains('light-mode');
            return {
                backgroundColor: isLightMode ? 'rgba(75, 192, 192, 0.2)' : 'rgba(255, 193, 7, 0.2)',
                borderColor: isLightMode ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 193, 7, 1)',
                ticksColor: isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)',
                gridColor: isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)'
            };
        }

        // Initialize the radar chart for performance
        const radarColors = getRadarChartColors();
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: courseNames, // Dynamic course names from PHP
                datasets: [{
                    data: courseMarks, // Dynamic marks from PHP
                    backgroundColor: radarColors.backgroundColor, // Light color for radar area
                    borderColor: radarColors.borderColor, // Border color of the radar
                    borderWidth: 2 // Border width of the lines
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Allows the chart to scale with the container size
                scales: {
                    r: {
                        suggestedMin: 0, // Minimum value for the radar chart
                        suggestedMax: 125, // Max marks is 125
                        ticks: {
                            backdropColor: 'transparent', // Remove background color
                            color: radarColors.ticksColor,
                            stepSize: 25, // Set the interval between ticks (25, 50, 75, 100, 125) // Color based on mode
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)' // Grid line color
                        },
                        angleLines: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)' // Adjust angle line color
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 5, // Add padding at the top to ensure no overlap
                        bottom: 5 // Padding at the bottom for better spacing
                    }
                },
                plugins: {
                    legend: {
                        display: false // Hide the legend entirely
                    }
                }
            }
        });
        updateRadarChartColors(); // to update the color based on the mode the user is in

        // Function to update radar chart colors based on light/dark mode
        function updateRadarChartColors() {
            const isLightMode = document.body.classList.contains('light-mode');

            // Update tick color
            performanceChart.options.scales.r.ticks.color = isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)';

            // Update grid color
            performanceChart.options.scales.r.grid.color = isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';

            // Update chart colors
            performanceChart.data.datasets[0].backgroundColor = isLightMode ? 'rgba(75, 192, 192, 0.2)' : 'rgba(255, 193, 7, 0.2)';
            performanceChart.data.datasets[0].borderColor = isLightMode ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 193, 7, 1)';

            performanceChart.update();
        }
        fetchUpdatedData(); // <-- Important for initial chart rendering
        setInterval(fetchUpdatedData, 30000); // Poll every 30 seconds
    </script>
</body>

</html>