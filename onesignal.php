<script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    
    OneSignalDeferred.push(async function(OneSignal) {
        await OneSignal.init({
            appId: "adcb3826-1ee5-4416-8e63-881e47eeaf42",
            serviceWorkerPath: "OneSignalSDKWorker.js",  // Same directory
            serviceWorkerUpdaterPath: "OneSignalSDKUpdaterWorker.js"  // Same directory
        });
        
        OneSignal.showSlidedownPrompt();
        
        OneSignal.getUserId().then(function(userId) {
            console.log("User ID:", userId);
        });
    });
</script>