<?php
session_start();
if (!isset($_SESSION['step1'])) {
    header('Location: ./register_step1.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $errors = [];
    if (empty($_POST['username']) || strlen($_POST['username']) < 6 || strlen($_POST['username']) > 13) {
        $errors[] = 'Username must be between 6-13 characters.';
    }
    if (empty($_POST['password']) || strlen($_POST['password']) < 8 || strlen($_POST['password']) > 12) {
        $errors[] = 'Password must be between 8-12 characters.';
    }
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Save data to session
        $_SESSION['step2'] = $_POST;
        header('Location: ./register_step3.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 2</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <aside class="side-nav">
            <ul>
                <li><a href="./index.php">Home</a></li>
                <li><a href="./register_step1.php">Register</a></li>
            </ul>
        </aside>
        <div class="content">
            <h1>Step 2: Create E-Account</h1>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST">
                <fieldset>
                    <legend>E-Account Information</legend>

                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </fieldset>

                <button type="submit">Proceed to Confirmation</button>
            </form>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
