//TIRAR FOTO
const video = document.getElementById('video');
var MediaStream;
var detect = null;
var iniCount = null;
var faceArea = 350;
var isUploading = false;

Promise.all([
  faceapi.nets.ssdMobilenetv1.loadFromUri("/assets/js/models"),
  faceapi.nets.tinyFaceDetector.loadFromUri("/assets/js/models"),
  faceapi.nets.faceLandmark68Net.loadFromUri("/assets/js/models"),
  faceapi.nets.faceExpressionNet.loadFromUri("/assets/js/models"),
  faceapi.nets.faceRecognitionNet.loadFromUri("/assets/js/models")
]).then(() => {
  checkHasDs();
});

$('.userPhoto:not(.disabled)').on('click', function(){
  $('.userPhotoAct').addClass('show');
  $('body').append('<div class="userPhotoBackDrop"></div>');
});

$('#cancelPicture').on('click', function(){
  if(isUploading){return;}
  $('.userPhotoAct').removeClass('show');
  $('.userPhotoBackDrop').remove();
});

$('#openCamera').on('click', function(){
  openCamera();
});

$('#cancelTakePicture').on('click', function(){
  closeCamera();
  finalCountDown();
});

$('#redoPicture').on('click', function(){
  redoPicture();
});

$('#confirmUserPic').on('click', function(){
  confirmUserPic();
});

async function openCamera() {

  if(isUploading){return;}

  $('.userPhotoAct').removeClass('show');

  if ('mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices) {
      
      await navigator.mediaDevices.
          getUserMedia({ 

              video: true,
              audio: false, 

          }).then((stream) => {

              $('.camera').addClass('show');
              video.srcObject = stream;
              MediaStream = stream.getTracks()[0];

        })
        .catch((err) => {
          swal({
              title: "ATENÇÃO",
              text: "Para tirar a foto permita que o CGF tenha acesso a sua câmera.",
              icon: "warning",
              button: "Fechar",
          }).then(() => {
            closeCamera();
          });
        });

  }else{
    swal({
      title: "ATENÇÃO",
      text: "Para tirar a foto é necessário uma câmera instalada.",
      icon: "warning",
      button: "Fechar",
    }).then(() => {
      closeCamera();
    });
  }

}

function closeCamera(){

    $('.camera').removeClass('show');
    $('.userPhotoAct').addClass('show');
    try{MediaStream.stop();}catch{}
    video.srcObject = null;
    clearInterval(detect);
    $('canvas').remove();
    $('.tokedPhotoContainer').removeClass('show');
    $('#userPhtoOk').attr('src', '');
    $('#confirmUserPic').removeClass('show');
    setTimeout(() => {
      $('#video').removeClass('Pause');
      $('.scoreUserPhoto').css({
        width: '0%',
        background: 'red'
      });
    }, 350);
    
}

function redoPicture(){

  $('#video').removeClass('Pause');
  $('.tokedPhotoContainer').removeClass('show'); 
  $('#confirmUserPic').removeClass('show');

  $('.scoreUserPhoto').css({
    width: '0%',
    background: 'red'
  });

  iniDetection();

}

function confirmUserPic(){

    const imgSrc = $('#userPhtoOk').attr('src');
    $('.userPhoto span').html('Trocar Foto');
    $('.userPhoto').css('background-image', `url(${imgSrc})`);
    $('#userPic').val(imgSrc);

    closeCamera();
    finalCountDown();

    $('.userPhotoAct').removeClass('show');
    $('.userPhotoBackDrop').remove();

    const hasWarning = $('.warnPicQuality').length;
    if(hasWarning){
      $('.warnPicQuality').remove();
    }
}

