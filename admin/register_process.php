<?php
// Include database connection
require_once '../database.php';

// Initialize error array
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register-submit'])) {

    // Sanitize and validate inputs
    $FullName = trim($_POST['FullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $BirthDate = $_POST['BirthDate'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $id_role = 3;
    $id_statut = 1;
    $date_added = date('Y-m-d H:i:s');
    $pictureBase64 = null;

    // Full Name validation
    if (empty($FullName)) {
        $errors[] = "Full name is required";
    }

    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Email already exists";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }

    // Birth date validation
    if (empty($BirthDate)) {
        $errors[] = "Birth date is required";
    }

    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // Handle file upload and convert to base64
    if (isset($_FILES['PictureLink']) && $_FILES['PictureLink']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['PictureLink'];

        // Validate file type
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);

        if (!array_key_exists($detectedType, $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image size must be less than 2MB";
        } else {
            // Convert the image to base64
            $imageData = file_get_contents($file['tmp_name']);
            $pictureBase64 = 'data:' . $detectedType . ';base64,' . base64_encode($imageData);
        }

        if ($_FILES['PictureLink']['error'] > 0 && $_FILES['PictureLink']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => "File is too large",
                UPLOAD_ERR_FORM_SIZE => "File is too large",
                UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
                UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
                UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
                UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
            ];
            $errors[] = $uploadErrors[$_FILES['PictureLink']['error']] ?? "Unknown upload error";
        }
    }

    // If no errors, insert into DB
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            $errors[] = "Failed to hash password";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (id_role, id_statut, FullName, BirthDate, email, password, date_added, Picture_Link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iissssss", $id_role, $id_statut, $FullName, $BirthDate, $email, $passwordHash, $date_added, $pictureBase64);
                if ($stmt->execute()) {
                    header("Location: confirm.php?name=" . urlencode($FullName));
                    exit();
                } else {
                    $errors[] = "Registration failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Database error: " . $conn->error;
            }
        }
    }
}

// Close connection
if (isset($conn) && $conn) {
    $conn->close();
}

// Display errors if any
if (!empty($errors)) {
    echo "<div style='color: red;'><ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul></div>";
}
?>
