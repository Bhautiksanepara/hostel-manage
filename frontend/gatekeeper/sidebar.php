<?php
$current_mode = $_GET['mode'] ?? 'out';
$current_mode = in_array($current_mode, ['out', 'in', 'leave'], true) ? $current_mode : 'out';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">H</div>
        <h2>Gatekeeper</h2>
    </div>
    <div class="sidebar-profile">
        <img src="../user/photos/Gpay.png" alt="Profile">
        <p>Gatekeeper Panel</p>
    </div>
    <ul class="sidebar-menu">
        <li><a href="gatekeeper.php?mode=out" class="<?php echo $current_mode === 'out' ? 'active' : ''; ?>"><i class="fa-solid fa-right-from-bracket"></i> Out</a></li>
        <li><a href="gatekeeper.php?mode=in" class="<?php echo $current_mode === 'in' ? 'active' : ''; ?>"><i class="fa-solid fa-right-to-bracket"></i> In</a></li>
        <li><a href="gatekeeper.php?mode=leave" class="<?php echo $current_mode === 'leave' ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-check"></i> Leave</a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>
