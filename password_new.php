<?php
	include 'includes/session.php';
	require_once 'includes/security.php';
   
	if(!isset($_GET['code']) OR !isset($_GET['user'])){
		header('location: index.php');
	    exit(); 
	}

	// Sanitize GET parameters
	$code = sanitize_input($_GET['code']);
	$user_id = validate_int($_GET['user']);
	
	if ($user_id === false) {
		header('location: index.php');
		exit();
	}

	$path = 'password_reset.php?code='.urlencode($code).'&user='.$user_id;

	if(isset($_POST['reset'])){
		$password = $_POST['password'] ?? '';
		$repassword = $_POST['repassword'] ?? '';
		
		// Validate password strength
		if (empty($password) || !validate_password($password)) {
			$_SESSION['error'] = 'Password must be at least 8 characters with letters and numbers';
			header('location: '.$path);
			exit();
		}

		if($password != $repassword){
			$_SESSION['error'] = 'Passwords did not match';
			header('location: '.$path);
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE reset_code=:code AND id=:id");
			$stmt->execute(['code'=>$code, 'id'=>$user_id]);
			$row = $stmt->fetch();

			if($row['numrows'] > 0){
				$password = password_hash($password, PASSWORD_DEFAULT);

				try{
					$stmt = $conn->prepare("UPDATE users SET password=:password WHERE id=:id");
					$stmt->execute(['password'=>$password, 'id'=>$row['id']]);

					$_SESSION['success'] = 'Password successfully reset';
					header('location: login.php');
				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
					header('location: '.$path);
				}
			}
			else{
				$_SESSION['error'] = 'Code did not match with user';
				header('location: '.$path);
			}

			$pdo->close();
		}

	}
	else{
		$_SESSION['error'] = 'Input new password first';
		header('location: '.$path);
	}

?>