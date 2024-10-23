<?php
include 'Connection.php'; // Include database connection
include 'Session.php'; // Include session management

// Fetch all notices from the database
$sql = "SELECT a.Announcement_ID, a.Title, a.Posting_Date, i.First_Name, i.Last_Name 
        FROM Announcements a
        JOIN Instructors i ON a.Author_ID = i.Instructor_ID
        ORDER BY a.Posting_Date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if a specific notice ID is provided via the URL
$noticeID = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Fetch the specific notice if notice ID is passed
$noticeDetail = null;
if ($noticeID) {
    $sql = "SELECT a.Title, a.Content, a.Posting_Date, i.First_Name, i.Last_Name
            FROM Announcements a
            JOIN Instructors i ON a.Author_ID = i.Instructor_ID
            WHERE a.Announcement_ID = :notice_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['notice_id' => $noticeID]);
    $noticeDetail = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Assets/icon.ico" type="image/x-icon">
    <link rel="icon" href="../Assets/icon.png" type="image/png">
    <link rel="stylesheet" href="../CSS/Preloader.css">
    <title>Notices</title>
    <style>
        :root {
            --bg-color-dark: #222327;
            --bg-color-light: #d9e8e8;

            --text-color-dark: #ffffff;
            --text-color-light: #000000;

            --sidebar-bg-color-dark: #232B3A;
            --sidebar-bg-color-light: #253b42;

            --icons-color-dark: #ffffff;
            --icons-color-light: #ffffff;

            --icons-color-active-dark: #000000;
            --icons-color-active-light: #000000;

            --table-header-dark: #4a4a4a;
            --table-header-light: #dddddd;
            --card-bg-dark: #2d2d2d;
            --card-bg-light: #ffffff;
            --card-hover-bg-dark: #3a3a3a;
            --card-hover-bg-light: #f1f1f1;
            --shadow-dark: rgba(0, 0, 0, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--bg-color-dark);
            color: var(--text-color-dark);
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;

            opacity: 0;
            /* Start with full transparency */
            transition: opacity 1.5s ease-in;
            /* Adjust duration as needed */
        }

        body.loaded {
            opacity: 1;
            /* Fully visible */
        }


        body.light-mode {
            background-color: var(--bg-color-light);
            color: var(--text-color-light);
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            padding-top: 0px;
            padding-left: 40px;
            overflow-y: auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: absolute;
            top: 10px;
            right: 20px;
            padding: 10px;
            background: var(--sidebar-bg-color-dark);
            border: none;
            border-radius: 10px;
            color: var(--text-color-dark);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        body.light-mode .toggle-switch {
            background: var(--sidebar-bg-color-light);
            color: var(--text-color-dark);
        }

        /* Notice Cards */
        /* Notice Cards */
        .notice-detail,
        .notice-card {
            background-color: var(--card-bg-dark);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px var(--shadow-dark);
            margin-bottom: 20px;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;

        }

        /* New Classes for Fading Effects */
        .notice-card {
            transform: scale(0.9);
            transition: 0.3 ease-in-out;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .notice-card.fade-in {
            opacity: 1;
            transform: translateY(0);
        }

        .notice-card.fade-out {
            opacity: 0;
            transform: translateY(-20px);
        }




        body.light-mode .notice-detail,
        body.light-mode .notice-card {
            background-color: var(--card-bg-light);
            box-shadow: 0 4px 8px var(--shadow-light);
        }

        .notice-card:hover {
            background-color: var(--card-hover-bg-dark);
            box-shadow: 0 6px 12px var(--shadow-dark);
        }

        body.light-mode .notice-card:hover {
            background-color: var(--card-hover-bg-light);
            box-shadow: 0 6px 12px var(--shadow-light);
        }

        .notice-card h3 {
            margin: 0;
            color: #29fd53;
        }

        .notice-card p {
            margin: 0;
            color: var(--text-color-dark);
        }

        body.light-mode .notice-card p {
            color: var(--text-color-light);
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        table th {
            background-color: var(--sidebar-bg-color-dark);
            color: var(--text-color-dark);
        }

        body.light-mode table th {
            background-color: var(--sidebar-bg-color-light);
            color: var(--text-color-light);
        }

        .navigation {
            position: relative;
            width: 100px;
            height: 100vh;
            background: var(--sidebar-bg-color-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            border-top-right-radius: 18px;
            border-bottom-right-radius: 18px;

        }

        .navigation ul {
            display: flex;
            flex-direction: column;
            width: 68px;

        }

        .navigation ul li {
            position: relative;
            list-style: none;
            width: 70px;
            height: 70px;
            z-index: 1;
        }

        .navigation ul li a {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            text-align: center;
            font-weight: 500;
        }

        .navigation ul li a .icons {
            position: relative;
            display: block;
            right: 20px;
            line-height: 68px;
            font-size: 1.7em;
            text-align: center;
            transition: 0.3s;
            color: var(--icons-color-dark);
        }

        .navigation ul li.active a .icons {
            color: var(--icons-color-active-dark);
            transform: translateX(48px);
        }

        .navigation ul li a .text {
            position: absolute;
            color: var(--text-color-dark);
            font-weight: 75em;
            right: 30px;
            font-size: 1em;
            letter-spacing: 0.05em;
            transition: 0.3s;
            opacity: 0;
            transform: translateX(-20px);
        }

        .navigation ul li.active a .text {
            opacity: 1;
            transform: translateX(-10px);
        }

        .indicator {
            position: absolute;
            left: 68%;
            width: 50px;
            height: 48px;
            background: #29fd53;
            border-radius: 50%;
            border: 6px solid var(--bg-color-dark);
            transition: 0.3s ease;
        }

        /* bottom shadow */
        .indicator::before {
            content: '';
            position: absolute;
            left: 13%;
            /*left right allignment */
            bottom: -45%;
            /*top bottom allignment */
            width: 20px;
            height: 20px;
            background: transparent;
            border-top-right-radius: 20px;
            box-shadow: 10px -1px 0 0 var(--bg-color-dark);
            /* box-shadow: 10px -1px 0 0 white; */

            transition: 0.3s ease;
        }

        /* top shadow */
        .indicator::after {
            content: '';
            position: absolute;
            left: 13%;
            top: -21px;
            width: 20px;
            height: 20px;
            background: transparent;
            border-bottom-right-radius: 20px;
            /* box-shadow: 10px 1px 0 0 white; */
            box-shadow: 10px 1px 0 0 var(--bg-color-dark);

            transition: 0.3s ease;
        }

        .navigation ul li:hover a .text {
            opacity: 1;
            transform: translateX(-10px);
        }

        .navigation ul li:hover a .icons {
            transform: translateX(48px);
        }

        .navigation ul li:nth-child(1).active~.indicator {
            transform: translateY(calc(70px * 0));
        }

        .navigation ul li:nth-child(2).active~.indicator {
            transform: translateY(calc(70px * 1));
        }

        .navigation ul li:nth-child(3).active~.indicator {
            transform: translateY(calc(70px * 2));
        }

        .navigation ul li:nth-child(4).active~.indicator {
            transform: translateY(calc(70px * 3));
        }

        body.light-mode .navigation {
            background: var(--sidebar-bg-color-light);
        }

        body.light-mode .navigation ul li a .icons {
            color: var(--icons-color-light);
        }

        body.light-mode .navigation ul li.active a .icons {
            color: var(--icons-color-active-light);
            ;
        }

        body.light-mode .navigation ul li a .text {
            color: var(--text-color-dark);
        }

        body.light-mode .indicator {
            border: 6px solid var(--bg-color-light);
        }

        /* bottom shadow */
        body.light-mode .indicator::before {

            box-shadow: 10px -1px 0 0 var(--bg-color-light);
        }

        /* top shadow */
        body.light-mode .indicator::after {

            box-shadow: 10px 1px 0 0 var(--bg-color-light);
        }

        .scrollable {
            max-height: 50vh;
            overflow-y: auto;
        }

        /* Scrollbar Styling for .main-content */
        .main-content::-webkit-scrollbar {
            width: 0px;
        }

        .main-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .main-content::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .main-content::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode .main-content::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode .main-content::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }

        /* Scrollbar Styling for .scrollable */
        .scrollable::-webkit-scrollbar {
            width: 10px;
        }

        .scrollable::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollable::-webkit-scrollbar-thumb {
            background-color: #555555;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .scrollable::-webkit-scrollbar-thumb:hover {
            background-color: #3c5a99;
        }

        body.dark-mode .scrollable::-webkit-scrollbar-thumb {
            background-color: #888888;
        }

        body.dark-mode .scrollable::-webkit-scrollbar-thumb:hover {
            background-color: #aaa;
        }
    </style>
</head>

<body>
    <div id="preloader">
        <img src="../Assets/Game.svg" alt="Loading..." class="preloader-image" />
        <!-- <div class="spinner"></div> -->
    </div>
    <div class="container">
        <!-- Sidebar -->
        <div class="navigation">
            <ul>
                <li class="list ">
                    <a href="../PHP/StudentLanding.php">
                        <span class="icons"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="text">Home</span>
                    </a>
                </li>
                <li class="list ">
                    <a href="../PHP/StudentProfile.php">
                        <span class="icons"><ion-icon name="person-outline"></ion-icon></span>
                        <span class="text">Profile</span>
                    </a>
                </li>
                <li class="list active">
                    <a href="../PHP/Announcements.php">
                        <span class="icons"><ion-icon name="notifications-outline"></ion-icon></span>
                        <span class="text">Notice</span>
                    </a>
                </li>
                <li class="list">
                    <a href="../PHP/Logout.php">
                        <span class="icons"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="text">Logout</span>
                    </a>
                </li>
                <div class="indicator"></div>
            </ul>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <header>
                <h1>Notices</h1>
                <button class="toggle-switch" id="toggle-mode">Toggle Theme</button>
            </header>
            <!-- Notice Detail Card -->
            <div id="notice-details" class="notice-detail">
                <?php if ($noticeDetail): ?>
                    <h2><?= htmlspecialchars($noticeDetail['Title']); ?></h2>
                    <p><strong>Posted by:</strong> <?= htmlspecialchars($noticeDetail['First_Name'] . ' ' . $noticeDetail['Last_Name']); ?></p>
                    <p><strong>Posted on:</strong> <?= date('F j, Y', strtotime($noticeDetail['Posting_Date'])); ?></p>
                    <p><?= nl2br(htmlspecialchars($noticeDetail['Content'])); ?></p>
                <?php endif; ?>
            </div>

            <h2>All Notices</h2>
            <div class="scrollable">
                <!-- Notice List Cards -->
                <div class="notices-wrapper">
                    <?php foreach ($notices as $notice): ?>
                        <div class="notice-card" onclick="loadNoticeDetails(<?= $notice['Announcement_ID']; ?>)">
                            <h3><?= htmlspecialchars($notice['Title']); ?></h3>
                            <p><strong>Posted on:</strong> <?= date('F j, Y', strtotime($notice['Posting_Date'])); ?></p>
                            <p><strong>Teacher:</strong> <?= htmlspecialchars($notice['First_Name'] . ' ' . $notice['Last_Name']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../JS/Preloader.js"></script>
    <!-- card fadeaway script -->
    <script>
        // Function to handle Intersection Observer events
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    entry.target.classList.remove('fade-out');
                } else {
                    entry.target.classList.add('fade-out');
                    entry.target.classList.remove('fade-in');
                }
            });
        }, {
            threshold: 0.5 // Adjust based on when you want to trigger the effect (0.1 means 10% of the card should be visible)
        });

        // Observe each notice card
        const cards = document.querySelectorAll('.notice-card');
        cards.forEach(card => observer.observe(card));
    </script>

    <!-- navbar script -->
    <script>
        const list = document.querySelectorAll('.list');
        let currentActiveItem = document.querySelector('.list.active');

        function activeLink() {
            list.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
            currentActiveItem = this;
        }

        function hoverLink() {
            list.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
        }

        function leaveLink() {
            list.forEach((item) => item.classList.remove('active'));
            if (currentActiveItem) {
                currentActiveItem.classList.add('active');
            }
        }

        list.forEach((item) => {
            item.addEventListener('click', activeLink);
            item.addEventListener('mouseenter', hoverLink);
            item.addEventListener('mouseleave', leaveLink);
        });
    </script>
    <!-- icons link -->
    <script
        type="module"
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script
        nomodule
        src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <!-- mode and page load toggle -->
    <script>
        // Add this in your script to trigger the loaded class on page load
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
        });
        document.getElementById('toggle-mode').addEventListener('click', function() {
            document.body.classList.toggle('light-mode');
            localStorage.setItem('mode', document.body.classList.contains('light-mode') ? 'light' : 'dark');
        });

        window.onload = function() {
            const mode = localStorage.getItem('mode');
            if (mode === 'light') {
                document.body.classList.add('light-mode');
            }
        };

        function loadNoticeDetails(noticeID) {
            window.location.href = 'Announcements.php?id=' + noticeID;
        }
    </script>
</body>

</html>