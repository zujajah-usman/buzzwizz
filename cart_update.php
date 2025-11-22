<?php
	include 'includes/session.php';
	require_once 'includes/security.php';
    
	$conn = $pdo->open();

	$output = array('error'=>false);

	$id = validate_int($_POST['id'] ?? 0);
	$qty = validate_int($_POST['qty'] ?? 0);
	
	if ($id === false || $id <= 0) {
		$output['error'] = true;
		$output['message'] = 'Invalid cart item ID';
		echo json_encode($output);
		exit();
	}
	
	if ($qty === false || $qty <= 0) {
		$output['error'] = true;
		$output['message'] = 'Invalid quantity';
		echo json_encode($output);
		exit();
	}

	if(isset($_SESSION['user'])){
		try{
			$stmt = $conn->prepare("UPDATE cart SET quantity=:quantity WHERE id=:id");
			$stmt->execute(['quantity'=>$qty, 'id'=>$id]);
			$output['message'] = 'Updated';
		}
		catch(PDOException $e){
			$output['message'] = $e->getMessage();
		}
	}
	else{
		foreach($_SESSION['cart'] as $key => $row){
			if($row['productid'] == $id){
				$_SESSION['cart'][$key]['quantity'] = $qty;
				$output['message'] = 'Updated';
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>