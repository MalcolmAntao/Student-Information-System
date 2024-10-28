<?php
include 'Connection.php'; // Include database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: Login.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Correct the PDO initialization
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; // Assuming you use utf8mb4 encoding
    $pdo = new PDO($dsn, $user, $pass); // Optional array of PDO options can be passed here if needed
    // Set error mode to exceptions for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: Could not connect to the database. " . $e->getMessage());
}

// Fetch the email of the current user (assuming it's stored in the session or you fetch it based on user_id)
$stmt = $pdo->prepare("SELECT Contact_Info FROM instructors WHERE Instructor_ID = ?");
$stmt->execute([$user_id]);
$userEmail = $stmt->fetchColumn();

// Fetch the Role_ID for the logged-in user
$query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$roleID = $stmt->fetchColumn();

// Fetch the Department_ID of the logged-in instructor
$stmt = $pdo->prepare("SELECT Department_ID FROM instructors WHERE Contact_Info = ?");
$stmt->execute([$userEmail]);
$department_id = $stmt->fetchColumn();

// Fetch all instructors in the same department, ordered by their start date
$stmt = $pdo->prepare("
    SELECT Instructor_ID, CONCAT(First_Name, ' ', Middle_Name, ' ', Last_Name) AS Name, Gender, Contact_Info, Start_Date
    FROM instructors
    WHERE Department_ID = ?
    ORDER BY Start_Date ASC
");
$stmt->execute([$department_id]);
$instructors = $stmt->fetchAll();

// Fetch all courses in the same department, excluding those already assigned to an instructor
$stmt = $pdo->prepare("
    SELECT courses.Course_ID, courses.CourseName
    FROM courses
    LEFT JOIN teaches ON courses.Course_ID = teaches.Course_ID
    WHERE courses.Department_ID = ? AND teaches.Course_ID IS NULL
");
$stmt->execute([$department_id]);
$availableCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch currently assigned courses for each instructor in the same department as the HoD
$stmt = $pdo->prepare("
    SELECT teaches.Instructor_ID, courses.CourseName
    FROM teaches
    INNER JOIN courses ON teaches.Course_ID = courses.Course_ID
    WHERE teaches.Instructor_ID IN (
        SELECT Instructor_ID FROM instructors WHERE Department_ID = ?
    ) AND courses.Department_ID = ?
");
$stmt->execute([$department_id, $department_id]);
$assignedCourses = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Assignment</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Root Variables */
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
            --profile-bg: #f5f5f5;
            --profile-border: #273c75;
            --courses-list-border: #273c75;
            --bio-border: #273c75;
            --form-bg: #ffffff;
            --form-text: #000000;
            --enroll-button-bg: #273c75;
            --enroll-button-text: white;
            --overlay-bg: rgba(0, 0, 0, 0.5);
            --expanded-card-bg: rgba(255, 255, 255, 0.95);
        }

        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #120E0E;
            color: #fff;
        }

        /* Sidebar */
        #sidebar {
            width: 60px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            background: linear-gradient(130deg,
                    hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            transition: width 0.3s;
        }

        #sidebar:hover {
            width: 200px;
        }

        #sidebar-icon {
            font-size: 24px;
            color: white;
            padding: 10px;
            cursor: pointer;
        }

        #sidebar-menu {
            display: none;
            padding-top: 20px;
        }

        #sidebar:hover #sidebar-menu {
            display: block;
        }

        #sidebar-menu ul {
            list-style: none;
        }

        #sidebar-menu ul li {
            padding: 10px;
            color: white;
        }

        #sidebar-menu ul li a {
            text-decoration: none;
            color: white;
            font-size: 16px;
        }

        #sidebar:hover ~ .main-content {
            margin-left: 200px;
        }

        /* Main Content */
        .main-content {
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
            color:#fff;
        }

        /* Content Area */
        .content-area {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }

        body.light-mode {
            background-color: #f5f5f5;
            color: #333;
        }

        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */
        }

        .light-mode #sidebar-menu ul li a {
            color: #333;
        }

        /* Toggle Button Styles */
        .header-controls {
            display: flex;
            align-items: center;
        }

        #theme-toggle {
            padding: 10px;
            margin-left: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #444;
            color: white;
            font-size: 16px;
        }

        .light-mode #theme-toggle {
            background-color: #333;
            color: white;
        }

        /* Style for the Profile Button */
        #profile-button {
            padding: 10px;
            margin-left: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #444;
            color: white;
            font-size: 16px;
        }

        .light-mode #profile-button {
            background-color: #333;
            color: white;
        }

        #profile-button:hover {
            background-color: #555;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #555;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        thead {
            background-color: #444;
        }

        .container {
            display: grid;
            grid-template-columns: 4fr 2fr;
            gap: 20px;
        }
        /* Card Styling */
        .card {
            background-color: var(--card-bg);
            color: var(--text-color); /* Ensures text is visible in both modes */
            box-shadow: 0 4px 8px var(--card-shadow);
            padding: 20px;
            border-radius: 10px;
            /* Remove fixed height to allow dynamic content height */
            /* height: 65vh; */
            max-width: 3200px; /* Increase the maximum width */
            width: 95%; /* Set the width to 90% of the parent container */
            margin: 20px auto; /* Add some vertical margin and center horizontally */
            border: 1px solid var(--card-shadow); /* Add border for better visibility */
        }

        /* Instructor Table Container */
        .instructor-table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: var(--card-bg); /* Same as card background */
            color: var(--text-color); /* Adjust text color */
            /* Ensure table takes full width of the card */
            table-layout: fixed; /* Enables fixed table layout for better control */
        }

        .assigned-instructor-table-container .instructor-table-container {
            overflow-x: auto;
        }

        .assigned-instructor-table-container th, .assigned-instructor-table-container td 
        .instructor-table-container th, .instructor-table-container td {
            padding: 10px;
            border: 1px solid #555;
            text-align: left;
        }

        .assigned-instructor-table-container thead .instructor-table-container thead {
            background-color: var(--sidebar-bg); /* Darker background for headers */
            color: var(--button-text); /* Light text for contrast */
        }

        /* Adjust table row colors for better contrast in both modes */
        .assigned-instructor-table-container tr:nth-child(even) .instructor-table-container tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.05); /* Light gray for alternating rows */
        }

        /* Set the width of the "Contact Info" column */
        .instructor-table-container th:nth-child(3),
        .instructor-table-container td:nth-child(3) {
            width: 300px; /* Adjust the width as needed */
        }

        .instructor-table-container th:nth-child(2),
        .instructor-table-container td:nth-child(2) {
            width: 80px; /* Adjust the width as needed */
        }

        .instructor-table-container th:nth-child(4),
        .instructor-table-container td:nth-child(4) {
            width: 120px; /* Adjust the width as needed */
        }

        .light-mode .assigned-instructor-table-container tr:nth-child(even) .light-mode .instructor-table-container tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.9); /* Light mode alternating rows */
        }

        .assigned-instructor-table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: var(--card-bg); /* Same as card background */
            color: var(--text-color); /* Adjust text color */
            /* Ensure table takes full width of the card */
            table-layout: fixed; /* Enables fixed table layout for better control */
        }

        /* Buttons */
        .actions {
            text-align: center;
        }

        .actions button {
            padding: 10px 20px;
            background-color: #444;
            color: white;
            border: none;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }

        .actions button:hover {
            background-color: #555;
        }

        /* Buttons in light mode */
        .light-mode .actions button {
            background-color: #ddd; /* Light button background */
            color: #646464; /* Dark button text */
            border: 1px solid #bbb;
        }

        .light-mode .actions button:hover {
            background-color: #ccc; /* Darker background on hover */
            color: #000; /* Darker hover text */
        }

        @media (max-width: 768px) {
            .card {
                width: 95%;
                max-width: none;
            }

            .assigned-instructor-table-container table .instructor-table-container table {
                font-size: 14px;
            }

            header h1 {
                font-size: 20px;
            }
        }
        .notice-heading {
            color: #0bb421;
        }

        .light-mode .notice-heading {
            color: #3161a4;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div id="sidebar-icon">&#9776;</div>
            <nav id="sidebar-menu">
                <ul>
                    <li><a href="teacherlanding.php">Home</a></li>
                    <li><a href="viewstudent.php">Students</a></li><!-- Conditionally show link for users with Role_ID 4 -->
                    <?php if ($roleID == 4): ?>
                        <li><a href="hodcourseapproval.php">Course Assignment</a></li>
                    <?php endif; ?>
                    <li><a href="gradeallo.php">Grade Allocation</a></li>
                    <li><a href="enrollapproval.php">Enrollments</a></li>
                    <li><a href="mentorship.php">Mentorship</a></li>
                    <li><a href="teachernotice.php">Announcements</a></li>
                    <li><a href="Logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="main-content">
        <header>
            <h1 class = "notice-heading">Course Assignment</h1>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button> <!-- Toggle Button -->
                <button id="profile-button">Go to Profile</button> <!-- Profile Button -->
            </div>
        </header>
        
        <section class="content-area">
            <div class="card">
                <div class="container">
                    <!-- Left Column: Course Assignment Table -->
                    <div class="instructor-table-container">
                        <form method="POST" action="save_assignments.php">
                            <table id="instructor-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Contact Info</th>
                                        <th>Job Start Date</th>
                                        <th>Course Assigned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($instructor['Name']); ?></td>
                                            <td><?= htmlspecialchars($instructor['Gender']); ?></td>
                                            <td><?= htmlspecialchars($instructor['Contact_Info']); ?></td>
                                            <td><?= htmlspecialchars($instructor['Start_Date']); ?></td>
                                            <td>
                                                <select class="course-select" name="courses[<?= $instructor['Instructor_ID']; ?>]">
                                                    <option value="">Select Course</option>
                                                    <?php
                                                    $assignedCourse = isset($assignedCourses[$instructor['Instructor_ID']]) ? $assignedCourses[$instructor['Instructor_ID']][0]['CourseName'] : null;
                                                    foreach ($availableCourses as $course):
                                                        $isSelected = ($assignedCourse === $course['CourseName']) ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $course['Course_ID']; ?>" <?= $isSelected; ?>>
                                                            <?= htmlspecialchars($course['CourseName']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- Action Buttons -->
                            <div class="actions">
                                <button type="submit" name="action" value="submit">Submit</button>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Assigned Courses Table -->
                    <div class="assigned-instructor-table-container">
                        <h3>Assigned Courses</h3>
                        <table id="assigned-courses-table">
                            <thead>
                                <tr>
                                    <th>Instructor Name</th>
                                    <th>Assigned Course</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($instructors as $instructor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($instructor['Name']); ?></td>
                                        <td>
                                            <?= isset($assignedCourses[$instructor['Instructor_ID']]) ? htmlspecialchars($assignedCourses[$instructor['Instructor_ID']][0]['CourseName']) : 'Not Assigned'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const courseDropdowns = document.querySelectorAll('.course-select');
            const assignedCoursesTable = document.querySelector('#assigned-courses-table tbody');

            courseDropdowns.forEach(dropdown => {
                dropdown.addEventListener('change', function() {
                    updateCourseOptions();
                    updateAssignedCourses(dropdown);
                });
            });

            function updateCourseOptions() {
                const selectedCourses = Array.from(courseDropdowns).map(dropdown => dropdown.value).filter(Boolean);
                courseDropdowns.forEach(dropdown => {
                    const currentSelected = dropdown.value;
                    const options = dropdown.querySelectorAll('option');

                    options.forEach(option => {
                        option.disabled = false;
                        if (selectedCourses.includes(option.value) && option.value !== currentSelected) {
                            option.disabled = true;
                        }
                    });
                });
            }

            // Function to update the "Assigned Courses" table dynamically
            function updateAssignedCourses(dropdown) {
                const instructorRow = dropdown.closest('tr');
                const instructorName = instructorRow.querySelector('td:first-child').textContent;
                const selectedCourse = dropdown.options[dropdown.selectedIndex].textContent;

                // Find the corresponding row in the "Assigned Courses" table and update it
                const assignedRow = Array.from(assignedCoursesTable.rows).find(row => {
                    return row.cells[0].textContent === instructorName;
                });

                if (assignedRow) {
                    assignedRow.cells[1].textContent = selectedCourse !== 'Select Course' ? selectedCourse : 'Not Assigned';
                }
            }
        });
        
        // Light/Dark Mode Toggle
        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.body.classList.toggle('light-mode'); // Toggle the light-mode class
        });

        // Event listener for the "Go to Profile" button
        document.getElementById('profile-button').addEventListener('click', function() {
            window.location.href = 'teacherprofile.php'; // Replace 'profile.html' with the actual profile page URL
        });
    </script>
</body>
</html>