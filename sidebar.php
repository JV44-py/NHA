<?php
// sidebar.php — NHA IT Support navigation sidebar
$current = basename($_SERVER['PHP_SELF']);
?>
<button id="sidebarToggle" aria-label="Toggle sidebar">&#9776;</button>

<div id="sidebarOverlay"></div>

<nav id="sidebar" class="sidebar" aria-label="Main Navigation">

    <div class="sidebar-brand">
        <div class="brand-sub">NHA · IT Support</div>
        <div class="brand-name">National Housing<br>Authority</div>
    </div>

    <a href="index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">
        🏠&nbsp; Dashboard
    </a>

    <div class="sidebar-section">Tickets</div>

    <a href="create_ticket.php" class="<?= $current === 'create_ticket.php' ? 'active' : '' ?>">
        🎫&nbsp; New Ticket
    </a>

    <a href="ticketlist.php" class="<?= ($current === 'ticketlist.php' && !isset($_GET['priority'])) ? 'active' : '' ?>">
        📋&nbsp; All Tickets
    </a>

    <a href="update_ticket.php" class="<?= $current === 'update_ticket.php' ? 'active' : '' ?>">
        ✎&nbsp; Update Ticket
    </a>

    <div class="sidebar-section">Priority Views</div>

    <a href="ticketlist.php?priority=Critical"
       class="<?= (isset($_GET['priority']) && $_GET['priority'] === 'Critical') ? 'active' : '' ?>">
        🔴&nbsp; Critical
    </a>

    <a href="ticketlist.php?priority=High"
       class="<?= (isset($_GET['priority']) && $_GET['priority'] === 'High') ? 'active' : '' ?>">
        🔧&nbsp; High Priority
    </a>

    <a href="ticketlist.php?priority=Medium"
       class="<?= (isset($_GET['priority']) && $_GET['priority'] === 'Medium') ? 'active' : '' ?>">
        🟡&nbsp; Medium
    </a>

    <a href="ticketlist.php?priority=Low"
       class="<?= (isset($_GET['priority']) && $_GET['priority'] === 'Low') ? 'active' : '' ?>">
        🟢&nbsp; Low
    </a>

    <div class="sidebar-user">
        Logged in as
        <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
    </div>

    <a href="logout.php" class="sidebar-logout">
        🚪&nbsp; Logout
    </a>

</nav>

<script>
(function () {
    var btn     = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!btn || !sidebar || !overlay) return;

    function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('active'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }

    btn.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
})();
</script>