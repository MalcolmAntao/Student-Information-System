<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

$student_id = $_SESSION['student_id']; // Get logged-in student's ID

// Fetch student details
$sql = "SELECT First_Name, Middle_Name, Last_Name, Roll_No, University_No, Date_Of_Birth, Email, PhoneNo, Current_Semester, Bio, Major, Profile_Picture
        FROM Students
        WHERE Student_ID = :student_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Full name concatenation
$student_name = $profile['First_Name'] . " " . $profile['Last_Name'];

// Fetch courses the student is enrolled in, including grades (if available)
$sql = "SELECT c.CourseName, c.Credits, c.Description, g.Semester, g.Year, a.IT1, a.IT2, a.IT3, a.Internal_Assessment, a.Sem
        FROM Enrolls_In e
        JOIN Courses c ON e.Course_ID = c.Course_ID
        LEFT JOIN Grades g ON e.Course_ID = g.Course_ID AND e.Student_ID = g.Student_ID
        LEFT JOIN Assessment a ON g.Assessment_ID = a.Assessment_ID
        WHERE e.Student_ID = :student_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch recent notices (announcements)
$sql = "SELECT Announcement_ID, Title
        FROM Announcements
        ORDER BY Posting_Date DESC
        LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate grade based on total marks
function calculateGrade($totalMarks) {
    if ($totalMarks >= 90) {
        return ['O', 10];
    } elseif ($totalMarks >= 80) {
        return ['A', 9];
    } elseif ($totalMarks >= 70) {
        return ['B', 8];
    } elseif ($totalMarks >= 60) {
        return ['C', 7];
    } elseif ($totalMarks >= 50) {
        return ['D', 6];
    } else {
        return ['F', 0];
    }
}

// Calculate CGPA and SGPA
$totalCredits = 0;
$totalGradePoints = 0;
$sgpa = 0;
$currentSemesterCredits = 0;
$currentSemesterGradePoints = 0;

foreach ($courses as &$course) { // Use reference to modify each course
    // Calculate average IT marks
    $averageIT = ($course['IT1'] + $course['IT2'] + $course['IT3']) / 3;
    // Total marks = average IT + Sem marks
    $totalMarks = $averageIT + $course['Sem'];
    // Calculate grade and grade point
    list($grade, $gradePoint) = calculateGrade($totalMarks);
    // Add to course array
    $course['Average_IT'] = round($averageIT, 2);
    $course['Total_Marks'] = round($totalMarks, 2);
    $course['Grade'] = $grade;
    $course['Grade_Point'] = $gradePoint;
    
    // Update CGPA calculations
    $totalCredits += $course['Credits'];
    $totalGradePoints += $course['Credits'] * $gradePoint;
    
    // Update SGPA calculations if current semester
    if ($course['Semester'] === $profile['Current_Semester']) {
        $currentSemesterCredits += $course['Credits'];
        $currentSemesterGradePoints += $course['Credits'] * $gradePoint;
    }
}
unset($course); // Break the reference

$cgpa = $totalCredits ? round($totalGradePoints / $totalCredits, 2) : 0;
$sgpa = $currentSemesterCredits ? round($currentSemesterGradePoints / $currentSemesterCredits, 2) : 0;

