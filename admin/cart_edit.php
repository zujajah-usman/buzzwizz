<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		require_once '../includes/security.php';
		
		$userid = validate_int($_POST['userid'] ?? 0);
		$cartid = validate_int($_POST['cartid'] ?? 0);
		$quantity = validate_int($_POST['quantity'] ?? 0);
		
		if ($userid === false || $userid <= 0) {
			$_SESSION['error'] = 'Invalid user ID';
			header('location: cart.php');
			exit();
		}
		
		if ($cartid === false || $cartid <= 0) {
			$_SESSION['error'] = 'Invalid cart ID';
			header('location: cart.php?user='.$userid);
			exit();
		}
		
		if ($quantity === false || $quantity <= 0) {
			$_SESSION['error'] = 'Invalid quantity';
			header('location: cart.php?user='.$userid);
			exit();
		}
   
		$conn = $pdo->open();

		try{
			$stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id");
			$stmt->execute(['quantity'=>$quantity, 'id'=>$cartid]);

			$_SESSION['success'] = 'Quantity updated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to update quantity. Please try again.';
		}
		
		$pdo->close();

		header('location: cart.php?user='.$userid);
	}

?>