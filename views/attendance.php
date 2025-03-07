<?php
// Start or resume the session
session_start();

// Check if sessions are working (debugging)
if (session_status() === PHP_SESSION_NONE) {
    die("Sessions are not working. Please check your PHP configuration.");
}

// Include the database connection file
include '../database/database.php'; // Adjust path as needed

// Process attendance form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    try {
        $membership_id = $_POST['membership_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $check_in = "$date $time:00";

        $sql = "INSERT INTO Attendance (membership_id, check_in) VALUES (:membership_id, :check_in)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':membership_id' => $membership_id,
            ':check_in' => $check_in
        ]);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_checkout'])) {
    try {
        $attendance_id = $_POST['attendance_id'];
        $check_out = $_POST['check_out'];

        $sql = "UPDATE Attendance SET check_out = :check_out WHERE attendance_id = :attendance_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':check_out' => $check_out,
            ':attendance_id' => $attendance_id
        ]);

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $checkout_error = "Error saving checkout: " . $e->getMessage();
    }
}

// Fetch all data from Attendance table with member names using a JOIN
$sql = "SELECT a.attendance_id, a.membership_id, a.check_in, a.check_out, 
               CONCAT(m.first_name, ' ', m.last_name) AS member_name 
        FROM Attendance a 
        LEFT JOIN membership m ON a.membership_id = m.membership_id";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active members from membership table
$activeMembersSql = "SELECT membership_id, CONCAT(first_name, ' ', last_name) AS member_name 
                     FROM membership 
                     WHERE status = 'Active'";
$activeMembersStmt = $conn->prepare($activeMembersSql);
$activeMembersStmt->execute();
$activeMembers = $activeMembersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracker - He-Man Fitness Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <style>
        /* Same styles as original code */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
            background-color: #1e1e1e;
            color: #e0e0e0;
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

        .main-content {
            flex: 1;
            padding: 20px;
            color: #e0e0e0;
            font-family: Arial, sans-serif;
            background-color: #1e1e1e;
            margin-left: 0;
            transition: margin-left 0.3s ease;
            position: relative;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        h2 {
            color: #ffffff;
            font-family: 'Anton', sans-serif;
            margin-bottom: 20px;
        }

        .form-section {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #ffffff;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #333;
            color: #e0e0e0;
            box-sizing: border-box;
        }

        button {
            background-color: #ff4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #cc0000;
        }

        .action-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .action-btn:hover {
            background-color: #45a049;
        }

        .search-section {
            margin-bottom: 20px;
        }

        .search-container {
            position: relative;
            max-width: 300px;
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        #search {
            padding-left: 30px;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2c2c2c;
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        th {
            background-color: #333;
            color: #ffffff;
            font-family: 'Anton', sans-serif;
        }

        tr:hover {
            background-color: #383838;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            width: 300px;
        }

        .popup.active {
            display: block;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .popup-overlay.active {
            display: block;
        }

        .close-btn {
            background-color: #666;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            float: right;
        }

        .close-btn:hover {
            background-color: #555;
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
            .popup {
                width: 90%;
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
                        <a href="../views/dashboard.php" class="nav-item">
                            <i class="fas fa-home nav-icon"></i>
                            <span>Home</span>
                        </a>
                        <a href="ListMember.php" class="nav-item">
                            <i class="fas fa-user-plus nav-icon"></i>
                            <span>Add Member</span>
                        </a>
                        <a href="attendance.php" class="nav-item nav-item--active">
                            <i class="fas fa-calendar-check nav-icon"></i>
                            <span>Attendance</span>
                        </a>
                        <a href="logs.php" class="nav-item">
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
                <div class="container">
                    <h2>Attendance Tracker</h2>
                    
                    <div class="form-section">
                        <?php if (isset($error)) : ?>
                            <p style="color: #ff4444;"><?php echo $error; ?></p>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="input-group">
                                <label for="membership_id">Member Name</label>
                                <select id="membership_id" name="membership_id" required>
                                    <option value="">Select a Member</option>
                                    <?php
                                    foreach ($activeMembers as $member) {
                                        echo "<option value='" . htmlspecialchars($member['membership_id']) . "'>" . htmlspecialchars($member['member_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date" required 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="input-group">
                                <label for="time">Time</label>
                                <input type="time" id="time" name="time" required 
                                       value="<?php echo date('H:i'); ?>" step="60">
                            </div>
                            <br>
                            <button type="submit" name="mark_attendance">Mark Attendance</button>
                        </form>
                    </div>

                    <div class="popup-overlay" id="popupOverlay"></div>
                    <div class="popup" id="checkoutPopup">
                        <?php if (isset($checkout_error)) : ?>
                            <p style="color: #ff4444;"><?php echo $checkout_error; ?></p>
                        <?php endif; ?>
                        <button class="close-btn" onclick="hidePopup()">X</button>
                        <form method="POST" action="">
                            <input type="hidden" name="attendance_id" id="popupAttendanceId">
                            <div class="input-group">
                                <label for="check_out">Check Out Time</label>
                                <input type="datetime-local" id="check_out" name="check_out" required>
                            </div>
                            <br>
                            <button type="submit" name="mark_checkout">Clock Out</button>
                        </form>
                    </div>

                    <div class="search-section">
                        <div class="search-container">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="search" placeholder="Search by member name..." onkeyup="searchTable()">
                        </div>
                    </div>

                    <table id="attendanceTable">
                        <thead>
                            <tr>
                                <th>Attendance ID</th>
                                <th>Member Name</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php
                            if (count($result) > 0) {
                                foreach ($result as $row) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["attendance_id"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["member_name"] ?? 'Unknown Member') . "</td>";
                                    echo "<td>" . htmlspecialchars($row["check_in"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["check_out"] ?? 'Not Checked Out') . "</td>";
                                    echo "<td>";
                                    if (!$row["check_out"]) {
                                        echo "<button class='action-btn' onclick='showPopup(" . htmlspecialchars($row["attendance_id"]) . ")'>Check Out</button>";
                                    } else {
                                        echo "-";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No attendance records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Set current time for check-in form when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Menu toggle functionality
            document.getElementById('menuToggle').addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                const mainContent = document.getElementById('mainContent');
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('shifted');
            });

            // Set initial time value
            updateTimeInput();

            // Optional: Update time every minute
            setInterval(updateTimeInput, 60000);
        });

        // Function to format current time for check-in input
        function updateTimeInput() {
            const now = new Date();
            const timeInput = document.getElementById('time');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            timeInput.value = `${hours}:${minutes}`;
        }

        function showPopup(attendanceId) {
            const popup = document.getElementById('checkoutPopup');
            const overlay = document.getElementById('popupOverlay');
            const attendanceIdInput = document.getElementById('popupAttendanceId');
            const checkOutInput = document.getElementById('check_out');
            
            // Set the attendance ID
            attendanceIdInput.value = attendanceId;
            
            // Set current date and time for checkout
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            checkOutInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
            
            // Show popup and overlay
            popup.classList.add('active');
            overlay.classList.add('active');
        }

        function hidePopup() {
            const popup = document.getElementById('checkoutPopup');
            const overlay = document.getElementById('popupOverlay');
            popup.classList.remove('active');
            overlay.classList.remove('active');
        }

        function searchTable() {
            const input = document.getElementById('search');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('attendanceTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[1];
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
        }
    </script>
</body>
</html>