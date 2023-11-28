<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Receipt</title>
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
            max-width: 600px;
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
            margin-bottom: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
        }

        .address {
            text-align: center;
            margin-top: -20px;
            margin-bottom: 40px;
            font-size: 12px;
            font-weight: 600;
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

        .footer {
            margin-top: 40px;
            text-align: center;
            margin-bottom: 40px;
            font-style: italic;
            font-size: 14px;
        }

        /* Dotted line styles */
        .dotted-line {
            border-bottom: 2px dotted #ccc;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        /* Additional styles */
        .top-text {
            text-align: left;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .approved-text {
            text-align: left;
            margin-top: 20px;
        }

        .amount-words {
            margin-top: 18px;
            font-style: italic;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="watermark">
            <p>APPROVED</p>
        </div>
        <div class="header">
            <div class="logo">
                <img src="logo.png" alt="Company Logo" />
            </div>
        </div>

        <div class="address">
            <p>{!! $address !!}</p>
        </div>

        <div class="receipt">
            <h2>Payment Receipt</h2>
            <p>Mar 1, 2023</p>
        </div>

        <div class="top-text">
            <p>Received with thanks from: <span>{{ $institution }}</span></p>
        </div>

        <div class="amount-words">
            <p>{{ $amountInWords }} Uganda Shillings only</p>
            <!-- Dotted line under the amount in words here -->
            <div class="dotted-line"></div>
            <p><strong>Amount In Words</strong></p>
        </div>

        <div class="amount-words">
            <p>{{ $amount }}</p>
            <!-- Dotted line under the amount in words here -->
            <div class="dotted-line"></div>
            <p><strong>Total Paid</strong></p>
        </div>

        <!-- Add a container for "Approved by" text and the dotted line -->
        <div class="approved-container">
            <div class="approved-text">
                <p style="margin-left: 150px; font-weight: 600; font-size: 12px">
                    {{ $approvedBy }}
                </p>
                <p>
                    Approved by ....................................................
                </p>
            </div>
        </div>

        <div class="footer">
            <p style="margin-bottom: 16px">
                For any billing and support queries please contact UNMEB at <br />
                info@unmeb.go.ug. Or Call +256-414-288947
            </p>
            <p>Thank you for your payment</p>
        </div>
    </div>
</body>

</html>
