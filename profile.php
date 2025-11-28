<?php
session_start();
require_once "DbConn.php"; 

// --- Check if user is logged in ---
if (!isset($_SESSION['Userid'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['Userid'];
$profile = null;
$fetchError = '';

try {
    $stmt = $conn->prepare("SELECT firstname, email, profile_picture FROM User WHERE Userid = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $profile = $result->fetch_assoc();
    } else {
        session_unset();
        session_destroy();
        header('Location: login.php?status=error');
        exit();
    }
    $stmt->close();
} catch (Exception $e) {
    $fetchError = 'Could not load profile data.';
    error_log("Profile load error: " . $e->getMessage());
}

$conn->close();

$firstname = $profile['firstname'] ?? 'User';
$email = $profile['email'] ?? 'N/A';
$picturePath = $profile['profile_picture'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOP{e} - User Profile</title>
    <link rel="stylesheet" href="wholestyle.css">
    <style>
       
        .profile-container {
            max-width: 500px;
            margin: 120px auto 50px; /* add top margin so header doesn't cover */
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ff69b4;
            margin: 0 auto 20px;
        }
        .info-label {
            font-weight: bold;
            color: #c71585;
            display: block;
            margin-top: 15px;
            font-size: 1.1em;
        }
        .info-value {
            font-size: 1em;
            color: #333;
            margin-bottom: 20px;
        }
        .delete-link {
            display: block;
            margin-top: 30px;
            color: #8b0000;
            font-weight: bold;
            text-decoration: none;
            padding: 10px;
            border: 1px dashed #ffaaaa;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .delete-link:hover {
            background-color: #ffe0e0;
        }
        .update-link {
            display: block;
            margin-top: 30px;
            color: #8b0000;
            font-weight: bold;
            text-decoration: none;
            padding: 10px;
            border: 1px dashed #ffaaaa;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .update-link:hover {
            background-color: #ffe0e0;
        }
        
    </style>
</head>
<body>

<header>
    <h1>Daily Outfit Planner</h1>
    <p>Dress Pressed NOT Depressed 😁😍</p>
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
    <div class="profile-container">
        <h2>Your Profile</h2>
        <?php if ($fetchError): ?>
            <p style="color:red;"><?= htmlspecialchars($fetchError) ?></p>
        <?php endif; ?>

        <?php if ($picturePath): ?>
            <img src="<?= htmlspecialchars($picturePath) ?>" alt="<?= htmlspecialchars($firstname) ?>'s Profile Picture" class="profile-picture" onerror="this.onerror=null;this.src='https://placehold.co/150x150/ffb6c1/8b008b?text=No+Image';">
        <?php else: ?>
            <img src="https://placehold.co/150x150/ffb6c1/8b008b?text=No+Image" alt="Placeholder Profile Picture" class="profile-picture">
        <?php endif; ?>

        <span class="info-label">Name:</span>
        <div class="info-value"><?= htmlspecialchars($firstname) ?></div>

        <span class="info-label">Email:</span>
        <div class="info-value"><?= htmlspecialchars($email) ?></div>

        <hr style="border-color: #eee;">

         <div style="margin-top: 30px;">
            <a href="cartoon.php" class="btn">Plan Another Outfit</a>
        </div>

        <div style="margin-top: 30px;">
            <a href="UserReports.php" class="btn">User Insights</a>
        </div>

        <a href="delete.php" class="delete-link">Account Management: Delete Account</a>
        <a href="update.php" class="update-link">Account Management: Update Account</a>
    </div>
   
</main>

<footer>
    <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
    <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>

</body>
</html>
