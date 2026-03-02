<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Welcome to Our Platform</title>
  <style>
    /* Inline styles for simplicity, consider using CSS classes for larger templates */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #f1f1f1;
    }

    .logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo img {
      max-width: 200px;
    }

    .message {
      padding: 20px;
      background-color: #ffffff;
    }

    .message p {
      margin-bottom: 10px;
    }

    .footer {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <div class="container">

    <div class="message">
      <p>Dear {{ $data['name'] }},</p>
      <p>To proceed with generating your certificate, please use the One-Time Password (OTP) provided below:

      <h4>Your OTP: {{ $data['otp'] }}</h4>
      (This OTP is valid for the next 10 minutes.)
      <br><br>To generate your certificate:

      Visit the certificate generation page.
      Enter the OTP provided above.
      Click "Generate Certificate."
      If you did not request a certificate, or if you have any issues, please contact our support team immediately.

      Thank you for your dedication to learning!
    </div>

  </div>
</body>

</html>