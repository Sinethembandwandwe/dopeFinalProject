<?php
session_start();
include "DbConn.php"; 

//Configuration Constant 
define('UPLOAD_DIR', 'profile_pics/');

// PHP Variables for State and Errors
$showRegistrationForm = false; 
$prefillEmail = ''; 
$signupError = '';
$loginError = '';
$successMsg = '';
$genericLoginError = 'Invalid email or password.';

//Ensure User Table Exists 
function ensureUserTableExists($conn) {
    $Usersql = "CREATE TABLE IF NOT EXISTS User (
        Userid INT AUTO_INCREMENT PRIMARY KEY,
        profile_picture VARCHAR(255) NULL, 
        firstname VARCHAR(30) NOT NULL,
        email VARCHAR(50) UNIQUE NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        password_hash VARCHAR(255) NOT NULL
    )";
    
    if ($conn->query($Usersql) === FALSE) {
        error_log('Database error creating User table: ' . $conn->error);
    }

    // Safety check in case column is missing
    $checkColumnSql = "SHOW COLUMNS FROM User LIKE 'profile_picture'";
    if ($conn->query($checkColumnSql)->num_rows == 0) {
        $alterSql = "ALTER TABLE User ADD profile_picture VARCHAR(255) NULL AFTER Userid";
        $conn->query($alterSql);
    }
}
ensureUserTableExists($conn);

