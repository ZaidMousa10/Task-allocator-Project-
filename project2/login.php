<?php
session_start();
require './db.inc.php'; // Include the database connection file


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Prepare SQL statement to fetch user credentials and role
        $stmt = $pdo->prepare("SELECT id,username, password, role FROM users WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        

        // Validate username and password
        if ($user && $password) {
            // Store user details in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store the role for access control

            if ($user['role'] === 'Team Leader') {
                $_SESSION['team_leader_id'] = $user['id']; // Ensure 'id' is fetched from the database
            }
            

            // Redirect based on role
            if ($user['role'] === 'Manager') {
                header('Location: ./dashboard.php');
            } elseif ($user['role'] === 'Team Leader') {
                header('Location: ./team_leader_dashboard.php');
            }elseif ($user['role'] === 'Team Member') {
                    header('Location: ./team_dashboard.php');
            }
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred while trying to log in. Please try again later.";
        error_log($e->getMessage()); // Log the actual error for debugging purposes
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <aside class="side-nav">
            <nav>
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./register_step1.php">Register</a></li>
                </ul>
            </nav>
        </aside>
        <div class="content">
            <h1>Login</h1>
            <?php if (!empty($error)): ?>
                <div class="errors">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="button-container">
                    <button type="submit">Login</button>
                </div>
            </form>
            <p>Don't have an account? <a href="./register_step1.php">Register here</a></p>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
