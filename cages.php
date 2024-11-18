<!-- cages.php -->
<?php
require_once 'config.php';

// Check if the user is logged in for the HTML interface
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isAdmin()) {
                    $cage_name = trim($_POST['cage_name']);
                    $capacity = (int)$_POST['capacity'];
                    $current_fish = (int)$_POST['current_fish_count'];
                    $status = $_POST['status'];

                    $stmt = $conn->prepare("INSERT INTO fish_cages (cage_name, capacity, current_fish_count, status) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("siis", $cage_name, $capacity, $current_fish, $status);

                    if ($stmt->execute()) {
                        $message = "Cage added successfully!";
                    } else {
                        $error = "Error adding cage";
                    }
                } else {
                    $error = "Only administrators can add cages";
                }
                break;

            case 'update':
                $cage_id = (int)$_POST['cage_id'];
                $cage_name = trim($_POST['cage_name']);
                $capacity = (int)$_POST['capacity'];
                $current_fish = (int)$_POST['current_fish_count'];
                $status = $_POST['status'];

                $stmt = $conn->prepare("UPDATE fish_cages SET cage_name = ?, capacity = ?, current_fish_count = ?, status = ? WHERE cage_id = ?");
                $stmt->bind_param("siisi", $cage_name, $capacity, $current_fish, $status, $cage_id);

                if ($stmt->execute()) {
                    $message = "Cage updated successfully!";
                } else {
                    $error = "Error updating cage";
                }
                break;

            case 'delete':
                if (isAdmin()) {
                    $cage_id = (int)$_POST['cage_id'];

                    $stmt = $conn->prepare("DELETE FROM fish_cages WHERE cage_id = ?");
                    $stmt->bind_param("i", $cage_id);

                    if ($stmt->execute()) {
                        $message = "Cage deleted successfully!";
                    } else {
                        $error = "Error deleting cage";
                    }
                } else {
                    $error = "Only administrators can delete cages";
                }
                break;
        }
    }
}

// Fetch all cages for the HTML page
$result = $conn->query("SELECT * FROM fish_cages ORDER BY cage_id");
$cages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fish Cages Management - Fish Cage Monitoring System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="cage.css">
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
            <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Fish Cages Management</h2>
                <?php if (isAdmin() && count($cages) < 5): ?>
                <button class="btn" onclick="showAddModal()">Add New Cage</button>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="cage-grid">
                <?php foreach ($cages as $cage): ?>
                <div class="cage-card">
                    <h3>
                        <?php echo htmlspecialchars($cage['cage_name']); ?>
                        <span class="status-badge status-<?php echo strtolower($cage['status']); ?>">
                            <?php echo htmlspecialchars($cage['status']); ?>
                        </span>
                    </h3>
                    <div class="cage-stats">
                        <p>Capacity: <?php echo number_format($cage['capacity']); ?> fish</p>
                        <p>Current Fish: <?php echo number_format($cage['current_fish_count']); ?> fish</p>
                        <p>Utilization: <?php echo $cage['capacity'] > 0 ? round(($cage['current_fish_count'] / $cage['capacity']) * 100, 1) : 0; ?>%</p>
                    </div>
                    <div class="cage-actions">
                        <button class="btn btn-edit" onclick="showEditModal(<?php echo htmlspecialchars(json_encode($cage)); ?>)">Edit</button>
                        <?php if (isAdmin()): ?>
                        <button class="btn btn-delete" onclick="confirmDelete(<?php echo $cage['cage_id']; ?>)">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Cage Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideAddModal()">&times;</span>
            <h2>Add New Cage</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="cage_name">Cage Name</label>
                    <input type="text" id="cage_name" name="cage_name" required>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" required min="1">
                </div>
                <div class="form-group">
                    <label for="current_fish_count">Current Fish Count</label>
                    <input type="number" id="current_fish_count" name="current_fish_count" required min="0">
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn">Add Cage</button>
            </form>
        </div>
    </div>

    <!-- Edit Cage Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideEditModal()">&times;</span>
            <h2>Edit Cage</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="cage_id" id="edit_cage_id">
                <div class="form-group">
                    <label for="edit_cage_name">Cage Name</label>
                    <input type="text" id="edit_cage_name" name="cage_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_capacity">Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" required min="1">
                </div>
                <div class="form-group">
                    <label for="edit_current_fish_count">Current Fish Count</label>
                    <input type="number" id="edit_current_fish_count" name="current_fish_count" required min="0">
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn">Update Cage</button>
            </form>
        </div>
    </div>

    <script>
    function showAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    function hideAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }

    function showEditModal(cage) {
        document.getElementById('edit_cage_id').value = cage.cage_id;
        document.getElementById('edit_cage_name').value = cage.cage_name;
        document.getElementById('edit_capacity').value = cage.capacity;
        document.getElementById('edit_current_fish_count').value = cage.current_fish_count;
        document.getElementById('edit_status').value = cage.status.toLowerCase();
        document.getElementById('editModal').style.display = 'block';
    }

    function hideEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function confirmDelete(cageId) {
        if (confirm('Are you sure you want to delete this cage?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="cage_id" value="${cageId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
    
    </script>
</body>
</html>