// Prepare profile picture
if (!empty($profile['Profile_Picture'])) {
    $profilePicture = 'data:image/jpeg;base64,' . base64_encode($profile['Profile_Picture']);
} else {
    // Default image if no profile picture is set
    $profilePicture = '../Assets/Profile.svg'; // Ensure this path is correct
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Homepage</title>
    <style>
        :root {
            --bg-color-dark:#1D2433;
            --text-color-dark: #ffffff;
            --bg-color-light: #d9e8e8;
            --text-color-light: #000000;
            --sidebar-bg-color-dark: #232B3A;
            --sidebar-bg-color-light: #253b42;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color-dark);
            color: var(--text-color-dark);
            overflow-x: hidden;
            transition: background-color 0.3s, color 0.3s;
        }

        body.light-mode {
            background-color: var(--bg-color-light);
            color: var(--text-color-light);
        }

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
            width: 200px; /* Expands on hover */
        }

        /* Sidebar hamburger icon */
        .sidebar-icon-container {
            display: flex;
            justify-content: flex-end;
            padding: 3px;
            position: relative;
        }

        /* Sidebar icon (hamburger menu) */
        .hamburger-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 24px; /* Adjusted height to fit 3 lines */
        }

        .sidebar-icon {
            width: 25px;
            height: 3px;
            background-color: #fff;
            margin: 0;
            transition: 0.4s;
        }

        /* Sidebar content */
        .sidebar-content {
            padding: 20px;
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
            transition-delay: 0s;
        }
        body.light-mode .sidebar-content h2{
            color: #ffffff;
        }
        .sidebar:hover .sidebar-content {
            opacity: 1;
            transform: translateX(0);
            transition-delay: 0.3s;
        }

        .sidebar:hover .sidebar-content h2 {
            transition-delay: 0.3s;
        }

        .sidebar:hover .sidebar-content p {
            transition-delay: 0.4s;
        }

        /* Sidebar links */
        .sidebar-links a {
            display: block;
            padding: 10px 0;
            color: #FFFFFF; /* Default dark mode text color */
            text-decoration: none;
            transition: color 0.3s ease;
        }

        body.light-mode .sidebar-links a {
            color: #cacaca; /* Darker color for light mode */
        }

        .sidebar-links a:hover {
            color: #2F9DFF; /* Hover effect for dark mode */
        }

        body.light-mode .sidebar-links a:hover {
            color: #4f8585; /* Hover effect for light mode */
        }

        /* Main content area divided into columns */
        .main-content {
            display: flex;
            flex: 4;
            padding: 20px;
            gap: 20px;
        }

        /* Column 2 layout */
        .column-2 {
            display: flex;
            flex-direction: column;
            flex: 2;
            gap: 20px;
        }

        /* Column 3 layout */
        .column-3 {
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 20px;
        }

        /* Attendance and Date blocks smaller */
        .small-block {
            background-color: #232B3A;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            flex: 1;
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative; /* For positioning course-marks */
        }

        .small-block p {
            margin: 0px;
            padding: 5px 0px;
        }
        .notice-block{
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding-left: 20px;
        }

        .notice-block a{
            color: #c1e1e2;
            text-decoration: none;
        }

        body.light-mode .notice-block a{
            background-color: #c3d8da;
            color: #000000;
        }

        body.light-mode .small-block {
            background-color: #c3d8da;
        }

        /* Flex container for date and attendance to be side by side */
        .date-attendance {
            display: flex;
            gap: 20px;
            flex-shrink: 0;
        }

        /* Search bar styling */
        .search-bar {
            width: 98%;
            margin-bottom: 10px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #232B3A;
            color: #ffffff;
        }

        body.light-mode .search-bar input {
            background-color: #c3d8da;
            color: #000000;
        }

        /* Courses section with scrolling */
        .courses {
            background-color: #232B3A;
            border-radius: 10px;
            padding: 20px;
           /* flex-grow: 1;*/
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            overflow-y: auto;
            overflow: hidden;
            justify-content: space-around;
        }

        body.light-mode .courses {
            background-color: #c3d8da;
        }

        /* Course card style */
        .course-card {
            background-color: #1B222E;
            border-radius: 10px;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            max-width: 250px; /* Set a max width */
            word-wrap: break-word;
            position: relative; /* For absolute positioning of marks */
            overflow: hidden; /* Hide overflow when transitioning */
            cursor: pointer; /* Indicate interactivity */
            transition: transform 0.3s ease;
        }

        .course-card:hover {
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        body.light-mode .course-card {
            background-color: #efeeee;
            color: #000;
        }

        /* Course basic info */
        .course-basic {
            transition: opacity 0.3s ease;
        }

        /* Course marks info */
        .course-marks {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(35, 43, 58, 0.95); /* Semi-transparent overlay */
            color: #c1e1e2;
            padding: 10px;
            box-sizing: border-box;
            opacity: 0;
            visibility: hidden; /* Initially hidden */
            transition: opacity 0.3s ease, visibility 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .course-card:hover .course-basic {
            opacity: 0;
        }

        .course-card:hover .course-marks {
            opacity: 1;
            visibility: visible; /* Make visible on hover */
            background-color: rgba(35, 43, 58, 0.95); /* Background remains consistent */
        }

        .course-marks p {
            line-height: 1.2; /* Adjust this value to control the spacing */
            margin: 0; /* Remove default margins to further reduce space */
        }
        /* Icons styling */
        .icons {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
        }

        .icon {
            padding: 10px;
            background-color: #364562;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .icon:hover{
            background-color: #4a6fa1;
        }
        body.light-mode .icon {
            background-color: #95c0c4;
        }

        body.light-mode .icon:hover{
            background-color: #c1e1e2;
        }

        /* Flexbox adjustments for third column */
        .column-3 > .small-block {
            flex-shrink: 0;
        }

        /* Toggle button styling */
        .toggle-button {
            background-color: #364562;
            border: none;
            border-radius: 10px;
            color: #ffffff;
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .toggle-button:hover{
            background-color: #4a6fa1;
        }

        body.light-mode .toggle-button {
            background-color: #95c0c4;
            color: #000000;
        }

        body.light-mode .toggle-button:hover{
            background-color: #c1e1e2;
        }

        /* Profile Card Styling */
        .profile-card {
            display: flex;
            align-items: center; /* Align items to the start vertically */
            background-color: #232B3A;
            border-radius: 10px;
            padding: 10px;
            gap: 15px;
        }

        .profile-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        body.light-mode .profile-card {
            background-color: #c3d8da;
        }

        body.light-mode .profile-card img {
            border: 2px solid #000;
        }

        .profile-details {
            display: grid;
            grid-template-columns: auto;
            row-gap: 0.1px;
            /* Removed column gap since labels and values are on the same line */
        }

        .profile-details p {
            margin: 0;
            font-size: 13px;
            display: flex; /* Use flex to align label and value */
            align-items: center;
        }

        .profile-details p strong {
            width: 120px;
            text-align: left; /* Ensure labels are left-aligned */
            padding-right: 10px;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .profile-card img {
                width: 80px;
                height: 80px;
            }

            .profile-details p strong {
                width: 100px;
            }

            /* Adjust course-card size on smaller screens */
            .course-card {
                max-width: 100%;
            }
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
                    <img src="../Assets/Hamburger.svg" alt="Menu" width="40" height="40">
                </div>
            </div>

            <!-- Sidebar content (only visible on hover) -->
            <div class="sidebar-content">
                <h2>Welcome back, <?php echo htmlspecialchars($student_name); ?></h2>
                <div class="sidebar-links">
                    <a href="Announcements.php">Announcements</a>
                    <a href="profile.html">Profile</a>
                    <a href="settings.html">Settings</a>
                    <a href="../HTML/Landing.html">Logout</a>
                </div>
            </div>
        </div>

        <!-- Main content area with two columns -->
        <div class="main-content">
            <!-- Column 2 -->
            <div class="column-2">
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="search.php" method="GET">
                        <input type="text" name="query" placeholder="Search Pages">
                    </form>
                </div>

                <!-- Date and Attendance (side by side) -->
                <div class="date-attendance">
                    <div class="small-block" id="date-block">
                        <p id="date-time"></p>
                    </div>
                    <div class="small-block">
                        <p>Attendance</p>
                    </div>
                </div>

                <!-- Courses section with scrollable content -->
                <div class="courses">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <!-- Basic Info -->
                            <div class="course-basic">
                                <strong><?php echo htmlspecialchars($course['CourseName']); ?></strong> <br>
                                <em>Description:</em> <?php echo htmlspecialchars($course['Description']); ?> <br>
                                <em>Credits:</em> <?php echo htmlspecialchars($course['Credits']); ?>
                            </div>
                            <!-- Marks Info -->
                            <div class="course-marks">
                                <strong style="color: white;">Marks Details</strong>
                                <p>Average IT Marks: <?= htmlspecialchars($course['Average_IT']); ?></p>
                                <p>Semester Marks: <?= htmlspecialchars($course['Sem']); ?></p>
                                <?php if ($course['Internal_Assessment'] !== null && $course['Internal_Assessment'] > 0): ?>
                                    <p>Project Marks: <?= htmlspecialchars($course['Internal_Assessment']); ?></p>
                                <?php endif; ?>
                                <p>Total Marks: <?= htmlspecialchars($course['Total_Marks']); ?></p>
                                <p>Grade: <?= htmlspecialchars($course['Grade']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 3 -->
            <div class="column-3">
                <!-- Icons (Reminders, Game, Profile) aligned to the right -->
                <div class="icons">
                    <div class="icon">
                        <img src="../Assets/Notification.svg" alt="Notification" width="25" height="25">
                    </div>
                    <div class="icon">
                        <img src="../Assets/Game.svg" alt="Game" width="25" height="25">
                    </div>
                    <div class="icon">
                        <img src="" alt="Profile" width="25" height="25">
                    </div>
                    <!-- Dark Mode Toggle Button -->
                    <button class="toggle-button" id="toggle-mode">
                        <img src="../Assets/Dark_mode.svg" alt="Dark mode" width="25" height="25">
                    </button>
                </div>

                <!-- CGPA and SGPA block -->
                <div class="small-block">
                    <p><strong>CGPA:</strong> <?= htmlspecialchars($cgpa); ?></p>
                    <p><strong>SGPA:</strong> <?= htmlspecialchars($sgpa); ?></p>
                </div>

                <!-- Profile section -->
                <div class="small-block">
                    <div class="profile-card">
                        <img src="<?= htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
                        <div class="profile-details">
                            <p><strong>Name:</strong> <?= htmlspecialchars($profile['First_Name'] . " " . $profile['Middle_Name']. " " . $profile['Last_Name']); ?></p>
                            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($profile['Date_Of_Birth']); ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($profile['Email']); ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($profile['PhoneNo']); ?></p>
                            <p><strong>Roll No.:</strong> <?= htmlspecialchars($profile['Roll_No']); ?></p>
                            <p><strong>University No.:</strong> <?= htmlspecialchars($profile['University_No']); ?></p>
                            <p><strong>Major:</strong> <?= htmlspecialchars($profile['Major']); ?></p>
                            <!-- Bio removed as per the requirement -->
                        </div>
                    </div>
                </div>

                <!-- Notices section -->
                <div class="small-block">
                    <p style="text-align: center;"><strong>Notices:</strong></p>
                    <ul class="notice-block">
                        <?php foreach ($announcements as $announcement): ?>
                            <li><a href="Announcements.php?id=<?= htmlspecialchars($announcement['Announcement_ID']); ?>">
                                <?= htmlspecialchars($announcement['Title']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Date and time display
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            };
            const formattedDateTime = now.toLocaleDateString('en-US', options).replace(',', ''); // Remove the comma after day
            document.getElementById('date-time').textContent = formattedDateTime;
        }

        // Update every minute
        setInterval(updateDateTime, 60000);
        updateDateTime(); // Initial call

        // Dark mode toggle functionality
        const toggleButton = document.getElementById('toggle-mode');
        const toggleIcon = toggleButton.querySelector('img');

        // Check local storage for saved mode preference
        const savedMode = localStorage.getItem('mode');
        if (savedMode) {
            document.body.classList.toggle('light-mode', savedMode === 'light');
            toggleIcon.src = savedMode === 'light' ? '../Assets/Light_mode.svg' : '../Assets/Dark_mode.svg';
        }

        toggleButton.addEventListener('click', function () {
            document.body.classList.toggle('light-mode');
            const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('mode', newMode);
            toggleIcon.src = newMode === 'light' ? '../Assets/Light_mode.svg' : '../Assets/Dark_mode.svg';
        });
    </script>
</body>
</html>
