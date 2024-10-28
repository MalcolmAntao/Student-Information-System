<?php
// Database connection setup using PDO
session_start();
include("Connection.php");

// Fetch teacher data from the database
$query = "SELECT * FROM instructors";
$stmt = $pdo->prepare($query);
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles from the database for the dropdown
$query_roles = "SELECT * FROM roles";
$stmt_roles = $pdo->prepare($query_roles);
$stmt_roles->execute();
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses from the database for the dropdown
$query_courses = "SELECT * FROM courses";
$stmt_courses = $pdo->prepare($query_courses);
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

// Ensure delete_id is being passed and is numeric
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare the delete query
    // Delete related announcements before deleting the instructor
    $deleteAnnouncements = $pdo->prepare("DELETE FROM announcements WHERE Author_ID = :Instructor_ID");
    $deleteAnnouncements->bindParam(':Instructor_ID', $delete_id, PDO::PARAM_INT);
    $deleteAnnouncements->execute();

    $deleteQuery = $pdo->prepare("DELETE FROM instructors WHERE Instructor_ID = :Instructor_ID");
    $deleteQuery->bindParam(':Instructor_ID', $delete_id, PDO::PARAM_INT);

    // Execute the query and check for errors
    if ($deleteQuery->execute()) {
        // If deletion is successful, redirect back to avoid re-triggering on page reload
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        // If an error occurs, log the error for debugging
        echo "Error deleting record: " . $deleteQuery->errorInfo()[2];
    }
}

