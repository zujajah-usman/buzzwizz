<?php
	include 'includes/session.php';

	require_once 'includes/security.php';
	$id = validate_int($_POST['id'] ?? 0);
	
	if ($id === false || $id <= 0) {
		$output = array('list'=>'', 'error' => 'Invalid transaction ID');
		echo json_encode($output);
		exit();
	}

	$conn = $pdo->open();

	$output = array('list'=>'');

	$stmt = $conn->prepare("SELECT * FROM details LEFT JOIN products ON products.id=details.product_id LEFT JOIN sales ON sales.id=details.sales_id WHERE details.sales_id=:id");
	$stmt->execute(['id'=>$id]);
   
	$total = 0;
	foreach($stmt as $row){
		$output['transaction'] = $row['pay_id'];
		$output['date'] = date('M d, Y', strtotime($row['sales_date']));
		$subtotal = $row['price']*$row['quantity'];
		$total += $subtotal;
		require_once 'includes/security.php';
		$output['list'] .= "
			<tr class='prepend_items'>
				<td>".escape_html($row['name'])."</td>
				<td>&#36; ".number_format($row['price'], 2)."</td>
				<td>".$row['quantity']."</td>
				<td>&#36; ".number_format($subtotal, 2)."</td>
			</tr>
		";
	}
	
	$output['total'] = '<b>&#36; '.number_format($total, 2).'<b>';
	$pdo->close();
	echo json_encode($output);

?>