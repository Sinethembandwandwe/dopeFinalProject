<?php
session_start();
require_once "DbConn.php"; 

// 1. Authentication Check
if (!isset($_SESSION['Userid'])) {
    // If not logged in, redirect to the login page
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['Userid'];
$successMessage = '';
$errorMessage = '';
$userData = [];

// --- Database Functions ---

// Function to ensure the necessary table structure for User table update (specifically the profile_picture column)
function ensureUserTableStructure($conn) {
    // Check if the 'profile_picture' column exists. If not, add it.
    $checkColumnSql = "SHOW COLUMNS FROM User LIKE 'profile_picture'";
    $result = $conn->query($checkColumnSql);
    
    if ($result && $result->num_rows == 0) {
        $addColumnSql = "ALTER TABLE User ADD profile_picture VARCHAR(255) NULL AFTER reg_date";
        if ($conn->query($addColumnSql) === FALSE) {
            error_log("Database error adding profile_picture column: " . $conn->error);
        }
    }
}
ensureUserTableStructure($conn);


// 2. Load Current User Data (GET request or initial load)
function loadUserData($conn, $userId) {
    $stmt = $conn->prepare("SELECT firstname, email, profile_picture FROM User WHERE Userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

// 3. Handle Form Submission (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Load current data for comparison
    $userData = loadUserData($conn, $userId);

    // --- Data Validation and Sanitization ---
    $newFirstname = trim($_POST['firstname'] ?? $userData['firstname']);
    $newEmail = trim($_POST['email'] ?? $userData['email']);
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $removePicture = isset($_POST['remove_picture']) ? true : false;

    // --- Security: Verify Current Password for ANY changes (best practice) ---
    $stmt = $conn->prepare("SELECT password_hash FROM User WHERE Userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userRecord = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($currentPassword, $userRecord['password_hash'])) {
        $errorMessage = 'Invalid current password. Please enter your password to save changes.';
    } else {
        // --- Prepare Update Query ---
        $updates = [];
        $params = [];
        $paramTypes = '';
        
        // 3a. Update First Name
        if ($newFirstname !== $userData['firstname']) {
            $updates[] = "firstname = ?";
            $params[] = $newFirstname;
            $paramTypes .= 's';
            $_SESSION['firstname'] = $newFirstname; // Update session immediately
        }

        // 3b. Update Email
        if ($newEmail !== $userData['email']) {
             if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Invalid email format.';
            } else {
                // Check if new email is already taken by another user
                $stmt = $conn->prepare("SELECT Userid FROM User WHERE email = ? AND Userid != ?");
                $stmt->bind_param("si", $newEmail, $userId);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $errorMessage = 'That email is already in use by another account.';
                } else {
                    $updates[] = "email = ?";
                    $params[] = $newEmail;
                    $paramTypes .= 's';
                }
                $stmt->close();
            }
        }

        // 3c. Update Password
        if (empty($errorMessage) && !empty($newPassword)) {
            // Note: Password policy checks (length, complexity) should go here
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updates[] = "password_hash = ?";
            $params[] = $hashedPassword;
            $paramTypes .= 's';
        } elseif (!empty($newPassword)) {
            $errorMessage = 'New password cannot be empty.';
        }


        // 3d. Update Profile Picture
        $newPicturePath = $userData['profile_picture'];
        if (empty($errorMessage) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "profile_pics/";
            $fileInfo = pathinfo($_FILES['profile_picture']['name']);
            $fileExtension = strtolower($fileInfo['extension']);
            
            // Basic validation
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($fileExtension, $allowedTypes)) {
                $errorMessage = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
            } elseif ($_FILES['profile_picture']['size'] > 500000) { // 500KB limit
                $errorMessage = 'File size must be less than 500KB.';
            } else {
                // Generate a unique file name
                $newFileName = $userId . '_' . time() . '.' . $fileExtension;
                $targetFile = $targetDir . $newFileName;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    // Delete old file if it exists
                    if ($userData['profile_picture'] && file_exists($userData['profile_picture'])) {
                        unlink($userData['profile_picture']);
                    }
                    $newPicturePath = $targetFile;
                    $updates[] = "profile_picture = ?";
                    $params[] = $newPicturePath;
                    $paramTypes .= 's';
                } else {
                    $errorMessage = 'Failed to upload profile picture.';
                }
            }
        }
        
        // 3e. Remove Profile Picture
        if (empty($errorMessage) && $removePicture && $userData['profile_picture']) {
             if (file_exists($userData['profile_picture'])) {
                unlink($userData['profile_picture']);
            }
            $newPicturePath = null;
            $updates[] = "profile_picture = ?";
            $params[] = $newPicturePath;
            $paramTypes .= 's';
        }


        //Execute Final Update
        if (empty($errorMessage) && !empty($updates)) {
            $sql = "UPDATE User SET " . implode(", ", $updates) . " WHERE Userid = ?";
            
            // Append userId to params and 'i' to types
            $params[] = $userId;
            $paramTypes .= 'i';

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                // We use call_user_func_array because bind_param needs references
                $bindParams = array_merge([$paramTypes], $params);
                
                // Create references for bind_param
                $refs = [];
                foreach ($bindParams as $key => $value) {
                    $refs[$key] = &$bindParams[$key];
                }

                call_user_func_array([$stmt, 'bind_param'], $refs);

                if ($stmt->execute()) {
                    $successMessage = 'Account details updated successfully!';
                } else {
                    $errorMessage = 'Database error: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $errorMessage = 'Failed to prepare database statement.';
            }
        } elseif (empty($errorMessage)) {
            // No changes made
            $errorMessage = 'No changes detected or invalid input provided.';
        }
    }
}

