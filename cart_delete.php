<?php
	include 'includes/session.php';
	require_once 'includes/security.php';
   
	$conn = $pdo->open();

	$output = array('error'=>false);
	$id = validate_int($_POST['id'] ?? 0);
	
	if ($id === false || $id <= 0) {
		$output['error'] = true;
		$output['message'] = 'Invalid cart item ID';
		echo json_encode($output);
		exit();
	}

	if(isset($_SESSION['user'])){
		try{
			$stmt = $conn->prepare("DELETE FROM cart WHERE id=:id");
			$stmt->execute(['id'=>$id]);
			$output['message'] = 'Deleted';
			
		}
		catch(PDOException $e){
			$output['message'] = $e->getMessage();
		}
	}
	else{
		foreach($_SESSION['cart'] as $key => $row){
			if($row['productid'] == $id){
				unset($_SESSION['cart'][$key]);
				$output['message'] = 'Deleted';
			}
		}
	}

	$pdo->close();
	echo json_encode($output);

?>