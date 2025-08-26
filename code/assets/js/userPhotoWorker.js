let workingDetections = false;
let queueDetections = 0;

let veiculo_id;
let device_id;

let loadedIds = [];
let loadedDetections = [];
// let loadedRecognitions = [];

let toUpdateBackup  = [];
let toRemoveBackup  = [];

let newRecognitionsBackup = [];

self.onmessage = function(event) {

    const action = event.data.action;
    const data = event.data.data ?? false;

    if (data && action === 'getUsers') {

        veiculo_id = data.veiculo_id;
        device_id = data.device_id;
        getUsers();

    }

    if (data && action === 'insertLoadedId') {
        insertLoadedId(data.ds_id);
    }

    if (data && action === 'insertLoadedDetection') {
        insertLoadedDetection(data.detection_id);
    }

    if (data && action === 'removeUserPicture') {
        removeUserPicture(data.user_id);
    }

    if (data && action === 'updateRecogntions') {
        updateRecogntions(data);
    }

    if (action === 'getDetections') {
        getDetections();
    }

    if (data && action === 'doRecognationsWorker') {
        doRecognationsWorker(data);
    }

    if (data && action === 'getRecognitions') {

        veiculo_id = data.veiculo_id;
        device_id = data.device_id;
        getRecognitions();

    }

    if (action === 'setEnd') {
        setEnd();
    }

};

async function getUsers(isQueued = false) {

    if(!isQueued && queueDetections > 0){
        self.postMessage({ action: 'sendToRN', data: { message: `Já tenho ${queueDetections} ${queueDetections > 1 ? 'solicitações' : 'solicitação'} na fila, aguarde...` } });
        queueDetections++;
        return false;
    }

    if(workingDetections){
        self.postMessage({ action: 'sendToRN', data: { message: 'Já estou trabalhando em 1 detecção, aguarde...' } });
        queueDetections++;
        return false;
    }

    workingDetections = true;

    self.postMessage({ action: 'sendToRN', data: { message: 'Certo, vou procurar usuários...', wd: true } });    
    
    const url = '/app/getUsersFace';

    const data = {
        "mob": 1,
        "notIn": loadedIds.join(', ')
    };
  
    const settings = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    };
  
    try {
  
      const resposta = await fetch(url, settings);
      const ret = await resposta.json();
  
      if (ret.status) {
  
        self.postMessage({ action: 'sendToRN', data: { message: 'Usuários carregados com sucesso!' } });

        self.postMessage({ action: 'getLabelFaces', data: { users: ret.users} });

      }else{

        if(ret.getDetections){

            getDetections();

        }else{
            
            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Erro ao carregar os usuários!' } });
            setEnd();

        }
       

      }
  
    } catch(error) {

        self.postMessage({ action: 'sendToRN', data: { message: error } });
        setEnd();
      
    }
    
}

function insertLoadedId(ds_id){

    loadedIds = loadedIds.concat(ds_id);

}


function insertLoadedDetection(detection_id){

    loadedDetections = loadedDetections.concat(detection_id);

}

async function removeUserPicture(user_id){

    self.postMessage({ action: 'sendToRN', data: { message: `Vou tentar remover foto de ${user_id}` } });

    const url = '/app/removeUserPicture';
  
    const data = {
        "mob": 1,
        "user_id": user_id,
    };
    
    const settings = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    };


    try{

        const resposta = await fetch(url, settings);
        const ret = await resposta.json();

        if (ret.message) {
            
            self.postMessage({ action: 'sendToRN', data: { message: ret.message } });

        }else{

            self.postMessage({ action: 'sendToRN', data: { message: `Erro ao remover foto de ${user_id}` } });

        }

    } catch {
  
        self.postMessage({ action: 'sendToRN', data: { message: `Erro ao remover foto de ${user_id}` } });
      
    }

}

async function getDetections(){  

    self.postMessage({ action: 'sendToRN', data: { message: 'Vou procurar detecções...' } });

    const url = '/app/getDetectionsFace';
  
    const data = {
        "mob": 1,
        "notIn": loadedDetections.join(', '),
        "device_id": device_id,
        "veiculo_id": veiculo_id
    };
    
    const settings = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    };

    try{

        const resposta = await fetch(url, settings);
        const ret = await resposta.json();

        if (ret.status) {
            
            self.postMessage({ action: 'sendToRN', data: { message: 'Deteções carregadas com sucesso!' } });
            
            if(ret.detections.length > 0){

                const promise = new Promise((resolve, reject) => {
    
                    const detectionsArray = [];
                
                    ret.detections.forEach(detection => {
                        const base64Image = detection.img;
                        const buffer = Uint8Array.from(atob(base64Image), c => c.charCodeAt(0)).buffer;
                        const blob = new Blob([buffer], { type: 'image/png' });
                        const imgUrl = URL.createObjectURL(blob);
                
                        detectionsArray.push({ id: detection.id , imgUrl: imgUrl, real_time: detection.real_time});
                    });
    
                    resolve(detectionsArray);
    
                });
    
                promise.then(detectionsArray => {
                    self.postMessage({ action: 'doDetections', data: { detections: detectionsArray, screenWidth: ret.screenWidth, screenHeight: ret.screenHeight} });
                });
    
            }else{

                self.postMessage({ action: 'sendToRN', data: { message: 'Nenhuma detecção encontrada!' } });
                getRecognitions();

            }

        }else{

            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Erro ao carregar detecções!' } });
            getRecognitions();

        }

    } catch {

        self.postMessage({ action: 'sendToRN', data: { message: 'Erro ao carregar detecções!' } });
        setEnd();
      
    }

}

