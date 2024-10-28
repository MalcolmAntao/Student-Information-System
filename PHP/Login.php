<?php
session_start();
include 'Connection.php'; // Include your PDO connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Query to retrieve user data based on email
        $stmt = $pdo->prepare("
            SELECT u.User_ID, u.Password, r.Role_Name 
            FROM users u
            JOIN roles r ON u.Role_ID = r.Role_ID
            WHERE u.Email = :email
        ");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $user['User_ID'];
            $role = $user['Role_Name'];
            $hashed_password = $user['Password'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                // Store email, role, and User_ID in the session
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['user_id'] = $user_id;

                // Determine whether the user is a student or instructor
                $role_stmt = $pdo->prepare("
                    SELECT Student_ID, Instructor_ID
                    FROM role_associations
                    WHERE User_ID = :user_id
                ");
                $role_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $role_stmt->execute();
                $role_info = $role_stmt->fetch(PDO::FETCH_ASSOC);

                // Set session variables based on role association
                if ($role_info['Student_ID']) {
                    $_SESSION['student_id'] = $role_info['Student_ID'];
                } elseif ($role_info['Instructor_ID']) {
                    $_SESSION['instructor_id'] = $role_info['Instructor_ID'];
                }

                // Redirect user based on role
                switch ($role) {
                    case 'Student':
                        header("Location: StudentLanding.php");
                        break;
                    case 'Teacher':
                        header("Location: teacher_homepage.php");
                        break;
                    case 'Admin':
                        header("Location: NewAdminDashboard.php");
                        break;
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No account found with that email.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
