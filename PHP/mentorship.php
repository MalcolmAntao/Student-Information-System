<?php
    include 'Connection.php'; // Include database connection
    session_start();

    // Establish connection to the MySQL database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the user_id from session (active logged-in user)
    $userId = $_SESSION['user_id'];

    $instructorDetails = [];
    $students = [];
    $sgpa = null;
    $cgpa = null;

    // Fetch the email from the users table using the logged-in user ID
    $query = "SELECT email FROM users WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $userId]);
    $userEmail = $stmt->fetchColumn();

    // Fetch the Role_ID for the logged-in user
    $query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $userId]);
    $roleID = $stmt->fetchColumn();

    if ($userEmail) {
        // Fetch the instructor's details using the email
        $query = "SELECT * FROM instructors WHERE Contact_Info = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':email' => $userEmail]);
        $instructorDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($instructorDetails) {
            $instructorID = $instructorDetails['Instructor_ID'];

            // Store instructor ID in session
            $_SESSION['instructor_id'] = $instructorID;

            // Fetch student roll numbers associated with the instructor
            $stmt = $pdo->prepare("SELECT Student_Roll_No FROM mentorship WHERE Instructor_ID = :instructor_id");
            $stmt->bindParam(':instructor_id', $instructorID);
            $stmt->execute();
            $studentRollNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Fetch student names and roll numbers if there are students
            if (!empty($studentRollNumbers)) {
                $placeholders = implode(',', array_fill(0, count($studentRollNumbers), '?'));
                $stmt = $pdo->prepare("SELECT Student_ID, First_Name, Last_Name, Roll_No FROM students WHERE Roll_No IN ($placeholders)");
                $stmt->execute($studentRollNumbers);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            echo "Instructor details not found.";
        }
    } else {
        echo "User email not found.";
    }

    // Fetch student and course details based on selected student ID
    $studentDetails = [];
    $courseDetails = [];

    if (isset($_GET['student_id'])) {
        $studentId = $_GET['student_id'];

        // Fetch student details
        $stmt = $pdo->prepare("SELECT First_Name, Last_Name, Roll_No, Current_Semester, PhoneNo, Date_of_Birth, Profile_Picture FROM students WHERE Student_ID = :student_id");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $studentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch course details for the selected student
        $stmt = $pdo->prepare("
            SELECT c.CourseName, c.course_code, c.Credits, et.Enrollment_Type_Name, g.IT1, g.IT2, g.IT3, g.Sem
            FROM enrolls_in e
            JOIN courses c ON e.Course_ID = c.Course_ID
            JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
            LEFT JOIN grades g ON g.Student_ID = e.Student_ID AND g.Course_ID = c.Course_ID
            WHERE e.Student_ID = :student_id
        ");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $courseDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch SGPA and CGPA from student_sgpa_cgpa view based on the semester
        $currentSemester = $studentDetails['Current_Semester'];
        $stmt = $pdo->prepare("
            SELECT First_Name, Last_Name, CGPA, 
            CASE :current_sem
                WHEN 'I' THEN SGPA_Sem1
                WHEN 'II' THEN SGPA_Sem2
                WHEN 'III' THEN SGPA_Sem3
                WHEN 'IV' THEN SGPA_Sem4
                WHEN 'V' THEN SGPA_Sem5
                WHEN 'VI' THEN SGPA_Sem6
                WHEN 'VII' THEN SGPA_Sem7
                WHEN 'VIII' THEN SGPA_Sem8
                ELSE NULL
            END AS SGPA
            FROM student_sgpa_cgpa
            WHERE Student_ID = :student_id
        ");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':current_sem', $currentSemester);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $sgpa = $result['SGPA'];
            $cgpa = $result['CGPA'];
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentee Information</title>
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
            transition: background-color 0.3s, color 0.3s;
        }

        .container {
            display: flex;
            height: 100vh; /* Ensure container covers full viewport height */
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 60px; /* Adjust initial margin to match sidebar width */
            transition: margin-left 0.3s ease;
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

        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */
        }

        .light-mode #sidebar-menu ul li a {
            color: #333; /* Dark text color for sidebar links */
            text-decoration: none;
            font-size: 16px;
        }

        .content-section {
            display: flex;
            justify-content: space-between;
            flex-direction: column;
        }

        .course-and-profile {
            display: flex;
            justify-content: space-between; /* To space out both divs */
            flex-wrap: wrap;
        }

        .course-info, .student-profile-container {
            flex-basis: 48%; /* Give each section a fixed width */
            height: 100%;    /* Prevent height changes */
            overflow: auto;  /* Allow scrolling if content overflows */
            flex: 1;
            margin-right: 20px;
            justify-content: space-between;
            width: 48%; /* Adjust width to fit two columns */
            padding: 10px;
        }

        .student-profile {
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            padding: 15px;
            border-radius: 5px;
        }

        .course-card {
            margin-bottom: 20px; /* Adds a gap between course cards */
            padding: 15px;       /* Optional: Adds some padding inside the course card */
            border: 1px solid #ccc; /* Optional: Adds a border around each course card */
            border-radius: 5px;   /* Optional: Adds rounded corners to each course card */
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);/* Optional: Adds a background color */
        }

        .course-info h2, .student-profile h2 {
            margin-bottom: 10px;
        }

        .student-profile img {
            width: 100px; /* Adjust size of the profile picture */
            height: auto;
        }

        @media (max-width: 768px) {
            .course-and-profile {
                flex-direction: column; /* Stack columns on smaller screens */
            }
        }

        .quick-links {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .quick-links button {
            background-color: #444;
            color: #fff;
            border: none;
            padding: 10px;
            margin: 5px;
            cursor: pointer;
        }

        .quick-links button:hover {
            background-color: #555;
        }

        /* Light mode */
        body.light-mode {
            background-color: #fff;
            color: #000;
        }

        body.light-mode .sidebar, body.light-mode .quick-links button {
            background-color: #f4f4f4;
            color: #000;
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
        
        /* Profile Section */
        .student-profile-container h2 {
            padding-left: 20px;
            margin-bottom: 10px;
            font-size: 22px; /* Same size as Course Information heading */
        }
        .cols {
            display: flex; /* Arranges the child elements side by side */
            justify-content: space-between; /* Ensures space between columns */
            align-items: center; /* Vertically aligns both the profile-top and profile-info */
            margin-bottom: 20px;
        }
        .student-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 20px;
            border-radius: 10px;
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            padding: 20px;
            border: 1px solid #ccc; /* Optional: Adds a border around each course card */
            border-radius: 10px;
            color: white;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
            height: 35%;
            box-sizing: border-box; /* Include padding in the height */
        }

        .light-mode .student-profile {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
        }


        /* Profile Picture - Adjustments to make it round and positioned correctly */
        .profile-top {
            width: 150px; /* Set a fixed width for the profile picture */
            height: 150px;
            border-radius: 50%; /* Makes the image circular */
            overflow: hidden; /* Ensures the image fits inside the circular container */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ccc; /* Placeholder background in case there's no image */
            margin-right: 20px; 
        }

        .profile-top img {
            width: 100%; /* Ensures the image takes full width */
            height: auto; /* Maintains aspect ratio */
            object-fit: cover; /* Ensures the image covers the container without distortion */
        }

        /* Profile Information */
        .profile-info {
            flex: 1; /* Allows the profile info to take up the remaining space */
        }
        .profile-info p {
            font-size: 18px;
            margin: 5px 0;
            color: #ccc;
        }

        .profile-info p span {
            color: #f1c40f;
        }

        body.light-mode .course-info {
            background-color: #fff;
            color: #333;
        }

        body.light-mode .course-card {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
            border: 1px solid #ddd;
            color: #333;
        }

        body.light-mode .course-card h3 {
            color: #007bff;
        }

        body.light-mode .course-card p {
            color: #555;
        }

        body.light-mode .student-profile-container h2 {
            color: #333;
        }

        body.light-mode .student-profile {
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
        }

        body.light-mode .student-profile img {
            border: 1px solid #ddd;
        }

        body.light-mode .student-profile .profile-info p {
            color: #555;
        }

        body.light-mode .profile-info span {
            color: #007bff;
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

        /* Main container for course selection and details */
        .student-info-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .student-selection {
            flex: 1;
        }

        /* Labels and Inputs in course-details */
        .student-selection label {
            font-size: 16px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .student-selection select {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
        }

        /* Course and Student Table Containers */
        .student-info-container{
            width: 100%;
        }
        /* Course Details */
        .sem-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .sem-details label {
            width: 100%;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sem-details input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 16px;
        }

        .sem-details input:disabled {
            background-color: #e9e9e9;
            color: #666;
        }

        @media (min-width: 600px) {
            .sem-details label {
                width: 30%;
                align-self: center;
            }

            .sem-details input {
                width: 65%;
            }
        }

         /* Main container for course selection and details */
        .student-info-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 20px;
            margin-bottom: 20px;
            width: 100%;
        }

        .student-selection, .sem-details {
            flex: 1;
        }

        /* Labels and Inputs in course-details */
        .student-selection label, .sem-details label {
            font-size: 16px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .student-selection select, .sem-details input {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
        }

        body.light-mode .sem-details {
            color: #333; /* Darker text */
        }

        /* Label - Light Mode */
        body.light-mode .sem-details label {
            color: #555; /* Medium dark text for labels */
        }

        /* Input - Light Mode */
        body.light-mode .sem-details input {
            background-color: #fff; /* Bright white input background */
            border: 1px solid #ccc; /* Light border */
            color: #333; /* Dark text inside input */
        }

        /* Disabled Input - Light Mode */
        body.light-mode .sem-details input:disabled {
            background-color: #f1f1f1; /* Slightly darker background for disabled inputs */
            color: #999; /* Light gray text for disabled inputs */
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
        
        label[for="student-select"] {
            color: white; /* Change the text color to white */
            font-size: 16px; /* Optional: adjust font size if needed */
        }
        /* Light mode styling for student select label */
        body.light-mode label[for="student-select"] {
            color: #333; /* Dark text for light background */
        }

        .course-card .student-profile {
            background-color: #4c4c4c;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .course-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #0bb421;
        }
        .light-mode .course-card h3 {
            color: #3161a4;
        }

        .course-card p {
            font-size: 16px;
            color: #ccc;
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
        <h1 class = "notice-heading">Welcome back, <?= htmlspecialchars($instructorDetails['First_Name'] . ' ' . $instructorDetails['Last_Name']) ?></h1>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button>
                <button id="profile-button">Go to Profile</button>
            </div>
        </header>

            <!-- Student Selection -->
            <div class="student-info-container">
                <!-- Student Selection -->
                <div class="student-selection">
                    <label for="student-select">Student:</label>
                    <form method="GET" action="">
                        <select id="student-select" name="student_id" onchange="this.form.submit()">
                            <option value="default">Select Student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['Student_ID']; ?>"
                                    <?= (isset($_GET['student_id']) && $_GET['student_id'] == $student['Student_ID']) ? 'selected' : ''; ?>>
                                    <?= $student['First_Name'] . ' ' . $student['Last_Name'] . ' - ' . $student['Roll_No']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <!-- Instructor and Course Information -->
                <div class="sem-details">
                    <label for="sem">Semester:</label>
                    <input type="text" id="sem" value="<?= htmlspecialchars($studentDetails['Current_Semester'] ?? 'N/A'); ?>" disabled>

                    <label for="sgpa">SGPA:</label>
                    <input type="text" id="sgpa" value="<?= htmlspecialchars($sgpa ?? 'N/A'); ?>" disabled>

                    <label for="cgpa">CGPA:</label>
                    <input type="text" id="cgpa" value="<?= htmlspecialchars($cgpa ?? 'N/A'); ?>" disabled>
                </div>
            </div>
            
            <?php if (!empty($studentDetails)): ?>
                <div class="content-section">
                    <div class="course-and-profile">
                        <!-- Course Information -->
                        <div class="course-info">
                            <h2 class = "notice-heading">Course Information</h2>
                            <?php if (!empty($courseDetails)): ?>
                                <?php foreach ($courseDetails as $course): ?>
                                    <div class="course-card">
                                        <h3 class = "notice-heading"><?= $course['CourseName']; ?>(<?= $course['course_code']; ?>)</h3>
                                        <p>Credits: <?= $course['Credits']; ?></p>
                                        <p>IT1 Marks: <?= $course['IT1'] ?? 'N/A'; ?></p>
                                        <p>IT2 Marks: <?= $course['IT2'] ?? 'N/A'; ?></p>
                                        <p>IT3 Marks: <?= $course['IT3'] ?? 'N/A'; ?></p>
                                        <p>Semester Marks: <?= $course['Sem'] ?? 'N/A'; ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No course details available for this student.</p>
                            <?php endif; ?>
                        </div>  
                        
                        <div class="student-profile-container">
                            <h2 class = "notice-heading">Student Profile</h2> <!-- Move this heading outside the profile box -->
                            <div class="student-profile">
                                <div class="cols">
                                    <div class="profile-top">
                                        <img src="<?= htmlspecialchars($studentDetails['Profile_Picture']); ?>" alt="Student">
                                    </div>
                                    <div class="profile-info">
                                        <p>Name: <?= $studentDetails['First_Name'] . ' ' . $studentDetails['Last_Name']; ?></p>
                                        <p>Roll Number: <?= $studentDetails['Roll_No']; ?></p>
                                        <p>Semester: <?= $studentDetails['Current_Semester']; ?></p>
                                        <p>Contact Info: <?= $studentDetails['PhoneNo']; ?></p>
                                        <p>Date of Birth: <?= $studentDetails['Date_of_Birth']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>  
                </div>
            <?php endif; ?>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            document.getElementById('theme-toggle').addEventListener('click', () => {
                document.body.classList.toggle('light-mode');
            });

            document.getElementById('profile-button').addEventListener('click', function() {
                window.location.href = 'teacherprofile.php';
            });
        });
        
    </script> 
</body>
</html>