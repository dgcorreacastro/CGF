//variavel de onde chama a função de carrega o ponto
const page = 'view';

//chamar função para pegar os dados do ponto
$(document).on('click', '.pontoMapaEuroNew', function(e){
  e.preventDefault();
  e.stopPropagation();

  //caso já tenha um ponto aberto retorna
  if($('.pontoMapaEuroNew.open').length != 0){
    return;
  }

  pegarDadosPonto($(this));
   
});

//mostrar onde está tocando na tela
$(document).on('touchstart', 'body', function(e){
  $(e.target).trigger('click');
  const touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
      
  const pontoPosition = {
  left: touch.pageX,
  top: touch.pageY
  }
  
  $('body').append(`<div class="circlePontoMapaEuroNew"
                              style="top: ${pontoPosition.top}px;
                              left: ${pontoPosition.left}px;
                              transform: translate(-50%, -50%);">
                              <div class="wave"></div>
                              </div>`);
});

//mostrar legenda dos horários quando segura sobre eles por 2 segundos
let mostrarLegenda;
$(document).on('touchstart', 'li', function(e){
  $('.horarioLegenda').remove();
  mostrarLegenda = setTimeout(()=>{
    e.stopPropagation();
    clearTimeout(mostrarLegenda);
    $(".circlePontoMapaEuroNew").remove();
    const touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
    const pontoPosition = {
    left: touch.pageX,
    top: touch.pageY
    }
    
    $('body').append(`<div class="horarioLegenda"
                              style="top: ${pontoPosition.top}px;
                              left: ${pontoPosition.left}px;
                              transform: translateX(-50%);">
                              ${$(this).attr('title')}
                              </div>`);
    
  }, 2000);
});

//remove a legenda quando clica na página
$(document).on('click', 'body', function(e){
  $('.horarioLegenda').remove();
  clearTimeout(mostrarLegenda);
});

//remove a legenda quando clica nela
$(document).on('click', '.horarioLegenda', function(e){
  $(this).remove();
});

$(document).on('touchmove', 'body', function(e){
  $('.horarioLegenda').remove();
  $(".circlePontoMapaEuroNew").remove();
  clearTimeout(mostrarLegenda);
});

$(document).on('touchend', 'body', function(e){
  e.stopPropagation();
  clearTimeout(mostrarLegenda);
  setTimeout(()=>{
    $(".circlePontoMapaEuroNew").remove();
  }, 1000);
});