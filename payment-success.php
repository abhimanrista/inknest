<?php
session_start();
require 'db.php';

$encData = $_GET['data'] ?? null;
$order_id = $_GET['order_id'] ?? null;
$success = false;

if ($encData && $order_id) {
    $jsonString = base64_decode($encData);
    $decodedData = json_decode($jsonString, true);

    if ($decodedData && isset($decodedData['status']) && $decodedData['status'] === 'COMPLETE') {
        $transaction_code = $decodedData['transaction_code'];

        try {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'Completed', transaction_id = ? WHERE id = ?");
            $stmt->execute([$transaction_code, $order_id]);

            // Clear cart session after payment verification
            unset($_SESSION['cart']);

            // Redirect to official invoice bill page
            header("Location: invoice.php?order_id=" . $order_id);
            exit();
        } catch (\PDOException $e) {
            $success = false;
        }
    }
}
die("Payment verification failed or invalid parameters.");
?>