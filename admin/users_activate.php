<?php
	include 'includes/session.php';

	if(isset($_POST['activate'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id'] ?? 0);
		
		if ($id === false || $id <= 0) {
			$_SESSION['error'] = 'Invalid user ID';
			header('location: users.php');
			exit();
		}
		
		$conn = $pdo->open();
   
		try{
			$stmt = $conn->prepare("UPDATE users SET status=:status WHERE id=:id");
			$stmt->execute(['status'=>1, 'id'=>$id]);
			$_SESSION['success'] = 'User activated successfully';
		}
		catch(PDOException $e){
			$_SESSION['error'] = 'Failed to activate user. Please try again.';
		}

		$pdo->close();

	}
	else{
		$_SESSION['error'] = 'Select user to activate first';
	}

	header('location: users.php');
?>