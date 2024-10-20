<?php
// Database connection details
$host = 'localhost';
$dbname = 'school_db';
$username = 'root';
$password = '';

// Create a PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form input data
    $courseName = $_POST['CourseName'] ?? '';
    $courseCode = $_POST['courseCode'] ?? '';
    $description = $_POST['Description'] ?? '';
    $departmentId = $_POST['departmentId'] ?? '';
    $credits = $_POST['credits'] ?? '';
    $instructorId = $_POST['Instructor_ID'] ?? '';
    $semester = $_POST['Semester'] ?? '';
    $enrollmentTypeId = $_POST['Enrollment_type_id'] ?? '';

    // Check if all required fields are filled
    if (empty($courseName) || empty($courseCode) || empty($description) || empty($departmentId) || empty($credits) || empty($instructorId) || empty($semester) || empty($enrollmentTypeId)) {
        echo "All fields are required!";
    } else {
        // Insert query using prepared statement
        $sql = "INSERT INTO courses (course_name, course_code, description, department_id, credits, instructor_id, semester, enrollment_type_id) 
                VALUES (:course_name, :course_code, :description, :department_id, :credits, :instructor_id, :semester, :enrollment_type_id)";

        // Prepare statement
        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':course_name', $courseName);
        $stmt->bindParam(':course_code', $courseCode);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':department_id', $departmentId);
        $stmt->bindParam(':credits', $credits);
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->bindParam(':semester', $semester);
        $stmt->bindParam(':enrollment_type_id', $enrollmentTypeId);

        // Execute the query
        if ($stmt->execute()) {
            echo "New course added successfully!";
        } else {
            echo "Failed to add the course!";
        }
    }

    try {
        // Prepare SQL to get course_id by course_code
        $stmt = $pdo->prepare("SELECT Course_ID FROM courses WHERE course_code = :course_code");
        $stmt->bindParam(':course_code', $course_code);
        // $course_code = 'CS101'; // Example course_code value
        $stmt->execute();

        // Fetch the course_id
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $Course_ID = $course['Course_ID'];

            // Now assign the instructor_id to the course
            $update_stmt = $pdo->prepare("INSERT INTO teaches (Instructor_ID,Course_ID) VALUES (Instructor_ID, Course_ID);");
            $update_stmt->bindParam(':Instructor_ID', $Instructor_ID);
            $update_stmt->bindParam(':Course_ID', $Course_ID);

            // $instructor_id = 3; // Example instructor_id value
            $update_stmt->execute();

            echo "Instructor assigned successfully!";
        } else {
            echo "Course not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Course Form</title>
    <style>
        /* Root Variables for Dark and Light Mode */
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
            background-color: var(--bg-color-dark);
            color: var(--text-color-dark);
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
            width: 200px;
            /* Expands on hover */
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
            width: 40%;
            padding: 10px;
            background-color: #333;
            border: none;
            color: white;
            border-radius: 50px;
        }

        .header-icons {
            display: flex;
            gap: 10px;
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

        /* Form Section */
        .form-container {
            position: relative;
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding-bottom: 80px;
            transition: background-color 0.3s ease;
        }

        body.light-mode .form-container {
            background-color: #f0f0f0;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        label {
            flex: 1;
            margin-right: 10px;
            font-size: 18px;
            align-self: center;
        }

        input,
        select {
            flex: 2;
            padding: 10px;
            border: none;
            border-radius: 10px;
            background-color: #2a2a2a;
            color: white;
        }

        body.light-mode input,
        body.light-mode select {
            background-color: #e0e0e0;
            color: #000;
        }

        /* Dropdown Styling Consistency */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #2a2a2a;
            color: white;
        }

        body.light-mode select {
            background-color: #e0e0e0;
            color: #000;
        }

        .save-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: #1e1e1e;
            color: white;
            border: none;
            padding: 16px;
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
    </style>
