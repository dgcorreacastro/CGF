$(document).ready(function(){ 
    //pre load imagem do mapa
    //será importante para conexões mais lentas
    const mapaImg = new XMLHttpRequest();
    const mapaBackground = $('#mapaBackground').val();
    mapaImg.open("GET", mapaBackground);
    

    mapaImg.onerror = function() {
      errorMapaImg();
      return;
    }

    mapaImg.onabort = function() {
      errorMapaImg();
      return;
    }

    mapaImg.onprogress = function(pe) {
        if(pe.lengthComputable) {
            $('.loadingMapa .progressBar ').css('width', `${parseInt(pe.loaded*100/pe.total)}%`);
            $('#progressNumber').html(`${parseInt(pe.loaded*100/pe.total)}%`);
        }
    }

    mapaImg.onloadend = function(pe) {
        if(pe.total == 0){
          errorMapaImg();
          return;
        }
        
        $('#mapaEuroNew').css('background-image' , `url('${pe.srcElement.responseURL}')`);
        $('#appMapaEuro').removeClass('loading');
        $('.loadingMapa').fadeOut(function(){
            $(this).remove();
        });

        //enquadrar o mapa na tela
        fitToParent(document.getElementById('mapaEuroNew'), 1692, 948);

        //ajustar os nomes dos pontos dentro dos pontos
        $('.pontoMapaEuroNew').each(function(){
          resizeFont($(this));
        });
    }    

    mapaImg.send();
});

function errorMapaImg(){
  $('.loadingMapa h2').html('Houve um erro...<br>Tentando novamente em:');
  $('#progressNumber').html('<b id="timeTryAgain">20</b>');
  $('.progressBar').css('width','5%');
  let width = 5;
  let time = Number($('#timeTryAgain').text());
  const tryAgain = setInterval(() => {
    if(time != 0){
      time--;
      $('#timeTryAgain').text(time);
      $('.progressBar').css('width', `${width}%`);
      width+=5;
    }else{
      clearInterval(tryAgain);
      location.reload();
    }
    
  }, 1000);
}

//função que adiciona horários na tela
function addHoraUL(ponto, tipo, id, title, classe, hora, novo){
  
  //limpa a ul caso seja o primeiro loop
  if(!$(ponto).find(`.${tipo}`).parent().hasClass('show')){
    $(ponto).find(`.${tipo}`).html('');
  }

  //adiciona as li de horário
  $(ponto).find(`.${tipo}`).append(`<li id="${id}" novo="${novo}" tipo=${tipo} title="${title}" class="${classe}">${hora}</li>`);
  
  //mostra a ul caso não esteja visível
  if(!$(ponto).find(`.${tipo}`).parent().hasClass('show')){
    $(ponto).find(`.${tipo}`).parent().addClass('show');
  }

  //abre o ponto se não estiver aberto
  if(!$(ponto).hasClass('open')){
    $(ponto).addClass('open');
    $(ponto).find('.loaderMapaEuroNew').css('display', 'none');
    $(ponto).find('.dadosPonto').removeAttr('style');
  }

  //mostra legendas se estiver na page view e ainda não estiver sendo mostrada
  if(page == 'view' && !$('.legendasMapaNew').hasClass('show')){
    $('.legendasMapaNew').addClass('show');
  }
}

