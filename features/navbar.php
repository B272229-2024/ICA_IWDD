<?php
$navbarLinks = '';

if (isset($_SESSION['user_id'])) {
    $navbarLinks .= <<< _LOGGEDIN
        <a href="user_queries.php">📁 My Queries</a>
        <a href="#" class="logout-link">🚪 Logout</a>
_LOGGEDIN;
} else {
    $navbarLinks .= <<< _LOGGEDOUT
        <a href="login_user.php">🔐 Login</a>
        <a href="register.php">📝 Register</a>
_LOGGEDOUT;
}

echo <<< _NAVBAR
<header>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo">
                <img src="https://bioinfmsc8.bio.ed.ac.uk/~s2756532/web_project/assets/images/favicon.png" alt="Protein Swirl" class="logo-icon">
                Protein Swirl
            </a>
        </div>
        <div class="nav-right">
            <a href="search_form.php">🔍 Search</a>

            <div class="dropdown">
                <button class="dropbtn">ℹ️ Info ▾</button>
                <div class="dropdown-content">
                    <a href="help.php">❓ Help</a>
                    <a href="about.php">ℹ️ About</a>
                    <a href="credits.php">📚 Credits</a>
                </div>
            </div>

            $navbarLinks
        </div>
    </nav>
</header>
_NAVBAR;
