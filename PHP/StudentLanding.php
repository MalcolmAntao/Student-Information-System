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

// Fetch courses the student is enrolled in, including grades (now part of `Grades` table)
$sql = "SELECT c.CourseName, c.Credits, c.Description, g.Semester, g.Year, g.IT1, g.IT2, g.IT3, g.Sem
        FROM Enrolls_In e
        JOIN Courses c ON e.Course_ID = c.Course_ID
        LEFT JOIN Grades g ON e.Course_ID = g.Course_ID AND e.Student_ID = g.Student_ID
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

// Initialize variables
$totalCredits = 0;
$totalGradePoints = 0;
$sgpa = 0;
$currentSemesterCredits = 0;
$currentSemesterGradePoints = 0;

// Fetch SGPA and CGPA from the student_sgpa_cgpa view
$sql = "SELECT CGPA, 
               CASE
                   WHEN SGPA_Sem8 IS NOT NULL THEN SGPA_Sem8
                   WHEN SGPA_Sem7 IS NOT NULL THEN SGPA_Sem7
                   WHEN SGPA_Sem6 IS NOT NULL THEN SGPA_Sem6
                   WHEN SGPA_Sem5 IS NOT NULL THEN SGPA_Sem5
                   WHEN SGPA_Sem4 IS NOT NULL THEN SGPA_Sem4
                   WHEN SGPA_Sem3 IS NOT NULL THEN SGPA_Sem3
                   WHEN SGPA_Sem2 IS NOT NULL THEN SGPA_Sem2
                   WHEN SGPA_Sem1 IS NOT NULL THEN SGPA_Sem1
                   ELSE NULL
               END AS Current_SGPA
        FROM student_sgpa_cgpa 
        WHERE Student_ID = :student_id";

// Prepare and execute the statement to fetch SGPA and CGPA
$stmt = $pdo->prepare($sql);
$stmt->execute(['student_id' => $student_id]);
$sgpa_cgpa = $stmt->fetch(PDO::FETCH_ASSOC);

// // Debug: Check if CGPA and SGPA values are retrieved
// var_dump($sgpa_cgpa); // This will display the array
// exit(); // Stop further execution to review the output


// Format CGPA and SGPA to 2 decimal places
$cgpa = number_format((float)$sgpa_cgpa['CGPA'], 2);
$sgpa = number_format((float)$sgpa_cgpa['Current_SGPA'], 2);



// $cgpa = $totalCredits ? round($totalGradePoints / $totalCredits, 2) : 0;
// $sgpa = $currentSemesterCredits ? round($currentSemesterGradePoints / $currentSemesterCredits, 2) : 0;

$courseNames = [];
$courseMarks = [];

foreach ($courses as &$course) { // Use reference to modify each course
    // Check if IT marks and Sem marks are present
    $it1 = isset($course['IT1']) ? $course['IT1'] : 0;
    $it2 = isset($course['IT2']) ? $course['IT2'] : 0;
    $it3 = isset($course['IT3']) ? $course['IT3'] : 0;
    $semMarks = isset($course['Sem']) ? $course['Sem'] : 0;

    // Calculate average IT marks
    $averageIT = ($it1 + $it2 + $it3) / 3;

    // Total marks = average IT + Sem marks
    $totalMarks = $averageIT + $semMarks;

     // Add course name and total marks to the arrays
     $courseNames[] = $course['CourseName'];
     $courseMarks[] = $totalMarks;

    // Calculate grade and grade point
    list($grade, $gradePoint) = calculateGrade($totalMarks);

    // Add to course array
    $course['Average_IT'] = round($averageIT, 2);
    $course['Total_Marks'] = round($totalMarks, 2); // This line is the source of the error if Total_Marks is undefined.
    $course['Grade'] = $grade;
    $course['Grade_Point'] = $gradePoint;
}
unset($course); // Break the reference


