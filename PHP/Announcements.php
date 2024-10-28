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
            --bg-color-dark: #120E0E;
            --bg-color-light: #d9e8e8;

            --text-color-dark: #ffffff;
            --text-color-light: #000000;

            --sidebar-bg-color-dark: linear-gradient(130deg,
                    hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);
            --sidebar-bg-color-light:linear-gradient(130deg,
            #83a4d4, #b6fbff);

            --icons-color-dark: #ffffff;
            --icons-color-light: #000000;

            --icons-color-active-dark: #000000;
            --icons-color-active-light: #ffffff;

            --table-header-dark: #4a4a4a;
            --table-header-light: #dddddd;

            --card-bg-dark: var(--sidebar-bg-color-dark);
            --card-bg-light: var(--sidebar-bg-color-light);

            --card-color-dark: linear-gradient(130deg,
                    hsl(0deg 0% 7%) 0%,
                    hsl(0deg 0% 8%) 24%,
                    hsl(0deg 0% 9%) 35%,
                    hsl(0deg 1% 10%) 44%,
                    hsl(0deg 3% 12%) 64%,
                    hsl(0deg 4% 13%) 100%);

            --card-color-light: linear-gradient(130deg,
            #83a4d4, #b6fbff);

            --shadow-dark: rgba(0, 0, 0, 0.3);
            --shadow-light: rgba(0, 0, 0, 0.1);

            --icon-color-dark: #000000;
            --icon-color-light: #000000;

            --scrollbar-dark:transparent;
            --scrollbar-light:transparent;

            --scrollbar-dark-hover:transparent;
            --scrollbar-light-hover:transparent;

            --shadow-dark: 0 0 10px rgba(0, 0, 0, 0.2);;
            --shadow-light: 0 0 10px rgba(0, 0, 0, 0.1);;

            --course-card-dark-gradient: radial-gradient(at right bottom, #EFEA75, #02D12F);
            --course-card-light-gradient: radial-gradient(at left top, rgba(0, 118, 255, 1) 0%, rgba(32, 157, 230, 1) 35%, rgba(109, 203, 255, 1) 69%, rgba(179, 229, 255, 1) 99%);

            --course-card-dark: #232323;
            --course-card-light: #B5E9FF;

            --card-hover-bg-dark: #3a3a3a;
            --card-hover-bg-light: #C8E9FF;
            
            --course-card-dark-gradient-hover: radial-gradient(at right top, rgba(11, 181, 24, 1) 0%, rgba(152, 205, 120, 1) 28%, rgba(67, 216, 29, 1) 63%, rgba(219, 223, 147, 1) 93%);
            --course-card-light-gradient-hover: radial-gradient(at left top, rgba(20, 205, 230, 1) 0%, rgba(146, 203, 236, 1) 40%, rgba(214, 232, 243, 1) 77%, rgba(214, 232, 243, 1) 77%);

            --icon-gradient-bg-dark-hover: radial-gradient(at right bottom, #2980b9, #6dd5fa, #ffffff);
            --icon-gradient-bg-light-hover: #cccccc;

            --bold-text-dark: #08C922;
            --bold-text-light: #1D5FA5;
            --bold-text-light-small: #1D5FA5;

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
            /* transition: opacity 1.5s ease-in; */
            /* Adjust duration as needed */
        }

        /* Keyframes for gradient animation */
        @keyframes gradient-animation {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }


        .gradient-bg {
            background: var(--course-card-dark-gradient);
            ;
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-bg-hover {
            background: var(--course-card-dark-gradient-hover);

            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 10s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-sidebar {
            background: var(--sidebar-bg-color-dark);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        .gradient-card {
            background: var(--card-color-dark);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 50s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-bg-hover {
            background: var(--course-card-light-gradient-hover);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 10s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-sidebar {
            background: var(--sidebar-bg-color-light);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */
            animation: gradient-animation 15s ease infinite;
            /* Slow animation loop */
            transition: animation-duration 0.5s ease;
            /* Smooth transition to fast speed */
        }

        body.light-mode .gradient-bg {
            background: var(--icon-gradient-bg-dark-hover);
            background-size: 400% 400%;

        }

        body.light-mode .gradient-card {
            background: var(--card-color-light);
            background-size: 400% 400%;

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
            top: 30px;
            right: 40px;
            padding: 8px;
            /* background: var(--sidebar-bg-color-dark); */
            border: none;
            border-radius: 25%;
            width: 45px;
            color: var(--text-color-dark);
            cursor: pointer;
            box-shadow: var(--shadow-dark);
            transition: background-color 0.3s;
            transition: transform 0.5s ease;
        }

        .toggle-switch:hover{
            background: var(--icon-gradient-bg-dark-hover);
            background-size: 400% 400%;
            /* Enlarge background for smooth transitions */

            transition: 0.5s ease;
            transform: scale(1.2);
            /* Increase size by 20% on hover */
        }

        body.light-mode .toggle-switch {
            /* background: var(--sidebar-bg-color-light); */
            background-color: var(--icon-bg-light);
            color: #000000;

        }

        body.light-mode .toggle-switch:hover{
            background: var(--course-card-dark-gradient);
            background-size: 400% 400%;
        }

        .toggle-switch ion-icon {
            position: relative;
            top: 1px;
            left: 0.5px;
        }

        /* Notice Cards */
        /* Notice Cards */
        .notice-detail,
        .notice-card {
            /* background: var(--card-bg-dark); */
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px var(--shadow-dark);
            margin-bottom: 20px;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;

        }

        /* New Classes for Fading Effects */
        .notice-card {
            background: var(--course-card-dark);
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
            /* background: var(--card-bg-light); */
            box-shadow: 0 4px 8px var(--shadow-light);
        }


        .notice-card:hover {
            background: var(--card-hover-bg-dark);
            box-shadow: 0 6px 12px var(--shadow-dark);
        }

        body.light-mode .notice-card{
            background: var(--course-card-light);
        }

        body.light-mode .notice-card:hover {
            background-color: var(--card-hover-bg-light);
            box-shadow: 0 6px 12px var(--shadow-light);
        }

        .notice-card h3 {
            margin: 0;
            color: var(--bold-text-dark)
        }
        body.light-mode .notice-card h3{
            color: var(--bold-text-light);
        }
        header h1{
            color: var(--bold-text-dark);
        }
        body.light-mode header h1{
            color: var(--bold-text-light);
        }

        h2{
            color: var(--bold-text-dark);
        }
        body.light-mode h2{
            color: var(--bold-text-light);
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
            /* background: var(--sidebar-bg-color-dark); */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 95vh;
            margin-left: 20px;
            margin-bottom: 20px;
            margin-top: 20px;
            border-radius: 10px;

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
            /* background: #29fd53; */
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
            box-shadow: var(--shadow-light);
            /* background: var(--sidebar-bg-color-light); */
        }

        body.light-mode .navigation ul li a .icons {
            color: var(--icons-color-light);
        }

        body.light-mode .navigation ul li.active a .icons {
            color: var(--icons-color-active-light);

        }

        body.light-mode .navigation ul li a .text {
            color: var(--text-color-light);
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
            background-color: var(--scrollbar-dark);
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .scrollable::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-dark-hover);        }

        body.light-mode .scrollable::-webkit-scrollbar-thumb {
            background-color:var(--scrollbar-light);
        }

        body.light-mode .scrollable::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-light-hover);
        }

        .colored-icon {
            font-weight: 500;
            color: var(--icon-color-dark);
        }

        body.light-mode .colored-icon {
            color: var(--icon-color-light);
        }

        
    </style>
</head>

<body>
    <div id="preloader">
    <img src="../Assets/Loading.svg" alt="Loading..." class="preloader-image" />
        <!-- <div class="spinner"></div> -->
    </div>
    <div class="container">
        <!-- Sidebar -->
        <div class="navigation gradient-sidebar">
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
                <div class="indicator gradient-bg"></div>
            </ul>
        </div>

        <!-- Main content area -->
        <div class="main-content">
            <header>
                <h1>Notices</h1>

            </header>
            <button class="toggle-switch gradient-bg" id="toggle-mode">
                <span class="colored-icon"><ion-icon name="sunny-outline" style="width: 25px; height: 25px"></ion-icon></span>
            </button>
            <!-- Notice Detail Card -->
            <div id="notice-details" class="notice-detail gradient-bg-hover" style="color: #000000;">
                <?php if ($noticeDetail): ?>
                    <h2 style="color: #000000;"><?= htmlspecialchars($noticeDetail['Title']); ?></h2>
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
        // Toggle light/dark mode on button click
        document.getElementById('toggle-mode').addEventListener('click', function() {
            // Toggle the light mode class on the body
            document.body.classList.toggle('light-mode');

            // Update the local storage with the current mode
            const newMode = document.body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('mode', newMode);

            // Update the Ionicon based on the current mode
            const iconElement = this.querySelector('ion-icon'); // Get the Ionicon element
            if (newMode === 'light') {
                iconElement.setAttribute('name', 'sunny-outline'); // Change to the light mode icon
            } else {
                iconElement.setAttribute('name', 'moon-outline'); // Change to the dark mode icon
            }
        });

        // Optional: Set the initial state of the toggle button icon on page load
        window.addEventListener('load', function() {
            const iconElement = document.getElementById('toggle-mode').querySelector('ion-icon'); // Get the Ionicon element
            const currentMode = localStorage.getItem('mode') || 'dark'; // Default to dark mode
            if (currentMode === 'light') {
                document.body.classList.add('light-mode');
                iconElement.setAttribute('name', 'sunny-outline'); // Set the light mode icon
            } else {
                iconElement.setAttribute('name', 'moon-outline'); // Set the dark mode icon
            }
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