// Reload data after potential update
$userData = loadUserData($conn, $userId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOP{e} - Update Account</title>
    <link rel="stylesheet" href="wholestyle.css">
    <style>
        .profile-pic-container { margin-bottom: 20px; }
        .current-pic { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-top: 10px; border: 2px solid #ff69b4; }
        .danger-zone { border: 1px solid red; padding: 15px; margin-top: 30px; border-radius: 8px; background-color: #ffeaea; }
        .danger-zone h3 { color: red; margin-top: 0; }
        .btn-danger { background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body style="font-family: Arial, sans-serif; background-color: #fff0f5;">

<header>
    <h1>Daily Outfit Planner</h1>
    <p style="color: #ff69b4; font-size: 18px;">Dress Pressed NOT Depressed 😁😍</p>
    <hr>
    <nav class="topnav" aria-label="Main navigation">
    <ul class="nav-links">

    <!-- Main links -->
    <li><a href="index%20.php" id="homeBtn">Home</a></li>
    
    <!-- Outfit Planner dropdown -->
    <li>
      <a href="index%20.php" class="dropdown-toggle">Outfit Planner</a>
      <ul class="dropdown-menu">
        <li><a href="index%20.php">Tips</a></li>
        <li><a href="index%20.php">FAQs</a></li>
        <li><a href="view_outfits.php">Save Outfits</a></li>
      </ul>
    </li>

    <!-- Reviews dropdown -->
    <li>
      <a href="#" class="dropdown-toggle">Reviews</a>
      <ul class="dropdown-menu">
        <li><a href="reviews.php">Submit Review</a></li>
        <li><a href="view_reviews.php">View Reviews</a></li>
      </ul>
    </li>

    <!-- Info links -->
    <li><a href="index%20.php">About Us</a></li>
    <li><a href="index%20.php">Contact</a></li>

    <!-- User options -->
    <?php if (isset($_SESSION['Userid'])): ?>
      <li><a href="profile.php">Profile</a></li>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>

  </ul>
</nav>

<div class="sidebar">
  <ul>
    <li><a href="index%20.php">Home</a></li>
    <li>
      <a href="index%20.php" class="dropdown-toggle-sidebar">Outfit Planner</a>
      <ul class="dropdown-menu">
        <li><a href="index%20.php">Tips</a></li>
        <li><a href="index%20.php">FAQs</a></li>
        <li><a href="view_outfits.php">Saved Outfits</a></li>
      </ul>
    </li>
    <li><a href="index%20.php">About Us</a></li>
    <li><a href="index%20.php">Contact</a></li>
    <li><a href="login.php">Login</a></li>
    <li><a href="reviews.php">Submit Review</a></li>
    <li><a href="view_reviews.php">View Reviews</a></li>
    
  </ul>
</div>
</header>

<main>
    <h2 style="color: #c71585;">Update Account Details</h2>

    <?php if ($successMessage): ?>
        <div style="color: green; margin-bottom: 10px;">• <?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    
    <?php if ($errorMessage): ?>
        <div style="color: red; margin-bottom: 10px;">• <?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="update.php" enctype="multipart/form-data">
        <!-- --- Profile Picture --- -->
        <div class="profile-pic-container">
            <label>Current Profile Picture:</label><br>
            <?php if ($userData['profile_picture'] && file_exists($userData['profile_picture'])): ?>
                <img src="<?= htmlspecialchars($userData['profile_picture']) ?>" alt="Profile Picture" class="current-pic"><br>
                <input type="checkbox" id="remove_picture" name="remove_picture">
                <label for="remove_picture" style="display: inline;">Remove current picture?</label>
            <?php else: ?>
                <p>No profile picture uploaded.</p>
            <?php endif; ?>
            
            <label for="profile_picture">Upload New Picture (Max 500KB, JPG/PNG/GIF):</label><br>
            <input type="file" id="btn" name="profile_picture" accept="image/jpeg,image/png,image/gif"><br><br>
        </div>
        
        <!-- --- Basic Details --- -->
        <label for="firstname">First Name:</label><br>
        <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($userData['firstname'] ?? '') ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required><br><br>
        
        <!-- --- Password Update --- -->
        <h3 style="margin-top: 20px; color: #ff69b4;">Change Password (Optional)</h3>
        <label for="new_password">New Password:</label><br>
        <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password"><br><br>

        <!-- --- Security Confirmation --- -->
        <h3 style="margin-top: 20px; color: #ff69b4;">Confirm Changes</h3>
        <p>To save *any* changes, you must confirm your current password.</p>
        <label for="current_password">Current Password (Required to save):</label><br>
        <input type="password" id="current_password" name="current_password" required><br><br>

        <input type="submit" value="Save Changes" class="btn">
    </form>
    
    <!-- --- Danger Zone --- -->
    <div class="danger-zone">
        <h3>Danger Zone</h3>
        <p>This action is permanent and cannot be undone.</p>
        <a href="delete.php" class="btn-danger">Permanently Delete My Account</a>
    </div>

</main>

<footer style="text-align: center; background-color: #ffb6c1; padding: 15px; margin-top: 20px;">
    <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
    <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>
</body>
</html>