<?php
	include 'includes/session.php';

	require_once '../includes/security.php';
	
	if(isset($_GET['return'])){
		$return = sanitize_input($_GET['return']);
		// Prevent path traversal
		$return = basename($return);
		if (empty($return) || !preg_match('/^[a-zA-Z0-9_\-\.]+\.php$/', $return)) {
			$return = 'home.php';
		}
	}
	else{
		$return = 'home.php';
	}  

	if(isset($_POST['save'])){
		require_once '../includes/security.php';
		
		$curr_password = $_POST['curr_password'] ?? '';
		$email = validate_email($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		$firstname = sanitize_input($_POST['firstname'] ?? '');
		$lastname = sanitize_input($_POST['lastname'] ?? '');
		
		// Validate inputs
		if ($email === false) {
			$_SESSION['error'] = 'Invalid email address';
			header('location:'.$return);
			exit();
		}
		
		if (empty($firstname) || empty($lastname)) {
			$_SESSION['error'] = 'First name and last name are required';
			header('location:'.$return);
			exit();
		}
		
		if(password_verify($curr_password, $admin['password'])){
			$filename = $admin['photo'];
			
			// SECURITY: Validate file upload
			if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
				$file_validation = validate_file_upload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2097152, '../images/');
				if ($file_validation !== false) {
					if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_validation['path'])) {
						$filename = $file_validation['filename'];
					}
				} else {
					$_SESSION['error'] = 'Invalid file upload. Only JPG, PNG, GIF images up to 2MB are allowed.';
					header('location:'.$return);
					exit();
				}
			}

			if($password == $admin['password']){
				$password = $admin['password'];
			}
			else{
				$password = password_hash($password, PASSWORD_DEFAULT);
			}

			$conn = $pdo->open();

			try{
				$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, photo=:photo WHERE id=:id");
				$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'photo'=>$filename, 'id'=>$admin['id']]);

				$_SESSION['success'] = 'Account updated successfully';
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}

			$pdo->close();
			
		}
		else{
			$_SESSION['error'] = 'Incorrect password';
		}
	}
	else{
		$_SESSION['error'] = 'Fill up required details first';
	}

	header('location:'.$return);

?>