//funcao para pegar os dados do ponto
function pegarDadosPonto(ponto){

  //loader no ponto
  $(ponto).find('.loaderMapaEuroNew').css('display', 'block');

  //backdrop
  $('#mapaEuroNew').append('<div class="backPontoOpen"></div>');

  //pega o id do ponto no atributo id
  const id = $(ponto).attr('id');
  const idCount = $(ponto).attr('idCount');

  //coloca o link para adicionar horarios caso seja page edit e ainda não exista
  if(page == 'edit' && $(ponto).find('.addHorario').length == 0){
    $(ponto).find('.tituloHorarios.add').each(function(){
      const titulo = $(this).text();
      $(this).append(`<i title="Adicionar Horário - ${titulo}" class="fas fa-plus-square addHorario"></i>`);
    });
  }

  //checa se é um ponto que já estará no banco
  if(id != idCount){
    //faz a busca dos dados no banco quando é page view ou page edit sem dados no localstorage
    if(page == 'view' || page == 'edit' && checaHorasSalvas(id, ponto)){
      //criar um abortController para mostrar erro caso
      //demore mais que 5 segundos pra carregar os dados
      const getDotsController = new AbortController();
      const { signal } = getDotsController;

      const url = `/configuracoes/getDots?id=${id}`;
      
      //setar um timeout de 10 segundos para chamar a abortController
      //mudar o tempo caso seja necessário 
      const espera = setTimeout(() => {
        getDotsController.abort(); 
      }, 10000);

      fetch(url, { signal })
      .then( resposta => {
        return resposta.json();
      })
      .then ( ret => {
        clearTimeout(espera);
          //chama função para criar as ul com os dados do banco
          ret.horarios.manha.map(function(horario) {
            addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
          });

          ret.horarios.tarde.map(function(horario) {
            addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
          });

          ret.horarios.picoAlmoco.map(function(horario) {
            addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
          });

          ret.horarios.noite.map(function(horario) {
            addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
          });

          ret.horarios.restaurante.map(function(horario) {
            addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
          });
          
          //atualiza os dados do localstorage caso seja page edit
          if(page == 'edit'){
            pontosSalvos.find(x => x.id === id).manha = ret.horarios.manha;
            pontosSalvos.find(x => x.id === id).tarde = ret.horarios.tarde;
            pontosSalvos.find(x => x.id === id).picoAlmoco = ret.horarios.picoAlmoco;
            pontosSalvos.find(x => x.id === id).noite = ret.horarios.noite;
            pontosSalvos.find(x => x.id === id).restaurante = ret.horarios.restaurante;
            localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
          }

          

          //checa se não tem horários para mostrar e trata de acordo com o page view ou edit
          if(ret.horarios.manha.length == 0 &&
            ret.horarios.tarde.length == 0 &&
            ret.horarios.picoAlmoco.length == 0 &&
            ret.horarios.noite.length == 0 &&
            ret.horarios.restaurante.length == 0){
              $(ponto).find('.loaderMapaEuroNew, .dadosPonto').css('display', 'none');
              $(ponto).addClass('open');
              if(page == 'view'){
                $(ponto).find('.erroPonto errorMsg').html('Não foram encontrados horários para esse ponto.');
                setTimeout(() => {
                  $(ponto).find('.erroPonto').addClass('show');
                }, 300);
              }
            }

            if(page == 'edit'){
              $(ponto).find('.dadosPonto').removeAttr('style');
              $(ponto).find('.horariosMapaNew').addClass('show');
            }
          
          //volta as uls de horários sempre para o início quando é page view
          if(page == 'view'){
            setTimeout(() => {
              $('ul').scrollLeft(0);
            }, 300);
          }
      })
      .catch(() => {
        //mostra mensagem de erro quando não consegue carregar os dados do ponto
        clearTimeout(espera);
        $(ponto).find('.loaderMapaEuroNew, .dadosPonto').css('display', 'none');
        $(ponto).addClass('open');
        $(ponto).find('.erroPonto errorMsg').html('Erro ao carregar, por favor tente novamente.');
        setTimeout(() => {
          $(ponto).find('.erroPonto').addClass('show');
        }, 300);

      }); 
    }
    
  }
  else{
    //caso não esteja no banco e seja page edit salva o ponto no banco
    if(page == 'edit'){
      savePonto(id, false);
    }
  }  
}

function resizeFont(ponto) {
  const span = $(ponto).find(".nomePonto");
  let fontSize = (span.css('font-size').match(/\d+/)[0]);

  while (span.width() < $(ponto).width()) {
    span.css("font-size", fontSize++);
  }

  while (span.width() > $(ponto).width()) {
    span.css("font-size", fontSize--);
  }
}
  
function fitToParent(element, width, height, margin) {
  let elementStyle = window.getComputedStyle(element);
  elementStyle = {
    width: width ? width : parseInt(elementStyle.getPropertyValue('width')),
    height: height ? height: parseInt(elementStyle.getPropertyValue('height')),
    marginTop: margin ? margin : parseInt(elementStyle.getPropertyValue('margin-top')),
    marginLeft: margin ? margin : parseInt(elementStyle.getPropertyValue('margin-left'))
  };
  
  const parentSize = {
    width: element.parentElement.clientWidth - elementStyle.marginLeft * 2,
    height: element.parentElement.clientHeight - elementStyle.marginTop * 2
  };

  const scale = Math.min(parentSize.width / elementStyle.width,
  parentSize.height / elementStyle.height);
  element.style.width = `${elementStyle.width}px`;
  element.style.height = `${elementStyle.height}px`;
  element.style.transform = `scale(${scale})`;
  
  if($('.mapaEuroNewTitle').length){
    setTimeout( ()=>{
      const titleHeight = parseInt($('.mapaEuroNewTitle')[0].getBoundingClientRect().height);
      const espacoAntes = parseInt((parentSize.height - element.getBoundingClientRect().height)/2);
      $('.mapaEuroNewTitle').css('margin-top', espacoAntes > titleHeight ? `calc(-${titleHeight}px)` : `calc(-${espacoAntes > 20 ? espacoAntes : 68/2}px)`);
    }, 500);
  }
  
}

window.onresize = function() {
  fitToParent(document.getElementById('mapaEuroNew'), 1692, 948);
}

//fechar o ponto
$(document).on('click', '.closePonto, .erroPonto', function(e){
  e.preventDefault();
  e.stopPropagation();
  if($(this).parent().hasClass('editandoPonto')){return;}
  $(this).parent().removeClass('open');
  $('.erroPonto, .horariosMapaNew, .legendasMapaNew').removeClass('show');
  $('.backPontoOpen').remove();
  if($(this).hasClass('closePonto') && page == 'edit'){
    setTimeout( ()=>{
      resizeFont($(this).parent());
    }, 500);
  }
});


$(document).on('click', '.backPontoOpen, .logosPontos, .legendasMapaNew', function(e){
  e.preventDefault();
  e.stopPropagation();
});
