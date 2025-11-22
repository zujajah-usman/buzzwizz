<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id']);
		
		if ($id === false || $id <= 0) {
			echo json_encode(['error' => 'Invalid category ID']);
			exit();
		}
		
		$conn = $pdo->open();
  
		$stmt = $conn->prepare("SELECT * FROM category WHERE id=:id");
		$stmt->execute(['id'=>$id]);
		$row = $stmt->fetch();
		
		$pdo->close();

		echo json_encode($row);
	}
?>