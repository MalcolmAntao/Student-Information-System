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

// Check if a specific notice ID is provided via the URL
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
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        :root {
            --bg-color: #fff;
            --text-color: #000;
            --accent-color: #007bff;
            --border-color: #ddd;
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --accent-color: #90caf9;
            --border-color: #444;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: var(--accent-color);
            color: #fff;
        }

        h1, h2 {
            color: var(--text-color);
        }

        .toggle-switch {
            cursor: pointer;
            font-size: 16px;
            padding: 5px 10px;
            background-color: #fff;
            border: none;
            border-radius: 20px;
            color: #000;
            transition: all 0.3s;
        }

        .toggle-switch.dark {
            background-color: #333;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: var(--bg-color);
        }

        th, td {
            padding: 12px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        th {
            background-color: var(--accent-color);
            color: #fff;
        }

        tr:hover {
            background-color: var(--accent-color);
            color: #fff;
        }

        .notice-detail {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            display: none;
        }

        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .content-wrapper {
            max-width: 1000px;
            margin: 20px;
        }

        .notice-title {
            cursor: pointer;
            color: var(--accent-color);
            text-decoration: underline;
        }
    </style>

    <script>
        // Toggle light and dark mode
        function toggleTheme() {
            var currentTheme = document.documentElement.getAttribute("data-theme");
            var targetTheme = currentTheme === "dark" ? "light" : "dark";
            document.documentElement.setAttribute("data-theme", targetTheme);
            localStorage.setItem("theme", targetTheme); // Save preference
        }

        // Load theme preference on page load
        window.onload = function() {
            var savedTheme = localStorage.getItem("theme") || "light";
            document.documentElement.setAttribute("data-theme", savedTheme);

            var urlParams = new URLSearchParams(window.location.search);
            var noticeID = urlParams.get('id');
            if (noticeID) {
                loadNoticeDetails(noticeID);
            }
        };

        // Load the notice details via AJAX
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

    <header>
        <h1>Notices</h1>
        <button class="toggle-switch" onclick="toggleTheme()">Toggle Theme</button>
    </header>

    <div class="center-content">
        <div class="content-wrapper">
            <!-- Placeholder for dynamic notice details -->
            <div id="notice-details" class="notice-detail"></div>

            <?php if ($noticeDetail): ?>
                <!-- If a specific notice ID is passed via the URL, display the details -->
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
