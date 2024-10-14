<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

// Fetch all notices from the database
$sql = "SELECT Announcement_ID, Title, Posting_Date, Content
        FROM Announcements
        ORDER BY Posting_Date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if a specific notice ID is provided from a GET request
$noticeID = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Fetch the specific notice if notice ID is passed
$noticeDetail = null;
if ($noticeID) {
    $sql = "SELECT Title, Content, Posting_Date
            FROM Announcements
            WHERE Announcement_ID = :notice_id";
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
        :root {
            --bg-color-dark:#1D2433;
            --text-color-dark: #ffffff;
            --bg-color-light: #d9e8e8;
            --text-color-light: #000000;
            --sidebar-bg-color-dark: #232B3A;
            --sidebar-bg-color-light: #253b42 ;
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

        /* Sidebar content */
        .sidebar-content {
            padding: 20px;
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        body.light-mode .sidebar-content h2{
          color: #ffffff ;
        }

        .sidebar:hover .sidebar-content {
            opacity: 1;
            transform: translateX(0);
        }

        /* Sidebar links */
        .sidebar-links a {
            display: block;
            padding: 10px 0;
            color: #FFFFFF;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        body.light-mode .sidebar-links a {
            color: #cacaca;
        }

        .sidebar-links a:hover {
            color: #2F9DFF;
        }

        body.light-mode .sidebar-links a:hover {
            color: #4f8585;
        }

        /* Main content area */
        .main-content {
            display: flex;
            flex: 4;
            padding: 20px;
            gap: 20px;
        }

        .main-content .notice-detail {
            background-color: #232B3A;
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            margin-top: 20px;
        }

        .main-content .notices-table {
            background-color: #232B3A;
            border-radius: 10px;
            padding: 20px;
            color: #fff;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }

        .notice-title {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .loading {
            font-size: 14px;
            color: grey;
        }

        /* Flexbox for dynamic styling */
        .column {
            display: flex;
            flex-direction: column;
        }

        /* Search bar styling */
        .search-bar {
            width: 100%;
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
            background-color: #c3d8da ;
            color: #000000;
        }

        /* Dark Mode Toggle */
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
            background-color: #4a6fa1 ;
        }

        body.light-mode .toggle-button {
            background-color: #95c0c4;
            color: #000000;
        }

        body.light-mode .toggle-button:hover{
            background-color: #c1e1e2 ;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-content">
                <h2>Welcome back, Malcolm</h2>
                <div class="sidebar-links">
                    <a href="home.html">Home</a>
                    <a href="Announcements.php">Announcements</a>
                    <a href="dashboard.html">Dashboard</a>
                    <a href="profile.html">Profile</a>
                    <a href="settings.html">Settings</a>
                    <a href="Landing.html">Logout</a>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <div class="column">
                <!-- Search Bar -->
                <div class="search-bar">
                    <form action="search.php" method="GET">
                        <input type="text" name="query" placeholder="Search Notices">
                    </form>
                </div>

                <!-- Notice details (shown on click) -->
                <div id="notice-details" class="notice-detail"></div>
                <p id="loading-message" class="loading" style="display:none;">Loading notice details...</p>

                <!-- Display the specific notice if ID is provided (from external link) -->
                <?php if ($noticeDetail): ?>
                    <div class="notice-detail">
                        <h2>Notice: <?= htmlspecialchars($noticeDetail['Title']); ?></h2>
                        <p><strong>Posted on:</strong> <?= date('F j, Y, g:i a', strtotime($noticeDetail['Posting_Date'])); ?></p>
                        <p><?= nl2br(htmlspecialchars($noticeDetail['Content'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- All notices displayed in a table -->
                <div class="notices-table">
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
        </div>
    </div>

    <script>
        // JavaScript to load the notice details via AJAX
        function loadNoticeDetails(noticeID) {
            var detailsContainer = document.getElementById('notice-details');
            var loadingMessage = document.getElementById('loading-message');
            loadingMessage.style.display = 'block';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'getNoticeDetails.php?id=' + noticeID, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    loadingMessage.style.display = 'none';
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        detailsContainer.innerHTML = '<h2>' + response.title + '</h2>' +
                                                     '<p><strong>Posted on:</strong> ' + response.posting_date + '</p>' +
                                                     '<p>' + response.content + '</p>';
                        detailsContainer.style.display = 'block';
                    } else {
                        detailsContainer.innerHTML = 'Error loading notice details.';
                    }
                }
            };
            xhr.send();
        }

        // Dark/Light Mode Toggle
        var toggleButton = document.querySelector('.toggle-button');
        toggleButton.addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
        });
    </script>
</body>
</html>
