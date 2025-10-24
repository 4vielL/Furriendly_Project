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
    <title>Add Event - FURRiendly</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/add_event.css">
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
                        <a href="events.php?filter=joined">Joined Events</a>
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

    <main class="add-event-main">
        <div class="back-container">
            <a href="events.php" class="back-btn">
                <span class="back-arrow">←</span> Back
            </a>
        </div>

        <div class="add-event-container">
            <h1>Host an Event</h1>
            
            <form action="process_event.php" method="POST" enctype="multipart/form-data" class="event-form">
                
                <!-- Event Information Section -->
                <section class="form-section">
                    <h2>What event will you be hosting?</h2>
                    
                    <div class="form-group">
                        <label for="event_title">Event Title *</label>
                        <input type="text" id="event_title" name="event_title" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Date *</label>
                            <input type="date" id="event_date" name="event_date" required>
                            <small class="error-message" id="date-error"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_time">Start Time *</label>
                            <input type="time" id="start_time" name="start_time" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_time">End Time *</label>
                            <input type="time" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" required>
                    </div>

                    <div class="form-group">
                        <label>Services *</label>
                        <div class="services-container">
                            <div class="service-input-group">
                                <input type="text" class="service-input" placeholder="Enter service">
                                <button type="button" class="add-service-btn" onclick="addService()">Add Service</button>
                            </div>
                            <div id="services-list" class="services-list"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                </section>

                <!-- Personal Information Section -->
                <section class="form-section">
                    <h2>Personal Information</h2>
                    
                    <div class="disclaimer">
                        <p>We value your privacy and are committed to protecting your personal information. Any data collected on this website will only be used for its intended purpose and will not be shared with third parties.</p>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number *</label>
                        <input type="tel" id="contact_number" name="contact_number" pattern="[0-9]+" required>
                        <small>Numbers only</small>
                    </div>

                    <div class="form-group">
                        <label for="position">Position *</label>
                        <select id="position" name="position" required onchange="toggleIdUpload()">
                            <option value="">Select Position</option>
                            <option value="Government Official">Government Official</option>
                            <option value="Veterinarian">Veterinarian</option>
                            <option value="Furr Parent">Furr Parent</option>
                        </select>
                    </div>

                    <div id="id-upload-section" class="upload-section" style="display: none;">
                        <div class="form-group">
                            <label>Upload your ID *</label>
                            <input type="file" name="id_upload" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>

                    <div class="upload-section">
                        <div class="form-group">
                            <label>Upload Valid ID *</label>
                            <input type="file" name="valid_id" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label>Permit or Authorization (From local government) *</label>
                            <input type="file" name="permit" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label>List of Veterinarians and Partners (PDF) *</label>
                            <input type="file" name="veterinarians_list" accept=".pdf" required>
                        </div>

                        <div class="form-group">
                            <label>Safety and Cleanliness Plan *</label>
                            <input type="file" name="safety_plan" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">Add Event</button>
                </div>
            </form>
        </div>
    </main>

        <script src="js/add_event.js"></script>
</body>
</html>