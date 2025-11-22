<?php
include 'includes/session.php';

$conn = $pdo->open();

$output = array('error' => false);

if (isset($_SESSION['user'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user']]);
        $output['message'] = 'Cart cleared successfully.';
    } catch (PDOException $e) {
        $output['error'] = true;
        $output['message'] = $e->getMessage();
    }
} else {
    unset($_SESSION['cart']);
    $output['message'] = 'Cart cleared successfully.';
}

$pdo->close();
echo json_encode($output);
?>
