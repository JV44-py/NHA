<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /hr_it_support/login.php');
    exit;
}
include 'config.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tickets WHERE id = $id");
    header('Location: /hr_it_support/ticketlist.php');
    exit;
}

// Search / filter
$search          = isset($_GET['search'])   ? mysqli_real_escape_string($conn, trim($_GET['search']))   : '';
$priority_filter = isset($_GET['priority']) ? mysqli_real_escape_string($conn, $_GET['priority'])        : '';
$status_filter   = isset($_GET['status'])   ? mysqli_real_escape_string($conn, $_GET['status'])          : '';

$where = 'WHERE 1=1';
if ($search)          $where .= " AND (client_name LIKE '%$search%' OR service_request_no LIKE '%$search%' OR department LIKE '%$search%')";
if ($priority_filter) $where .= " AND priority_level = '$priority_filter'";
if ($status_filter)   $where .= " AND status = '$status_filter'";

$result = mysqli_query($conn, "SELECT * FROM tickets $where ORDER BY id DESC");
if (!$result) {
    die('<div style="padding:20px;color:red;font-family:sans-serif;"><strong>Query error:</strong> ' . mysqli_error($conn) . '</div>');
}
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket List — NHA IT Support</title>
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
            <h1>Ticket List</h1>
            <div class="sub">Browse, search, and manage all support tickets.</div>
        </div>

        <!-- Toolbar / filter -->
        <form method="GET" action="ticketlist.php">
            <div class="toolbar" style="margin-bottom:0; border:1px solid var(--line); border-bottom:none;">
                <a href="create_ticket.php" class="btn btn-primary btn-sm">+ New Ticket</a>

                <input type="text" name="search" placeholder="Search name, request no., dept…"
                       value="<?= htmlspecialchars($search) ?>" style="min-width:180px; flex:1;">

                <select name="priority">
                    <option value="">All Priorities</option>
                    <option value="Low"      <?= $priority_filter === 'Low'      ? 'selected' : '' ?>>Low</option>
                    <option value="Medium"   <?= $priority_filter === 'Medium'   ? 'selected' : '' ?>>Medium</option>
                    <option value="High"     <?= $priority_filter === 'High'     ? 'selected' : '' ?>>High</option>
                    <option value="Critical" <?= $priority_filter === 'Critical' ? 'selected' : '' ?>>Critical</option>
                </select>

                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="Open"                 <?= $status_filter === 'Open'                 ? 'selected' : '' ?>>Open</option>
                    <option value="In-Progress"          <?= $status_filter === 'In-Progress'          ? 'selected' : '' ?>>In-Progress</option>
                    <option value="For Parts"            <?= $status_filter === 'For Parts'            ? 'selected' : '' ?>>For Parts</option>
                    <option value="For Replacement"      <?= $status_filter === 'For Replacement'      ? 'selected' : '' ?>>For Replacement</option>
                    <option value="Endorsed to Supplier" <?= $status_filter === 'Endorsed to Supplier' ? 'selected' : '' ?>>Endorsed to Supplier</option>
                    <option value="Unrepairable"         <?= $status_filter === 'Unrepairable'         ? 'selected' : '' ?>>Unrepairable</option>
                    <option value="Resolved"             <?= $status_filter === 'Resolved'             ? 'selected' : '' ?>>Resolved</option>
                    <option value="Closed"               <?= $status_filter === 'Closed'               ? 'selected' : '' ?>>Closed</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">Filter</button>

                <?php if ($search || $priority_filter || $status_filter): ?>
                    <a href="ticketlist.php" style="font-size:12px; color:var(--red); text-decoration:none;">✕ Clear</a>
                <?php endif; ?>

                <span class="toolbar-count"><?= $total ?> ticket<?= $total !== 1 ? 's' : '' ?></span>
            </div>
        </form>

        <!-- Table -->
        <div style="border:1px solid var(--line); overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request No.</th>
                        <th>Date</th>
                        <th>Client Name</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($total === 0): ?>
                    <tr><td colspan="9" class="empty-state">No tickets found.</td></tr>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($result)):
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
                        <td><?= htmlspecialchars($row['date_created']) ?></td>
                        <td><?= htmlspecialchars($row['client_name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><span class="badge <?= $pcls ?>"><?= htmlspecialchars($p) ?></span></td>
                        <td><span class="badge <?= $scls ?>"><?= htmlspecialchars($st) ?></span></td>
                        <td>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                <a href="#" class="btn btn-outline btn-sm"
                                   onclick="openModal(<?= htmlspecialchars(json_encode($row)) ?>); return false;">View</a>
                                <a href="update_ticket.php?id=<?= $row['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="ticketlist.php?delete=<?= $row['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this ticket? This cannot be undone.')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /page-content -->
</div><!-- /shell -->

<!-- VIEW MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal" id="modalBox">
        <div class="modal-header">
            <span>Ticket Details</span>
            <button class="modal-close" onclick="document.getElementById('modalOverlay').classList.remove('active')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-grid" id="modalGrid"></div>
            <div style="margin-top:12px; text-align:right;">
                <a href="#" id="modalEditLink" class="btn btn-primary btn-sm">Edit This Ticket →</a>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(row) {
    var grid = document.getElementById('modalGrid');
    function f(label, val) {
        return '<div class="modal-field"><label>' + label + '</label><span>' + (val || '—') + '</span></div>';
    }
    function fFull(label, val) {
        return '<div class="modal-field full"><label>' + label + '</label><span>' + (val || '—') + '</span></div>';
    }
    function head(title) {
        return '<div class="modal-section-head">' + title + '</div>';
    }

    var hw = [
        row.hw_install  == 1 ? 'Installation'           : '',
        row.hw_repair   == 1 ? 'Repair'                 : '',
        row.hw_assembly == 1 ? 'Assembly'               : '',
        row.hw_pm       == 1 ? 'Preventive Maintenance' : '',
        row.hw_others   == 1 ? 'Others'                 : '',
    ].filter(Boolean).join(', ') || '—';

    var sw = [
        row.sw_install == 1 ? 'Installation' : '',
        row.sw_repair  == 1 ? 'Repair'       : '',
        row.sw_update  == 1 ? 'Updating'     : '',
        row.sw_format  == 1 ? 'Formatting'   : '',
        row.sw_others  == 1 ? 'Others'       : '',
    ].filter(Boolean).join(', ') || '—';

    var nm = [
        row.nm_vc     == 1 ? 'Video Conferencing'  : '',
        row.nm_tu     == 1 ? 'Tune-up/OS Updating' : '',
        row.nm_vs     == 1 ? 'Virus Scanning'      : '',
        row.nm_ns     == 1 ? 'Network/Sharing'     : '',
        row.nm_others == 1 ? 'Others'              : '',
    ].filter(Boolean).join(', ') || '—';

    grid.innerHTML =
        head('Client Information') +
        f('Request No.', row.service_request_no) +
        f('Date', row.date_created) +
        fFull('Department', row.department) +
        f('Client Name', row.client_name) +
        f('Position', row.position_designation) +
        f('Contact', row.contact_number) +
        f('Email', row.email) +
        f('Priority', row.priority_level) +

        head('Hardware / Item') +
        f('Type', row.type) +
        f('Brand / Model', row.brand_model) +
        f('Warranty', row.warranty) +
        f('Property No.', row.property_number) +
        f('Serial No.', row.serial_number) +
        f('Year Acquired', row.year_acquired) +
        f('Active Directory', row.active_directory) +
        f('Memory Type', row.memory_type) +
        f('Memory Speed', row.memory_speed) +
        f('Storage Type', row.storage_type) +

        head('Support Type') +
        f('Support Type', row.support_type) +
        f('Hardware', hw) +
        f('Software', sw) +
        f('Network &amp; Maint.', nm) +

        head('Resolution') +
        fFull('Details / Scenario', row.details) +
        fFull('Diagnosis', row.diagnosis) +
        fFull('Actions Taken', row.actions_taken) +
        fFull('Technical Personnel', row.tech_personnel) +
        fFull('Accepted By', row.accepted_by);

    document.getElementById('modalEditLink').href = 'update_ticket.php?id=' + row.id;
    document.getElementById('modalOverlay').classList.add('active');
}

function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) {
        document.getElementById('modalOverlay').classList.remove('active');
    }
}
</script>

</body>
</html>