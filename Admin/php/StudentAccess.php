<?php
// Database connection using PDO
// $dsn = 'mysql:host=localhost;dbname=studentdb';
// $username = 'admin';
// $password = '';

// try {
//     $pdo = new PDO($dsn, $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die('Database connection failed: ' . $e->getMessage());
// }

// Start session for dark mode handling (optional)
session_start();
// Database connection
require 'Connection.php';

// Fetch student data from the database
$query = $pdo->query("SELECT * FROM students");
$students = $query->fetchAll(PDO::FETCH_ASSOC);

// Toggle dark mode based on session variable
$darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] ? 'light-mode' : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <style>
        /* Root variables for dark and light modes */
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
            /* background-color: var(--bg-color-dark); */
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
            /* Expands on hover */
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

        th,
        td {
            padding: 15px;
            border: none;
            text-align: left;
        }

        th {
            background-color: #444;
            font-weight: bold;
        }

        body.light-mode th {
            background-color: #c0c0c0;
        }

        .popup {
            display: none;
            position: fixed;
            width: 300px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ccc;
            border-radius: 10px;
            z-index: 1000;
            background-color: #333;
            color: white;
            padding: 20px;
            transition: background-color 0.3s ease;
        }

        .options-btn {
            border-radius: 25px;
            background-color: #1e1e1e;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        body.light-mode .options-btn {
            background-color: #c0c0c0;
            color: black;
        }

        body.light-mode .options-btn:active {
            background-color: #c0c0c0;
        }

        .options-btn:active {
            background-color: #333;
        }

        .close-btn {
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        body.light-mode .popup {
            background-color: #e0e0e0;
            color: #000;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #1e1e1e;
            color: white;
            border: none;
            padding: 10px;
            margin: 10px 0;
            cursor: pointer;
            border-radius: 50px;
            transition: background-color 0.3s ease;
        }

        .close-btn:hover {
            background-color: red;
            /* Darker green on hover */
        }

        body.light-mode .close-btn {
            background-color: #c0c0c0;
            color: #000
        }

        body.light-mode .close-btn:hover {
            background-color: red;
        }

        .add-more-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #1e1e1e;
            color: white;
            border: none;
            padding: 8px 16px;
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

        /* Updated to add a circular profile picture in the popup */
        .popup-content {
            display: flex;
            align-items: center;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            background-size: cover;
            background-position: center;
        }

        .student-info {
            flex: 1;
        }

        .popup {
            display: none;
            position: fixed;
            width: 400px;
            /* Increased width for better spacing */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ccc;
            border-radius: 10px;
            z-index: 1000;
            background-color: #333;
            color: white;
            padding: 20px;
            transition: background-color 0.3s ease;
        }

        .popup-content {
            display: flex;
            align-items: flex-start;
            /* Aligns items to the top */
            gap: 20px;
            /* Adds spacing between the image and the text */
        }

        .profile-picture img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
            /* Ensures the image won't shrink */
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
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
</head>

<body class="<?= $darkMode ?>">
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="/Assests/menu(darkmode).svg" alt="Menu Icon" />
                </div>
            </div>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <div class="header">
                <h1>Student Information Table</h1>
                <div class="dark-mode-toggle">
                    <button id="darkModeToggle" onclick="toggleDarkMode()">
                        <img id="modeIcon" src="/Assests/lightmode.svg" alt="Toggle Dark Mode" />
                    </button>
                </div>
            </div>

            <!-- Student Table -->
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Contact Details</th>
                        <th>Mentors</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?= htmlspecialchars($student['student_id']) ?></td> //should we display the student id or roll no here
                            <td><?= htmlspecialchars($student['name']) ?></td> //how do we display the full name
                            <td><?= htmlspecialchars($student['Gender']) ?></td>
                            <td><?= htmlspecialchars($student['Department_ID']) ?></td> //how do we display the department name from department id
                            <td><?= htmlspecialchars($student['PhoneNo']) ?></td>
                            <td><?= htmlspecialchars($student['mentor']) ?></td> //gonna come fr nathania side
                            <td><?= htmlspecialchars($student['semester']) ?></td> //Current_Semester
                            <td>
                                <button class="options-btn" onclick="openPopup(
                                    '<?= htmlspecialchars($student['name']) ?>',        //need to discuss on it
                                    '<?= htmlspecialchars($student['student_id']) ?>',      //need to discuss on t
                                    '<?= htmlspecialchars($student['Roll_No']) ?>', 
                                    '<?= htmlspecialchars($student['University_No']) ?>',
                                    '<?= htmlspecialchars($student['Department_ID']) ?>', 
                                    '<?= htmlspecialchars($student['contact']) ?>', 
                                    '<?= htmlspecialchars($student['mentor']) ?>',      //coming frmo nathania side
                                    <?= htmlspecialchars($student['Current_Semester']) ?>,
                                    '<?= htmlspecialchars($student['Email']) ?>',
                                    '<?= htmlspecialchars($student['Date_Of_Birth']) ?>',
                                    '<?= htmlspecialchars($student['Gender']) ?>')">
                                    <img src="/Assests/readmore(darkmode).svg" id="viewmore" />
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Popup Modal -->
            <div class="popup" id="student-popup">
                <button class="close-btn" id="closeIcon" onclick="closePopup()">
                    <img src="/Assests/cancel(darkmode).svg" />
                </button>
                <div class="popup-content">
                    <div id="profile-picture" class="profile-picture">
                        <img src="/Assests/johndoeprofile.png" />
                    </div>
                    <div class="student-info">
                        <p><strong>Student Name:</strong> <span id="popup-name"></span></p>
                        <p><strong>Student ID:</strong> <span id="popup-id"></span></p>
                        <p><strong>Gender:</strong> <span id="popup-gender"></span></p>
                        <p><strong>Department:</strong> <span id="popup-department"></span></p>
                        <p><strong>Contact Details:</strong> <span id="popup-contact"></span></p>
                        <p><strong>Mentors:</strong> <span id="popup-mentors"></span></p>
                        <p><strong>Semester:</strong> <span id="popup-semester"></span></p>
                        <p><strong>Address:</strong> <span id="popup-address"></span></p>
                        <p><strong>Parent's Contact:</strong> <span id="popup-parent"></span></p>
                        <p><strong>Date of Birth:</strong> <span id="popup-dob"></span></p>
                    </div>
                </div>
            </div>

            <div id="overlay" class="overlay"></div>
        </div>
        <a href="NewEntry(S).html">
            <button class="add-more-btn"><img src="/Assests/add_more(darkmode).svg" id="addIcon" /></button>
        </a>
    </div>
    <script>
        function toggleDarkMode() {
            const menuIcon = document.getElementById('hamburger-icon');
            const body = document.body;
            const toggleButton = document.getElementById('darkModeToggle');
            const modeIcon = document.getElementById('modeIcon');
            const addIcon = document.getElementById('addIcon');
            const closeIcon = document.getElementById('closeIcon'); // Get close icon reference
            const viewmoreIcons = document.querySelectorAll('.options-btn img');

            body.classList.toggle('light-mode'); // Toggles the light-mode class on the body

            if (body.classList.contains('light-mode')) {
                toggleButton.querySelector('img').src = '/images/darkmode.svg'; // Toggle button icon to dark mode
                menuIcon.src = '/images/menu(lightmode).svg'; // Change menu icon for light mode
                addIcon.src = '/images/add_more(lightmode).svg';
                closeIcon.querySelector('img').src = '/images/cancel(lightmode).svg'; // Change close icon for light mode
                viewmoreIcons.forEach(icon => {
                    icon.src = '/images/readmore(lightmode).svg';
                });
            } else {
                toggleButton.querySelector('img').src = '/images/lightmode.svg'; // Toggle button icon to light mode
                menuIcon.src = '/images/menu(darkmode).svg'; // Change menu icon for dark mode
                addIcon.src = '/images/add_more(darkmode).svg';
                closeIcon.querySelector('img').src = '/images/cancel(darkmode).svg'; // Change close icon for dark mode
                viewmoreIcons.forEach(icon => {
                    icon.src = '/images/readmore(darkmode).svg';
                });
            }
        }

        let selectedRow;
        let selectedPost = '';

        function openPopup(name, id, department, contact, mentors, semester, address, parentContact, dob, gender) {
            document.getElementById('popup-name').innerText = name;
            document.getElementById('popup-id').innerText = id;
            document.getElementById('popup-gender').innerText = gender; // Set Gender
            document.getElementById('popup-department').innerText = department;
            document.getElementById('popup-contact').innerText = contact;
            document.getElementById('popup-mentors').innerText = mentors;
            document.getElementById('popup-semester').innerText = semester;
            document.getElementById('popup-address').innerText = address;
            document.getElementById('popup-parent').innerText = parentContact;
            document.getElementById('popup-dob').innerText = dob;

            // Set the student's profile picture dynamically
            const profilePicture = document.getElementById('profile-picture');
            const imagePath = `/images/${id}-profile.jpg`;
            profilePicture.style.backgroundImage = `url(${imagePath})`;

            document.getElementById('student-popup').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('student-popup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>
</body>

</html>