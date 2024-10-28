<?php
include 'Connection.php';
session_start();

header('Content-Type: application/json'); // JSON response format

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Retrieve form data
        $firstName = $_POST['first_name'];
        $middleName = $_POST['middle_name'];
        $lastName = $_POST['last_name'];
        $contactInfo = $_POST['contact_info'];
        $gender = $_POST['gender'];
        $departmentId = $_POST['department'];
        $profilePicturePath = '';

        // Handle profile picture upload
        if (!empty($_FILES['profile_photo']['name'])) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES["profile_photo"]["name"]);
            
            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
                $profilePicturePath = $targetFile;
            } else {
                $response['error'] = 'Error uploading profile picture.';
                echo json_encode($response);
                exit;
            }
        }

        // Ensure instructor ID exists in the session
        if (isset($_SESSION['instructor_id'])) {
            $instructorID = $_SESSION['instructor_id'];
            var_dump($params, $_SESSION['instructor_id']);

            // Update query
            $query = "UPDATE instructors 
                      SET First_Name = :first_name, 
                          Middle_Name = :middle_name, 
                          Last_Name = :last_name, 
                          Contact_Info = :contact_info, 
                          Gender = :gender, 
                          Department_ID = :department_id";

            if ($profilePicturePath) {
                $query .= ", Profile_Picture = :profile_picture";
            }

            $query .= " WHERE Instructor_ID = :instructor_id";

            $stmt = $pdo->prepare($query);

            $params = [
                ':first_name' => $firstName,
                ':middle_name' => $middleName,
                ':last_name' => $lastName,
                ':contact_info' => $contactInfo,
                ':gender' => $gender,
                ':department_id' => $departmentId,
                ':instructor_id' => $instructorID
            ];

            if ($profilePicturePath) {
                $params[':profile_picture'] = $profilePicturePath;
            }

            // Execute update and handle results
            if ($stmt->execute($params)) {
                // Fetch the updated data to confirm the changes
                $response = [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'contact_info' => $contactInfo,
                    'gender' => $gender,
                    'department' => $departmentId,
                    'profile_photo' => $profilePicturePath
                ];
            } else {
                $response['error'] = 'Failed to execute update.';
            }
        } else {
            $response['error'] = 'Instructor ID not found in session.';
        }

    } catch (PDOException $e) {
        $response['error'] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $response['error'] = 'General error: ' . $e->getMessage();
    }

    echo json_encode($response);
}
