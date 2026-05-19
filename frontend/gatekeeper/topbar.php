<div class="topbar">
    <div class="topbar-left">
        <button class="toggle-btn" type="button" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <span class="toggle-btn-icon">&#9776;</span>
        </button>
        <h1 class="topbar-logo"><a href="gatekeeper.php?mode=<?php echo htmlspecialchars($current_mode); ?>">Gatekeeper</a></h1>
    </div>
    <div class="topbar-right">
        <div class="topbar-user">
            <img src="../../photos/profile_photo.jpg" alt="Profile Picture">
            <span>Gatekeeper</span>
        </div>
        <a href="logout.php" class="topbar-logout" aria-label="Logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
