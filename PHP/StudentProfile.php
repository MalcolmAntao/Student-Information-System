<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

$student_id = $_SESSION['student_id']; // Get logged-in student's ID

// Fetch student details
$sql = "SELECT First_Name, Middle_Name, Last_Name, Roll_No, University_No, Date_Of_Birth, Email, PhoneNo, Current_Semester, Bio, Major, Profile_Picture
        FROM Students
        WHERE Student_ID = :student_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

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
function calculateGrade($totalMarks) {
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
$defaultImage = '../Assets/Profile.svg';
$ProfilePath = '../Assets/ProfileImages/' . htmlspecialchars($profile['Profile_Picture']); // Assuming the images are stored in the 'ProfileImages' folder
if (!empty($profile['Profile_Picture'])) {
    // If the profile picture exists, display it from the stored path
    $profilePicture = $ProfilePath;
    if (!file_exists($profilePicture)) {
        $profilePicture = $defaultImage; // Fallback to default if image file not found
    }
} else {
    // If no profile picture is set, use a default image (with relative path)
    $profilePicture = $defaultImage; // Ensure this path is correct and points to your default image
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Dashboard UI</title>
    <style>
        /* CSS Variables for Light and Dark Modes */
        :root {
            --background-color: #f0f1f6;
            --dashboard-bg: white;
            --sidebar-bg: #273c75;
            --sidebar-hover-bg: #3c5a99;
            --text-color: #000;
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
            --hover_box: ;
            --course_hover:#f0f0f0;
            --course_details: #5a7fa2  ;
        }

        body.dark-mode {
            --background-color: #121212;
            --dashboard-bg: #1e1e1e;
            --sidebar-bg: #1f1f1f;
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
            --hover_box: ;
            --course_hover:#333333;
            --course_details: #555555;
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

        /* Sidebar Styles */
        .sidebar {
            width: 60px;
            height: 100%;
            background-color: var(--sidebar-bg);
            position: relative;
            transition: width 0.3s ease, background-color 0.3s ease;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 200px;
            background-color: var(--sidebar-hover-bg);
        }

        .sidebar-icon-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }

        .hamburger-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 20px;
        }

        .sidebar-icon {
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 3px 0;
            transition: 0.4s;
        }

        .sidebar-content {
            padding: 20px;
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            transition-delay: 0s;
        }

        .sidebar:hover .sidebar-content {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-content h2 {
            color: var(--text-color);
            margin-bottom: 20px;
            font-size: 1.2em;
            text-align: center;
        }

        .sidebar-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .sidebar-links a {
            color: white;
            text-decoration: none;
            font-size: 1em;
            transition: color 0.3s;
        }

        .sidebar-links a:hover {
            color: var(--link-hover-color);
        }

        /* Main Content Styles */
        .main-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            position: relative; /* For positioning expanded-courses */
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

        .top-row{
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            height: 100vh;
            gap: 20px;
        } 
        
        .middle-row, .bottom-row {
            display: flex;
            justify-content: space-evenly;
            flex-wrap: wrap;
            flex: 0.5 0.5;
            gap: 20px;
        }
        .time-learning
        {
            display: flex;;
            justify-content: center;
            align-items: center;
        }
        .performance, .marks-obtained,.time-learning,.courses {
            flex: 1;
            min-width: 250px;
            height: 300px;
            
        }

        /* Courses List with Vertical Colored Lines */
        .courses-list {
            list-style: none;
            padding: 20px 0 0 0;
            margin: 0;
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
            overflow-y: auto;
        }

        .expanded-card {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--expanded-card-bg);
            padding: 20px;
            border-radius: 10px;
            position: absolute;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
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
            gap: 15px;
        }

        .course-section.active {
            display: flex;
        }

        .sub-course {
            padding: 15px;
            background-color: #f1f1f1;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        body.dark-mode .sub-course {
            background-color: #3a3a3a;
        }

        /* Teacher History Styles */
        .Center-cards{
            height: 90%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .teacher-history{
            display: inline-block;
            
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
            display: none; /* Hidden by default */
            position: absolute;
            bottom: 100%; /* Position above the image */
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--hover_box); /* Background color with transparency */
            color: #fff;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            white-space: nowrap; /* Prevent text wrapping */
            z-index: 1;
        }

        .teacher-image-container:hover .hover-box {
            display: block; /* Show on hover */
        }

        .teacher img {
            width: 100px; /* Adjust size as needed */
            height: 100px; /* Adjust size as needed */
            border-radius: 50%; /* Make the image circular */
            object-fit: cover;
        }

        .teacher p {
            text-align: center;
            margin-top: 10px; /* Space between image and name */
            font-size: 16px;
            color: var(--text-color); /* Adjust color as needed */
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

        .edit-profile-btn, .save-profile-btn, .cancel-profile-btn {
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

        .edit-profile-btn:hover, .save-profile-btn:hover, .cancel-profile-btn:hover {
            background-color: #1e90ff;
        }

        .edit-profile-btn:focus, .save-profile-btn:focus, .cancel-profile-btn:focus {
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
            width: 24px;  /* Adjust as needed */
            height: 24px; /* Adjust as needed */
            
            /* Reset any default styles */
            padding: 0;
            margin: 0;
            border: none;
            background: none;
            display: block; /* To remove any inline or extra space */
        }


        .close-btn:hover {
            opacity: 0.7; /* Optional: add hover effect for close button */
        }



        #marks-popup {
            display: none;
        }

        .courses-list li {
            cursor: pointer; /* Changes the cursor to a hand symbol */
            padding: 10px;
            margin: 5px 0;
            transition: background-color 0.3s ease;
        }

        .courses-list li:hover {
            background-color: var(--course_hover); /* Optional: Add a hover effect */
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .student-profile {
                display: none; /* Hide profile on smaller screens */
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
    </style>
</head>
<body>
    <div class="dashboard">
        

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="../Assets/Hamburger.svg" alt="Menu" width="40" height="40">
                </div>
            </div>

            <div class="sidebar-content">
                <h2>Welcome back, Malcolm Antão</h2>
                <div class="sidebar-links">
                    <a href="../PHP/StudentLanding.php">Home</a>
                    <a href="../PHP/Announcements.php">Announcements</a>
                    <!-- <a href="settings.html">Settings</a> -->
                    <a href="../PHP/Logout.php">Logout</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Top Row: Soft Skills, Marks Obtained -->
            <div class="top-row">
                <div class="card performance">
                    <p style="text-align: center;">Performance<p>
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
                                        <img src="<?php echo $TprofilePicture; ?>" alt="Prof. <?php echo $fullName; ?>">
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
                        <img src="../Assets/Expand.svg"/>
                    </button>
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
                                data-credits="<?= htmlspecialchars($course['Credits']); ?>"
                            >
                                <?= htmlspecialchars($course['CourseName']); ?> (<?= htmlspecialchars($course['Semester']); ?> <?= htmlspecialchars($course['Year']); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
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
        <div class="student-profile" >
            <div class="profile-top">
                <img src="<?= htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
                <!-- Dark Mode Toggle Button -->
                <button class="toggle-button" id="mode-toggle" style="height: 5vh; width: 5vh;">
                    <img src="../Assets/Light_mode.svg" alt="Toggle Dark Mode" id="toggle-icon">
                </button>
            </div>
            <div class="profile-middle">
                <p><strong>Name:</strong> <?= htmlspecialchars($profile['First_Name'] . " " . $profile['Middle_Name']. " " . $profile['Last_Name']); ?></p>
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
            <form class="edit-form" id="edit-form" method="POST" action="update_profile.php" enctype="multipart/form-data" style="display: none; overflow:auto">
                <label>
                    Profile Photo:
                    <input type="file" name="profile-photo-input" id="profile-photo-input" accept="image/*">
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
                    Student Name:
                    <input type="text" id="student-name-input" value="<?= htmlspecialchars($profile['First_Name'] . " " . $profile['Middle_Name']. " " . $profile['Last_Name']); ?>" readonly>
                </label>
                <label style="display: flex; gap: 5px; padding-top:10px;">
                    Roll No:
                    <input type="text" id="roll-no-input" value=" <?= htmlspecialchars($profile['Roll_No']); ?>" readonly>
                </label>
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
            <h3>Expanded Courses</h3>
            <svg id="close-expanded-courses" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <!-- Close icon (X) -->
                <path d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <div class="expanded-content">
            <div class="course-nav">
                <button id="current-courses-btn">Current Courses</button>
                <button id="prof-electives-btn">Professional Electives</button>
                <button id="open-electives-btn">Open Electives</button>
                <button id="majors-minors-btn">Majors and Minors</button>
            </div>
            <div id="current-courses" class="course-section active">
                <h4>Current Courses</h4>
                <div class="sub-course">
                    <h5>Math Course</h5>
                    <p>Description for Math Course.</p>
                    <!-- No enroll button for Current Courses -->
                </div>
                <div class="sub-course">
                    <h5>Japanese Course</h5>
                    <p>Description for Japanese Course.</p>
                    <!-- No enroll button for Current Courses -->
                </div>
                <div class="sub-course">
                    <h5>English Course</h5>
                    <p>Description for English Course.</p>
                    <!-- No enroll button for Current Courses -->
                </div>
            </div>
            <div id="prof-electives" class="course-section">
                <h4>Professional Electives</h4>
                <div class="sub-course">
                    <h5>Elective 1</h5>
                    <p>Description for Elective 1.</p>
                    <button class="enroll-btn" data-course-id="1">Enroll in this course</button>
                </div>
                <div class="sub-course">
                    <h5>Elective 2</h5>
                    <p>Description for Elective 2.</p>
                    <button class="enroll-btn" data-course-id="2">Enroll in this course</button>
                </div>
            </div>
            <div id="open-electives" class="course-section">
                <h4>Open Electives</h4>
                <div class="sub-course">
                    <h5>Elective A</h5>
                    <p>Description for Elective A.</p>
                    <button class="enroll-btn" data-course-id="3">Enroll in this course</button>
                </div>
                <div class="sub-course">
                    <h5>Elective B</h5>
                    <p>Description for Elective B.</p>
                    <button class="enroll-btn" data-course-id="4">Enroll in this course</button>
                </div>
            </div>
            <div id="majors-minors" class="course-section">
                <h4>Majors and Minors</h4>
                <div class="sub-course" id="major-course-list"></div>
                <div class="sub-course" id="minor-course-list"></div>
            </div>
        </div>
    </div>
</div>


    </div>
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
        window.addEventListener('click', function(event) {
            if (event.target === popup) {
                popup.style.display = 'none';
            }
        });
    });

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
            const profilePhotoInput = document.getElementById('profile-photo-input');
            const bioInput = document.getElementById('bio-input').value;

            // Check if a profile photo is already uploaded in the database
            const isProfilePhotoUploaded = <?= json_encode(!empty($profile['Profile_Photo'])); ?>; // Assume 'Profile_Photo' is the column name in the database

            // Update static content
            const profileMiddle = document.querySelector('.profile-middle');
            const profileMiddlePs = profileMiddle.querySelectorAll('p');
            // Update content based on inputs (Student name, Roll No, etc.)
            
            // Update the profile photo if a new one is selected and if not already uploaded
            if (isProfilePhotoUploaded) {
                if (profilePhotoInput.files && profilePhotoInput.files[0]) {
                    alert("You can only upload the profile photo once."); // Alert if they try to upload again
                }
            } else {
                if (profilePhotoInput.files && profilePhotoInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.profile-top img').src = e.target.result;
                    }
                    reader.readAsDataURL(profilePhotoInput.files[0]);
                }
            }

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
        })};
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

        currentCoursesBtn.addEventListener('click', () => {
            showCourseSection('current-courses');
        });

        profElectivesBtn.addEventListener('click', () => {
            showCourseSection('prof-electives');
        });

        openElectivesBtn.addEventListener('click', () => {
            showCourseSection('open-electives');
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

        // Dark Mode Toggle Functionality
        const modeToggleButton = document.getElementById('mode-toggle');
        const modeToggleIcon = document.getElementById('toggle-icon');
        const currentMode = localStorage.getItem('mode') || 'light';

        // Function to apply mode
        function applyMode(mode) {
            if (mode === 'dark') {
                document.body.classList.add('dark-mode');
                modeToggleIcon.src = '../Assets/Dark_mode.svg'; // Path to Dark Mode icon
                modeToggleIcon.alt = 'Switch to Light Mode';
                courseColor()
            } else {
                document.body.classList.remove('dark-mode');
                modeToggleIcon.src = '../Assets/Light_mode.svg'; // Path to Light Mode icon
                modeToggleIcon.alt = 'Switch to Dark Mode';
                courseColor()
            }
        }
        
        // Apply the saved mode on page load
        applyMode(currentMode);
        function redrawCharts() {
            cgpaChart.update(); // Redraw CGPA chart
            sgpaChart.update(); // Redraw SGPA chart
        }
        // Toggle mode on button click
        modeToggleButton.addEventListener('click', () => {
            const mode = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            applyMode(mode);
            localStorage.setItem('mode', mode);
        });

        // Profile Dark Mode Toggle (if separate)
        const profileToggleButton = document.getElementById('profile-mode-toggle');
        const profileToggleIcon = document.getElementById('profile-toggle-icon');

        profileToggleButton.addEventListener('click', () => {
            const mode = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            applyMode(mode);
            localStorage.setItem('mode', mode);
            
        });

        // // Profile Edit Functionality
        // const editProfileBtn = document.getElementById('edit-profile-btn');
        // const editForm = document.getElementById('edit-form');
        // const bioText = document.getElementById('bio-text');
        // const cancelProfileBtn = document.getElementById('cancel-profile-btn');
        // const saveProfileBtn = document.getElementById('save-profile-btn');

        // editProfileBtn.addEventListener('click', () => {
        //     editForm.classList.add('active');
        //     // Hide static content
        //     document.querySelector('.profile-top').style.display = 'none';
        //     document.querySelector('.profile-middle').style.display = 'none';
        //     document.querySelector('.profile-bio').style.display = 'none';
        // });

        // cancelProfileBtn.addEventListener('click', () => {
        //     editForm.classList.remove('active');
        //     // Show static content
        //     document.querySelector('.profile-top').style.display = 'flex';
        //     document.querySelector('.profile-middle').style.display = 'block';
        //     document.querySelector('.profile-bio').style.display = 'flex';
        //     // Reset form
        //     editForm.reset();
        // });

        // editForm.addEventListener('submit', (e) => {
        //     e.preventDefault();
        //     // Get form values
        //     const profilePhotoInput = document.getElementById('profile-photo-input');
        //     const studentNameInput = document.getElementById('student-name-input').value;
        //     const rollNoInput = document.getElementById('roll-no-input').value;
        //     const universityNoInput = document.getElementById('university-no-input').value;
        //     const semesterInput = document.getElementById('semester-input').value;
        //     const emailInput = document.getElementById('email-input').value;
        //     const phoneInput = document.getElementById('phone-input').value;
        //     const bioInput = document.getElementById('bio-input').value;

        //     // Update static content
        //     const profileMiddle = document.querySelector('.profile-middle');
        //     const profileMiddlePs = profileMiddle.querySelectorAll('p');

        //     profileMiddlePs[0].innerHTML = `<strong>Student Name:</strong> ${studentNameInput}`;
        //     profileMiddlePs[1].innerHTML = `<strong>Roll No:</strong> ${rollNoInput}`;
        //     profileMiddlePs[2].innerHTML = `<strong>University No:</strong> ${universityNoInput}`;
        //     profileMiddlePs[3].innerHTML = `<strong>Current Semester:</strong> ${semesterInput}`;
        //     profileMiddlePs[4].innerHTML = `<strong>Email:</strong> ${emailInput}`;
        //     profileMiddlePs[5].innerHTML = `<strong>Phone:</strong> ${phoneInput}`;
        //     bioText.textContent = bioInput;

        //     // Update profile photo if a new one is selected
        //     if (profilePhotoInput.files && profilePhotoInput.files[0]) {
        //         const reader = new FileReader();
        //         reader.onload = function(e) {
        //             document.querySelector('.profile-top img').src = e.target.result;
        //         }
        //         reader.readAsDataURL(profilePhotoInput.files[0]);
        //     }

        //     // Hide edit form and show static content
        //     editForm.classList.remove('active');
        //     document.querySelector('.profile-top').style.display = 'flex';
        //     document.querySelector('.profile-middle').style.display = 'block';
        //     document.querySelector('.profile-bio').style.display = 'flex';
        // });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- // to use charts -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script>  <!-- //to display cgpa and sgpa inside the doughnut chart -->
    
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
            cgpa: isLightMode 
                ? ['#F39C12', '#8E44AD']   // Light Mode
                : ['#1E90FF', '#FF5A5F'],  // Dark Mode
            
            // SGPA Chart Colors
            sgpa: isLightMode 
                ? ['#9B1B30', '#2C3E50']   // Light Mode
                : ['#00CED1', '#D5006D']   // Dark Mode
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
            fetch('fetch_courses_and_marks.php')  // PHP file to return updated data in JSON
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
            labels: courseNames,  // Dynamic course names from PHP
            datasets: [{
                data: courseMarks,  // Dynamic marks from PHP
                backgroundColor: radarColors.backgroundColor,  // Light color for radar area
                borderColor: radarColors.borderColor,  // Border color of the radar
                borderWidth: 2  // Border width of the lines
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,  // Allows the chart to scale with the container size
            scales: {
                r: {
                    suggestedMin: 0,  // Minimum value for the radar chart
                    suggestedMax: 125,  // Max marks is 125
                    ticks: {
                        backdropColor: 'transparent', // Remove background color
                        color: radarColors.ticksColor,
                        stepSize: 25,  // Set the interval between ticks (25, 50, 75, 100, 125) // Color based on mode
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'  // Grid line color
                    },
                    angleLines: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)'  // Adjust angle line color
                    }
                }
            },
            layout: {
                padding: {
                    top: 5,  // Add padding at the top to ensure no overlap
                    bottom: 5  // Padding at the bottom for better spacing
                }
            },
            plugins: {
                legend: {
                    display: false  // Hide the legend entirely
                }
            }
        }
    });
    updateRadarChartColors();// to update the color based on the mode the user is in
        
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
