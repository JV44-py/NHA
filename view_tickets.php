<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Include database connection
include 'config.php';

// Get filter parameters
$filter_priority = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build WHERE clause
$where_clauses = [];
if (!empty($filter_priority)) {
    $where_clauses[] = "priority = '" . $conn->real_escape_string($filter_priority) . "'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = '" . $conn->real_escape_string($filter_status) . "'";
}
if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $where_clauses[] = "(issue_title LIKE '%$search_term%' OR issue_description LIKE '%$search_term%')";
}

$where = '';
if (!empty($where_clauses)) {
    $where = "WHERE " . implode(" AND ", $where_clauses);
}

// Fetch support tickets with sorting
$sql = "SELECT * FROM support_tickets $where ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tickets - HR IT Support</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Support Tickets Report</h1>
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></strong></p>
            <p>View and manage all IT support requests</p>
        </header>

        <button id="sidebarToggle" class="sidebar-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <a href="index.php">← Back to Dashboard</a>
            <a href="create_ticket.php">Create New Ticket</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Filter and Search Section -->
        <form method="GET" style="margin-bottom: 30px;">
            <div class="form-row">
                <div class="form-group">
                    <label for="search">Search by Title or Description</label>
                    <input type="text" id="search" name="search" 
                           placeholder="Search tickets..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group">
                    <label for="priority">Filter by Priority</label>
                    <select id="priority" name="priority">
                        <option value="">All Priorities</option>
                        <option value="Low" <?php echo $filter_priority == 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $filter_priority == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $filter_priority == 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Filter by Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="Open" <?php echo $filter_status == 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="In-Progress" <?php echo $filter_status == 'In-Progress' ? 'selected' : ''; ?>>In-Progress</option>
                        <option value="Resolved" <?php echo $filter_status == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
            </div>
            <button type="submit">🔍 Search & Filter</button>
            <a href="view_tickets.php" style="margin-left: 10px; padding: 12px 30px; background: #999; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">Clear Filters</a>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Device ID</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['device_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_title']); ?></td>
                            <td><span class="<?php echo get_priority_class($row['priority']); ?>"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                            <td><span class="<?php echo get_status_class($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td><?php echo date('M d, Y | H:i', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No tickets found</h3>
                <p>Try adjusting your filters or <a href="create_ticket.php" style="color: #667eea; text-decoration: none;">create a new ticket</a></p>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Total Tickets Found: <strong><?php echo $result->num_rows; ?></strong></p>
            <p>&copy; 2026 HR IT Support System. All rights reserved.</p>
        </div>
    </div>
    <script>
    // sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.body.classList.toggle('sidebar-open');
    });
    </script>
</body>
</html>
