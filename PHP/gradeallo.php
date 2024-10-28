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

// Fetch courses to populate dropdown
function getCourses($pdo) {
    $sql = "SELECT Course_ID, course_code, CourseName FROM courses";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch students based on the selected course, including the Current_Semester
function getStudentsByCourse($pdo, $courseID) {
    $sql = "SELECT s.Student_ID, s.Roll_No, s.First_Name, s.Middle_Name, s.Last_Name, s.Current_Semester,
                   g.IT1, g.IT2, g.IT3, g.Sem
            FROM students s
            JOIN enrolls_in e ON s.Student_ID = e.Student_ID
            LEFT JOIN grades g ON s.Student_ID = g.Student_ID AND g.Course_ID = :courseID
            WHERE e.Course_ID = :courseID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['courseID' => $courseID]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Insert or update grades when the form is submitted
if (isset($_POST['submit'])) {
    $courseID = $_POST['course_id'];
    $grades = $_POST['grades']; // This will contain IT1, IT2, IT3, Sem, and Current_Semester for each student

    foreach ($grades as $studentID => $gradeData) {
        $it1 = $gradeData['IT1'];
        $it2 = $gradeData['IT2'];
        $it3 = $gradeData['IT3'];
        $sem = $gradeData['Sem'];
        $currentSemester = $gradeData['Current_Semester']; // Use the Current_Semester from the form data
        $currentYear = date('Y');

        // Validation
        if (($it1 >= 0 && $it1 <= 25) && ($it2 >= 0 && $it2 <= 25) && ($it3 >= 0 && $it3 <= 25) && ($sem >= 0 && $sem <= 100)) {
            // Check if entry already exists in the grades table
            $checkSql = "SELECT * FROM grades WHERE Student_ID = :studentID AND Course_ID = :courseID";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute(['studentID' => $studentID, 'courseID' => $courseID]);

            if ($checkStmt->rowCount() > 0) {
                // Update the existing row
                $updateSql = "UPDATE grades 
                              SET IT1 = :it1, IT2 = :it2, IT3 = :it3, Sem = :sem, Semester = :currentSemester, Year = :currentYear 
                              WHERE Student_ID = :studentID AND Course_ID = :courseID";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    'it1' => $it1,
                    'it2' => $it2,
                    'it3' => $it3,
                    'sem' => $sem,
                    'currentSemester' => $currentSemester,
                    'currentYear' => $currentYear,
                    'studentID' => $studentID,
                    'courseID' => $courseID
                ]);
            } else {
                // Insert new entry
                $insertSql = "INSERT INTO grades (Student_ID, Course_ID, IT1, IT2, IT3, Sem, Semester, Year)
                              VALUES (:studentID, :courseID, :it1, :it2, :it3, :sem, :currentSemester, :currentYear)";
                $insertStmt = $pdo->prepare($insertSql);
                $insertStmt->execute([
                    'studentID' => $studentID,
                    'courseID' => $courseID,
                    'it1' => $it1,
                    'it2' => $it2,
                    'it3' => $it3,
                    'sem' => $sem,
                    'currentSemester' => $currentSemester,
                    'currentYear' => $currentYear
                ]);
            }
        }
    }

    // Alert message to show successful grade submission
    echo "<script>alert('Grades updated successfully!');</script>";
}

