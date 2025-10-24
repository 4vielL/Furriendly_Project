<?php
session_start();
require '../php/db_connect.php';

$isLoggedIn = isset($_SESSION['username']);
$user = null;

if ($isLoggedIn) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

// Get the active tab from URL parameter
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'joined';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - FURRiendly</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/joined_events.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <h4>FURRiendly</h4>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li class="events-dropdown">
                    <a href="events.php" class="events-link">Events</a>
                    <div class="events-dropdown-content">
                        <a href="events.php?filter=upcoming">Upcoming Events</a>
                        <a href="joined_events.php">Joined Events</a>
                    </div>
                </li>
                <li><a href="contact.php">Contact</a></li>
            </ul>

            <div class="auth-butt">
                <?php if ($isLoggedIn && $user): ?>
                    <div class="user-dropdown">
                        <a href="#" class="profile-link">
                            <img src="<?php echo htmlspecialchars(!empty($user['profile_pic']) ? $user['profile_pic'] : '../images/default-avatar.png'); ?>" alt="Profile">
                            <span class="profile-name"><?php echo htmlspecialchars($user['name'] ?? $username); ?></span>
                        </a>
                        <div class="dropdown-content">
                            <a href="dashboard.php">Edit Profile</a>
                            <a href="your_pet.php">Pet Profile</a>
                            <a href="events.php">Events</a>
                            <a href="myhosting.php">My Hosting</a>
                            <hr>
                            <a href="../php/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="auth.php" class="btn auth">Log-in/Sign-up</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="joined-events-main">
        <div class="back-container">
            <a href="events.php" class="back-btn">
                <span class="back-arrow">←</span> Back to Events
            </a>
        </div>

        <div class="joined-events-container">
            <h1>My Events</h1>
            
            <!-- Tabs Navigation -->
            <div class="events-tabs">
                <a href="?tab=joined" class="tab-btn <?php echo $active_tab == 'joined' ? 'active' : ''; ?>">
                    Joined Events
                </a>
                <a href="?tab=completed" class="tab-btn <?php echo $active_tab == 'completed' ? 'active' : ''; ?>">
                    Completed Events
                </a>
                <a href="?tab=canceled" class="tab-btn <?php echo $active_tab == 'canceled' ? 'active' : ''; ?>">
                    Canceled Events
                </a>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <?php if ($active_tab == 'joined'): ?>
                    <!-- Joined Events Tab -->
                    <div class="events-grid">
                        <?php include 'joined_events_content.php'; ?>
                    </div>
                
                <?php elseif ($active_tab == 'completed'): ?>
                    <!-- Completed Events Tab -->
                    <div class="events-grid">
                        <?php include 'completed_events_content.php'; ?>
                    </div>
                
                <?php elseif ($active_tab == 'canceled'): ?>
                    <!-- Canceled Events Tab -->
                    <div class="events-grid">
                        <?php include 'canceled_events_content.php'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>