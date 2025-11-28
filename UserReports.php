<?php
session_start();
require_once 'DbConn.php';

// Redirect if not logged in


$userId = (int) $_SESSION['Userid'];

try {
    // Report 1: Daily Outfit Saves (Last 30 days)
    $stmt = $conn->prepare("
        SELECT 
          DATE(saved_at) AS day,
          COUNT(*) AS save_count
        FROM save_outfit
        WHERE saved_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(saved_at)
        ORDER BY day ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $daily_saves = [];
    while ($row = $result->fetch_assoc()) {
        $daily_saves[$row['day']] = (int)$row['save_count'];
    }

    // Report 2: Daily Reviews (Last 30 days)
    $stmt = $conn->prepare("
        SELECT 
          DATE(submission_date) AS day,
          COUNT(*) AS review_count
        FROM review
        WHERE submission_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(submission_date)
        ORDER BY day ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $daily_reviews = [];
    while ($row = $result->fetch_assoc()) {
        $daily_reviews[$row['day']] = (int)$row['review_count'];
    }

    // Totals
    $total_saves_all_time = $conn->query("SELECT COUNT(*) FROM save_outfit")->fetch_row()[0];
    $total_reviews_all_time = $conn->query("SELECT COUNT(*) FROM review")->fetch_row()[0];

    // Prepare timeline for last 30 days
    $dates = [];  // labels
    $saves_data = [];
    $reviews_data = [];

    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $dates[] = date('M j', strtotime($d));  // e.g. Oct 12, or “Oct 12”
        $saves_data[] = isset($daily_saves[$d]) ? $daily_saves[$d] : 0;
        $reviews_data[] = isset($daily_reviews[$d]) ? $daily_reviews[$d] : 0;
    }

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daily Engagement Reports</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Example styling loosely matching a planner theme */
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #fdf8f8;
      color: #333;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    header {
      background: #ff69b4;
      color: white;
      padding: 20px;
      text-align: center;
    }
    header h1 {
      margin: 0;
      font-weight: 500;
    }
    .stats {
      display: flex;
      justify-content: space-around;
      padding: 20px;
      background: #fff0f5;
    }
    .stat {
      text-align: center;
    }
    .stat .num {
      font-size: 2em;
      font-weight: bold;
      color: #c71585;
    }
    .charts {
      padding: 20px;
    }
    .chart-box {
      margin-bottom: 30px;
    }
    .chart-box h2 {
      color: #c71585;
      margin-bottom: 10px;
      font-size: 1.2em;
    }
    canvas {
      width: 100% !important;
      height: 300px !important;
    }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <h1>📆 Daily Engagement</h1>
      <p>Last 30 Days — Outfit Saves & Reviews</p>
    </header>

    <div class="stats">
      <div class="stat">
        <div class="num"><?php echo $total_saves_all_time; ?></div>
        <div>All-Time Saves</div>
      </div>
      <div class="stat">
        <div class="num"><?php echo $total_reviews_all_time; ?></div>
        <div>All-Time Reviews</div>
      </div>
    </div>

    <div class="charts">
      <div class="chart-box">
        <h2>Outfits Saved (Daily)</h2>
        <canvas id="savesChart"></canvas>
      </div>
      <div class="chart-box">
        <h2>User Reviews (Daily)</h2>
        <canvas id="reviewsChart"></canvas>
      </div>
    </div>
  </div>

  <script>
    const dates = <?php echo json_encode($dates); ?>;
    const savesData = <?php echo json_encode($saves_data); ?>;
    const reviewsData = <?php echo json_encode($reviews_data); ?>;

    const commonOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        },
        x: {
          ticks: {
            maxRotation: 45,
            minRotation: 0
          }
        }
      }
    };

    // Outfits Saved Chart
    new Chart(document.getElementById('savesChart'), {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Saves',
          data: savesData,
          borderColor: '#e74c3c',
          backgroundColor: 'rgba(231, 76, 60, 0.2)',
          tension: 0.3,
          fill: true,
        }]
      },
      options: commonOptions
    });

    // Reviews Chart
    new Chart(document.getElementById('reviewsChart'), {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Reviews',
          data: reviewsData,
          borderColor: '#2ecc71',
          backgroundColor: 'rgba(46, 204, 113, 0.2)',
          tension: 0.3,
          fill: true,
        }]
      },
      options: commonOptions
    });
  </script>

</body>
</html>
