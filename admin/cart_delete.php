<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		require_once '../includes/security.php';
		
		$userid = validate_int($_POST['userid'] ?? 0);
		$cartid = validate_int($_POST['cartid'] ?? 0);
		
		if ($userid === false || $userid <= 0 || $cartid === false || $cartid <= 0) {
			$_SESSION['error'] = 'Invalid IDs';
			header('location: cart.php');
			exit();
		}
		  
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("DELETE FROM cart WHERE id=:id");
			$stmt->execute(['id'=>$cartid]);

			$_SESSION['success'] = 'Product deleted from cart';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to delete item. Please try again.';
		}
		
		$pdo->close();

		header('location: cart.php?user='.$userid);
	}

?>