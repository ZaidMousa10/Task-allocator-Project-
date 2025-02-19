<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $errors = [];
    if (empty($_POST['name'])) $errors[] = 'Name is required.';
    if (empty($_POST['flat']) || empty($_POST['street']) || empty($_POST['city']) || empty($_POST['country'])) $errors[] = 'Complete address is required.';
    if (empty($_POST['dob']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob']) || strtotime($_POST['dob']) >= time()) {
        $errors[] = 'Valid date of birth is required (the date should be before this date).';
    }
    if (empty($_POST['id_number']) || !is_numeric($_POST['id_number'])) $errors[] = 'Valid ID number is required.';
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email address is required.';
    if (empty($_POST['phone']) || !is_numeric($_POST['phone'])) $errors[] = 'Valid phone number is required.';
    if (empty($_POST['role'])) $errors[] = 'Role is required.';
    if (empty($_POST['qualification'])) $errors[] = 'Qualification is required.';
    if (empty($_POST['skills'])) $errors[] = 'Skills are required.';

    if (empty($errors)) {
        // Save data to session
        $_SESSION['step1'] = $_POST;
        header('Location: ./register_step2.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Step 1</title>
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
            <h1>Step 1: User Information</h1>
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
                    <legend>Personal Information</legend>

                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="flat">Flat/House No:</label>
                        <input type="text" id="flat" name="flat" placeholder="Flat/House No" required>
                    </div>

                    <div class="form-group">
                        <label for="street">Street:</label>
                        <input type="text" id="street" name="street" placeholder="Street" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" placeholder="City" required>
                    </div>

                    <div class="form-group">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" placeholder="Country" required>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>

                    <div class="form-group">
                        <label for="id_number">ID Number:</label>
                        <input type="text" id="id_number" name="id_number" required>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Contact Information</legend>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Professional Information</legend>

                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="Manager">Manager</option>
                            <option value="Team Leader">Team Leader</option>
                            <option value="Team Member">Team Member</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="qualification">Qualification:</label>
                        <input type="text" id="qualification" name="qualification" required>
                    </div>

                    <div class="form-group">
                        <label for="skills">Skills:</label>
                        <input type="text" id="skills" name="skills" required>
                    </div>
                </fieldset>

                <button type="submit">Proceed</button>
            </form>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
