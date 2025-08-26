let worker;
const workerVersion = '3.0';
const workerUrl = `/assets/js/userPhotoWorker.js?v=${workerVersion}`;
Promise.all([
  faceapi.nets.ssdMobilenetv1.loadFromUri("/assets/js/models"),
  faceapi.nets.tinyFaceDetector.loadFromUri("/assets/js/models"),
  faceapi.nets.faceLandmark68Net.loadFromUri("/assets/js/models"),
  faceapi.nets.faceRecognitionNet.loadFromUri("/assets/js/models"),
]).then(() => {

  worker = new Worker(workerUrl);

  worker.onmessage = function(event) {

    const action = event.data.action;
    const data = event.data.data;

    try{

      if(action === 'sendToRN'){

        let send = {
          webStatus: data.message,
          wd: data.wd ?? undefined
        };

        try{
          window.ReactNativeWebView.postMessage(JSON.stringify(send));
        } catch {
          console.log(send);
        }

      }else if(action === 'getLabelFaces'){

        getLabelFaces(data.users)

      }else if(action === 'getDetections'){

        getDetections();

      }else if(action === 'doDetections'){

        doDetections(data.detections, data.screenWidth, data.screenHeight);

      }else if(action === 'treatRecognitions'){
        treatRecognitions(data.recognitions);
      }

    }catch{}

  };

});


function btnGetUser(){

  // $('.recognition').remove();
  const veiculo_id = $("#veiculo_id").val();
  const device_id = $("#device_id").val();
  worker.postMessage({ action: 'getUsers', data: {veiculo_id: veiculo_id, device_id: device_id}});
  
}

const labeledDescriptors = [];

async function createLabeledDescriptors(descriptors){

  for (const ds of descriptors) {

    worker.postMessage({ action: 'insertLoadedId', data: { ds_id: ds.id } });

    let labeledDescriptor = labeledDescriptors.find(descriptor => descriptor.label === ds.controle_acesso_id);
    
    var stringArray = ds.ds.split(',');

    var floatArray = stringArray.map(function(item) {
      return parseFloat(item);
    });

    var descriptorOK = new Float32Array(floatArray);

    if (!labeledDescriptor) {
      labeledDescriptor = new faceapi.LabeledFaceDescriptors(ds.controle_acesso_id, [descriptorOK]);
    }else{
      labeledDescriptor.descriptors.push(descriptorOK);
    }

    if (!labeledDescriptors.find(descriptor => descriptor.label === ds.controle_acesso_id)) {
      labeledDescriptors.push(labeledDescriptor);
    }

  }
  
}

async function getLabelFaces(descriptors) {
  try {
    await createLabeledDescriptors(descriptors);
    await getDetections();
  } catch (error) {
    console.error('Erro:', error);
  }
}

async function getDetections() {

  if (labeledDescriptors.length > 0) {
    worker.postMessage({ action: 'getDetections' });
  }

}

let noTrust = [];
let notRecognized = [];
let matchMap = {};

async function doDetections(detections, screenWidth, screenHeight){

  try {

    if (labeledDescriptors.length > 0) {

      for (const detection of detections) {

        worker.postMessage({ action: 'insertLoadedDetection', data: { detection_id: detection.id } });

        const image = await faceapi.fetchImage(detection.imgUrl);

        const detectionDescriptor = await faceapi.detectSingleFace(image).withFaceLandmarks().withFaceDescriptor();

        if (detectionDescriptor && detectionDescriptor.detection.score >= 0.6) {

          const faceBox = detectionDescriptor.detection.box;

          const scaleX = screenWidth / image.width;
          const scaleY = screenHeight / image.height;
          const scaledFaceBoxX = Math.ceil(faceBox.x * scaleX);
          const scaledFaceBoxY = Math.ceil(faceBox.y * scaleY);
          const scaledFaceBoxW = Math.ceil(faceBox.width * scaleX);
          const scaledFaceBoxH = Math.ceil(faceBox.height * scaleY);

          const rightSize = (scaledFaceBoxH >= 150);

          const checkFrontalFace = await isFrontalFace(detectionDescriptor);

          if(rightSize && checkFrontalFace){

            // appendDetection(detection, screenWidth, screenHeight, image, detectionDescriptor);

            notRecognized.push({
              detectionId: detection.id,
              real_time: detection.real_time,
              detectionDescriptor: detectionDescriptor,
              scaledFaceBoxX, scaledFaceBoxY, scaledFaceBoxW, scaledFaceBoxH, screenWidth, screenHeight
            });

            for (const user of labeledDescriptors) {

              for (const descriptor of user.descriptors) {

                const faceMatcher = new faceapi.FaceMatcher(descriptor);
                const bestMatch = faceMatcher.findBestMatch(detectionDescriptor.descriptor);

                if (bestMatch && bestMatch._distance <= 0.46 && bestMatch.label !== 'unknown') {

                  // Atualiza o mapa de correspondência
                  if (!matchMap[detection.id]) {
                      matchMap[detection.id] = {};
                  }
                  if (!matchMap[detection.id][user.label]) {
                      matchMap[detection.id][user.label] = 1;
                  }
                  matchMap[detection.id][user.label]++;

                }

              }

            }

          }else{

            // appendDetection(detection, screenWidth, screenHeight, image);
  
            noTrust.push(Number(detection.id));
  
          }

        }else{

          // appendDetection(detection, screenWidth, screenHeight, image);

          noTrust.push(Number(detection.id));

        }

      }
      
      doRecognations();

    }

  } catch (error) {

    console.error('Erro:', error);

  }

}

