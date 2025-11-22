<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid product ID';
			header('location: products.php');
			exit();
		}
		
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("DELETE FROM products WHERE id=:id");
			$stmt->execute(['id'=>$id]);

			$_SESSION['success'] = 'Product deleted successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to delete product. Please try again.';
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Select product to delete first';
	}

	header('location: products.php');
	
?>