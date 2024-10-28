<?php
include 'Connection.php'; // Include database connection
session_start();
$userId = $_SESSION['user_id']; // Assuming user ID is stored in session

// Fetch the email associated with the user
$query = "SELECT Email FROM users WHERE User_ID = :userId";
$stmt = $pdo->prepare($query);
$stmt->execute([':userId' => $userId]);
$userEmail = $stmt->fetchColumn();

// Fetch the Role_ID for the logged-in user
$query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $userId]);
$roleID = $stmt->fetchColumn();

if ($userEmail) {
    // Fetch the instructor's details using the email
    $query = "SELECT Instructor_ID, First_Name, Middle_Name, Last_Name, Gender, Contact_Info, Department_ID, Profile_Picture FROM instructors WHERE Contact_Info = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':email' => $userEmail]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instructor) {
        $instructorId = $instructor['Instructor_ID'];
        
        // Fetch the department name
        $query = "SELECT Name FROM departments WHERE Department_ID = :deptId";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':deptId' => $instructor['Department_ID']]);
        $department = $stmt->fetchColumn();

        // Fetch courses taught by the instructor
        $query = "
            SELECT c.CourseName, c.Course_Code, e.Enrollment_Type_Name, d.Name AS Department
            FROM courses c
            JOIN teaches t ON c.Course_ID = t.Course_ID
            JOIN enrollment_types e ON c.Enrollment_Type_ID = e.Enrollment_Type_ID
            JOIN departments d ON c.Department_ID = d.Department_ID
            WHERE t.Instructor_ID = :instructorId";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':instructorId' => $instructorId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch notices/announcements
        $sql = "SELECT a.Announcement_ID, a.Title, a.Posting_Date, i.First_Name, i.Last_Name 
        FROM Announcements a
        JOIN Instructors i ON a.Author_ID = i.Instructor_ID
        ORDER BY a.Posting_Date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if a specific notice ID is provided via the URL
        $noticeID = isset($_GET['id']) ? (int) $_GET['id'] : null;

        // Fetch the specific notice if notice ID is passed
        $noticeDetail = null;
        if ($noticeID) {
        $sql = "SELECT a.Title, a.Content, a.Posting_Date, i.First_Name, i.Last_Name
            FROM Announcements a
            JOIN Instructors i ON a.Author_ID = i.Instructor_ID
            WHERE a.Announcement_ID = :notice_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['notice_id' => $noticeID]);
        $noticeDetail = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Landing Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background-color: #120E0E;
            --sidebar-bg: #273c75;
            --sidebar-hover-bg: #3c5a99;
            --text-color: #000;
            --button-bg: #273c75;
            --button-text: white;
            /* Notices Section Variables */
            --notices-bg: #f9f9f9; /* Background for the notices section */
            --notices-border: #ddd; /* Border for the notices section */
            --notices-header-bg: #4CAF50; /* Header background color */
            --notices-header-text: #fff; /* Header text color */
            --notices-row-hover: #f1f1f1; /* Row hover color */
            --notices-alt-row: #f9f9f9; /* Alternating row color */
            --notices-text: #333; /* Text color */
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

        .content-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
            gap: 20px;
        }

        .section {
            background-color: #444;
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            font-size: 24px;
        }

        #date-section {
            width: 100%; /* Set width to 100% so it fills the entire left column */
            height: auto; /* Remove fixed height to let the content adjust */
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 20px; /* Adds space between the date section and course cards */
        }

        #date-section h2 {
            color: #0bb421; /* A distinct color for the heading */
            padding: 5px;
        }

        #date-section p {
            color: #ccc; /* Text color for the date */
            font-size: 18px; /* Adjust the font size if needed */
            padding: 5px;
            border-radius: 5px;
            background-color: #120E0E; /* Light background for the date text */
            border: 1px solid #444; /* A subtle border around the date */
        }

        .notice-section, .previous-notices {
            width: 48%; 
            max-height: 300px; /* Adjust max-height as needed */
            padding: 15px; /* Reduce padding to make it more compact */
        }

        .notice-section {
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            border-radius: 5px;
        }

        .notice-detail,
        .notice-card {
            background-color: #120E0E;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px #1B222E;
            margin-bottom: 10px;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;

        }

        body.light-mode .notice-detail,
        body.light-mode .notice-card {
            background-color: #ffffff;
            box-shadow: 0 4px 8px #ccc;
        }

        .notice-section h2 {
            margin-top: 0;
            color: #0bb421;
        }

        #notices-section {
            flex-grow: 1;
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 20px; 
        }

        /* Light Mode Styling */
        body.light-mode #notices-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Light Mode Styling for Date Section */
        body.light-mode #date-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
            color: #232B3A; /* Dark text color */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        body.light-mode #date-section h2 {
            color: #3161a4; /* A distinct color for the heading */
            padding: 5px;
        }

        body.light-mode #date-section p {
            color: #333; /* Text color for the date */
            font-size: 18px; /* Adjust the font size if needed */
            padding: 10px;
            border-radius: 5px;
            background-color: #ffffff; /* Light background for the date text */
            border: 1px solid #ddd; /* A subtle border around the date */
        }


        /* Profile Section */
        .profile-section {
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
        }

        .light-mode .profile-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
        }

        /* Profile Picture - Adjustments to make it round and positioned correctly */
        .profile-picture {
            width: 120px; /* Adjust width and height for proper scaling */
            height: 120px;
            border-radius: 50%; /* Makes the image round */
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ccc;
        }

        .profile-picture img {
            width: 100%; /* Ensures the image takes full width */
            height: auto; /* Maintains aspect ratio */
            object-fit: cover; /* Ensures the image covers the container without distortion */
        }

        /* Profile Information */
        .profile-info p {
            font-size: 18px;
            margin: 5px 0;
            color: #ccc;
        }
        .light-mode .profile-info p {
            
            color: #000000;
        }

        .profile-info p span {
            color: #f1c40f;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .content-area {
                flex-direction: column;
            }

            .right-column {
                margin-top: 20px;
            }
        }


        .course-cards {
            display: flex;
            flex-direction: column; /* Stack course cards vertically */
        }

        .course-card {
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .light-mode .course-card {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
        }

        .course-card h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #0bb421;
        }

        .light-mode .course-card h3 {
            color : #3161a4
        }

        .light-mode .course-card p {
            color: #000000;
        }

        .course-card p {
            font-size: 16px;
            color: #ccc;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .course-cards {
                flex-direction: column;
            }
        }

        .left-column {
            display: flex;
            flex-direction: column;
            width: 45%; /* Adjust to fit your layout */
        }

        .right-column {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px; /* Space between notices and profile */
        }

        body.light-mode {
            background-color: #f5f5f5; /* Light background */
            color: #333; /* Dark text */
        }

        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */
        }

        .light-mode #sidebar-menu ul li a {
            color: #333;
            text-decoration: none;
            font-size: 16px;
        }

        .light-mode .course-card {
            background-color: #fff; /* Light card background */
            color: #333; /* Dark text */
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
        .notices-wrapper {
            max-height: 400px; /* Adjust the height as needed */
            /* overflow-y: auto; Enable vertical scrolling */
            padding-right: 10px; /* Optional padding to avoid scrollbar overlap */
        }
        .scrollable {
        overflow-y: scroll; /* Enables vertical scrolling */
        scrollbar-width: none; /* Hides scrollbar in Firefox */
        }

        .scrollable::-webkit-scrollbar {
        display: none; /* Hides scrollbar in Chrome, Safari, Edge, and Brave */
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
            <h1 class="notice-heading">Welcome back, <?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?></h1>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button>
                <button id="profile-button">Go to Profile</button>
            </div>
        </header>

        <section class="content-area">
            <div class="left-column">
                <div class="section" id="date-section">
                    <h2>Date</h2>
                    <p id="current-date"></p>
                </div>

                <section class="course-cards">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <h3><?= htmlspecialchars($course['CourseName']) ?></h3>
                            <p>Course Code: <?= htmlspecialchars($course['Course_Code']) ?></p>
                            <p>Course Type: <?= htmlspecialchars($course['Enrollment_Type_Name']) ?></p>
                            <p>Department: <?= htmlspecialchars($course['Department']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>
            </div>

            <div class="right-column">
                <!-- Notices Section -->
                <div class="section" id="notices-section">
                    <h2 class="notice-heading">Notices</h2>
                    <div id="notice-details" class="notice-detail">
                        <?php if ($noticeDetail): ?>
                            <h2><?= htmlspecialchars($noticeDetail['Title']); ?></h2>
                            <p><strong>Posted by:</strong> <?= htmlspecialchars($noticeDetail['First_Name'] . ' ' . $noticeDetail['Last_Name']); ?></p>
                            <p><strong>Posted on:</strong> <?= date('F j, Y', strtotime($noticeDetail['Posting_Date'])); ?></p>
                            <p><?= nl2br(htmlspecialchars($noticeDetail['Content'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <h2 class="notice-heading">All Notices</h2>
                    <div class="scrollable" style = "height:35vh;">
                        <!-- Notice List Cards -->
                        <div class="notices-wrapper">
                            <?php foreach ($notices as $notice): ?>
                                <div class="notice-card" onclick="loadNoticeDetails(<?= $notice['Announcement_ID']; ?>)">
                                    <h3><?= htmlspecialchars($notice['Title']); ?></h3>
                                    <p><strong>Posted on:</strong> <?= date('F j, Y', strtotime($notice['Posting_Date'])); ?></p>
                                    <p><strong>Teacher:</strong> <?= htmlspecialchars($notice['First_Name'] . ' ' . $notice['Last_Name']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
                <div class="profile-section">
                    <div class="profile-picture">
                        <img src="<?= htmlspecialchars($instructor['Profile_Picture'] ?: 'default.png') ?>" alt="Instructor Picture">
                    </div>
                    <div class="profile-info">
                        <p><?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Middle_Name'] . ' ' . $instructor['Last_Name']) ?></p>
                        <p>Email: <?= htmlspecialchars($instructor['Contact_Info']) ?></p>
                        <p>Gender: <?= htmlspecialchars($instructor['Gender']) ?></p>
                        <p>Department: <?= htmlspecialchars($department) ?></p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Display current date
        function displayDate() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0
            var yyyy = today.getFullYear();
            document.getElementById('current-date').textContent = dd + '/' + mm + '/' + yyyy;
        }
        displayDate();

        function showNotice(title, content, date, author) {
            // Combine the notice details in a string to display in the alert
            var noticeDetails = "Title: " + title + "\n" +
                                "Content: " + content + "\n" +
                                "Date: " + date + "\n" +
                                "Posted by: " + author;

            // Display the alert box with the notice details
            alert(noticeDetails);
        }

        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.body.classList.toggle('light-mode'); // Toggle the light-mode class
        });
        // Event listener for the "Go to Profile" button
        document.getElementById('profile-button').addEventListener('click', function() {
            window.location.href = 'teacherprofile.php'; // Replace 'profile.html' with the actual profile page URL
        });
        function loadNoticeDetails(noticeID) {
            window.location.href = 'teacherlanding.php?id=' + noticeID;
        }
    </script>
</body>
</html>
