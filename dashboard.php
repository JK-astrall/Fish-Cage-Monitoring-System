<?php
require_once 'config.php';
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');

    // Fetch all cages
    $result = $conn->query("SELECT * FROM fish_cages ORDER BY cage_id");
    $cages = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($cages);
    exit();
}
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Cage Monitoring System</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <nav>
            <h1>Fish Cage Monitor</h1>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="cages.php">Fish Cages</a></li>
                <li><a href="feeding.php">Feeding Records</a></li>
                <li><a href="expenses.php">Expenses</a></li>
                <li><a href="income.php">Income</a></li>
                <?php if (isAdmin()): ?>
                <li><a href="employees.php">Employees</a></li>
                <?php endif; ?>
                <li><a href="login.php">Logout</a></li>
            </ul>
        </nav>

        <main>
            <div class="dashboard-grid">
                <div class="card">
                    <h2>Fish Cage Status</h2>
                    <div class="cage-status">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>

                <div class="card">
                    <h2>Feeding Distribution</h2>
                    <canvas id="feedingChart"></canvas>
                </div>

                <div class="card">
                    <h2>Financial Summary</h2>
                    <div class="financial-summary">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="script.js"></script>
</body>
</html>