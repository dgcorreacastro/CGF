<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <title>TESTE</title>
        
    </head>
    <body>

        <script defer src="<?php echo BASE_URL; ?>assets/js/face-api.min.js"></script>
        <script defer src="<?php echo BASE_URL; ?>assets/js/detectionDescriptor.js?<?php echo time(); ?>"></script>
        <script>

                document.addEventListener('message', async function(event) {
                    var message = JSON.parse(event.data);

                    if (message.hasOwnProperty('data') && message.hasOwnProperty('type')) {
                        var messageType = message.type;

                        if(messageType === 'detectionDescriptor'){
                            try{
                                await getDescriptor(message.data);
                            }catch (error) {
                                window.ReactNativeWebView.postMessage(JSON.stringify({webStatus: error}));
                            }
                        }

                        if(messageType === 'createLabeledDescriptors'){
                            try{
                                await createLabeledDescriptors(message.data.descriptors, message.data.isFirst);
                            }catch (error) {
                                window.ReactNativeWebView.postMessage(JSON.stringify({webStatus: error}));
                            }
                        }

                        if(messageType === 'firstTime'){
                            try{
                                await firstTime(message.data);
                            }catch (error) {
                                window.ReactNativeWebView.postMessage(JSON.stringify({webStatus: error}));
                            }
                        }

                        if(messageType === 'takePicture'){
                            try{
                                await takePicture(message.data);
                            }catch (error) {
                                window.ReactNativeWebView.postMessage(JSON.stringify({webStatus: error}));
                            }
                        }
                    }
                });

        </script>
    </body>
</html>