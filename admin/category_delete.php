<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid category ID';
			header('location: category.php');
			exit();
		}
		
		$conn = $pdo->open();
   
		try{
			$stmt = $conn->prepare("DELETE FROM category WHERE id=:id");
			$stmt->execute(['id'=>$id]);

			$_SESSION['success'] = 'Category deleted successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to delete category. Please try again.';
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Select category to delete first';
	}

	header('location: category.php');
	
?>