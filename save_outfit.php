<?php
session_start();
include "DbConn.php"; 

//1. AUTHENTICATION CHECK ---
if (!isset($_SESSION['Userid'])) {
    http_response_code(401); // Unauthorized
    echo "You must be logged in to save outfits.";
    exit();
}

// --- 2. INPUT VALIDATION ---
if (empty($_POST['outfit_path'])) {
    http_response_code(400); // Bad Request
    echo "No outfit data received.";
    exit();
}

$Userid = $_SESSION['Userid'];
$outfitPath = trim($_POST['outfit_path']); // Trim whitespace just in case

//3. ENSURE TABLE EXISTS
function ensureSaveOutfitTableExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS save_outfit (
        SaveOutfitID INT AUTO_INCREMENT PRIMARY KEY,
        Userid INT NOT NULL,
        outfitpath VARCHAR(255) NOT NULL,
        saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (Userid) REFERENCES User(Userid)
    )";

    if (!$conn->query($sql)) {
        error_log("Failed to create save_outfit table: " . $conn->error);
    }
}
ensureSaveOutfitTableExists($conn);

//4. INSERT RECORD SECURELY 
$stmt = $conn->prepare("INSERT INTO save_outfit (Userid, outfitpath) VALUES (?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo "Database error: Unable to prepare statement.";
    //error_log("Prepare failed: " . $conn->error);
    exit();
}

$stmt->bind_param("is", $Userid, $outfitPath);

if ($stmt->execute()) {
    http_response_code(200);
    echo "Outfit saved successfully!";
} else {
    http_response_code(500);
    echo "Failed to save outfit: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