</head>

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
            <!-- Search and Header Section -->
            <div class="header">
                <input type="text" placeholder="Page Search">
                <div class="header-icons">
                    <button><img src="/images/reminder(dark_mode).svg" alt="Reminder Icon" /></button>
                    <button>Game icon</button>
                    <button><img src="/images/profile(dark_mode).svg" alt="Profile Icon" /></button>
                </div>
            </div>

            <!-- Dark Mode Toggle Button -->
            <div class="dark-mode-toggle">
                <button id="darkModeToggle" onclick="toggleDarkMode()"><img src="/images/lightmode.svg" /></button>
            </div>


            <!-- Form Section -->
            <div class="form-container">
                <h2>New Course</h2>
                <form>
                    <div class="form-row">
                        <label for="CourseName">Course Name</label>
                        <input type="text" id="CourseName" name="CourseName" placeholder="Course Name" required>
                    </div>
                    <div class="form-row">
                        <label for="courseCode">Course Code</label>
                        <input type="text" id="courseName" name="courseName" placeholder="Course Code">
                    </div>
                    <div class="form-row">
                        <label for="Description">Description</label>
                        <input type="text" id="Description" name="Description" placeholder="Description" required>
                    </div>
                    <div class="form-row">
                        <label for="department">Department Name</label>
                        <select id="department" name="departmentId">
                            <option value="" disabled selected>Choose Department</option>
                            <option value="1">Computer Engineering</option>
                            <option value="2">Information Technology</option>
                            <option value="3">Mechanical Engineering</option>
                            <option value="4">Civil Engineering</option>
                            <option value="5">Electronics and Aomputer Science</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label for="credits">Number of Credits</label>
                        <input type="number" id="credits" name="credits" placeholder="Enter Credits" min="1" max="10">
                    </div>

                    <div class="form-row">
                        <label for="access">Give Access To:</label>
                        <select id="Instructor_ID" name="Instructor_ID" required>
                            <option value="" disabled selected>Choose Teacher</option>
                            <option value="1">Computer Engineering</option>
                            <option value="2">Information Technology</option>
                            <option value="3">Mechanical Engineering</option>
                            <option value="4">Civil Engineering</option>
                            <option value="5">Electronics and Aomputer Science</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="Semester">Semester:</label>
                        <select id="Semester" name="Semester" required>
                            <option value="" disabled selected>Semester</option>
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

                    <div class="form-row">
                        <label for="Enrollment_type_id">Enrollment Type:</label>
                        <select id="Enrollment_type_id" name="Enrollment_type_id" required>
                            <option value="" disabled selected>Enrollment Type</option>
                            <option value="1">Major</option>
                            <option value="2">Minor</option>
                            <option value="3">Professional Elective</option>
                            <option value="4">Open Elective</option>
                            <option value="5">Core</option>
                        </select>
                    </div>

                    <button type="submit" class="save-btn"><img src="/images/addperson(darkmode).svg" id="saveIcon" /></button>
                </form>
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
            const saveIcon = document.getElementById('saveIcon');

            body.classList.toggle('light-mode');

            // Check if light mode is active
            if (body.classList.contains('light-mode')) {
                // Change to light mode icons
                toggleButton.querySelector('img').src = '/images/darkmode.svg'; // Toggle button icon to dark mode
                reminderIcon.src = '/images/reminder(light_mode).svg'; // Change reminder icon for light mode
                profileIcon.src = '/images/profile(light_mode).svg'; // Change profile icon for light mode
                addIcon.src = '/images/add_more(lightmode).svg';
                saveIcon.src = '/images/addperson(lightmode).svg';
                menuIcon.src = '/images/menu(lightmode).svg'; // Change menu icon for light mode
            } else {
                // Change to dark mode icons
                toggleButton.querySelector('img').src = '/images/lightmode.svg'; // Toggle button icon to light mode
                reminderIcon.src = '/images/reminder(dark_mode).svg'; // Change reminder icon for dark mode
                profileIcon.src = '/images/profile(dark_mode).svg'; // Change profile icon for dark mode
                addIcon.src = '/images/add_more(darkmode).svg';
                menuIcon.src = '/images/menu(darkmode).svg'; // Change menu icon for dark mode
                saveIcon.src = '/images/addperson(darkmode).svg';
            }
        }
    </script>
</body>

</html>