<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bank Statement</title>
    <style>
        *{margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent}body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f5f5f5}.container{max-width:800px;margin:0 auto;padding:20px;background-color:#fff;border:1px solid #ccc;box-shadow:0 0 10px rgba(0,0,0,.2);position:relative}.watermark{position:absolute;font-size:72px;font-weight:900;opacity:.2;top:35%;left:50%;transform:translate(-50%,-50%) rotate(-45deg)}.logo{text-align:center}.logo img{max-width:100%;margin-bottom:10px}.header{display:flex;justify-content:space-between;align-items:center}.address{text-align:right;margin-bottom:20px;font-size:14px}.receipt{text-align:center;margin-top:20px;margin-bottom:20px}.receipt h2{font-size:24px;margin:10px 0}.account-summary{margin-bottom:20px;border-collapse:collapse;width:100%}.account-summary th,.account-summary td{border:1px solid #ddd;padding:8px;text-align:left}.account-summary th{background-color:#f2f2f2}.transactions-table{width:100%;border-collapse:collapse;margin-top:20px}.transactions-table th,.transactions-table td{border:1px solid #ddd;padding:4px;text-align:left}.transactions-table th{background-color:#f2f2f2}.footer{margin-top:40px;text-align:center;margin-bottom:40px;font-style:italic;font-size:14px}.top-text{text-align:left;font-weight:bold;margin-bottom:20px}.customer-info{text-align:right;margin-bottom:20px}.dotted-line{border-bottom:2px dotted #ccc;margin-top:5px;margin-bottom:20px}.approved-container{text-align:center;margin-top:20px;max-width:200px;margin:0 auto}.approved-text{text-align:center;margin-top:5px;font-weight:600;font-size:12px}.signature-container{display:flex;align-items:center;justify-content:center;flex-direction:column}.signature-image{width:100px;margin-bottom:10px}table{font-size:12px}
    </style>
</head>

<body>
    <div class="container">
        <!-- Your HTML content -->
        <?php
        $totalDebit = 0;
        $totalCredit = 0;
        $balance = 0;
        ?>
        @foreach ($transactions as $transaction)
        <tr>
            <td>{{ $transaction->created_at }}</td>
            <td>{{ $transaction->comment }}</td>
            <td>{{ $transaction->type == 'debit' ? '-' . number_format($transaction->amount) : '' }}</td>
            <td>{{ $transaction->type == 'credit' ? number_format($transaction->amount) : '' }}</td>
            <td>
                <?php
                if ($transaction->type == 'debit') {
                    $balance -= $transaction->amount; // Subtracting debit amount
                } elseif ($transaction->type == 'credit') {
                    $balance += $transaction->amount; // Adding credit amount
                }
                echo number_format($balance);
                ?>
            </td>
        </tr>
        <?php
        if ($transaction->type == 'debit') {
            $totalDebit += $transaction->amount;
        } elseif ($transaction->type == 'credit') {
            $totalCredit += $transaction->amount;
        }
        ?>
        @endforeach
        <tr>
            <td colspan="2"><strong>Total</strong></td>
            <td><strong>{{ '-' . number_format($totalDebit) }}</strong></td> <!-- Display debits with negative sign -->
            <td><strong>{{ number_format($totalCredit) }}</strong></td>
            <td><strong>{{ number_format($balance) }}</strong></td> <!-- Display calculated balance here -->
        </tr>
    </div>
</body>

</html>
