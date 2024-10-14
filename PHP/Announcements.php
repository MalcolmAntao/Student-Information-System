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
        /* Add your custom styles here */
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
        .notice-detail {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            display: none; /* Initially hidden */
        }
    </style>
    <script>
        // JavaScript to load the notice details via AJAX
        function loadNoticeDetails(noticeID) {
            var detailsContainer = document.getElementById('notice-details');
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'getNoticeDetails.php?id=' + noticeID, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText); // Parse the JSON response
                    if (response.success) {
                        detailsContainer.innerHTML = '<h2>' + response.title + '</h2>' +
                                                     '<p><strong>Posted on:</strong> ' + response.posting_date + '</p>' +
                                                     '<p>' + response.content + '</p>';
                        detailsContainer.style.display = 'block'; // Show the details
                    } else {
                        detailsContainer.innerHTML = '<p>Error loading notice details.</p>';
                    }
                }
            };
            xhr.send();
        }

        // Check if the page is loaded with a specific notice ID from the URL
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            var noticeID = urlParams.get('id');
            if (noticeID) {
                loadNoticeDetails(noticeID);
            }
        };
    </script>
</head>
<body>
    <h1>Notices</h1>

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
                        <span style="cursor: pointer; color: blue;" onclick="loadNoticeDetails(<?= $notice['Announcement_ID']; ?>)">
                            <?= htmlspecialchars($notice['Title']); ?>
                        </span>
                    </td>
                    <td><?= date('F j, Y, g:i a', strtotime($notice['Posting_Date'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