// Prepare profile picture
$defaultImage = '../Assets/Profile.svg';
$ProfilePath = '../Assets/ProfileImages/' . htmlspecialchars($profile['Profile_Picture']); // Assuming the images are stored in the 'ProfileImages' folder
if (!empty($profile['Profile_Picture'])) {
    // If the profile picture exists, display it from the stored path
    $profilePicture = $ProfilePath;
    if (!file_exists($profilePicture)) {
        $profilePicture = $defaultImage; // Fallback to default if image file not found
    }
} else {
    // If no profile picture is set, use a default image (with relative path)
    $profilePicture = $defaultImage; // Ensure this path is correct and points to your default image
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
            flex: 0.8;
            gap: 20px;
        }

        /* gpa and Date blocks smaller */
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

        /* Flex container for date and gpa to be side by side */
        .date-gpa {
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

        
        /* Courses section */
        .courses {
            background-color: #232B3A;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            overflow-y: auto;
            justify-content: space-around;
        }

        body.light-mode .courses {
            background-color: #c3d8da;
        }

        /* Course card container */
        .course-card {
            background-color: #1B222E;
            border-radius: 10px;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            max-width: 300px;
            word-wrap: break-word;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s ease;
            flex: 1; /* Ensure card grows and shrinks properly within the flex container */
            min-width: 200px; /* Prevent cards from getting too small */
            box-sizing: border-box;
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
            padding: 10px;
            z-index: 1;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Course marks card (initially hidden under basic info) */
        .course-marks {
            background-color: rgba(54, 62, 78, 0.95) ;
            color: #c1e1e2;
            border-radius: 10px;
            padding: 10px;
            box-sizing: border-box;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 0; /* Hidden by default */
            z-index: 0; /* Under the basic info */
            transition: opacity 0.3s ease, z-index 0.3s ease;
        }

        body.light-mode .course-marks {
            background-color: rgba(180, 200, 202, 0.95); /* Light mode background color */
            color: #000000;
        }

        /* On hover, show the marks card and hide the basic info */
        .course-card:hover .course-basic {
            opacity: 0; /* Fade out basic info */
            z-index: 0; /* Move it behind marks card */
        }

        .course-card:hover .course-marks {
            opacity: 1; /* Fade in marks card */
            z-index: 1; /* Bring it to the top */
        }

        .course-marks p, .course-marks strong {
            line-height: 1.2;
            margin: 0;
            transition: color 0.3s ease; /* Smooth transition for text color */
        }

        /* Ensure "Marks Details" changes color along with other texts */
        .course-marks strong {
            color: white;
        }

        body.light-mode .course-marks strong {
            color: black; /* Change to black for light mode */
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
        canvas {
            margin: 10px; /* Add some margin to each chart */
        }

        .chart-container {
            display: flex;
            justify-content: center; /* Center the charts horizontally */
            gap: 50px; /* Add space between the charts */
        }

        .chart-block {
            text-align: center; /* Center the text inside each chart block */
        }

        .chart-block canvas {
            display: block;
            margin: 0 auto; /* Center the canvas inside the chart block */
            max-width: 100%; /* Ensure canvas does not overflow */
            max-height: 100%; /* Ensure canvas does not overflow */
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
                    <a href="../PHP/Announcements.php">Announcements</a>
                    
                    <a href="../PHP/StudentProfile.php">Profile</a>
                    <!-- <a href="settings.html">Settings</a> -->
                    <a href="../PHP/Logout.php">Logout</a>
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

                <!-- Date and GPA (side by side) -->
                <div class="date-gpa">
                    <div class="small-block" id="date-block">
                        <p id="date-time"></p>
                    </div>
                    <div class="small-block">
                        <div class="chart-container">
                            <div class="chart-block">
                                <canvas id="cgpaChart" width="100" height="100"></canvas>
                            </div>
                            <div class="chart-block">
                                <canvas id="sgpaChart" width="100" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="small-block">
                        <p>What to put here?</p>
                    </div>
                </div>
                
                <!-- Courses section with scrollable content -->
                <!-- Courses section with separate cards for basic info and marks -->
                <div class="courses">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <!-- Basic Info Card -->
                            <div class="course-basic">
                                <strong><?php echo htmlspecialchars($course['CourseName']); ?></strong> <br>
                                <em>Description:</em> <?php echo htmlspecialchars($course['Description']); ?> <br>
                                <em>Credits:</em> <?php echo htmlspecialchars($course['Credits']); ?>
                            </div>
                            <!-- Marks Info Card (Initially hidden) -->
                            <div class="course-marks">
                                <strong>Marks Details</strong>
                                <p>Average IT Marks: <?= htmlspecialchars($course['Average_IT']); ?></p>
                                <p>Semester Marks: <?= htmlspecialchars($course['Sem']); ?></p>
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
                        <img src="../Assets/Profile.svg" alt="Profile" width="25" height="25">
                    </div>
                    <!-- Dark Mode Toggle Button -->
                    <button class="toggle-button" id="toggle-mode">
                        <img src="../Assets/Dark_mode.svg" alt="Dark mode" width="25" height="25">
                    </button>
                </div>

                <!-- Performance block -->
                <div class="small-block">
                    <p>performance<p>
                    <canvas id="performanceChart"></canvas> <!-- Radar chart canvas -->
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
        // Redraw charts after mode change
        function redrawCharts() {
            cgpaChart.update(); // Redraw CGPA chart
            sgpaChart.update(); // Redraw SGPA chart
        }

        toggleButton.addEventListener('click', function () {
            document.body.classList.toggle('light-mode');
            const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('mode', newMode);
            toggleIcon.src = newMode === 'light' ? '../Assets/Light_mode.svg' : '../Assets/Dark_mode.svg';

            // Redraw the doughnut charts with new colors
            const chartColors = getChartColors();
            cgpaChart.data.datasets[0].backgroundColor = chartColors.cgpa;
            sgpaChart.data.datasets[0].backgroundColor = chartColors.sgpa;
            cgpaChart.update();
            sgpaChart.update();

            // Redraw the radar chart with new colors
            const radarColors = getRadarChartColors();
            performanceChart.data.datasets[0].backgroundColor = radarColors.backgroundColor;
            performanceChart.data.datasets[0].borderColor = radarColors.borderColor;
            performanceChart.options.scales.r.ticks.color = radarColors.ticksColor;
            performanceChart.options.scales.r.grid.color = radarColors.gridColor;
            performanceChart.update();  // Update the radar chart

            updateRadarChartColors(); // Call to update other radar chart colors, if necessary
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- // to use charts -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-doughnutlabel"></script>  <!-- //to display cgpa and sgpa inside the doughnut chart -->
    
    <!-- //script for cgpa/sgpa chart -->
    <script>
    // Function to draw the text in the center of the doughnut chart
    function drawCenterText(chart, text) {
    const ctx = chart.ctx;
    const width = chart.width;
    const height = chart.height;

    // Split the text by line breaks if provided
    const lines = text.split('\n');

    // Check if light mode is enabled
    const isLightMode = document.body.classList.contains('light-mode');
    const textColor = isLightMode ? '#000' : '#fff'; // Black for light mode, white for dark mode

    ctx.restore();
    const fontSize = (height / 114).toFixed(2);
    ctx.font = fontSize + "em sans-serif";
    ctx.textBaseline = "middle";
    ctx.fillStyle = textColor; // Set text color based on the current mode

    // Calculate the Y-position for the first line (centered)
    const lineHeight = fontSize * 25; // Adjust this for more or less spacing between lines
    const textYStart = height / 2 - (lines.length - 1) * lineHeight / 2;

    // Draw each line with appropriate Y-coordinate
    lines.forEach((line, index) => {
        const textX = Math.round((width - ctx.measureText(line).width) / 2);
        const textY = textYStart + index * lineHeight;
        ctx.fillText(line, textX, textY);
    });

    ctx.save();
}
    
    // Function to get current mode and return the colors for the doughnut chart
    function getChartColors() {
    const isLightMode = document.body.classList.contains('light-mode');
    
    return {
        // CGPA Chart Colors
        cgpa: isLightMode 
            ? ['#F39C12', '#8E44AD']   // Light Mode
            : ['#1E90FF', '#FF5A5F'],  // Dark Mode
        
        // SGPA Chart Colors
        sgpa: isLightMode 
            ? ['#9B1B30', '#2C3E50']   // Light Mode
            : ['#00CED1', '#D5006D']   // Dark Mode
    };
}



// Doughnut chart for CGPA
const cgpaCtx = document.getElementById('cgpaChart').getContext('2d');
const cgpaColors = getChartColors();
const cgpaChart = new Chart(cgpaCtx, {
    type: 'doughnut',
    data: {
        labels: ['CGPA', 'Remaining'],
        datasets: [{
            data: [<?= $cgpa; ?>, 10 - <?= $cgpa; ?>], // Assume max GPA is 10
            backgroundColor: cgpaColors.cgpa,
            borderWidth: 0 // Remove the border
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false // Hide the legend (labels)
            }
        },
        cutout: '80%', // Thinner doughnut chart
        responsive: true,
        animation: {
            animateScale: true
        }
    },
    plugins: [{
        afterDraw: function(chart) {
            drawCenterText(chart, 'CGPA\n<?= $cgpa; ?>'); // Display CGPA in the center
        }
    }]
});

// Doughnut chart for SGPA
const sgpaCtx = document.getElementById('sgpaChart').getContext('2d');
const sgpaColors = getChartColors();
const sgpaChart = new Chart(sgpaCtx, {
    type: 'doughnut',
    data: {
        labels: ['SGPA', 'Remaining'],
        datasets: [{
            data: [<?= $sgpa; ?>, 10 - <?= $sgpa; ?>], // Assume max GPA is 10
            backgroundColor: sgpaColors.sgpa,
            borderWidth: 0 // Remove the border
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false // Hide the legend (labels)
            }
        },
        cutout: '80%', // Thinner doughnut chart
        responsive: true,
        animation: {
            animateScale: true
        }
    },
    plugins: [{
        afterDraw: function(chart) {
            drawCenterText(chart, 'SGPA\n<?= $sgpa; ?>'); // Display SGPA in the center
        }
    }]
});
</script>
<!-- //script for performance chart -->
<script>
    // Fetch the course names and marks from PHP
    const courseNames = <?= json_encode($courseNames); ?>;
    const courseMarks = <?= json_encode($courseMarks); ?>;

    // Function to fetch updated courses and marks periodically
    function fetchUpdatedData() {
        fetch('fetch_courses_and_marks.php')  // PHP file to return updated data in JSON
            .then(response => response.json())
            .then(data => {
                updatePerformanceChart(data.courses, data.marks);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    // // Poll the server every 30 seconds for updated data
    // setInterval(fetchUpdatedData, 30000);

    // Function to update the radar chart dynamically
    function updatePerformanceChart(courses, marks) {
        // Update radar chart data
        performanceChart.data.labels = courses;
        performanceChart.data.datasets[0].data = marks;

        // Re-render the chart to reflect updated data
        performanceChart.update();
    }
// Function to get colors for the radar chart based on the mode
function getRadarChartColors() {
    const isLightMode = document.body.classList.contains('light-mode');
    return {
        backgroundColor: isLightMode ? 'rgba(75, 192, 192, 0.2)' : 'rgba(255, 193, 7, 0.2)',
        borderColor: isLightMode ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 193, 7, 1)',
        ticksColor: isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)',
        gridColor: isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)'
    };
}

// Initialize the radar chart for performance
const radarColors = getRadarChartColors();
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: courseNames,  // Dynamic course names from PHP
        datasets: [{
            data: courseMarks,  // Dynamic marks from PHP
            backgroundColor: radarColors.backgroundColor,  // Light color for radar area
            borderColor: radarColors.borderColor,  // Border color of the radar
            borderWidth: 2  // Border width of the lines
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,  // Allows the chart to scale with the container size
        scales: {
            r: {
                suggestedMin: 0,  // Minimum value for the radar chart
                suggestedMax: 125,  // Max marks is 125
                ticks: {
                    backdropColor: 'transparent', // Remove background color
                    color: radarColors.ticksColor,
                    stepSize: 25,  // Set the interval between ticks (25, 50, 75, 100, 125) // Color based on mode
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'  // Grid line color
                },
                angleLines: {
                    display: true,
                    color: 'rgba(0, 0, 0, 0.1)'  // Adjust angle line color
                }
            }
        },
        layout: {
            padding: {
                top: 5,  // Add padding at the top to ensure no overlap
                bottom: 5  // Padding at the bottom for better spacing
            }
        },
        plugins: {
            legend: {
                display: false  // Hide the legend entirely
            }
        }
    }
});
updateRadarChartColors();// to update the color based on the mode the user is in
    
    // Function to update radar chart colors based on light/dark mode
function updateRadarChartColors() {
    const isLightMode = document.body.classList.contains('light-mode');
    
    // Update tick color
    performanceChart.options.scales.r.ticks.color = isLightMode ? 'rgba(0, 0, 0, 0.87)' : 'rgba(255, 255, 255, 0.87)';
    
    // Update grid color
    performanceChart.options.scales.r.grid.color = isLightMode ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)';
    
    // Update chart colors
    performanceChart.data.datasets[0].backgroundColor = isLightMode ? 'rgba(75, 192, 192, 0.2)' : 'rgba(255, 193, 7, 0.2)';
    performanceChart.data.datasets[0].borderColor = isLightMode ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 193, 7, 1)';
    
    performanceChart.update();
}
    fetchUpdatedData(); // <-- Important for initial chart rendering
    setInterval(fetchUpdatedData, 30000); // Poll every 30 seconds

</script>

</body>
</html>
