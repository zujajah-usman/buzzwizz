<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
   
	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">

	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-9">
	        		<?php
	        			if(isset($_SESSION['error'])){
	        				echo "
	        					<div class='alert alert-danger'>
	        						".$_SESSION['error']."
	        					</div>
	        				";
	        				unset($_SESSION['error']);
	        			}
	        		?>
	        		
		       		<?php
		       			$month = date('m');
		       			$conn = $pdo->open();

		       			try{
		       			 	$inc = 3;	
						    // SECURITY FIX: Use parameterized query instead of string interpolation
						    $stmt = $conn->prepare("SELECT *, SUM(quantity) AS total_qty FROM details LEFT JOIN sales ON sales.id=details.sales_id LEFT JOIN products ON products.id=details.product_id WHERE MONTH(sales_date) = :month GROUP BY details.product_id ORDER BY total_qty DESC LIMIT 6");
						    $stmt->execute(['month' => $month]);
						    foreach ($stmt as $row) {
						    	$image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/noimage.jpg';
						    	$inc = ($inc == 3) ? 1 : $inc + 1;
	       						if($inc == 1) echo "<div class='row'>";
		       						require_once 'includes/security.php';
		       						echo "
	       							<div class='col-sm-4'>
	       								<div class='box box-solid'>
		       								<div class='box-body prod-body'>
		       									<img src='".escape_html($image)."' width='100%' height='230px' class='thumbnail'>
		       									<h5><a href='product.php?product=".escape_html($row['slug'])."'>".escape_html($row['name'])."</a></h5>
		       								</div>
		       								<div class='box-footer'>
		       									<b>&#36; ".number_format($row['price'], 2)."</b>
		       								</div>
	       								</div>
	       							</div>
	       						";
	       						if($inc == 3) echo "</div>";
						    }
						    if($inc == 1) echo "<div class='col-sm-4'></div><div class='col-sm-4'></div></div>"; 
							if($inc == 2) echo "<div class='col-sm-4'></div></div>";
						}
						catch(PDOException $e){
							// SECURITY: Don't expose database errors
							error_log("COD error: " . $e->getMessage());
							echo "Error loading products.";
						}

						$pdo->close();

		       		?> 

	        	</div>

                <div class="col-sm-9">
                    <h4><b>Enter following details to complete your order</b></h4>
                    <table class="table">
                        <tr>
                            <td>Name:</td>
                            <td><input type="text" name="name" id="name" class="form-control"></td>
                        </tr>
                        <tr>
                            <td>Address:</td>
                            <td><input type="text" name="address" id="address" class="form-control"></td>
                        </tr>
                        <tr>
                            <td>Contact:</td>
                            <td><input type="text" name="contact" id="contact" class="form-control"></td>
                        </tr>
                    </table>
                    <button id="enterButton" class="btn btn-primary">Enter</button>
                   
					<div id="successMessage" style="color: green; margin-top: 10px; display: none;">Order has been successfully placed! Your order ID is BZ334</div>
					<!-- Add the new button for tracking the order -->
   <!-- Add the new button for tracking the order with styling -->
   <button id="trackOrderButton" class="btn btn-purple" style="background-color: purple; color: white; margin-top: 10px;" onclick="window.location.href='rating.php'">Track Your Order</button>
                </div>
                
	        	<div class="col-sm-3">
	        		<?php include 'includes/sidebar.php'; ?>
	        	</div>


	        </div>
	      </section>
	     
	    </div>
	  </div>
  
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
document.getElementById('enterButton').addEventListener('click', function() {
    // Add your logic for processing the order details here

    // Send AJAX request to clear the cart
    $.ajax({
        type: 'POST',
        url: 'clear_cart.php',
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                // Display success message
                document.getElementById('successMessage').style.display = 'block';
            } else {
                // Display error message
                alert('Error: ' + response.message); // You can replace this with a more user-friendly notification
            }
        },
        error: function(error) {
            // Handle AJAX error
            console.error(error);
        }
    });
});
</script>
</body>
</html>
