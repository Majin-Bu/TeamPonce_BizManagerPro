<?php
require_once '../../config/database.php';
require_once '../../config/session.php';
requireLogin();

$user_id = getCurrentUserId();
$error = '';
$success = '';
$clients = [];
$products = [];

// Fetch clients
$client_query = "SELECT client_id, CONCAT(first_name, ' ', last_name) AS name FROM clients WHERE user_id = ? ORDER BY first_name, last_name";
$client_result = executeQuery($conn, $client_query, "i", [$user_id]);

if ($client_result['success']) {
    $stmt = $client_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($client_id, $name);
    
    while ($stmt->fetch()) {
        $clients[] = ['client_id' => $client_id, 'name' => $name];
    }
    $stmt->close();
}

// Fetch products
$product_query = "SELECT product_id, product_name, price, stock_quantity FROM products WHERE user_id = ? ORDER BY product_name";
$product_result = executeQuery($conn, $product_query, "i", [$user_id]);

if ($product_result['success']) {
    $stmt = $product_result['stmt'];
    $stmt->store_result();
    $stmt->bind_result($product_id, $product_name, $price, $stock);
    
    while ($stmt->fetch()) {
        $products[] = ['product_id' => $product_id, 'product_name' => $product_name, 'price' => $price, 'stock' => $stock];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id'] ?? 0);
    $order_items = $_POST['items'] ?? [];
    
    if ($client_id <= 0 || empty($order_items)) {
        $error = 'Please select a client and add at least one item.';
    } else {
        // Calculate total
        $total_amount = 0;
        foreach ($order_items as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity'])) {
                $product_id = intval($item['product_id']);
                $quantity = intval($item['quantity']);
                
                // Get product price
                $price_query = "SELECT price FROM products WHERE product_id = ? AND user_id = ?";
                $price_result = executeQuery($conn, $price_query, "ii", [$product_id, $user_id]);
                
                if ($price_result['success']) {
                    $stmt = $price_result['stmt'];
                    $stmt->bind_result($price);
                    $stmt->fetch();
                    $total_amount += $price * $quantity;
                    $stmt->close();
                }
            }
        }
        
        if ($total_amount <= 0) {
            $error = 'Order total must be greater than 0.';
        } else {
            // Insert order
            $insert_query = "INSERT INTO orders (client_id, total_amount, status) VALUES (?, ?, 'Pending')";
            $insert_result = executeQuery($conn, $insert_query, "id", [$client_id, $total_amount]);
            
            if ($insert_result['success']) {
                $order_id = $conn->insert_id;
                
                // Insert order items and update stock
                $success_items = true;
                foreach ($order_items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity'])) {
                        $product_id = intval($item['product_id']);
                        $quantity = intval($item['quantity']);
                        
                        // Get product price
                        $price_query = "SELECT price FROM products WHERE product_id = ? AND user_id = ?";
                        $price_result = executeQuery($conn, $price_query, "ii", [$product_id, $user_id]);
                        
                        if ($price_result['success']) {
                            $stmt = $price_result['stmt'];
                            $stmt->bind_result($price);
                            $stmt->fetch();
                            $subtotal = $price * $quantity;
                            $stmt->close();
                            
                            // Insert order item
                            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)";
                            $item_result = executeQuery($conn, $item_query, "iiidd", [$order_id, $product_id, $quantity, $price, $subtotal]);
                            
                            if ($item_result['success']) {
                                // Update stock
                                $stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
                                executeQuery($conn, $stock_query, "ii", [$quantity, $product_id]);
                            } else {
                                $success_items = false;
                            }
                        }
                    }
                }
                
                if ($success_items) {
                    $success = 'Order created successfully!';
                } else {
                    $error = 'Order created but some items failed to add.';
                }
            } else {
                $error = 'Failed to create order.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order - BizManagerPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h2 class="mb-4">Create New Order</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="card p-4">
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Select Client *</label>
                        <select class="form-control" id="client_id" name="client_id" required>
                            <option value="">-- Select a Client --</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['client_id']; ?>"><?php echo $client['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Order Items</label>
                        <div id="items-container">
                            <div class="row mb-2 item-row">
                                <div class="col-md-5">
                                    <select class="form-control product-select" name="items[0][product_id]">
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>">
                                                <?php echo $product['product_name']; ?> (₱<?php echo number_format($product['price'], 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control quantity-input" name="items[0][quantity]" placeholder="Quantity" min="1">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control subtotal-display" placeholder="Subtotal" disabled style="color:#1e293b;">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="add-item">Add Item</button>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Total Amount: ₱<span id="total-amount">0.00</span></h5>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Order</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let itemCount = 1;
        
        function updateTotals() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const productSelect = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const subtotalDisplay = row.querySelector('.subtotal-display');
                
                if (productSelect.value && quantityInput.value) {
                    const price = parseFloat(productSelect.options[productSelect.selectedIndex].dataset.price);
                    const quantity = parseInt(quantityInput.value);
                    const subtotal = price * quantity;
                    subtotalDisplay.value = '₱' + subtotal.toFixed(2);
                    total += subtotal;
                } else {
                    subtotalDisplay.value = '';
                }
            });
            document.getElementById('total-amount').textContent = total.toFixed(2);
        }
        
        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newRow = document.querySelector('.item-row').cloneNode(true);
            newRow.querySelectorAll('input, select').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, '[' + itemCount + ']');
                el.value = '';
            });
            container.appendChild(newRow);
            attachEventListeners();
            itemCount++;
        });
        
        function attachEventListeners() {
            document.querySelectorAll('.product-select, .quantity-input').forEach(el => {
                el.addEventListener('change', updateTotals);
                el.addEventListener('input', updateTotals);
            });
            
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.item-row').remove();
                    updateTotals();
                });
            });
        }
        
        attachEventListeners();
    </script>
</body>
</html>
