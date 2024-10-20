<?php
// Include the database connection
include('Connection.php');

// Fetch teacher info
$teacherQuery = $pdo->query("SELECT * FROM instructors");
$teachers = $teacherQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch notices
$noticeQuery = $pdo->query("SELECT * FROM announcements");
$notices = $noticeQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch notices----->>need to check this queries
// $updateQuery = $pdo->query("SELECT * FROM updates");
// $updates = $updateQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch student info
$studentQuery = $pdo->query("SELECT * FROM students");
$students = $studentQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        /* Root variables for dark and light modes */
        :root {
            --bg-color-dark: #121212;
            --text-color-dark: #ffffff;
            --bg-color-light: #ffffff;
            --text-color-light: #000000;
            --sidebar-bg-color-dark: #1e1e1e;
            --sidebar-bg-color-light: #d1d1d1;

            --light: #f6f6f9;
            --primary: #1976D2;
            --light-primary: #CFE8FF;
            --grey: #eee;
            --dark-grey: #AAAAAA;
            --dark: #363949;
            --danger: #D32F2F;
            --light-danger: #FECDD3;
            --warning: #FBC02D;
            --light-warning: #FFF2C6;
            --success: #388E3C;
            --light-success: #BBF7D0;
        }

        /* General styling for body */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;            /* background-color: var(--bg-color-dark); */
            background-color: #262626;
            color: var(--text-color-dark);
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        body.light-mode {
            background-color: var(--bg-color-light);
            color: var(--text-color-light);
        }

        /* Main container layout */
        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar styling */
        .sidebar {
            width: 45px;
            height: 100%;
            background-color: var(--sidebar-bg-color-dark);
            position: relative;
            transition: width 0.3s ease, background-color 0.3s ease;
            overflow: hidden;
        }

        body.light-mode .sidebar {
            background-color: var(--sidebar-bg-color-light);
        }

        .sidebar:hover {
            width: 200px;            /* Expands on hover */
        }

        /* Sidebar hamburger icon */
        .sidebar-icon-container {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            position: relative;
        }

        /* Sidebar icon (hamburger menu) */
        .hamburger-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 24px;            /* Adjusted height to fit 3 lines */
        }

        .sidebar-icon {
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 0;
            transition: 0.4s;
        }

        /* Sidebar styling */
        .sidebar {
            width: 60px;
            height: 100vh;
            background-color: var(--sidebar-bg-color-dark);
            position: relative;
            transition: width 0.3s ease, background-color 0.3s ease;
            overflow: hidden;
        }

        body.light-mode .sidebar {
            background-color: var(--sidebar-bg-color-light);
        }

        .sidebar:hover {
            width: 200px;            /* Expands on hover */
        }

        .sidebar-icon-container {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
        }

        .hamburger-icon img {
            display: flex;
            align-items: center;
        }

        .sidebar-icon {
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 0;
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
            transition-delay: 0.3s;
        }

        .sidebar p {
            margin: 15px 0;
        }

        /* Sidebar links styling */
        .sidebar-items p {
            color: #fff;
            transition: color 0.3s;
        }

        body.light-mode .sidebar-items p {
            color: #000;
        }

        .sidebar-items p:hover {
            color: #c0c0c0;
        }

        body.light-mode .sidebar-items p:hover {
            color: #555;
        }

        .sidebar-links a {
            display: block;
            padding: 10px 0;
            color: #fff;            /* Default dark mode text color */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        body.light-mode .sidebar-links a {
            color: #c0c0c0;            /* Darker color for light mode */
        }

        .sidebar-links a:hover {
            color: #2F9DFF;            /* Hover effect for dark mode */
        }

        body.light-mode .sidebar-links a:hover {
            color: #4f8585;            /* Hover effect for light mode */
        }

        /* Main content styling */
        .main-content {
            width: 85%;
            height: 90vh;
            flex: 4;
            background-color: #262626;
            padding: 30px;
        }

        body.light-mode .main-content {
            background-color: #f0f0f0;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header input[type="text"] {
            width: 60%;
            padding: 10px;
            background-color: #333;
            border: none;
            color: white;
            border-radius: 50px;
        }

        .header-icons {
            display: flex;
            gap: 10px;            /* Adds space between icons */
        }

        .header-icons button {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-mode .header-icons button {
            background-color: #e0e0e0;
            color: #000;
        }

        body.light-mode .header input[type="text"],
        body.light-mode .header-icons button {
            background-color: #e0e0e0;
            color: #000;
        }

        /* Dark Mode Toggle Button Styling */
        .dark-mode-toggle {
            margin: 20px 0;
        }

        .dark-mode-toggle button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        body.light-mode .dark-mode-toggle button {
            background-color: #e0e0e0;
            color: #000;
        }

        .dashboard h2 {
            justify-content: center;
            align-items: center;
        }

        .dashboard p {
            justify-content: center;
            align-items: center;
        }

        .dashboard a {
            text-decoration: none;
            color: #fff;
        }


        .dashboard a::after {
            text-decoration: none;
            color: #fff;
        }

        /* Dashboard grid layout */
        .dashboard .container1 {
            grid-template-columns: 65% 35%;
            grid-template-rows: .05fr .05fr;
            display: grid;
            gap: 20px;
        }

        .dashboard .container2 {
            grid-template-columns: 35% 65%;
            grid-template-rows: .05fr .05fr;
            display: grid;
            gap: 20px;
        }

        .full-block-link {
            display: block;
            height: 100%;
            width: 100%;
            text-decoration: none;            /* Remove underline from the link */
            color: inherit;            /* Keep the original text color */
            justify-content: center;
            align-items: center;
            display: flex;
            flex-direction: column;
        }

        body.light-mode .full-block-link {
            background-color: #e0e0e0;
            color: #000;
        }

        /* Block styling */
        .block {
            background-color: #333;            /* padding-left: 30px; */
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        body.light-mode .block {
            background-color: #e0e0e0;
            color: #000;
        }

        .full-block-link .teacher-info {
            grid-column: 1 / 2;
            grid-row: 1 / 2;
        }

        .notices {
            grid-column: 2 / 3;
            grid-row: 1 / 2;
        }

        .updates {
            grid-column: 1 / 2;
            grid-row: 2 / 3;
        }

        .student-info {
            grid-column: 2 / 3;
            grid-row: 2 / 3;
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            font-size: 14px;
            color: #ccc;
        }

        body.light-mode p {
            color: #555;
        }

        /* Updates block styling */
        .container2 .updates .task-list {
            width: 90%;            /* Takes full width of the parent updates block */
            background: #333;            /* Keeps the background consistent with the block */
            padding: 10px 20px;           /* Add padding inside the task-list */
            box-sizing: border-box;            /* Ensures padding doesn't cause overflow */
            display: flex;
            flex-direction: column;
            gap: 16px;            /* Space between each task */
            align-items: center;            /* Make each item stretch to fit the width */
            justify-content: center;            /* Vertically center the content */
            overflow-y: auto;            /* Allow vertical scrolling if content overflows */
            border-radius: 10px;            /* Keeps the border radius consistent with the block */
            overflow: hidden;
        }

        .container2 .updates .task-list li {
            width: 100%;            /* Task items take up full width of the task-list */
            margin-bottom: 8 px;
            color: #fff;
            padding: 5px 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #444;            /* Background color for list items */
        }

        .container2 .updates .task-list li.completed {
            border-left: 10px solid var(--success);
        }

        .container2 .updates .task-list li.not-completed {
            border-left: 10px solid var(--danger);
        }

        .container2 .updates .task-list li .task-title {
            margin-left: 6px;
            text-align: left;            /* Aligns the text to the left */
        }

        /* Updates block styling for light mode */
        body.light-mode .container2 .updates {
            background-color: #e0e0e0;            /* Light mode background */
            color: #000;            /* Light mode text */
        }

        body.light-mode .container2 .updates .task-list {
            background: #e0e0e0;            /* Matches the light mode background */
        }

        body.light-mode .container2 .updates .task-list li {
            background-color: #d0d0d0;            /* Light mode background for list items */
            color: #000;            /* Light mode text color */
        }

        body.light-mode .container2 .updates .task-list li.completed {
            border-left: 10px solid var(--success);
            /* Keep same border color for success */
        }

        body.light-mode .container2 .updates .task-list li.not-completed {
            border-left: 10px solid var(--danger);            /* Keep same border color for danger */
        }

        body.light-mode .container2 .updates .task-list li .task-title {
            color: #000;            /* Light mode text color */
        }

        .updates {
            position: relative;            /* Allow absolute positioning of the button */
        }

        .add-more-btn {
            position: absolute;
            top: 15px;            /* Adjusts the vertical alignment relative to the block */
            right: 10px;            /* Adjusts the horizontal alignment to place it near the right edge */
            background-color: #1e1e1e;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            padding: 10px 10px 10px 10px;
            align-items: center;
            justify-content: center;
            border-radius: 50px;
            cursor: pointer;
            transition: background-color 0.3s ease;
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
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar hamburger icon -->
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="/Assests/menu(darkmode).svg" alt="Menu Icon" />
                </div>
            </div>

            <!-- Sidebar content (only visible on hover) -->
            <div class="sidebar-content">
                <p>Welcome back, Malcolm</p>
                <div class="sidebar-items">
                    <div class="sidebar-links">
                        <a href="AdminDashboard.php">Dashboard</a>
                        <a href="/Html/CourseAccess.html">Courses Access</a>
                        <a href="/Html/TeacherAccess.html">Teacher Data</a>
                        <a href="/Html/StudentAccess.html">Student Data</a>
                        <a href="/Html/UpdatesPage.html">Updates</a>
                        <a href="/Html/AdminDashboard.html">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <input type="text" placeholder="Page Search">
                <div class="header-icons">
                    <button><img src="/Assests/reminder(dark_mode).svg" alt="Reminder Icon" /></button>
                    <button>Game icon</button>
                    <button><img src="/Assests/profile(dark_mode).svg" alt="Profile Icon" /></button>
                </div>
            </div>

            <!-- Dark Mode Toggle Button -->
            <div class="dark-mode-toggle">
                <button id="darkModeToggle" onclick="toggleDarkMode()"><img src="/Assests/lightmode.svg" /></button>
            </div>

            <!-- Dashboard grid layout -->
            <div class="dashboard">
                <div class="container1">
                    <!-- Teacher Info Block -->
                    <a href="TeacherAccess.html">
                        <div class="teacher-info block">
                            <h2>Teacher Info</h2>
                            <p>Click for more info</p>
                            <!-- Dynamic Teacher Info -->
                            <?php foreach ($teachers as $teacher): ?>
                                <p><?= $teacher['name']; ?> - <?= $teacher['subject']; ?></p>
                            <?php endforeach; ?>
                        </div>
                    </a>

                    <!-- Notices Block -->
                    <div class="notices block">
                        <h2>Notices</h2>
                        <p>To whole college</p>
                        <!-- Dynamic Notices -->
                        <?php foreach ($notices as $notice): ?>
                            <p><?= $notice['title']; ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="container2">
                    <!-- Updates Block -->
                    <div class="updates block">
                        <h3>Updates Made BY Admin</h3>
                        <!-- Add More Button -->
                        <a href="UpdatesPage.html">
                            <button class="add-more-btn">
                                <img src="Assests/add_more(darkmode).svg" id="addIcon" />
                            </button>
                        </a>
                        <ul class="task-list">
                            <!-- Dynamic Updates -->
                            <?php foreach ($updates as $update): ?>
                                <li class="<?= $update['status'] == 'completed' ? 'completed' : 'not-completed'; ?>">
                                    <div class="task-title">
                                        <p><?= $update['description']; ?></p>
                                    </div>
                                    <i class='bx bx-dots-vertical-rounded'></i>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Student Info Block -->
                    <div class="student-info block">
                        <a href="StudentAccess.html" class="full-block-link">
                            <h2>Student Info</h2>
                            <p>Click for more info</p>
                            <!-- Dynamic Student Info -->
                            <?php foreach ($students as $student): ?>
                                <p><?= $student['name']; ?> - <?= $student['grade']; ?></p>
                            <?php endforeach; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            const toggleButton = document.getElementById('darkModeToggle');
            const reminderIcon = document.querySelector('.header-icons button img[alt="Reminder Icon"]');
            const profileIcon = document.querySelector('.header-icons button img[alt="Profile Icon"]');
            const menuIcon = document.querySelector('.hamburger-icon img'); // Fixed menu icon selector
            const addIcon = document.getElementById('addIcon');

            body.classList.toggle('light-mode');

            // Check if light mode is active
            if (body.classList.contains('light-mode')) {
                // Change to light mode icons
                toggleButton.querySelector('img').src = '/images/darkmode.svg'; // Toggle button icon to dark mode
                reminderIcon.src = '/images/reminder(light_mode).svg'; // Change reminder icon for light mode
                profileIcon.src = '/images/profile(light_mode).svg'; // Change profile icon for light mode
                addIcon.src = '/images/add_more(lightmode).svg';
                menuIcon.src = '/images/menu(lightmode).svg'; // Change menu icon for light mode
            } else {
                // Change to dark mode icons
                toggleButton.querySelector('img').src = '/images/lightmode.svg'; // Toggle button icon to light mode
                reminderIcon.src = '/images/reminder(dark_mode).svg'; // Change reminder icon for dark mode
                profileIcon.src = '/images/profile(dark_mode).svg'; // Change profile icon for dark mode
                addIcon.src = '/images/add_more(darkmode).svg';
                menuIcon.src = '/images/menu(darkmode).svg'; // Change menu icon for dark mode
            }
        }
    </script>
</body>

</html>