function startDetection(){

    const canvas = faceapi.createCanvasFromMedia(video);
    canvas.id = "detection";

    $('.camera').append(canvas);

    faceapi.matchDimensions(canvas, { height: video.height, width: video.width });
  
    detect = setInterval(async () => {

      const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks();  

      let score = 0;
      let box = null;

      try{

        const resizedDetections = faceapi.resizeResults(detection, {
          height: video.height,
          width: video.width,
        });
  
        canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
        faceapi.draw.drawDetections(canvas, resizedDetections);
        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

        score = resizedDetections.alignedRect._score;
        box = resizedDetections.detection.box;
        
                
      }catch{

        score = 0;
        box = null;

      }

      let scorePercent = Math.round((score/1) * 100);

      if(scorePercent < 45){
        $('.scoreUserPhoto').css('background', 'red');
      }

      if(scorePercent > 45){
        $('.scoreUserPhoto').css('background', 'yellow');
      }

      if(scorePercent > 80){
        $('.scoreUserPhoto').css('background', 'orange');
      }

      $('.scoreUserPhoto').css('width', `${scorePercent}%`);

      if(scorePercent >= 90 && box !== null){

        extractFaceFromBox(video, box);
        clearInterval(detect);
        $('.scoreUserPhoto').css({
            width: '100%',
            background: 'green'
        });
        
      }

    }, 200);
}

function drawRectangle(){

  const canvas = faceapi.createCanvasFromMedia(video);

  canvas.id = "rect";

  $('.camera').append(canvas);

  let ctx = canvas.getContext('2d');

  canvas.width = video.width;
  canvas.height = video.height;

  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const pX = canvas.width/2 - faceArea/2;
  const pY = canvas.height/2 - faceArea/2;

  ctx.rect(pX,pY,faceArea,faceArea);
  ctx.lineWidth = "4";
  ctx.strokeStyle = "green";    
  ctx.stroke();

}

async function extractFaceFromBox(midia, box){ 
  
  $('#video').addClass('Pause');
  $('canvas').remove();

  setTimeout(async () => {

    $('.scoreUserPhoto').css({
        width: '100%',
        background: 'green'
    });

    const regionsToExtract = [
        new faceapi.Rect(box._x-40, box._y-70, box._width+130, box._width+130)
    ];
                        
    let faceImages = await faceapi.extractFaces(midia, regionsToExtract);
    let image = faceImages[0].toDataURL();
    
    $('#userPhtoOk').attr('src', image);
    $('.tokedPhotoContainer').addClass('show'); 
    $('#confirmUserPic').addClass('show');

  }, 300);
}

function iniDetection(){

  drawRectangle();
  
  $('.cameraCountDown .5').removeClass('ativo');
  iniCount = setTimeout(() => {
    $('.cameraCountDown .5').addClass('hide');
    $('.cameraCountDown .4').removeClass('ativo');

    iniCount = setTimeout(() => {
      $('.cameraCountDown .4').addClass('hide');
      $('.cameraCountDown .3').removeClass('ativo');

      iniCount = setTimeout(() => {
        $('.cameraCountDown .3').addClass('hide');
        $('.cameraCountDown .2').removeClass('ativo');

        iniCount = setTimeout(() => {
          $('.cameraCountDown .2').addClass('hide');
          $('.cameraCountDown .1').removeClass('ativo');

          iniCount = setTimeout(() => {
            $('.cameraCountDown .1').addClass('hide');
            finalCountDown();
            startDetection();
          }, 1000);

        }, 1000);

      }, 1000);

    }, 1000);

  }, 1000);

}

function finalCountDown(){

  clearInterval(iniCount);
  iniCount = null;

  $('.cameraCountDown .1').addClass('ativo');
  $('.cameraCountDown .2').addClass('ativo');
  $('.cameraCountDown .3').addClass('ativo');
  $('.cameraCountDown .4').addClass('ativo');
  $('.cameraCountDown .5').addClass('ativo');

  setTimeout(() => {
    $('.cameraCountDown .1').removeClass('hide');
    $('.cameraCountDown .2').removeClass('hide');
    $('.cameraCountDown .3').removeClass('hide');
    $('.cameraCountDown .4').removeClass('hide');
    $('.cameraCountDown .5').removeClass('hide');
  }, 500);

}

// video.addEventListener("play", () => {
//   iniDetection();
// });


//UPLOAD userPhotoUpload
$('#cancelUploadUserPhoto').on('click', function(){
  endUserPhotoUpload(false, true);
});