function doRecognations() {
  
  let recognized = [];
  const notRecognizedOk = [];
  
  for (const detectionId in matchMap) {
      const matches = matchMap[detectionId];
      let maxMatchCount = 0;
      let userWithMaxMatches = null;

      for (const userLabel in matches) {
          const matchCount = matches[userLabel];
          if (matchCount >= 3 && matchCount > maxMatchCount) {
              maxMatchCount = matchCount;
              userWithMaxMatches = userLabel;
          }
      }

      if (userWithMaxMatches) {
        
        const inNotRecognizedIndex = notRecognized.findIndex(item => item.detectionId === detectionId);

        if (inNotRecognizedIndex !== -1) {

          const {
            real_time,
            scaledFaceBoxX,
            scaledFaceBoxY,
            scaledFaceBoxW,
            scaledFaceBoxH,
            screenWidth,
            screenHeight
          } = notRecognized[inNotRecognizedIndex];

          recognized.push({
            detectionId: Number(detectionId),
            real_time,
            userId: Number(userWithMaxMatches),
            matchCount: maxMatchCount,
            scaledFaceBoxX,
            scaledFaceBoxY,
            scaledFaceBoxW,
            scaledFaceBoxH,
            screenWidth: Number(screenWidth),
            screenHeight: Number(screenHeight)
          });  

          notRecognized.splice(inNotRecognizedIndex, 1);

        }

      }

  }

  const recognizedByUserId = Object.values(recognized.reduce((acc, item) => {
    if (!acc[item.userId] || item.matchCount > acc[item.userId].matchCount) {
      acc[item.userId] = { ...item };
      delete acc[item.userId].matchCount;
    }
    return acc;
  }, {}));
  
  const flattenedRecognizedByUserId = recognizedByUserId.flatMap(group => group);

  const recognizedDetectionIdsSet = new Set(flattenedRecognizedByUserId.map(item => item.detectionId));

  const removeDuplicatesRec = recognized.filter(item => !recognizedDetectionIdsSet.has(item.detectionId)).map(item => item.detectionId);

  // for (const recognizedItem of recognizedByUserId) {

  //   $(`#reconhecido_${recognizedItem.detectionId}`).html(recognizedItem.userId);

  // }

  // for (const removeDuplicate of removeDuplicatesRec) {

  //   $(`.detection[id=${removeDuplicate}]`).remove();

  // }

  // for (const ntItem of noTrust) {
  //   $(`.detection[id=${ntItem}]`).remove();
  // }

  const removeDuplicateNRec = [];

  if(notRecognized.length > 1){

    const indicesToRemove = [];
    const totalNr = notRecognized.length;

    notRecognized.forEach((item, index) => {

      if((index + 1) < totalNr){

        const faceMatcher = new faceapi.FaceMatcher(item.detectionDescriptor.descriptor);
        const bestMatch = faceMatcher.findBestMatch(notRecognized[index + 1].detectionDescriptor.descriptor);
        
        if (bestMatch && bestMatch._distance <= 0.5) {
          // $(`.detection[id=${item.detectionId}]`).remove();
          removeDuplicateNRec.push(Number(item.detectionId));
          indicesToRemove.push(index);
        }

      }

    });

    indicesToRemove.reverse().forEach(index => {
      notRecognized.splice(index, 1);
    });

  }

  for (const notRecognizedItem of notRecognized) {

    const {
      detectionId,
      real_time,
      scaledFaceBoxX,
      scaledFaceBoxY,
      scaledFaceBoxW,
      scaledFaceBoxH,
      screenWidth,
      screenHeight
    } = notRecognizedItem;

    // $(`#reconhecido_${notRecognizedItem.detectionId}`).html('Não Reconhecido.');

    notRecognizedOk.push({
      detectionId: Number(detectionId),
      real_time,
      userId: 0,
      scaledFaceBoxX,
      scaledFaceBoxY,
      scaledFaceBoxW,
      scaledFaceBoxH,
      screenWidth: Number(screenWidth),
      screenHeight: Number(screenHeight)
    });
  }
  
  doRecognationsWorker(recognizedByUserId, removeDuplicatesRec, notRecognizedOk, removeDuplicateNRec);
}

