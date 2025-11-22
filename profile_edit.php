<?php
	include 'includes/session.php';

	$conn = $pdo->open();   

	if(isset($_POST['edit'])){
		require_once 'includes/security.php';
		
		$curr_password = $_POST['curr_password'] ?? '';
		$email = validate_email($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		$firstname = sanitize_input($_POST['firstname'] ?? '');
		$lastname = sanitize_input($_POST['lastname'] ?? '');
		$contact = sanitize_input($_POST['contact'] ?? '');
		$address = sanitize_input($_POST['address'] ?? '');
		
		// Validate inputs
		if ($email === false) {
			$_SESSION['error'] = 'Invalid email address';
			header('location: profile.php');
			exit();
		}
		
		if (empty($firstname) || empty($lastname)) {
			$_SESSION['error'] = 'First name and last name are required';
			header('location: profile.php');
			exit();
		}
		
		if(password_verify($curr_password, $user['password'])){
			$filename = $user['photo'];
			
			// SECURITY: Validate file upload
			if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
				$file_validation = validate_file_upload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2097152, 'images/');
				if ($file_validation !== false) {
					if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_validation['path'])) {
						$filename = $file_validation['filename'];
					}
				} else {
					$_SESSION['error'] = 'Invalid file upload. Only JPG, PNG, GIF images up to 2MB are allowed.';
					header('location: profile.php');
					exit();
				}
			}

			if($password == $user['password']){
				$password = $user['password'];
			}
			else{
				$password = password_hash($password, PASSWORD_DEFAULT);
			}

			try{
				$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, contact_info=:contact, address=:address, photo=:photo WHERE id=:id");
				$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'contact'=>$contact, 'address'=>$address, 'photo'=>$filename, 'id'=>$user['id']]);

				$_SESSION['success'] = 'Account updated successfully';
			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
			
		}
		else{
			$_SESSION['error'] = 'Incorrect password';
		}
	}
	else{
		$_SESSION['error'] = 'Fill up edit form first';
	}

	$pdo->close();

	header('location: profile.php');

?>