$(document).on('click', '.photoPreview:not(.bad)', function(){
    const imgSrc = $(this).attr('src');
    $('.userPhoto span').html('Trocar');
    $('.userPhoto').css('background-image', `url(${imgSrc})`);
    $('#pic_front_smiling').val(imgSrc);
    endUserPhotoUpload(false, true);
    $('#cancelPicture').trigger('click');

    const hasWarning = $('.warnPicQuality').length;
    if(hasWarning){
      $('.warnPicQuality').remove();
    }
});

function dataURLtoBlob(dataurl) {
  var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
      bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
  while(n--){
      u8arr[n] = bstr.charCodeAt(n);
  }
  return new Blob([u8arr], {type:mime});
}

function getOriginalTxt(type, complete = false){
  
  let originalTxt;

  switch (type) {
    case 'pic_front_smiling':
    case 'pic_front_smiling_eg':
      originalTxt = 'Frontal Sorrindo';
      break;
    case 'pic_front_serious':
    case 'pic_front_serious_eg':
      originalTxt = 'Frontal Sério';
      break;
    case 'pic_right_perfil':
    case 'pic_right_perfil_eg':
      originalTxt = 'Perfil Direito';
      break;
    case 'pic_left_perfil':
    case 'pic_left_perfil_eg':
      originalTxt = 'Perfil Esquerdo';
      break;
    default:
      originalTxt = 'Tipo não reconhecido';
      break;
  }

  if (complete && type.includes('_eg')) {
    originalTxt += ' (Com Óculos)';
  }

  return originalTxt;

}

function iniUpload(type){

  isUploading = true;

  $('.removeUserPhoto, #cancelPicture').addClass('disabled');

  $('.allPics input[type=file]').prop('disabled', true);
  $(`label[for="${type}_upload"]`).attr('title', 'Carregando...');
  $('.allPics label').addClass('disabled');

  $(`#${type}_upload_preview`).append('<div class="userPhotoLoader"></div>');
  $(`#${type}_upload_preview .pic_description`).append('<i class="percentUserPhoto ml-2 mb-0">0%</i>');

}

function endUserPhotoUpload(showMsg = false, type, clear = false){

  isUploading = false;
  
  $(`#${type}_upload_preview .userPhotoLoader`).remove();

  $('.allPics input[type=file]').prop('disabled', false);
  $('#cancelPicture').removeClass('disabled');
  $(`label[for="${type}_upload"]`).attr('title', 'Upload');
  $('.allPics label').removeClass('disabled');

  const originalTxt = getOriginalTxt(type);

  $(`#${type}_upload_preview .pic_description`).html(originalTxt);

  if(clear || showMsg){

    if(clear){

      $(`#${type}`).val(0);
      $(`#${type}_ds`).val(0);
      $(`#${type}_upload`).val('');

      $(`#${type}_upload_preview .removeUserPhoto`).addClass('disabled');

      $(`#${type}_upload_preview`).css('background-image', `url(/assets/images/${type}.png)`);

      if(type === 'pic_front_smiling'){
        $('.userPhoto').css('background-image', `url(/assets/images/${type}.png)`);
      }

    }
    
    if(showMsg){
      swal({
        title: "ATENÇÃO",
        text: showMsg,
        icon: "warning",
        button: "Fechar",
      }); 
    }
     
  }

  checkCanDeletePic();

}

function checkCanDeletePic(){

  let emptyCount = 0;
  const eyeglasses = $("#eyeglasses").val();
  const inputsCount = eyeglasses == 1 ? 8 : 4;

  $('.picToSave').each(function() {

    const typeId = $(this).attr('id');

    if(eyeglasses == 0 && typeId.indexOf('_eg') !== -1){
      return true;
    }

    if ($(this).val() == 0) {

      emptyCount++;
      
    } else {

      $(`#${typeId}_upload_preview .removeUserPhoto`).removeClass('disabled');
      
    }
    
  });

  $('.userPhoto span').html(emptyCount == inputsCount ? 'Adicionar Fotos' : 'Trocar Fotos');

}

let waitApp = null;
let gettingApp = false;
let request_id = null;

