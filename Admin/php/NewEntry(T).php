<?php
// Database configuration
// $host = 'localhost';
// $db = 'employees_db';
// $user = 'root'; // Replace with your database username
// $pass = '';     // Replace with your database password
// $charset = 'utf8mb4';

// $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
// $options = [
//     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//     PDO::ATTR_EMULATE_PREPARES   => false,
// ];

// try {
//     $pdo = new PDO($dsn, $user, $pass, $options);
// } catch (\PDOException $e) {
//     throw new \PDOException($e->getMessage(), (int)$e->getCode());
// }

// Database connection
require 'Connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $First_Name = $_POST['First_Name'];
    $Middle_Name = $_POST['Middle_Name'] ?? null;
    $Last_Name = $_POST['Last_Name'];
    $Gender = $_POST['Gender'];
    $Email = $_POST['Email'];
    $Department_ID = $_POST['Department_ID'];

    // Prepare the SQL statement
    $sql = "INSERT INTO teachers (First_Name, Middle_Name, Last_Name, Gender, Email, Department_ID) VALUES (:First_Name, :Middle_Name, :Last_Name, :Gender, :Email, :Department_ID)";
    $stmt = $pdo->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':First_Name', $First_Name);
    $stmt->bindParam(':Middle_Name', $Middle_Name);
    $stmt->bindParam(':Last_Name', $Last_Name);
    $stmt->bindParam(':Gender', $Gender);
    $stmt->bindParam(':Email', $Email);
    $stmt->bindParam(':Department_ID', $Department_ID);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Employee data saved successfully.";
    } else {
        echo "Error saving employee data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Teacher Form</title>
</head>

<style>
    /* Root Variables */
    :root {
        --bg-color-dark: #121212;
        --text-color-dark: #ffffff;
        --bg-color-light: #ffffff;
        --text-color-light: #000000;
        --sidebar-bg-color-dark: #1e1e1e;
        --sidebar-bg-color-light: #d1d1d1;
    }

    /* General styling for body */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
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
        width: 200px;
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

    .main-content {
        width: 100%;
        padding: 20px;
        background-color: var(--main-bg-dark);
        transition: background-color 0.3s ease;
    }

    body.light-mode .main-content {
        background-color: var(--main-bg-light);
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

    .header-icons button {
        background-color: #333;
        border: none;
        color: white;
        padding: 10px;
        margin-left: 10px;
        border-radius: 50px;
    }

    body.light-mode .header input[type="text"],
    body.light-mode .header-icons button {
        background-color: #e0e0e0;
        color: #000;
    }

    /* Dark Mode Toggle Button Styling */
    .dark-mode-toggle {
        margin-left: auto;
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

    body {
        font-family: Arial, sans-serif;
    }

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

    .save-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #1e1e1e;
        color: white;
        border: none;
        padding: 16px 16px 16px 16px;
        border-radius: 50px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    body.light-mode .save-btn {
        background-color: #c0c0c0;
        color: black;
    }

    .save-btn:active {
        background-color: #333;
    }

    body.light-mode .save-btn:active {
        background-color: #999;
    }

    /* Change the color of the calendar icon to white */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }

    body.light-mode input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0);
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
        color: #fff;
        /* Default dark mode text color */
        text-decoration: none;
        transition: color 0.3s ease;
    }

    body.light-mode .sidebar-links a {
        color: #c0c0c0;
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
</style>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar hamburger icon -->
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="/images/menu(darkmode).svg" alt="Menu Icon" />
                </div>
            </div>

            <!-- Sidebar content (only visible on hover) -->
            <div class="sidebar-content">
                <p>Welcome back, Malcolm</p>
                <div class="sidebar-items">
                    <div class="sidebar-links">
                        <a href="AdminDashboard.html">Dashboard</a>
                        <a href="/Html/CourseAccess.html">Courses Access</a>
                        <a href="/Html/TeacherAccess.html">Teacher Data</a>
                        <a href="/Html/StudentAccess.html">Student Data</a>
                        <a href="/Html/UpdatesPage.html">Updates</a>
                        <a href="/Html/AdminDashboard.html">Logout</a>
                        <!-- <a href="home.html">Home</a> -->
                        <!-- <a href="profile.html">Profile</a> -->
                        <!-- <a href="settings.html">Settings</a> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h2>New Student Data</h2>

                <!-- Dark Mode Toggle Button -->
                <div class="dark-mode-toggle">
                    <button id="darkModeToggle" onclick="toggleDarkMode()">
                        <img id="modeIcon" src="/images/lightmode.svg" alt="Toggle Dark Mode" />
                    </button>
                </div>
            </div>

            <div class="form-container">
                <form id="employee-form" action="/php/NewEntry(S).php" method="POST">
                    <div class="name-container">
                        <div class="form-group">
                            <label for="fullName">Full Name:</label>
                            <input type="text" id="First_Name" name="First_Name" placeholder="First Name" required>
                            <input type="text" id="Middle_Name" name="Middle_Name" placeholder="Middle Name">
                            <input type="text" id="Last_Name" name="Last_Name" placeholder="Last Name" required>
                        </div>
                    </div>

                    <div class="form-group">
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

                    <!-- <div class="form-group">
                        <label for="ParentContactDetails">Parent Contact Details</label>
                        <input type="text" id="parentContactDetails" name="ParentContactDetails"
                            placeholder="Parent Contact Details" required>
                    </div> -->

                    <div class="form-group">
                        <label for="Current_Semester">Semester:</label>
                        <input type="text" id="Current_Semester" name="Current_Semester" placeholder="Current Semester" required>
                    </div>

                    <div class="form-group">
                        <label for="Department_ID">Department Id:</label>
                        <input type="text" id="Department_ID" name="Department_ID" placeholder="Department ID" required>
                    </div>                    

                    <button type="submit" class="save-btn"><img src="/images/addperson(darkmode).svg"
                            id="saveIcon" /></button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            const toggleButton = document.getElementById('darkModeToggle');
            const modeIcon = document.getElementById('modeIcon');
            const optionsIcons = document.querySelectorAll('td button img[id^="optionsIcon"]');
            const saveIcon = document.getElementById('saveIcon');
            const menuIcon = document.getElementById('hamburger-icon');

            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                toggleButton.querySelector('img').src = '/images/darkmode.svg';
                menuIcon.src = '/images/menu(lightmode).svg';
                saveIcon.src = '/images/addperson(lightmode).svg';
                optionsIcons.forEach(icon => icon.src = '/images/options(lightmode).svg');
            } else {
                toggleButton.querySelector('img').src = '/images/lightmode.svg';
                menuIcon.src = '/images/menu(darkmode).svg';
                saveIcon.src = '/images/addperson(darkmode).svg';
                optionsIcons.forEach(icon => icon.src = '/images/options(darkmode).svg');
            }
        }
    </script>
</body>

</html>