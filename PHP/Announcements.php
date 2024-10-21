<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

// Fetch all notices from the database
$sql = "SELECT Announcement_ID, Title, Posting_Date, Content FROM Announcements ORDER BY Posting_Date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if a specific notice ID is provided via the URL
$noticeID = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Fetch the specific notice if notice ID is passed
$noticeDetail = null;
if ($noticeID) {
    $sql = "SELECT Title, Content, Posting_Date FROM Announcements WHERE Announcement_ID = :notice_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['notice_id' => $noticeID]);
    $noticeDetail = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Light and Dark mode styles */
        :root {
            --bg-color-light: #ffffff;
            --text-color-light: #000000;
            --bg-color-dark: #121212;
            --text-color-dark: #e0e0e0;
            --primary-color-light: #95c0c4;
            --primary-color-dark: #364562;
            --accent-color-light: #f0f0f0;
            --accent-color-dark: #1c1c1c;
            --sidebar-bg-color-dark: #222;
        }

        [data-theme="light"] {
            background-color: var(--bg-color-light);
            color: var(--text-color-light);
        }

        [data-theme="dark"] {
            background-color: var(--bg-color-dark);
            color: var(--text-color-dark);
        }

        /* Sidebar styling */
        .sidebar {
            width: 45px;
            height: 100%;
            background-color: var(--sidebar-bg-color-dark);
            position: fixed;
            transition: width 0.3s ease, background-color 0.3s ease;
            overflow: hidden;
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

        .sidebar:hover .sidebar-content {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar-links a {
            display: block;
            padding: 10px 0;
            color: #FFFFFF; /* Default dark mode text color */
            text-decoration: none;
            transition: color 0.3s ease;
        }
        /* Content area */
        .content {
            margin-left: 45px; /* Adjust to sidebar's closed width */
            padding: 20px;
            transition: margin-left 0.3s;
        }

        .sidebar:hover + .content {
            margin-left: 200px; /* Adjust to sidebar's open width */
        }

        /* Header and main content styling */
        header {
            padding: 20px;
            background-color: var(--primary-color-dark);
            color: var(--text-color-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            margin: 0;
        }

        .toggle-switch {
            cursor: pointer;
            font-size: 16px;
            padding: 5px 10px;
            background-color: var(--text-color-dark);
            border: none;
            border-radius: 20px;
            color: var(--bg-color-dark);
            transition: all 0.3s;
        }

        [data-theme="light"] .toggle-switch {
            background-color: var(--text-color-light);
            color: var(--bg-color-light);
        }

        .notice-detail, table {
            background-color: var(--accent-color-dark);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #444;
        }

        th {
            background-color: var(--primary-color-dark);
        }

        tr:hover {
            background-color: var(--primary-color-dark);
        }

        .notice-title {
            color: #90caf9;
            cursor: pointer;
            text-decoration: underline;
        }

        .notice-detail {
            display: none;
        }

        /* Notices section styling */
        .notices-wrapper {
            margin: 20px;
            background-color: var(--accent-color-dark);
            padding: 20px;
            border-radius: 8px;
        }

    </style>

    <script>
        // Toggle between light and dark mode
        function toggleTheme() {
            var currentTheme = document.documentElement.getAttribute("data-theme");
            var targetTheme = currentTheme === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", targetTheme);
            localStorage.setItem("theme", targetTheme); // Save preference
        }

        // On page load, apply the saved theme from localStorage
        window.onload = function() {
            var savedTheme = localStorage.getItem("theme") || "dark";
            document.documentElement.setAttribute("data-theme", savedTheme);

            var urlParams = new URLSearchParams(window.location.search);
            var noticeID = urlParams.get('id');
            if (noticeID) {
                loadNoticeDetails(noticeID);
            }
        };

        // Load notice details via AJAX
        function loadNoticeDetails(noticeID) {
            var detailsContainer = document.getElementById('notice-details');
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'getNoticeDetails.php?id=' + noticeID, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        detailsContainer.innerHTML = '<h2>' + response.title + '</h2>' +
                                                     '<p><strong>Posted on:</strong> ' + response.posting_date + '</p>' +
                                                     '<p>' + response.content + '</p>';
                        detailsContainer.style.display = 'block';
                    } else {
                        detailsContainer.innerHTML = '<p>Error loading notice details.</p>';
                    }
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>

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
            <div class="sidebar-links">
                <a href="../PHP/StudentLanding.php">Home</a>
                <a href="../PHP/StudentProfile.php">Profile</a>
                <a href="../HTML/Landing.html">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <header>
            <h1>Notices</h1>
            <button class="toggle-switch" onclick="toggleTheme()">Toggle Theme</button>
        </header>

        <div class="notices-wrapper">
            <div id="notice-details" class="notice-detail"></div>

            <?php if ($noticeDetail): ?>
                <div class="notice-detail">
                    <h2>Notice: <?= htmlspecialchars($noticeDetail['Title']); ?></h2>
                    <p><strong>Posted on:</strong> <?= date('F j, Y, g:i a', strtotime($noticeDetail['Posting_Date'])); ?></p>
                    <p><?= nl2br(htmlspecialchars($noticeDetail['Content'])); ?></p>
                </div>
            <?php endif; ?>

            <h2>All Notices</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Posting Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notices as $notice): ?>
                        <tr>
                            <td>
                                <span class="notice-title" onclick="loadNoticeDetails(<?= $notice['Announcement_ID']; ?>)">
                                    <?= htmlspecialchars($notice['Title']); ?>
                                </span>
                            </td>
                            <td><?= date('F j, Y, g:i a', strtotime($notice['Posting_Date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
