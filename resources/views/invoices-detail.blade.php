<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Detail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background-color: #f4f7fa; */
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 50%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h5 {
            color: #2c3e50;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .col {
            width: 48%;
        }

        .text-muted {
            color: #95a5a6;
        }

        .text-end {
            text-align: right;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .table td.text-end {
            text-align: right;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table .fw-semibold {
            font-weight: 600;
        }

        .total-row {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .total-amount {
            font-size: 18px;
            color: #27ae60;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .invoice-header h5 {
            margin: 0;
            font-size: 20px;
        }

        .invoice-date {
            font-size: 14px;
            color: #7f8c8d;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .company-details {
            margin-top: 10px;
            font-size: 16px;
            color: #7f8c8d;
        }

        /* Responsive styling */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 15px;
            }

            .row {
                flex-direction: column;
                align-items: flex-start;
            }

            .col {
                width: 100%;
                margin-bottom: 20px;
            }

            .text-end {
                text-align: left;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="invoice-header">
        <div class="col">
            <h5>Invoice Detail</h5>
        </div>
        
    </div>

    <div class="row">
        <div class="col">
            <div class="company-details">
                <p class="text-muted">Billed To</p>
            </div>
            <div class="company-name"><?php echo $billed->company_name; ?></div>
        </div>
        <div class="col text-end">
            
            <div class="company-details">
                <p class="text-muted">Invoice Date:</p>
                <div class="company-name">
                    <strong><?php echo \Carbon\Carbon::now()->format('d F Y'); ?></strong>
                </div>
            </div>
            
        </div>
    </div>

    <div class="py-2">
        <h5>Order Summary</h5>
    
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="fw-bold">No.</th>
                        <th class="fw-bold">Item</th>
                        <th class="fw-bold">Quantity</th>
                        <th class="fw-bold"></th>
                        <th class="fw-bold">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $price = 150000;
                    $total = count($produk) * $price;
                    ?>
                    <?php foreach ($produk as $item): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $item->nama_produk; ?></td>
                            <td><?php echo $item->quantity; ?></td>
                            <td></td>
                            <td class="">Rp <?php echo number_format($price); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4" class="">Total</td>
                        <td class="total-amount">Rp <?php echo number_format($total); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Keterangan Nomor Rekening -->
    <div class="bank-details">
        <h5>Payment Details</h5>
        <p>Please make the payment to the following account:</p>
        <p><strong>Bank Name:</strong> BCA</p>
        <p><strong>Account Name:</strong> Swalayan KITA</p>
        <p><strong>Account Number:</strong> 123-456-7890</p>
    </div>
    
</div>

</body>
</html>
