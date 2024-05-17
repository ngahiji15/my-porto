<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Load uuid library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uuid/8.3.2/uuid.min.js"></script>
    <!-- Load CryptoJS library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <!-- Load jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <button type="button" id="buttonPay">get url</button>
    
    <script>
        var checkoutButton = document.getElementById('buttonPay');

        checkoutButton.addEventListener('click', function() {
            var base_url = "https://api-sandbox.doku.com";
            var endpoints = "/checkout/v1/payment";
            var client_id = "BRN-0211-1711162968689";

            // Perbaiki pemanggilan uuid.v4() menjadi uuid.v4()
            var request_id = uuid.v4();
            console.log("Request-Id: " + request_id);
            var request_timestamp = new Date().toISOString().slice(0, 19) + "Z";
            console.log("Request-Timestamp: " + request_timestamp);
            var secret_key = "SK-8S3Q9rUWv7r6LYaZpaTv";
            var datePart = request_timestamp.slice(0, 10).replace(/-/g, '');
            var timePart = request_timestamp.slice(11, 19).replace(/:/g, '');

            var invoice_number = "INV-" + datePart + '-' + timePart;
            console.log("Invoice-Number: " + invoice_number);

            var body = JSON.stringify({
                order: {
                    amount: 20000,
                    invoice_number: invoice_number,
                    currency: "IDR",
                    session_id: "DOKUTEST",
                    disable_retry_payment: true,
                    callback_url: "http://doku.com",
                    auto_redirect: true,
                    line_items: [{
                        name: "Fresh flowers",
                        price: 20000,
                        quantity: 1
                    }]
                },
                payment: {
                    payment_due_date: 60
                },
                customer: {
                    id: "CUST-1",
                    name: "test1",
                    email: "email1@gmail.com"
                }
            });
            console.log(body);
            //var digest = CryptoJS.enc.Base64.stringify(CryptoJS.SHA256(body));
           var digestSHA256 = CryptoJS.SHA256(CryptoJS.enc.Utf8.parse(body));
           var digest = CryptoJS.enc.Base64.stringify(digestSHA256);
            console.log("Digest: " + digest);

            var component_signature = "Client-Id:" + client_id + "\n" + "Request-Id:" + request_id +
                "\n" + "Request-Timestamp:" + request_timestamp + "\n" + "Request-Target:" +
                endpoints + "\n" + "Digest:" + digest;
            console.log("Component-Signature: " + component_signature);

            var signatureHmacSHA256 = CryptoJS.HmacSHA256(component_signature, secret_key);
            var signature = "HMACSHA256=" + CryptoJS.enc.Base64.stringify(signatureHmacSHA256);
            console.log("Signature: " + signature);

            $.ajax({
                url: '/api/getPaymentUrl',
                method: "POST",
                contentType: "application/json",
                data: body,
                async: false,
                headers: {
                    'Client-Id': client_id,
                    'Request-Id': request_id,
                    'Request-Timestamp': request_timestamp,
                    'Signature': signature,
                },
                success: function(response) {
                    console.log(response);
                    // Handle successful response here
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    // Handle error response here
                }
            });
        });
    </script>
</body>
</html>
