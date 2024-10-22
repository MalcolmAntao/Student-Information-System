<?php
// Include database connection and session management
require_once 'Connection.php';
session_start();

header('Content-Type: application/json'); // Set header to JSON

$response = ['status' => 'error', 'message' => 'Something went wrong'];

if (!isset($_SESSION['student_id'])) {
    $response['message'] = "No student ID found in session.";
    echo json_encode($response);
    exit;
}

$student_id = $_SESSION['student_id'];

// Check if the delete profile picture request was made
    if (isset($_POST['delete_profile_pic'])) {
        // Get the existing profile photo path from the database
        $query = "SELECT Profile_Picture FROM students WHERE Student_ID = :student_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $existingPhoto = $stmt->fetchColumn();

        // Check if a profile picture exists and delete it
        if ($existingPhoto) {
            $photo_path = '../Assets/ProfileImage/' . $existingPhoto; // Adjust the path as necessary
            if (file_exists($photo_path)) {
                unlink($photo_path); // Delete the file from the server
            }

            // Remove the profile picture path from the database
            $update_photo_query = "UPDATE students SET Profile_Picture = NULL WHERE Student_ID = :student_id";
            $stmt = $pdo->prepare($update_photo_query);
            $stmt->bindParam(':student_id', $student_id);
            $stmt->execute();

            // Return a success response
            echo json_encode(['status' => 'success']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No profile picture found.']);
            exit();
        }
    }


// Handle bio update and profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the bio input
    $bio = htmlspecialchars($_POST['bio-input'], ENT_QUOTES);

    // Handle profile photo upload
    if (isset($_FILES['profile-photo-input']) && $_FILES['profile-photo-input']['error'] === UPLOAD_ERR_OK) {
        $profile_photo = $_FILES['profile-photo-input'];

        $upload_dir = '../Assets/ProfileImages/';
        $file_ext = strtolower(pathinfo($profile_photo['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('profile_', true) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($profile_photo['tmp_name'], $upload_path)) {
                // Update profile picture path in the database
                $relative_path = '../Assets/ProfileImages/' . $new_filename;
                $update_photo_query = "UPDATE students SET Profile_Picture = :profile_photo WHERE Student_ID = :student_id";
                $stmt = $pdo->prepare($update_photo_query);
                $stmt->bindParam(':profile_photo', $relative_path);
                $stmt->bindParam(':student_id', $student_id);
                $stmt->execute();
            } else {
                $response['message'] = "Error uploading the profile picture.";
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = "Invalid file type for the profile picture.";
            echo json_encode($response);
            exit;
        }
    }

    // Update the bio field
    $update_bio_query = "UPDATE students SET Bio = :bio WHERE Student_ID = :student_id";
    $stmt = $pdo->prepare($update_bio_query);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':student_id', $student_id);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = "Profile updated successfully!";
    } else {
        $response['message'] = "Error updating bio.";
    }
}

echo json_encode($response);
?>
