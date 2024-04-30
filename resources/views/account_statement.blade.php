<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bank Statement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
            font-size: 100%;
            vertical-align: baseline;
            background: transparent;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            /* To position the watermark */
        }

        .watermark {
            position: absolute;
            font-size: 72px;
            font-weight: 900;
            opacity: 0.2;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
        }

        .logo {
            text-align: center;
            /* Center the logo */
        }

        .logo img {
            max-width: 100%;
            /* Fit the logo to match the width of the parent container */
            margin-bottom: 10px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .address {
            text-align: right;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .receipt {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .receipt h2 {
            font-size: 24px;
            margin: 10px 0;
        }

        .account-summary {
            margin-bottom: 20px;
            border-collapse: collapse;
            width: 100%;
        }

        .account-summary th,
        .account-summary td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .account-summary th {
            background-color: #f2f2f2;
        }

        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }

        .transactions-table th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            margin-bottom: 40px;
            font-style: italic;
            font-size: 14px;
        }

        /* Additional styles */
        .top-text {
            text-align: left;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .customer-info {
            text-align: right;
            margin-bottom: 20px;
        }

        /* Dotted line styles */
        .dotted-line {
            border-bottom: 2px dotted #ccc;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* Approved by styles */
        .approved-container {
            text-align: center;
            margin-top: 20px;
            max-width: 200px;
            margin: 0 auto;
        }

        .approved-text {
            text-align: center;
            margin-top: 5px;
            font-weight: 600;
            font-size: 12px;
        }

        .signature-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .signature-image {
            width: 100px;
            margin-bottom: 10px;
        }

        table {
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="watermark">
            <p>STATEMENT</p>
        </div>
        <div class="header">
            <div class="logo">
                <img src="logo.png" alt="Company Logo" />
            </div>
            <div class="address">
                <p><strong>Institution Information</strong></p>
                <p>{{ $customerInfo['name'] }}
                    <br />
                    {{ $customerInfo['location'] }},
                    {{ $customerInfo['phone'] }}
                </p>
            </div>
        </div>

        <div class="receipt">
            <h2>Transactional Statement</h2>
            <p>{{ $statementDate }}</p>
        </div>

        <table class="account-summary">
            <thead>
                <tr>
                    <th colspan="2">Account Balance</th>
                    <th colspan="2">Total Expense</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2">{{ $balance }}</td>
                    <td colspan="2">{{ $expense }}</td>
                </tr>
            </tbody>
        </table>

        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalDebit = 0;
                $totalCredit = 0;
                $balance = 0;
                ?>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at }}</td>
                        <td>{{ $transaction->comment }}</td>
                        <td>{{ $transaction->type == 'debit' ? number_format($transaction->amount) : '' }}</td>
                        <td>{{ $transaction->type == 'credit' ? number_format($transaction->amount) : '' }}</td>
                        <td>
                            <?php
                            $balance += $transaction->type == 'debit' ? -$transaction->amount : $transaction->amount;
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
                    <td><strong>{{ number_format($totalDebit) }}</strong></td>
                    <td><strong>{{ number_format($totalCredit) }}</strong></td>
                    <td><strong>{{ number_format($balance) }}</strong></td> <!-- Display calculated balance here -->
                </tr>
            </tbody>


        </table>

        <div class="footer">
            <p style="margin-bottom: 16px">
                For any billing and support queries please contact UNMEB at <br />
                info@unmeb.go.ug. Or Call +256-414-288947
            </p>

        </div>
    </div>
</body>

</html>