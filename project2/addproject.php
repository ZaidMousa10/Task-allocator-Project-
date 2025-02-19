<?php
session_start();
require './db.inc.php'; // Include the database connection file to initialize $pdo

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

$username = $_SESSION['username'];

// Check if the user is a Manager
$stmt = $pdo->prepare("SELECT role FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'Manager') {
    header('Location: ./unauthorized.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate Project ID
    if (empty($_POST['project_id']) || !preg_match('/^[A-Z]{4}-\d{5}$/', $_POST['project_id'])) {
        $errors['project_id'] = 'Project ID must start with 4 uppercase letters followed by a dash and 5 digits.';
    }

    // Validate Required Fields
    $requiredFields = ['project_title', 'description', 'customer_name', 'total_budget', 'start_date', 'end_date'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    // Validate Budget
    if (!empty($_POST['total_budget']) && (!is_numeric($_POST['total_budget']) || $_POST['total_budget'] <= 0)) {
        $errors['total_budget'] = 'Budget must be a positive numeric value.';
    }

    // Validate Dates
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        if (strtotime($_POST['end_date']) <= strtotime($_POST['start_date'])) {
            $errors['end_date'] = 'End Date must be later than Start Date.';
        }
    }

    // Validate File Uploads
    if (!empty($_FILES['supporting_documents']['name'][0])) {
        $fileCount = count($_FILES['supporting_documents']['name']);
        $titlesCount = count($_POST['document_title']);

        if ($fileCount > 3) {
            $errors['supporting_documents'] = 'You can upload a maximum of 3 files.';
        } elseif ($fileCount !== $titlesCount) {
            $errors['document_title'] = 'Each uploaded file must have a corresponding title.';
        } else {
            foreach ($_FILES['supporting_documents']['name'] as $index => $fileName) {
                $fileSize = $_FILES['supporting_documents']['size'][$index];
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                if ($fileSize > 2 * 1024 * 1024) {
                    $errors['supporting_documents'] = 'Each file must not exceed 2MB.';
                }
                if (!in_array(strtolower($fileType), ['pdf', 'docx', 'png', 'jpg'])) {
                    $errors['supporting_documents'] = 'Only PDF, DOCX, PNG, and JPG files are allowed.';
                }
            }
        }
    }

    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Save project details to the database
            $stmt = $pdo->prepare("INSERT INTO projects (project_id, project_title, description, customer_name, total_budget, start_date, end_date, manager_username) 
                                   VALUES (:project_id, :project_title, :description, :customer_name, :total_budget, :start_date, :end_date, :manager_username)");
            
            $stmt->execute([
                ':project_id' => $_POST['project_id'],
                ':project_title' => $_POST['project_title'],
                ':description' => $_POST['description'],
                ':customer_name' => $_POST['customer_name'],
                ':total_budget' => $_POST['total_budget'],
                ':start_date' => $_POST['start_date'],
                ':end_date' => $_POST['end_date'],
                ':manager_username' => $username
            ]);

            $projectId = $pdo->lastInsertId();

            // Handle file uploads
            if (!empty($_FILES['supporting_documents']['name'][0])) {
                foreach ($_FILES['supporting_documents']['name'] as $index => $fileName) {
                    $fileTmpPath = $_FILES['supporting_documents']['tmp_name'][$index];
                    $fileTitle = $_POST['document_title'][$index];
                    $destination = "./uploads/" . basename($fileName); // Unique file name

                    if (move_uploaded_file($fileTmpPath, $destination)) {
                        $stmt = $pdo->prepare("INSERT INTO project_files (project_id, file_name, file_title, file_path) VALUES (:project_id, :file_name, :file_title, :file_path)");
                        $stmt->execute([
                            ':project_id' => $projectId,
                            ':file_name' => basename($fileName),
                            ':file_title' => $fileTitle,
                            ':file_path' => $destination
                        ]);
                    }
                }
            }

            // Set the success message and redirect to a success page
            $_SESSION['success_message'] = 'Project successfully added.';
            header('Location: ./success_task.php');
            exit;
        } catch (PDOException $e) {
            $errors['database'] = 'Failed to save the project: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <aside class="side-nav">
            <nav>
                <ul>
                    <li><a href="./dashboard.php">Home</a></li>
                </ul>
            </nav>
        </aside>
        <div class="content">
            <h1>Add New Project</h1>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="project-form">
    <div class="form-group">
        <label for="project_id">Project ID:</label>
        <input type="text" id="project_id" name="project_id" placeholder="e.g., ABCD-12345" required>
    </div>
    <div class="form-group">
        <label for="project_title">Project Title:</label>
        <input type="text" id="project_title" name="project_title" required>
    </div>
    <div class="form-group">
        <label for="description">Project Description:</label>
        <textarea id="description" name="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required>
    </div>
    <div class="form-group">
        <label for="total_budget">Total Budget:</label>
        <input type="number" id="total_budget" name="total_budget" required>
    </div>
    <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
    </div>
    <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
    </div>
    <div class="form-group">
        <label for="file1">File 1:</label>
        <input type="file" id="file1" name="supporting_documents[]" accept=".pdf,.docx,.png,.jpg">
        <label for="title1">Title for File 1:</label>
        <input type="text" id="title1" name="document_title[]" placeholder="Title for File 1">
    </div>
    <div class="form-group">
        <label for="file2">File 2:</label>
        <input type="file" id="file2" name="supporting_documents[]" accept=".pdf,.docx,.png,.jpg">
        <label for="title2">Title for File 2:</label>
        <input type="text" id="title2" name="document_title[]" placeholder="Title for File 2">
    </div>
    <div class="form-group">
        <label for="file3">File 3:</label>
        <input type="file" id="file3" name="supporting_documents[]" accept=".pdf,.docx,.png,.jpg">
        <label for="title3">Title for File 3:</label>
        <input type="text" id="title3" name="document_title[]" placeholder="Title for File 3">
    </div>
    <button type="submit" class="submit-button">Add Project</button>
</form>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
