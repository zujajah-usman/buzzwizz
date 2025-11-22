<?php
	include 'includes/session.php';
	include 'includes/slugify.php';
 
	if(isset($_POST['edit'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		$name = sanitize_input($_POST['name'] ?? '');
		$slug = slugify($name);
		$category = validate_int($_POST['category'] ?? 0);
		$price = filter_var($_POST['price'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRAC);
		$description = sanitize_input($_POST['description'] ?? '');
		
		// Validate inputs
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid product ID';
			header('location: products.php');
			exit();
		}
		
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

		try{
			$stmt = $conn->prepare("UPDATE products SET name=:name, slug=:slug, category_id=:category, price=:price, description=:description WHERE id=:id");
			$stmt->execute(['name'=>$name, 'slug'=>$slug, 'category'=>$category, 'price'=>$price, 'description'=>$description, 'id'=>$id]);
			$_SESSION['success'] = 'Product updated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to update product. Please try again.';
		}
		
		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up edit product form first';
	}

	header('location: products.php');

?>