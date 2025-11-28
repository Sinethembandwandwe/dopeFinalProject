<?php
session_start();
include "DbConn.php"; 

// 1. AUTHENTICATION CHECK
// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['Userid'])) {
    // You can add a message here before redirecting if desired
    header("Location: login.php");
    exit();
}

// Get the logged-in Userid
$Userid = $_SESSION['Userid'];

$reviewError = '';
$reviewSuccess = '';

// If submission failed, pre-fill the review text and rating back into the form
$currentReviewText = $_POST['review'] ?? '';
$currentRating = $_POST['rating'] ?? 0;

//2. ENSURE REVIEW TABLE EXISTS 
function ensureReviewTableExists($conn) {
    
    $ReviewSql = "CREATE TABLE IF NOT EXISTS Review (
        Reviewid INT AUTO_INCREMENT PRIMARY KEY,
        Userid INT NOT NULL,
        rating INT NOT NULL,
        review_text TEXT NOT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (Userid) REFERENCES User(Userid)
    )";
    if ($conn->query($ReviewSql) === FALSE) {
        error_log("Database error creating Review table: " . $conn->error);
    }
}
ensureReviewTableExists($conn);


//3. HANDLE REVIEW SUBMISSION 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewText = trim($_POST['review'] ?? '');
    $rating = $_POST['rating'] ?? 0; // Retrieve the rating

    // Re-assign for pre-filling in case of error
    $currentReviewText = $reviewText; 
    $currentRating = $rating;

    if (empty($reviewText)) {
        $reviewError = 'Please write your review before submitting.';
    } elseif (strlen($reviewText) < 10) {
        $reviewError = 'Your review must be at least 10 characters long.';
    } elseif (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        $reviewError = 'Please select a rating between 1 and 5 stars.';
    } else {
        // Use prepared statement to insert the review and rating
        // 'iis' means integer (Userid), integer (rating), string (review_text)
        $stmt = $conn->prepare("INSERT INTO Review (Userid, rating, review_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $Userid, $rating, $reviewText);
        
        if ($stmt->execute()) {
            $reviewSuccess = 'Thank you! Your review has been submitted successfully.';
            // Clear the form on successful submission
            $currentReviewText = '';
            $currentRating = 0;
        } else {
            $reviewError = 'Database error: Could not submit your review.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOP{e} - Submit a Review</title>
    <link rel="stylesheet" href="wholestyle.css">
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
</head>
<body style="font-family: Arial, sans-serif; background-color: #fff0f5;">

<main>
  <form class="auth-form" method="POST" action="">
    <h2 class="form-title magenta-title">Submit a Review</h2>

    <?php if ($reviewSuccess): ?>
        <div style="color: green; margin-bottom: 15px; font-weight: bold;">
            <?= htmlspecialchars($reviewSuccess) ?>
        </div>
    <?php elseif ($reviewError): ?>
        <div style="color: red; margin-bottom: 15px; font-weight: bold;">
            <?= htmlspecialchars($reviewError) ?>
        </div>
    <?php endif; ?>

    <p style="margin-bottom: 20px; color: #555;">
        You are submitting this review as <strong><?= htmlspecialchars($_SESSION['firstname'] ?? 'User') ?></strong>.
    </p>

    <!-- NEW: RATING INPUT -->
    <label>Rate this establishment (1 to 5 stars):</label><br>
    <div class="rating-stars" title="Click to set your rating">
        <input type="radio" id="star5" name="rating" value="5" <?= ($currentRating == 5) ? 'checked' : '' ?> required>
        <label for="star5" title="5 stars"></label>
        
        <input type="radio" id="star4" name="rating" value="4" <?= ($currentRating == 4) ? 'checked' : '' ?>>
        <label for="star4" title="4 stars"></label>
        
        <input type="radio" id="star3" name="rating" value="3" <?= ($currentRating == 3) ? 'checked' : '' ?>>
        <label for="star3" title="3 stars"></label>
        
        <input type="radio" id="star2" name="rating" value="2" <?= ($currentRating == 2) ? 'checked' : '' ?>>
        <label for="star2" title="2 stars"></label>
        
        <input type="radio" id="star1" name="rating" value="1" <?= ($currentRating == 1) ? 'checked' : '' ?>>
        <label for="star1" title="1 star"></label>
    </div>
    <br><br>
    <!-- END NEW: RATING INPUT -->

    <label for="review">Your Review:</label><br>
    <textarea id="review" name="review" placeholder="Write your review here..." rows="5" cols="40" required><?= htmlspecialchars($currentReviewText) ?></textarea><br><br>

    <input type="submit" value="Submit Review" class="btn">
    
  </form>
</main>

<footer style="text-align: center; background-color: #ffb6c1; padding: 15px; margin-top: 20px;">
  <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
  <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>

</body>
</html>
