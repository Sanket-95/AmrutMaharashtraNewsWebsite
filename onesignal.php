<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f0f0f0;
        }
        .hello {
            color: #333;
            font-size: 48px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>

    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>

    <script>
    window.OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
    OneSignal.init({
        appId: "YOUR_APP_ID"
    });
    });
</script>
</head>
<body>
    <div class="hello">
        <?php
            echo "Hello, World!";
        ?>
    </div>
</body>
</html>