<?php
	include 'includes/session.php';
	include 'includes/slugify.php';
   
	if(isset($_POST['add'])){
		require_once '../includes/security.php';
		
		$name = sanitize_input($_POST['name'] ?? '');
		$slug = slugify($name);
		$category = validate_int($_POST['category'] ?? 0);
		$price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRAC);
		$description = sanitize_input($_POST['description'] ?? '');
		
		// Validate inputs
		if (empty($name)) {
			$_SESSION['error'] = 'Product name is required';
			header('location: products.php');
			exit();
		}
		
		if ($category === false || $category <= 0) {
			$_SESSION['error'] = 'Invalid category';
			header('location: products.php');
			exit();
		}
		
		if ($price <= 0) {
			$_SESSION['error'] = 'Invalid price';
			header('location: products.php');
			exit();
		}

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM products WHERE slug=:slug");
		$stmt->execute(['slug'=>$slug]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'Product already exist';
		}
		else{
			$new_filename = '';
			
			// SECURITY: Validate file upload
			if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
				$file_validation = validate_file_upload($_FILES['photo'], ['jpg', 'jpeg', 'png', 'gif'], 2097152, '../images/');
				if ($file_validation !== false) {
					// Use slug-based name but with validated extension
					$new_filename = $slug . '.' . $file_validation['extension'];
					$final_path = '../images/' . $new_filename;
					if (move_uploaded_file($_FILES['photo']['tmp_name'], $final_path)) {
						// File uploaded successfully
					} else {
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

			try{
				$stmt = $conn->prepare("INSERT INTO products (category_id, name, description, slug, price, photo) VALUES (:category, :name, :description, :slug, :price, :photo)");
				$stmt->execute(['category'=>$category, 'name'=>$name, 'description'=>$description, 'slug'=>$slug, 'price'=>$price, 'photo'=>$new_filename]);
				$_SESSION['success'] = 'User added successfully';

			}
			catch(PDOException $e){
				$_SESSION['error'] = $e->getMessage();
			}
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up product form first';
	}

	header('location: products.php');

?>