<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hr_it_support/login.php');
    exit;
}
include 'config.php';

// Statistics
$total_tickets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets"))['c'];
$open_tickets  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status = 'Open' OR status IS NULL OR status = ''"))['c'];
$in_progress   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status = 'In-Progress'"))['c'];
$resolved      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status = 'Resolved'"))['c'];
$closed        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM tickets WHERE status = 'Closed'"))['c'];

// Recent tickets
$recent = mysqli_query($conn, "SELECT * FROM tickets ORDER BY id DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NHA IT Support</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Source+Sans+3:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="shell">

    <?php include 'sidebar.php'; ?>

    <div class="page-content">

        <div class="page-header">
            <div class="eyebrow">National Housing Authority — IT Support</div>
            <h1>Dashboard</h1>
            <div class="sub">Welcome back, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong> &nbsp;·&nbsp; <?= date('F d, Y') ?></div>
        </div>

        <!-- Statistics -->
        <p class="dash-section-title">Quick Statistics</p>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_tickets ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>
            <div class="stat-card stat-open">
                <div class="stat-number"><?= $open_tickets ?></div>
                <div class="stat-label">Open</div>
            </div>
            <div class="stat-card stat-progress">
                <div class="stat-number"><?= $in_progress ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card stat-resolved">
                <div class="stat-number"><?= $resolved ?></div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card stat-closed">
                <div class="stat-number"><?= $closed ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <p class="dash-section-title">Quick Actions</p>
        <div class="action-grid">
            <a href="create_ticket.php" class="action-card">
                <div class="action-icon">🎫</div>
                <div class="action-title">New Ticket</div>
                <div class="action-desc">Submit a support request</div>
            </a>
            <a href="ticketlist.php" class="action-card">
                <div class="action-icon">📋</div>
                <div class="action-title">All Tickets</div>
                <div class="action-desc">Browse all tickets</div>
            </a>
            <a href="ticketlist.php?priority=Critical" class="action-card">
                <div class="action-icon">🔴</div>
                <div class="action-title">Critical</div>
                <div class="action-desc">View critical tickets</div>
            </a>
            <a href="ticketlist.php?priority=High" class="action-card">
                <div class="action-icon">🔧</div>
                <div class="action-title">High Priority</div>
                <div class="action-desc">View high priority</div>
            </a>
            <a href="update_ticket.php" class="action-card">
                <div class="action-icon">✎</div>
                <div class="action-title">Update Ticket</div>
                <div class="action-desc">Change ticket status</div>
            </a>
        </div>

        <!-- Recent Tickets -->
        <p class="dash-section-title">Recent Tickets</p>
        <?php if (mysqli_num_rows($recent) > 0): ?>
        <div style="border:1px solid var(--line); overflow-x:auto; margin-bottom:24px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request No.</th>
                        <th>Client Name</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent)):
                    $p    = $row['priority_level'] ?? '';
                    $pcls = match(strtolower($p)) {
                        'low'      => 'badge-low',
                        'medium'   => 'badge-medium',
                        'high'     => 'badge-high',
                        'critical' => 'badge-critical',
                        default    => ''
                    };
                    $st   = $row['status'] ?: 'Open';
                    $scls = match(strtolower(trim($st))) {
                        'open'                 => 'badge-open',
                        'in-progress'          => 'badge-in-progress',
                        'for parts'            => 'badge-parts',
                        'for replacement'      => 'badge-replacement',
                        'endorsed to supplier' => 'badge-supplier',
                        'unrepairable'         => 'badge-unrepairable',
                        'resolved'             => 'badge-resolved',
                        'closed'               => 'badge-closed',
                        default                => 'badge-open'
                    };
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['service_request_no']) ?></td>
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><span class="badge <?= $pcls ?>"><?= htmlspecialchars($p) ?></span></td>
                    <td><span class="badge <?= $scls ?>"><?= htmlspecialchars($st) ?></span></td>
                    <td><?= htmlspecialchars($row['date_created']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div style="text-align:right; margin-bottom:24px;">
            <a href="ticketlist.php" class="btn btn-outline btn-sm">View All Tickets →</a>
        </div>
        <?php else: ?>
            <div class="empty-state">
                No tickets yet. <a href="create_ticket.php">Create the first one →</a>
            </div>
        <?php endif; ?>

        <div class="dash-footer">
            &copy; <?= date('Y') ?> National Housing Authority IT Support System
        </div>

    </div><!-- /page-content -->
</div><!-- /shell -->

</body>
</html>