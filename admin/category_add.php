
<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		require_once '../includes/security.php';
		
		$name = sanitize_input($_POST['name'] ?? '');
		
		if (empty($name)) {
			$_SESSION['error'] = 'Category name is required';
			header('location: category.php');
			exit();
		}
  
		$conn = $pdo->open();

		$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM category WHERE name=:name");
		$stmt->execute(['name'=>$name]);
		$row = $stmt->fetch();

		if($row['numrows'] > 0){
			$_SESSION['error'] = 'Category already exist';
		}
		else{
			try{
				$stmt = $conn->prepare("INSERT INTO category (name) VALUES (:name)");
				$stmt->execute(['name'=>$name]);
				$_SESSION['success'] = 'Category added successfully';
			}
			catch(PDOException $e){
				$_SESSION['error'] = 'Failed to add category. Please try again.';
			}
		}

		$pdo->close();
	}
	else{
		$_SESSION['error'] = 'Fill up category form first';
	}

	header('location: category.php');

?>