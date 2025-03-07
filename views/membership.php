<?php
// Start or resume the session
session_start();

// Check if sessions are working (debugging)
if (session_status() === PHP_SESSION_NONE) {
    die("Sessions are not working. Please check your PHP configuration.");
}

// Include the database connection file
include '../database/database.php';

// Fetch all data from membership table using PDO
$sql = "SELECT * FROM membership";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check the current script path
$scriptPath = __DIR__;
echo "Script path: " . $scriptPath . "<br>"; // Remove or comment out in production
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership - He-Man Fitness Gym</title>
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
            z-index: 30;
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
            width: 100%;
        }

        .form-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 40px;
            background-color: #2c2c2c;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.25);
            text-align: center;
        }

        h1 {
            color: #ffffff;
            font-family: 'Anton', sans-serif;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            max-width: 600px;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #333;
            color: #e0e0e0;
            font-size: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .search-input:focus {
            border-color: #ff4444;
            outline: none;
        }

        .search-button {
            background-color: #ff4444;
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .search-button:hover {
            background-color: #cc0000;
        }

        .membership-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #333;
            color: #e0e0e0;
            font-family: 'Inter', sans-serif;
            table-layout: fixed;
        }

        .membership-table th,
        .membership-table td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid #444;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
        }

        .membership-table th {
            background-color: #ff4444;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
        }

        .membership-table td {
            font-size: 15px;
        }

        .membership-table tr:hover {
            background-color: #3a3a3a;
        }

        .membership-table .no-members {
            text-align: center;
            padding: 20px;
            color: #ff4444;
            font-weight: 700;
        }

        .edit-btn {
            background-color: #ff4444;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .edit-btn:hover {
            background-color: #cc0000;
        }

        @media (max-width: 991px) {
            .sidebar-nav {
                width: 250px;
                left: -250px;
            }
            .sidebar-nav.active {
                left: 0;
            }
            .form-container {
                padding: 20px;
                max-width: 100%;
            }
            .search-input {
                max-width: 100%;
            }
            .membership-table {
                font-size: 13px;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .membership-table th,
            .membership-table td {
                padding: 10px 8px;
                min-width: 120px;
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
                        <a href="/test/views/logs.php" class="nav-item">
                            <i class="fas fa-file-alt nav-icon"></i>
                            <span>Logs</span>
                        </a>
                        <a href="membership.php" class="nav-item nav-item--active">
                            <i class="fas fa-id-card nav-icon"></i>
                            <span>Membership</span>
                        </a>
                    </div>
                </div>
            </nav>

            <main class="main-content" id="mainContent">
                <div class="form-container">
                    <h1>Membership</h1>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search members...">
                    <button class="search-button" id="searchButton">Search</button>
                    <table class="membership-table">
                        <thead>
                            <tr>
                                <th>Membership ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Birth Date</th>
                                <th>Plan</th>
                                <th>Start Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (count($result) > 0) {
                                foreach ($result as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["membership_id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["first_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["last_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["address"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["birth_date"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["plan"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["start_date"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["amount"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                                    echo "<td><a href='edit_member.php?id=" . htmlspecialchars($row["membership_id"]) . "' class='edit-btn'><i class='fas fa-edit'></i></a></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12' class='no-members'>No Membership Yet</td></tr>";
                            }
                            ?>
                        </tbody>
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
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');

        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent click from bubbling to document
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Prevent clicks inside sidebar from closing it
        sidebar.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            searchTable();
        });

        // Optional: Add search on Enter key press
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchTable();
            }
        });

        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.querySelector('.membership-table');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let rowText = '';
                const td = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < td.length - 1; j++) {
                    rowText += td[j].textContent.toLowerCase() + ' ';
                }

                if (rowText.indexOf(filter) > -1) {
                    tr[i].style.display = '';
                } else {
                    tr[i].style.display = 'none';
                }
            }

            if (filter === '') {
                for (let i = 1; i < tr.length; i++) {
                    tr[i].style.display = '';
                }
            }
        }
    </script>
</body>
</html>