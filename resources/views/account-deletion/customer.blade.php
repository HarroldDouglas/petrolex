<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Author" content="Afrik Solutions">
    <title>Account Deletion - Petrolex Customer App</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/mini-logo.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #227093;
            --secondary-color: #c9a505;
            --font-family: 'Poppins', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            line-height: 1.6;
            color: #333;
            background: #f5f7fa;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 40px 30px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(34, 112, 147, 0.2);
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            height: 80px;
            width: auto;
            background: white;
            padding: 10px;
            border-radius: 10px;
        }

        .header h1 {
            font-size: 2em;
            margin: 20px 0 10px;
            font-weight: 600;
        }

        .header .subtitle {
            font-size: 1.1em;
            opacity: 0.95;
            font-weight: 300;
        }

        .content-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        h2 {
            color: var(--primary-color);
            margin-top: 35px;
            margin-bottom: 20px;
            font-size: 1.6em;
            font-weight: 600;
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 12px;
            display: inline-block;
        }

        h2:first-child {
            margin-top: 0;
        }

        .section {
            margin: 30px 0;
        }

        .steps {
            counter-reset: step-counter;
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }

        .steps li {
            counter-increment: step-counter;
            padding: 25px 25px 25px 70px;
            margin: 20px 0;
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            position: relative;
            transition: all 0.3s ease;
        }

        .steps li:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .steps li::before {
            content: counter(step-counter);
            position: absolute;
            left: 20px;
            top: 20px;
            background: var(--primary-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1em;
        }

        .steps li strong {
            color: var(--primary-color);
            font-size: 1.05em;
            display: block;
            margin-bottom: 5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .badge-deleted {
            background: #d4edda;
            color: #155724;
        }

        .badge-kept {
            background: #fff3cd;
            color: #856404;
        }

        .alert {
            padding: 18px 20px;
            margin: 25px 0;
            border-radius: 8px;
            border-left: 5px solid;
        }

        .alert-warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        .contact-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 8px;
            margin-top: 25px;
            text-align: center;
            border: 2px solid var(--secondary-color);
        }

        .contact-box strong {
            color: var(--primary-color);
            font-size: 1.15em;
        }

        .contact-box a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-box a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            padding: 30px 20px;
            color: #6c757d;
            font-size: 0.9em;
        }

        .footer strong {
            color: var(--primary-color);
        }

        ul {
            margin-left: 25px;
            margin-top: 15px;
        }

        ul li {
            margin: 10px 0;
            padding-left: 10px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 1.5em;
            }

            .content-card {
                padding: 25px 20px;
            }

            h2 {
                font-size: 1.3em;
            }

            .steps li {
                padding: 20px 20px 20px 60px;
            }

            .steps li::before {
                width: 30px;
                height: 30px;
                left: 15px;
            }

            .data-table {
                font-size: 0.9em;
            }

            .data-table th,
            .data-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ asset('assets/images/logo/isogaz-white-bg.png') }}" alt="Petrolex Logo">
            </div>
            <h1>🗑️ Account Deletion</h1>
            <p class="subtitle">Petrolex Customer Application</p>
        </div>

        <div class="content-card">
            <div class="section">
                <h2>📱 About the Application</h2>
                <p>
                    The <strong>Petrolex Customer App</strong>, developed by <strong>Afrik Solutions</strong>,
                    allows you to order and receive your gas cylinders easily and conveniently.
                </p>
            </div>

            <div class="section">
                <h2>🔐 How to Delete My Account</h2>
                <p>
                    You can request the deletion of your account at any time by contacting our support team:
                </p>

                <ol class="steps">
                    <li>
                        <strong>Open the Petrolex Customer App</strong><br>
                        Log in with your credentials and access your profile to verify your account information.
                    </li>
                    <li>
                        <strong>Send a deletion request by email</strong><br>
                        Send an email to <strong><a href="mailto:support@petrolex.cm">support@petrolex.cm</a></strong> with the subject line: <em>"Account Deletion Request"</em>
                    </li>
                    <li>
                        <strong>Include your information</strong><br>
                        In your email, provide: your full name, registered email address, and phone number to help us identify your account.
                    </li>
                    <li>
                        <strong>Confirmation and processing</strong><br>
                        Our support team will confirm receipt of your request. Your account will be permanently deleted within 7 business days.
                    </li>
                </ol>

                <div class="alert alert-warning">
                    <strong>⚠️ Warning:</strong> Account deletion is permanent and irreversible.
                    All your personal data will be permanently removed from our systems.
                </div>
            </div>

            <div class="section">
                <h2>📊 What Data is Processed?</h2>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Status After Deletion</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Personal Information</strong><br>
                                (Name, email, phone)
                            </td>
                            <td><span class="badge badge-deleted">✓ Deleted</span></td>
                            <td>Immediate and permanent deletion</td>
                        </tr>
                        <tr>
                            <td><strong>Delivery Addresses</strong></td>
                            <td><span class="badge badge-deleted">✓ Deleted</span></td>
                            <td>All your addresses are deleted</td>
                        </tr>
                        <tr>
                            <td><strong>Order History</strong></td>
                            <td><span class="badge badge-kept">⚠ Retained</span></td>
                            <td>Anonymized and retained for 5 years (legal accounting requirement)</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Data</strong></td>
                            <td><span class="badge badge-kept">⚠ Retained</span></td>
                            <td>Anonymized and retained for 5 years (legal tax requirement)</td>
                        </tr>
                        <tr>
                            <td><strong>Photos & Documents</strong></td>
                            <td><span class="badge badge-deleted">✓ Deleted</span></td>
                            <td>Immediate and permanent deletion</td>
                        </tr>
                        <tr>
                            <td><strong>Preferences & Settings</strong></td>
                            <td><span class="badge badge-deleted">✓ Deleted</span></td>
                            <td>Immediate and permanent deletion</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h2>⏱️ Retention Periods</h2>
                <p>After your account deletion:</p>
                <ul>
                    <li><strong>Immediate deletion</strong>: Your personally identifiable data is deleted within 7 days.</li>
                    <li><strong>Legal retention (5 years)</strong>: Billing and transaction data is anonymized and retained to comply with legal accounting and tax obligations.</li>
                    <li><strong>Technical logs (90 days)</strong>: Connection logs are automatically deleted after 90 days.</li>
                </ul>

                <div class="alert alert-info">
                    <strong>ℹ️ Why retain some data?</strong><br>
                    The anonymized retention of billing data is a legal requirement in Cameroon
                    (Article 27 of the General Tax Code). This data can no longer identify you.
                </div>
            </div>

            <div class="section">
                <h2>📞 Need Help?</h2>
                <div class="contact-box">
                    <p>If you encounter difficulties or have questions about deleting your account:</p>
                    <p style="margin-top: 15px;">
                        <strong>Email:</strong> <a href="mailto:support@petrolex.cm">support@petrolex.cm</a><br>
                        <strong>Phone:</strong> +237 6 77 88 99 00
                    </p>
                </div>
            </div>

            <div class="section">
                <h2>🔒 Your Rights</h2>
                <p>
                    In accordance with personal data protection regulations, you have the following rights:
                </p>
                <ul>
                    <li>Right to access your personal data</li>
                    <li>Right to rectify your data</li>
                    <li>Right to delete your data</li>
                    <li>Right to data portability</li>
                    <li>Right to object to data processing</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>
                <strong>Petrolex - Customer Application</strong><br>
                Developed by Afrik Solutions<br>
                Last updated: {{ now()->format('d/m/Y') }}
            </p>
        </div>
    </div>
</body>
</html>