//Handle POST Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $stmt = null; 

    //SIGNUP 
    /**Checks if action is signup,
     * Takes user input and assign it to variables
     * Performs validation using filters
     * Prepare statement for query of verifying the user email
     
     */ 
    if ($action === 'signup') {
        $firstname = trim($_POST['regName'] ?? '');
        $email = trim($_POST['regEmail'] ?? '');
        $password = $_POST['regPassword'] ?? '';
        $confirm = $_POST['passwordConfirm'] ?? '';
        $prefillEmail = $email; 
        $profilePicturePath = null;

        // Validation
        if (!$firstname || !$email || !$password || !$confirm) {
            $signupError = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signupError = 'Invalid email format.';
        } elseif ($password !== $confirm) {
            $signupError = 'Passwords do not match.';
        } 

        //Photo Upload Handling 
        /**
         * Checks if there are no signup errors before handling the profile photo upload.
         * Verifies that the uploaded file exists and that there were no upload errors.
         * Extracts important file information such as name, temporary path, and size.
         * Converts the file extension to lowercase and validates that it’s an allowed image type.
         * Ensures the uploaded image does not exceed the maximum file size limit (500KB).
         * Generates a unique filename for the uploaded photo to avoid conflicts.
         * Defines the destination directory for storing the uploaded profile picture.
         * Creates the upload directory if it doesn’t exist and assigns proper permissions.
         * Moves the uploaded file from its temporary location to the final destination.
         * If the upload succeeds, the path is stored for database insertion.
         * If any step fails (invalid type, too large, or permission error), 
         * an error message is stored in `$signupError`.
         */
        if ($signupError === '') {
            if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['profilePicture'];
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $maxFileSize = 500000; // 500 KB limit

                if (!in_array($fileExt, $allowed)) {
                    $signupError = 'Photo file type not allowed. Only JPG, JPEG, PNG, or WEBP.';
                } elseif ($fileSize > $maxFileSize) {
                    $signupError = 'Photo file size must be less than 500KB.';
                } else {
                    $fileNewName = uniqid('profile-', true) . '.' . $fileExt;
                    $fileDestination = UPLOAD_DIR . $fileNewName;

                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0777, true);
                    }

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $profilePicturePath = $fileDestination;
                    } else {
                        $signupError = 'Failed to upload photo. Check file permissions.';
                    }
                }
            }
        }

        //Database Registration
        /**
         * Executes only if there are no signup errors, including photo upload issues.
         * Prepares an SQL statement to insert the new user record into the database.
         * Binds user data (name, surname, email, password hash, and photo path) to the statement.
         * Executes the insertion and verifies if the operation was successful.
         * If successful, sets a success message or redirects the user to the login page.
         * If insertion fails (e.g., database connectivity or constraint errors),
         * sets an appropriate signup error message for user feedback.
         */
        
        if ($signupError === '') {
            $stmt = $conn->prepare("SELECT email FROM User WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $loginError = 'An account for this email already exists. Please log in.';
                $stmt->close();
            } else {
                $stmt->close();
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO User (profile_picture, firstname, email, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $profilePicturePath, $firstname, $email, $hashed);

                if ($stmt->execute()) {
                    $successMsg = 'Registration successful! You can now log in.';
                    $prefillEmail = ''; 
                } else {
                    $signupError = 'Signup failed. Database error.';
                }
            }
        }

        if ($signupError) {
            $showRegistrationForm = true;
        }
    }

    //Handle Login
    elseif ($action === 'login') {
        $email = trim($_POST['loginEmail'] ?? '');
        $password = $_POST['loginPassword'] ?? '';
        $prefillEmail = $email;

        if (!$email || !$password) {
            $loginError = $genericLoginError;
        } else {
            $stmt = $conn->prepare("SELECT Userid, firstname, password_hash FROM User WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true); 
                    $_SESSION['Userid'] = $user['Userid'];
                    $_SESSION['firstname'] = $user['firstname'];
                    header("Location: cartoon.php");
                    exit();
                } else {
                    $loginError = $genericLoginError;
                }
            } else {
                $loginError = 'No account found with that email. Please register below.';
                $showRegistrationForm = true;
            }
        }
    }

    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DOP{e} - Login / Registration</title>
    <link rel="stylesheet" href="wholestyle.css">
    <script src="javascript.js" defer></script> 
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
    <h2 style="color: #c71585;">User Login / Registration</h2>

    <h3 class="form-title">Login</h3>

    <?php if ($successMsg): ?>
        <div style="color: green; margin-bottom: 10px;">• <?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>
    
    <?php if ($loginError): ?>
        <div id="login-errors" class="loginError" style="color: red; margin-bottom: 10px;">• <?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="">
        <input type="hidden" name="action" value="login">

        <label for="loginEmail">Email:</label><br>
        <input type="email" id="loginEmail" name="loginEmail" placeholder="Enter your email" value="<?= htmlspecialchars($prefillEmail) ?>" required><br><br>

        <label for="loginPassword">Password:</label><br>
        <input type="password" id="loginPassword" name="loginPassword" placeholder="Enter your password" required> 
        <button type="button" class="toggle-password" data-target="loginPassword">Show</button><br><br>

        <input type="submit" value="Login" class="btn">
    </form>

    <br><br>

    <?php if ($showRegistrationForm || $signupError): ?>
        <h3 class="form-title">Register</h3>
        <?php if ($signupError): ?>
            <div id="registration-errors" class="signupError" style="color: red; margin-bottom: 10px;">• <?= htmlspecialchars($signupError) ?></div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="signup">

            <label for="regName">Name:</label><br>
            <input type="text" id="regName" name="regName" placeholder="Enter your name" value="<?= htmlspecialchars($firstname ?? '') ?>" required><br><br>

            <label for="regEmail">Email:</label><br>
            <input type="email" id="regEmail" name="regEmail" placeholder="Enter your email" value="<?= htmlspecialchars($prefillEmail) ?>" required><br><br>

            <label for="profilePicture">Profile Picture (JPG, PNG, WEBP - Max 500KB):</label><br>
            <input type="file" id="profilePicture" name="profilePicture" accept="image/jpeg, image/png, image/webp"><br><br>

            <label for="regPassword">Password:</label><br>
            <input type="password" id="regPassword" name="regPassword" placeholder="Enter your password" required>

            <label for="regPasswordConfirm">Confirm Password:</label><br>
            <input type="password" id="regPasswordConfirm" name="passwordConfirm" placeholder="Confirm password" required>
            <br><br>
            <button type="button" class="toggle-password" data-target="regPassword, regPasswordConfirm">Show</button>
            <br><br>

            <input type="submit" value="Register" class="btn"><br>
        </form>
    <?php endif; ?>
</main>

<footer style="text-align: center; background-color: #ffb6c1; padding: 15px; margin-top: 20px;">
    <p style="color: #8b008b;">Author: Sinethemba Ndwandwe, Sibongiseni Masilela, and Kulungile Vuso</p>
    <p style="color: #8a2be2;">&copy; 2025 Daily Outfit Planner. All rights reserved.</p>
</footer>

</body>
</html>
