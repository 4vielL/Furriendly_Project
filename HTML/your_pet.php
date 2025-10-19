<?php
session_start();
require '../php/db_connect.php';

$isLoggedIn = isset($_SESSION['username']);
if (!$isLoggedIn) {
    header("Location: ../html/auth.php?error=Please log in first.");
    exit();
}

$username = $_SESSION['username'];
$user = null;

// Fetch user info for navbar
$stmt = $conn->prepare("SELECT name, profile_pic FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// --- Handle Pet Delete ---
if (isset($_POST['delete_pet'])) {
    $pet_id_to_delete = $_POST['pet_id'];
    if ($pet_id_to_delete) {
        $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND username = ?");
        $stmt->bind_param('is', $pet_id_to_delete, $username);
        $stmt->execute();
        header('Location: your_pet.php?success=deleted');
        exit();
    }
}

// Check if 'created_at' column exists to prevent SQL errors
$cols_result = $conn->query("SHOW COLUMNS FROM `pets` LIKE 'created_at'");
$has_created_at = $cols_result && $cols_result->num_rows > 0;
// Also check for pet_profile_pic
$cols_result_pic = $conn->query("SHOW COLUMNS FROM `pets` LIKE 'pet_profile_pic'");
$has_pet_pic = $cols_result_pic && $cols_result_pic->num_rows > 0;

$pic_col = $has_pet_pic ? 'pet_profile_pic' : 'NULL AS pet_profile_pic';

// Fetch user's pets
if ($has_created_at) {
    $sql = "SELECT id, pet_name, $pic_col FROM pets WHERE username = ? ORDER BY created_at DESC";
} else {
    // Fallback for older schema without 'created_at'
    $sql = "SELECT id, pet_name, $pic_col FROM pets WHERE username = ? ORDER BY id DESC";
}
$pets_stmt = $conn->prepare($sql);
$pets_stmt->bind_param('s', $username);
$pets_stmt->execute();
$pets = $pets_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FURRiendly | Your Pets</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-left">
                <h4>FURRiendly</h4>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="events.php">Events</a></li>
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
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <div class="dashboard-body">
        <main class="dashboard-container">
            <h2>Your Pets</h2>
            <?php if ($pets->num_rows > 0): ?>
                <div class="your-pets-grid">
                    <?php while ($pet = $pets->fetch_assoc()): ?>                        <div class="your-pet-card">
                            <a href="pets.php?edit_id=<?php echo $pet['id']; ?>" class="your-pet-card-link">
                                <img src="<?php echo htmlspecialchars(!empty($pet['pet_profile_pic']) ? $pet['pet_profile_pic'] : '../images/default-pet-avatar.png'); ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>">
                                <p><?php echo htmlspecialchars($pet['pet_name']); ?></p>
                            </a>
                            <form method="POST" class="delete-pet-form">
                                <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                <button type="submit" name="delete_pet" class="delete-pet-btn" onclick="return confirm('Are you sure you want to delete this pet? This action cannot be undone.');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                     <a href="pets.php" class="add-pet-card-small" title="Add another pet">+</a>
                </div>
            <?php else: ?>
                <div class="no-pets-container">
                    <p>You haven't added any pets yet.</p>
                    <a href="pets.php" class="add-pet-placeholder">
                        <span>+</span>
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>