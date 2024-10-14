<?php
include 'Connection.php'; // Include database connection

// Check if a notice ID is passed
$noticeID = isset($_GET['id']) ? (int) $_GET['id'] : null;

$response = array('success' => false);

if ($noticeID) {
    // Fetch the specific notice from the database
    $sql = "SELECT Title, Content, Posting_Date
            FROM Announcements
            WHERE Announcement_ID = :notice_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['notice_id' => $noticeID]);
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($notice) {
        // Return the notice details as a JSON response
        $response['success'] = true;
        $response['title'] = $notice['Title'];
        $response['content'] = nl2br(htmlspecialchars($notice['Content']));
        $response['posting_date'] = date('F j, Y, g:i a', strtotime($notice['Posting_Date']));
    }
}

// Output the response as JSON
echo json_encode($response);