async function uploadUserPhotoFromApp(position, id){

  $('#cancelPicture').css('display','none');
  $('.iframeQrUser').remove();
  $('.userPhotoAct').addClass('appPhoto');
  $('.paxQr').append(`<iframe class="iframeQrUser" src="paxqrcode?idpax=${id}&position=${position}"></iframe>`);
  $('.paxQr').addClass('open');
  waitApp = setInterval(async () => {
    if(gettingApp){
      console.log('esperar');
      return false;
    }
    gettingApp = true;
    $.ajax({
      url: "/cadastroPax/appTakePicture",
      method: 'post',
      data: {controle_acesso_id: id, position: position},
      dataType: 'json',
      success:function(ret){

        if(ret.status){
          
          $('.iframeQrUser').remove();

          if(ret.retorno == 0 && !request_id){
            request_id = ret.request_id;
            $('#deviceAppId').text(`Aguardando Aparelho: ${ret.device_id}`);
          }

          if(ret.retorno == 3 || ret.retorno == 1){
            
            if (waitApp !== null) {
              clearInterval(waitApp);
              waitApp = null;
            }
            if(ret.retorno == 3){
              request_id = null;
              $('.cancelTakeAppPic').trigger('click');
              swal({
                title: "CANCELADO",
                text: `Cancelado pelo aparelho ${ret.device_id}`,
                icon: "warning",
                button: "OK",
              });  
            }
            if(ret.retorno == 1 && ret.picture){
              
              $(`#${position}_upload_preview`).css('background-image', `url(${ret.picture})`);

              if(position === 'pic_front_smiling'){
                $('.userPhoto').css('background-image', `url(${ret.picture})`);
              }

              $(`#${position}`).val(1);

              $(`.removeUserPhoto[typer=${position}]`).removeClass('disabled');
              $(`#btn_up_${position}`).attr('title','Trocar Foto');

              swal({
                title: "SUCESSO",
                text: `Imagem alterada com sucesso!`,
                icon: "success",
                button: "OK",
              });

              setTimeout(() => {
                $('.cancelTakeAppPic').trigger('click');
              }, 1000);
            }
          }
          
        }

        gettingApp = false;

      }
    });
  }, 2000);
}

$('.cancelTakeAppPic').on('click', function(){
  const iniMsgPic = $('#iniMsgPic').val();
  $('#deviceAppId').text(iniMsgPic);
  $('#cancelPicture').css('display','flex');
  $('.paxQr').removeClass('open');
  $('.iframeQrUser').remove();
  $('.userPhotoAct').removeClass('appPhoto');
  if (waitApp !== null) {
    clearInterval(waitApp);
    waitApp = null;
  }
  if(request_id !== null){
    $.ajax({
      url: "/cadastroPax/removeAppTakePicture",
      method: 'post',
      data: {request_id: request_id},
      dataType: 'json',
      success:function(){

        request_id = null;

      }
    });
  }
});

