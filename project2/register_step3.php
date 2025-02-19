<?php
session_start();
require './db.inc.php'; // Include the database connection file

// Redirect to step 1 if session variables are missing
if (!isset($_SESSION['step1']) || !isset($_SESSION['step2'])) {
    header('Location: ./register_step1.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Retrieve session data
        $step1 = $_SESSION['step1'];
        $step2 = $_SESSION['step2'];

        // Concatenate the address fields
        $address = "{$step1['flat']}, {$step1['street']}, {$step1['city']}, {$step1['country']}";

        // Check if email, ID number, or username already exists in the database
        $checkStmt = $pdo->prepare("SELECT 
                                        CASE 
                                            WHEN email = :email THEN 'email'
                                            WHEN id_number = :id_number THEN 'id_number'
                                            WHEN username = :username THEN 'username'
                                        END AS duplicate_field
                                    FROM users
                                    WHERE email = :email 
                                        OR id_number = :id_number 
                                        OR username = :username
                                    LIMIT 1");
        $checkStmt->execute([
            ':email' => $step1['email'],
            ':id_number' => $step1['id_number'],
            ':username' => $step2['username']
        ]);

        $duplicateField = $checkStmt->fetchColumn();

        if ($duplicateField) {
            // Handle specific duplicate field errors
            if ($duplicateField === 'email') {
                throw new Exception("The email address '{$step1['email']}' is already in use. Please use a different email.");
            } elseif ($duplicateField === 'id_number') {
                throw new Exception("The ID number '{$step1['id_number']}' is already registered. Please use a unique ID number.");
            } elseif ($duplicateField === 'username') {
                throw new Exception("The username '{$step2['username']}' is already taken. Please choose a different username.");
            } else {
                throw new Exception("A duplicate entry was found, but the field couldn't be determined.");
            }
        }

        // Prepare SQL statement to insert user data
        $stmt = $pdo->prepare("INSERT INTO users 
            (full_name, address, date_of_birth, id_number, email, phone_number, role, qualification, skills, username, password) 
            VALUES 
            (:full_name, :address, :date_of_birth, :id_number, :email, :phone_number, :role, :qualification, :skills, :username, :password)");

        // Bind parameters
        $stmt->bindParam(':full_name', $step1['name']);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':date_of_birth', $step1['dob']);
        $stmt->bindParam(':id_number', $step1['id_number']);
        $stmt->bindParam(':email', $step1['email']);
        $stmt->bindParam(':phone_number', $step1['phone']);
        $stmt->bindParam(':role', $step1['role']);
        $stmt->bindParam(':qualification', $step1['qualification']);
        $stmt->bindParam(':skills', $step1['skills']);
        $stmt->bindParam(':username', $step2['username']);
        $stmt->bindParam(':password', $step2['password']); // Bind password

        // Execute the statement
        $stmt->execute();

        // Retrieve the auto-incremented user ID
        $userID = $pdo->lastInsertId();

        // Save success message in session
        $_SESSION['success_message'] = "Registration Successful! Your User ID is: $userID";

        // Redirect to success page
        header('Location:./success.php');

        exit;
    } catch (Exception $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 3</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <aside class="side-nav">
            <ul>
                <li><a href="./index.php">Home</a></li>
            </ul>
        </aside>
        <div class="content">
            <h1>Step 3: Confirmation</h1>
            <form method="POST">
                <p>Review your details below:</p>
                <table class="details-table">
                    <?php 
                    foreach (array_merge($_SESSION['step1'], $_SESSION['step2']) as $key => $value): 
                        if ($key !== 'password' && $key !== 'confirm_password'): ?>
                        <tr>
                            <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</th>
                            <td><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endif; endforeach; ?>
                </table>
                <div class="button-container">
                    <button type="submit" class="submit-button">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
