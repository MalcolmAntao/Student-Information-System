<?php
include 'Connection.php'; // Include database connection
session_start();

// Get the currently logged-in user's ID
$user_id = $_SESSION['user_id'];

// Initialize variables
$instructorDetails = [];
$courses = [];
$notices = [];

// Fetch the email from the users table using the logged-in user ID
$query = "SELECT email FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$userEmail = $stmt->fetchColumn();

// Fetch the Role_ID for the logged-in user
$query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$roleID = $stmt->fetchColumn();

// Fetch the instructor's details using the email (stored in the Contact_Info column)
$query = "SELECT * FROM instructors WHERE Contact_Info = :email";
$stmt = $pdo->prepare($query);
$stmt->execute([':email' => $userEmail]);
$instructorDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if ($instructorDetails) {
    $instructorID = $instructorDetails['Instructor_ID'];

    // Store instructor ID in session
    $_SESSION['instructor_id'] = $instructorID; // Add this line
    var_dump($_SESSION['instructor_id']);

    // Fetch the profile picture path
    $profilePicture = $instructorDetails['Profile_Picture'];

    // Set a default image if no profile picture is found
    $defaultImage = '../Assets/Profile.svg';
    if (empty($profilePicture) || !file_exists($profilePicture)) {
        $profilePicture = $defaultImage;
    }

    // Fetch courses taught by the instructor
    $query = "SELECT c.CourseName, c.course_code, d.Name AS Department, et.Enrollment_Type_Name 
              FROM teaches t
              JOIN courses c ON t.Course_ID = c.Course_ID
              JOIN departments d ON c.Department_ID = d.Department_ID
              JOIN enrollment_types et ON c.Enrollment_Type_ID = et.Enrollment_Type_ID
              WHERE t.Instructor_ID = :instructor_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':instructor_id' => $instructorID]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch notices posted by the instructor
    $query = "SELECT Title, Posting_Date, Content FROM announcements WHERE Author_ID = :author_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':author_id' => $instructorID]);
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch instructor details including department name
    $query = "SELECT i.*, d.Name AS Department_Name 
    FROM instructors i 
    LEFT JOIN departments d ON i.Department_ID = d.Department_ID 
    WHERE i.Instructor_ID = :instructor_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':instructor_id' => $instructorID]);
    $instructorDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Profile</title>
    <style>
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background-color: #120E0E;
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

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            font-size: 24px;
        }

        .content-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 20px;
        }

        /* Left column with date, notices, and courses */
        .left-column {
            display: flex;
            flex-direction: column;
            width: 70%; /* Adjust based on preference */
            padding: 10px;
        }

        .info-sections {
            display: flex;
            justify-content: space-between; /* Adjust space between sections */
            align-items: flex-start;
        }

        .section {
            width: 45%;
            background-color: #444;
            padding: 20px;
            border-radius: 5px;
        }

        .course-cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            justify-content: space-around; 
        }

        .course-card {
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;;
            padding: 20px;
            border-radius: 5px;
            width: 45%;
        }
        .light-mode .course-card {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
        }

        .course-card h3 {
            font-size: 20px;
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

        /* Profile section on the right */
        .profile-section {
            width: 30%;
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;
            padding: 20px;
            border-radius: 5px;
            color: white;
            height: 100vh; /* Cover full height */
            box-sizing: border-box; /* Include padding in the height */
        }

        .light-mode .profile-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .info-sections, .course-cards {
                flex-direction: column;
            }
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

        .light-mode .section,
        .light-mode .course-card,
        .light-mode .profile-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);

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

        .profile-top {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
            border-bottom: 1px solid var(--bio-border);
        }

        .profile-top img {
            width: 150px; /* Adjust size as needed */
            height: 150px; /* Keep height equal to width for a perfect circle */
            border-radius: 50%; /* Makes the image a circle */
            object-fit: cover; /* Ensures the image covers the entire area without distortion */
            border: 3px solid var(--bio-border); /* Optional: Add a border around the circle */
        }

        .instructor-details {
            font-size: 12px;
            color: #333;
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-right: 20px;
        }

        body.dark-mode .instructor-details {
            background-color: #333;
            color: #fff;
            box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1);
        }

        /* Add margin between the paragraph elements */
        .profile-section p {
            margin-bottom: 10px; /* Adjust this value as needed */
        }

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

        /* Edit Form Styles */
        .edit-form {
            display: none;
            flex-direction: column;
            gap: 15px;
            width: 30%;
            background-color: #444;
            padding: 20px;
            color: white;
            border-radius: 5px;
            height: 100vh; /* Cover full height */
            box-sizing: border-box; /* Include padding in the height */
        }

        .edit-form.active {
            display: flex;
        }

        .edit-form label {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="tel"],
        .edit-form textarea {
            padding: 8px;
            border: 1px solid var(--bio-border);
            border-radius: 5px;
            background-color: var(--form-bg);
            color: var(--form-text);
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .edit-form input[type="file"] {
            padding: 5px;
        }

        #date-section {
            width: 200px; /* Fixed width for the date section */
            height: 100px; /* Fixed height for the date section */
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);;

            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
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
            background-color: #333; /* Light background for the date text */
            border: 1px solid #444; /* A subtle border around the date */
        }

        #notices-section {
            flex-grow: 1; /* This allows the notices section to take up available space */
            background-color: #444;
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
            margin-left: 20px;
        }


        #notices-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #notices-section th {
            background-color: #0bb421;
            color: white;
            padding: 10px;
            text-align: left;
        }

        #notices-section td {
            padding: 8px;
            border-bottom: 1px solid #555;
            color: #fff;
        }

        #notices-section tr:hover {
            background-color: #444;
        }

        #notices-section tr:nth-child(even) {
            background-color: #555;
        }

        #notices-section table {
            border: 1px solid #555;
        }

        #notices-section table th, #notices-section table td {
            padding: 12px 15px;
        }

        #notices-section table tr:nth-child(even) {
            background-color: #555;
        }

        #notices-section table tr:hover {
            background-color: #444;
        }

        /* Light Mode Styling */
        body.light-mode #notices-section {
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        body.light-mode #notices-section table {
            border: 1px solid #ddd;
        }

        body.light-mode #notices-section th {
            background-color: #1e90ff;
            color: #fff;
        }

        body.light-mode #notices-section td {
            color: #333;
            border-bottom: 1px solid #ddd;
        }

        body.light-mode #notices-section tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        body.light-mode #notices-section tr:hover {
            background-color: #f1f1f1;
        }

        /* Light Mode Styling for Date Section */
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
            padding: 5px;
            border-radius: 5px;
            background-color: #ffffff; /* Light background for the date text */
            border: 1px solid #ddd; /* A subtle border around the date */
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
            <h1 class = "notice-heading">Welcome back, <?php echo htmlspecialchars($instructorDetails['First_Name']); ?></h1>
            <div class="header-controls">
                <button id="theme-toggle">Change Theme</button>
            </div>
        </header>

        <section class="content-area">
            <div class="left-column">
                <div class="info-sections">
                    <div class="section" id="date-section">
                        <h2>Date</h2>
                        <p id="current-date"><?php echo date('d/m/Y'); ?></p>
                    </div>

                    <!-- Notices Section -->
                    <div class="section" id="notices-section">
                        <h2 class = "notice-heading">Your Notices</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notices as $notice): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($notice['Posting_Date']); ?></td>
                                        <td><a href="#" onclick="showNotice('<?php echo htmlspecialchars($notice['Title']); ?>', '<?php echo htmlspecialchars($notice['Content']); ?>', '<?php echo htmlspecialchars($notice['Posting_Date']); ?>')">
                                            <?php echo htmlspecialchars($notice['Title']); ?>
                                        </a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
    
                <!-- Courses Section -->
                <div class="course-cards">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <h3><?php echo htmlspecialchars($course['CourseName']); ?></h3>
                            <p>Course Code: <?php echo htmlspecialchars($course['course_code']); ?></p>
                            <p>Department: <?php echo htmlspecialchars($course['Department']); ?></p>
                            <p>Course Type: <?php echo htmlspecialchars($course['Enrollment_Type_Name']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
    
            <!-- Profile Section -->
            <div class="profile-section">
                <h2 align="center" class = "notice-heading">Instructor's Profile</h2>
                <div class="profile-top">
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Instructor" id="profile-image">
                </div>
                <p>First Name: <span id="first-name-text"><?php echo htmlspecialchars($instructorDetails['First_Name']); ?></span></p>
                <p>Middle Name: <span id="middle-name-text"><?php echo htmlspecialchars($instructorDetails['Middle_Name']); ?></span></p>
                <p>Last Name: <span id="last-name-text"><?php echo htmlspecialchars($instructorDetails['Last_Name']); ?></span></p>
                <p>Contact: <span id="contact-info-text"><?php echo htmlspecialchars($instructorDetails['Contact_Info']); ?></span></p>
                <p>Gender: <span id="gender-text"><?php echo htmlspecialchars($instructorDetails['Gender']); ?></span></p>
                <p><strong>Department:</strong> <span id="department-text"><?= htmlspecialchars($instructorDetails['Department_Name']); ?></span></p> <!-- Display Department Name -->
                <button class="edit-profile-btn" id="edit-profile-btn">Edit Profile</button>
            </div>

            <!-- Edit Profile Form -->
            <form class="edit-form" id="edit-form" style="display:none;" method="POST" enctype="multipart/form-data">
                <label>
                    Profile Photo:
                    <input type="file" name="profile-photo-input" id="profile-photo-input" accept="image/*">
                </label>
                
                <label>
                    First Name:
                    <input type="text" name="first-name" id="first-name-input" value="<?= htmlspecialchars($instructorDetails['First_Name']); ?>" required>
                </label>
                
                <label>
                    Middle Name:
                    <input type="text" name="middle-name" id="middle-name-input" value="<?= htmlspecialchars($instructorDetails['Middle_Name']); ?>">
                </label>

                <label>
                    Last Name:
                    <input type="text" name="last-name" id="last-name-input" value="<?= htmlspecialchars($instructorDetails['Last_Name']); ?>" required>
                </label>

                <label>
                    Contact:
                    <input type="email" name="contact-info" id="contact-info-input" value="<?= htmlspecialchars($instructorDetails['Contact_Info']); ?>" required>
                </label>

                <label>
                    Gender:
                    <select name="gender" id="gender-input" required>
                        <option value="M" <?= htmlspecialchars($instructorDetails['Gender']) === 'M' ? 'selected' : ''; ?>>Male</option>
                        <option value="F" <?= htmlspecialchars($instructorDetails['Gender']) === 'F' ? 'selected' : ''; ?>>Female</option>
                        <option value="X" <?= htmlspecialchars($instructorDetails['Gender']) === 'X' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </label>

                <label>
                    Department:
                    <select name="department" id="department-input" required>
                        <option value="">Select Department</option>
                        <?php
                        // Assuming you have a database connection and have fetched departments
                        $query = "SELECT Department_ID, Name FROM departments";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($departments as $department) {
                            echo '<option value="' . htmlspecialchars($department['Department_ID']) . '">' . htmlspecialchars($department['Name']) . '</option>';
                        }
                        ?>
                    </select>
                </label>

                <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top:10px;">
                    <button type="button" class="cancel-profile-btn" id="cancel-profile-btn">Cancel</button>
                    <button type="submit" class="save-profile-btn" id="save-profile-btn">Save</button>
                </div>
            </form>

        </section>
    </div>
    <script>
            // Show Edit Profile Form
            document.getElementById('edit-profile-btn').addEventListener('click', function() {
                document.getElementById('edit-form').style.display = 'block';
                document.getElementById('edit-profile-btn').style.display = 'none';
            });

            // Cancel Edit
            document.getElementById('cancel-profile-btn').addEventListener('click', function() {
                document.getElementById('edit-form').style.display = 'none';
                document.getElementById('edit-profile-btn').style.display = 'block';
            });

            // Handle form submission
            document.getElementById('save-profile-btn').addEventListener('click', function(e) {
                e.preventDefault();

                var formData = new FormData();
                formData.append('first_name', document.getElementById('first-name-input').value);
                formData.append('middle_name', document.getElementById('middle-name-input').value);
                formData.append('last_name', document.getElementById('last-name-input').value);
                formData.append('contact_info', document.getElementById('contact-info-input').value);
                formData.append('gender', document.getElementById('gender-input').value);
                
                // Get the selected department ID from the dropdown
                var departmentId = document.getElementById('department-input').value;
                formData.append('department', departmentId);

                var profilePhoto = document.getElementById('profile-photo-input').files[0];
                if (profilePhoto) {
                    formData.append('profile_photo', profilePhoto);
                }

                // Send AJAX request to update the profile
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'teacher_update_profile.php', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        document.getElementById('first-name-text').textContent = response.first_name;
                        document.getElementById('middle-name-text').textContent = response.middle_name;
                        document.getElementById('last-name-text').textContent = response.last_name;
                        document.getElementById('contact-info-text').textContent = response.contact_info;
                        document.getElementById('gender-text').textContent = response.gender;
                        document.getElementById('department-text').textContent = response.department; // Update department display
                        if (response.profile_photo) {
                            document.getElementById('profile-image').src = response.profile_photo;
                        }
                        document.getElementById('edit-form').style.display = 'none';
                        document.getElementById('edit-profile-btn').style.display = 'block';
                        alert('Profile updated successfully!');
                    } else {
                        alert('Error updating profile.');
                    }
                };
                xhr.send(formData);
            });
    </script>
    <script>
        function showNotice(title, content, date) {
            alert("Title: " + title + "\nContent: " + content + "\nDate: " + date);
        }

        document.getElementById('sidebar-icon').addEventListener('click', function() {
            var sidebar = document.getElementById('sidebar');
            var mainContent = document.querySelector('.main-content');
            
            if (sidebar.style.width === '60px' || sidebar.style.width === '') {
                sidebar.style.width = '200px';
                mainContent.style.marginLeft = '200px';
            } else {
                sidebar.style.width = '60px';
                mainContent.style.marginLeft = '60px';
            }
        });

        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.body.classList.toggle('light-mode'); // Toggle the light-mode class
        });

        // Function to display today's date in the "Date" card
        function displayDate() {
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
            var yyyy = today.getFullYear();

            var formattedDate = dd + '/' + mm + '/' + yyyy;
            document.getElementById('current-date').textContent = formattedDate;
        }

        // Call the function when the page loads
        window.onload = function() {
            displayDate();
        };
    </script>
</body>
</html>