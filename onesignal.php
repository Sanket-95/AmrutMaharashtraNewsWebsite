<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OneSignal Test</title>

```
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

<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>

<script>
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {

    await OneSignal.init({
        appId: "adcb3826-1ee5-4416-8e63-881e47eeaf42",

        // IMPORTANT for mobile
        serviceWorkerPath: "/OneSignalSDKWorker.js",
        serviceWorkerUpdaterPath: "/OneSignalSDKUpdaterWorker.js"
    });

    // Force popup (important for mobile)
    OneSignal.showSlidedownPrompt();
});
</script>
```

</head>

<body>

<div class="hello">
    <?php echo "Hello, World!"; ?>
</div>

</body>
</html>
