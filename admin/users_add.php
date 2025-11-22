<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		require_once '../includes/security.php';
		
		$firstname = sanitize_input($_POST['firstname'] ?? '');
		$lastname = sanitize_input($_POST['lastname'] ?? '');
		$email = validate_email($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		$address = sanitize_input($_POST['address'] ?? '');
		$contact = sanitize_input($_POST['contact'] ?? '');
		
		// Validate inputs
		if (empty($firstname) || empty($lastname)) {
			$_SESSION['error'] = 'First name and last name are required';
			header('location: users.php');
			exit();
		}
		
		if ($email === false) {
			$_SESSION['error'] = 'Invalid email address';
			header('location: users.php');
			exit();
		}
		
		if (empty($password) || !validate_password($password)) {
			$_SESSION['error'] = 'Password must be at least 8 characters with letters and numbers';
			header('location: users.php');
			exit();
		}
   
		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email=:email");
		$stmt->execute(['email'=>$email]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'Email already taken';
		}
		else{
			$password = password_hash($password, PASSWORD_DEFAULT);
			$filename = '';
			$now = date('Y-m-d');
			
			// SECURITY: Validate file upload
			if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
				$file_validation = validate_file_upload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2097152, '../images/');
				if ($file_validation !== false) {
					if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_validation['path'])) {
						$filename = $file_validation['filename'];
					}
				} else {
					$_SESSION['error'] = 'Invalid file upload. Only JPG, PNG, GIF images up to 2MB are allowed.';
					header('location: users.php');
					exit();
				}
			}
			try{
				$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, address, contact_info, photo, status, created_on) VALUES (:email, :password, :firstname, :lastname, :address, :contact, :photo, :status, :created_on)");
				$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'address'=>$address, 'contact'=>$contact, 'photo'=>$filename, 'status'=>1, 'created_on'=>$now]);
				$_SESSION['success'] = 'User added successfully';

			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up user form first';
	}

	header('location: users.php');

?>