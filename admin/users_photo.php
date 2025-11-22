<?php
	include 'includes/session.php';

	if(isset($_POST['upload'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid user ID';
			header('location: users.php');
			exit();
		}
		
		$filename = '';
		
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
		 
		if (empty($filename)) {
			$_SESSION['error'] = 'No valid file uploaded';
			header('location: users.php');
			exit();
		}
		
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("UPDATE users SET photo=:photo WHERE id=:id");
			$stmt->execute(['photo'=>$filename, 'id'=>$id]);
			$_SESSION['success'] = 'User photo updated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Select user to update photo first';
	}

	header('location: users.php');
?>