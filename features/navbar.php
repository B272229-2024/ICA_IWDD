<?php
$navbarLinks = '';

if (isset($_SESSION['user_id'])) {
    $navbarLinks .= <<< _LOGGEDIN
        <a href="user_queries.php">📁 My Queries</a>
        <a href="logout_user.php">🚪 Logout</a>
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
            <a href="index.php" class="logo">🌀 Protein Swirl</a>
        </div>
        <div class="nav-right">
            <a href="search_form.php">🔍 Search</a>
            $navbarLinks
        </div>
    </nav>
</header>
_NAVBAR;
