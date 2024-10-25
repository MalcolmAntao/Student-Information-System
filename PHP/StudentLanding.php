<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

$student_id = $_SESSION['student_id']; // Get logged-in student's ID

// Fetch student details
$sql = "SELECT First_Name, Middle_Name, Last_Name, Roll_No, University_No, Date_Of_Birth, Email, PhoneNo, Current_Semester, Bio, Profile_Picture
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Assets/icon.ico" type="image/x-icon">
    <link rel="icon" href="../Assets/icon.png" type="image/png">
    <link rel="stylesheet" href="../CSS/Preloader.css">

    <title>Homepage</title>
    <style>
        :root {
            --bg-color-dark: #120E0E;
            --bg-color-light: #d9e8e8;

            --text-color-dark: #ffffff;
            --text-color-light: #ffffff;

            --sidebar-bg-color-dark: linear-gradient(130deg,
                    hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);

            --sidebar-bg-color-light: linear-gradient(130deg,
                    hsl(196deg 49% 21%) 11%,
                    hsl(198deg 38% 25%) 29%,
                    hsl(199deg 32% 28%) 37%,
                    hsl(199deg 29% 31%) 41%,
                    hsl(200deg 27% 34%) 45%,
                    hsl(200deg 25% 36%) 48%,
                    hsl(200deg 24% 38%) 50%,
                    hsl(201deg 23% 40%) 52%,
                    hsl(201deg 22% 42%) 54%,
                    hsl(201deg 21% 44%) 55%,
                    hsl(201deg 21% 46%) 56%,
                    hsl(201deg 20% 48%) 57%,
                    hsl(202deg 20% 50%) 58%,
                    hsl(202deg 21% 51%) 59%,
                    hsl(202deg 22% 53%) 60%,
                    hsl(202deg 23% 54%) 61%,
                    hsl(202deg 24% 56%) 62%,
                    hsl(202deg 25% 57%) 63%,
                    hsl(202deg 26% 59%) 65%,
                    hsl(202deg 27% 60%) 66%,
                    hsl(202deg 29% 62%) 68%,
                    hsl(202deg 30% 63%) 70%,
                    hsl(202deg 32% 64%) 74%,
                    hsl(202deg 33% 66%) 79%,
                    hsl(202deg 35% 67%) 92%);

            --icons-color-dark: #ffffff;
            --icons-color-light: #ffffff;

            --icons-color-active-dark: #000000;
            --icons-color-active-light: #000000;

            --icon-color-dark: #000000;
            --icon-color-light: #000000;

            --sidebar-circe-dark: #f8290b;
            --sidebar-circe-light: #0f0;

            --card-color-dark: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);

            --card-color-light: radial-gradient(at top left,
                    hsl(202deg 33% 32%) 0%,
                    hsl(202deg 29% 35%) 22%,
                    hsl(202deg 27% 38%) 37%,
                    hsl(202deg 25% 41%) 46%,
                    hsl(202deg 24% 44%) 52%,
                    hsl(201deg 23% 46%) 56%,
                    hsl(201deg 22% 49%) 60%,
                    hsl(201deg 22% 51%) 64%,
                    hsl(201deg 23% 53%) 68%,
                    hsl(201deg 25% 55%) 73%,
                    hsl(201deg 26% 57%) 78%,
                    hsl(201deg 28% 59%) 85%,
                    hsl(201deg 30% 61%) 92%,
                    hsl(201deg 32% 63%) 100%);

            --shadow-dark: 0 0 10px rgba(0, 0, 0, 0.2);
            --shadow-light: 0 0 10px rgba(0, 0, 0, 0.5);

            --shadow-card-details-dark: var(--shadow-dark);
            --shadow-card-details-light: #F19B1A;

            --shadow-profile-dark: 0 0 10px rgba(25, 81, 10, 1);
            --shadow-profile-light: #74e857;

            --cot-dark: #D7FE65;
            --cot-light: #0f0;

            --cot-dark-hover: #D3F263;
            --cot-light-hover: #0f0;

            --gradient-bg-dark: radial-gradient(at right bottom, #EFEA75, #02D12F);
            --gradient-bg-light: linear-gradient(200deg, #ff7e5f, #feb47b, #86a8e7);

            --icon-bg-dark: #f8290b;
            --icon-bg-light: var(--card-color-light);

            --icon-bg-dark-hover: #E88010;
            --icon-bg-light-hover: #cccccc;

            --icon-gradient-bg-dark-hover: radial-gradient(at right bottom, #2980b9, #6dd5fa, #ffffff);
            --icon-gradient-bg-light-hover: #cccccc;

            --course-card-dark-gradient: radial-gradient(at right bottom, #EFEA75, #02D12F);
            --course-card-light-gradient: radial-gradient(at left top, rgba(0, 118, 255, 1) 0%, rgba(32, 157, 230, 1) 35%, rgba(109, 203, 255, 1) 69%, rgba(179, 229, 255, 1) 99%);

            --course-card-dark: #232323;
            --course-card-light: #f0f0f0;

            --course-card-dark-gradient-hover: radial-gradient(at right top, rgba(11, 181, 24, 1) 0%, rgba(152, 205, 120, 1) 28%, rgba(67, 216, 29, 1) 63%, rgba(219, 223, 147, 1) 93%);
            --course-card-light-gradient-hover: radial-gradient(at left top, rgba(20, 205, 230, 1) 0%, rgba(146, 203, 236, 1) 40%, rgba(214, 232, 243, 1) 77%, rgba(214, 232, 243, 1) 77%);

            --bold-text-dark: #08C922;
            --bold-text-light: #58baf3;

            --profile-border-dark: #54b23c;
            --profile-border-light: #74e857;
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color-dark);
            color: var(--text-color-dark);
            overflow-x: hidden;
            display: flex;
            justify-content: start;
            align-items: start;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        body.light-mode {
            background-color: var(--bg-color-light);
            color: var(--text-color-light);
        }

        /* Keyframes for gradient animation */
        @keyframes gradient-animation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }


        .gradient-bg {
            background: var(--course-card-dark-gradient);
            ;
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-bg-hover {
            background: var(--course-card-dark-gradient-hover);

            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 10s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-sidebar {
            background: var(--sidebar-bg-color-dark);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-card {
            background: var(--card-color-dark);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 50s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-bg-hover {
            background: var(--course-card-light-gradient-hover);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 10s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-sidebar {
            background: var(--sidebar-bg-color-light);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-bg {
            background: var(--icon-gradient-bg-dark-hover);
            background-size: 400% 400%;

        }

        body.light-mode .gradient-card {
            background: var(--card-color-light);
            background-size: 400% 400%;

        }

        .container {
            display: flex;
            background: var(--bg-color-dark);
            height: 100vh;
            transition: 0.3s ease;
        }

        body.light-mode .container {
            background: var(--bg-color-light);
        }


        /* Main content area divided into columns */
        .main-content {
            display: flex;
            flex: 4;
            padding: 20px;
            gap: 20px;
        }

        /* Column 2 layout */
        .column-2 {
            display: flex;
            flex-direction: column;
            flex: 2;
            gap: 20px;
        }

        /* Column 3 layout */
        .column-3 {
            display: flex;
            flex-direction: column;
            flex: 0.8;
            gap: 20px;
        }

        /* gpa and Date blocks smaller */
        .small-block {
            /* background-color: var(--card-color-dark); */
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            flex: 1;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-dark);
            /* For positioning course-marks */
        }

        .small-block p {
            margin: 0px;
            padding: 5px 0px;
        }

        .notice-block {
            display: flex;
            flex-direction: column;
            align-items: start;
            padding-left: 20px;
        }

        .notice-block a {
            color: #c1e1e2;
            text-decoration: none;
        }

        body.light-mode .notice-block a {
            background-color: transparent;
            color: var(--text-color-dark);
        }

        body.light-mode .small-block {
            box-shadow: var(--shadow-light);
            /* background: var(--card-color-light); */
        }


        /* Flex container for date and gpa to be side by side */
        .date-gpa {
            display: flex;
            gap: 20px;
            flex-shrink: 0;
        }

        /* Search bar styling */
        .search-bar {
            width: 98%;
            position: relative;
            top: 0;
        }

        .search-bar h2 {
            padding-top: 15px;
            color: var(--bold-text-dark);
        }

        body.light-mode h2 {
            color: var(--bold-text-light);
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #232B3A;
            color: #ffffff;
        }

        body.light-mode .search-bar input {
            background-color: #c3d8da;
            color: #000000;
        }


        /* Courses section */
        .courses {
            background-color: var(--card-color-dark);
            border-radius: 10px;
            padding: 20px;
            display: flex;
            gap: 10px;
            height: 70vh;
            flex-wrap: wrap;
            overflow-y: auto;
            justify-content: space-around;
            box-shadow: var(--shadow-dark);
        }

        body.light-mode .courses {
            background-color: var(--card-color-light);
            box-shadow: var(--shadow-light);
        }

        /* Course card container */
        .course-card {
            background-color: var(--course-card-dark);
            border-radius: 10px;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            max-width: 300px;
            max-height: 135px;
            height: 40%;
            word-wrap: break-word;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
            flex: 1;
            /* Ensure card grows and shrinks properly within the flex container */
            min-width: 200px;
            /* Prevent cards from getting too small */
            box-sizing: border-box;
            transform: scale(0.98);
            box-shadow: var(--shadow-card-details-dark);
        }

        .course-card:hover {
            transform: scale(1.05);
            /* Slightly enlarge on hover */
        }

        body.light-mode .course-card {
            background-color: var(--course-card-light);
            color: #000;
            box-shadow: var(--shadow-light);

        }

        /* Course basic info */
        .course-basic {
            padding: 10px;
            z-index: 1;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .font-size{
            padding: 5px;
            font-size: small;
        }

        .course-basic strong {
            color: var(--bold-text-dark);
        }

        body.light-mode .course-basic strong {
            color: var(--bold-text-light);
        }

        /* Course marks card (initially hidden under basic info) */
        .course-marks {
            background-color: var(--course-card-dark-gradient-hover);
            color: var(--text-color-light);
            border-radius: 10px;
            padding: 10px;
            box-sizing: border-box;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 0;
            /* Hidden by default */
            z-index: 0;
            /* Under the basic info */
            transition: opacity 0.3s ease, z-index 0.3s ease;
        }

        body.light-mode .course-marks {
            background-color: rgba(180, 200, 202, 0.95);
            /* Light mode background color */
            color: #000000;
        }

        /* On hover, show the marks card and hide the basic info */
        .course-card:hover .course-basic {
            opacity: 0;
            /* Fade out basic info */
            z-index: 0;
            /* Move it behind marks card */
        }

        .course-card:hover .course-marks {
            opacity: 1;
            /* Fade in marks card */
            z-index: 1;
            /* Bring it to the top */
        }

        .course-marks p,
        .course-marks strong {
            line-height: 1.2;
            margin: 0;
            transition: color 0.3s ease;
            /* Smooth transition for text color */
        }

        /* Ensure "Marks Details" changes color along with other texts */
        .course-marks strong {
            color: var(--text-color-light);
        }

        body.light-mode .course-marks strong {
            color: black;
            /* Change to black for light mode */
        }

        /* Icons styling */
        .icons {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
        }

        .icons:has(.icon) {
            padding-right: 20px;

        }

        .icon {
            padding: 10px;
            background-color: var(--icon-bg-dark);
            border-radius: 25%;
            cursor: pointer;
            height: 45px;
            transition: background-color 0.3s ease;
            transition: transform 0.5s ease;
        }

        .icon:hover {
            background: linear-gradient(to right, #a8ff78, #78ffd6);

            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */

            transition: 0.5 ease;
            transform: scale(1.3);
            /* Increase size by 20% on hover */
        }

        .toggle-button ion-icon {
            position: relative;
            top: 0.5px;
            left: 0.5px;
        }


        .icon:hover {
            background-color: var(--icon-bg-dark-hover);
        }

        body.light-mode .icon {
            background-color: var(--icon-bg-light);
        }

        body.light-mode .icon:hover {
            background-color: var(--icon-bg-light-hover);
        }

        .column-3 {
            padding-top: 15px;
        }

        /* Flexbox adjustments for third column */
        .column-3>.small-block {
            flex-shrink: 0;
        }

        /* Toggle button styling */
        .toggle-button {
            /* background-color: var(--icon-bg-dark); */
            border: none;
            height: 45px;
            border-radius: 25%;
            color: #ffffff;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            transition: transform 0.5s ease;
        }


        .toggle-button:hover {
            background: var(--icon-gradient-bg-dark-hover);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */

            transition: 0.5 ease;
            transform: scale(1.3);
            /* Increase size by 20% on hover */
        }

        body.light-mode .toggle-button {
            background-color: var(--icon-bg-light);
            color: #000000;
        }

        body.light-mode .toggle-button:hover {
            background: var(--course-card-dark-gradient);
            ;
            background-size: 400% 400%;

        }

        /* Profile Card Styling */
        .profile-card {
            display: flex;
            align-items: center;
            height: 100%;
            /* Align items to the start vertically */
            background-color: transparent;
            border-radius: 10px;
            padding: 10px;
            gap: 15px;
        }

        .profile-card strong {
            color: var(--bold-text-dark)
        }

        .small-block strong {
            color: var(--bold-text-dark);
        }

        body.light-mode .profile-card strong {
            color: var(--bold-text-light);
        }

        body.light-mode strong {
            color: var(--bold-text-light);
        }

        .profile-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: var(--shadow-profile-dark);
        }

        body.light-mode .profile-card {
            background-color: transparent;
        }

        body.light-mode .profile-card img {
            box-shadow: var(--shadow-profile-light);
        }

        .profile-details {
            display: grid;
            grid-template-columns: auto;
            row-gap: 0.1px;
            height: 130%;
            padding: 5px;
            overflow: auto;
            /* Removed column gap since labels and values are on the same line */
        }

        .profile-details p {
            margin: 0;
            font-size: 13px;
            display: flex;
            /* Use flex to align label and value */
            align-items: center;

        }

        .profile-details p strong {
            width: 120px;
            text-align: left;
            color: var(--bold-text-dark);
            /* Ensure labels are left-aligned */
            padding-right: 10px;
            font-weight: bold;
        }

        .profile-details strong {
            width: 110px !important;
        }

        /* Scrollbar Styling for.profile-details */
        .profile-details::-webkit-scrollbar {
            width: 10px;
        }

        .profile-details::-webkit-scrollbar-track {
            background: transparent;
        }

        .profile-details::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-dark);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .profile-details::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-dark-hover);
        }

        body.light-mode.profile-details::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-light);
        }

        body.light-mode.profile-details::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-light-hover);
        }

        .navigation {
            position: relative;

            width: 100px;

            /* background: var(--sidebar-bg-color-dark); */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 95vh;
            margin-left: 20px;
            margin-bottom: 20px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow-dark);
            transition: 0.3s ease;

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
            color: var(--icons-color-dark);
        }

        .navigation ul li.active a .icons {
            color: var(--icons-color-active-dark);
            transform: translateX(47px);
        }

        .navigation ul li a .text {
            position: absolute;
            color: var(--text-color-dark);
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
            /* background: var(--sidebar-circe-dark); */
            border-radius: 50%;
            border: 6px solid var(--bg-color-dark);
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
            box-shadow: 10px -1px 0 0 var(--bg-color-dark);
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
            box-shadow: 10px 1px 0 0 var(--bg-color-dark);

            transition: 0.3s ease;
        }

        .navigation ul li:hover a .text {
            opacity: 1;
            transform: translateX(-10px);
        }

        .navigation ul li:hover a .icons {
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

        body.light-mode .navigation {
            box-shadow: var(--sidebar-shadow-light);
            /* background: var(--sidebar-bg-color-light); */
        }

        body.light-mode .navigation ul li a .icons {
            color: var(--icons-color-light);
        }

        body.light-mode .navigation ul li.active a .icons {
            color: var(--icons-color-active-light);

        }

        body.light-mode .navigation ul li a .text {
            color: var(--text-color-dark);
        }

        body.light-mode .indicator {
            /* background: var(--sidebar-circe-light); */
            border: 6px solid var(--bg-color-light);
        }

        /* bottom shadow */
        body.light-mode .indicator::before {

            box-shadow: 10px -1px 0 0 var(--bg-color-light);
        }

        /* top shadow */
        body.light-mode .indicator::after {

            box-shadow: 10px 1px 0 0 var(--bg-color-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-card img {
                width: 80px;
                height: 80px;
            }

            .profile-details p strong {
                width: 100px;
            }

            /* Adjust course-card size on smaller screens */
            .course-card {
                max-width: 100%;
            }
        }

        canvas {
            margin: 10px;
            /* Add some margin to each chart */
        }


        .chart-container {
            display: inline-flex;
            height: 100%;
            margin: 10px;
            justify-content: center;
            /* Center the charts horizontally */
            gap: 50px;
            /* Add space between the charts */
        }

        .chart-block {
            text-align: center;
            /* Center the text inside each chart block */
        }

        .chart-block canvas {
            display: block;
            margin: 0 auto;
            /* Center the canvas inside the chart block */
            max-width: 100%;
            /* Ensure canvas does not overflow */
            max-height: 100%;
            /* Ensure canvas does not overflow */
        }

        .colored-icon {
            font-weight: 500;
            color: var(--icon-color-dark);
        }

        body.light-mode .colored-icon {
            color: var(--icon-color-light);
        }
    </style>
</head>

<body>
    <div id="preloader">
        <img src="../Assets/Game.svg" alt="Loading..." class="preloader-image" />
        <h3>Welcome Back <?php echo htmlspecialchars($student_name); ?></h3>
        <!-- <div class="spinner"></div> -->
    </div>
    <div class="container">
        <!-- Sidebar -->
        <div class="navigation gradient-sidebar">
            <ul>
                <li class="list active">
                    <a href="../PHP/StudentLanding.php">
                        <span class="icons"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                <li class="list ">
                    <a href="../PHP/StudentProfile.php">
                        <span class="icons"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="text">Profile</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/Announcements.php">
                        <span class="icons"><ion-icon name="notifications-outline"></ion-icon></span>
                        <span class="text">Notice</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/Logout.php">
                        <span class="icons"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="text">Logout</span>
                    </a>
                </li>
                <div class="indicator gradient-bg"></div>
            </ul>
        </div>

        <!-- Main content area with two columns -->
        <div class="main-content">
            <!-- Column 2 -->
            <div class="column-2">
                <!-- Search Bar -->
                <div class="search-bar">
                    <h2>Welcome back, <?php echo htmlspecialchars($student_name); ?></h2>
                </div>

                <!-- Date and GPA (side by side) -->
                <div class="date-gpa">
                    <div class="small-block gradient-card" id="date-block">
                        <p id="date-time" style="text-align:start; font-size:small; line-height:2em"></p>
                    </div>
                    <div class="small-block gradient-card">
                        <div class="chart-container">
                            <div class="chart-block">
                                <canvas id="cgpaChart" width="100" height="100"></canvas>
                            </div>
                            <div class="chart-block">
                                <canvas id="sgpaChart" width="100" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="small-block gradient-card">
                        <p>What to put here?</p>
                    </div>
                </div>

                <!-- Courses section with scrollable content -->
                <!-- Courses section with separate cards for basic info and marks -->
                <div class="courses gradient-card">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card ">
                            <!-- Basic Info Card -->
                            <div class="course-basic">
                                <strong><?php echo htmlspecialchars($course['CourseName']); ?></strong> <br>
                                <div class="font-size">
                                    <em>Description:</em> <?php echo htmlspecialchars($course['Description']); ?> <br>
                                    <em>Credits:</em> <?php echo htmlspecialchars($course['Credits']); ?>

                                </div>
                            </div>
                            <!-- Marks Info Card (Initially hidden) -->
                            <div class="course-marks gradient-bg-hover">
                                <strong>Marks Details</strong>
                                <p>Average IT Marks: <?= htmlspecialchars($course['Average_IT']); ?></p>
                                <p>Semester Marks: <?= htmlspecialchars($course['Sem']); ?></p>
                                <p>Total Marks: <?= htmlspecialchars($course['Total_Marks']); ?></p>
                                <p>Grade: <?= htmlspecialchars($course['Grade']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- Column 3 -->
            <div class="column-3">
                <!-- Icons (Reminders, Game, Profile) aligned to the right -->
                <div class="icons">
                    <div class="icon gradient-bg">

                        <a href="../PHP/Announcements.php"><span class="colored-icon"><ion-icon name="notifications-outline" style="width: 25px; height: 25px"></ion-icon></span></a>
                    </div>
                    <div class="icon gradient-bg">
                        <a href="../HTML/Game.html"><span class="colored-icon"><ion-icon name="game-controller-outline" style="width: 25px; height: 25px"></ion-icon></span></a>
                    </div>
                    <div class="icon gradient-bg">
                        <a href="../PHP/StudentProfile.php"><span class="colored-icon"><ion-icon name="person-outline" style="width: 25px; height: 25px"></ion-icon></span></a>
                    </div>
                    <!-- Dark Mode Toggle Button -->
                    <button class="toggle-button gradient-bg" id="toggle-mode">
                        <span class="colored-icon"><ion-icon name="sunny-outline" style="width: 25px; height: 25px"></ion-icon></span>
                    </button>
                </div>

                <!-- Performance block -->
                <div class="small-block gradient-card">
                    <p style="color: transparent;">Made by Malcolm Antao
                    <p>
                        <canvas id="performanceChart" style="height: 100%; margin:5px; position:relative; top:-10px;"></canvas> <!-- Radar chart canvas -->
                </div>

                <!-- Profile section -->
                <div class="small-block gradient-card">
                    <div class="profile-card">
                        <div style="flex-direction: column;">
                            <p><strong> </strong></p>
                            <img src="<?= htmlspecialchars($profilePicture); ?>" alt="Profile Picture">

                        </div>

                        <div class="profile-details">
                            <p><strong>Name:</strong> <?= htmlspecialchars($profile['First_Name'] . " " . $profile['Middle_Name'] . " " . $profile['Last_Name']); ?></p>
                            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($profile['Date_Of_Birth']); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($profile['Email']); ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($profile['PhoneNo']); ?></p>
                            <p><strong>Roll No.:</strong> <?= htmlspecialchars($profile['Roll_No']); ?></p>
                            <p><strong>University No.:</strong> <?= htmlspecialchars($profile['University_No']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Notices section -->
                <div class="small-block gradient-card" style="align-items: start;">
                    <p style="text-align: center; "><strong>Notices:</strong></p>
                    <ul class="notice-block">
                        <?php foreach ($announcements as $announcement): ?>
                            <li><a href="Announcements.php?id=<?= htmlspecialchars($announcement['Announcement_ID']); ?>">
                                    <?= htmlspecialchars($announcement['Title']); ?>
                                </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script src="../JS/Preloader.js"></script>
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

    <script>
        // Date and time display
        function updateDateTime() {
            const now = new Date();
            const optionsDate = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const optionsTime = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };

            const formattedDate = now.toLocaleDateString('en-US', optionsDate).replace(',', ''); // Format the date
            const formattedTime = now.toLocaleTimeString('en-US', optionsTime); // Format the time

            // Combine the formatted date and time with an HTML line break
            const dateTimeString = `DATE: ${formattedDate} <br> TIME: ${formattedTime}`;

            document.getElementById('date-time').innerHTML = dateTimeString; // Use innerHTML to render the <br> tag
        }


        // Update every minute
        setInterval(updateDateTime, 60000);
        updateDateTime(); // Initial call

        // Dark mode toggle functionality
        const toggleButton = document.getElementById('toggle-mode');
        const toggleIcon = toggleButton.querySelector('ion-icon'); // Change from img to ion-icon

        // Check local storage for saved mode preference
        const savedMode = localStorage.getItem('mode');
        if (savedMode) {
            document.body.classList.toggle('light-mode', savedMode === 'light');
            toggleIcon.setAttribute('name', savedMode === 'light' ? 'sunny-outline' : 'moon-outline'); // Update Ionicon
        }
        // Redraw charts after mode change
        function redrawCharts() {
            cgpaChart.update(); // Redraw CGPA chart
            sgpaChart.update(); // Redraw SGPA chart
        }

        toggleButton.addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
            const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('mode', newMode);

            // Update the Ionicon icon based on the mode
            toggleIcon.setAttribute('name', newMode === 'light' ? 'sunny-outline' : 'moon-outline'); // Toggle between icons

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- // to use charts -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script> <!-- //to display cgpa and sgpa inside the doughnut chart -->

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
            const textColor = isLightMode ? '#ffffff' : '#ffffff'; // Black for light mode, white for dark mode

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
                    ['#08C922', '#FF5A5F'], // Dark Mode

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
                cutout: '80%', // Thinner doughnut chart
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
                cutout: '80%', // Thinner doughnut chart
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
            fetch('fetch_courses_and_marks.php')
                .then(response => response.json())
                .then(data => {
                    updatePerformanceChart(data.courses, data.marks);
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Function to update the radar chart dynamically
        function updatePerformanceChart(courses, marks) {
            performanceChart.data.labels = courses;
            performanceChart.data.datasets[0].data = marks;
            performanceChart.update();
        }

        // Function to get colors for the radar chart based on the mode
        function getRadarChartColors() {
            const isLightMode = document.body.classList.contains('light-mode');
            return {
                backgroundColor: isLightMode ? 'rgba(153, 204, 255, 0.2)' : 'rgba(184, 245, 170, 0.2)',
                borderColor: isLightMode ? 'rgba(0, 140, 255,1)' : 'rgba(54, 162, 22, 1)',
                ticksColor: isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)',
                gridColor: isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)',
                pointLabelsColor: isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)' // Color for course titles
            };
        }

        // Initialize the radar chart for performance
        const radarColors = getRadarChartColors();
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: courseNames,
                datasets: [{
                    data: courseMarks,
                    backgroundColor: radarColors.backgroundColor,
                    borderColor: radarColors.borderColor,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        suggestedMin: 0,
                        suggestedMax: 125,
                        ticks: {
                            backdropColor: 'transparent',
                            color: radarColors.ticksColor,
                            stepSize: 25
                        },
                        grid: {
                            color: radarColors.gridColor
                        },
                        angleLines: {
                            color: radarColors.gridColor
                        },
                        pointLabels: {
                            color: radarColors.pointLabelsColor // Set color for course titles
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Function to update radar chart colors based on light/dark mode
        function updateRadarChartColors() {
            const isLightMode = document.body.classList.contains('light-mode');
            const radarColors = getRadarChartColors();

            // Update chart properties for colors
            performanceChart.options.scales.r.ticks.color = radarColors.ticksColor;
            performanceChart.options.scales.r.grid.color = radarColors.gridColor;
            performanceChart.options.scales.r.pointLabels.color = radarColors.pointLabelsColor;

            performanceChart.data.datasets[0].backgroundColor = radarColors.backgroundColor;
            performanceChart.data.datasets[0].borderColor = radarColors.borderColor;

            performanceChart.update();
        }

        // Fetch initial data and set up intervals
        fetchUpdatedData();
        setInterval(fetchUpdatedData, 30000);
        setInterval(updateRadarChartColors, 30000); // Ensure colors update periodically
    </script>


</body>

</html>