<?php
session_start();
require_once "DbConn.php"; 

// 1. Check if user is logged in
if (!isset($_SESSION['Userid'])) {
    // If not logged in, redirect them immediately
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['Userid'];
$firstname = $_SESSION['firstname'];
$deleteError = '';
$deleteSuccess = '';

// --- Handle Account Deletion POST Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    
    $password = $_POST['password_confirm'] ?? '';

    if (empty($password)) {
        $deleteError = 'Please enter your password to confirm.';
    } else {
        // Step A: Retrieve password hash and profile picture path from the User table
        $stmt = $conn->prepare("SELECT password_hash, profile_picture FROM User WHERE Userid = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = $user['password_hash'];
            $profilePicturePath = $user['profile_picture'];
            $stmt->close();

            // Step B: Verify Password
            if (password_verify($password, $hashedPassword)) {
                      
                // 1. Delete associated Saved Outfits
                $stmt = $conn->prepare("DELETE FROM save_outfit WHERE Userid = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();

                // 2. Delete the profile picture file (if it exists)
                if ($profilePicturePath && file_exists($profilePicturePath)) {
                    unlink($profilePicturePath);
                }

                // 3. Delete the User record itself
                $stmt = $conn->prepare("DELETE FROM User WHERE Userid = ?");
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    // SUCCESS: Destroy session and redirect
                    session_unset();
                    session_destroy();
                    // Give a success message on the login page
                    header('Location: login.php?status=deleted');
                    exit();
                } else {
                    $deleteError = 'Database error during user deletion. Please contact support.';
                }

                $stmt->close();

            } else {
                $deleteError = 'Incorrect password. Account deletion failed.';
            }
        } else {
            // Should not happen if the user is logged in, but as a fallback:
            $deleteError = 'User account not found.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Account</title>
    <link rel="stylesheet" href="wholestyle.css">
    
</head>
<body style="font-family: Arial, sans-serif; background-color: #fff0f5;">

<header>
    <header>
    <h1>Daily Outfit Planner</h1>
    <p style="color: #ff69b4; font-size: 18px;">Dress Pressed NOT Depressed 😁😍</p>
    <hr>
    <nav class="topnav">
  <ul class="nav-links">
    <li><a href="index%20.php" id="homeBtn">Home</a></li>
    <li>
      <a href="#planner" class="dropdown-toggle">Outfit Planner</a>
      <ul class="dropdown-menu">
        <li><a href="index%20.php">Tips</a></li>
        <li><a href="index%20.php">FAQs</a></li>
        <li><a href="view_outfits.php">Save Outfits</a></li> 
      </ul>
    </li>
    <li><a href="index%20.php">About Us</a></li>
    <li><a href="index%20.php">Contact</a></li>
    
    <?php if (isset($_SESSION['Userid'])): ?>
      <!-- Show Profile and Logout when logged in -->
      <li><a href="profile.php">Profile</a></li>
      <li><a href="logout.php">Logout</a></li>
    <?php else: ?>
      <!-- Show Login when logged out -->
      <li><a href="login.php">Login</a></li>
    <?php endif; ?>
    
    <li><a href="reviews.php">Submit Review</a></li>
    <li><a href="#browser-details" id="Browser-info">Browser Info</a></li>
  </ul>
</nav>
</header>

</header>

<main>
    <div class="container">
        <h2>Delete Account: <?= htmlspecialchars($firstname) ?></h2>

        <?php if ($deleteError): ?>
            <div class="error"><?= htmlspecialchars($deleteError) ?></div>
        <?php endif; ?>

        <div class="warning">
            <p><strong>DANGER ZONE:</strong> This action is permanent and cannot be undone. All your saved data, including outfits and reviews, will be immediately and permanently deleted.</p>
            <p>If you are absolutely sure you want to proceed, please enter your password below to confirm.</p>
        </div>

        <form class="delete-form" method="post" action="">
            <input type="hidden" name="action" value="delete_account">
            
            <label for="password_confirm">Enter Password:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            
            <input type="submit" value="Permanently Delete Account" class="btn-delete">
        </form>
    </div>
</main>

<footer style="text-align: center; background-color: #ffb6c1; padding: 15px; margin-top: 20px;">
    <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
    <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>
</body>
</html>
