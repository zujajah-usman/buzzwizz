<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		$name = sanitize_input($_POST['name'] ?? '');
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid category ID';
			header('location: category.php');
			exit();
		}
		
		if (empty($name)) {
			$_SESSION['error'] = 'Category name is required';
			header('location: category.php');
			exit();
		}
    
		try{
			$stmt = $conn->prepare("UPDATE category SET name=:name WHERE id=:id");
			$stmt->execute(['name'=>$name, 'id'=>$id]);
			$_SESSION['success'] = 'Category updated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to update category. Please try again.';
		}
		
		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up edit category form first';
	}

	header('location: category.php');

?>