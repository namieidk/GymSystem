<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - He-Man Fitness Gym</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Google Fonts for Anton and Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Inter&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
            background-color: #1e1e1e;
            color: #e0e0e0;
            font-family: 'Inter', sans-serif;
        }

        .admin-header {
            background-color: rgba(68, 68, 68, 1);
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            font-family: 'Inter', sans-serif;
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

        .nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #1e1e1e;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        .logs-container {
            padding-top: 5px;
        }

        .search-container {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            width: 50%;
            margin-left: 260px;
        }

        .search-container input {
            padding: 8px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #333;
            color: #e0e0e0;
            flex: 1;
        }

        .search-container button {
            background-color: #ff4444;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
        }

        .search-container button:hover {
            background-color: #cc0000;
        }

        table {
            border-collapse: collapse;
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #2c2c2c;
            color: #e0e0e0;
        }

        th, td {
            border: 1px solid #444;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #333;
        }

        .no-logs {
            text-align: center;
            padding: 20px;
            color: #ff4444;
        }

        h1 {
            color: #ffffff;
            font-family: 'Anton', sans-serif;
            font-size: 32px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 991px) {
            .sidebar-nav {
                width: 100%;
                left: -100%;
            }
            .sidebar-nav.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.shifted {
                margin-left: 0;
            }
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <i class="fas fa-bars menu-icon" id="menuToggle"></i>
        <h1 class="admin-title">He-Man Fitness Gym</h1>
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
                        <a href="/test/views/dashboard.php" class="nav-item">
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
                        <a href="/test/views/logs.php" class="nav-item nav-item--active">
                            <i class="fas fa-file-alt nav-icon"></i>
                            <span>Logs</span>
                        </a>
                        <a href="membership.php" class="nav-item">
                            <i class="fas fa-id-card nav-icon"></i>
                            <span>Membership</span>
                        </a>
                    </div>
                </div>
            </nav>

            <main class="main-content" id="mainContent">
                <div class="logs-container">
                    <h1>Logs</h1>
                    
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search logs...">
                        <button onclick="searchLogs()">Search</button>
                    </div>

                    <table>
                        <tr>
                            <th>Log ID</th>
                            <th>Log Message</th>
                            <th>Log Date</th>
                        </tr>
                        <tr>
                            <td colspan="3" class="no-logs">No logs yet</td>
                        </tr>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
    <script>
        // Toggle sidebar functionality
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        // Modified search functionality to trigger only when input is 5+ characters
        function searchLogs() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let table = document.querySelector('table');
            let tr = table.getElementsByTagName('tr');

            // Only search if input length is 5 or more
            if (input.length >= 5) {
                for (let i = 1; i < tr.length; i++) {
                    let row = tr[i];
                    let text = row.textContent.toLowerCase();
                    row.style.display = text.includes(input) ? '' : 'none';
                }
            } else {
                // Show all rows if input is less than 5 characters
                for (let i = 1; i < tr.length; i++) {
                    tr[i].style.display = '';
                }
            }
        }
    </script>
</body>
</html>