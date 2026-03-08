<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';

// Get statistics
$total_tickets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tickets"))['count'];
$open_tickets  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tickets WHERE status = 'Open' OR status IS NULL OR status = ''"))['count'];
$in_progress   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tickets WHERE status = 'In-Progress'"))['count'];
$resolved      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tickets WHERE status = 'Resolved'"))['count'];
$closed        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tickets WHERE status = 'Closed'"))['count'];

// Recent tickets
$recent = mysqli_query($conn, "SELECT * FROM tickets ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NHA IT Support</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* ── Dashboard-specific styles ── */
        .dashboard-wrap {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #222;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        }

        /* Sidebar */
        .layout {
            display: flex;
            min-height: 60vh;
        }

        .sidebar {
            width: 190px;
            background: #2a2a2a;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            padding: 16px 0;
        }

        .sidebar a {
            color: #ccc;
            text-decoration: none;
            padding: 10px 20px;
            font-size: 12.5px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border-left: 3px solid transparent;
            transition: background 0.15s, color 0.15s;
            font-family: 'Source Sans 3', Arial, sans-serif;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #3a3a3a;
            color: #fff;
            border-left-color: #c9a000;
        }

        /* Main content */
        .main-content {
            flex: 1;
            padding: 24px;
            background: #faf8f4;
        }

        .welcome-bar {
            font-size: 13px;
            color: #555;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #ddd;
        }

        .welcome-bar strong { color: #222; }

        /* Section headings */
        .dash-section-title {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 13px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #2a2a2a;
            margin: 0 0 12px;
            padding-bottom: 4px;
            border-bottom: 2px solid #2a2a2a;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #2a2a2a;
            color: #fff;
            padding: 18px 14px;
            text-align: center;
            border: 1px solid #111;
        }

        .stat-card.open     { background: #8b1a1a; }
        .stat-card.progress { background: #7a6200; }
        .stat-card.resolved { background: #2a6a2a; }

        .stat-number {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 2.4em;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            opacity: 0.85;
        }

        /* Action cards */
        .action-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 28px;
        }

        .action-card {
            background: #fff;
            border: 1px solid #ccc;
            padding: 18px 12px;
            text-align: center;
            text-decoration: none;
            color: #222;
            transition: background 0.15s, border-color 0.15s;
            display: block;
        }

        .action-card:hover {
            background: #2a2a2a;
            color: #fff;
            border-color: #2a2a2a;
        }

        .action-icon { font-size: 2em; margin-bottom: 8px; }

        .action-title {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.03em;
        }

        .action-desc { font-size: 11px; opacity: 0.65; }
        .action-card:hover .action-desc { opacity: 0.85; color: #ddd; }

        /* Recent tickets table */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 24px;
        }

        .dash-table thead tr { background: #2a2a2a; color: #fff; }

        .dash-table thead th {
            padding: 7px 12px;
            text-align: left;
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 11px;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .dash-table tbody tr { border-bottom: 1px solid #e0ddd6; }
        .dash-table tbody tr:nth-child(even) { background: #faf8f4; }
        .dash-table tbody tr:hover { background: #f0ede6; }
        .dash-table tbody td { padding: 6px 12px; }

        /* Priority / Status badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid;
        }

        .badge-low      { color: #2a7a2a; border-color: #2a7a2a; background: #edfaed; }
        .badge-medium   { color: #7a6200; border-color: #c9a000; background: #fffbe6; }
        .badge-high     { color: #b84000; border-color: #e06000; background: #fff3eb; }
        .badge-critical { color: #fff;    border-color: #a00;    background: #a00; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
        }

        /* Footer */
        .dash-footer {
            text-align: center;
            font-size: 11px;
            color: #999;
            padding: 12px;
            border-top: 1px solid #ddd;
            background: #f0ede6;
        }

        @media (max-width: 750px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; flex-direction: row; flex-wrap: wrap; padding: 8px; }
            .sidebar a { border-left: none; border-bottom: 2px solid transparent; padding: 8px 12px; }
            .stats-grid, .action-cards { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="dashboard-wrap">

    <h1 class="title nha">
        National Housing Authority
        <sub>IT Support Dashboard</sub>
    </h1>

    <div class="layout">

        <?php include 'sidebar.php'; ?>

    <div class="main-content">

            <div class="welcome-bar">
                Welcome, <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                &nbsp;·&nbsp; <?= date('F d, Y') ?>
            </div>

            <!-- Stats -->
            <p class="dash-section-title">Quick Statistics</p>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_tickets ?></div>
                    <div class="stat-label">Total Tickets</div>
                </div>
                <div class="stat-card open">
                    <div class="stat-number"><?= $open_tickets ?></div>
                    <div class="stat-label">Open</div>
                </div>
                <div class="stat-card progress">
                    <div class="stat-number"><?= $in_progress ?></div>
                    <div class="stat-label">In Progress</div>
                </div>
                <div class="stat-card resolved">
                    <div class="stat-number"><?= $resolved ?></div>
                    <div class="stat-label">Resolved</div>
                </div>
                <div class="stat-card" style="background:#444;">
                    <div class="stat-number"><?= $closed ?></div>
                    <div class="stat-label">Closed</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <p class="dash-section-title">Quick Actions</p>
            <div class="action-cards">
                <a href="create_ticket.php" class="action-card">
                    <div class="action-icon">🎫</div>
                    <div class="action-title">New Ticket</div>
                    <div class="action-desc">Submit a support request</div>
                </a>
                <a href="ticket_list.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <div class="action-title">All Tickets</div>
                    <div class="action-desc">Browse all tickets</div>
                </a>
                <a href="ticket_list.php?priority=Critical" class="action-card">
                    <div class="action-icon">🔴</div>
                    <div class="action-title">Critical</div>
                    <div class="action-desc">View critical priority</div>
                </a>
                <a href="ticket_list.php?priority=High" class="action-card">
                    <div class="action-icon">🔧</div>
                    <div class="action-title">High Priority</div>
                    <div class="action-desc">View high priority</div>
                </a>
                <a href="update_ticket.php" class="action-card">
                    <div class="action-icon">&#x270e;</div>
                    <div class="action-title">Update Ticket</div>
                    <div class="action-desc">Change ticket status</div>
                </a>
            </div>

            <!-- Recent Tickets -->
            <p class="dash-section-title">Recent Tickets</p>
            <?php if (mysqli_num_rows($recent) > 0): ?>
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request No.</th>
                        <th>Client Name</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['service_request_no']) ?></td>
                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                        <td><span class="badge <?= get_priority_class($row['priority_level']) ?>"><?= htmlspecialchars($row['priority_level']) ?></span></td>
                        <td><?php
                            $st = $row['status'] ?? 'Open';
                            if (!$st) $st = 'Open';
                            $stcls = match($st) {
                                'Open'        => 'badge-high',
                                'In-Progress' => 'badge-medium',
                                'Resolved'    => 'badge-low',
                                'Closed'      => '',
                                default       => ''
                            };
                            $stbg = $st === 'Closed' ? 'display:inline-block;padding:2px 8px;font-size:10.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;border:1px solid #555;color:#555;background:#eee;' : '';
                        ?>
                        <?php if ($stbg): ?>
                            <span style="<?= $stbg ?>"><?= htmlspecialchars($st) ?></span>
                        <?php else: ?>
                            <span class="badge <?= $stcls ?>"><?= htmlspecialchars($st) ?></span>
                        <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['date_created']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">No tickets yet. <a href="create_ticket.php">Create the first one →</a></div>
            <?php endif; ?>

    </div><!-- /main-content -->

    <div class="dash-footer">
        &copy; <?= date('Y') ?> National Housing Authority IT Support System
    </div>

</div><!-- /dashboard-wrap -->
<script>
(function(){
    var btn=document.getElementById("sidebarToggle");
    var sidebar=document.getElementById("sidebar");
    var overlay=document.getElementById("sidebarOverlay");
    function openS(){ sidebar.classList.add("open"); overlay.classList.add("active"); }
    function closeS(){ sidebar.classList.remove("open"); overlay.classList.remove("active"); }
    btn.addEventListener("click",function(){ sidebar.classList.contains("open")?closeS():openS(); });
    overlay.addEventListener("click",closeS);
})();
</script>
</body>
</html>
