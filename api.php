<?php
require_once 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$db = getDB();

switch ($action) {
    case 'place_order':
        placeOrder($db);
        break;
    case 'get_order':
        getOrder($db);
        break;
    case 'check_status':
        checkStatus($db);
        break;
    default:
        echo json_encode(['error' => 'Action not found']);
}

function placeOrder(PDO $db): void {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || empty($data['items']) || empty($data['table_id'])) {
        echo json_encode(['error' => 'Data tidak lengkap']);
        return;
    }

    $tableId      = (int)$data['table_id'];
    $customerName = trim($data['customer_name'] ?? 'Guest') ?: 'Guest';
    $notes        = trim($data['notes'] ?? '');
    $paymentMethod = $data['payment_method'] ?? 'cash';
    $items        = $data['items'];

    // Validate table
    $stmt = $db->prepare("SELECT * FROM tables_cafe WHERE id = ? AND is_active = 1");
    $stmt->execute([$tableId]);
    $table = $stmt->fetch();

    if (!$table) {
        echo json_encode(['error' => 'Meja tidak ditemukan']);
        return;
    }

    // Validate & calculate items
    $subtotal = 0;
    $validatedItems = [];

    foreach ($items as $item) {
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1");
        $stmt->execute([(int)$item['id']]);
        $menuItem = $stmt->fetch();

        if (!$menuItem) continue;

        $qty      = max(1, (int)$item['qty']);
        $price    = (float)$menuItem['price'];
        $itemSub  = $price * $qty;
        $subtotal += $itemSub;

        $validatedItems[] = [
            'menu_item_id' => $menuItem['id'],
            'quantity'     => $qty,
            'price'        => $price,
            'subtotal'     => $itemSub,
            'notes'        => $item['notes'] ?? '',
        ];
    }

    if (empty($validatedItems)) {
        echo json_encode(['error' => 'Tidak ada item valid']);
        return;
    }

    $tax   = round($subtotal * TAX_RATE, 2);
    $total = $subtotal + $tax;
    $code  = generateOrderCode();

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("
            INSERT INTO orders (order_code, table_id, customer_name, payment_method, subtotal, tax, total, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$code, $tableId, $customerName, $paymentMethod, $subtotal, $tax, $total, $notes]);
        $orderId = $db->lastInsertId();

        $stmtItem = $db->prepare("
            INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($validatedItems as $vi) {
            $stmtItem->execute([
                $orderId, $vi['menu_item_id'], $vi['quantity'],
                $vi['price'], $vi['subtotal'], $vi['notes']
            ]);
        }

        $db->commit();

        echo json_encode([
            'success'    => true,
            'order_code' => $code,
            'order_id'   => $orderId,
            'subtotal'   => $subtotal,
            'tax'        => $tax,
            'total'      => $total,
            'table'      => $table['table_name'],
            'message'    => 'Pesanan berhasil dibuat!'
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['error' => 'Gagal membuat pesanan: ' . $e->getMessage()]);
    }
}

function getOrder(PDO $db): void {
    $code = $_GET['code'] ?? '';
    if (!$code) {
        echo json_encode(['error' => 'Kode pesanan diperlukan']);
        return;
    }

    $stmt = $db->prepare("
        SELECT o.*, t.table_name
        FROM orders o
        JOIN tables_cafe t ON o.table_id = t.id
        WHERE o.order_code = ?
    ");
    $stmt->execute([$code]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['error' => 'Pesanan tidak ditemukan']);
        return;
    }

    $stmt = $db->prepare("
        SELECT oi.*, m.name as item_name
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll();

    echo json_encode(['success' => true, 'order' => $order]);
}

function checkStatus(PDO $db): void {
    $code = $_GET['code'] ?? '';
    $stmt = $db->prepare("SELECT status, payment_status FROM orders WHERE order_code = ?");
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    echo json_encode($row ?: ['error' => 'Not found']);
}
