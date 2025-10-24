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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FURRiendly | Events</title>
    <link rel="stylesheet" href="css/style.css">
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

    <main class="content">
        <h2>Upcoming Events</h2>
        <div class="search-right-container">
            <form action="events.php" method="GET" class="search-right-form">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search events..." 
                    class="search-input-right"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                >
            </form>
        </div>
        <div class="events-grid">
    <?php
    $events_stmt = $conn->prepare("
        SELECT * FROM events 
        WHERE status = 'approved' AND event_date >= CURDATE() 
        ORDER BY event_date ASC
    ");
    $events_stmt->execute();
    $events_result = $events_stmt->get_result();
    $approved_events = $events_result->fetch_all(MYSQLI_ASSOC);
    
    if (empty($approved_events)): ?>
        <div class="no-events">
            <div class="no-events-icon">📅</div>
            <h3>No Upcoming Events</h3>
            <p>Check back later for new events!</p>
        </div>
    <?php else: ?>
        <?php foreach ($approved_events as $event): ?>
        <div class="event-card">
            <div class="event-header">
                <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                <span class="event-service">Free Service</span>
            </div>
            
            <div class="event-body">
                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                
                <div class="event-details">
                    <div class="event-date">
                        <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                    </div>
                    <div class="event-time">
                        <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                    </div>
                    <div class="event-location">
                        <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                    </div>
                    <div class="event-host">
                        <strong>Host:</strong> <?php echo htmlspecialchars($event['full_name']); ?>
                    </div>
                </div>
                
                <?php $services = json_decode($event['services'], true); ?>
                <?php if (!empty($services)): ?>
                <div class="event-services">
                    <strong>Services:</strong>
                    <div class="service-tags">
                        <?php foreach ($services as $service): ?>
                            <span class="service-tag"><?php echo htmlspecialchars($service); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="event-footer">
                <a href="join_event.php?event_id=<?php echo $event['id']; ?>" class="join-btn">
                    Join Event
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
         <!-- Events Container -->
        <div class="events-container">
            <div class="events-placeholder">
                <p>Events will be posted here</p>
            </div>
        </div>
    </main>
    <div class="add-event-placeholder">
        <a href="add_event.php" class="add-event-static-btn">Add Event</a>
    </div>
</body>
</html>