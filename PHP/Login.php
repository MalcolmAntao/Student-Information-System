<?php
session_start();
include 'Connection.php'; // Include your PDO connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try 
    {
        // Modify the query to also select the id
        $stmt = $pdo->prepare("SELECT Associated_ID, role, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() > 0) 
        {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $user['Associated_ID'];  // Fetch the user ID
            $role = $user['role'];
            $hashed_password = $user['password'];

            // Verify password
            if (password_verify($password, $hashed_password)) 
            {
                // Store email, role, and student_id (or user_id) in the session
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['student_id'] = $user_id;  // Assuming student_id is stored as 'id'

                // Redirect user based on role
                switch ($role) 
                {
                    case 'student':
                        header("Location: StudentLanding.php");
                        break;
                    case 'teacher':
                        header("Location: teacher_homepage.php");
                        break;
                    case 'admin':
                        header("Location: admin_homepage.php");
                        break;
                }
                exit();
            } 
            else 
            {
                echo "Invalid password.";
            }
        } 
        else 
        {
            echo "No account found with that email.";
        }
    } 
    catch (PDOException $e) 
    {
        echo "Error: " . $e->getMessage();
    }
}
?>
