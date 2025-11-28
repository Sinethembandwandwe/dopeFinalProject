<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['Userid'])) {
    header('Location: login.php');
    exit();
}

include "DbConn.php";

$userId = $_SESSION['Userid'];
$username = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : 'User'; // Fallback if session var not set

$outfits = []; // Default value to avoid count(null) errors

$stmt = $conn->prepare("
    SELECT save_outfit.outfitpath, save_outfit.saved_at, User.firstname 
    FROM save_outfit 
    JOIN User ON save_outfit.Userid = User.Userid 
    WHERE save_outfit.Userid = ? 
    ORDER BY saved_at DESC
");

if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $outfits[] = $row;
    }

    $stmt->close();
} else {
    error_log("Query preparation failed: " . $conn->error);
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($username); ?>’s Saved Outfits</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      background: #fdfcfa;
      font-family: 'Poppins', sans-serif;
      color: #333;
    }

    h1 {
      text-align: center;
      color: #e75480;
      font-weight: 600;
      margin-top: 40px;
      font-size: 2.5rem;
    }

    .header-buttons {
      display: flex;
      justify-content: flex-end;
      padding: 20px 40px;
      gap: 15px;
    }

    .btn {
      background: #e75480;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: #d4446f;
    }

    .gallery {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 40px;
      padding: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .outfit-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.06);
      padding: 20px;
      position: relative;
      transition: transform 0.3s ease;
    }

    .outfit-card:hover {
      transform: translateY(-5px);
    }

    .viewer {
      width: 100%;
      height: 320px;
      border-radius: 10px;
      overflow: hidden;
      background: #f0f0f0;
    }

    .timestamp {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
      color: #888;
    }

    .empty-message {
      text-align: center;
      margin-top: 100px;
      font-size: 18px;
      color: #999;
    }

    @media (max-width: 600px) {
      .btn {
        padding: 8px 12px;
        font-size: 13px;
      }
    }
  </style>

  <!-- Load Three.js and GLTFLoader -->
  <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
</head>
<body>

  <div class="header-buttons">
    <button class="btn" onclick="window.location.href='index%20.php'">Home</button>
    <button class="btn" onclick="window.location.href='logout.php'">Logout</button>
  </div>

  <h1><?php echo htmlspecialchars($username); ?>’s Saved Outfits</h1>

  <div class="gallery">
    <?php if (count($outfits) === 0): ?>
      <p class="empty-message">You haven’t saved any outfits yet 💔</p>
    <?php else: ?>
      <?php foreach ($outfits as $i => $outfit): ?>
        <div class="outfit-card">
          <div class="viewer" id="viewer<?php echo $i; ?>"></div>
          <div class="timestamp">Saved at: <?php echo htmlspecialchars($outfit['saved_at']); ?></div>
        </div>
        <script>
        (function() {
          const container = document.getElementById("viewer<?php echo $i; ?>");
          const scene = new THREE.Scene();
          const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 1000);
          const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
          renderer.setSize(container.clientWidth, container.clientHeight);
          container.appendChild(renderer.domElement);

          // Lighting
          const light = new THREE.DirectionalLight(0xffffff, 1);
          light.position.set(1, 1, 1).normalize();
          scene.add(light);

          // Load the GLB model
          const loader = new THREE.GLTFLoader();
          const outfitPath = "<?php echo htmlspecialchars($outfit['outfitpath']); ?>";
          loader.load(
            outfitPath,
            function(gltf) {
              scene.add(gltf.scene);
              camera.position.set(0, 1, 3);
              animate();
            },
            undefined,
            function(error) {
              container.innerHTML = "<div style='color:red;text-align:center;margin-top:120px;'>Model not found</div>";
            }
          );

          function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
          }
        })();
        </script>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>
