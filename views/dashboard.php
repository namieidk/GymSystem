<?php
// Start the session
session_start();

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - He-Man Fitness Gym</title>
    <!-- Add Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Add Google Fonts for Anton -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
        }

        .admin-header {
            background-color: rgba(68, 68, 68, 1);
            padding: 15px 40px;
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.25);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 20;
        }

        .menu-icon {
            font-size: 20px;
            color: #fff;
            margin-right: 15px;
            cursor: pointer;
        }

        .admin-title {
            font-family: 'Anton', sans-serif;
            font-size: 24px;
            color: #fff;
            font-weight: 400;
            margin: 0;
        }

        .admin-profile {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .admin-name-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .admin-name {
            color: #fff;
            font-family: Inter, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: 500;
            margin: 0;
        }

        .admin-subtitle {
            color: #ccc;
            font-family: Inter, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: 400;
            margin: 0;
        }

        .settings-container {
            position: relative;
            cursor: pointer;
        }

        .settings-icon {
            color: #fff;
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .settings-icon:hover {
            transform: rotate(90deg);
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 30px;
            background-color: rgba(44, 44, 44, 1);
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: none;
            transform-origin: top right;
            transform: scaleY(0);
            transition: transform 0.3s ease;
            min-width: 120px;
        }

        .settings-container.active .dropdown-menu {
            display: block;
            transform: scaleY(1);
        }

        .dropdown-item {
            padding: 8px 15px;
            color: #fff;
            text-decoration: none;
            display: block;
            font-family: Inter, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            transition: background-color 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: rgba(226, 29, 29, 1);
        }

        .dashboard-layout {
            background-color: rgba(49, 49, 49, 0.88);
            min-height: 100%;
            display: flex;
            flex-direction: column;
            padding-top: 50px;
        }

        .content-wrapper {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .sidebar-nav {
            background-color: rgba(44, 44, 44, 1);
            width: 250px;
            padding: 20px 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 10;
            position: fixed;
            height: 100%;
            top: 0;
            left: -250px;
            transition: left 0.3s ease;
        }

        .sidebar-nav.active {
            left: 0;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px 0;
        }

        .sidebar-logo {
            width: 30px;
            margin-left: 10px;
            display: inline-block;
        }

        .sidebar-content {
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .gym-title {
            color: #fff;
            font-size: 24px;
            font-family: 'Anton', sans-serif;
            font-weight: 400;
            margin: 0;
            text-align: center;
            display: inline-block;
        }

        .nav-menu {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 100%;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .nav-item {
            border-radius: 20px;
            background-color: rgba(226, 29, 29, 1);
            padding: 12px 15px;
            color: #fff;
            text-decoration: none;
            font-family: Inter, -apple-system, Roboto, Helvetica, sans-serif;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            box-sizing: border-box;
            text-align: left;
            cursor: pointer;
        }

        .nav-item--active {
            background-color: rgba(255, 50, 50, 1);
        }

        .nav-item--bordered {
            border: 2px solid rgba(226, 29, 29, 1);
        }

        .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            color: #fff;
            font-family: Inter, -apple-system, Roboto, Helvetica, sans-serif;
            background-color: rgba(49, 49, 49, 0.88);
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        .main-footer {
            background-color: #000;
            padding: 20px 40px;
            width: 100%;
            box-sizing: border-box;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1;
            min-width: 200px;
        }

        .footer-title {
            font-family: Poppins, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 20px;
            color: #fff;
            font-weight: 800;
            margin: 0;
        }

        .footer-subtitle {
            font-family: Poppins, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 16px;
            color: #fff;
            font-weight: 400;
            margin: 5px 0 0;
        }

        .footer-grid {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 10px;
        }

        .footer-column {
            width: auto;
        }

        .footer-column--small {
            width: auto;
        }

        .footer-row {
            display: flex;
            gap: 20px;
            justify-content: flex-end;
        }

        .platform-title,
        .contact-title,
        .location-title {
            color: #fff;
            font-size: 16px;
            font-family: Poppins, -apple-system, Roboto, Helvetica, sans-serif;
            font-weight: 700;
            margin: 0;
            text-align: center;
        }

        .platform-list {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            justify-content: center;
        }

        .platform-icon {
            width: 16px;
            height: 16px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .contact-icon {
            width: 16px;
            margin-top: 10px;
        }

        .phone-number {
            font-family: Poppins, -apple-system, Roboto, Helvetica, sans-serif;
            font-size: 16px;
            color: #fff;
            font-weight: 400;
            margin: 10px 0 0;
        }

        .location-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .location-icon {
            width: 16px;
            margin-top: 10px;
        }

        @media (max-width: 991px) {
            .admin-header {
                padding: 15px 20px;
            }

            .dashboard-layout {
                padding-top: 50px;
            }

            .sidebar-nav {
                width: 100%;
                left: -100%;
            }

            .sidebar-nav.active {
                left: 0;
            }

            .main-content {
                padding: 20px;
                margin-left: 0;
            }

            .main-content.shifted {
                margin-left: 0;
            }

            .sidebar-content {
                padding: 20px;
            }

            .nav-menu {
                margin-top: 30px;
                gap: 15px;
                padding: 0;
            }

            .nav-item {
                padding: 10px 15px;
                font-size: 16px;
            }

            .main-footer {
                padding: 15px 20px;
            }

            .footer-content {
                flex-direction: column;
                justify-content: flex-start;
                align-items: flex-start;
            }

            .footer-grid {
                justify-content: flex-start;
                width: 100%;
            }

            .footer-row {
                justify-content: flex-start;
                width: 100%;
            }

            .footer-column,
            .footer-column--small {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <i class="fas fa-bars menu-icon" id="menuToggle"></i>
        <h1 class="admin-title">He-Man Fitness</h1>
        <div class="admin-profile">
            <div class="admin-name-container">
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="admin-subtitle">Admin</span>
            </div>
            <div class="settings-container" id="settingsToggle">
                <i class="fas fa-cog settings-icon"></i>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">Settings</a>
                    <a href="logout.php" class="dropdown-item" id="logoutLink">Log Out</a>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-layout">
        <div class="content-wrapper">
            <nav class="sidebar-nav" id="sidebar">
                <div class="sidebar-header">
                    <h2 class="gym-title">He-Man Fitness Gym</h2>
                    <img
                        src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/487c8045bbe2fa69683c59be11df183c65f6fc7aac898ce424235e21c4b67556?placeholderIfAbsent=true"
                        alt="Dashboard Logo"
                        class="sidebar-logo"
                    />
                </div>

                <div class="sidebar-content">
                    <div class="nav-menu">
                        <a href="#" class="nav-item nav-item--active">
                            <i class="fas fa-home nav-icon"></i>
                            <span>Home</span>
                        </a>
                        <a href="ListMember.php" class="nav-item">
                            <i class="fas fa-user-plus nav-icon"></i>
                            <span>Add Member</span>
                        </a>
                        <a href="attendance.php" class="nav-item">
                            <i class="fas fa-calendar-check nav-icon"></i>
                            <span>Attendance</span>
                        </a>
                        <a href="Logs.php" class="nav-item">
                            <i class="fas fa-file-alt nav-icon"></i>
                            <span>Logs</span>
                        </a>
                        <a href="membership.php" class="nav-item nav-item--bordered">
                            <i class="fas fa-id-card nav-icon"></i>
                            <span>Membership</span>
                        </a>
                    </div>
                </div>
            </nav>

            <main class="main-content" id="mainContent">
                <h2>Get Fit</h2>
                <p>This is where your dashboard content will go, aligned with the sidebar. Use the sidebar to add new members and manage gym operations.</p>
            </main>
        </div>

        <footer class="main-footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">Contacts</h3>
                    <p class="footer-subtitle">Suggestions and Recommendations</p>
                </div>

                <div class="footer-grid">
                    <div class="footer-column">
                        <div class="footer-row">
                            <div class="social-platforms">
                                <h4 class="platform-title">Platforms</h4>
                                <div class="platform-list">
                                    <a href="#" class="platform-link">
                                        <img
                                            src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/689d025ff0d37035466667e2407e062f4fff7333de7dcd1cc30cca3a5c5fdca9?placeholderIfAbsent=true"
                                            alt="Social Platform 1"
                                            class="platform-icon"
                                        />
                                    </a>
                                    <a href="#" class="platform-link">
                                        <img
                                            src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/2467cceb7323f73c6c2ac1474c1c6072de91f30c4cd6083efc880f8ee1c30338?placeholderIfAbsent=true"
                                            alt="Social Platform 2"
                                            class="platform-icon"
                                        />
                                    </a>
                                    <a href="#" class="platform-link">
                                        <img
                                            src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/59a45bf25522ce036613580be1606720c2c14a6a517072297636ce996f539b0a?placeholderIfAbsent=true"
                                            alt="Social Platform 3"
                                            class="platform-icon"
                                        />
                                    </a>
                                    <a href="#" class="platform-link">
                                        <img
                                            src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/d11f06a17a91dde5580881397cfa9f2450cacccfb32e24aed67abb627449d9eb?placeholderIfAbsent=true"
                                            alt="Social Platform 4"
                                            class="platform-icon"
                                        />
                                    </a>
                                </div>
                            </div>

                            <div class="contact-info">
                                <h4 class="contact-title">Phone</h4>
                                <img
                                    src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/5f8765b0432bbea85b15de0e9e707266c0300402914e43bbfb1ca2a2dd777db5?placeholderIfAbsent=true"
                                    alt="Phone Icon"
                                    class="contact-icon"
                                />
                                <p class="phone-number">415-555-0132</p>
                            </div>
                        </div>
                    </div>

                    <div class="footer-column footer-column--small">
                        <div class="location-info">
                            <h4 class="location-title">Location</h4>
                            <img
                                src="https://cdn.builder.io/api/v1/image/assets/d47b9c64343c4fb28708dd8b67fd1cce/48eec7d80666a75778b595b5f0f0429a13496cc72b4d4cd21ee2b88e86bc2447?placeholderIfAbsent=true"
                                alt="Location Icon"
                                class="location-icon"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Toggle sidebar functionality
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        // Toggle settings dropdown
        document.getElementById('settingsToggle').addEventListener('click', function() {
            this.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const settingsContainer = document.getElementById('settingsToggle');
            if (!settingsContainer.contains(event.target)) {
                settingsContainer.classList.remove('active');
            }
        });
    </script>
</body>
</html>