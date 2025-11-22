<?php
	include 'includes/session.php';
  
	if(isset($_POST['edit'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		$firstname = sanitize_input($_POST['firstname'] ?? '');
		$lastname = sanitize_input($_POST['lastname'] ?? '');
		$email = validate_email($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		$address = sanitize_input($_POST['address'] ?? '');
		$contact = sanitize_input($_POST['contact'] ?? '');
		
		// Validate inputs
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid user ID';
			header('location: users.php');
			exit();
		}
		
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

		$conn = $pdo->open();
		$stmt = $conn->prepare("SELECT * FROM users WHERE id=:id");
		$stmt->execute(['id'=>$id]);
		$row = $stmt->fetch();

		if($password == $row['password']){
			$password = $row['password'];
		}
		else{
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		try{
			$stmt = $conn->prepare("UPDATE users SET email=:email, password=:password, firstname=:firstname, lastname=:lastname, address=:address, contact_info=:contact WHERE id=:id");
			$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'address'=>$address, 'contact'=>$contact, 'id'=>$id]);
			$_SESSION['success'] = 'User updated successfully';

		}
		catch(PDOException $e){
			// SECURITY: Don't expose database errors
			$_SESSION['error'] = 'Failed to update user. Please try again.';
		}
		

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up edit user form first';
	}

	header('location: users.php');

?>