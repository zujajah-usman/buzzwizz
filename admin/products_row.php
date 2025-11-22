<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		require_once '../includes/security.php';
		
		$id = validate_int($_POST['id']);
		
		if ($id === false || $id <= 0) {
			echo json_encode(['error' => 'Invalid product ID']);
			exit();
		}
		
		$conn = $pdo->open();
  
		$stmt = $conn->prepare("SELECT *, products.id AS prodid, products.name AS prodname, category.name AS catname FROM products LEFT JOIN category ON category.id=products.category_id WHERE products.id=:id");
		$stmt->execute(['id'=>$id]);
		$row = $stmt->fetch();
		
		$pdo->close();

		echo json_encode($row);
	}
?>