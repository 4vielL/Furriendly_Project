<?php
session_start();
require '../php/db_connect.php';

if (!isset($_SESSION['username'])) {
    header('Location: auth.php');
    exit();
}

$username = $_SESSION['username'];
$user = null;
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get events hosted by user
$events_stmt = $conn->prepare("
    SELECT * FROM events 
    WHERE host_username = ? 
    ORDER BY 
        CASE status 
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
            WHEN 'completed' THEN 4
        END,
        event_date ASC
");
$events_stmt->bind_param("s", $username);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
$hosted_events = $events_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Hosting - FURRiendly</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/myhosting.css">
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
                <?php if ($user): ?>
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

    <main class="myhosting-main">
        <div class="back-container">
            <a href="events.php" class="back-btn">
                <span class="back-arrow">←</span> Back to Events
            </a>
        </div>

        <div class="myhosting-container">
            <h1>My Hosted Events</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-msg"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="hosting-stats">
                <div class="stat-card pending">
                    <h3>Pending</h3>
                    <p><?php echo count(array_filter($hosted_events, fn($e) => $e['status'] === 'pending')); ?></p>
                </div>
                <div class="stat-card approved">
                    <h3>Approved</h3>
                    <p><?php echo count(array_filter($hosted_events, fn($e) => $e['status'] === 'approved')); ?></p>
                </div>
                <div class="stat-card completed">
                    <h3>Completed</h3>
                    <p><?php echo count(array_filter($hosted_events, fn($e) => $e['status'] === 'completed')); ?></p>
                </div>
                <div class="stat-card rejected">
                    <h3>Rejected</h3>
                    <p><?php echo count(array_filter($hosted_events, fn($e) => $e['status'] === 'rejected')); ?></p>
                </div>
            </div>

            <div class="events-tabs">
                <button class="tab-btn active" onclick="filterEvents('all')">All Events</button>
                <button class="tab-btn" onclick="filterEvents('pending')">Pending</button>
                <button class="tab-btn" onclick="filterEvents('approved')">Approved</button>
                <button class="tab-btn" onclick="filterEvents('completed')">Completed</button>
                <button class="tab-btn" onclick="filterEvents('rejected')">Rejected</button>
            </div>

            <div class="events-grid">
                <?php if (empty($hosted_events)): ?>
                    <div class="no-events">
                        <div class="no-events-icon">🏠</div>
                        <h3>No Hosted Events</h3>
                        <p>You haven't hosted any events yet. Start by creating your first event!</p>
                        <a href="add_event.php" class="action-btn view-btn">Host an Event</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($hosted_events as $event): ?>
                    <div class="event-card" data-status="<?php echo $event['status']; ?>">
                        <div class="event-header">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                            <span class="event-status status-<?php echo $event['status']; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                        
                        <div class="event-details">
                            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            
                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                                </div>
                                <div class="event-meta-item">
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                </div>
                                <div class="event-meta-item">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            </div>
                            
                            <?php $services = json_decode($event['services'], true); ?>
                            <?php if (!empty($services)): ?>
                            <div class="event-services">
                                <span class="services-label">Services:</span>
                                <div class="service-tags">
                                    <?php foreach ($services as $service): ?>
                                        <span class="service-tag"><?php echo htmlspecialchars($service); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="event-actions">
                            <span class="event-date">Submitted: <?php echo date('M j, Y g:i A', strtotime($event['created_at'])); ?></span>
                            <?php if ($event['status'] === 'approved'): ?>
                                <button class="action-btn complete-btn" onclick="markCompleted(<?php echo $event['id']; ?>)">Mark as Completed</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/myhosting.js"></script>
</body>
</html>