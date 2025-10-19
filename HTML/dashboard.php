<?php
session_start();
require '../php/db_connect.php'; // database connection file

if (!isset($_SESSION['username'])) {
    header("Location: ../html/auth.php?error=Please log in first.");
    exit();
}

$username = $_SESSION['username'];
$isLoggedIn = true; // Since we check for session above

// Get current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// --- Handle Profile Update ---
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);

        $update_pic = $conn->prepare("UPDATE users SET profile_pic = ? WHERE username = ?");
        $update_pic->bind_param("ss", $target_file, $username);
        $update_pic->execute();
    }

    $update = $conn->prepare("UPDATE users SET name=?, email=?, bio=? WHERE username=?");
    $update->bind_param("ssss", $name, $email, $bio, $username);
    $update->execute();

    // Redirect to index.php after updating profile
    header("Location: ../html/index.php?success=ProfileUpdated");
    exit();
}

// (Pets feature removed from dashboard - simplified view)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FURRiendly | Dashboard</title>
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
  <div class="dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

    <?php if (!empty($user['profile_pic'])): ?>
      <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-pic" alt="Profile Picture">
    <?php else: ?>
      <img src="../images/default-avatar.png" class="profile-pic" alt="Default Profile Picture">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="profile-form">
      <label>Profile Picture:</label>
      <input type="file" name="profile_pic">

      <label>Name:</label>
      <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">

      <label>Email:</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

      <label>Bio:</label>
      <textarea name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

      <button type="submit" name="update_profile">Save Profile</button>
    </form>

  </div>
</div>
</body>
</html>