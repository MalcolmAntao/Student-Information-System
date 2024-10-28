<?php
include 'Connection.php'; // Include database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['noticeTitle'];
    $content = $_POST['noticeContent'];
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID from session
    $postingDate = date('Y-m-d');

    // Fetch the user's email from the 'users' table
    $fetchEmailQuery = "SELECT Email FROM users WHERE User_ID = :userId";
    $emailStmt = $pdo->prepare($fetchEmailQuery);
    $emailStmt->execute([':userId' => $userId]);
    $userEmail = $emailStmt->fetchColumn();

    if ($userEmail) {
        // Fetch the instructor's ID based on the email in the 'instructors' table
        $fetchInstructorIdQuery = "SELECT Instructor_ID FROM instructors WHERE Contact_Info = :email";
        $instructorStmt = $pdo->prepare($fetchInstructorIdQuery);
        $instructorStmt->execute([':email' => $userEmail]);
        $authorId = $instructorStmt->fetchColumn();

        if ($authorId) {
            // Insert new notice into the 'announcements' table
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


            // Check if the insertion was successful
            if ($insertStmt->rowCount()) {
                // Fetch the updated 8 most recent notices
                // $insertStmt->execute();
                $announcements = $insertStmt->fetchAll(PDO::FETCH_ASSOC);

                // Return success and the updated list of announcements
                echo json_encode([
                    'success' => true,
                    'notices' => $announcements
                ]);
            } else {
                // Return failure response
                echo json_encode(['success' => false, 'message' => 'Failed to insert the notice.']);
            }
        } else {
            // No matching instructor found for the email
            echo json_encode(['success' => false, 'message' => 'Instructor not found.']);
        }
    } else {
        // No matching user email found
        echo json_encode(['success' => false, 'message' => 'User email not found.']);
    }
    exit;
}

// Continue with the HTML output and displaying previous notices if needed
?>