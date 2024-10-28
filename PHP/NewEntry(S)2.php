<?php
// Database connection
session_start();
include('Connection.php');

// Include bcrypt for password hashing (built-in password_hash function)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data
    $First_Name = trim($_POST['First_Name']);
    $Middle_Name = trim($_POST['Middle_Name']);
    $Last_Name = trim($_POST['Last_Name']);
    $Gender = trim($_POST['Gender']);
    $Roll_No = trim($_POST['Roll_No']);
    $University_No = trim($_POST['University_No']);
    $Date_Of_Birth = trim($_POST['Date_Of_Birth']);
    $Email = trim($_POST['Email']);
    $PhoneNo = trim($_POST['PhoneNo']);
    $Current_Semester = trim($_POST['Current_Semester']);
    $Department_ID = trim($_POST['Department_ID']);

    // Validate input
    if (empty($First_Name) || empty($Last_Name) || empty($Roll_No) || empty($University_No) || empty($Email)) {
        $_SESSION['error'] = "Required fields are missing!";
        header('Location: NewEntry(S).php');
        exit;
    }

    // Generate a password based on first and last name
    $password = $First_Name . $Last_Name . "_123";

    // Hash the password using bcrypt
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Start transaction to ensure consistency between 'students' and 'users' tables
        $pdo->beginTransaction();

        // Insert student data into 'students' table
        $sql = "INSERT INTO students (First_Name, Middle_Name, Last_Name, Gender, Roll_No, University_No, Date_Of_Birth, Email, PhoneNo, Current_Semester, Department_ID) 
        VALUES (:First_Name, :Middle_Name, :Last_Name, :Gender, :Roll_No, :University_No, :Date_Of_Birth, :Email, :PhoneNo, :Current_Semester, :Department_ID)";

        // Bind parameters
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':First_Name', $First_Name);
        $stmt->bindValue(':Middle_Name', $Middle_Name);
        $stmt->bindValue(':Last_Name', $Last_Name);
        $stmt->bindValue(':Gender', $Gender);
        $stmt->bindValue(':Roll_No', $Roll_No);
        $stmt->bindValue(':University_No', $University_No);
        $stmt->bindValue(':Date_Of_Birth', $Date_Of_Birth);
        $stmt->bindValue(':Email', $Email);
        $stmt->bindValue(':PhoneNo', $PhoneNo);
        $stmt->bindValue(':Current_Semester', $Current_Semester);
        $stmt->bindValue(':Department_ID', $Department_ID);

        // Execute the statement
        if ($stmt->execute()) {
            // Fetch the role_id from the roles table (assuming role is student)
            $roleSql = "SELECT Role_ID FROM roles WHERE Role_Name = 'Student'";  // Assuming 'Student' role
            $roleStmt = $pdo->query($roleSql);
            $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
            $Role_ID = $role['Role_ID'];

            // Insert user data into 'users' table
            $userSql = "INSERT INTO users (Email, Password, Role_ID) VALUES (:Email, :Password, :Role_ID)";
            $userStmt = $pdo->prepare($userSql);
            $userStmt->bindValue(':Email', $Email);
            $userStmt->bindValue(':Password', $hashedPassword);
            $userStmt->bindValue(':Role_ID', $Role_ID);

            // Execute the statement for users
            if ($userStmt->execute()) {
                // Commit transaction
                $pdo->commit();
                $_SESSION['success'] = "Student details successfully added! Generated Password: " . $password;
                header('Location: NewEntry(S).php');
                exit;
            } else {
                // Rollback if user insertion fails
                $pdo->rollBack();
                $_SESSION['error'] = "Error occurred while adding user details!";
                header('Location: NewEntry(S).php');
                exit;
            }
        } else {
            // Rollback if student insertion fails
            $pdo->rollBack();
            $_SESSION['error'] = "Error occurred while adding student details!";
            header('Location: NewEntry(S).php');
            exit;
        }
    } catch (PDOException $e) {
        // Rollback on any error
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: NewEntry(S).php');
        exit;
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
    <title>New Student</title>
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
        max-width: 1000vw;
        max-height: 100vh;
        height: 100%;
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
        width: 100%;
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
        width: 25px;
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

    .course-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #333;
        color: white;
        border-radius: 10px;
        overflow: none;
    }

    .course-table th,
    .course-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #444;
    }

    .course-table th {
        background-color: #444;
    }

    .course-table td {
        background-color: #555;
    }

    /* Content column hover effect */
    .course-content {
        white-space: nowrap;
        overflow: none;
        text-overflow: ellipsis;
        max-width: 200px;
        /* Limit the content width */
        cursor: pointer;
        position: relative;
        /* overflow: hidden; */
    }

    .course-content:hover::after {
        content: attr(data-full-content);
        /* Show full content on hover */
        position: absolute;
        left: 0;
        top: 100%;
        white-space: normal;
        background-color: #333;
        padding: 10px;
        border-radius: 5px;
        z-index: 1;
        max-width: 300px;
        color: #fff;
        box-shadow: 0px -5px 10px rgba(0, 0, 0, 0.3);
    }

    /* Responsive adjustments for light mode */
    body.light-mode .course-table {
        background-color: #e0e0e0;
        color: #000;
    }

    body.light-mode .course-table th {
        background-color: #d0d0d0;
    }

    body.light-mode .course-table td {
        background-color: #c0c0c0;
    }

    body.light-mode .course-content:hover::after {
        background-color: #f0f0f0;
        color: #000;
    }

    /* Button Section Styling */
    .button-section {
        margin-top: 20px;
        text-align: right;
        text-decoration: none;
    }

    .button-section .action-button {
        padding: 10px 20px;
        background-color: #1e1e1e;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 10px;
        transition: background-color 0.3s ease;
        text-decoration: none;
    }

    .button-section .action-button:hover {
        background-color: #333;
    }

    .button-section .delete-button {
        background-color: #d9534f;
        color: white;
    }

    .button-section .delete-button:hover {
        background-color: #c9302c;
    }

    body.light-mode .button-section .action-button {
        background-color: #c0c0c0;
        color: black;
    }

    body.light-mode .button-section .action-button:hover {
        background-color: #999;
    }

    body.light-mode .delete-button {
        background-color: #ff6666;
    }

    body.light-mode .delete-button:hover {
        background-color: #ff4d4d;
    }

    /* Form Section */
    .form-container {
        background-color: #333;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: background-color 0.3s ease;
    }

    body.light-mode .form-container {
        background-color: var(--main-bg-light);
    }

    form {
        display: flex;
        flex-direction: column;
    }

    form label {
        flex: 1;
        margin-right: 10px;
        font-size: 18px;
        align-self: center;
        margin-bottom: 10px;
        margin-top: 10px;
        color: var(--text-color-dark);
    }

    body.light-mode form label {
        color: var(--text-color-light);
    }

    form input,
    form select {
        /* width: 40%; */
        /* margin-bottom: 20px; */
        /* border: none; */
        /* border-radius: 25px; */
        background-color: #2a2a2a;
        padding: 10px;
        color: white;
        margin: 0 auto 20px;
        text-align: center;
        flex: 2;
        padding: 10px;
        border: none;
        border-radius: 10px;
    }

    body.light-mode form input,
    body.light-mode form select {
        background-color: #c0c0c0;
        color: var(--text-color-light);
    }

    form input::placeholder {
        color: #c0c0c0;
    }

    body.light-mode form input::placeholder {
        color: #1e1e1e;
    }

    /* Gender dropdown styling */
    form select {
        width: 43%;
        padding: 10px;
        margin-bottom: 20px;
        background-color: #2a2a2a;
        border: none;
        border-radius: 25px;
        color: white;
        margin: 0 auto;
        text-align: center;
    }

    body.light-mode form select {
        background-color: #c0c0c0;
        color: var(--text-color-light);
    }

    /* Dropdown placeholder styling */
    form select option {
        background-color: #1e1e1e;
        color: white;
    }

    body.light-mode form select option {
        background-color: #c0c0c0;
        color: var(--text-color-light);
    }

    .add-more-btn {
        bottom: 20px;
        background-color: var(--gradient-bg-dark);
        color: #ffffff;
        border: none;
        padding: 16px 16px;
        border-radius: 50px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        border: 2px solid white;
        color: black;
        width: 30%;
        /* Button width */
    }

    .add-more-btn ion-icon {
        color: white;
        /* Change icon color to white */
        font-size: 1.5em;
        /* Adjust size if needed */
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
                <li class="list">
                    <a href="../PHP/NewTeacherAccess.php">
                        <span class="icons"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="text">Teacher</span>
                    </a>
                </li>
                <li class="list active">
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
                    <h1>New Student Details</h1>
                    <!-- Icons (Reminders, Game, Profile) aligned to the right -->
                    <div class="icons">
                        <div class="icon gradient-bg">

                            <a href="../PHP/NewNotices.php"><span class="colored-icon"><ion-icon
                                        name="notifications-outline"
                                        style="width: 25px; height: 25px"></ion-icon></span></a>
                        </div>
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
                            <div class="table-container" style="align-items: center;">
                                <!-- Form Section -->
                                <div class="form-container" style="align-items: center;">
                                    <form id="student-form" action="NewEntry(S).php" method="POST" style="align-items: center;">
                                        <div class="name-container">
                                            <div class="form-group">
                                                <label for="fullName">Full Name:</label>
                                                <input type="text" id="First_Name" name="First_Name" placeholder="First Name" required>
                                                <input type="text" id="Middle_Name" name="Middle_Name" placeholder="Middle Name">
                                                <input type="text" id="Last_Name" name="Last_Name" placeholder="Last Name" required>
                                            </div>
                                        </div>

                                        <div class="form-group" style="width: 90%;">
                                            <label for="Gender">Gender:</label>
                                            <select id="Gender" name="Gender" required>
                                                <option value="">Select Gender</option>
                                                <option value="M">Male</option>
                                                <option value="M">Female</option>
                                                <option value="O">Other</option>
                                            </select>
                                        </div>

                                        <br>

                                        <div class="id-container">
                                            <div class="form-group">
                                                <label for="studentID">Student ID:</label>
                                                <input type="text" id="Roll_No" name="Roll_No" placeholder="Roll Number" required>
                                                <input type="text" id="University_No" name="University_No" placeholder="University ID"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="Date_Of_Birth">Date of Birth:</label>
                                            <input type="date" id="Date_Of_Birth" name="Date_Of_Birth" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="Email">Email:</label>
                                            <input type="email" id="Email" name="Email" placeholder="Email" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="PhoneNo">Contact Details:</label>
                                            <input type="text" id="PhoneNo" name="PhoneNo" placeholder="Candidate Contact Details" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="Current_Semester">Semester:</label>
                                            <select id="Current_Semester" name="Current_Semester" style="width: 55%;" required>
                                                <option value="">Select Semester</option>
                                                <option value="I">I</option>
                                                <option value="II">II</option>
                                                <option value="III">III</option>
                                                <option value="IV">IV</option>
                                                <option value="V">V</option>
                                                <option value="VI">VI</option>
                                                <option value="VII">VII</option>
                                                <option value="VIII">VIII</option>
                                            </select>
                                        </div>

                                        <div class="form-row" style="margin-top: 20px; margin-bottom: 20px;">
                                            <label for="Department_ID">Department Id:</label>
                                            <select id="Department_ID" name="Department_ID" required>
                                                <option value="" disabled selected>Choose Department</option>
                                                <option value="1">Computer Engineering</option>
                                                <option value="2">Information Technology</option>
                                                <option value="3">Mechanical Engineering</option>
                                                <option value="4">Civil Engineering</option>
                                                <option value="5">Electronics and Computer Science</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="add-more-btn" style="width: 30%;"><ion-icon name="person-add-outline"></ion-icon></button>
                                    </form>
                                </div>
                            </div>
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
</script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</html>