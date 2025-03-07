<?php
// Start or resume the session
session_start();

// Check if sessions are working (debugging)
if (session_status() === PHP_SESSION_NONE) {
    die("Sessions are not working. Please check your PHP configuration.");
}

// Include the PDO database connection file
include '../database/database.php'; 

// Define prices (can be moved to a config file or database later)
$prices = [
    'Per Session' => 80,  // ₱80 per session
    'Monthly' => 850      // ₱850 monthly
];

// Set the default dateAdded to today's date
$defaultDateAdded = date('Y-m-d'); // Format: YYYY-MM-DD

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure PDO connection is available
    if (!isset($conn) || !$conn) {
        die("Database connection not established.");
    }

    // Check if this is a confirmation submission
    if (isset($_POST['confirm']) && $_POST['confirm'] == '1') {
        // Retrieve form data from hidden fields
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $birthDate = $_POST['birthDate'] ?? '';
        $dateAdded = $_POST['dateAdded'] ?? $defaultDateAdded;
        $plan = $_POST['plan'] ?? '';
        $amount = $prices[$plan] ?? 0; // Get amount based on plan
        $status = 'Active'; // Default status

        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($phoneNumber) || empty($email) || 
            empty($address) || empty($birthDate) || empty($plan) || empty($dateAdded)) {
            $error = "All required fields must be filled.";
            echo $error;
        } else {
            try {
                // Prepare SQL query for the Membership table
                $sql = "INSERT INTO Membership (
                    first_name, last_name, email, phone, address, birth_date, 
                    plan, start_date, amount, status
                ) VALUES (:first_name, :last_name, :email, :phone, :address, :birth_date, 
                          :plan, :start_date, :amount, :status)";
                
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    die("Prepare failed: " . $conn->errorInfo()[2]);
                }

                // Bind parameters and execute
                $stmt->execute([
                    ':first_name' => $firstName,
                    ':last_name' => $lastName,
                    ':email' => $email,
                    ':phone' => $phoneNumber,
                    ':address' => $address,
                    ':birth_date' => $birthDate,
                    ':plan' => $plan,
                    ':start_date' => $dateAdded,
                    ':amount' => $amount,
                    ':status' => $status
                ]);

                // Redirect to membership.php with success message
                header("Location: membership.php?success=1");
                exit();
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
                echo $error; // Display the error for debugging
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Membership - He-Man Fitness Gym</title>
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

        .form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #2c2c2c;
            border-radius: 5px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.25);
        }

        h2 {
            color: #ffffff;
            font-family: 'Anton', sans-serif;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #ffffff;
            font-family: 'Inter', sans-serif;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #333;
            color: #e0e0e0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        input:focus, select:focus {
            border-color: #ff4444;
            outline: none;
        }

        button {
            background-color: #ff4444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            display: block;
            margin: 20px auto 0;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #cc0000;
        }

        .success-message {
            color: #ff4444;
            text-align: center;
            margin-top: 15px;
        }

        /* Modal Styling */
        .modal-content {
            background-color: #ffffff;
            color: #000;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #ccc;
            padding: 15px;
        }

        .modal-title {
            font-family: 'Inter', sans-serif;
            font-size: 18px;
            font-weight: bold;
        }

        .modal-body {
            padding: 20px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        .modal-footer {
            border-top: 1px solid #ccc;
            padding: 15px;
            justify-content: center;
        }

        .modal-footer .btn {
            padding: 8px 20px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            border-radius: 20px;
        }

        .modal-footer .btn-primary {
            background-color: #ff4444;
            border: none;
            color: #fff;
        }

        .modal-footer .btn-secondary {
            background-color: #ff4444;
            border: none;
            color: #fff;
            margin-left: 10px;
        }

        .modal-footer .btn-primary:hover {
            background-color: #cc0000;
        }

        .modal-footer .btn-secondary:hover {
            background-color: #cc0000;
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
            .form-row {
                flex-direction: column;
                gap: 15px;
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
                        <a href="ListMember.php" class="nav-item nav-item--active">
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
                        <a href="membership.php" class="nav-item">
                            <i class="fas fa-id-card nav-icon"></i>
                            <span>Membership</span>
                        </a>
                    </div>
                </div>
            </nav>

            <main class="main-content" id="mainContent">
                <div class="form-container">
                    <h2>Add Membership</h2>
                    <form method="POST" action="" id="addMemberForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name:</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name:</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phoneNumber">Phone Number:</label>
                                <input type="tel" id="phoneNumber" name="phoneNumber" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <input type="text" id="address" name="address" required>
                            </div>
                            <div class="form-group">
                                <label for="birthDate">Birth Date:</label>
                                <input type="date" id="birthDate" name="birthDate" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dateAdded">Date Added:</label>
                                <input type="date" id="dateAdded" name="dateAdded" value="<?php echo $defaultDateAdded; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="plan">Plan:</label>
                                <select id="plan" name="plan" required>
                                    <option value="" disabled selected>Select a plan</option>
                                    <option value="Per Session">Per Session</option>
                                    <option value="Monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" id="previewButton">Add Member</button>
                    </form>
                    <?php if (isset($_GET['success'])): ?>
                        <p class="success-message">Member added successfully!</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Preview Member Details & Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="previewModalBody">
                    <!-- Dynamically populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmSubmit">Confirm</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for confirmation -->
    <form method="POST" action="" id="confirmForm" style="display: none;">
        <input type="hidden" name="confirm" value="1">
        <input type="hidden" name="firstName" id="hiddenFirstName">
        <input type="hidden" name="lastName" id="hiddenLastName">
        <input type="hidden" name="phoneNumber" id="hiddenPhoneNumber">
        <input type="hidden" name="email" id="hiddenEmail">
        <input type="hidden" name="address" id="hiddenAddress">
        <input type="hidden" name="birthDate" id="hiddenBirthDate">
        <input type="hidden" name="dateAdded" id="hiddenDateAdded">
        <input type="hidden" name="plan" id="hiddenPlan">
    </form>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
    <script>
        // Pass PHP prices to JavaScript
        const prices = <?php echo json_encode($prices); ?>;

        // Toggle sidebar functionality
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        });

        // Handle form preview
        document.getElementById('addMemberForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            // Gather form data
            const formData = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                phoneNumber: document.getElementById('phoneNumber').value,
                email: document.getElementById('email').value,
                address: document.getElementById('address').value,
                birthDate: document.getElementById('birthDate').value,
                dateAdded: document.getElementById('dateAdded').value,
                plan: document.getElementById('plan').value
            };

            // Get payment amount from prices object
            const paymentAmount = prices[formData.plan] ? `₱${prices[formData.plan]}${formData.plan === 'Per Session' ? '/session' : ''}` : 'N/A';

            // Populate modal
            const modalBody = document.getElementById('previewModalBody');
            modalBody.innerHTML = `
                <h6>Member Details:</h6>
                <p><strong>First Name:</strong> ${formData.firstName}</p>
                <p><strong>Last Name:</strong> ${formData.lastName}</p>
                <p><strong>Phone Number:</strong> ${formData.phoneNumber}</p>
                <p><strong>Email:</strong> ${formData.email}</p>
                <p><strong>Address:</strong> ${formData.address}</p>
                <p><strong>Birth Date:</strong> ${formData.birthDate}</p>
                <p><strong>Date Added:</strong> ${formData.dateAdded}</p>
                <p><strong>Plan:</strong> ${formData.plan}</p>
                <hr>
                <h6>Payment Procedure:</h6>
                <p><strong>Amount Due:</strong> ${paymentAmount}</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="paymentReceived" required>
                    <label class="form-check-label" for="paymentReceived">Payment Received</label>
                </div>
            `;

            // Populate hidden form fields for confirmation
            document.getElementById('hiddenFirstName').value = formData.firstName;
            document.getElementById('hiddenLastName').value = formData.lastName;
            document.getElementById('hiddenPhoneNumber').value = formData.phoneNumber;
            document.getElementById('hiddenEmail').value = formData.email;
            document.getElementById('hiddenAddress').value = formData.address;
            document.getElementById('hiddenBirthDate').value = formData.birthDate;
            document.getElementById('hiddenDateAdded').value = formData.dateAdded;
            document.getElementById('hiddenPlan').value = formData.plan;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        });

        // Handle confirmation
        document.getElementById('confirmSubmit').addEventListener('click', function() {
            const paymentReceived = document.getElementById('paymentReceived');
            if (paymentReceived.checked) {
                document.getElementById('confirmForm').submit();
            } else {
                alert('Please confirm payment has been received.');
            }
        });
    </script>
</body>
</html>