function uploadUserPhoto(type){

  
  let fileTypes = ['jpg', 'jpeg', 'png'];

  const input = document.getElementById(`${type}_upload`);

  if (input.files && input.files[0]) {

    const extension = input.files[0].name.split('.').pop().toLowerCase();
    const isValidExt = fileTypes.indexOf(extension) > -1;

    if(!isValidExt){
      swal({
        title: "ATENÇÃO",
        text: `Arquivo Inválido! Os formatos aceitos são: ${fileTypes.join(', ').replace(/,(?!.*,)/gmi, ' e')}.`,
        icon: "warning",
        button: "Fechar",
      });  
      return false;
    }

    iniUpload(type);
    
    var reader = new FileReader();

    reader.onerror = function() {
      endUserPhotoUpload("Houve um erro ao carregar a imagem, tente novamente.", type);
      return;
    }

    reader.onabort = function() {
      endUserPhotoUpload("Houve um erro ao carregar a imagem, tente novamente.", type);
      return;
    }

    reader.onprogress = function(pe) {

        if(pe.lengthComputable) {
          
          const percent = parseInt(pe.loaded*100/pe.total);
          $(`#${type}_upload_preview .userPhotoLoader`).css('width', `${percent}%`);
            
          $(`#${type}_upload_preview .pic_description .percentUserPhoto`).html(`${percent}%`);

          if(percent < 45){
            $(`#${type}_upload_preview .userPhotoLoader`).css('background', 'red');
          }
      
          if(percent > 45){
            $(`#${type}_upload_preview .userPhotoLoader`).css('background', 'yellow');
          }
      
          if(percent > 80){
            $(`#${type}_upload_preview .userPhotoLoader`).css('background', 'orange');
          }

          if(percent == 100){
            $(`#${type}_upload_preview .userPhotoLoader`).css('background', 'green');
          }
        }
    }

    reader.onload = function (e) {
      // console.log(e.target.result);

      setTimeout(async () => {

        $(`#${type}_upload_preview .pic_description`).html('Analisando imagem...');

        const img = await faceapi.bufferToImage(dataURLtoBlob(e.target.result));

        const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceExpressions().withFaceDescriptor();
        
        if(detection){

          const descriptor = detection.descriptor;

          //quando tiver que ser frontal
          if(type === 'pic_front_smiling' || type === 'pic_front_serious' || type === 'pic_front_smiling_eg' || type === 'pic_front_serious_eg'){
 
            const checkFrontalFace = await isFrontalFace(detection);
            
            if(!checkFrontalFace){
              endUserPhotoUpload("A imagem não contém um rosto em pose frontal.", type);
              return;
            }
            
          }

          //quando tiver que ser de perfil:
          // if(type === 'pic_right_perfil' || type === 'pic_left_perfil' || type === 'pic_right_perfil_eg' || type === 'pic_left_perfil_eg'){
 
          //   const checkFrontalFace = await isFrontalFace(detectionWithFaceLandmarks);
            
          //   if(checkFrontalFace){
          //     endUserPhotoUpload("A imagem não contém um rosto de perfil.", type);
          //     return;
          //   }
            
          // }

          const fe = detection.expressions;

          //quando tem que ser sorrindo
          if((type === 'pic_front_smiling' || type === 'pic_front_smiling_eg') && fe.happy < 0.5){
            endUserPhotoUpload("A imagem não contém um rosto sorrindo, tente outra imagem.", type);
            return;
          }

          //quando tem que ser sério
          if ((type === 'pic_front_serious' || type === 'pic_front_serious_eg') && fe.happy >= 0.5) {
            endUserPhotoUpload("A imagem não contém um rosto sério, tente outra imagem.", type);
            return;
          }

          const fd = detection.detection;
          console.log(fd);
          const minSize = 750;

          const extraSpace = Math.max((Math.abs(fd.box._width - fd.box._height) / 2), (minSize - Math.max(fd.box._width, fd.box._height)) / 2);

          const roiX = Math.max(fd.box._x - extraSpace, 0);
          const roiY = Math.max(fd.box._y - extraSpace, 0);
          const maxSize = Math.max(fd.box._width, fd.box._height) + 2 * extraSpace;

          const expandedRect = new faceapi.Rect(roiX, roiY, Math.max(maxSize, minSize), Math.max(maxSize, minSize));

          const regionsToExtract = [expandedRect];

          let faceImages = await faceapi.extractFaces(img, regionsToExtract);

          let image = faceImages[0].toDataURL();

          $(`#${type}_upload_preview`).css('background-image', `url(${image})`);

          if(type === 'pic_front_smiling'){
            $('.userPhoto').css('background-image', `url(${image})`);
          }

          $(`#${type}`).val(image);
          $(`#${type}_ds`).val(descriptor);
          
          endUserPhotoUpload(false, type);


        }else{

          endUserPhotoUpload("Não foi possível achar um rosto ou um rosto com a qualidade necessária na imagem, tente com outra imagem.", type);
          return;
          
        }

      }, 100);

      
    }

    reader.readAsDataURL(input.files[0]);
  }
}

async function isFrontalFace(detection) {
  

  if (detection) {
    const landmarks = detection.landmarks;

    
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    
    if (leftEye && rightEye) {
      
      const eyeDistance = rightEye[0]._x - leftEye[3]._x;

     
      const frontalThreshold = 50;

      
      if (eyeDistance > frontalThreshold) {
        return true; 
      } else {
        return false;
      }
    }
  }

  return false;
}

// $(document).on('click', '.removeUserPhoto:not(.disabled)', function(){

