<?php
session_start();
require_once "db_connect.php";

if (isset($_POST['loginBtn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header("Location: ../html/auth.php?error=All fields are required");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // ✅ Save session
            $_SESSION['username'] = $user['username'];

            // ✅ Check if profile is already complete
            if (!empty($user['name']) && !empty($user['email'])) {
                // Profile complete → Go to index
                header("Location: ../html/index.php");
            } else {
                // Profile incomplete → Go to dashboard
                header("Location: ../html/dashboard.php");
            }
            exit();

        } else {
            header("Location: ../html/auth.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: ../html/auth.php?error=User not found");
        exit();
    }
}
?>