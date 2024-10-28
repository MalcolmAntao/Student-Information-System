<?php
include 'Connection.php'; // Include database connection
session_start();

// Get the currently logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the email associated with the user
$query = "SELECT Email FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$userEmail = $stmt->fetchColumn();

// Fetch the Role_ID for the logged-in user
$query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$roleID = $stmt->fetchColumn();

// Fetch the instructor's details using the email
$query = "SELECT Instructor_ID, First_Name, Middle_Name, Last_Name, Gender, Contact_Info, Department_ID, Profile_Picture FROM instructors WHERE Contact_Info = :email";
$stmt = $pdo->prepare($query);
$stmt->execute([':email' => $userEmail]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch courses for the dropdown
$sql_courses = "SELECT Course_ID, course_code, CourseName FROM courses";
$stmt_courses = $pdo->query($sql_courses);
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

// Check if course is selected and fetch students for that course
$students = [];
if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    // Fetch students enrolled in the selected course with department name
    $sql_students = "SELECT s.Student_ID, s.First_Name, s.Middle_Name, s.Last_Name, s.Gender, 
                     s.Roll_No, s.University_No, s.Date_Of_Birth, s.Email, s.PhoneNo, 
                     s.Current_Semester, d.Name
                     FROM students s
                     JOIN enrolls_in e ON s.Student_ID = e.Student_ID
                     JOIN departments d ON s.Department_ID = d.Department_ID
                     WHERE e.Course_ID = ?";
    $stmt_students = $pdo->prepare($sql_students);
    $stmt_students->execute([$course_id]);
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information System - Teacher View</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        #sidebar-menu ul {
            display: none;
        }

        #sidebar:hover #sidebar-menu ul {
            display: block;
            padding: 10px;
        }

        #sidebar-menu ul li {
            margin-bottom: 20px;
        }

        #sidebar-menu ul li a {
            color: white;
            text-decoration: none;
        }

        /* Main Content */
        .main-content {
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s;
        }

        #sidebar:hover ~ .main-content {
            margin-left: 200px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
        }

        .quick-links button {
            background-color: #444;
            color: white;
            border: none;
            padding: 10px;
            margin-left: 10px;
            cursor: pointer;
        }

        .quick-links button:hover {
            background-color: #555;
        }

        .content-area {
            margin-top: 20px;
        }

        .course-selection {
            margin-bottom: 20px;
        }

        #course-select {
            padding: 10px;
            font-size: 16px;
        }

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

        /* Toggle Button Styles */
        .header-controls {
            display: flex;
            align-items: center; /* Center items vertically */
        }

        #theme-toggle {
            padding: 10px;
            margin-left: 10px; /* Space between search and toggle */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #444; /* Default button color */
            color: white; /* Default text color */
            font-size: 16px;
        }

        .light-mode #theme-toggle {
            background-color: #333; /* Button color in light mode */
            color: white; /* Text color in light mode */
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

        body.light-mode {
            background-color: #f5f5f5;
            color: #333;
        }

        .light-mode table, .light-mode th, .light-mode td {
            border-color: #ccc;
        }

        .light-mode thead {
            background-color: #e0e0e0;
        }

        .light-mode .course-selection label {
            color: black; /* Change the text color to white */
            font-size: 16px; /* Optional: adjust font size if needed */
        }

        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */

        }

        .light-mode #sidebar-menu ul li a {
            color: #333;
        }

        .light-mode #theme-toggle {
            background-color: #333;
            color: white;
        }

        /* Container and label styling */
        label {
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            color: #444;
            font-size: 16px;
            margin-right: 10px;
        }
        
        /* Dropdown styling */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
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
        
        /* Focus state */
        select:focus {
            border-color: #102770;
            box-shadow: 0 0 10px rgba(16, 39, 112, 0.2);
        }
        
        /* Option styling */
        option {
            font-family: 'Roboto', sans-serif;
            padding: 10px;
            background-color: #fff;
            color: #333;
            font-size: 16px;
        }

        /* Remove the default arrow for Safari */
        select::-ms-expand {
            display: none;
        }
        
        .course-selection label {
            color: white; /* Change the text color to white */
            font-size: 16px; /* Optional: adjust font size if needed */
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

    <!-- Main Content -->
    <div class="main-content">
        <header>
        <h1 class = "notice-heading">Welcome back, <?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?></h1>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button>
                <button id="profile-button">Go to Profile</button>
            </div>
        </header>

        <section class="content-area">
            <!-- Course Selection -->
            <div class="course-selection">
                <label for="course-select" class = "notice-heading">Course:</label>
                <form method="GET" action="">
                    <select id="course-select" name="course_id" onchange="this.form.submit()">
                        <option value="default">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= $course['Course_ID']; ?>"
                                <?= (isset($course_id) && $course_id == $course['Course_ID']) ? 'selected' : ''; ?>>
                                <?= $course['course_code'] . ' - ' . $course['CourseName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- Student Table -->
            <table id="student-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>Roll No</th>
                        <th>University No</th>
                        <th>Date of Birth</th>
                        <th>Email</th>
                        <th>Phone No</th>
                        <th>Current Semester</th>
                        <th>Department ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['Student_ID']); ?></td>
                                <td><?= htmlspecialchars($student['First_Name']); ?></td>
                                <td><?= htmlspecialchars($student['Middle_Name']); ?></td>
                                <td><?= htmlspecialchars($student['Last_Name']); ?></td>
                                <td><?= htmlspecialchars($student['Gender']); ?></td>
                                <td><?= htmlspecialchars($student['Roll_No']); ?></td>
                                <td><?= htmlspecialchars($student['University_No']); ?></td>
                                <td><?= htmlspecialchars($student['Date_Of_Birth']); ?></td>
                                <td><?= htmlspecialchars($student['Email']); ?></td>
                                <td><?= htmlspecialchars($student['PhoneNo']); ?></td>
                                <td><?= htmlspecialchars($student['Current_Semester']); ?></td>
                                <td><?= htmlspecialchars($student['Name']); ?></td> <!-- Updated to show department name -->
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="12">No students found for this course.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        // Populate students based on course selection
        function populateStudents() {
            const courseSelect = document.getElementById('course-select');
            const selectedCourse = courseSelect.value;
            const tableBody = document.querySelector('#student-table tbody');

            // Clear the current table rows
            tableBody.innerHTML = '';

            // If no course is selected, don't populate
            if (selectedCourse === 'default') return;

            // Populate the table with student data
            const students = studentData[selectedCourse];
            students.forEach(student => {
                const row = `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.firstName}</td>
                        <td>${student.middleName}</td>
                        <td>${student.lastName}</td>
                        <td>${student.gender}</td>
                        <td>${student.rollNo}</td>
                        <td>${student.universityNo}</td>
                        <td>${student.dob}</td>
                        <td>${student.email}</td>
                        <td>${student.phoneNo}</td>
                        <td>${student.currentSemester}</td>
                        <td>${student.departmentId}</td>
                    </tr>`;
                tableBody.innerHTML += row;
            });
        }


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