function doRecognationsWorker(recognizedByUserId, removeDuplicatesRec, notRecognizedOk, removeDuplicateNRec){

  const data = {
    recognizedByUserId,
    removeDuplicatesRec,
    notRecognizedOk,
    removeDuplicateNRec,
    noTrust
  }

  noTrust = [];
  notRecognized = [];
  matchMap = {};

  worker.postMessage({ action: 'doRecognationsWorker', data: data });

}

async function treatRecognitions(recognitions){

  // $('.recognition').remove();
  // $('.detection').remove();

  const newRecognitions = [];
  const labeledDescriptors = [];

  for (const recognition of recognitions) {
    
    if(recognition.controle_acesso_id != 0){

      let labeledDescriptor = labeledDescriptors.find(descriptor => descriptor.label === recognition.controle_acesso_id);
      if(!labeledDescriptor) {
        const image = await faceapi.fetchImage(recognition.imgUrl);
        const descriptor = await faceapi.detectSingleFace(image).withFaceLandmarks().withFaceDescriptor();
        if(descriptor){
          labeledDescriptor = new faceapi.LabeledFaceDescriptors(recognition.controle_acesso_id, [descriptor.descriptor]);
          labeledDescriptors.push(labeledDescriptor);
        }
        
      }
    }
    
  }

  const faceMatcher = labeledDescriptors.length > 0 ? new faceapi.FaceMatcher(labeledDescriptors) : false;

  for (const recognition of recognitions) {

    let controle_acesso_id = recognition.controle_acesso_id;

    if(controle_acesso_id == 0 && faceMatcher){

      const image = await faceapi.fetchImage(recognition.imgUrl);

      const recognitionDescriptor = await faceapi.detectSingleFace(image).withFaceLandmarks().withFaceDescriptor();

      if(recognitionDescriptor){

        const bestMatch = faceMatcher.findBestMatch(recognitionDescriptor.descriptor);

        if (bestMatch && bestMatch._distance <= 0.46 && bestMatch.label !== 'unknown') {

          controle_acesso_id = bestMatch.label;
          newRecognitions.push({
            id: Number(recognition.id),
            controle_acesso_id: Number(controle_acesso_id),
            real_time: recognition.real_time,
            facedetection_id: recognition.facedetection_id
          });
          
        }

      }

    }

    // const recognitionDiv = $(`<div class="recognition" id="${recognition.id}"><img src="${recognition.imgUrl}">
    // ${`<p>${controle_acesso_id}</p>`}${`<p>${recognition.formated_time} - ${recognition.facedetection_id}</p>`}</div>`);
    // $('body').append(recognitionDiv);

  }
  
  if(newRecognitions.length > 0){
    worker.postMessage({ action: 'updateRecogntions', data: newRecognitions });
  }else{
    worker.postMessage({ action: 'setEnd' });
  }

}

// function appendDetection(detection, screenWidth, screenHeight, image, detectionDescriptor = false){

//   const detection_id = detection.id;
//   const detection_img = detection.imgUrl;

//   const imgWidth = image.width;
//   const imgHeight = image.height;

//   const detectionDiv = $(`<div class="detection" id="${detection_id}" style="opacity: ${detectionDescriptor ? '1' : '0.5'};">
//       <img src="${detection_img}" width="${screenWidth}" height="${screenHeight}">
//       <p>id: ${detection_id}</p>
//       <p id="reconhecido_${detection_id}">${detectionDescriptor ? 'Analisando...':'Não Confiar.'}</p>
//   </div>`);

//   if(detectionDescriptor){
//     const faceBox = detectionDescriptor.detection.box;
//     const scaleX = screenWidth / imgWidth;
//     const scaleY = screenHeight / imgHeight;
//     const scaledFaceBoxX = Math.ceil(faceBox.x * scaleX);
//     const scaledFaceBoxY = Math.ceil(faceBox.y * scaleY);
//     const scaledFaceBoxW = Math.ceil(faceBox.width * scaleX);
//     const scaledFaceBoxH = Math.ceil(faceBox.height * scaleY);
//     const canvas = document.createElement('canvas');
//     canvas.width = screenWidth;
//     canvas.height = screenHeight;
//     const ctx = canvas.getContext('2d');
//     ctx.strokeStyle = 'red';
//     ctx.lineWidth = 3;
//     ctx.strokeRect(scaledFaceBoxX, scaledFaceBoxY, scaledFaceBoxW, scaledFaceBoxH);
//     detectionDiv.append(canvas);
//   }
  
//   $('body').append(detectionDiv);

// }

async function isFrontalFace(detection) {
  
  if (detection) {
    
    const landmarks = detection.landmarks;

    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    
    if (leftEye && rightEye) {
      
      const eyeDistance = rightEye[0]._x - leftEye[3]._x;
     
      const frontalThreshold = 65;

      if (eyeDistance > frontalThreshold) {
        return true; 
      } else {
        return false;
      }
    }

  }

  return false;
}