// Check if course is selected to fetch students
$selectedCourseID = isset($_POST['course_id']) ? $_POST['course_id'] : null;
$students = [];
if ($selectedCourseID) {
    $students = getStudentsByCourse($pdo, $selectedCourseID);
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Allocation</title>
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

        header h1 {
            font-size: 24px;
        }

        .grade-allocation {
            margin-top: 20px;
        }

        label {
            margin-right: 10px;
        }

        #course-select {
            padding: 10px;
            background-color: #555;
            color: white;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table thead th {
            background-color: #444;
            padding: 10px;
            border: 1px solid #555;
        }

        table tbody td {
            background-color: #555;
            padding: 10px;
            border: 1px solid #666;
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

        /* Toggle Button Styles */
        header {
            display: flex;
            justify-content: space-between; /* This will push content to opposite sides */
            align-items: center;
            padding: 20px; /* Adjust padding if needed */
        }

        .header-controls {
            display: flex;
            justify-content: flex-end;
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

        /* Light Mode Styling */
        body.light-mode {
            background-color: #f5f5f5; /* Light background for body */
            color: #333; /* Dark text */
        }

        /* Sidebar in light mode */
        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */
        }

        /* Sidebar menu links in light mode */
        .light-mode #sidebar-menu ul li a {
            color: #333; /* Dark text for sidebar links */
            text-decoration: none;
            font-size: 16px;
        }

        /* Light mode for the main content sections */
        .light-mode .main-content,
        .light-mode .grade-allocation {
            background-color: #fff; /* Light background for content sections */
            color: #333; /* Dark text */
        }

        /* Light mode table styling */
        .light-mode #student-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
        }

        .light-mode #student-table thead th {
            background-color: #d3e4f1; /* Light blue for headers */
            color: #333;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ccc;
        }

        .light-mode #student-table tbody td {
            background-color: #f9f9f9; /* Light gray for rows */
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }

        /* Table row hover effect in light mode */
        .light-mode #student-table tbody tr:hover {
            background-color: #e6f7ff; /* Very light blue on hover */
        }

        /* Input styling inside the table */
        .light-mode #student-table input[type="number"] {
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
        .light-mode #student-table input[type="number"]:invalid {
            border-color: #ff6666;
            background-color: #ffeeee;
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

        .light-mode .course-select label {
            color: black; /* Change the text color to white */
            font-size: 16px; /* Optional: adjust font size if needed */
        }

        /* Container and label styling */
        label {
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            color: #444;
            font-size: 16px;
            margin-right: 10px;
        }

        .light-mode label {
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            color: #333; /* Darker, softer color */
            font-size: 16px;
            margin-right: 10px;
            background-color: #f9f9f9; /* Light background */
            padding: 4px 8px; /* Add padding for readability */
            border-radius: 4px; /* Optional for rounded label edges */
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
        
        label[for="course-select"] {
            color: white; /* Change the text color to white */
            font-size: 16px; /* Optional: adjust font size if needed */
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
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
                <button id="theme-toggle">Change Theme</button> <!-- Toggle Button -->
                <button id="profile-button">Go to Profile</button> <!-- Profile Button -->
            </div>            
        </header>
        <form method="POST" action="">
        <label for="course-select">Select Course:</label>
        <select id="course-select" name="course_id" onchange="this.form.submit()">
            <option value="default">Select Course</option>
            <?php
            // Fetch courses dynamically
            $courses = getCourses($pdo);
            foreach ($courses as $course) {
                $selected = $selectedCourseID == $course['Course_ID'] ? 'selected' : '';
                echo "<option value=\"{$course['Course_ID']}\" $selected>{$course['course_code']} - {$course['CourseName']}</option>";
            }
            ?>
        </select>
    </form>

    <?php if ($selectedCourseID && !empty($students)): ?>
    <form method="POST" action="">
        <input type="hidden" name="course_id" value="<?= $selectedCourseID ?>">

        <table id="student-table" border="1">
            <thead>
                <tr>
                    <th>Roll Number</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>IT1</th>
                    <th>IT2</th>
                    <th>IT3</th>
                    <th>Sem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= $student['Roll_No'] ?></td>
                        <td><?= $student['First_Name'] ?></td>
                        <td><?= $student['Middle_Name'] ?></td>
                        <td><?= $student['Last_Name'] ?></td>
                        <td><input type="number" name="grades[<?= $student['Student_ID'] ?>][IT1]" min="0" max="25" required value="<?= $student['IT1'] ?? '' ?>"></td>
                        <td><input type="number" name="grades[<?= $student['Student_ID'] ?>][IT2]" min="0" max="25" required value="<?= $student['IT2'] ?? '' ?>"></td>
                        <td><input type="number" name="grades[<?= $student['Student_ID'] ?>][IT3]" min="0" max="25" required value="<?= $student['IT3'] ?? '' ?>"></td>
                        <td><input type="number" name="grades[<?= $student['Student_ID'] ?>][Sem]" min="0" max="100" required value="<?= $student['Sem'] ?? '' ?>"></td>
                        <input type="hidden" name="grades[<?= $student['Student_ID'] ?>][Current_Semester]" value="<?= $student['Current_Semester'] ?>">
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <button type="submit" name="submit">Submit Grades</button>
        </div>

    </form>
    <?php endif; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle theme functionality
            document.getElementById('theme-toggle').addEventListener('click', function() {
                document.body.classList.toggle('light-mode');
            });

            // Redirect to profile page
            document.getElementById('profile-button').addEventListener('click', function() {
                window.location.href = 'teacherprofile.php'; // Replace 'teacherprofile.html' with correct file path if needed
            });
        });
        
        // Sidebar toggle (optional for icon click functionality)
        document.getElementById('sidebar-icon').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.main-content');
            
            if (sidebar.style.width === '60px' || sidebar.style.width === '') {
                sidebar.style.width = '200px';
                mainContent.style.marginLeft = '200px';
            } else {
                sidebar.style.width = '60px';
                mainContent.style.marginLeft = '60px';
            }
        });

    </script>
</body>
</html>