<?php
include 'Connection.php'; // Include database connection

// Check if notice ID is passed
if (isset($_GET['id'])) {
    $noticeID = (int)$_GET['id'];

    // Fetch the specific notice from the database
    $sql = "SELECT Title, Content, Posting_Date
            FROM Announcements
            WHERE Announcement_ID = :notice_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['notice_id' => $noticeID]);
    $noticeDetail = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($noticeDetail) {
        // Return the notice details in JSON format
        echo json_encode([
            'success' => true,
            'title' => htmlspecialchars($noticeDetail['Title']),
            'posting_date' => date('F j, Y, g:i a', strtotime($noticeDetail['Posting_Date'])),
            'content' => nl2br(htmlspecialchars($noticeDetail['Content']))
        ]);
    } else {
        // Return an error if the notice is not found
        echo json_encode(['success' => false]);
    }
}
