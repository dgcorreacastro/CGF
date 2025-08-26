const labeledDescriptors = [];
let FaceMatcher;
Promise.all([
    faceapi.nets.ssdMobilenetv1.loadFromUri("/assets/js/models"),
    faceapi.nets.tinyFaceDetector.loadFromUri("/assets/js/models"),
    faceapi.nets.faceLandmark68Net.loadFromUri("/assets/js/models"),
    faceapi.nets.faceRecognitionNet.loadFromUri("/assets/js/models"),
]).then(() => {
    window.ReactNativeWebView.postMessage(JSON.stringify({loadedFaceModels: true}));
});

async function createLabeledDescriptors(descriptors, isFirst){
    
    try {

        for (const ds of descriptors) {

            let labeledDescriptor = labeledDescriptors.find(descriptor => descriptor.label === ds.controle_acesso_id);
            
            var stringArray = ds.ds.split(',');
    
            var floatArray = stringArray.map(function(item) {
                return parseFloat(item);
            });
    
            var descriptorOK = new Float32Array(floatArray);
    
            if (!labeledDescriptor) {
                labeledDescriptor = new faceapi.LabeledFaceDescriptors(ds.controle_acesso_id, [descriptorOK]);
                labeledDescriptors.push(labeledDescriptor);
            }else{
                labeledDescriptor.descriptors.push(descriptorOK);
            }

            if(labeledDescriptors.length > 0){
                FaceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
            }
    
        }
        
        if(isFirst){
            window.ReactNativeWebView.postMessage(JSON.stringify({loadedUsers: true}));
        }
        
        
    } catch {

        if(isFirst){
            window.ReactNativeWebView.postMessage(JSON.stringify({loadedUsers: false}));
        }

    }

}

async function pushDs(user, descriptor){

    let labeledDescriptor = labeledDescriptors.find(descriptor => descriptor.label === user);

    if(labeledDescriptor){
        labeledDescriptor.descriptors.push(descriptor);
    }

    if(labeledDescriptors.length > 0){
        FaceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.5);
    }

}

async function firstTime(img){

    const detImg = new Image();

    function cleanup() {
        detImg.onload = null;
        detImg.onerror = null;
        detImg.src = '';
    }

    detImg.src = `data:image/jpg;base64,${img}`;

    detImg.onload = async () => {
        try {

            await faceapi.detectSingleFace(detImg).withFaceLandmarks().withFaceDescriptor();
            window.ReactNativeWebView.postMessage(JSON.stringify({loadedFirst: true}));
    
        } catch {
            window.ReactNativeWebView.postMessage(JSON.stringify({loadedFirst: false}));
        }finally{
            cleanup();
        }
    };

    detImg.onerror = () => {
        window.ReactNativeWebView.postMessage(JSON.stringify({loadedFirst: false}));
        cleanup();
    };

}

async function getDescriptor(metadata){

    const { img } = metadata;
    const detImg = new Image();

    function cleanup() {
        detImg.onload = null;
        detImg.onerror = null;
        detImg.src = '';
    }

    detImg.src = `data:image/jpg;base64,${img}`;

    detImg.onload = async () => {
        try {
            const detectionDescriptor = await faceapi.detectSingleFace(detImg).withFaceLandmarks().withFaceDescriptor();
            metadata.score = detectionDescriptor ? detectionDescriptor.detection.score : 0;
            if (detectionDescriptor && detectionDescriptor.detection.score >= 0.8 && labeledDescriptors.length > 0) {

                metadata.ds = Object.values(detectionDescriptor.descriptor).join(',');

                const bestMatch = FaceMatcher.findBestMatch(detectionDescriptor.descriptor);

                if (bestMatch && bestMatch.label !== 'unknown') {
                    metadata.controle_acesso_id = bestMatch.label;
                    window.ReactNativeWebView.postMessage(JSON.stringify({addRecognized: metadata}));
                    pushDs(bestMatch.label, detectionDescriptor.descriptor);
                }else{
                    metadata.controle_acesso_id = 0;                               
                    window.ReactNativeWebView.postMessage(JSON.stringify({addNotRecognized: metadata}));
                }

            }else{
                metadata.controle_acesso_id = 0;
                metadata.ds = '';
                window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: metadata}));
            }
        } catch {
            metadata.controle_acesso_id = 0;
            metadata.ds = '';
            window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: metadata}));
        }finally{
            cleanup();
        }
    };

    detImg.onerror = () => {
        metadata.controle_acesso_id = 0;
        metadata.ds = '';
        window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: metadata}));
        cleanup();
    };
}


async function takePicture(img){

    const detImg = new Image();

    function cleanup() {
        detImg.onload = null;
        detImg.onerror = null;
        detImg.src = '';
    }

    detImg.src = `data:image/jpg;base64,${img}`;

    detImg.onload = async () => {
        try {
            const detectionDescriptor = await faceapi.detectSingleFace(detImg).withFaceLandmarks().withFaceDescriptor();
            
            if (detectionDescriptor && detectionDescriptor.detection.score >= 0.8) {

                window.ReactNativeWebView.postMessage(JSON.stringify({picDs: Object.values(detectionDescriptor.descriptor).join(',')}));

            }else{
                window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: true}));
            }
        } catch {
            window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: true}));
        }finally{
            cleanup();
        }
    };

    detImg.onerror = () => {
        window.ReactNativeWebView.postMessage(JSON.stringify({tryAgain: true}));
        cleanup();
    };
}