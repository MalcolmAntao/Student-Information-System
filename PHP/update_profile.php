<?php
// Include database connection and session management
require_once 'Connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get current logged-in user ID or unique identifier (assuming it's stored in the session)
    $student_id = $_SESSION['student_id']; 
    echo "Student ID: " . $student_id . "<br>"; // Debugging statement

    // Get the bio input
    $bio = htmlspecialchars($_POST['bio-input'], ENT_QUOTES);
    echo "Bio: " . $bio . "<br>"; // Debugging statement

    // Check if a new profile photo is uploaded and if there is no existing profile photo
    if (isset($_FILES['profile-photo-input']) && $_FILES['profile-photo-input']['error'] === UPLOAD_ERR_OK) {
        $profile_photo = $_FILES['profile-photo-input'];
        
        // Get the existing profile photo from the database
        $query = "SELECT Profile_Photo FROM students WHERE Student_ID = :student_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $existingPhoto = $stmt->fetchColumn();

        echo "Existing Profile Photo: " . $existingPhoto . "<br>"; // Debugging statement

        // If there's no existing profile photo, allow the upload
        if (empty($existingPhoto)) {
            // Handle file upload (Ensure a proper uploads directory)
            $upload_dir = '../Assets/ProfileImages/';
            $file_ext = strtolower(pathinfo($profile_photo['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $new_filename = uniqid('profile_', true) . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($profile_photo['tmp_name'], $upload_path)) {
                    // Update profile picture in the database
                    $update_photo_query = "UPDATE students SET Profile_Photo = :profile_photo WHERE Student_ID = :student_id";
                    $stmt = $pdo->prepare($update_photo_query);
                    $stmt->bindParam(':profile_photo', $new_filename);
                    $stmt->bindParam(':student_id', $student_id);
                    $stmt->execute();

                    echo "Profile photo updated to: " . $new_filename . "<br>"; // Debugging statement
                } else {
                    echo "Error uploading the profile picture.<br>";
                }
            } else {
                echo "Invalid file type for the profile picture. Allowed types: jpg, jpeg, png, gif.<br>";
            }
        }
    }

    // Update the bio field
    $update_bio_query = "UPDATE students SET Bio = :bio WHERE Student_ID = :student_id";
    $stmt = $pdo->prepare($update_bio_query);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':student_id', $student_id);

    // Execute the bio update and check for success
    if ($stmt->execute()) {
        echo "Bio updated successfully.<br>"; // Debugging statement
    } else {
        echo "Error updating bio. Statement Error Info: " . json_encode($stmt->errorInfo()) . "<br>"; // Debugging statement
    }
}
?>
