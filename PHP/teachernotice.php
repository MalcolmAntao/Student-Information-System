<?php
include 'Connection.php'; // Include database connection
session_start();

// Get the currently logged-in user's ID
$user_id = $_SESSION['user_id'];

try {
    // Create a PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the Role_ID for the logged-in user
    $query = "SELECT Role_ID FROM users WHERE User_ID = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $roleID = $stmt->fetchColumn();

    // Fetch all notices from the database
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

    // Logic for form submission to create a new notice
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = $_POST['noticeTitle'];
        $content = $_POST['noticeContent'];
        $authorId = 1; // Replace with the ID of the currently logged-in user
        $postingDate = date('Y-m-d');

        // Insert new notice into the database
        $insertQuery = "
            INSERT INTO announcements (Title, Content, Posting_Date, Author_ID) 
            VALUES (:title, :content, :postingDate, :authorId)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            ':title' => $title,
            ':content' => $content,
            ':postingDate' => $postingDate,
            ':authorId' => $authorId
        ]);

        if ($insertStmt->rowCount()) {
            echo "<script>alert('Notice uploaded successfully!'); window.location.href='teachernotice.php';</script>";
            exit; // Stop further script execution
        } else {
            echo "<script>alert('Failed to upload notice.'); window.location.href='teachernotice.php';</script>";
            exit; // Stop further script execution
        }        
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Notice Creation</title>
    <style>
        /* Reset and basic styling */
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Default dark mode styling */
        body {
            background-color: #120E0E; /* Dark background */
            color: white; /* Light text */
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

        .main-content {
            margin-left: 60px;
            padding: 20px;
            /*width: calc(100% - 60px);*/ /* Takes up the rest of the available width */
            transition: margin-left 0.3s, width 0.3s;
        }

        #sidebar:hover ~ .main-content {
            margin-left: 200px;
            /*width: calc(100% - 200px);*/ /* Adjusts width when the sidebar expands */
        }

        /* Added styling for the header container */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-color: #444; /* Default dark button color */
            color: white;
            font-size: 16px;
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

        #profile-button:hover {
            background-color: #555;
        }

        /* Flexbox container */
        .flex-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 20px; /* Adds space between the two sections */
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
                    hsl(0deg 4% 13%) 100%);;

            border-radius: 5px;
        }

        .notice-section h2 {
            margin-top: 0;
        }

        input[type="text"], textarea, select {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            background-color: #333; 
            color: white;
            border: 1px solid #555;
            border-radius: 5px;
        }  

        button {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
        }

        .notice-detail,
        .notice-card {
            background-color: #333;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px #333;
            margin-bottom: 10px;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;

        }

        body.light-mode .notice-detail,
        body.light-mode .notice-card {
            background-color: #ffffff;
            box-shadow: 0 4px 8px #ccc;
        }

        /* Date Section Flex styling */
        .date-section {
            display: flex;
            flex-direction: row; /* Keep the date and buttons next to each other */
            justify-content: space-between;
            align-items: center;
            width: 100%;
            }

        #date-section {
            width: 15%; /* Change this to 15% if you want it smaller */
            background: linear-gradient(175deg, hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            border-radius: 5px;
            box-sizing: border-box;
            height: 100px; /* Adjust the height as needed */
            padding: 5px; /* Reduce padding to make it more compact */
        }

        #date-section h3 {
            color: #0bb421; /* A distinct color for the heading */
        }

        #date-section p {
            color: #ccc; /* Text color for the date */
            font-size: 14px; /* Adjust the font size if needed */
            padding: 5px;
            border-radius: 5px;
            background-color: #333; /* Light background for the date text */
            border: 1px solid #444; /* A subtle border around the date */
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
            color: #333; /* Dark text color */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        }

        body.light-mode #date-section h3 {
            color: #3161a4; /* A distinct color for the heading */
            padding: 0px;
        }

        body.light-mode #date-section p {
            color: #333; /* Text color for the date */
            font-size: 14px; /* Adjust the font size if needed */
            padding: 5px;
            border-radius: 5px;
            background-color: #ffffff; /* Light background for the date text */
            border: 1px solid #ddd; /* A subtle border around the date */
        }

        /* Light Mode Styling */
        body.light-mode {
            background-color: #f5f5f5; /* Light background for body */
            color: #333; /* Dark text */
        }

        /* Light mode sidebar */
        .light-mode #sidebar {
            background: linear-gradient(130deg,
            #83a4d4, #b6fbff); /* Light sidebar */
        }

        .light-mode #sidebar-menu ul li a {
            color: #333;
            text-decoration: none;
            font-size: 16px;
        }

        /* Light mode main content */
        .light-mode .main-content {
            background-color: #fff; /* Light background for content sections */
            color: #333; /* Dark text */
        }

        /* Light Mode Styling for the Notice Creation Form */
        .light-mode .notice-section {
            background: radial-gradient(at top left,
            #83a4d4, #b6fbff);
            color: #333; 
        }

        .light-mode input[type="text"], 
        .light-mode textarea {
            background-color: #fff; /* Light background for input fields */
            color: #333; /* Dark text for input fields */
            border: 1px solid #ccc; /* Lighter border in light mode */
        }

        .light-mode button {
            background-color: #ddd; /* Light background for buttons */
            color: #333; /* Dark text for buttons */
        }

        .light-mode button:hover {
            background-color: #ccc; /* Slightly darker hover effect for buttons */
        }

        /* Light mode for the form labels */
        .light-mode label {
            color: #333; /* Dark text for labels */
        }

        /* Light mode for buttons */
        .light-mode button {
            background-color: #ddd; /* Light button */
            color: #333; /* Dark text */
        }

        .light-mode button:hover {
            background-color: #ccc;
        }

        /* Light mode for previous notices */
        .light-mode .previous-notices {
            background-color: #eee;
        }

        /* Light mode for individual notice items */
        .light-mode .previous-notices ul li {
            background-color: #fff;
            border: 1px solid #ccc;
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

        <main class="main-content">
            <div class="header-container">
                <div id="date-section" class="section">
                    <h3>Date</h3>
                    <p id="current-date"></p>
                </div>

                <div class="header-controls">
                    <button id="theme-toggle">Change Theme</button>
                    <button id="profile-button">Go to Profile</button>
                </div>
            </div>

            <div class="flex-container">
                <div class="notice-section">
                    <h2 class = "notice-heading">Create Notice</h2>
                    <form id="noticeForm" action="notice-handler.php" method="POST">
                        <label for="noticeTitle">Notice Title:</label>
                        <input type="text" id="noticeTitle" name="noticeTitle" placeholder="Enter Notice Title" required>
                        <br><br>

                        <label for="noticeContent">Notice Content:</label>
                        <br>
                        <textarea id="noticeContent" name="noticeContent" placeholder="Enter Text" required></textarea>

                        <button type="submit" id="uploadButton">Upload</button>
                    </form>
                </div>

                <div class="section" id="notices-section">
                    <!-- Notice Detail Card -->
                    <div id="notice-details" class="notice-detail">
                        <?php if ($noticeDetail): ?>
                            <h2><?= htmlspecialchars($noticeDetail['Title']); ?></h2>
                            <p><strong>Posted by:</strong> <?= htmlspecialchars($noticeDetail['First_Name'] . ' ' . $noticeDetail['Last_Name']); ?></p>
                            <p><strong>Posted on:</strong> <?= date('F j, Y', strtotime($noticeDetail['Posting_Date'])); ?></p>
                            <p><?= nl2br(htmlspecialchars($noticeDetail['Content'])); ?></p>
                        <?php endif; ?>
                    </div>

                    <h2 class = "notice-heading">All Notices</h2>
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
            </div>
        </main>
    <script>
        // Handle form submission
        document.getElementById('noticeForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting the traditional way

            const formData = new FormData(this); // Collect form data

            // Send form data to the server asynchronously
            fetch('notice-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Expect JSON response
            .then(data => {
                if (data.success) {
                    alert('Notice uploaded successfully!');

                    // Clear the form fields
                    document.getElementById('noticeForm').reset();

                    // Update the table with the latest notices
                    updateNoticesTable(data.announcements);
                } else {
                    alert('Failed to upload notice.');
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Function to update the notices table dynamically
        function updateNoticesTable(announcements) {
            const tableBody = document.querySelector('#notices-section tbody');
            tableBody.innerHTML = ''; // Clear existing table rows

            // Populate the table with new data
            announcements.forEach(announcement => {
                const authorName = `${announcement.First_Name} ${announcement.Middle_Name} ${announcement.Last_Name}`.trim();
                const noticeDetails = `
                    Title: ${announcement.Title}
                    Content: ${announcement.Content}
                    Date: ${announcement.Posting_Date}
                    Author: ${authorName}`;

                const row = document.createElement('tr');
                row.onclick = function() { alert(noticeDetails); };
                
                row.innerHTML = `
                    <td>${announcement.Posting_Date}</td>
                    <td>${announcement.Title}</td>
                    <td>${authorName}</td>
                `;

                tableBody.appendChild(row);
            });
        }

        // Function to display today's date in the "Date" section
        function displayDate() {
            const today = new Date();
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            const formattedDate = today.toLocaleDateString('en-GB', options); // Format as DD/MM/YYYY

            document.getElementById('current-date').textContent = formattedDate;
        }

        // Call the function when the page loads
        window.onload = function() {
            displayDate();
        };

        // Theme Toggle Functionality
        document.getElementById('theme-toggle').addEventListener('click', () => {
            document.body.classList.toggle('light-mode');
        });

        // Event listener for the "Go to Profile" button
        document.getElementById('profile-button').addEventListener('click', function() {
            window.location.href = 'teacherprofile.php'; // Ensure this path is correct
        });

        function loadNoticeDetails(noticeID) {
            window.location.href = 'teachernotice.php?id=' + noticeID;
        }

    </script>
</body>
</html>