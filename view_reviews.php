<?php
session_start();
require_once "DbConn.php"; 

$reviews = [];
$errorMessage = '';


// 1. Fetch Reviews
try {
    // JOIN review and User tables to display the user's name
    $sql = "SELECT 
                r.review_text, 
                r.rating, 
                u.firstname,
                r.submission_date
            FROM review r
            JOIN User u ON r.Userid = u.Userid
            ORDER BY r.submission_date DESC"; // Show newest reviews first
    
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    } else {
        $errorMessage = "Error fetching reviews: " . $conn->error;
    }

} catch (Exception $e) {
    $errorMessage = "An error occurred: " . $e->getMessage();
}

// Helper function to render star rating (1 to 5)
function renderStars($rating) {
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '<span style="color: gold;">★</span>';
        } else {
            $output .= '<span style="color: lightgray;">☆</span>';
        }
    }
    return $output;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOP{e} - User Reviews</title>
    <link rel="stylesheet" href="wholestyle.css">
    <style>
        .review-card {
            background-color: #ffffff;
            border: 1px solid #ffb6c1;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #ffb6c1;
            padding-bottom: 5px;
        }
        .review-author {
            font-weight: bold;
            color: #c71585;
            font-size: 1.1em;
        }
        .review-date {
            font-size: 0.9em;
            color: #888;
        }
        .review-rating {
            font-size: 1.5em;
        }
        .review-text {
            color: #333;
            line-height: 1.6;
        }
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

</header>

<main>
    <h2 style="color: #c71585;">What Users Are Saying</h2>

    <?php if ($errorMessage): ?>
        <div style="color: red; margin-bottom: 15px;"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <p style="text-align: center;">No reviews have been submitted yet. Be the first!</p>
    <?php endif; ?>

    <?php foreach ($reviews as $review): ?>
        <div class="review-card">
            <div class="review-header">
                <span class="review-author"><?= htmlspecialchars($review['firstname']) ?></span>
                <span class="review-rating"><?= renderStars($review['rating']) ?></span>
            </div>
            <p class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
            <div class="review-date">Submitted on: <?= date("M j, Y", strtotime($review['submission_date'])) ?></div>
        </div>
    <?php endforeach; ?>

</main>

<footer style="text-align: center; background-color: #ffb6c1; padding: 15px; margin-top: 20px;">
    <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
    <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>
</body>
</html>