// Handle form submission for role and course assignment
if (isset($_POST['assign_role'])) {
    $Instructor_ID = $_POST['Instructor_ID'];
    $Course_ID = $_POST['course'];
    $Role_ID = $_POST['role'];

    // Insert or update the assigned role and course for the instructor
    $assignQuery = $pdo->prepare("INSERT INTO instructor_assignments(Instructor_ID, Course_ID, Role_ID) 
                                  VALUES (:Instructor_ID, :Course_ID, :Role_ID)
                                  ON DUPLICATE KEY UPDATE Role_ID = :Role_ID, Course_ID = :Course_ID");
    $assignQuery->bindParam(':Instructor_ID', $Instructor_ID, PDO::PARAM_INT);
    $assignQuery->bindParam(':Course_ID', $Course_ID, PDO::PARAM_STR);
    $assignQuery->bindParam(':Role_ID', $Role_ID, PDO::PARAM_INT);

    if ($assignQuery->execute()) {
        // echo "<script>alert('Role and course assigned successfully!');</script>";
    } else {
        // echo "<script>alert('Error assigning role and course!');</script>";
    }

    // Fetch and reload updated data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetching instructors with assigned roles and courses
$sql = "SELECT i.*, r.Role_Name, c.CourseName, ia.Course_ID 
        FROM instructors i
        LEFT JOIN instructor_assignments ia ON i.Instructor_ID = ia.Instructor_ID
        LEFT JOIN roles r ON ia.Role_ID = r.Role_ID
        LEFT JOIN courses c ON ia.Course_ID = c.Course_ID";

$stmt = $pdo->query($sql);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle the remove access button click
if (isset($_POST['remove_access'])) {
    $Instructor_ID = $_POST['Instructor_ID'];

    // Update the instructor's course and role to NULL
    $removeAccessQuery = $pdo->prepare("UPDATE instructor_assignments SET Role_ID = NULL, Course_ID = NULL WHERE Instructor_ID = :Instructor_ID");
    $removeAccessQuery->bindParam(':Instructor_ID', $Instructor_ID, PDO::PARAM_INT);

    if ($removeAccessQuery->execute()) {
        echo "<script>alert('Access removed successfully!');</script>";
        // Redirect to prevent re-submission on page reload
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = $removeAccessQuery->errorInfo();
        echo "<script>alert('Error removing access: " . $error[2] . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Teacher Details</title>
</head>

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
        background-size: 300% 300%;
        animation: gradient-animation 15s ease infinite;
        transition: animation-duration 0.5s ease;
    }


    .gradient-bg-hover {
        background: var(--course-card-dark-gradient-hover);
        background-size: 300% 300%;
        animation: gradient-animation 10s ease infinite;
        transition: animation-duration 0.5s ease;
    }

    .gradient-sidebar {
        background: var(--sidebar-bg-color-dark);
        background-size: 400% 400%;
        animation: gradient-animation 15s ease infinite;
        transition: animation-duration 0.5s ease;
    }

    .gradient-card {
        background: var(--card-color-dark);
        background-size: 400% 400%;
        animation: gradient-animation 50s ease infinite;
        transition: animation-duration 0.5s ease;
    }

    body.light-mode .gradient-bg-hover {
        background: var(--course-card-light-gradient-hover);
        background-size: 400% 400%;
        animation: gradient-animation 10s ease infinite;
        transition: animation-duration 0.5s ease;
    }

    body.light-mode .gradient-sidebar {
        background: var(--sidebar-bg-color-light);
        background-size: 400% 400%;
        animation: gradient-animation 15s ease infinite;
        transition: animation-duration 0.5s ease;
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
        gap: 30px;
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
        width: 88vw;
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

    .search-bar h1 {
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
        height: 100vh;
        flex-wrap: wrap;
        overflow-y: auto;
        justify-content: space-around;
        box-shadow: var(--shadow-dark);
    }

    .courses::-webkit-scrollbar {
        display: none;
        /* Chrome, Safari, Opera */
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
        max-width: 81vw;
        max-height: 90vh;
        word-wrap: break-word;
        position: relative;
        cursor: pointer;
        transition: transform 0.3s ease;
        flex: 1;
        min-width: 200px;
        box-sizing: border-box;
        transform: scale(0.98);
        box-shadow: var(--shadow-card-details-dark);
    }

    .course-card:hover {
        transform: scale(1.05);
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
        width: 81vw;
        height: 70vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        opacity: 0;
        z-index: 0;
        transition: opacity 0.3s ease, z-index 0.3s ease;
    }

    body.light-mode .course-marks {
        background-color: rgba(180, 200, 202, 0.95);
        color: #000000;
    }

    /* On hover, show the marks card and hide the basic info */
    .course-card:hover .course-basic {
        opacity: 0;
        z-index: 0;
    }

    .course-card:hover .course-marks {
        opacity: 1;
        z-index: 1;
    }

    .course-marks p,
    .course-marks strong {
        line-height: 1.2;
        margin: 0;
        transition: color 0.3s ease;
    }

    /* Ensure "Marks Details" changes color along with other texts */
    .course-marks strong {
        color: var(--text-color-light);
    }

    body.light-mode .course-marks strong {
        color: black;
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
        transition: 0.5 ease;
        transform: scale(1.3);
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
        transition: 0.5 ease;
        transform: scale(1.3);
    }

    body.light-mode .toggle-button {
        background-color: var(--icon-bg-light);
        color: #000000;
    }

    body.light-mode .toggle-button:hover {
        background: var(--course-card-dark-gradient);
        background-size: 400% 400%;
    }

    /* Profile Card Styling */
    .profile-card {
        display: flex;
        align-items: center;
        height: 100%;
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
    }

    .profile-details p {
        margin: 0;
        font-size: 13px;
        display: flex;
        align-items: center;

    }

    .profile-details p strong {
        width: 120px;
        text-align: left;
        color: var(--bold-text-dark);
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
        background: var(--sidebar-bg-color-dark);
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
        border-radius: 50%;
        border: 6px solid var(--bg-color-dark);
        transition: 0.3s ease;
    }

    /* bottom shadow */
    .indicator::before {
        content: '';
        position: absolute;
        left: 13%;
        bottom: -48%;
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

    .navigation ul li:nth-child(5).active~.indicator {
        transform: translateY(calc(70px * 4));
    }

    .navigation ul li:nth-child(6).active~.indicator {
        transform: translateY(calc(70px * 5));
    }

    body.light-mode .navigation {
        box-shadow: var(--sidebar-shadow-light);
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

        .course-card {
            max-width: 100%;
        }
    }

    canvas {
        margin: 10px;
    }

    .chart-container {
        display: inline-flex;
        height: 100%;
        margin: 10px;
        justify-content: center;
        gap: 50px;
    }

    .chart-block {
        text-align: center;
    }

    .chart-block canvas {
        display: block;
        margin: 0 auto;
        max-width: 100%;
        max-height: 100%;
    }

    .colored-icon {
        font-weight: 500;
        color: var(--icon-color-dark);
    }

    body.light-mode .colored-icon {
        color: var(--icon-color-light);
    }

    .table-container {
        height: 70vh;
        /* Adjust this value as per your layout needs */
        background-color: inherit;
        /* padding-right: 10px; */
        margin-right: 10px;
        ;
        margin-left: 10px;
        margin-bottom: 20px;
        /* Space between the table and other content */
        overflow: auto;
        /* Enables scrolling */
        -ms-overflow-style: none;
        /* Hides scrollbar in Internet Explorer 10+ */
        scrollbar-width: none;
        /* Hides scrollbar in Firefox */
    }

    .table-container::-webkit-scrollbar {
        display: none;
        /* Hides scrollbar in Chrome, Safari, and Opera */
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #333;
        color: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease;
    }

    body.light-mode table {
        background-color: #e0e0e0;
        color: #000;
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    .table-container table th,
    .table-container table td {
        width: 50px;
        /* Set uniform width for all columns */
    }

    thead {
        position: sticky;
        top: 0;
        background-color: #444;
        /* Ensure header is visible during scroll */
    }

    th,
    td {
        padding: 15px;
        border: none;
        text-align: center;
    }

    th {
        background-color: #444;
        font-weight: bold;
    }

    body.light-mode th {
        background-color: #c0c0c0;
    }

    .add-more-btn {
        bottom: 20px;
        background-color: var(--gradient-bg-dark);
        color: white;
        border: none;
        padding: 16px 16px;
        border-radius: 50px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border: 4px solid black;
        color: black;
    }

    body.light-mode .add-more-btn {
        background-color: #c0c0c0;
        color: black;
    }

    .add-more-btn:active {
        background-color: #333;
    }

    body.light-mode .add-more-btn:active {
        background-color: #999;
    }

    .overlay {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        top: 0;
        /* Sit on top */
        left: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        background-color: rgba(0, 0, 0, 0.5);
        /* Black background with transparency */
        z-index: 999;
        /* Sit on top */
    }

    .popup {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        top: 50%;
        /* Center vertically */
        left: 50%;
        /* Center horizontally */
        transform: translate(-50%, -50%);
        /* Offset the center */
        background-color: #1e1e1e;
        /* Popup background color */
        padding: 20px;
        /* Padding inside the popup */
        border-radius: 8px;
        /* Rounded corners */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        /* Shadow effect */
        z-index: 1000;
        /* Sit on top */
    }

    .action-button {
        background-color: #1e1e1e;
        /* Black background */
        color: white;
        /* White text */
        padding: 10px 20px;
        /* Padding for the button */
        border: none;
        /* Remove default border */
        border-radius: 25px;
        /* Rounded corners */
        cursor: pointer;
        /* Pointer cursor on hover */
        font-size: 16px;
        /* Font size */
        transition: background-color 0.3s, transform 0.3s;
        /* Transition for hover effects */
        margin: 5px;
        /* Margin for spacing */
    }

    .assign_role {
        background-color: #1e1e1e;        /* Black background */
        color: white;        /* White text */
        border: none;        /* Remove default border */
        border-radius: 25px;
        /* Rounded corners */
        cursor: pointer;
        /* Pointer cursor on hover */
        font-size: 16px;
        /* Font size */
        transition: background-color 0.3s, transform 0.3s;
        /* Transition for hover effects */
    }

    .assign_role:hover {
        background-color: #c0c0c0;
        /* Darker black on hover */
        transform: scale(1.05);
        /* Slightly scale up on hover */
    }

    a {
        text-decoration: none;
    }

    .action-button:hover {
        background-color: #c0c0c0;
        /* Darker black on hover */
        transform: scale(1.05);
        /* Slightly scale up on hover */
    }

    .delete-button {
        background-color: #E52B50;
        /* Red background for delete button */
    }

    .delete-button:hover {
        background-color: darkred;
        /* Darker red on hover */
    }

    .action-button:focus {
        outline: none;
        /* Remove outline on focus */
    }
</style>

<body>
    <div class="container">

        <div class="navigation gradient-sidebar">
            <ul>
                <li class="list">
                    <a href="../PHP/NewAdminDashboard.php">
                        <span class="icons"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                <li class="list active">
                    <a href="../PHP/NewTeacherAccess.php">
                        <span class="icons"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="text">Teacher</span>
                    </a>
                </li>
                <li class="list ">
                    <a href="../PHP/NewStudentAccess.php">
                        <span class="icons"><ion-icon name="school-outline"></ion-icon></span>
                        <span class="text">Student</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/NewCourseAccess.php">
                        <span class="icons"><ion-icon name="book-outline"></ion-icon></span>
                        <span class="text">Courses</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/NewNotices.php">
                        <span class="icons"><ion-icon name="information-circle-outline"></ion-icon></span>
                        <span class="text">Notices</span>
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
                    <h1>Teacher Information</h1>
                    <!-- Icons (Reminders, Game, Profile) aligned to the right -->
                    <div class="icons">
                        <div class="icon gradient-bg">
                            <a href="../PHP/NewNotices.php"><span class="colored-icon"><ion-icon
                                        name="notifications-outline"
                                        style="width: 25px; height: 25px"></ion-icon></span></a>
                        </div>
                        <!-- <div class="icon gradient-bg">
                            <a href="../HTML/Game.html"><span class="colored-icon"><ion-icon name="game-controller-outline"
                                        style="width: 25px; height: 25px"></ion-icon></span></a>
                        </div> -->
                        <div class="icon gradient-bg">
                            <a href="../PHP/StudentProfile.php"><span class="colored-icon"><ion-icon name="person-outline"
                                        style="width: 25px; height: 25px"></ion-icon></span></a>
                        </div>
                        <!-- Dark Mode Toggle Button -->
                        <button class="toggle-button gradient-bg" id="toggle-mode">
                            <span class="colored-icon"><ion-icon name="sunny-outline"
                                    style="width: 25px; height: 25px"></ion-icon></span>
                        </button>
                    </div>
                </div>

                <div class="courses gradient-card">
                    <div class="course-card ">
                        <!-- Basic Info Card -->
                        <div class="course-basic" style=" display: flex; justify-content: center; align-items: center;">
                            <H1><em>Hover to Reveal Information</em></H1>
                        </div>
                        <!-- Marks Info Card (Initially hidden) -->
                        <div class="course-marks gradient-bg-hover">
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Teacher ID</th>
                                            <th>Teacher Name</th>
                                            <th>Gender</th>
                                            <th>Role</th>
                                            <th>Course</th>
                                            <th>Rights</th>
                                            <th>More Options
                                            <th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($teacher['Instructor_ID']) ?></td>
                                                <td><?= htmlspecialchars($teacher['First_Name']) ?></td>
                                                <td><?= htmlspecialchars($teacher['Gender']) ?></td>
                                                <td><?= htmlspecialchars($teacher['Role_Name'] ?? 'Not Assigned') ?></td>
                                                <td><?= htmlspecialchars($teacher['CourseName'] ?? 'Not Assigned') ?></td>
                                                <td>
                                                    <button class="action-button" onclick="openPopup(<?= htmlspecialchars($teacher['Instructor_ID']) ?>)">Provide</button>
                                                    <form method="post">
                                                        <input type="hidden" name="Instructor_ID" value="<?= htmlspecialchars($teacher['Instructor_ID']) ?>">
                                                        <button type="submit" name="remove_access" class="action-button" onclick="return confirm('Are you sure you want to remove access for this instructor?')">Remove</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <a href="NewEditTeacher.php?Instructor_ID=<?= htmlspecialchars($teacher['Instructor_ID']) ?>">
                                                        <button type="button" class="action-button">Edit</button>
                                                    </a>
                                                    <a href="NewTeacherAccess.php?delete_id=<?= htmlspecialchars($teacher['Instructor_ID'], ENT_QUOTES, 'UTF-8') ?>" onclick="return confirm('Are you sure you want to delete this instructor?')">
                                                        <button type="button" class="action-button delete-button">Delete</button>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="overlay" class="overlay"></div>
                            <!-- Popup for role and course assignment -->
                            <div id="popup" class="popup">
                                <div class="popup-content">
                                    <h3>Assign Role and Course</h3>
                                    <form method="post">
                                        <input type="hidden" id="instructorIdInput" name="Instructor_ID">
                                        <label for="role">Role:</label>
                                        <select id="role" name="role">
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['Role_ID'] ?>"><?= htmlspecialchars($role['Role_Name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="course">Course:</label>
                                        <select id="course" name="course">
                                            <?php foreach ($courses as $course): ?>
                                                <option value="<?= $course['Course_ID'] ?>"><?= htmlspecialchars($course['CourseName']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="assign_role">Assign</button>
                                        <button type="button" onclick="closePopup()">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            <a href="NewEntry(T)2.php">
                                <button class="add-more-btn"><ion-icon name="person-add-outline"></ion-icon></button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

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

    // Dark mode toggle functionality
    const toggleButton = document.getElementById('toggle-mode');
    const toggleIcon = toggleButton.querySelector('ion-icon'); // Change from img to ion-icon

    // Check local storage for saved mode preference
    const savedMode = localStorage.getItem('mode');
    if (savedMode) {
        document.body.classList.toggle('light-mode', savedMode === 'light');
        toggleIcon.setAttribute('name', savedMode === 'light' ? 'sunny-outline' : 'moon-outline'); // Update Ionicon
    }

    toggleButton.addEventListener('click', function() {
        document.body.classList.toggle('light-mode');
        const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
        localStorage.setItem('mode', newMode);

        // Update the Ionicon icon based on the mode
        toggleIcon.setAttribute('name', newMode === 'light' ? 'sunny-outline' : 'moon-outline'); // Toggle between icons
    });

    function openPopup(instructorId) {
        // Set the hidden input's value to the selected instructor ID
        document.getElementById('instructorIdInput').value = instructorId;
        // Display the overlay and popup
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('popup').style.display = 'block';
    }

    function closePopup() {
        // Hide the overlay and popup
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('popup').style.display = 'none';
    }
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</html>