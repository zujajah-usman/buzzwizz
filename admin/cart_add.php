<?php
	include 'includes/session.php';
  
	if(isset($_POST['add'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		$product = validate_int($_POST['product'] ?? 0);
		$quantity = validate_int($_POST['quantity'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid user ID';
			header('location: cart.php');
			exit();
		}
		
		if ($product === false || $product <= 0) {
			$_SESSION['error'] = 'Invalid product ID';
			header('location: cart.php?user='.$id);
			exit();
		}
		
		if ($quantity === false || $quantity <= 0) {
			$_SESSION['error'] = 'Invalid quantity';
			header('location: cart.php?user='.$id);
			exit();
		}

		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM cart WHERE product_id=:id");
		$stmt->execute(['id'=>$product]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'Product exist in cart';
		}
		else{
			try{
				$stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user, :product, :quantity)");
				$stmt->execute(['user'=>$id, 'product'=>$product, 'quantity'=>$quantity]);

				$_SESSION['success'] = 'Product added to cart';
			}
			catch(PDOException $e){
				$_SESSION['error'] = 'Failed to add product to cart. Please try again.';
			}
		}

		$pdo->close();

		header('location: cart.php?user='.$id);
	}

?>