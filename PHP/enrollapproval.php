<?php
session_start();
include 'Connection.php'; // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

try {
    // Establish a connection to the database
    $dsn = "mysql:host=$host;dbname=studentdb;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the logged-in user's email using session
    $user_id = $_SESSION['user_id'];
    
    // Fetch the email (Contact_Info) from the 'users' table
    $stmt = $pdo->prepare("SELECT Email FROM users WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    $userEmail = $stmt->fetchColumn();

    // Fetch the Role_ID for the logged-in user
    $query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $roleID = $stmt->fetchColumn();

    // Fetch the instructor ID from the instructors table
    $stmt = $pdo->prepare("SELECT Instructor_ID FROM instructors WHERE Contact_Info = ?");
    $stmt->execute([$userEmail]);
    $instructor_id = $stmt->fetchColumn();

    // Fetch the list of courses the instructor teaches
    $stmt = $pdo->prepare("
        SELECT courses.Course_ID, courses.CourseName 
        FROM teaches 
        INNER JOIN courses ON teaches.Course_ID = courses.Course_ID
        WHERE teaches.Instructor_ID = ?
    ");
    $stmt->execute([$instructor_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if a course has been selected via POST request
    $selected_course_id = $_POST['course_id'] ?? null;

    if ($selected_course_id) {
        // Fetch course details: Enrollment_Type_Name and Department_Name
        $stmt = $pdo->prepare("
            SELECT et.Enrollment_Type_Name, d.Name AS Department_Name
            FROM courses c
            INNER JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
            INNER JOIN departments d ON c.Department_ID = d.Department_ID
            WHERE c.Course_ID = ?
        ");
        $stmt->execute([$selected_course_id]);
        $course_details = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch the list of students and their details for the selected course
        $stmt = $pdo->prepare("
            SELECT s.Student_ID, s.Roll_No, CONCAT(s.First_Name, ' ', s.Middle_Name, ' ', s.Last_Name) AS Student_Name, s.PhoneNo, d.Name AS Department, s.Current_Semester, cs.Accepted
            FROM course_selections cs
            INNER JOIN students s ON cs.Student_ID = s.Student_ID
            INNER JOIN departments d ON s.Department_ID = d.Department_ID
            WHERE cs.Course_ID = ?
        ");
        $stmt->execute([$selected_course_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Approval</title>
    <style>
        /* General Reset */
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
        }

        /* Content Area */
        .content-area {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        /* Course and Student Table Containers */
        .course-info-container, .student-table-container {
            width: 100%;
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

        /* Buttons */
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

        /* Container and Label Styling */
        label {
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            color: #444;
            font-size: 16px;
            margin-right: 10px;
        }

        /* Dropdown Styling */
        select {
            appearance: none;
            color: #333;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            font-weight: 500;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            width: 220px;
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        select:focus {
            border-color: #102770;
            box-shadow: 0 0 10px rgba(16, 39, 112, 0.2);
        }

        option {
            font-family: 'Roboto', sans-serif;
            padding: 10px;
            background-color: #fff;
            color: #333;
            font-size: 16px;
        }

        select::-ms-expand {
            display: none;
        }

        /* Course Selection */
        .course-selection label {
            color: white;
            font-size: 16px;
        }

        .course-selection {
            margin-bottom: 20px;
        }

        #course-select {
            padding: 10px;
            font-size: 16px;
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

        /* Course Details */
        .course-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .course-details label {
            width: 100%;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .course-details input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 16px;
        }

        .course-details input:disabled {
            background-color: #e9e9e9;
            color: #666;
        }

        @media (min-width: 600px) {
            .course-details label {
                width: 30%;
                align-self: center;
            }

            .course-details input {
                width: 65%;
            }
        }

        /* Flexbox layout for Course Type and Department Name */
        .course-type-dept {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            width: 100%;
        }

        .course-type-container, .department-container {
            flex: 1;
        }

        /* Align labels and inputs inside course type and department properly */
        .course-type-container label, .department-container label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .course-type-container input, .department-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 16px;
        }

        .course-type-container input:disabled, .department-container input:disabled {
            background-color: #e9e9e9;
            color: #666;
        }

        /* Main container for course selection and details */
        .course-info-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .course-selection, .course-details {
            flex: 1;
        }

        /* Labels and Inputs in course-details */
        .course-selection label, .course-details label {
            font-size: 16px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .course-selection select, .course-details input {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
        }

        /* Separate container for the student table */
        .student-table-container {
            margin-top: 20px;
            width: 100%;
        }

        .student-table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .student-table-container th, .student-table-container td {
            padding: 10px;
            border: 1px solid #555;
            text-align: left;
        }

        .student-table-container thead {
            background-color: #444;
        }
        /* Light mode styling for the student table container */
        .light-mode .student-table-container {
            margin-top: 20px;
            width: 100%;
        }

        /* Light mode styling for the table */
        .light-mode .student-table-container table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff; /* White background */
            margin-top: 20px;
        }

        /* Light mode styling for table headers */
        .light-mode .student-table-container th {
            background-color: #d3e4f1; /* Light blue for headers */
            color: #333; /* Darker text color */
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ccc; /* Light gray border */
            text-align: left;
        }

        /* Light mode styling for table cells */
        .light-mode .student-table-container td {
            background-color: #f9f9f9; /* Light gray for rows */
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd; /* Light border between cells */
        }

        /* Hover effect for table rows in light mode */
        .light-mode .student-table-container tbody tr:hover {
            background-color: #e6f7ff; /* Very light blue on hover */
        }

        /* Input styling inside the table for light mode */
        .light-mode .student-table-container input[type="number"] {
            width: 100%;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #333;
            text-align: center;
        }

        /* Error input styling for light mode */
        .light-mode .student-table-container input[type="number"]:invalid {
            border-color: #ff6666; /* Red border for invalid input */
            background-color: #ffeeee; /* Light red background for invalid input */
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
        .notice-heading {
            color: #0bb421;
        }

        .light-mode .notice-heading {
            color: #3161a4;
        }

    </style>
</head>
<body>
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

    <div class="main-content">
        <header>
            <h2 class = "notice-heading">Enrollment Approval</h2>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button> <!-- Toggle Button -->
                <button id="profile-button">Go to Profile</button> <!-- Profile Button -->
            </div>
        </header>

        <section class="content-area">
            <div class="course-info-container">
                <div class="course-selection">
                    <form method="post" action="">
                        <label for="course-select">Course:</label>
                        <select id="course-select" name="course_id" onchange="this.form.submit()">
                            <option value="default">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['Course_ID'] ?>" <?= $selected_course_id == $course['Course_ID'] ? 'selected' : '' ?>>
                                    <?= $course['CourseName'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if (isset($course_details)): ?>
                    <div class="course-details">
                        <label for="course-type">Course Type:</label>
                        <input type="text" id="course-type" value="<?= $course_details['Enrollment_Type_Name'] ?>" disabled>

                        <label for="department-name">Department:</label>
                        <input type="text" id="department-name" value="<?= $course_details['Department_Name'] ?>" disabled>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($students)): ?>
                <div class="student-table-container">
                    <form method="post" action="update_students.php">
                        <table id="student-table">
                            <thead>
                                <tr>
                                    <th>Roll</th>
                                    <th>Student Name</th>
                                    <th>Contact</th>
                                    <th>Department</th>
                                    <th>Semester</th>
                                    <th>Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= $student['Roll_No'] ?></td>
                                        <td><?= $student['Student_Name'] ?></td>
                                        <td><?= $student['PhoneNo'] ?></td>
                                        <td><?= $student['Department'] ?></td>
                                        <td><?= $student['Current_Semester'] ?></td>
                                        <td>
                                            <input type="checkbox" name="approved_students[]" value="<?= $student['Student_ID'] ?>" <?= $student['Accepted'] == 1 ? 'checked' : '' ?>>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <input type="hidden" name="course_id" value="<?= $selected_course_id ?>">
                        <button type="submit" id="submit-button">Submit</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
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