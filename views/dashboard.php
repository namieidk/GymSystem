<?php
// Start the session
session_start();

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['admin_name'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include '../database/database.php';

// Get total members
$total_members_query = "SELECT COUNT(*) as total FROM membership WHERE status = 'Active'";
$total_members_stmt = $conn->prepare($total_members_query);
$total_members_stmt->execute();
$total_members = $total_members_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total sales
$total_sales_query = "SELECT SUM(amount) as total FROM membership WHERE status = 'Active'";
$total_sales_stmt = $conn->prepare($total_sales_query);
$total_sales_stmt->execute();
$total_sales = $total_sales_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Get sales data for graph
$daily_sales_query = "SELECT DATE(start_date) as date, SUM(amount) as total 
                     FROM membership 
                     WHERE status = 'Active' 
                     AND start_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     GROUP BY DATE(start_date)";
$daily_sales_stmt = $conn->prepare($daily_sales_query);
$daily_sales_stmt->execute();
$daily_sales = $daily_sales_stmt->fetchAll(PDO::FETCH_ASSOC);

$weekly_sales_query = "SELECT WEEK(start_date) as week, SUM(amount) as total 
                      FROM membership 
                      WHERE status = 'Active' 
                      AND start_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                      GROUP BY WEEK(start_date)";
$weekly_sales_stmt = $conn->prepare($weekly_sales_query);
$weekly_sales_stmt->execute();
$weekly_sales = $weekly_sales_stmt->fetchAll(PDO::FETCH_ASSOC);

$yearly_sales_query = "SELECT YEAR(start_date) as year, SUM(amount) as total 
                      FROM membership 
                      WHERE status = 'Active'
                      GROUP BY YEAR(start_date)";
$yearly_sales_stmt = $conn->prepare($yearly_sales_query);
$yearly_sales_stmt->execute();
$yearly_sales = $yearly_sales_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - He-Man Fitness Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Original styles remain unchanged */
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

        /* New styles for cards and graph */
        .dashboard-cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: rgba(44, 44, 44, 1);
            padding: 20px;
            border-radius: 5px;
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-icon {
            font-size: 30px;
            color: rgba(226, 29, 29, 1);
        }

        .card-content h3 {
            margin: 0;
            font-size: 16px;
            color: #fff;
        }

        .card-content p {
            margin: 5px 0 0;
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .graph-container {
            margin-top: 30px;
            width: 80%;
            height: 70%;
        }

        .graph-container h3 {
            margin: 0 0 20px 0;
            color: #fff;
        }

        /* Rest of original styles */
        .main-footer {
            background-color: #000;
            padding: 20px 40px;
            width: 100%;
            box-sizing: border-box;
        }

        /* ... rest of original CSS ... */

        @media (max-width: 991px) {
            /* ... original media queries ... */
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
                
                <div class="dashboard-cards">
                    <div class="card">
                        <i class="fas fa-dollar-sign card-icon"></i>
                        <div class="card-content">
                            <h3>Total Sales</h3>
                            <p>$<?php echo number_format($total_sales, 2); ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <i class="fas fa-users card-icon"></i>
                        <div class="card-content">
                            <h3>Total Members</h3>
                            <p><?php echo $total_members; ?></p>
                        </div>
                    </div>
                </div>

                <div class="graph-container">
                    <h3>Sales Overview</h3>
                    <canvas id="salesChart"></canvas>
                </div>
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
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        document.getElementById('settingsToggle').addEventListener('click', function() {
            this.classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const settingsContainer = document.getElementById('settingsToggle');
            if (!settingsContainer.contains(event.target)) {
                settingsContainer.classList.remove('active');
            }
        });

        // Chart.js configuration
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            const dailyLabels = <?php echo json_encode(array_column($daily_sales, 'date')); ?>;
            const dailyData = <?php echo json_encode(array_column($daily_sales, 'total')); ?>;
            const weeklyLabels = <?php echo json_encode(array_column($weekly_sales, 'week')); ?>;
            const weeklyData = <?php echo json_encode(array_column($weekly_sales, 'total')); ?>;
            const yearlyLabels = <?php echo json_encode(array_column($yearly_sales, 'year')); ?>;
            const yearlyData = <?php echo json_encode(array_column($yearly_sales, 'total')); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: [{
                        label: 'Daily Sales',
                        data: dailyData,
                        borderColor: 'rgba(226, 29, 29, 1)',
                        tension: 0.1
                    }, {
                        label: 'Weekly Sales',
                        data: weeklyData,
                        borderColor: '#4CAF50',
                        tension: 0.1
                    }, {
                        label: 'Yearly Sales',
                        data: yearlyData,
                        borderColor: '#2196F3',
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        x: {
                            type: 'category',
                            labels: dailyLabels.concat(weeklyLabels, yearlyLabels),
                            title: {
                                display: true,
                                text: 'Time Period',
                                color: '#fff'
                            },
                            ticks: {
                                color: '#fff'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Amount ($)',
                                color: '#fff'
                            },
                            ticks: {
                                color: '#fff'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#fff'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>