<?php
// Start the session to manage user login information
session_start();
require 'connection.php'; // Include database connection file

// Check if the user is logged in--------------->>need to confirm this
if (!isset($_SESSION['User_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details--------------->>need to confirm this
$User_id = $_SESSION['User_id'];

try {
    // Prepare the SQL statement to fetch user details
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = :id");
    $stmt->bindParam(':id', $User_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch tasks
    $task_stmt = $pdo->prepare("SELECT title, status FROM tasks WHERE User_id = :User_id");
    $task_stmt->bindParam(':User_id', $User_id, PDO::PARAM_INT);
    $task_stmt->execute();
    $tasks = $task_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS file -->
</head>

<style>
    /* Apply box-sizing globally */
    * {
        box-sizing: border-box;
    }

    /* Root variables for dark and light modes */
    :root {
        --bg-color-dark: #121212;
        --text-color-dark: #ffffff;
        --bg-color-light: #ffffff;
        --text-color-light: #000000;
        --sidebar-bg-color-dark: #1e1e1e;
        --sidebar-bg-color-light: #d1d1d1;

        --light: #f6f6f9;
        --primary: #1976D2;
        --light-primary: #CFE8FF;
        --grey: #eee;
        --dark-grey: #AAAAAA;
        --dark: #363949;
        --danger: #D32F2F;
        --light-danger: #FECDD3;
        --warning: #FBC02D;
        --light-warning: #FFF2C6;
        --success: #388E3C;
        --light-success: #BBF7D0;
    }

    /* General styling for body */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #262626;
        color: var(--text-color-dark);
        overflow-x: hidden; /* Prevent horizontal scrolling */
        transition: background-color 0.3s, color 0.3s;
    }

    body.light-mode {
        background-color: var(--bg-color-light);
        color: var(--text-color-light);
    }

    /* Main container layout */
    .container {
        display: flex;
        height: 100vh;
        overflow-x: hidden; /* Prevent horizontal scrolling in the container */
    }

    /* Sidebar styling */
    .sidebar {
        width: 45px;
        height: 100%;
        background-color: var(--sidebar-bg-color-dark);
        position: relative;
        transition: width 0.3s ease, background-color 0.3s ease;
        overflow: hidden;
    }

    body.light-mode .sidebar {
        background-color: var(--sidebar-bg-color-light);
    }

    .sidebar:hover {
        width: 200px;
        /* Expands on hover */
    }

    /* Sidebar hamburger icon */
    .sidebar-icon-container {
        display: flex;
        justify-content: flex-end;
        padding: 10px;
        position: relative;
    }

    .sidebar-icon {
        width: 25px;
        height: 3px;
        background-color: #fff;
        margin: 0;
        transition: 0.4s;
    }

    .sidebar-content {
        padding: 20px;
        opacity: 0;
        transform: translateX(-20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
        transition-delay: 0s;
    }

    .sidebar:hover .sidebar-content {
        opacity: 1;
        transform: translateX(0);
        transition-delay: 0.3s;
    }

    .sidebar p {
        margin: 15px 0;
    }

    /* Sidebar links styling */
    .sidebar-items p {
        color: #fff;
        transition: color 0.3s;
    }

    body.light-mode .sidebar-items p {
        color: #000;
    }

    .sidebar-items p:hover {
        color: #c0c0c0;
    }

    body.light-mode .sidebar-items p:hover {
        color: #555;
    }

    .sidebar-links a {
        display: block;
        padding: 10px 0;
        color: #fff;
        /* Default dark mode text color */
        text-decoration: none;
        transition: color 0.3s ease;
    }

    body.light-mode .sidebar-links a {
        color: #c0c0c0;
        /* Darker color for light mode */
    }

    .sidebar-links a:hover {
        color: #2F9DFF;
        /* Hover effect for dark mode */
    }

    body.light-mode .sidebar-links a:hover {
        color: #4f8585;
        /* Hover effect for light mode */
    }


    /* Main Content */
    /* Main content styling */
    .main-content {
        width: calc(100% - 45px); /* Account for the sidebar width */
        height: 90vh;
        flex: 4;
        background-color: #262626;
        padding: 30px;
        flex-shrink: 1; /* Allow shrinking */
    }

    body.light-mode .main-content {
        background-color: #f0f0f0;
    }

    /* Header */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .header input[type="text"] {
        width: 60%;
        padding: 10px;
        background-color: #333;
        border: none;
        color: white;
        border-radius: 50px;
    }

    .header-icons {
        display: flex;
        gap: 10px;
        /* Adds space between icons */
    }

    .header-icons button {
        background-color: #333;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 50px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    body.light-mode .header-icons button {
        background-color: #e0e0e0;
        color: #000;
    }

    body.light-mode .header input[type="text"],
    body.light-mode .header-icons button {
        background-color: #e0e0e0;
        color: #000;
    }

    /* Dark Mode Toggle Button Styling */
    .dark-mode-toggle {
        margin: 20px 0;
    }

    .dark-mode-toggle button {
        padding: 10px 20px;
        background-color: #333;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    body.light-mode .dark-mode-toggle button {
        background-color: #e0e0e0;
        color: #000;
    }

    .updates-block {
        background-color: #2e2e2e;
        padding: 30px;
        border-radius: 10px;
    }

    body.light-mode .updates-block {
        background-color: #e0e0e0;
        color: #000;
    }

    /* Updates block styling */
    .updates-block .task-list {
        width: 100%; /* Full width of the updates block */
        background: #333;
        padding: 10px 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        border-radius: 10px;
        margin: 0 auto;
    }

    .updates-block .task-list li {
        width: 100%;
        margin-bottom: 16px;
        color: #fff;
        padding: 10px 15px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #444;
    }

    .updates-block .task-list li.completed {
        border-left: 10px solid var(--success);
    }

    .updates-block .task-list li.not-completed {
        border-left: 10px solid var(--danger);
    }

    .updates-block .task-list li .task-title {
        margin-left: 6px;
        text-align: left;
    }

    /* Updates block styling for light mode */
    body.light-mode .updates-block {
        background-color: #e0e0e0;
        color: #000;
    }

    body.light-mode .updates-block .task-list {
        background: #e0e0e0;
    }

    body.light-mode .updates-block .task-list li {
        background-color: #d0d0d0;
        color: #000;
    }

    .updates-block {
        position: relative;
    }

    /* Bottom Buttons */
    .bottom-buttons {
        display: flex;
        justify-content: space-evenly;
        margin-top: 20px;
    }

    .bottom-buttons .btn {
        padding: 10px 20px;
        background-color: #333;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        color: #fff;
    }

    .bottom-buttons .btn:hover {
        background-color: #555;
    }

    body.light-mode .bottom-buttons .btn {
        background-color: #e0e0e0;
        color: #000;
    }

    
    /* Sidebar links styling */
    .sidebar-items p {
        color: #fff;
        transition: color 0.3s;
    }

    body.light-mode .sidebar-items p {
        color: #000;
    }

    .sidebar-items p:hover {
        color: #c0c0c0;
    }

    body.light-mode .sidebar-items p:hover {
        color: #555;
    }

    .sidebar-links a {
        display: block;
        padding: 10px 0;
        color: #fff;
        /* Default dark mode text color */
        text-decoration: none;
        transition: color 0.3s ease;
    }

    body.light-mode .sidebar-links a {
        color: #c0c0c0;
        /* Darker color for light mode */
    }

    .sidebar-links a:hover {
        color: #2F9DFF;
        /* Hover effect for dark mode */
    }

    body.light-mode .sidebar-links a:hover {
        color: #4f8585;
        /* Hover effect for light mode */
    }
</style>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-icon-container">
                <div class="hamburger-icon">
                    <img src="/Assests/menu(darkmode).svg" alt="Menu Icon" />
                </div>
            </div>

            <div class="sidebar-content">
                <p>Welcome back, <?php echo htmlspecialchars($user['name']); ?></p>
                <div class="sidebar-items">
                    <div class="sidebar-links">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="CourseAccess.php">Courses Access</a>
                        <a href="TeacherAccess.php">Teacher Data</a>
                        <a href="StudentAccess.php">Student Data</a>
                        <a href="UpdatesPage.php">Updates</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="main-content">
            <div class="header">
                <input type="text" placeholder="Page Search">
                <div class="header-icons">
                    <button><img src="/Assests/reminder(dark_mode).svg" alt="Reminder Icon" /></button>
                    <button>Game icon</button>
                    <button><img src="/Assests/profile(dark_mode).svg" alt="Profile Icon" /></button>
                </div>
            </div>

            <div class="dark-mode-toggle">
                <button id="darkModeToggle" onclick="toggleDarkMode()"><img src="/Assests/lightmode.svg" /></button>
            </div>

            <!-- Updates Block -->
            <div class="updates-block">
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="<?php echo $task['status'] === 'completed' ? 'completed' : 'not-completed'; ?>">
                            <div class="task-title">
                                <p><?php echo htmlspecialchars($task['title']); ?></p>
                            </div>
                            <i class='bx bx-dots-vertical-rounded'></i>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Bottom Buttons -->
            <div class="bottom-buttons">
                <a href="NewEntry(T).php">
                    <button class="btn">Add New Teacher</button>
                </a>
                <a href="NewEntry(S).php">
                    <button class="btn">Add New Student</button>
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleDarkMode() {
            const body = document.body;
            const toggleButton = document.getElementById('darkModeToggle');
            const reminderIcon = document.querySelector('.header-icons button img[alt="Reminder Icon"]');
            const profileIcon = document.querySelector('.header-icons button img[alt="Profile Icon"]');
            const menuIcon = document.querySelector('.hamburger-icon img'); 
            const addIcon = document.getElementById('addIcon');

            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                toggleButton.querySelector('img').src = '/Assests/darkmode.svg';
                reminderIcon.src = '/Assests/reminder(light_mode).svg';
                profileIcon.src = '/Assests/profile(light_mode).svg';
                addIcon.src = '/Assests/add_more(lightmode).svg';
                menuIcon.src = '/Assests/menu(lightmode).svg';
            } else {
                toggleButton.querySelector('img').src = '/Assests/lightmode.svg';
                reminderIcon.src = '/Assests/reminder(dark_mode).svg';
                profileIcon.src = '/Assests/profile(dark_mode).svg';
                addIcon.src = '/Assests/add_more(darkmode).svg';
                menuIcon.src = '/Assests/menu(darkmode).svg';
            }
        }
    </script>
</body>

</html>
