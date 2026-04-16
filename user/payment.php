<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

$id_user = $_SESSION['user']['id_user'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    header('Location: index.php');
    exit();
}

// Ambil data pesanan milik user ini
$order_q = mysqli_prepare($conn, 'SELECT id_pesanan, id_user, total_harga, tanggal, status FROM pesanan WHERE id_pesanan = ? LIMIT 1');
mysqli_stmt_bind_param($order_q, 'i', $id_pesanan);
mysqli_stmt_execute($order_q);
$order_res = mysqli_stmt_get_result($order_q);
$order = mysqli_fetch_assoc($order_res);

if (!$order || (int)$order['id_user'] !== (int)$id_user) {
    header('Location: index.php');
    exit();
}

// Ambil item pesanan
$items_q = mysqli_prepare($conn, 'SELECT d.id_menu, d.jumlah, d.subtotal, m.nama_menu, m.harga FROM detail_pesanan d JOIN menu m ON d.id_menu = m.id_menu WHERE d.id_pesanan = ?');
mysqli_stmt_bind_param($items_q, 'i', $id_pesanan);
mysqli_stmt_execute($items_q);
$items_res = mysqli_stmt_get_result($items_q);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan #<?php echo htmlspecialchars($id_pesanan); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        .qr-container {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 15px 0;
        }
        .qr-code {
            margin: 15px auto;
            padding: 15px;
            background: white;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .qr-code canvas {
            display: block;
        }
        .payment-method-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .payment-method-card:hover {
            border-color: #ff6b35 !important;
            box-shadow: 0 2px 8px rgba(255, 107, 53, 0.2);
        }
        .payment-method-card.selected {
            border-color: #ff6b35 !important;
            background: #fff5ef;
        }
        .qr-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 12px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center" style="background:#ff6b35; color:#fff;">
                        <h5 class="mb-0">Pembayaran Pesanan</h5>
                        <a href="index.php" class="btn btn-sm btn-light">Kembali</a>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-muted">ID Pesanan</div>
                                    <div class="fw-semibold">#<?php echo str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted">Tanggal</div>
                                    <div class="fw-semibold"><?php echo date('d M Y H:i', strtotime($order['tanggal'])); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $computed_total = 0; ?>
                                    <?php while ($row = mysqli_fetch_assoc($items_res)): ?>
                                        <?php $computed_total += (float)$row['subtotal']; ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_menu']); ?></td>
                                            <td class="text-center"><?php echo (int)$row['jumlah']; ?></td>
                                            <td class="text-end">Rp <?php echo number_format((float)$row['harga'], 0, ',', '.'); ?></td>
                                            <td class="text-end">Rp <?php echo number_format((float)$row['subtotal'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">Rp <?php echo number_format((float)$order['total_harga'], 0, ',', '.'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <form action="confirm_payment.php" method="post" class="mt-4">
                            <input type="hidden" name="id_pesanan" value="<?php echo (int)$order['id_pesanan']; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Metode Pembayaran</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="payment-method-card border rounded p-3 h-100" onclick="selectPayment('qris')">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_qris" value="qris" checked>
                                            <label class="form-check-label" for="pay_qris">
                                                <i class="bx bx-qr-scan me-2"></i>QRIS
                                            </label>
                                            <div class="small text-muted mt-2">Scan QR Code untuk pembayaran</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="payment-method-card border rounded p-3 h-100" onclick="selectPayment('transfer')">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_transfer" value="transfer">
                                            <label class="form-check-label" for="pay_transfer">
                                                <i class="bx bx-bank me-2"></i>Transfer Bank
                                            </label>
                                            <div class="small text-muted mt-2">BCA - 123456789 a/n FoodOrder</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="payment-method-card border rounded p-3 h-100" onclick="selectPayment('tunai')">
                                            <input class="form-check-input" type="radio" name="metode" id="pay_cash" value="tunai">
                                            <label class="form-check-label" for="pay_cash">
                                                <i class="bx bx-money me-2"></i>Tunai
                                            </label>
                                            <div class="small text-muted mt-2">Bayar saat pesanan diterima</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code Display Section -->
                            <div id="qrSection" class="qr-container" style="display: none;">
                                <div class="text-center mb-3">
                                    <h6 class="mb-3">
                                        <i class="bx bx-qr-scan me-2"></i>
                                        Scan QR Code untuk Pembayaran
                                    </h6>
                                </div>
                                <div class="qr-info">
                                    <div class="qr-info">
                                    <div class="row mb-3">
                                            <strong>ID Merchant:</strong><br>
                                            <span class="text-primary">ID123456789012</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Jumlah Pembayaran:</strong><br>
                                            <span class="text-danger fs-5 fw-bold">Rp <?php echo number_format((float)$order['total_harga'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>ID Pesanan:</strong><br>
                                            <span class="text-primary">#<?php echo str_pad($order['id_pesanan'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            <i class="bx bx-time-five me-1"></i>
                                            Berlaku selama 30 menit
                                        </small>
                                    </div>
                                </div>
                                <div class="qr-code-wrapper text-center">
                                    <div id="qrcode" class="qr-code"></div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadQR()">
                                            <i class="bx bx-download me-1"></i> Download QR
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" onclick="refreshQR()">
                                            <i class="bx bx-refresh me-1"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading">
                                        <i class="bx bx-info-circle me-2"></i>
                                        Cara Pembayaran:
                                    </h6>
                                    <ol class="mb-0">
                                        <li>Buka aplikasi pembayaran (GoPay, OVO, Dana, ShopeePay, dll)</li>
                                        <li>Pilih menu "Scan QR" atau "QRIS Payment"</li>
                                        <li>Arahkan kamera ke QR code ini</li>
                                        <li>Konfirmasi pembayaran dengan jumlah yang tertera</li>
                                        <li>Tunggu notifikasi pembayaran berhasil</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <div>
                                    Setelah Anda menekan tombol "Saya sudah bayar", status pesanan akan menjadi <strong>Diproses</strong> dan admin akan segera menyiapkan pesanan Anda.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="background:#ff6b35; border-color:#ff6b35;">
                                Saya sudah bayar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script>
        let currentQRCode = null;

        function generateQRCode(amount, orderId) {
            // Create proper QRIS payment data format following Indonesian standard
            const merchantName = "FoodOrder System";
            const merchantId = "123456789012"; // Static merchant ID for demo
            const nmid = "ID123456789012"; // QRIS Merchant ID
            
            // Format amount to proper QRIS standard (in cents, no decimal)
            const amountInCents = Math.round(amount * 100);
            const amountStr = amountInCents.toString().padStart(12, '0');
            
            // Create QRIS data following exact Indonesian QRIS standard for static QR
            // Format: 00020101021226580012 + Merchant ID (16 chars) + 0214 + Amount (12 chars) + 5802 + ID59
            const qrData = `00020101021226580012ID1234567890120214${amountStr}5802ID59`;
            
            // Generate QR code with high error correction for reliable scanning
            const qr = qrcode(0, 'H'); // High error correction level
            qr.addData(qrData);
            qr.make();
            
            return qr.createImgTag(8, 0); // Even larger size for better readability
        }

        function selectPayment(method) {
            // Update radio button
            document.getElementById('pay_' + method).checked = true;
            
            // Update card styles
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            // Show/hide QR section
            const qrSection = document.getElementById('qrSection');
            if (method === 'qris') {
                qrSection.style.display = 'block';
                generateAndShowQR(); // Generate QR immediately
            } else {
                qrSection.style.display = 'none';
            }
        }

        function generateAndShowQR() {
            const amount = <?php echo (float)$order['total_harga']; ?>;
            const orderId = <?php echo (int)$order['id_pesanan']; ?>;
            
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = generateQRCode(amount, orderId);
            qrContainer.dataset.orderId = orderId; // Store order ID for download
        }

        // Download QR Code function
        function downloadQR() {
            const qrImage = document.querySelector('#qrcode img');
            if (qrImage) {
                const link = document.createElement('a');
                link.download = `QRIS_Payment_Order_${document.getElementById('qrcode').dataset.orderId}.png`;
                link.href = qrImage.src;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }

        // Refresh QR Code function
        function refreshQR() {
            generateAndShowQR();
            showAlert('success', 'QR Code berhasil diperbarui!');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show QR code if QRIS is selected by default
            if (document.getElementById('pay_qris').checked) {
                generateAndShowQR();
                document.querySelector('.payment-method-card').classList.add('selected');
            }
        });
    </script>
</body>
</html>
