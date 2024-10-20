<?php
// // Database connection setup using PDO
// $host = 'localhost';
// $dbname = 'studentdb';
// $username = 'admin';
// $password = 'admin';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }

// Database connection
require 'Connection.php';

// Fetch teacher data from the database
$query = "SELECT Instructor_ID, name, Gender, designation FROM teachers";
$stmt = $pdo->prepare($query);
$stmt->execute();
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="AdminDashboard.css">
</head>

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

    .popup .give-access,
    .popup .remove-access {
        background-color: #1e1e1e;
        color: white;
        border: none;
        padding: 10px;
        margin: 10px 0;
        width: 49%;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    body.light-mode .popup .give-access,
    body.light-mode .popup .remove-access {
        background-color: #c0c0c0;
        color: #000
    }

    .popup-options {
        display: flex;
        flex-direction: column;
    }

    .popup-options button {
        margin: 5px 0;
        padding: 10px;
        color: white;
        border: none;
        cursor: pointer;
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

    .popup-options button {
        background-color: #1e1e1e;
        color: white;
        border: none;
        padding: 10px;
        margin: 10px 0;
        width: 100%;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .close-btn {
        background-color: #1e1e1e;
        color: white;
        border: none;
        padding: 10px;
        margin: 10px 0;
        width: 100%;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .popup-options button:hover {
        background-color: #388E3C;
        /* Darker green on hover */
    }

    .close-btn:hover {
        background-color: red;
        /* Darker green on hover */
    }

    body.light-mode .popup-options button,
    body.light-mode .close-btn {
        background-color: #c0c0c0;
        color: #000
    }

    body.light-mode .popup-options button:hover {
        background-color: #2E7D32;
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
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="/Assests/menu(darkmode).svg" alt="Menu Icon" />
                </div>
            </div>
            <div class="sidebar-content">
                <p>Welcome back, Malcolm</p>
                <div class="sidebar-items">
                    <div class="sidebar-links">
                        <a href="AdminDashboard.php">Dashboard</a>
                        <a href="/Html/CourseAccess.html">Courses Access</a>
                        <a href="/Html/TeacherAccess.html">Teacher Data</a>
                        <a href="/Html/StudentAccess.html">Student Data</a>
                        <a href="/Html/UpdatesPage.html">Updates</a>
                        <a href="/Html/AdminDashboard.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <div class="header">
                <h2>Teacher Access Management</h2>
                <div class="dark-mode-toggle">
                    <button id="darkModeToggle" onclick="toggleDarkMode()">
                        <img id="modeIcon" src="/Assests/lightmode.svg" alt="Toggle Dark Mode" />
                    </button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Teacher Name</th>
                        <th>Gender</th>
                        <th>Teacher Designation</th>
                        <th>Post</th>
                        <th>Given Access To</th>
                        <th>More Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                        <tr>
                            <td><?= htmlspecialchars($teacher['Instructor_ID']) ?></td>
                            <td><?= htmlspecialchars($teacher['name']) ?></td>      //we need to display the full name here. how should we do it
                            <td><?= htmlspecialchars($teacher['Gender']) ?></td>
                            <td><?= htmlspecialchars($teacher['designation']) ?></td>   //designation or the post of the teacher is in which table and how will we display it
                            <td class="post-info"></td>
                            <td class="access-info"></td>
                            <td>
                                <button class="options-btn" onclick="showMoreOptions(this)">
                                    <img id="optionsIcon<?= htmlspecialchars($teacher['Instructor_ID']) ?>" src="/Assests/options(darkmode).svg" alt="Options" />
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Popup for Access Options -->
            <div id="accessPopup" class="popup">
                <h3>Select Year for Access</h3>
                <div class="popup-options">
                    <button onclick="updateAccess('First Year')">First Year</button>
                    <button onclick="updateAccess('Second Year')">Second Year</button>
                    <button onclick="updateAccess('Third Year')">Third Year</button>
                    <button onclick="updateAccess('Fourth Year')">Fourth Year</button>
                </div>
                <button class="close-btn" onclick="closeAccessPopup()">Close</button>
            </div>

            <!-- Overlay -->
            <div id="overlay" class="overlay"></div>
        </div>

        <a href="NewEntry(T).html">
            <button class="add-more-btn"><img src="Assests/add_more(darkmode).svg" id="addIcon" /></button>
        </a>
    </div>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            const toggleButton = document.getElementById('darkModeToggle');
            const modeIcon = document.getElementById('modeIcon');
            const optionsIcons = document.querySelectorAll('td button img[id^="optionsIcon"]'); // Select all options button icons
            const addIcon = document.getElementById('addIcon');
            const menuIcon = document.getElementById('hamburger-icon');

            body.classList.toggle('light-mode'); // Toggles the light-mode class on the body

            // Change the mode icon for the toggle button
            if (body.classList.contains('light-mode')) {
                toggleButton.querySelector('img').src = '/images/darkmode.svg'; // Toggle button icon to dark mode
                menuIcon.src = '/images/menu(lightmode).svg'; // Change menu icon for light mode
                addIcon.src = '/images/add_more(lightmode).svg';
                optionsIcons.forEach(icon => icon.src = '/images/options(lightmode).svg');
            } else {
                toggleButton.querySelector('img').src = '/images/lightmode.svg'; // Toggle button icon to dark mode
                menuIcon.src = '/images/menu(darkmode).svg'; // Change menu icon for light mode
                addIcon.src = 'imagess/add_more(darkmode).svg';
                optionsIcons.forEach(icon => icon.src = '/images/options(darkmode).svg');
            }
        }

        let selectedRow;
        let selectedPost = '';

        function showMoreOptions(button) {
            selectedRow = button.closest('tr');
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('moreOptionsPopup').style.display = 'block';
        }

        function giveAccess() {
            document.getElementById('moreOptionsPopup').style.display = 'none';
            document.getElementById('postPopup').style.display = 'block';
        }

        function selectPost(post) {
            selectedPost = post;
            const postInfoCell = selectedRow.querySelector('.post-info');
            postInfoCell.textContent = selectedPost;
            closePostPopup();
            document.getElementById('accessPopup').style.display = 'block';
        }

        function updateAccess(year) {
            const accessInfoCell = selectedRow.querySelector('.access-info');
            accessInfoCell.textContent = year;
            closeAccessPopup();
        }

        function removeAccess() {
            const accessInfoCell = selectedRow.querySelector('.access-info');
            const postInfoCell = selectedRow.querySelector('.post-info');
            accessInfoCell.textContent = '';
            postInfoCell.textContent = '';
            closePopup();
        }

        function closePopup() {
            document.getElementById('moreOptionsPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        function closePostPopup() {
            document.getElementById('postPopup').style.display = 'none';
        }

        function closeAccessPopup() {
            document.getElementById('accessPopup').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>

</body>

</html>