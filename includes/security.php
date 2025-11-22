<?php
/**
 * Security helper functions for input validation and sanitization
 */

/**
 * Sanitize string input to prevent XSS
 * @param string $data The input string
 * @return string Sanitized string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate and sanitize email
 * @param string $email The email address
 * @return string|false Sanitized email or false if invalid
 */
function validate_email($email) {
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return false;
}

/**
 * Validate integer input
 * @param mixed $data The input value
 * @return int|false Validated integer or false if invalid
 */
function validate_int($data) {
    $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
    if (filter_var($data, FILTER_VALIDATE_INT)) {
        return (int)$data;
    }
    return false;
}

/**
 * Get POST value with sanitization
 * @param string $key The POST key
 * @param mixed $default Default value if not set
 * @return string Sanitized value
 */
function get_post($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_input($_POST[$key]) : $default;
}

/**
 * Get GET value with sanitization
 * @param string $key The GET key
 * @param mixed $default Default value if not set
 * @return string Sanitized value
 */
function get_get($key, $default = '') {
    return isset($_GET[$key]) ? sanitize_input($_GET[$key]) : $default;
}

/**
 * Escape output for HTML display
 * @param string $data The data to escape
 * @return string Escaped string
 */
function escape_html($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate password strength
 * @param string $password The password to validate
 * @return bool True if password is strong enough
 */
function validate_password($password) {
    // Minimum 8 characters, at least one letter and one number
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
}

/**
 * Validate and sanitize file upload
 * @param array $file $_FILES array element
 * @param array $allowed_extensions Allowed file extensions (e.g., ['jpg', 'png', 'gif'])
 * @param int $max_size Maximum file size in bytes
 * @param string $upload_dir Directory to upload to
 * @return array|false Returns array with 'filename' and 'path' on success, false on failure
 */
function validate_file_upload($file, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152, $upload_dir = 'images/') {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate extension
    if (!in_array($ext, $allowed_extensions)) {
        return false;
    }
    
    // Generate secure filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $path = $upload_dir . $filename;
    
    // Prevent path traversal
    $path = str_replace(['../', '..\\'], '', $path);
    
    return [
        'filename' => $filename,
        'path' => $path,
        'extension' => $ext
    ];
}

/**
 * Sanitize filename to prevent path traversal
 * @param string $filename The filename to sanitize
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove path components
    $filename = basename($filename);
    // Remove any non-alphanumeric characters except dots, hyphens, underscores
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token The token to verify
 * @return bool True if token is valid
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>

