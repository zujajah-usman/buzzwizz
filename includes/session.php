<?php
	include 'includes/conn.php';
	
	// SECURITY: Configure session settings
	if (session_status() === PHP_SESSION_NONE) {
		// Use secure session settings
		ini_set('session.cookie_httponly', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
		ini_set('session.cookie_samesite', 'Strict');
		
		session_start();
		
		// Regenerate session ID periodically to prevent session fixation
		if (!isset($_SESSION['created'])) {
			$_SESSION['created'] = time();
		} else if (time() - $_SESSION['created'] > 1800) {
			// Regenerate session ID every 30 minutes
			session_regenerate_id(true);
			$_SESSION['created'] = time();
		}
	}

	if(isset($_SESSION['admin'])){
		header('location: admin/home.php');
	}

	if(isset($_SESSION['user'])){
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
			$stmt->execute(['id'=>$_SESSION['user']]);
			$user = $stmt->fetch();
		}
		catch(PDOException $e){
			// SECURITY: Don't expose database errors
			// Log error instead of displaying
			error_log("Session error: " . $e->getMessage());
		}

		$pdo->close();
	}
?>