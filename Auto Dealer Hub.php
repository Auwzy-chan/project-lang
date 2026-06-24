<?php
header('Content-Type: application/json');

// 1. Database Configuration for XAMPP
$servername = "localhost";
$username = "root";      // Default XAMPP username
$password = "";          // Default XAMPP password is empty
$dbname = "hub1";        // Use the MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

$action = trim($_POST['action'] ?? '');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    $conn->close();
    exit;
}

if ($action === 'signin') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (empty($email) || empty($pass)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
        $conn->close();
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare("SELECT full_name, password FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $conn->error]);
        $conn->close();
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->bind_result($full_name, $hashed_password);
    $stmt->fetch();

    if (!password_verify($pass, $hashed_password)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
        $stmt->close();
        $conn->close();
        exit;
    }

    echo json_encode(['success' => true, 'user' => $full_name]);
    $stmt->close();
    $conn->close();
    exit;
}

if ($action !== 'signup') {
    echo json_encode(['success' => false, 'error' => 'Unknown action.']);
    $conn->close();
    exit;
}

// Collect and sanitize input data
$full_name = trim($_POST['Full_Name'] ?? '');
$email     = trim($_POST['Email_Address'] ?? '');
$phone     = trim($_POST['Phone_Number'] ?? '');
$pass      = trim($_POST['Password'] ?? '');

// Basic Validation
if (empty($full_name) || empty($email) || empty($phone) || empty($pass)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    $conn->close();
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    $conn->close();
    exit;
}

// Securely hash the password (Never store plain text passwords!)
$hashed_password = password_hash($pass, PASSWORD_BCRYPT);

// Check if email already exists
$checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
if (!$checkEmail) {
    echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$checkEmail->store_result();

if ($checkEmail->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'An account with this email already exists.']);
    $checkEmail->close();
    $conn->close();
    exit;
}
$checkEmail->close();

// Prepare SQL statement to insert user data
$stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}
$stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'user' => $full_name]);
} else {
    echo json_encode(['success' => false, 'error' => 'Something went wrong. Please try again.']);
}

$stmt->close();
$conn->close();
exit;
?>