//   const type = $(this).attr('typeR');
  
//   const originalTxt = getOriginalTxt(type, true);

//   swal({
//     title: `Remover ${originalTxt}?`,
//     text: "Tem certeza que deseja remover essa foto?",
//     icon: 'warning',
//     dangerMode: true,
//     buttons: {
//       cancel: "Cancelar",
//       confirm: "Confirmar"
//     },
//   }).then((result) => {

//     if (result) {
//       endUserPhotoUpload(false, type, true);
//     }

//   });

// });


$(document).on('click', '.removeUserPhoto:not(.disabled)', function(){

  const type = $(this).attr('typeR');
  const controle_acesso_id = $(this).attr('controle_acesso_id');
  
  const originalTxt = getOriginalTxt(type, true);

  const btn = $(this);

  swal({
    title: `Remover ${originalTxt}?`,
    text: "Tem certeza que deseja remover essa foto?",
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Confirmar"
    },
  }).then((result) => {

    if (result) {

      $("#carregando").addClass('show');

      $.ajax({
        url: "/cadastroPax/removeUserPhoto",
        method: 'post',
        data: {controle_acesso_id: controle_acesso_id, position: type},
        dataType: 'json',
        success:function(ret){
  
          if(ret.status){
            
            btn.addClass('disabled');
            $(`#${type}`).val(0);
            $(`#btn_up_${type}`).attr('title','Tirar Foto');
            
            $(`#${type}_upload_preview`).css('background-image', `url(/assets/images/${type}.png)`);

            if(type === 'pic_front_smiling'){
              $('.userPhoto').css('background-image', `url(/assets/images/${type}.png)`);
            }
            
          }
  
          $("#carregando").removeClass('show');
        }
      });
    }

  });

});

//para mudar se o passageiro usa óculos
function setEyeglasses(checked){
  $("#eyeglasses").val(checked ? 1 : 0);
  if(checked == 1){
    $(".egPic").removeClass('egPicHide').addClass('egPicShow');
  }else{
    $(".egPic").addClass('egPicHide').removeClass('egPicShow');
  }

  checkCanDeletePic();
  
}

function checkHasDs(){

  // const pic_front_smiling_hasDs = $('#pic_front_smiling_hasDs').val() == 0;
  // const pic_front_serious_hasDs = $('#pic_front_serious_hasDs').val() == 0;
  // const pic_right_perfil_hasDs = $('#pic_right_perfil_hasDs').val() == 0;
  // const pic_left_perfil_hasDs = $('#pic_left_perfil_hasDs').val() == 0;

  // const pic_front_smiling_eg_hasDs = $('#pic_front_smiling_eg_hasDs').val() == 0;
  // const pic_front_serious_eg_hasDs = $('#pic_front_serious_eg_hasDs').val() == 0;
  // const pic_right_perfil_eg_hasDs = $('#pic_right_perfil_eg_hasDs').val() == 0;
  // const pic_left_perfil_eg_hasDs = $('#pic_left_perfil_eg_hasDs').val() == 0;


  // if(pic_front_smiling_hasDs){
  //   createDs('pic_front_smiling');
  // }

  // if(pic_front_serious_hasDs){
  //   createDs('pic_front_serious');
  // }

  // if(pic_right_perfil_hasDs){
  //   createDs('pic_right_perfil');
  // }

  // if(pic_left_perfil_hasDs){
  //   createDs('pic_left_perfil');
  // }

  // if(pic_front_smiling_eg_hasDs){
  //   createDs('pic_front_smiling_eg');
  // }

  // if(pic_front_serious_eg_hasDs){
  //   createDs('pic_front_serious_eg');
  // }

  // if(pic_right_perfil_eg_hasDs){
  //   createDs('pic_right_perfil_eg');
  // }

  // if(pic_left_perfil_eg_hasDs){
  //   createDs('pic_left_perfil_eg');
  // }
  
}

async function createDs(position){
  const getImg = $(`#${position}`).val();
  const img = await faceapi.bufferToImage(dataURLtoBlob(getImg));
  const detection = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor();

  if(detection){

    const descriptor = detection.descriptor;

    $(`#${position}_ds`).val(descriptor);

  }
}