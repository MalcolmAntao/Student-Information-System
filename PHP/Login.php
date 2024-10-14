<?php
session_start();
include 'Connection.php'; // Include your PDO connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try 
    {
        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare("SELECT role, password FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() > 0) 
        {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $role = $user['role'];
            $hashed_password = $user['password'];

            // Verify password
            if (password_verify($password, $hashed_password)) 
            {
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                // Redirect user based on role
                switch ($role) 
                {
                    case 'student':
                        //print("Connection Successful");
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
            } else 
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