async function doRecognationsWorker(data){

    const toUpdate = toUpdateBackup.concat(data.recognizedByUserId, data.notRecognizedOk);
    
    toUpdate.sort((a, b) => a.detectionId - b.detectionId);

    const combinedToRemove = toRemoveBackup.concat(data.removeDuplicatesRec, data.removeDuplicateNRec, data.noTrust);
    combinedToRemove.sort((a, b) => a - b);
    const toRemove = combinedToRemove.join(', ');

    const url = '/app/doRecognationsFace';

    const dataSend = {
        "mob": 1,
        "toUpdate": toUpdate,
        "toRemove": toRemove,
        "device_id": device_id,
        "veiculo_id": veiculo_id
    };

    const settings = {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(dataSend)
    };

    try {

        const resposta = await fetch(url, settings);
        const ret = await resposta.json();

        if (ret.status) {

            toUpdateBackup = ret.doBackup['toUpdate'] ?? [];
            toUpdateBackup = ret.doBackup['toRemove'] ? combinedToRemove : [];

            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Reconhecimentos criados com sucesso!' } });

            getRecognitions();

        }else{

            toUpdateBackup = toUpdate;
            toRemoveBackup = combinedToRemove;
            
            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Erro ao criar reconhecimentos.' } });

        }
        
    } catch {

        toUpdateBackup = toUpdate;
        toRemoveBackup = combinedToRemove;

        self.postMessage({ action: 'sendToRN', data: { message: 'Erro ao criar reconhecimentos.' } });
        
    }

}

async function getRecognitions(){  

    self.postMessage({ action: 'sendToRN', data: { message: 'Vou procurar reconhecimentos...' } });

    const url = '/app/getRecognitionsFace';
  
    const data = {
        "mob": 1,
        "device_id": device_id,
        "veiculo_id": veiculo_id
    };
    
    const settings = {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    };

    try{

        const resposta = await fetch(url, settings);
        const ret = await resposta.json();

        if (ret.status) {
            
            self.postMessage({ action: 'sendToRN', data: { message: 'Reconhecimentos carregados com sucesso!' } });
            
            if(ret.recognitions.length > 0){

                const promise = new Promise((resolve, reject) => {
    
                    const recognitionsArray = [];
                
                    ret.recognitions.forEach(recognition => {
                        const base64Image = recognition.img;
                        const buffer = Uint8Array.from(atob(base64Image), c => c.charCodeAt(0)).buffer;
                        const blob = new Blob([buffer], { type: 'image/png' });
                        const imgUrl = URL.createObjectURL(blob);
                
                        recognitionsArray.push({ id: recognition.id , imgUrl: imgUrl, real_time: recognition.real_time, controle_acesso_id: recognition.controle_acesso_id, formated_time: recognition.formated_time, facedetection_id: recognition.facedetection_id});
                        // loadedRecognitions = loadedRecognitions.concat(recognition.id);
                    });
    
                    resolve(recognitionsArray);
    
                });
    
                promise.then(recognitionsArray => {
                    self.postMessage({ action: 'treatRecognitions', data: { recognitions: recognitionsArray } });
                });
    
            }else{

                self.postMessage({ action: 'sendToRN', data: { message: 'Nenhum reconhecimento encontrado!' } });
                setEnd();

            }

        }else{

            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Erro ao carregar reconhecimentos!' } });
            setEnd();

        }

    } catch {

        self.postMessage({ action: 'sendToRN', data: { message: 'Erro ao carregar reconhecimentos!' } });
        setEnd();
      
    }

}

async function updateRecogntions(newRecognitions){
    
    const toUpdate = newRecognitionsBackup.concat(newRecognitions);
    
    const url = '/app/updateRecogntionsFace';

    const data = {
        "mob": 1,
        "toUpdate": toUpdate,
        "device_id": device_id,
        "veiculo_id": veiculo_id
    };

    const settings = {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    };

    try {

        const resposta = await fetch(url, settings);
        const ret = await resposta.json();

        if (ret.status) {

            newRecognitionsBackup = [];
            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Reconhecimentos atualizados com sucesso!' } });

        }else{

            newRecognitionsBackup = toUpdate;
            self.postMessage({ action: 'sendToRN', data: { message: ret.message ?? 'Erro ao atualizar reconhecimentos.' } });

        }
        
    } catch {

        newRecognitionsBackup = toUpdate;
        self.postMessage({ action: 'sendToRN', data: { message: 'Erro ao atualizar reconhecimentos.' } });
        
    }finally{

        setEnd();

    }

}

function setEnd(){

    workingDetections = false;

    self.postMessage({ action: 'sendToRN', data: { message: 'FIM', wd: false } }); 
    
}

setInterval(() => {

    self.postMessage({ action: 'sendToRN', data: { message: 'Verificando se tem detections na fila...' } });
    
    if (queueDetections > 0 && !workingDetections) {
        queueDetections--;
        getUsers(true);
    }
  
    if(queueDetections > 0){
        self.postMessage({ action: 'sendToRN', data: { message: `Tenho ${queueDetections} ${queueDetections > 1 ? 'detecções' : 'detecção'} na fila` } });
    }else{
        self.postMessage({ action: 'sendToRN', data: { message: 'Não tem detections na fila' } });
    }

}, 180000);// a cada 3 minutos tentar checar a fila