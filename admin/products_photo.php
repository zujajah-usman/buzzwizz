<?php
	include 'includes/session.php';

	if(isset($_POST['upload'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid product ID';
			header('location: products.php');
			exit();
		}

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT * FROM products WHERE id=:id");
		$stmt->execute(['id'=>$id]);
		$row = $stmt->fetch();
  
		if (!$row) {
			$_SESSION['error'] = 'Product not found';
			header('location: products.php');
			exit();
		}
  
		$new_filename = '';
		
		// SECURITY: Validate file upload
		if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
			$file_validation = validate_file_upload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2097152, '../images/');
			if ($file_validation !== false) {
				// Use slug-based name with validated extension
				$new_filename = sanitize_filename($row['slug']) . '_' . time() . '.' . $file_validation['extension'];
				$final_path = '../images/' . $new_filename;
				if (!move_uploaded_file($_FILES['photo']['tmp_name'], $final_path)) {
					$_SESSION['error'] = 'Failed to upload file';
					header('location: products.php');
					exit();
				}
			} else {
				$_SESSION['error'] = 'Invalid file upload. Only JPG, PNG, GIF images up to 2MB are allowed.';
				header('location: products.php');
				exit();
			}
		}
		
		if (empty($new_filename)) {
			$_SESSION['error'] = 'No valid file uploaded';
			header('location: products.php');
			exit();
		}
		
		try{
			$stmt = $conn->prepare("UPDATE products SET photo=:photo WHERE id=:id");
			$stmt->execute(['photo'=>$new_filename, 'id'=>$id]);
			$_SESSION['success'] = 'Product photo updated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = $e->getMessage();
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Select product to update photo first';
	}

	header('location: products.php');
?>