var timeAtualiza = 300000;
var expanded = false;
var xhrurl = null;
var needLogin = false;
let timerRels = null;
let checkExcelTime = 1000;

let appName;
let portalName;

let timeTimingRels;

//VARIAVEIS DE ATUALIZAÇÃO DOS GRÁFICOS DO DASH E DOS DADOS DOS RELATÓRIOS

//Dash
var atualizarBuscaDash = null;
var atualizarPontualidade = null;
var atualizarCartoes = null;
var atualizarTaxaOc = null;

//Relatórios
var atualizarRelAnalitico = null;
var atualizarRelConsolidado = null;
var atualizarGerarRelatorioCartao =  null;
var atualizarGerarRelatorioListagemPax = null;
var atualizarGerarRelatorioSintetico = null;
var atualizarRelatorioEmbSemCartao = null;

let getdataDashNewController = new AbortController();

$( document ).ajaxStop(function(){
  
  if(needLogin){
    swal({
      title: 'Sessão Expirada',
      text: 'Por favor clique no botão a baixo e faça o login novamente',
      icon: 'warning',
      dangerMode: true,
      buttons: {
        confirm: "Fazer Login"
      },
    }).then(() => {
      window.location.href = "/login";
    });
  }
  
});

$(document).ready(function()
{
  
  appName = $("#appName").val();
  portalName = $("#portalName").val();

  //controle enter filtros
  $('body').on('keypress', function(e) {
      if (e.which == 13) {
          
          // filtro listagem passageiros
          if ($('#btnSearchPax').length) {
              e.preventDefault();
              $('#btnSearchPax').trigger('click');
          }
      }
  });

  //controle de versão
  if($("#cgfVersionTxt").length){
    let cgfVersion = localStorage.getItem("cgfVersion");
    cgfVersion = cgfVersion ? cgfVersion : 0;

    $("#cgfVersionTxt").html(`Controle de Versão: ${cgfVersion} / `);
  }

  if ($("#txtEditor").length)
    $("#txtEditor").richText();

  let tim = $("#timeAtualiza").val();
  timeAtualiza = tim * 60 * 1000; // Passando para milisegundos

  let reld = $("#relDays").val();
  relDays = reld ? Number(reld) : 7;
  let relm = $("#relMonth").val();
  relMonth = relm ? Number(relm) : 6;

  relDaysMsg = `O período máximo permitido é de ${relDays == 1 ? '1 dia' : `${relDays} dias`}!`;

  /////////// PEGANDO O GRÁFICO DO DASH \\\\\\\\\\\\\
  if($('#homeDashBoard').length){

    if( $("#cFret").length == 0 )
    {
      setTimeout(()=>{ 
        getdataDashNew(1);
        if($('#barCartaoUtiliza').length){
          getdataDashNew(2);
        }
        getdataDashNew(3);
        
      }, 1000);
    }
  }

  $('.wrapper1').on('scroll', function (e) {
    $('.customScroll').scrollLeft($('.wrapper1').scrollLeft());
    $('.tBodyScroll').scrollLeft($('.wrapper1').scrollLeft());
  }); 

  $('.customScroll').on('scroll', function (e) {
    $('.wrapper1').scrollLeft($('.customScroll').scrollLeft());
  });

  $('.tBodyScroll').on('scroll', function (e) {
    $('.customScroll').scrollLeft($('.tBodyScroll').scrollLeft());
  });

  $(window).on('load', function (e) {
    hasCustonScroll($('.customScroll'));
    setTimeout( () => {
      hasCustonScroll($('.customScroll'));
    },200);
    applyThWidth();
  });

    var url = window.location.href;

    if (url.search("itinerario") == -1 && 
        url.search("totem") == -1 && 
        url.search("parametro") == -1 && 
        url.search("atualizar") == -1 && 
        url.search("dashboard") == -1 && 
        url.search("login") == -1 && 
        url.search("usuarios") == -1 && 
        url.search("grupos") == -1 && 
        url.search("linhas") == -1 && 
        url.search("carros") == -1
        ){ 

    }

    // $('body').on('click', function(ele){
    //   let separator = " ";
    //   console.log(ele);
    //   let classs = ele.target.className ? ele.target.className.split(separator) : "";

    //   if( classs.indexOf("mnon") === -1 )
    //   {
    //     closeNav();
    //     $('.menuItem').removeClass('active');
    //   }
      
    // });

    $('.currentSub').closest('.menuItem').addClass('current');


    // if($('.menuItem.current').length == 0 && window.location.pathname != "/"){
    //   if(window.location.pathname != "/")
    //   window.location.href = "/";
    // }
    
    if($(window).width() >= 769){
      setTimeout(() => {
        if($('.menuItem.current').length){
          $('.newMenu').animate({scrollTop: 
            $('.menuItem.current')[0].offsetTop - ($('.newMenu').height()/2 - $('.menuItem.current').height()/2)
          }, 100);
        }
      }, 200);
    }
    
    
    $('.openMenuMob').on('click', function(e){
      $('.newMenu').toggleClass('opened');
      $('body').toggleClass('menuMobOpen');
      $(this).toggleClass('fa-times');
      $(this).toggleClass('fa-bars');
    });

    $('.newMenuLinks.paiMenu').on('click', function(e){
      e.preventDefault();
    });

    $('#navbarDropdown').on('click', function(){
      if($('#dropMenuItem').hasClass('show')){
        $('#dropMenuItem').removeClass('show');
      }else{
        $('#dropMenuItem').addClass('show');
      }
    });

    $('#navbarDropdownRel').on('click', function(){
      if($('#dropMenuRel').hasClass('hide')){
        $('#dropMenuRel').removeClass('hide');
      }else{
        $('#dropMenuRel').addClass('hide');
      }
    });

    $('body').on('click', function(e){
      if ($('#dropMenuItem').hasClass('show') && 
      e.target != $('#navbarDropdown')[0] && 
      e.target != $('#navbarDropdownRel')[0]) {
          $('#dropMenuItem').removeClass('show');
        if(!$('#dropMenuRel').hasClass('hide')){
          $('#dropMenuRel').addClass('hide');
        }
      }
    });

    $("#todosVeiculos").on('click', function(){

      if($('#todosVeiculos:checkbox:checked').length > 0){
        $('#veiculos option').prop('selected', true).change();
      } else {
        $('#veiculos option').prop('selected', false).change();
      }

    });

    $("#todosGrupos").on('click', function(){

      if($('#todosGrupos:checkbox:checked').length > 0){
        $('.grupoCheck').prop('checked', true).change();
      } else {
        $('.grupoCheck').prop('checked', false).change();
      }

    });
  
    $("#todosLinha").on('click', function(){

      if($('#todosLinha:checkbox:checked').length > 0){
        $('.linhaCheck').prop('checked', true).change();
      } else {
        $('.linhaCheck').prop('checked', false).change();
      }

    });

    $('.checkFiltro').on('change', function(){
      const qualFiltro = $(this).closest('.checkboxesFiltro');
      checkFiltrosCount(qualFiltro);
    });

    $(document).on('click', '.removeFiltroSel', function(e){
      const qualFiltro = $(this).closest('.checkboxesFiltroSelecionados').attr('id');
      const id = $(this).parent().attr('idSel');
      const checkBox = $(`.checkboxesFiltro[id=${qualFiltro}]`).find(`[id=${id}]`);
      if($(this).parent().hasClass('groupDefault')){
        $('#groupDefault').val(0);
      }
      $(this).parent().fadeOut(function(){
        $(checkBox).prop('checked', false);
        $(checkBox).removeClass('lbgroupDefault');
        $(this).remove();
        checkFiltrosCount($(`.checkboxesFiltro[id=${qualFiltro}]`));
      });
      
    });

    $(document).on('click', '.setGroupDefault', function(e){
      const qualFiltro = $(this).closest('.checkboxesFiltroSelecionados').attr('id');
      const id = $(this).parent().attr('idSel');
      const checkBox = $(`.checkboxesFiltro[id=${qualFiltro}]`).find(`[id=${id}]`);
      
      const grupo = $(this).attr('idGr');
      const nome = $(this).parent('span').text().trim();
      
      if($(this).parent().hasClass('groupDefault')){
        $('#groupDefault').val(0);
        $(checkBox).removeClass('lbgroupDefault');
        $(this).parent().removeClass('groupDefault');
        $(this).attr('title', `Selecionar ${nome} como GRUPO PADRÃO`);
        return;
      }
     
      const grupoDefault = $('.checkboxesFiltroSelecionados .groupDefault');
      if($(grupoDefault).length){
        const nomeDefault = $(grupoDefault)[0].childNodes[0].nodeValue.trim();
        const idDefault = $(grupoDefault).attr('idSel');
        const checkBoxDefault = $(`.checkboxesFiltro[id=${qualFiltro}]`).find(`[id=${idDefault}]`)
        $(checkBoxDefault).removeClass('lbgroupDefault');
        $(grupoDefault).find('.setGroupDefault').attr('title', `Selecionar ${nomeDefault} como GRUPO PADRÃO`);
        $(grupoDefault).removeClass('groupDefault');
      }

      $('#groupDefault').val(grupo);
      $(this).parent().addClass('groupDefault');
      $(checkBox).addClass('lbgroupDefault');
      $(this).attr('title', `Desativar ${nome} como GRUPO PADRÃO`);

    });

    $(document).on('click', '.btSetGroupDefault, .btRemoveGroupDefault', function(e){
      e.preventDefault();
      e.stopPropagation();
      const id = $(this).parent().attr('for');
      $('.checkboxesFiltroSelecionados[id=gruposSel]').find(`span[idsel=${id}] .setGroupDefault`).trigger('click');
    });
    
    $(document).on('click', '#okCheckFiltro', function(){
      $('.fechaCheckboxesFiltro').trigger('click');
      $('.fechaCheckboxesLinhasAdicionais').trigger('click');
    });

    $(document).on('click', '#limpaCheckFiltro', function(e){
      e.preventDefault();
      e.stopPropagation();
      const qualFiltro = $(this).closest('.checkboxesFiltro');
      $(qualFiltro).find('input[type=checkbox]:not(:disabled)').prop('checked', false);
      checkFiltrosCount($(qualFiltro));
    });

    $(document).on('click', '#todosCheckFiltro', function(e){
      e.preventDefault();
      e.stopPropagation();
      const qualFiltro = $(this).closest('.checkboxesFiltro');
      $(qualFiltro).find('input[type=checkbox]:not(:disabled)').prop('checked', true);
      checkFiltrosCount($(qualFiltro));
    });
    
    $(".buscaFiltroInput").on('keyup', function(){
      
      let value = $(this).val().toLowerCase();
      $(this).parent().parent('.checkboxesFiltro').children('.checkboxesFiltroLista').children('label').each(function () {
          if ($(this).text().toLowerCase().search(value) > -1) {
              $(this).show().removeClass('hidden');
          } else {
              $(this).hide().addClass('hidden');
          }
      });   
    });

    $(".buscaFiltroLinhasAdicionaisInput").on('keyup', function(){
      
      let value = $(this).val().toLowerCase();
      $(this).parent().parent('.linhasAdicionais').children('.checkboxesFiltroLista').children('li').each(function () {
          if ($(this).text().toLowerCase().search(value) > -1) {
              $(this).show().removeClass('hidden');
          } else {
              $(this).hide().addClass('hidden');
          }
      });   
    });

    $(".fechaCheckboxesFiltro").on('click', function(){
      $(this).parent('.checkboxesFiltro').removeClass('show');
      $('.checkboxesFiltroBackDrop').remove();
    });


    $(".fechaCheckboxesLinhasAdicionais").on('click', function(){
      $(this).parent('.linhasAdicionais').removeClass('show');
      $('.checkboxesFiltroBackDrop').remove();
    });

    $(".filtroSelect").on('click', function(){
      const checkboxesFiltro = $(this).attr('checkboxesFiltro');
      $(`.checkboxesFiltro[id=${checkboxesFiltro}]:not(.show)`).addClass('show');
      $('body').append('<div class="checkboxesFiltroBackDrop"></div>');
    });
  
    $("#todasLinhas").on('click', function(){

      if($('#todasLinhas:checkbox:checked').length > 0){
        $('#linhas option').prop('selected', true);
      } else {
        $('#linhas option').prop('selected', false);
      }

    });

    // Tratando os filtros Laterais
    $("#abaFiltro").on("click", function(){

      if($("#filtroLateral").hasClass('openFiltro')){
        $("#filtroLateral").removeClass('openFiltro');
      } else {
        $("#filtroLateral").addClass('openFiltro');
      }

    });

    $('.filtrosBtn').on('click', function(){
      $(this).addClass('open');
      $('.filtroDivNew form').addClass('open');
      $('.btsFiltro').addClass('open');
      if(!$('.filtroDivNew').hasClass('open')){
        $('.filtroDivNew').addClass('open');
      }else if(!$('.agendamentosBtn').hasClass('open')){

          $('.filtroDivNew').removeClass('open');

          setTimeout(() => {
            $(this).removeClass('open');
          }, 200);

      }
      $('.agendamentos').removeClass('open');
      $('.agendamentosBtn').removeClass('open');
    });

    $('.semagenda').on('click', function(){
      $('.filtrosBtn').trigger('click');
    });

    $('.agendamentosBtn').on('click', function(){
      if(!$('.filtroDivNew').hasClass('open')){
        $('.filtroDivNew').addClass('open');
      }
      $('.agendamentos').addClass('open');
      $(this).addClass('open');
      $('.filtrosBtn').removeClass('open');
      $('.btsFiltro').removeClass('open');
      $('.filtroDivNew form').removeClass('open');
    });

    $("#menuRel").on("click", function(){

      if($("#linkRel").hasClass('openRel')){
        $("#linkRel").removeClass('openRel')
      } else {
        $("#linkRel").addClass('openRel')
      }

    });

    $("#menuRelConfig").on("click", function(){

      if($("#linkRelConfig").hasClass('openRel')){
        $("#linkRelConfig").removeClass('openRel')
      } else {
        $("#linkRelConfig").addClass('openRel')
      }

    });


    $("#gerarLink").on('click', function(e){
      e.preventDefault();

      let url      = "";
      let client   = $("#ID_ORIGIN").val();
      let clieName = $("#ID_ORIGIN option:selected").html();
      let random   = Math.floor(Math.random() * 10000001); 
      url = client +'-'+ clieName.replace(/\s/g, '') +'-'+ random;
      $("#LINK").val(url);

    });

    $("#gerarLinkPax").on('click', function(e){
      e.preventDefault();

      let url      = "";
      let client   = $("#ID_ORIGIN").val();
      let clieName = $("#ID_ORIGIN option:selected").html();
      clieName     = clieName.split('-');
      let random   = Math.floor(Math.random() * 1000); 

      url = client +'-'+ clieName[0].replace(/\s/g, '') +'-'+ random;
      $("#LINK").val(url);

    });

    $("#gerarQrcode").on('click', function(e){
      e.preventDefault();
      $("#carregando").addClass('show');
      let client = $("#ID_ORIGIN").val();
      let baseURL = $("#baseURL").val();
      let random1 = Math.floor(Math.random() * 10000001);
      let random2 = Math.floor(Math.random() * 10000001);
  
      let url = client + '-' + random1;
      let cod = client + '' + random2;
  
      $("#qrcode").val(url);
      $("#codigo").val(cod);
  
      $("#divQr canvas").remove();
      $("#divQr img").remove();
  
      createQRCode(`${baseURL}app/qrcode?qr=${url}`)
          .then(() => {
              
              $("#carregando").removeClass('show');
  
             
              $("#divQr").show();
          })
          .catch((error) => {
              console.log("Erro ao criar o QR code:");
  
              $("#carregando").removeClass('show');
          });
    });
    
    function createQRCode(url) {
        return new Promise((resolve, reject) => {
            let qrcode = new QRCode(document.getElementById("divQr"), {
                text: url,
                width: 300,
                height: 300
            });
    
            // Verificar continuamente se o QR code foi renderizado
            let checkRenderInterval = setInterval(() => {
                if (document.getElementById("divQr").querySelector("canvas")) {
                    clearInterval(checkRenderInterval); // Parar a verificação
                    resolve(); // Resolva a promessa
                }
            }, 100); // Intervalo de verificação em milissegundos
        });
    }  

    $("#btnformRota").on("click", function(e){
      e.preventDefault();
      getRotasItinerario();
    });

    $("#btnformRotaAll").on("click", function(e){
      e.preventDefault();
      getRotasItinerario(1);
    });

    $("#btnformPax").on("click", function(e){
      e.preventDefault();
      getPaxItinerario();
    });

    $("#btnformPaxEspecial").on("click", function(e){
      e.preventDefault();
      //getPaxItinerarioEspecial();
      getPaxItinerario();
    });

    $("#centroCusto").on('keydown', function(e){
      if(e.which == 13 || e.which == 9){
        carregaCentroCusto();
      }
    });

    $("#centroCusto").on('blur', function(){
        carregaCentroCusto();
    });
    
    if($('#chartCartaoUtiliza').length && $("#cFret").length == 0){
      setTimeout(()=>{
        getDataDashCartoesSemUtil();
      }, 1000);
    }

    //setar icones aos titulos das páginas de acordo com os icones do menu
    if($('.pageTitle').length){
      let setIcon = '';
      let setText = '';
      let fatherTxt = false;
            
      let inTxt;

      if($('.currentSub').length){

        inTxt = $('.currentSub').text().trim()
        setIcon = $('.currentSub i').attr('class').replace('mnon', '');
        setText = `<b class="h4">&#10148; ${inTxt}</b>`;
        fatherTxt = $('.currentSub').closest('.menuItem').find('.mnon').first().text().trim();

        if(fatherTxt){
          setText = `${fatherTxt} ${setText}`;
        }

      }else{
        if(($('.menuItem.current').length)){
          setIcon = $('.menuItem.current').find('i:not(.maisMenu)').attr('class').replace('mnon', '');
          setText = $('.menuItem.current').find('b').text().trim();
        }else{
          return;
        }
        
      }
      $('.pageTitle').prepend(`<i class="${setIcon}"></i> ${setText}`);
      
      setNotifyMsgs(inTxt);
    }
});

  function setNotifyMsgs(inTxt){
    if($('#notificaScreen').length){
      $('#notificaScreen').val(`${$('#notificaScreen').val()} ${inTxt} está pronto!`);
    }

    if($('#notificaDownload').length){
      $('#notificaDownload').val(`Download ${$('#notificaDownload').val()} ${inTxt} concluído!`);
    }
    
    if($('#downloadName').length){
      $('#downloadName').val(`Relatório ${inTxt}`);
    }

  }


  function getCookie(cookieName){
    let cookie = {};
    document.cookie.split(';').forEach(function(el) {
      let [key,value] = el.split('=');
      cookie[key.trim()] = value;
    })
    return cookie[cookieName];
  }

function carregaCentroCusto(){
  let cc = $("#centroCusto").val();
  if(cc == ''){return;}
  $("#descricaoCC").val('');
  $.ajax({
    url: "/escala/centroCustoDescr",
    method: 'post',
    data : { cc },
    dataType: 'json',
    success:function(data)
    {
      if(data.txt == ''){
        swal({
          title: "ATENÇÃO",
          text: "Centro de Custo Inválido!",
          icon: "warning",
          button: "Fechar",
        });

        return false;
      }
      
      $("#descricaoCC").val( data.txt );

    }
  });
}

function checkFiltrosCount(element){
  const listaSel = $(`.checkboxesFiltroSelecionados[id=${$(element).attr('id')}]`);
  const selecionados = $(element).find('input[type=checkbox]:checked');
  const selectChange = $(`.filtroSelect[checkboxesFiltro=${$(element).attr('id')}]`);
  if(selecionados.length == 0){
    selectChange.attr('title', selectChange.attr('originaltxt'));
    selectChange.find('texto').text(selectChange.attr('originaltxt'));
    $('#groupDefault').val(0);
  }else{
    let txtSelecionado;
    if($(element).attr('id') == 'linhasSel'){
      if(selecionados.length == 1){
        txtSelecionado = '1 LINHA SELECIONADA';
      }
      else{
        txtSelecionado = `${selecionados.length} LINHAS SELECIONADAS`;
      }
    }

    if($(element).attr('id') == 'gruposSel'){
      if(selecionados.length == 1){
        txtSelecionado = '1 GRUPO SELECIONADO';
      }
      else{
        txtSelecionado = `${selecionados.length} GRUPOS SELECIONADOS`;
      }
    }

    if($(element).attr('id') == 'menuUserSel'){
      if(selecionados.length == 1){
        txtSelecionado = '1 MENU PERMITIDO';
      }
      else{
        txtSelecionado = `${selecionados.length} MENUS PERMITIDOS`;
      }
    }

    selectChange.attr('title', txtSelecionado);
    selectChange.find('texto').text(txtSelecionado);
  }

  if($(listaSel).length){
    
    $(listaSel).html('');

    

      $(selecionados).each(function(){
        
        const id = $(this).attr('id');
        const idGr = $(this).val();
        const nome = $(this).next('label')[0].childNodes[0].nodeValue.trim();

        if(listaSel.attr('id') == 'gruposSel'){
          
          $(listaSel).append(`<span title="${nome}" idSel="${id}">
          <i title="Remover ${nome}" class="fas fa-user-times removeFiltroSel"></i>
          <i title="Selecionar ${nome} como GRUPO PADRÃO" class="fa fa-star setGroupDefault" idGr="${idGr}"></i>
          ${nome}
          </span>`);

          if($('#groupDefault').length){
            if($('#groupDefault').val() == idGr){
              $(`.setGroupDefault[idGr=${idGr}]`).trigger('click');
            }
          }

        }

        if(listaSel.attr('id') == 'menuUserSel'){

          const icon = $(this).attr('icon');

          $(listaSel).append(`<span title="${nome}" idSel="${id}">
          ${icon != '' ?
          `<i class="${icon} iconMenuUserSel" style="cursor:default"></i>` : '<i class="fa fa-home" style="cursor:default"></i>'
          }
          ${nome}
          ${id != 'm-1' ? 
          `<i title="Remover ${nome}" class="fas fa-trash-alt right removeFiltroSel bg-danger text-white"></i>` : 
          ''}
          </span>`);
          
        } 
       
      });

    
  }
};

function saveQrCodeAppInfo(){
  
  const id = $('input[name=id]').val()
  const codigo = $('#codigo').val();
  const register = $('select[name=register]').val();
  const groupDefault = $('#groupDefault').val();
  const grupos = $("input[name='grupo[]']:checked")
              .map(function(){return $(this).val();}).get();

              const savedQrCodeAppInfo = {
                id: id,
                codigo: codigo,
                register: register,
                groupDefault: groupDefault,
                grupos: grupos,
              }
  
              localStorage.setItem('savedQrCodeAppInfo', JSON.stringify(savedQrCodeAppInfo));
}

function closeLinkApp(id, link){
  if (localStorage.getItem('savedQrCodeAppInfo') !== null) {
    const savedQrCodeAppInfo = JSON.parse(localStorage.getItem('savedQrCodeAppInfo'));
    const codigo = $('#codigo').val();
    const register = $('select[name=register]').val();
    const groupDefault = $('#groupDefault').val();
    const grupos = $("input[name='grupo[]']:checked")
                .map(function(){return $(this).val();}).get();
    
    
    if(savedQrCodeAppInfo.id == id){
      const noGroupChange = (grupos.length == savedQrCodeAppInfo.grupos.length) && grupos.every(function(element, index) {
        return element === savedQrCodeAppInfo.grupos[index]; 
      });
      
      if(savedQrCodeAppInfo.codigo != codigo ||
          savedQrCodeAppInfo.register != register ||
          savedQrCodeAppInfo.groupDefault != groupDefault ||
          !noGroupChange){
            swal({
              title: 'ATENÇÃO',
              text: "Fechar sem salvar alterações?",
              icon: 'warning',
              dangerMode: true,
              buttons: {
                cancel: "Cancelar",
                confirm: "Fechar"
              },
            }).then((result) => {
              if (result) {
                localStorage.removeItem('savedQrCodeAppInfo');
                window.location.href = link;
              }
            });
        return;
      }
    }
  }
  localStorage.removeItem('savedQrCodeAppInfo');
  window.location.href = link;

}
function saveLinkApp(){
  const gruposSelecionados = $('.checkboxesFiltro[id=gruposSel]').find('input[type=checkbox]:checked').length;
  const codigo = $('#codigo').val();
  const groupDefault = $('#groupDefault').val();
  if(codigo == ''){
    swal({
      title: 'ATENÇÃO',
      text: `Clique em "${$('#gerarQrcode').text()}" antes de prosseguir!`,
      icon: 'warning',
      buttons: {
        confirm: "OK"
      },
    });
  
    return;
  }

  if(gruposSelecionados == 0){
    swal({
      title: 'ATENÇÃO',
      text: `Selecione ao menos 1 Grupo!`,
      icon: 'warning',
      buttons: {
        confirm: "OK"
      },
    }).then((result) => {
      if (result) {
        $('.filtroSelect').trigger('click');
      }
    });
  
    return;
  }

  if(groupDefault == 0){
    swal({
      title: 'ATENÇÃO',
      text: `Selecione ao menos 1 GRUPO PADRÃO`,
      icon: 'warning',
      buttons: {
        confirm: "OK"
      },
    });
  
    return;
  }
  
  $('form').submit();
};

function hasCustonScroll(element){
  if(! $(element).length){
    $('.wrapper1').css('display','none');
    $('.wrapper1after').css('display','none');
    return false;
  }
  
  if($(element).get(0).scrollWidth > $(element).width()){
    $('.wrapper1').css('display','block');
    $('.wrapper1after').css('display','block');
    $('.div1').width($(element).get(0).scrollWidth);
    $('.wrapper1').scrollLeft(0);
  }else{
    $('.wrapper1').css('display','none');
    $('.wrapper1after').css('display','none');
  }
}

function applyThWidth(){
  if(!$('.applyWidth').length){
    return false;
  }
  
  let thWidths = [];

  function calculateWidths() {
    return new Promise((resolve, reject) => {
      $('tbody tr:not(.trHeight):eq(0) td:not(.dn)').each(function() {
        let elem = $(this)[0];
        if (elem) {
          let width = elem.getBoundingClientRect().width.toFixed(2);
          thWidths.push(width);
        }
      });
      resolve(thWidths);
    });
  }

  
  calculateWidths().then((thWidths) => {
    if(thWidths.length > 0){
      $('tr.applyWidth th').each(function(key){
        $(this).css('min-width','');
        let currentStyle = $(this).attr('style') || '';
        let newStyle = `min-width: ${thWidths[key]}px !important; `;
        let combinedStyle = newStyle + currentStyle; 
        $(this).attr('style', combinedStyle);

        if ($(this).hasClass('dn')) {
          let text = $(this).text();
          let matchingTh = $('.topHeader th[colspan="1"]').filter(function() {
              return $(this).text() === text;
          });
  
          if (matchingTh.length > 0) {
              $(matchingTh).attr('style', combinedStyle);
          }
        }
      });
  
      $('tbody tr').each(function(){
        $(this).children('td:not(.dn)').each(function(key){
          if(!$(this).hasClass('picTdRel')){
            $(this).css('min-width','');
            let currentStyle = $(this).attr('style') || '';
            let newStyle = `min-width: ${thWidths[key]}px !important; `;
            let combinedStyle = newStyle + currentStyle;
            $(this).attr('style', combinedStyle);
          }
          
        });
      });
    }
    
  }).then(() => {
    $('.customScroll, .tBodyScroll').addClass('show');
  });
}

window.onresize = function() {
  hasCustonScroll($('.customScroll'));
  applyThWidth();
}

function openCloseMenu(id, e)
{
  e.preventDefault();
  if($("#link-"+id).hasClass('openRel')){
    $("#link-"+id).removeClass('openRel')
  } else {
    $("#link-"+id).addClass('openRel')
  }
}

function changeCroqui(mod)
{
  switch(mod){
    case 1:
    case "1": 
      // Para 36 Lugares
      // Hide 37 until 46
      $(".l45e46").hide();
      $(".l37e44").hide();
      break;
    case 2:
    case "2": 
    // Para 44 Lugares
    // Hide 45 and 46
    $(".l45e46").hide();
    $(".l37e44").show();
      break;
    case 3:
    case "3": 
      // Para 46 Lugares
      // Show All
      $(".l45e46").show();
      $(".l37e44").show();
      break;
  }

}

var allPax = [];

function getInfosCar()
{

  let gr = $("#gruposAcesso").val();
  // let inter = $("#intercalaPol").val();
  let lines = $("#lines").val();
  $("#tableDetals").hide();
  $("#bodyPolPax").html('');

    allPax    = [];

    if ( gr == "" || gr == null || gr == undefined || gr == "Selecione"){

        swal({
            title: "ATENÇÃO",
            text: "Selecione um Grupo!",
            icon: "warning",
            button: "Fechar",
        });

        return false;
    }

    if ( lines == "" || lines == null || lines == undefined || lines == "Selecione"){

      swal({
          title: "ATENÇÃO",
          text: "Selecione uma Linha!",
          icon: "warning",
          button: "Fechar",
      });

      return false;
  }
    
    //// Busca os Passageiros daquela Linha e preenche as poltronas \\\\
    $("#carregando").addClass('show');

    $(".ocupado").each(function(){

      if ( $(this).find('path').hasClass('filOcup') )
      {
        $(this).find('.filOcup').addClass('fil3').removeClass('filOcup');
        $(this).find('.filOcupO').addClass('fil4').removeClass('filOcupO');
        $(this).removeClass('ocupado');

      }

    });

    $(".intercalar").each(function(){

      if ($(this).find('path').hasClass('filInterc') )
      {
        $(this).find('.filInterc').addClass('fil3').removeClass('filInterc');
        $(this).find('.filIntercO').addClass('fil4').removeClass('filIntercO');
        $(this).removeClass('intercalar');
      }

    });

    ///////// Limpar 'Cache' \\\\\\\\\
    for(let i=1; i<=46; i++)
    {
      $("#poltrona-"+i).removeClass('ocupado');
      $("#poltrona-"+i).removeClass('intercalar');
    }
    ///////////////\\\\\\\\\\\\\\\\\

    /// SE FOR POR AJAX TRAZ O CONTEUDO E COLOCA NA TABLE \\\
    $.ajax({
        url: "/poltronas/paxCar",
        method: 'post',
        data : {gr, lines},
        dataType: 'json',
        success:function(data){
          let listPax = data.paxs;
          let option = "";
          let datLn = data.dataLinha;
          allPax = data.dataLinha;
          let inter = data.intercala && data.intercala.intercalarPOL ? data.intercala.intercalarPOL : 2; // Default não

          // if (allPax.length == 0)
          // {
          //   $("#carregando").removeClass('show');
            
          //   swal({
          //     title: "ATENÇÃO",
          //     text: "Dados de passageiros não encontrado para essa Linha.",
          //     icon: "warning",
          //     button: "Fechar",
          //   });

          //   return false;
          // }

          /// Mapeando Croqui \\\\\
          for(let i=0; i < listPax.length; i++ )
          {

            let px = listPax[i];

            // if ( px.POLTRONA && px.POLTRONA != null)
            // { // Para os que já tem Poltrona
              
            //   let pol = parseInt(px.POLTRONA);
            //   $("#poltrona-"+pol).find('.fil3').addClass('filOcup').removeClass('fil3');
            //   $("#poltrona-"+pol).find('.fil4').addClass('filOcupO').removeClass('fil4');
            //   $("#poltrona-"+pol).addClass('ocupado');

            //   // Check Intercala 
            //   if (inter == 1)
            //   {
            //     let polInter = ( pol % 2 == 0 ) ? --pol : ++pol;
       
            //     $("#poltrona-"+polInter).addClass('intercalar');
            //     $("#poltrona-"+polInter).find('.fil3').addClass('filInterc').removeClass('fil3');
            //     $("#poltrona-"+polInter).find('.fil4').addClass('filIntercO').removeClass('fil4');
            //   }

            // }

            option += "<option value='"+px.NOME+"' matric='"+px.MATRICULA_FUNCIONAL+"' grupo='"+px.GRUPO+"'>"+ px.NOME +" - Matric.: "+ px.MATRICULA_FUNCIONAL +"</option>";

          }

          $("#pax").html(option);


          let trPax = "";

          for(let ii=0; ii < datLn.length; ii++)
          {
            let pxx = datLn[ii];
            let polTx = pxx.POLTRONA != null && pxx.POLTRONA != "0" && pxx.POLTRONA != 0 ? pxx.POLTRONA : "-";

            trPax += "<tr style='text-align: center;'>";
            trPax += "<td>"+pxx.NOME+"</td>";
            trPax += "<td>"+pxx.MATRICULA_FUNCIONAL+"</td>";
            trPax += "<td>"+polTx+"</td>";
            trPax += "</tr>";
          
            ////////// MONTANDO CROQUI \\\\\\\
            if ( pxx.POLTRONA && pxx.POLTRONA != null && pxx.POLTRONA != "0" && pxx.POLTRONA != 0)
            { // Para os que já tem Poltrona
              
              let pol = parseInt(pxx.POLTRONA);
              $("#poltrona-"+pol).find('.fil3').addClass('filOcup').removeClass('fil3');
              $("#poltrona-"+pol).find('.fil4').addClass('filOcupO').removeClass('fil4');
              $("#poltrona-"+pol).addClass('ocupado');

              // Check Intercala 
              if (inter == 1)
              {
                let polInter = ( pol % 2 == 0 ) ? --pol : ++pol;
       
                $("#poltrona-"+polInter).addClass('intercalar');
                $("#poltrona-"+polInter).find('.fil3').addClass('filInterc').removeClass('fil3');
                $("#poltrona-"+polInter).find('.fil4').addClass('filIntercO').removeClass('fil4');
              }

            }

          }

          if(trPax != "")
          { 
            $("#bodyPolPax").html(trPax);
            $("#tableDetals").show();
          }
        
          $("#carregando").removeClass('show');

        }
    });

    setTimeout( ()=>{
      $("#carregando").removeClass('show');
    }, 20000);
}

function savePaxPoltrona()
{
  let pax    = $("#pax").val();
  let polt   = $("#numberPol").html();
  let matric = $("#pax").find(":selected").attr('matric');
  let grupo  = $("#pax").find(":selected").attr('grupo');
  let lines  = $("#lines").val();
  let sent   = 0;

  if ( ( pax == "" || pax == undefined ) ||
        ( polt == "" || polt == undefined) ||
        ( grupo == "" || grupo == undefined)
    ){
      swal({
        title: "ATENÇÃO",
        text: "Ocorreu um erro ao tentar salvar! Atualize a pagina e tente novamente.",
        icon: "warning",
        button: "Fechar",
      });

      return false;
    }

    for(let i=0; i < allPax.length; i++ )
    {
      let px = allPax[i];

      if ( px.NOME == pax )
      {
        sent = px.SENTIDO;
      }

    }

    $("#carregando").addClass('show');

    $.ajax({
        url: "/poltronas/paxSavePol",
        method: 'post',
        data : { 
          pax, polt, matric, grupo, sent, lines
        },
        dataType: 'json',
        success:function(data)
        {

          $("#carregando").removeClass('show');

          $("#numberPol").html('');
          $("#sentidoPAX").html('');
          $("#modalChoses").modal('hide');

          if (data.success)
          {
            swal({
              title: "ATENÇÃO",
              text: "Dados salvo com sucesso.",
              icon: "success",
              button: "Fechar",
            });

            getInfosCar();

          } else {
            swal({
              title: "ATENÇÃO",
              text: "Ocorreu um erro ao tentar salvar! Atualize a pagina e tente novamente.",
              icon: "warning",
              button: "Fechar",
            });
          }

         
        }
    });

    setTimeout( ()=>{
      $("#carregando").removeClass('show');
    }, 20000);

}

function poltrona(p)
{
  
  let gr = $("#gruposAcesso").val();
  let ln = $("#lines").val();

  if ( $("#poltrona-"+p).hasClass('intercalar') ){

    swal({
      title: "ATENÇÃO",
      text: "Essa poltrona não pode ser usada. Está parametrizado para intercalar!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  if ( gr == "" || gr == null || gr == undefined || gr == "Selecione"){
      swal({
          title: "ATENÇÃO",
          text: "Busque um grupo!",
          icon: "warning",
          button: "Fechar",
      });

    return false;
  }

  if ( ln == "" || ln == null || ln == undefined || ln == "Selecione" ){
    swal({
        title: "ATENÇÃO",
        text: "Busque uma Linha!",
        icon: "warning",
        button: "Fechar",
    });

    return false;
  }

  /// Verifica se é para adiciona ou remover \\\
  if ( $("#poltrona-"+p).hasClass('ocupado') )
  {
    $("#name").val('');
    $("#matric").val('');
    $("#poltron").val('');
    $("#grupo").val('');

    for(let i=0; i < allPax.length; i++ )
    {
      let px = allPax[i];

      if ( ( px.POLTRONA == p ) || ( parseInt(px.POLTRONA) == parseInt(p) ) )
      {
 
        let htm = "<tr align='center'><td>"+px.NOME+"</td><td>"+px.MATRICULA_FUNCIONAL+"</td></tr>";

        $("#bodyInfoPol").html(htm);
        $("#name").val( px.NOME );
        $("#matric").val( px.MATRICULA_FUNCIONAL );
        $("#poltron").val( px.POLTRONA );
        $("#grupo").val( px.GRUPO );
        $("#sentido").val( px.SENTIDO );
      }

    }

    $("#modalInfosPax").modal('show');

  } else {

    $("#numberPol").html(p);
    $("#modalChoses").modal('show');

  }

}

function removePaxPoltrona()
{
  let nome   = $("#name").val();
  let matric = $("#matric").val();
  let poltro = $("#poltron").val();
  let grupo  = $("#grupo").val();
  let sent   = $("#sentido").val();

  $("#carregando").addClass('show');

  $.ajax({
    url: "/poltronas/paxRemovePol",
    method: 'post',
    data : { 
      nome, poltro, matric, grupo, sent
    },
    dataType: 'json',
    success:function(data)
    {
      $("#carregando").removeClass('show');
      $("#modalInfosPax").modal('hide');

      if (data.success)
      {
        swal({
          title: "ATENÇÃO",
          text: "Dados salvo com sucesso.",
          icon: "success",
          button: "Fechar",
        });

        getInfosCar();

      } else {
        swal({
          title: "ATENÇÃO",
          text: "Ocorreu um erro ao tentar salvar! Atualize a pagina e tente novamente.",
          icon: "warning",
          button: "Fechar",
        });
      }

      }
  });

  setTimeout( ()=>{
    $("#carregando").removeClass('show');
  }, 20000);

}

function xhr(url)
{
  
  if( xhrurl != null ){
    xhrurl.abort();
    xhrurl = null;
  }
  
  xhrurl = url;
}

//IMPORT PAX
function checkImportPax(grU){

  let grupoName = "";

  if(grU == 0){

    $("#sendFilePax .seletorPax").addClass('disabled');
    
  }else{

    grupoName = $('#groupID').find(":selected").text();
    $("#modelImportPax").closest('.disabled').removeClass('disabled');

  }

  $("#groupIDName").val(grupoName);

}

$('#modalImportPax').on('hidden.bs.modal', function () {
  $('#filePax').val('');
  $('#groupID').val(0).change();
  $('.sendFilePax').prop('disabled', true);
});

function sendPaxImport(){

  let filePax = $("#filePax").val();

  if( filePax == "")
  {
    swal({
        title: "ATENÇÃO",
        text: "Selecione um arquivo!",
        icon: "warning",
        button: "Fechar",
    });

    return false;
  }

  $('body').append(`<div class="paxProgress">
    <iframe src="" name="inactivePaxProgress" id="inactivePaxProgress"></iframe>
  </div>`);

  setTimeout(() => {
    $("#sendFilePax").submit();
  }, 200)
  
}

//END IMPORT PAX

//DESATIVA PAX
function checkDesativePax(grU){

  let grupoName = "";

  if(grU == 0){

    $("#sendInactivePax .seletorPax").addClass('disabled');
    
  }else{

    grupoName = $('#groupIDdesativa').find(":selected").text();
    $("#modelDesativaPax").closest('.disabled').removeClass('disabled');

  }

  $("#groupIDNameDesativa").val(grupoName);

}

$('#modalSendInactivePax').on('hidden.bs.modal', function () {
  $('#fileInactivePax').val('');
  $('#groupIDdesativa').val(0).change();
  $('.sendInactivePax').prop('disabled', true);
});

function sendInactivePax(){
  let fileInactivePax = $("#fileInactivePax").val();

  if( fileInactivePax == "")
  {
    swal({
        title: "ATENÇÃO",
        text: "Selecione um arquivo!",
        icon: "warning",
        button: "Fechar",
    });

    return false;
  }

  $('body').append(`<div class="paxProgress">
    <iframe src="" name="inactivePaxProgress" id="inactivePaxProgress"></iframe>
  </div>`);

  setTimeout(() => {
    $("#sendInactivePax").submit();
  }, 200);
  
}

//END DESATIVA PAX


//ERASE PAX
function checkErasePax(grU){

  let grupoName = "";

  if(grU == 0){

    $(".eraseBasePax").prop('disabled', true);
    
  }else{

    grupoName = $('#groupIDerase').find(":selected").text();
    $(".eraseBasePax").prop('disabled', false);

  }

  $("#groupIDNameErase").val(grupoName);
  
}

$('#modalEraseBase').on('hidden.bs.modal', function () {
  $('#groupIDerase').val(0).change();
});

function eraseBasePax(){

  let groupID = $('#groupIDerase').find(":selected").val();

  if( groupID == 0)
  {
    swal({
        title: "ATENÇÃO",
        text: "Selecione um Grupo Usuário!",
        icon: "warning",
        button: "Fechar",
    });

    return false;
  }

  let grupoName = $('#groupIDerase').find(":selected").text();

  swal({
    title: 'ATENÇÃO',
    text: "Tem certeza que deseja limpar DEFINITIVAMENTE a base de passageiros do Grupo Usuário:\n\n"+grupoName+"\n\nESSA AÇÃO NÃO PODERÁ SER DESFEITA.",
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Confirmar"
    },
  }).then((result) => {

    if (result) {
      $('body').append(`<div class="paxProgress">
        <iframe src="" name="inactivePaxProgress" id="inactivePaxProgress"></iframe>
      </div>`);

      setTimeout(() => {
        $("#eraseBasePax").submit();
      }, 200);
    }

  });
  
}


//END ERASE PAX

$(document).on('hidden.bs.modal', function () {
  $('.uploadProgressTxt').html('').fadeOut();
});

$(document).on('change', 'input[type=file]:not(.userPhotoUpload)', function(e){
  
  const IDFILE = $(this).attr('id');

  if($(this).val() == ''){

    if(IDFILE == 'fileInactivePax'){
      $('.sendInactivePax').prop('disabled', true);
    }

    if(IDFILE == 'filePax'){
      $('.sendFilePax').prop('disabled', true);
    }

    return;
  }

  const label = $(`label[for="${$(this).attr('id')}"]`);
  const extension = $(this).val().split('.').pop();
  const extOk = $(this).attr('extOk').split(',');

  if(!extOk.includes(extension)){
    swal({
      title: 'ATENÇÃO',
      text: `A extensão ".${extension}" não é permitida!`,
      icon: 'warning',
      buttons: {
        confirm: "OK"
      },
    }).then((result) => {
      if (result) {
        $(this).val('');

        if(IDFILE == 'fileInactivePax'){
          $('.sendInactivePax').prop('disabled', true);
        }

        if(IDFILE == 'filePax'){
          $('.sendFilePax').prop('disabled', true);
        }

      }
    });
    return;
  }
  const fileName = $(this).val().split('/').pop().split('\\').pop();
  $(label).find('.uploadProgressTxt').html(fileName).fadeIn();
  if($(this).hasClass('subOnchage')){
    $(this).parent('form').submit();
  }

  if(IDFILE == 'fileInactivePax'){
    $('.sendInactivePax').prop('disabled', false);
  }

  if(IDFILE == 'filePax'){
    $('.sendFilePax').prop('disabled', false);
  }

});


$(document).on('click', '#modelImportPax:not(.downloadingImpPax)', function(e){

  let = groupID = $("#groupID").length ? $("#groupID").val() : 0;

  if($("#groupID").length && groupID == 0){
    swal({
      title: "ATENÇÃO",
      text: 'Selecione um Grupo Usuário.',
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $(this).addClass('downloadingImpPax');

  $("#getExcelImportPax").remove();
  
  $('body').append(`<iframe style="display:none" src="/cadastroPax/excelImportPax?groupID=${groupID}" name="getExcelImportPax" id="getExcelImportPax"></iframe>`);

  let relName = 'importPaxExcel'
  let checkExcel = setInterval(() => {

    let isReady = getCookie(relName);
    if(isReady && isReady == 'ready'){
      clearInterval(checkExcel);
      document.cookie = relName+'=; Path=/; Max-Age=-99999999;';
      $(this).removeClass('downloadingImpPax');
      $("#filePax").closest('.disabled').removeClass('disabled');
    }
    
  }, checkExcelTime);

});

function releaseDesactive(){
  $("#fileInactivePax").closest('.disabled').removeClass('disabled');
}

$(document).on('click', '.btn_download', function(e){
  const btn = $(this);
  $(btn).find('.downloadProgressTxt').html('Carregando...');
  const url = $(this).attr('url');
  const filename = $(this).attr('filename');
  const fileDownload = new XMLHttpRequest();

  const endDownload = () =>{
    setTimeout(()=>{
      $(btn).removeClass('downloading');
      $(btn).find('.downloadProgress').fadeOut(function(){
        $(this).css('width','0%');
        $(this).removeAttr('style');
      });
      $(btn).find('.downloadProgressTxt').fadeOut(function(){
        $(this).html('');
        $(this).removeAttr('style');
      });
    }, 2000);
  };
  
  $(btn).addClass('downloading');
  fileDownload.open("GET", url, true);
  fileDownload.setRequestHeader("Cache-Control", "no-cache");

  fileDownload.onerror = function() {
    $(btn).find('.downloadProgressTxt').html('Erro ao baixar o arquivo!');
    endDownload();
    return;
  }

  fileDownload.onabort = function() {
    $(btn).find('.downloadProgressTxt').html('Download Cancelado!');
    endDownload();
    return;
  }

  fileDownload.onprogress = function(pe) {
    if(pe.lengthComputable) {
      $(btn).find('.downloadProgress').css('width', `${parseInt(pe.loaded*100/pe.total)}%`);
      $(btn).find('.downloadProgressTxt').html(`Fazendo Download: ${parseInt(pe.loaded*100/pe.total)}%`);
    } 
  } 

  fileDownload.onloadend = function(pe) {
    
    if(pe.total == 0){
      $(btn).find('.downloadProgressTxt').html('Erro ao baixar o arquivo!');
      endDownload();
      return;
    }
    $(btn).find('.downloadProgressTxt').html('Download Completo: 100%');
    let link = document.createElement('a');
    link.href = pe.target.responseURL;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    endDownload();
  }    

  fileDownload.send();
});

function showCheckboxes() {
  var checkboxes = document.getElementById("checkboxes");
  if (!expanded) {
    checkboxes.style.display = "block";
    expanded = true;
  } else {
    checkboxes.style.display = "none";
    expanded = false;
  }
}

function showCheckboxesLin() {
  var checkboxes = document.getElementById("checkLines");
  if (!expanded) {
    checkboxes.style.display = "block";
    expanded = true;
  } else {
    checkboxes.style.display = "none";
    expanded = false;
  }
}

function getRotationDegrees(obj) {
  var matrix = obj.css("-webkit-transform") ||
  obj.css("-moz-transform")    ||
  obj.css("-ms-transform")     ||
  obj.css("-o-transform")      ||
  obj.css("transform");
  if(matrix !== 'none') {
      var values = matrix.split('(')[1].split(')')[0].split(',');
      var a = values[0];
      var b = values[1];
      var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
  } else { var angle = 0; }
  return (angle < 0) ? angle + 360 : angle;
}

function removeNewChartDonut(container){

  $(`${container} .donutSemDados`).removeClass('show');
  $(`${container} .donutChart`).find('li').fadeOut(function(){
    $(this).remove();
  });

  $(`${container} .donutChartLegends`).find('li').fadeOut(function(){
    $(this).remove();
  });

  $(`${container} .donutCarrega`).fadeOut();

  if($(container).hasClass('relPage')){
    $(container).removeClass('loaded');
  }

}

//Função única para todos os Charts Donut do Dash
function drawNewChartDonut(isLoad = true, container, dataIni = false, dataFim = false) {
    
  //Para todos os charts
  if(isLoad == true)
    return true;
    removeNewChartDonut(container);
    let valorTotal = 0;
    let dados = []

    //Começa a tratar cada chart de acordo com o container

    //Pontualidade Chegada Viagens
    if(container == "#donutPontualidade"){   

      let lineViagens = $("#pontualViagensIda").val();
      lineViagens = JSON.parse(atob(lineViagens));

      if(lineViagens && (lineViagens[0][1] > 0 || lineViagens[1][1] > 0 || lineViagens[2][1] > 0 || lineViagens[3][1] > 0)){
        //Pontual
        if(lineViagens[0][1] > 0){
          valorTotal += lineViagens[0][1];
          dados.push(
            { "legenda": lineViagens[0][0], "qtd": lineViagens[0][1], "cor": lineViagens[0][2] },
          );
        }

        //Adiantado
        if(lineViagens[1][1] > 0){
          valorTotal += lineViagens[1][1];
          dados.push(
            { "legenda": lineViagens[1][0], "qtd": lineViagens[1][1], "cor": lineViagens[1][2] },
          );
        }

         //Atrasado
         if(lineViagens[2][1] > 0){
          valorTotal += lineViagens[2][1];
          dados.push(
            { "legenda": lineViagens[2][0], "qtd": lineViagens[2][1], "cor": lineViagens[2][2] },
          );
        }

        //Não Realizado Sistema
        if(lineViagens[3][1] > 0){
          valorTotal += lineViagens[3][1];
          dados.push(
            { "legenda": lineViagens[3][0], "qtd": lineViagens[3][1], "cor": lineViagens[3][2] },
          );
        }
      }
    }

    //Taxa de Ocupação das Linhas
    if(container == "#donutOcupaLinhas"){
      const limit     = $("#limit").val(); 
      const embarcado = parseInt($("#embarcado").val());
      const semUso    = parseInt((embarcado > 0 && limit > 0) ? limit - embarcado : 0);
      const graphReColor = $("#graphReColor").val();
      const graphSreColor = $("#graphSreColor").val();
      const graphReTxt = $("#graphReTxt").val();
      const graphSreTxt = $("#graphSreTxt").val();
      valorTotal = limit;
      if(embarcado > 0){
        dados.push(
          { "legenda": graphReTxt, "qtd": embarcado, "cor": graphReColor },
        );
      }
      
      if(semUso > 0){
        dados.push(
          { "legenda": graphSreTxt, "qtd": semUso, "cor": graphSreColor },
        );
      }
    }
    
    //Se retorna dados monta o gráfico
    if(dados.length){
      // const now = new Date;
      // const data = `${now.getDate() < 10 ? '0' : ''}${now.getDate()}/${now.getMonth()+1 < 10 ? '0' : ''}${now.getMonth()+1}/${now.getFullYear()}`;
      // const hora = `${now.getHours() < 10 ? '0' : ''}${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;
      setTimeout(() => {
        for(i=0; i<dados.length; i++) {

          let porcentoTxt = parseFloat((dados[i].qtd * 100) / valorTotal).toFixed(1);
          porcentoTxt = porcentoTxt.slice(-1) === '0' ? porcentoTxt.slice(0, -2) : porcentoTxt;

          const porcentoDash = porcentoTxt;

          porcentoTxt = `${porcentoTxt.replace('.',',')}%`;
          
          $(container).get(0).style.setProperty(`--donutChartDeg${i+1}`, porcentoDash);
          $(container).get(0).style.setProperty(`--donutChartColor${i+1}`, dados[i].cor);

          let top = '-5';
          let left = 5;

          if((i+1) == dados.length){
            top = 0;
          }

          else{
            if(i == 0){

              if(porcentoDash < 1){
                top = '-18'
              }

              else if(porcentoDash < 7){
                top = '-10';
              }

              else if(porcentoDash < 10){
                top = '-2';
              }

              else if(porcentoDash > 40){
                top = 35;
                left = 20;
              }
              else{
                top = 0;
              }
          }else if(i == 1){
            if(porcentoDash > 40){
              top = 35;
              left = 20;
            }
            else{
              top = 0;
              left = 0;
            }
          }
            
          }

          $(`${container} .donutChart`).append(`
          <li>
              <span style="top: ${top}px; left: ${left}px" title='${dados[i].legenda}: ${porcentoTxt}'>${porcentoTxt}</span>
          </li>`);
  
            $(`${container} .donutChartLegends`).append(`
            <li>
              <span title='${dados[i].legenda}: ${porcentoTxt}'>${dados[i].legenda}</span>
            </li>`);
        }
        
        let data_inicio = dataIni ? dataIni : $('#data_inicio').val();
        let data_fim = dataFim ? dataFim : $('#data_fim').val();

        const diaIni = data_inicio.slice(-2);
        const mesIni = data_inicio.substring(5, 7);
        const anoIni = data_inicio.substring(0, 4);

        const diaFim = data_fim.slice(-2);
        const mesFim = data_fim.substring(5, 7);
        const anoFim = data_fim.substring(0, 4);

        let dataShow = `${diaIni}/${mesIni}/${anoIni}`;

        if(data_inicio != data_fim){
          dataShow = `${dataShow} - ${diaFim}/${mesFim}/${anoFim}`;
        }

        $(`${container} .atualizado`).html(dataShow);

        if($(container).hasClass('relPage')){
          $(container).addClass('loaded');
        }
      }, 500);
      // setTimeout(() => {
      //   let sign;
      //   $(`${container} .donutChart`).find('li').each(function(){
      //     const degree = getRotationDegrees($(this));
      //     if(degree < 0) {
      //       sign = -1;
      //     } else {
      //       sign = 1;
      //     }
      //     if(degree == 180){
      //       $(this).find('span').css({
      //         'top':'5px',
      //         'left':'5px'
      //       });
      //     }else{
      //       const numY = Math.abs($(this).position().top + sign*( ($(this).outerHeight() / 2) - Math.sin(degree)));
            
      //       const top = Math.floor(degree - numY - 30);
      //       $(this).find('span').css({
      //         'top':`${top}px`,
      //         'left':'44px'
      //       });
      //       console.log(degree);
      //       console.log(sign);
      //       console.log(numY);
      //       console.log('----');
      //     }
          
      //   });
      // }, 2000);
    }else{
      //Se não retorna dados mostra a mensagem de sem dados
      $(`${container} .donutSemDados`).addClass('show');
    }
}

function drawChartCardUtil(isLoad = true) 
{

  if(isLoad == true)
    return true;

  $('#barCartaoUtiliza .donutSemDados').removeClass('show');
  $('#barCartaoUtiliza .datas').html('');
  $('#barCartaoUtiliza .marcaChart').html('');
  $('#barCartaoUtiliza .barras').html('');
  $('#barCartaoUtiliza .carrega').removeClass('loaded');
  $('#barCartaoUtiliza .marcaChart').removeClass('loaded');
  const graphBarraColor = $("#graphBarraColor").val();
  const graphBarraTxtColor = $("#graphBarraTxtColor").val();
  
  let cardUtil = $("#cartoesUltizac").val();
  
  if(cardUtil){
    cardUtil = JSON.parse(atob(cardUtil));
    if (!cardUtil.error || cardUtil.error == false)
    {
      let valoresTotais = [];

      cardUtil.map(function(dia) {
        const qtd = dia[1];
        valoresTotais.push(qtd);
      });

      const valorMaximo = Math.max(...valoresTotais.map(o => o));
     
      if(valorMaximo != 0){
        let valorAllLine = true;

        cardUtil.map(function(dia) {
          const data = dia[0];
          const qtd = dia[1];
          $('#barCartaoUtiliza .datas').append(`<li>${data}</li>`);
          $('#barCartaoUtiliza .barras').append(`<div class="barra" style="background-color: ${graphBarraColor}; color: ${graphBarraTxtColor}" title='(${data}) - ${qtd}'>${qtd}</div>`);
        });

        // const now = new Date;
        // const data = `${now.getDate() < 10 ? '0' : ''}${now.getDate()}/${now.getMonth()+1 < 10 ? '0' : ''}${now.getMonth()+1}/${now.getFullYear()}`;
        // const hora = `${now.getHours() < 10 ? '0' : ''}${now.getHours()}:${now.getMinutes() < 10 ? '0' : ''}${now.getMinutes()}`;
        
        const valorMinimo = Math.min(...valoresTotais.map(o => o));
        const diffMaioMenor = (valorMaximo - valorMinimo);
        const checkDiff = Math.floor(diffMaioMenor/7);

        for(let lc=0; lc < 6; lc++){
          let valor;
          if(checkDiff != 0){
            valor = Math.ceil((valorMaximo/5)*lc);
          } else {
            valor = (valorMinimo + (lc - 1));
          }

          if(!valorAllLine){
            valor = Math.abs(lc % 2) == 1 ? valor : '';
          }
          
          $('.marcaChart').append(`<div class="linha" style="--valor: '${valor}'"></div>`);
        }       

        $('#barCartaoUtiliza .barras .barra').each(function(){

        const valor = Number($(this).text());
        let height;

        if(checkDiff != 0){
          height = Math.floor((valor * 100) / valorMaximo);
        } else {
          const minXvalor = ((valor - valorMinimo) + 1);
          const fator = Math.ceil((valor * (20*minXvalor)));
          height = Math.ceil(fator / valorMaximo);
          
        }

        setTimeout(()=>{
        $(this).css({'height': `${height}%`});
        $(this).addClass('show');
        }, 200);
        });

        // let data_inicio = $('#data_inicio').val();
        // let data_fim = $('#data_fim').val();

        // const diaIni = data_inicio.slice(-2);
        // const mesIni = data_inicio.substring(5, 7);
        // const anoIni = data_inicio.substring(0, 4);

        // const diaFim = data_fim.slice(-2);
        // const mesFim = data_fim.substring(5, 7);
        // const anoFim = data_fim.substring(0, 4);

        // let dataShow = `${diaIni}/${mesIni}/${anoIni}`;

        // if(data_inicio != data_fim){
        //   dataShow = `${dataShow} - ${diaFim}/${mesFim}/${anoFim}`;
        // }

        // $('#barCartaoUtiliza .atualizado').html(dataShow);


        $('#barCartaoUtiliza .marcaChart').addClass('loaded');
      } else {
        $('#barCartaoUtiliza .donutSemDados').addClass('show');
      }
    } else {
      $('#barCartaoUtiliza .donutSemDados').addClass('show');
    }
  } else {
    $('#barCartaoUtiliza .donutSemDados').addClass('show');
  }  
  $('#barCartaoUtiliza .carrega').addClass('loaded');
  
}

function getDataDashCartoes(url, data) {

  return $.ajax({
    url: url,
    type: 'post',
    data : data,
    dataType: 'json',
  });

}

async function getDataDashCartoesSemUtil() {
  $('#chartCartaoUtiliza').addClass('carregando');
  $('.linhaChart').remove();
  $('.linhaContainer').remove();
  try {

    let qtdDias = $("#qtdDias").val();
    $('#diasCartaoTitulo').text((qtdDias == 1) ? 'Hoje' : `Últimos ${qtdDias} dias`);
    var gr1 = []; 

    $('input[name="grupo[]"]:checked').each(function() {
      gr1.push($(this).val());
    });

    const data= {qtdDias, groups: gr1.join(',') }

    const res = await getDataDashCartoes("/relatorioCartaoUtilizacao/getDataDash", data);

    if(res.success){
      
      
      $('#chartCartaoUtiliza').removeClass('carregando');

      let cartsPerDayAndLine = JSON.parse(atob(res.cartsPerDayAndLine));
      
      const dias = Object.keys(cartsPerDayAndLine).map((key) => [key, cartsPerDayAndLine[key]]);
      
      let linhasAgrupadas = [];

      dias.map(function(dia) {
      
        let infosCid = Object.keys(dia[1]).map((key) => [key, dia[1][key]]);
        
        infosCid.map(function(linha) {
          const idLinha = linha[0];
          const NMLINHAIDA = linha[1]['NMLINHAIDA'] ? linha[1]['NMLINHAIDA'] : '';
          const NMLINHAVOL = linha[1]['NMLINHAVOL'] ? linha[1]['NMLINHAVOL'] : '';
          const QTDLINHAIDA = linha[1]['QTDLINHAIDA'] ? linha[1]['QTDLINHAIDA'] : 0;
          const QTDLINHAVOL = linha[1]['QTDLINHAVOL'] ? linha[1]['QTDLINHAVOL'] : 0;

          if(linhasAgrupadas.length > 0 && linhasAgrupadas.find(x => x.id == idLinha)){
            linhasAgrupadas.find(x => x.id == idLinha).QTDLINHAIDA = 
            linhasAgrupadas.find(x => x.id == idLinha).QTDLINHAIDA + QTDLINHAIDA;

            linhasAgrupadas.find(x => x.id == idLinha).QTDLINHAVOL = 
            linhasAgrupadas.find(x => x.id == idLinha).QTDLINHAVOL + QTDLINHAVOL;

          }else{
            const linhaCid = {
              'id':          idLinha,
              'NMLINHAIDA':  NMLINHAIDA,
              'NMLINHAVOL':  NMLINHAVOL,
              'QTDLINHAIDA': QTDLINHAIDA,
              'QTDLINHAVOL': QTDLINHAVOL,
            }
            linhasAgrupadas.push(linhaCid);
          }
        });
      });

      let linhas = 0;
      let valoresTotais = [];
      linhasAgrupadas.map(function(linha) {
        linhas++;
        $('.chartNewDois').append(`<div title="Linha: ${(linha['NMLINHAIDA']) ? linha['NMLINHAIDA'] : linha['NMLINHAVOL']}" class="linhaChart" idLinha="${linha['id']}">
        </div>`);
        if(linha['QTDLINHAIDA']){
          valoresTotais.push(linha['QTDLINHAIDA']);
          $(`.linhaChart[idLinha=${linha['id']}]`).append(`<span title="LINHA IDA" class="barraLinha"><b>${linha['QTDLINHAIDA']}</b></span>`);
        }
        if(linha['QTDLINHAVOL']){
          valoresTotais.push(linha['QTDLINHAVOL']);
          $(`.linhaChart[idLinha=${linha['id']}]`).append(`<span title="LINHA VOLTA" class="barraLinha"><b>${linha['QTDLINHAVOL']}</b></span>`);
        }
        $(`.linhaChart[idLinha=${linha['id']}]`).append(`<div class="legendaLinha">${(linha['NMLINHAIDA']) ? `${linha['NMLINHAIDA'].substring(0,30)}...` : `${linha['NMLINHAVOL'].substring(0,30)}...`}</div>`);
      });

      const valorMaximo = Math.max(...valoresTotais.map(o => o));

      $('.linhaChart').each(function(){
        if($(this).find('.barraLinha').length == 1){
          $(this).addClass('umaLinha');
        }
      });

      $('.barraLinha').each(function(){
        const valor = Number($(this).find('b').text());
        const height = Math.floor((valor * 100) / valorMaximo);
        setTimeout(()=>{
          $(this).css({'height': `${height}%`});
          if(height > 40){
            $(this).addClass('medioBarChart');
          }
          if(height > 60){
            $(this).addClass('altoBarChart');
          }
        }, 200);
      });

      for(let lc=0; lc < 4; lc++){
        let valor;
        if(lc == 0){
          valor = 0;
        }
        else if(lc == 3){
          valor = valorMaximo;
        }
        else{
          valor = Math.ceil((valorMaximo/3)*lc);
        }
        $('.marcaChart').append(`<div class="linhaContainer"><span>${valor}</span><div class="linha"></div></div>`);
      }
    } 

  } catch(err) {
    $('#chartCartaoUtiliza').removeClass('carregando');
    console.log(err);
  }

}

async function getdataDashNew(w){
  
  let pontualidade  = 1;
  let cartaoUtiliza = 1;
  let taxaOcupa     = 1;
  
  const { signal } = getdataDashNewController;

  const settings = {
    signal,
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  };
  
  switch(w){
    case 1:
    pontualidade = 2; break;
    case 2:
    cartaoUtiliza = 2; break;
    case 3:
    taxaOcupa = 2; break;
  }
    const url = `home/getDataDash?pontualidade=${pontualidade}&cartaoUtiliza=${cartaoUtiliza}&taxaOcupa=${taxaOcupa}`;
    await fetch(url, settings)
    .then( resposta => {
      return resposta.json();
    })
    .then ( ret => {
      if(ret.success){
        
        //PONTUALIDADE DE CHEGADA DAS VIAGENS
        if(w == 1){
          let pontualid = ret.pontualid;
          $("#pontualViagensIda").val(pontualid.ida);
          drawNewChartDonut(false, '#donutPontualidade');         
          
          //ATUALIZAR PONTUALIDADE DE CHEGADA DAS VIAGENS
          if (atualizarPontualidade === null) {
              
              atualizarPontualidade = setInterval(() => {
                $('#donutPontualidade .donutCarrega').fadeIn();
                getdataDashNew(1)
              }, timeAtualiza);
          }
        }

        //CARTÕES NÃO UTILIZADOS NOS ÚLTIMOS 7 DIAS
        if(w == 2){
          $("#cartoesUltizac").val(ret.cartoesUltizac);
          drawChartCardUtil(false);

          //ATUALIZAR CARTÕES NÃO UTILIZADOS NOS ÚLTIMOS 7 DIAS
          if (atualizarCartoes === null) {
            
            atualizarCartoes = setInterval(() => {
              $('#barCartaoUtiliza .carrega').removeClass('loaded');
              getdataDashNew(2)
            }, timeAtualiza);
          }
        }
               
        //TAXA OCUPAÇÃO DAS LINHAS
        if(w == 3){
          let taxaOcupacao = ret.taxaOcupacao;
          if(taxaOcupacao)
          {
            $("#limit").val(taxaOcupacao.limits);
            $("#embarcado").val(taxaOcupacao.embarcados);
          }
          drawNewChartDonut(false, '#donutOcupaLinhas');

          //ATUALIZAR TAXA OCUPAÇÃO DAS LINHAS
          if (atualizarTaxaOc === null) {
            
            atualizarTaxaOc = setInterval(() => {
              $('#donutOcupaLinhas .donutCarrega').fadeIn();
              getdataDashNew(3)
            }, timeAtualiza);
          }
        }
      }
    })
    .catch((err) => {
     
      if(err.message.includes('is not valid JSON')){
        swal({
          title: 'Sessão Expirada',
          text: 'Por favor clique no botão a baixo e faça o login novamente',
          icon: 'warning',
          dangerMode: true,
          buttons: {
            confirm: "Fazer Login"
          },
        }).then(() => {
          window.location.href = "/login";
        });
      }
    });
};



window.addEventListener("beforeunload", function (e) {

  if($('#abortGetRelsBtn').is(":visible") || $('#abortGetViagem').is(":visible") || $('#abortGetExcel').is(":visible")){
    e.preventDefault();
    return (e.returnValue = "");
  }
  
  $('#abortGetRelsBtn').hide();
  $('#abortGetViagem').hide();
  $('#abortGetExcel').hide();
  $("#getExcel").remove();
  $("#carregando").addClass('show');
  getdataDashNewController.abort(); 
  
});

async function buscarDadosDash(atualiza = false)
{
  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();

  let getMonI = data_inicio.split("-");
  let getMonF = data_fim.split("-");

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  if(!atualiza){
    $("#carregando").addClass('show');

    if (atualizarBuscaDash !== null) {
      clearInterval(atualizarBuscaDash);
      atualizarBuscaDash = null;
    }

  }else{
    $('#donutPontualidade .donutCarrega').fadeIn();
    $('#donutOcupaLinhas .donutCarrega').fadeIn();
  }

  if (data_inicio > data_fim) {
    swal({
      title: "ATENÇÃO",
      text: "A Data Início não pode ser maior que a Data Fim!",
      icon: "warning",
      button: "Fechar",
    });

    $("#carregando").removeClass('show');
    $('#donutPontualidade .donutCarrega').fadeOut();
    $('#donutOcupaLinhas .donutCarrega').fadeOut();
    return false;
  }

  if (getMonI[1] > 12 || getMonF[1] > 12)
  {
    swal({
      title: "ATENÇÃO",
      text: "O Mês não pode ser maior que 12!",
      icon: "warning",
      button: "Fechar",
    });

    $("#carregando").removeClass('show');
    $('#donutPontualidade .donutCarrega').fadeOut();
    $('#donutOcupaLinhas .donutCarrega').fadeOut();
    return false;
  }

  $("#carregando").addClass('show');
  setTimeout(() => {
    $('#abortGetRelsBtn').fadeIn();
  }, 100);

  const { signal } = getdataDashNewController;

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "lns": lns.join(', ')
  };
    
  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch('/home/atualizaDash', settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
      if(ret.status){
        
        if (atualizarPontualidade !== null) {
          clearInterval(atualizarPontualidade);
          atualizarPontualidade = null;
        }
      
        if (atualizarTaxaOc !== null) {
          clearInterval(atualizarTaxaOc);
          atualizarTaxaOc = null;
        }
        
        //ATUALIZAR BUSCA DASH 
        if (atualizarBuscaDash === null) {
          atualizarBuscaDash = setInterval(() => {
            buscarDadosDash(true);
          }, timeAtualiza);
        }

        $("#pontualViagensIda").val(ret.pontualidades.ida);
        drawNewChartDonut(false, '#donutPontualidade');        

        $("#limit").val(ret.limit);
        $("#embarcado").val(ret.embarcado);
        drawNewChartDonut(false, '#donutOcupaLinhas');

        $("#carregando").removeClass('show');
        $('#donutPontualidade .donutCarrega').fadeOut();
        $('#donutOcupaLinhas .donutCarrega').fadeOut();
      
      }else {
            
        swal({
          title: "ATENÇÃO",
          text: "Nenhum resultado encontrado para os filtros usados!",
          icon: "warning",
          button: "Fechar",
          });
          $("#carregando").removeClass('show');
          $('#abortGetRelsBtn').hide();

          clearInterval(atualizarRelConsolidado);
          atualizarRelConsolidado = null;
          
          return false;
      }

      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

    }).catch((err) => {
        $('#abortGetRelsBtn').hide();
        if(err.message.includes('is not valid JSON')){
            swal({
            title: 'Sessão Expirada',
            text: 'Por favor clique no botão a baixo e faça o login novamente',
            icon: 'warning',
            dangerMode: true,
            buttons: {
                confirm: "Fazer Login"
            },
            }).then(() => {
            window.location.href = "/login";
            });
        }
    });
}

function drawChart() 
{

	var data = google.visualization.arrayToDataTable([
    ['Task', 'Hours per Day'],      
    ['NÃO', 32],
    ['SIM', 7]
    ]);

  var options = {
    title: 'Exceção',
    is3D: true,
  };

  var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
  chart.draw(data, options);
}

function gerarRelatorioRastreaPax(form, url, ajax = 0, tbody = "")
   {

    if(($("#nome").val() == "" || $("#registro").val()) && $("#dias").val() == ""){
      swal({
        title: "ATENÇÃO",
        text: "Necessário Preencher o Nome ou Matrícula, e a quantidade de dias!",
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }

    $("#bodyModalPax").html("");

    let nome      = $("#nome").val();
    let registro  = $("#registro").val();
    let grupo     = $("#grupo").val();
    let grupoName = $('#grupo').find(":selected").text();
    let dias      = $("#dias").val();

    if(ajax == 0 && ( $("#registro").val() != "" || $("#nome").val() != "" )) {

      window.open(url + "?nome="+nome+"&registro="+registro+"&grupo="+grupo+"&dias="+dias+"&grupoName="+grupoName, '_blank');

    } else {
      $("#carregando").addClass('show');
    /// SE FOR POR AJAX TRAZ O CONTEUDO E COLOCA NA TABLE \\\
    $("#modais").html("");
    $(tbody).html(''); 

    $.ajax({
      url: "/relatorioRastreamento/resultado",
      method: 'post',
      data : {
        "nome": nome,
        "registro": registro,
        "grupo": grupo,
        "dias": dias
      },
      dataType: 'json',
      success:function(data){
        let ret       = data.linhas; 
        let htmlBody  = "";
        let htmlModal = "";

          /// SE FOR PARA ESCOLHER O PASSAGEIRO PRIMEIRO \\\
          if(data.paxsLeng && registro == ""){
            let pax = data.paxs;
            let trs = "";

            for(let i=0; i < data.paxsLeng; i++){
              let mat = pax[i].MATRICULA_FUNCIONAL ? pax[i].MATRICULA_FUNCIONAL : "-";

              trs += '<tr class="cursor" onclick=\'selectPaxMap("'+mat+'", "'+pax[i].NOME+'", "'+form+'", "'+url+'", "'+ajax+'", "'+tbody+'")\'>'+
              '<td>'+pax[i].NOME+'</td>'+
              '<td>'+mat+'</td>'+
              '</tr>';
            }

            $("#bodyModalPax").html(trs);
            $("#modalPaxSelect").modal("show");
            $("#carregando").removeClass('show');
            return false;

          } else if(data.linhasLeng){

            for(let i = 0; i < data.linhasLeng; ++i){
              
              let pol = ret[i].POL ? ret[i].POL : "-";

              htmlBody = '<tr class="control-'+i+' cursor hide" title="Click para ver mais informações" onclick="openModalEfeito(\'#modalList-\' , '+i+')">'+
              '<td>'+grupoName+'</td>'+
              '<td>'+ret[i].prefixo+'</td>'+
              '<td>'+ret[i].linha+'</td>'+
              '<td>'+ret[i].sentido+'</td>'+
              '<td>'+ret[i].data+'</td>'+
              '<td>'+pol+'</td>'+
              '</tr>';
              
              $(tbody).append(htmlBody);
              
              let part  = ret[i].passageiros;
              let partEx= ret[i].paxSemCart;
              let trs   = "";

              for(let ii = 0; ii < part.length; ii++){
                trs += '<tr><td>'+part[ii].geral+'</td></tr>';
              }
              
              for(let ii = 0; ii < partEx.length; ii++){
                trs += '<tr><td>'+partEx[ii]+'</td></tr>';
              }

              htmlModal = '<div class="modal cssanimation fadeInBottom" id="modalList-'+i+'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">'+
              '<div class="modal-dialog" role="document" style="min-width: 45%;">'+
              '<div class="modal-content" style="background-color: #024845 !important;">'+
              '<div class="modal-header">'+
              '<h5 class="modal-title" style="color: #fff !important;">Passageiros X Linha</h5>'+
              '<button type="button" class="close" onclick="closeAnimation(\'#modalList-\' , '+i+')" aria-label="Close">'+
              '<span aria-hidden="true">&times;</span>'+
              '</button></div>'+
              '<div class="modal-body">'+
              '<table id="table" class="table table-striped"> <thead>'+
              '<tr>'+
              '<th scope="col" style="min-width: 100%;width: 600px;">Passageiro - Viagem: '+ret[i].idViagem+'</th>'+
              '</tr>'+
              '</thead>'+
              '<tbody>'+trs+'</tbody>'+
              '</table>'+
              '</div>'+
              '<div class="modal-footer">'+
              '<button type="button" class="btn btn-secondary" onclick="closeAnimation(\'#modalList-\', '+i+')">Fechar</button>'+
              '</div></div></div></div>';
              
              $("#modais").append(htmlModal);
            }
            
            for(let i = 0; i < ret.length; ++i){
              setTimeout(function(){
                $(".control-"+i).removeClass('hide');
                $(".control-"+i).addClass('trShow');
              }, 1000);
            }

          } else {

            if(data.error.length){
              swal({
                title: "ATENÇÃO",
                text: data.error.error,
                icon: "warning",
                button: "Fechar",
              });
            } else if(!data.linhas.length){
              swal({
                title: "ATENÇÃO",
                text: "Sem resultado para o filtro usado!",
                icon: "warning",
                button: "Fechar",
              });
            } else {
              swal({
                title: "ATENÇÃO",
                text: "Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente!",
                icon: "warning",
                button: "Fechar",
              });
            }
            $("#carregando").removeClass('show');
            return false;
          }
          
          $("#carregando").removeClass('show');
        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }
        }
      });

setTimeout(()=>{
  $("#carregando").removeClass('show');
}, 20000);

  } /// END ELSE \\\
}

function openGroupAcess()
{
  let gr = $("#gruposAcesso").val();

  if( gr && gr != "Selecione")
  {

    swal({
      title: "ATENÇÃO",
      text: "Deseja intercalar poltrona para esse Grupo de Acesso?",
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        confirmar: {
          text: "Intercalar",
          value: "yes",
        },
        no: {
          text: "Não Intercalar",
          className:'sweet-warning',
          value: "no",
        },
      },
    }).then((value) => {

      let inter = 0;

      switch (value) {
     
        case "yes":
          inter = 1;
          break;
     
        case "no":
          inter = 2;
          break;
      }

      /// Salva no banco a informação  \\\
      if( inter > 0)
      {

        $.ajax({
          url: "/poltronas/saveParamPol",
          method: 'post',
          data : {inter, gr},
          dataType: 'json',
          success:function(ret){
            
            /// Reload busca se tiver Grupo e Linha selecionados \\
            if ( $("#gruposAcesso").val() > 0 &&  $("#lines").val() > 0)
            {
              getInfosCar();
            }
            
          }
        });
        
      }

    });

  } else {
    swal({
      title: "ATENÇÃO",
      text: "Por favor selecione um Grupo de Acesso para edição!",
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }
 
}

function printListPax(tp)
{
  let group = $("#gruposAcesso").val();
  let lines = $("#lines").val();

  let p = "g="+group+"&l="+lines+"&t="+tp

  window.open("/poltronas/print?"+p, '_blank').focus();
}

function selectPaxMap(matric, nomeCompleto, form, url, ajax, tbody)
{
  $("#nome").val(nomeCompleto);
  $("#registro").val(matric);
  $("#modalPaxSelect").modal("hide");
  gerarRelatorioRastreaPax(form, url, ajax, tbody);
}

function abortGetRels(){
  $("#carregando").removeClass('show');
  $('#abortGetRelsBtn').hide();
  getdataDashNewController.abort(); 
  getdataDashNewController = new AbortController();
}

function iniDownRels(relName = false){

  $('.btnExcel').prop('disabled', true);
  $('.btnExcel').attr('title', 'Carregando...');
  $('.btnExcel').html('<i class="fas fa-spinner fa-spin"></i>');

  $('body').append(`<div class="fazendoDownload alert alert-warning alert-dismissible fade" style="position: fixed; bottom: .5em; left: 50%; transform: translateX(-50%); z-index:9999;" role="alert">
    <strong>Fazendo download...</strong>
  </div>`);

  setTimeout(() => {
    $('.fazendoDownload').addClass('show');
  }, 200); 

  if(relName){
    $("#carregando").addClass('show');

    setTimeout(() => {
      $('#abortGetExcel').fadeIn();
    }, 100);

    timingRels();

    let checkExcel = setInterval(() => {

      let isReady = getCookie(relName);
      if(isReady && isReady == 'ready'){
        clearInterval(checkExcel);
        document.cookie = relName+'=; Path=/; Max-Age=-99999999;';
        endDownRels(true, true);
        timingRels(true, '#6aff2e');
      }
      
    }, checkExcelTime);
  }
 
}

function endDownRels(showEnd = true, msg = false){
  
  $('.fazendoDownload').alert('close');

  $("#carregando").removeClass('show');
  $('#abortGetExcel').hide();

  if(showEnd){
    $('body').append(`<div class="downloadConcluido alert alert-success alert-dismissible fade" style="position: fixed; bottom: .5em; left: 50%; transform: translateX(-50%); z-index:9999;" role="alert">
      <strong>Download Concluído!</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
      </button>
    </div>`);
  }

  
  if(msg){
    notifyReady(false);
  }
  
  setTimeout(() => {
    $('.downloadConcluido').addClass('show');
    $('.btnExcel').prop('disabled', false);
    $('.btnExcel').attr('title', 'Baixar Excel');
    $('.btnExcel').html('<i class="fas fa-file-excel" style="font-size:22px;color:white"></i>');
  }, 200);
  setTimeout(() => {
    $('.downloadConcluido').alert('close');
  }, 2000);
}

function abortGetExcel(){
  $("#getExcel").remove();
  endDownRels(false);
  timingRels(true, 'yellow');
}

async function gerarRelatorioListagemPax(form, url, ajax = 0, atualiza = false)
{

  if(!atualiza) {
    clearInterval(atualizarGerarRelatorioListagemPax);
    atualizarGerarRelatorioListagemPax = null;
  }

  let nome      = $("#nome").val();
  let matricula = $("#matricula").val();
  let codigo    = $("#codigo").val();
  let situacao  = $("#situacao").val();
  let autocad   = $("#autocad").val();
  let error     = "";

  if($('input[name="grupo[]"]:checked').length == 0){
    error += " Selecione pelo menos 1 Grupo.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  var gr1 = []; 
  $('input[name="grupo[]"]:checked').each(function() {
    gr1.push($(this).val());
  });

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  if(ajax == 0) {

    $("#getExcel").remove();
    const src = `${url}?nome=${nome}&matricula=${matricula}&codigo=${codigo}&situacao=${situacao}&autocad=${autocad}&grupo=${gr1.join(', ')}&lns=${lns.join(', ')}`;
    $('body').append(`<iframe style="display:none" src="${src}" name="getExcel" id="getExcel"></iframe>`);
    iniDownRels('excelListagem');

  } else {

    $('.btnExcel').hide();
    $('.filterRelResultContainer').removeClass('show');
    
    $("#carregando").addClass('show');
    setTimeout(() => {
      $('#abortGetRelsBtn').fadeIn();
    }, 100);

    timingRels();
    
    $("#tbodyListagem").html('');
    $('#relListagem .customScroll, #relListagem .tBodyScroll').removeClass('show');

    const { signal } = getdataDashNewController;

    const data = {
      "nome": nome,
      "matricula": matricula,
      "codigo": codigo,
      "situacao": situacao,
      "autocad": autocad,
      "grupo": gr1.join(', '),
      "lns": lns.join(', ')
    };
    
    const settings = {
        signal,
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    };

    await fetch(url, settings)
    .then( resposta => {
    return resposta.json();
    })
    .then ( ret => {
        
        if(ret.html){
          
          $("#tbodyListagem").html(ret.html);
          $("#totalListagem").html(`Total: ${ret.totalListagem}`);

          setTimeout( () => {
            hasCustonScroll($('.customScroll'));
            checkShowDownload('tbodyListagem');
          },500);

          applyThWidth();

          if(atualizarGerarRelatorioListagemPax === null && ajax == 1 && !atualiza){
            atualizarGerarRelatorioListagemPax = setInterval(()=>{
              gerarRelatorioListagemPax('#gerarRelat', '/relatorioListagem/resultado', 1, true);
            }, timeAtualiza);
          }

          timingRels(true, '#6aff2e');
        
        }else {

          $('.btnExcel').hide();
          $('.filterRelResultContainer').removeClass('show');
              
          if(ret.error != undefined){
              swal({
              title: "ATENÇÃO",
              text: ret.error,
              icon: "warning",
              button: "Fechar",
              });
              $("#carregando").removeClass('show');
              $('#abortGetRelsBtn').hide();
          } else {
              swal({
              title: "ATENÇÃO",
              text: "Nenhum resultado encontrado para os filtros usados!",
              icon: "warning",
              button: "Fechar",
              });
              $("#carregando").removeClass('show');
              $('#abortGetRelsBtn').hide();
          }

          timingRels(true, 'yellow');

          clearInterval(atualizarGerarRelatorioListagemPax);
          atualizarGerarRelatorioListagemPax = null;
          
          return false;
        }
        $("#carregando").removeClass('show');
        $('#abortGetRelsBtn').hide();

        closeFilters();

    }).catch((err) => {
      
      $('.btnExcel').hide();
      $('.filterRelResultContainer').removeClass('show');
      timingRels(true, 'yellow');
      $('#abortGetRelsBtn').hide();
      if(err.message.includes('is not valid JSON')){
          swal({
          title: 'Sessão Expirada',
          text: 'Por favor clique no botão a baixo e faça o login novamente',
          icon: 'warning',
          dangerMode: true,
          buttons: {
              confirm: "Fazer Login"
          },
          }).then(() => {
          window.location.href = "/login";
          });
      }
    });

  } /// END ELSE \\\
}

async function gerarRelatorioCartao(atualiza = false)
{

  if(!atualiza) {
    clearInterval(atualizarGerarRelatorioCartao);
    atualizarGerarRelatorioCartao = null;
  }

  let qtdDias   = $("#qtdDias").val();
  let error     = "";

  if($("input[name='grupo[]']:checked").length == 0){
    error += " Selecione pelo menos 1 Grupo.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  const url = '/relatorioCartaoUtilizacao/resultado';

  var gr1 = []; 
  $('input[name="grupo[]"]:checked').each(function() {
    gr1.push($(this).val());
  });

  $('.btnExcel').hide();
  $('.filterRelResultContainer').removeClass('show');

  $("#carregando").addClass('show');
  setTimeout(() => {
    $('#abortGetRelsBtn').fadeIn();
  }, 100);

  timingRels();
  
  $("#tbodyListagem").html('');
  $('#relCartaoUtiliza .customScroll, #relCartaoUtiliza .tBodyScroll').removeClass('show');

  if(('#chartCartaoUtiliza').length && $("#cFret").length == 0){
    $('#chartCartaoUtiliza').addClass('carregando');
    $('.chartDia').remove();
  }

  const { signal } = getdataDashNewController;

  const data = {
    "qtdDias": qtdDias,
    "grupo": gr1.join(', ')
  };
  
  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
      if(ret.html){
        
        $("#tbodyListagem").html(ret.html);

        setTimeout( () => {
          hasCustonScroll($('.customScroll'));
          checkShowDownload('tbodyListagem');
        },500);

        if(('#chartCartaoUtiliza').length && $("#cFret").length == 0){
          setTimeout(()=>{
            getDataDashCartoesSemUtil();
          }, 1000);
        }

        applyThWidth();

        if(atualizarGerarRelatorioCartao === null && ajax == 1 && !atualiza){
          atualizarGerarRelatorioCartao = setInterval(()=>{
            gerarRelatorioCartao(true);
          }, timeAtualiza);
        }

        timingRels(true, '#6aff2e');
      
      }else {

        $('.btnExcel').hide();

        $('.filterRelResultContainer').removeClass('show');
            
        if(ret.error != undefined){
            swal({
            title: "ATENÇÃO",
            text: ret.error,
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        } else {
            swal({
            title: "ATENÇÃO",
            text: "Nenhum resultado encontrado para os filtros usados!",
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        }

        if(('#chartCartaoUtiliza').length){
          $('#chartCartaoUtiliza').removeClass('carregando');
        }

        timingRels(true, 'yellow');

        clearInterval(atualizarGerarRelatorioCartao);
        atualizarGerarRelatorioCartao = null;
        
        return false;
      }
      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

  }).catch((err) => {
    $('.btnExcel').hide();
    $('.filterRelResultContainer').removeClass('show');
    timingRels(true, 'yellow');
    $('#abortGetRelsBtn').hide();
    if(err.message.includes('is not valid JSON')){
        swal({
        title: 'Sessão Expirada',
        text: 'Por favor clique no botão a baixo e faça o login novamente',
        icon: 'warning',
        dangerMode: true,
        buttons: {
            confirm: "Fazer Login"
        },
        }).then(() => {
        window.location.href = "/login";
        });
    }
  });
}


function abortGetViagem(){
  document.cookie = 'PHPTRIPVIEW=; Path=/; Max-Age=-99999999;';
  $('.iframeViagem').removeClass('open');
  $('#iframeViagem').remove();
  $("#carregando").removeClass('show');
  $('#abortGetViagem').hide();
  $('body').css('overflow','auto');
  timingRels(true, 'yellow');

  const isSintetico = $('#relSintetico').length ? true : false;
  const isConsolidado = $('#relConsolidado').length ? true : false;

  if(isSintetico && atualizarGerarRelatorioSintetico === null){
    atualizarGerarRelatorioSintetico = setInterval(()=>{
      gerarRelatorioSintetico(true);
    }, timeAtualiza);
  }

  if(isConsolidado && atualizarRelConsolidado === null){
    atualizarRelConsolidado = setInterval(()=>{
      gerarRelatorioConsolidado(true);
    }, timeAtualiza);
  }
}

async function digestMessage(message) {
  const msgUint8 = new TextEncoder().encode(message); // encode as (utf-8) Uint8Array
  const hashBuffer = await crypto.subtle.digest("SHA-256", msgUint8); // hash the message
  const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
  const hashHex = hashArray
    .map((b) => b.toString(16).padStart(2, "0"))
    .join(""); // convert bytes to hex string
  return hashHex;
}

async function verViagem(viagem, data_inicio, data_fim){

  const hash = await digestMessage(viagem);
  
  document.cookie = `PHPTRIPVIEW=${hash}; Path=/`;

  const vendoAgenda = $('#vendoAgenda').val();
  const errorViagem = $('#errorViagem').val();

  let avid = '0';
  let tagAgenda = '';

  if(vendoAgenda != ""){
    const downloadName = $('#downloadName').val().replace('Relatório ','');
    tagAgenda = `${vendoAgenda} (${downloadName})`;
  }
  
  const isSintetico = $('#relSintetico').length ? true : false;
  const isConsolidado = $('#relConsolidado').length ? true : false;
  const notify = ($("#notifyReady").length && $("#notifyReady").is(':checked')) ? 1 : 0;

  if(isConsolidado){
    clearInterval(atualizarRelConsolidado);
    atualizarRelConsolidado = null;

    if(vendoAgenda != "" && errorViagem == 0){
      avid = `consolidado-${vendoAgenda}`;
    }

  }

  if(isSintetico && errorViagem == 0){
      clearInterval(atualizarGerarRelatorioSintetico);
      atualizarGerarRelatorioSintetico = null;

      if(vendoAgenda != ""){
        avid = `sintetico-${vendoAgenda}`;
      }

  }

  const src = `/relatorioAnalitico/viagem?v=${viagem}&avid=${avid}&dti=${data_inicio}&dtf=${data_fim}&notify=${notify}&tagAgenda=${tagAgenda}`;
  
  timingRels();

  $("#carregando").addClass('show');

  if(avid == 0){
    setTimeout(() => {
      $('#abortGetViagem').fadeIn();
    }, 100);
  }  

  let iframe = document.createElement("iframe");
  iframe.setAttribute('id', 'iframeViagem');
  iframe.onload = function (){ 
    $('body').css('overflow','hidden');
    $('.iframeViagem').addClass('open');
    $("#carregando").removeClass('show');
    $('#abortGetViagem').hide();
    timingRels(true, '#6aff2e');

    if(notify == 1){
      notifyReady(true, `Relatório Embarcados Viagem está pronto!`);
    }
    
  };
  
  iframe.src = src;

  $('.iframeViagem').append(iframe);

}

async function gerarRelatorioAnalitics(atualiza = false, agenda = 0)
{

  if(agenda != 0 && (agenda == $('#vendoAgenda').val())){
    closeFilters();
    return;
  }

  $('#vendoAgenda').val('');

  const url = '/relatorioAnalitico/resultado';

  let viagemID = $("#viagemID").val();
  viagemID = !viagemID ? '' : viagemID;

  let matricula = viagemID == '' ? $("#matricula").val() : '';

  if(!atualiza) {
    clearInterval(atualizarRelAnalitico);
    atualizarRelAnalitico = null;
  }

  if(agenda == 0){
    let error = "";
    if($("#data_inicio").val() == ""){
      error += " Preencha a Data Início.\n";
    }

    if( $("#data_fim").val() == ""){
      error += " Preencha a Data Fim.\n";
    }

    if(viagemID == '' && $("input[name='grupo[]']:checked").length == 0){

      if(matricula == ''){
        error += " Selecione pelo menos 1 Grupo.\n";
      }
      
    }

    if(error != ""){
      swal({
        title: "ATENÇÃO",
        text: "Por favor preencher os filtros: \n" + error,
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }
  }
  
  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let previsto    = $("#previsto").val();
  
  var val1 = []; 
  $('select[name="veiculos[]"] option:selected').each(function() {
    val1.push($(this).val());
  });

  var gr1 = []; 
  $('input[name="grupo[]"]:checked').each(function() {
    gr1.push($(this).val());
  });

  let lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  let todosGrupos = $('#todosGrupos').is(':checked') ? 1 : 0;

  $('.btnExcel').hide();

  $('.filterRelResultContainer').removeClass('show');

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "grupo": gr1.join(', '),
    "todosGrupos": todosGrupos,
    "veiculos": "",
    "matricula":matricula,
    "previsto":previsto,
    "viagemID":viagemID,
    "lns":lns.join(', '),
    "agenda": agenda
  };

  $("#carregando").addClass('show');

  if(agenda == 0){
    setTimeout(() => {
      $('#abortGetRelsBtn').fadeIn();
    }, 100);
  }
  
  timingRels();
  
  $("#bodyTable").html('');
  $('#relAnalitico .customScroll, #relAnalitico .tBodyScroll').removeClass('show');
  $('.agendamentoTitle').remove();

  const { signal } = getdataDashNewController;
  
  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      if(ret.html){
      
      $("#bodyTable").html(ret.html);

      setTimeout( () => {
        hasCustonScroll($('.customScroll'));
        checkShowDownload('bodyTable');
        if(agenda != 0){
          $('.btnExcel').before(`<p class="agendamentoTitle badge badge-primary p-2 m-1">Agendamento # ${agenda}</p>`);
          $('#vendoAgenda').val(agenda);
        }
      },500);

      applyThWidth();
      
      if(atualizarRelAnalitico === null && !atualiza && agenda == 0){
        atualizarRelAnalitico = setInterval(()=>{
          gerarRelatorioAnalitics(true);
        }, timeAtualiza);
      }

      if(agenda == 0){
        notifyReady();
      }

      timingRels(true, '#6aff2e');
      
      }else {

        $('.btnExcel').hide();

        $('.filterRelResultContainer').removeClass('show');
            
        if(ret.error != undefined){
            swal({
            title: "ATENÇÃO",
            text: ret.error,
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        } else {
            swal({
            title: "ATENÇÃO",
            text: agenda == 0 ? "Nenhum resultado encontrado para os filtros usados!" : "Agendamento não encontrou resultados!",
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        }

        timingRels(true, 'yellow');

        clearInterval(atualizarRelAnalitico);
        atualizarRelAnalitico = null;
        
        return false;
      }
      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

  }).catch((err) => {
    $('.btnExcel').hide();
    $('.filterRelResultContainer').removeClass('show');
    timingRels(true, 'yellow');
    $('#abortGetRelsBtn').hide();
    if(err.message.includes('is not valid JSON')){
        swal({
        title: 'Sessão Expirada',
        text: 'Por favor clique no botão a baixo e faça o login novamente',
        icon: 'warning',
        dangerMode: true,
        buttons: {
            confirm: "Fazer Login"
        },
        }).then(() => {
        window.location.href = "/login";
        });
    }
  });
}

async function gerarRelatorioConsolidado(atualiza = false, agenda = 0)
{

  if(agenda != 0 && (agenda == $('#vendoAgenda').val())){
    closeFilters();
    return;
  }
  
  $('#vendoAgenda').val('');

  const url = '/relatorioConsolidado/resultado';

  if(!atualiza) {
    clearInterval(atualizarRelConsolidado);
    atualizarRelConsolidado = null;
  }

  if(agenda == 0){

    let error = "";
    if($("#data_inicio").val() == ""){
      error += " Preencha a Data Início.\n";
    }

    if( $("#data_fim").val() == ""){
      error += " Preencha a Data Fim.\n";
    }

    if(error != ""){
      swal({
        title: "ATENÇÃO",
        text: "Por favor preencher os filtros: \n" + error,
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }

  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let sentido     = $("#sentido").val();
  let pontual     = $("#pontual").val();

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }
    
  $("#carregando").addClass('show');
  $('.btnExcel').hide();
  $('.filterRelResultContainer').removeClass('show');
  
  if(agenda == 0){
    setTimeout(() => {
      $('#abortGetRelsBtn').fadeIn();
    }, 100);
  }

  timingRels();
    
  $("#bodyConsolidado").html(''); 
  $('#relConsolidado .customScroll, #relConsolidado .tBodyScroll').removeClass('show');
  $('.agendamentoTitle').remove();

  const { signal } = getdataDashNewController;

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "lns": lns.join(', '),
    "sentido": sentido,
    "pontual": pontual,
    "agenda": agenda
  };
    
  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
      if(ret.html){

        $("#bodyConsolidado").append(ret.html);
        
        setTimeout( () => {
          hasCustonScroll($('.customScroll'));
          checkShowDownload('bodyConsolidado');
          if(agenda != 0){
            $('.btnExcel').before(`<p class="agendamentoTitle badge badge-primary p-2 m-1">Agendamento # ${agenda}</p>`);
            $('#vendoAgenda').val(agenda);
            $('#errorViagem').val(ret.errorViagem ? ret.errorViagem : 0);
          }
        },500);

        applyThWidth();
        
        let veicCap = ret.capacUso;
        $("#limit").val(veicCap.limits);
        $("#embarcado").val(veicCap.embarcados);

        let dataIni = ret.data_inicio ?? false;
        let dataFim = ret.data_fim ?? false;
        drawNewChartDonut(false, '#donutOcupaLinhas', dataIni, dataFim);

        if(atualizarRelConsolidado === null && !atualiza && agenda == 0){
          atualizarRelConsolidado = setInterval(()=>{
            gerarRelatorioConsolidado(true);
          }, timeAtualiza);
        }

        if(agenda == 0){
          notifyReady();
        }

        timingRels(true, '#6aff2e');
      
      }else {

        $('.btnExcel').hide();

        $('.filterRelResultContainer').removeClass('show');
          
        if(ret.error != undefined){
            swal({
            title: "ATENÇÃO",
            text: ret.error,
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        } else {
            swal({
            title: "ATENÇÃO",
            text: agenda == 0 ? "Nenhum resultado encontrado para os filtros usados!" : "Agendamento não encontrou resultados!",
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        }

        clearInterval(atualizarRelConsolidado);
        atualizarRelConsolidado = null;
        
        timingRels(true, 'yellow');
        
        return false;
      }
      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

    }).catch((err) => {
        $('.btnExcel').hide();
        $('.filterRelResultContainer').removeClass('show');
        timingRels(true, 'yellow');
        $('#abortGetRelsBtn').hide();
        if(err.message.includes('is not valid JSON')){
            swal({
            title: 'Sessão Expirada',
            text: 'Por favor clique no botão a baixo e faça o login novamente',
            icon: 'warning',
            dangerMode: true,
            buttons: {
                confirm: "Fazer Login"
            },
            }).then(() => {
            window.location.href = "/login";
            });
        }
    });
}

async function gerarRelatorioSintetico(atualiza = false, agenda = 0)
{

  if(agenda != 0 && (agenda == $('#vendoAgenda').val())){
    closeFilters();
    return;
  }
  
  $('#vendoAgenda').val('');

  const url = '/relatorioSintetico/resultado';

  if(!atualiza) {
    clearInterval(atualizarGerarRelatorioSintetico);
    atualizarGerarRelatorioSintetico = null;
  }

  if(agenda == 0){

    let error = "";
    if($("#data_inicio").val() == "")
      error += " Preencha a Data Início.\n";
    
    if( $("#data_fim").val() == "")
      error += " Preencha a Data Fim.\n";

    if(error != ""){
      swal({
        title: "ATENÇÃO",
        text: "Por favor preencher os filtros: \n" + error,
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }

  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let pontual     = $("#pontual").val();

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }
    
  $("#carregando").addClass('show');
  $('.btnExcel').hide();
  $('.filterRelResultContainer').removeClass('show');

  removeNewChartDonut('#donutOcupaLinhas');

  if(agenda == 0){
    setTimeout(() => {
      $('#abortGetRelsBtn').fadeIn();
    }, 100);
  }

  timingRels();
  
  $("#bodySintetico").html(''); 
  $('#relSintetico .customScroll, #relSintetico .tBodyScroll').removeClass('show');
  $('.agendamentoTitle').remove();

  const { signal } = getdataDashNewController;

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "lns": lns.join(', '),
    "pontual": pontual,
    "agenda": agenda
  };

  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
      if(ret.html){
        
        $("#bodySintetico").append(ret.html);

        setTimeout( () => {
          hasCustonScroll($('.customScroll'));
          checkShowDownload('bodySintetico');
          if(agenda != 0){
            $('.btnExcel').before(`<p class="agendamentoTitle badge badge-primary p-2 m-1">Agendamento # ${agenda}</p>`);
            $('#vendoAgenda').val(agenda);
            $('#errorViagem').val(ret.errorViagem ? ret.errorViagem : 0);
          }
        },500);
        
        applyThWidth();

        let veicCap = ret.capacUso;
        $("#limit").val(veicCap.limits);
        $("#embarcado").val(veicCap.embarcados);

        let dataIni = ret.data_inicio ?? false;
        let dataFim = ret.data_fim ?? false;
        drawNewChartDonut(false, '#donutOcupaLinhas', dataIni, dataFim);

        if(atualizarGerarRelatorioSintetico === null && !atualiza && agenda == 0){
          atualizarGerarRelatorioSintetico = setInterval(()=>{
            gerarRelatorioSintetico(true);
          }, timeAtualiza);
        }

        if(agenda == 0){
          notifyReady();
        }
        
        timingRels(true, '#6aff2e');
      
      }else {

          $('.btnExcel').hide();

          $('.filterRelResultContainer').removeClass('show');
            
          if(ret.error != undefined){
              swal({
              title: "ATENÇÃO",
              text: ret.error,
              icon: "warning",
              button: "Fechar",
              });
              $("#carregando").removeClass('show');
              $('#abortGetRelsBtn').hide();
          } else {
              swal({
              title: "ATENÇÃO",
              text: agenda == 0 ? "Nenhum resultado encontrado para os filtros usados!" : "Agendamento não encontrou resultados!",
              icon: "warning",
              button: "Fechar",
              });
              $("#carregando").removeClass('show');
              $('#abortGetRelsBtn').hide();
          }

          clearInterval(atualizarGerarRelatorioSintetico);
          atualizarGerarRelatorioSintetico = null;

          timingRels(true, 'yellow');
          
          return false;
      }
      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

    }).catch((err) => {
        $('.btnExcel').hide();
        $('.filterRelResultContainer').removeClass('show');
        timingRels(true, 'yellow');
        $('#abortGetRelsBtn').hide();
        if(err.message.includes('is not valid JSON')){
            swal({
            title: 'Sessão Expirada',
            text: 'Por favor clique no botão a baixo e faça o login novamente',
            icon: 'warning',
            dangerMode: true,
            buttons: {
                confirm: "Fazer Login"
            },
            }).then(() => {
            window.location.href = "/login";
            });
        }
    });

}

async function gerarRelatorioEmbSemCartao(atualiza = false)
{

  const url = '/embarqueSemCartao/resultado';

  if(!atualiza) {
    clearInterval(atualizarRelatorioEmbSemCartao);
    atualizarRelatorioEmbSemCartao = null;
  }

  let error = "";
  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if($("input[name='grupo[]']:checked").length == 0){
    error += " Selecione pelo menos 1 Grupo.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }
  
  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let matricula   = $("#matricula").val();
  
  var gr1 = []; 
  $('input[name="grupo[]"]:checked').each(function() {
    gr1.push($(this).val());
  });

  let lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  $('.btnExcel').hide();
  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "grupo": gr1.join(', '),
    "matricula":matricula,
    "lns":lns.join(', ')
  };

  $("#carregando").addClass('show');

  setTimeout(() => {
    $('#abortGetRelsBtn').fadeIn();
  }, 100);
  
  timingRels();
  
  $("#bodyTable").html('');
  $('#relEmbSemRfid .customScroll, #relEmbSemRfid .tBodyScroll').removeClass('show');
  $('.agendamentoTitle').remove();

  const { signal } = getdataDashNewController;
  
  const settings = {
      signal,
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
  };

  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      if(ret.html){
      
      $("#bodyTable").html(ret.html);

      setTimeout( () => {
        hasCustonScroll($('.customScroll'));
        checkShowDownload('bodyTable');
      },500);

      applyThWidth();
      
      if(atualizarRelatorioEmbSemCartao === null && !atualiza){
        atualizarRelatorioEmbSemCartao = setInterval(()=>{
          gerarRelatorioEmbSemCartao(true);
        }, timeAtualiza);
      }

      notifyReady();

      timingRels(true, '#6aff2e');
      
      }else {

        $('.btnExcel').hide();
            
        if(ret.error != undefined){
            swal({
            title: "ATENÇÃO",
            text: ret.error,
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        } else {
            swal({
            title: "ATENÇÃO",
            text: "Nenhum resultado encontrado para os filtros usados!",
            icon: "warning",
            button: "Fechar",
            });
            $("#carregando").removeClass('show');
            $('#abortGetRelsBtn').hide();
        }

        timingRels(true, 'yellow');

        clearInterval(atualizarRelatorioEmbSemCartao);
        atualizarRelatorioEmbSemCartao = null;
        
        return false;
      }
      $("#carregando").removeClass('show');
      $('#abortGetRelsBtn').hide();

      closeFilters();

  }).catch((err) => {
    $('.btnExcel').hide();
    timingRels(true, 'yellow');
    $('#abortGetRelsBtn').hide();
    if(err.message.includes('is not valid JSON')){
        swal({
        title: 'Sessão Expirada',
        text: 'Por favor clique no botão a baixo e faça o login novamente',
        icon: 'warning',
        dangerMode: true,
        buttons: {
            confirm: "Fazer Login"
        },
        }).then(() => {
        window.location.href = "/login";
        });
    }
  });
}

function getDadosDash(form, url, ajax = 0)
{
  let error = "";
  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if(
    $("#data_inicio").val() != "" && 
    $("#data_fim").val() != "" && 
    getDiffDates($("#data_fim").val(), $("#data_inicio").val()) > relDays
    ){
      swal({
        title: "ATENÇÃO",
        text: relDaysMsg,
        icon: "warning",
        button: "Fechar",
      });
    return false;
  }

let grupo     = $("#grupo").val();
let grupoName = $('#grupo').find(":selected").text();

$.ajax({
  url: url,
  method: 'post',
  data : {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "grupo": grupo
  },
  success:function(data){
    let cartao    = data.retorno; 
    let htmlBody  = "";

    if(data.total > 0){


    } else {

      if(data.error != undefined){
        swal({
          title: "ATENÇÃO",
          text: data.error,
          icon: "warning",
          button: "Fechar",
        });
      } else {
        swal({
          title: "ATENÇÃO",
          text: "Ocorreu um erro ao tentar conectar ao Banco de Dados, tente novamente!",
          icon: "warning",
          button: "Fechar",
        });
        $("#carregando").removeClass('show');
      }
      
      return false;
    }
    
    $("#carregando").removeClass('show');
  }
});

setTimeout(()=>{
  $("#carregando").removeClass('show');
}, 10000);
}

function trataDataStartJorn(created, start_work=null)
{

	let dtHr = created.split(" ");
	let dt 	 = dtHr[0].split("-");
	let hrDt = " - ";

	if(start_work!=null)
		hrDt = dt[2] +"/"+ dt[1] +"/"+ dt[0] +" "+ start_work;
	else
		hrDt = dt[2] +"/"+ dt[1] +"/"+ dt[0] +" "+ dtHr[1];

	return hrDt;
}

function getHeight(height)
{
	var he = 400;

	if(height){

		if(height > 15 && height < 30){
			he = 800;
		} else {
			he = 1200;
		}

	}

	return he;
}

function openNav() 
{
  document.getElementById("mySidenav").style.display = "block";
  //document.getElementById("app").style.marginLeft = "250px";
  $("#openNav").hide();
}

function closeNav() 
{
  if(document.getElementById("mySidenav"))
  {
    document.getElementById("mySidenav").style.display = "none";
    //document.getElementById("app").style.marginLeft = "0";
    $("#openNav").show();
  }

}

function confirmDelet(type, name = false, url, id)
{

  const txt = name ? `Deseja realmente deletar ${name}?` : 'Deseja realmente deletar esse registro?'; 
	
  swal({
    title: 'Deletar '+type,
    text: txt,
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancelar: {
        text: "Cancelar",
        className:'btn-success',
        value: "cancelar",
      },
      deletar: {
        text: "Deletar",
        className:'btn-danger',
        value: "deletar",
      }
    },
  }).then((value) => {

    if(value == 'deletar'){
      
      const itensLeft = $(`tr#${id}`).parent('tbody').find('tr').length;

      $("#carregando").addClass('show');
      $.ajax({
        url: url,
        method: 'get',
        data: {id:id},
        dataType: 'json',
        success:function(ret){
    
          swal({
            title: ret.title,
            text: ret.text,
            icon: ret.icon,
            button: ret.button,
          });
    
          if (ret.status){
            $(`tr#${id}`).remove();
            if((itensLeft - 1) == 0){
              $('.btnExcel').hide();
              $('.filterRelResultContainer').removeClass('show');
            }
          }
    
          $("#carregando").removeClass('show');
                   
        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }
          else{
            swal({
              title: "ERRO",
              text: "Ocorreu um erro ao deletar, tente novamente!",
              icon: "error",
              button: "Fechar",
            });
          }
    
          $("#carregando").removeClass('show');
        }
      });
      
    }
    
  });
}

function openModalEfeito(modal, id)
{
  $(modal +  id).modal('show');
}

function closeAnimation(modal, id)
{
  $(modal +  id).removeClass('fadeInBottom');
  $(modal +  id).addClass('fadeOutBottom');

  setTimeout(()=>{
    $(modal +  id).modal('hide');
  }, 1000);
}


function sortUserPerms(select, attr){

  let options = $(`${select} option`);
    
    options.sort(function(a,b) {
        if ($(a).attr(`${attr}`) > $(b).attr(`${attr}`)) return 1;
        if ($(a).attr(`${attr}`) < $(b).attr(`${attr}`)) return -1;
        return 0
    });
    
    $(`${select}`).empty().append( options );

}

function concederPermissao(tipo)
{

  if(tipo == "CAR"){

    let idsCars = $("#idsCardPermission").val();

    if(idsCars != "")
      idsCars = idsCars.split(',');
    else 
      idsCars = [];

    $("#selectCarUser option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let nome = $(this).attr('nome');

      $("#selectCarUserPerm").append(`<option nome="${nome}" value="${item}">${text}</option>`);

      if(!idsCars.indexOf(item) !== -1) {
        idsCars.push(item);
      } 

      $(this).remove();

    });

    sortUserPerms('#selectCarUserPerm', 'nome');

    idsCars = idsCars.join(',');
    $("#idsCardPermission").val(idsCars);

  }

  if(tipo == "LIN"){

    let idsLinhas = $("#idsLinhaPermission").val();

    if(idsLinhas != "")
      idsLinhas = idsLinhas.split(',');
    else 
      idsLinhas = [];


    
    $("#selectLinhaUser option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let grlinhaid = $(this).attr('grlinhaid');
      let nome = $(this).attr('nome');
      

      const temNome = $(`#selectLinhaUserPerm .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).length;
      if(temNome == 0){
        const nomeGrupo = $(`#selectLinhaUser .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).html();
        $("#selectLinhaUserPerm").append(`<option disabled="" class="nomeGrupoSelect" grlinhaidnome="${grlinhaid}">${nomeGrupo}</option>`)
      }

      $("#selectLinhaUserPerm").append(`<option nome="${nome}" value="${item}" grlinhaid="${grlinhaid}">${text}</option>`);

      if(!idsLinhas.indexOf(item) !== -1) {
        idsLinhas.push(item);
      } 

      $(this).remove();
      let totalGrupo = $(`#selectLinhaUser option[grlinhaid=${grlinhaid}]`).length;
      if(totalGrupo == 0){
        $(`#selectLinhaUser .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).fadeOut();
      }

    });

    sortUserPerms('#selectLinhaUserPerm', 'nome');

    idsLinhas = idsLinhas.join(',');
    $("#idsLinhaPermission").val(idsLinhas);

  }

  if(tipo == "GRUPO"){

    let idsGrupos = $("#idsGrupoPermission").val();

    if(idsGrupos != "")
      idsGrupos = idsGrupos.split(',');
    else 
      idsGrupos = [];

    $("#selectGrupoUser option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let nome = $(this).attr('nome');

      $("#selectGrupoUserPerm").append(`<option nome="${nome}" value="${item}">${text}</option>`);
      if(!idsGrupos.indexOf(item) !== -1) {
        idsGrupos.push(item);
      } 
      $(this).remove();

    });

    sortUserPerms('#selectGrupoUserPerm', 'nome');

    idsGrupos = idsGrupos.join(',');
    $("#idsGrupoPermission").val(idsGrupos);

  }
}

function removerPermissao(tipo)
{
  if(tipo == "CAR"){

    let idsCars = $("#idsCardPermission").val();

    if(idsCars != "")
      idsCars = idsCars.split(',');
    else 
      idsCars = [];

    $("#selectCarUserPerm option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let nome = $(this).attr('nome');

      $("#selectCarUser").append(`<option nome="${nome}" value="${item}">${text}</option>`);

      if(idsCars.indexOf(item) !== -1) {
        idsCars.splice(idsCars.indexOf(item), 1);
      } 

      $(this).remove();
    });

    sortUserPerms('#selectCarUser', 'nome');

    idsCars = idsCars.join(',');
    $("#idsCardPermission").val(idsCars);

  }

  if(tipo == "LIN"){

    let idsLinhas = $("#idsLinhaPermission").val();

    if(idsLinhas != "")
      idsLinhas = idsLinhas.split(',');
    else 
      idsLinhas = [];

    $("#selectLinhaUserPerm option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let grlinhaid = $(this).attr('grlinhaid');
      let nome = $(this).attr('nome');

      $(`#selectLinhaUser .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).after(`<option value="${item}" nome="${nome}" grlinhaid="${grlinhaid}">${text}</option>`);
      $(`#selectLinhaUser .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).fadeIn();
      if(idsLinhas.indexOf(item) !== -1) {
        idsLinhas.splice(idsLinhas.indexOf(item), 1);
      } 

      $(this).remove();

      let totalGrupo = $(`#selectLinhaUserPerm option[grlinhaid=${grlinhaid}]`).length;
      if(totalGrupo == 0){
        $(`#selectLinhaUserPerm .nomeGrupoSelect[grlinhaidnome=${grlinhaid}]`).remove();
      }
    });

    sortUserPerms('#selectLinhaUser', 'nome');

    idsLinhas = idsLinhas.join(',');
    $("#idsLinhaPermission").val(idsLinhas);
    selectUserLinhaFilter();

  }

  if(tipo == "GRUPO"){

    let idsGrupos = $("#idsGrupoPermission").val();

    if(idsGrupos != "")
      idsGrupos = idsGrupos.split(',');
    else 
      idsGrupos = [];

    $("#selectGrupoUserPerm option:selected").each(function() {
      let item = $(this).val();
      let text = $(this).text();
      let nome = $(this).attr('nome');

      $("#selectGrupoUser").append(`<option nome="${nome}" value="${item}">${text}</option>`);

      if(idsGrupos.indexOf(item) !== -1) {
        idsGrupos.splice(idsGrupos.indexOf(item), 1);
      } 

      $(this).remove();
    });

    sortUserPerms('#selectGrupoUser', 'nome');

    idsGrupos = idsGrupos.join(',');
    $("#idsGrupoPermission").val(idsGrupos);

  }
}

function getDiffDates(t1, t2)
{
  t1 = new Date(t1);
  t2 = new Date(t2);
  var diffMS = t1 - t2;  
  var diffS = diffMS / 1000;  
  var diffM = diffS / 60;
  var diffH = diffM / 60;
  var diffD = diffH / 24;

  return diffD;
}

function getItinetariosGer(codIntegra, ind)
{

  if ( codIntegra == null || codIntegra == undefined ||  ind == null || ind == undefined)
  {

    swal({
      title: "ATENÇÃO",
      text: "Ocorreu um erro ao buscar os pontos do Itinerário, favor tentar novamente!",
      icon: "warning",
      button: "Fechar",
    });

    return false;

  } else {

    document.getElementById('bodyItiMaps-' + ind).innerHTML = "";

    $.ajax({
      url: "/passageiro/mapsUserItinerario",
      method: 'post',
      data : { codIntegra },
      dataType: 'json',
      success:function(ret){
       
        if(ret.html && ret.html.length){
          console.log(ret.htm);
          let dados = ret.html;
          html = dados.length > 0 ? dados[0].TRAJETO : "";
  
          $("#carregaSpinner-" + ind).hide();

          document.getElementById('bodyItiMaps-' + ind).innerHTML = html;
  
        } else {
  
          // swal({
          //   title: "ATENÇÃO",
          //   text: "Nenhuma resultado encontrado!",
          //   icon: "warning",
          //   button: "Fechar",
          // });
  
          $("#carregaSpinner-" + ind).hide();
        }
  
      }
    });


  }

}

function incluirNovaLinhaIda()
{

  const id = `${Math.floor(Math.random() * 100)}${Date.now()}`;
  let selec = '<select id="novaLinhaIda-'+id+'" class="form-control" name="novaLinha[]"></select>';
  let html = "<tr class='btn-primary'><td style='vertical-align: middle; width:90%;'>"+selec+"</td><td style='text-align: center; vertical-align: middle;'><span class='btn-danger p-2' style='cursor:pointer;' onclick='deleteLine(this)'><i class='fas fa-trash-alt' style='font-size:18px;'></i></span></td></tr>";
  
  $("#linesAdicional").append(html);

  $("#linhaIda option").each(function(){
    $(`#novaLinhaIda-${id}`).append(`<option value="${this.value}">${this.text}</option>`);
  });

  setTimeout(() => {
    $(`#novaLinhaIda-${id}`).select2({
      width:'100%',
      "language": {
      "noResults": function(){
          return "Nenhum resultado encontrado";
      }
      },
        escapeMarkup: function (markup) {
            return markup;
        }
    });
  }, 200);
  
}

function incluirNovaLinhaVolta()
{

  const id = `${Math.floor(Math.random() * 100)}${Date.now()}`;
  let selec = '<select id="novaLinhaVolta-'+id+'" class="form-control" name="novaLinha[]"></select>';
  let html = "<tr class='btn-warning'><td style='vertical-align: middle; width:90%;'>"+selec+"</td><td style='text-align: center; vertical-align: middle;'><span class='btn-danger p-2' style='cursor:pointer;' onclick='deleteLine(this)'><i class='fas fa-trash-alt' style='font-size:18px;'></i></span></td></tr>";
  
  $("#linesAdicional").append(html);

  $("#linhaVolta option").each(function(){
    $(`#novaLinhaVolta-${id}`).append(`<option value="${this.value}">${this.text}</option>`);
  });

  setTimeout(() => {
    $(`#novaLinhaVolta-${id}`).select2({
      width:'100%',
      "language": {
      "noResults": function(){
          return "Nenhum resultado encontrado";
      }
      },
        escapeMarkup: function (markup) {
            return markup;
        }
    });
  }, 200);
  
}

function deleteLine(obj)
{
  $(obj).parent().parent().remove();
}

function deleteLineExist(obj, id)
{

  swal({
    title: "ATENÇÃO",
    text: "Deseja deletar essa Linha Adicional?",
    icon: "warning",
    buttons: {
      cancel: "Cancelar",
      confirmar: {
        text: "Confirmar",
        value: "yes",
      },
    },
  }).then((value) => {

    if( value == "yes")
    {

      $("#carregando").addClass('show');

      $.ajax({
        url: "/cadastroPax/deleteLineExist",
        method: 'post',
        data : {id},
        dataType: 'json',
        success:function(ret){

          swal({
            title: "ATENÇÃO",
            text: ret.msg,
            icon: ret.success ? "success" : "warning",
            button: "Fechar",
          });

          if (ret.success)
          {
            $(obj).parent().parent().remove();
          }

          $("#carregando").removeClass('show');

        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }
          else{
            swal({
              title: "ERRO",
              text: "Ocorreu um erro ao remover, tente novamente!",
              icon: "error",
              button: "Fechar",
            });
          }
    
          $("#carregando").removeClass('show');
        }
      });
      
    }

  });

  
}

function openAdicionalLines(id, nome)
{
  $("#carregando").addClass('show');
  $('.nomePaxLinhasAdicionais').html(' - ');
  $.ajax({
    url: "/cadastroPax/getLinesExtras",
    method: 'post',
    data : {id},
    dataType: 'json',
    success:function(ret){

      if (ret.success)
      {
        let retData = ret.data;
        let html = "";

        for(let i=0; i < retData.length; i++)
        {
          html += `<li class=${retData[i].sentido == 1 ? 'btn-warning': 'btn-primary'}>`;
          html += `${retData[i].PREFIXO} - ${retData[i].NOME} - ${retData[i].sentido == 1 ? 'RETORNO' : 'ENTRADA'}`
          html += "</li>";
        }

        if(nome != ''){
          $('.nomePaxLinhasAdicionais').html(nome);
        }

        $('.linhasAdicionais .checkboxesFiltroLista').html(html);
        $('.linhasAdicionais').addClass('show');
        $('body').append('<div class="checkboxesFiltroBackDrop"></div>');

      } else {

        swal({
          title: "ATENÇÃO",
          text: ret.msg,
          icon: "warning",
          button: "Fechar",
        });

      }

      $("#carregando").removeClass('show');

    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao carregar as linhas, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
    
  });
}

/////////////// MOSTRAR E MONTAR O MAPA DE 10 EM 10 \\\\\\\\\\\\\\\
var rotasEncon = "";
var totalBusca = 0;
var lastShowBu = 0;

function getRotasItinerario(all = 0)
{
  waypNew = [];
  let end   = $("#enderecoToken").val();
  let ic    = $("#ic").val();
  let url   = "/rotas/seach";
  rotasEncon = "";
  totalBusca = 0;
  lastShowBu = 0;

  if(end == "" && all == 0){
    swal({
      title: "ATENÇÃO",
      text: "Informe um endereço!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $("#linhasItiner").html('');
  $("#allModais").html('');
  $("#carregando").addClass('show');
  $.ajax({ 
    url: url,
    method: 'post',
    data : {
      "end": end,
      "ic": ic,
      "all": all
    },
    dataType: 'json',
    success:function(ret){
      if(ret.success){

        let html = "";

        if(ret.html && ret.html.length){

          let dados = ret.html;
          totalBusca = ret.cont;
          rotasEncon = ret.html;

          for(let i=0; i < ret.cont; i++) {

            if(i < 10) {
              html += "<tr class='rotasEncontradas' data-toggle='modal' data-target='#maps-"+i+"' onclick='getItinetariosGer(\""+dados[i].CODIGO_INTEGRACAO+"\", "+i+")'>";
              html += "<td>"+dados[i].DESCRICAO+"</td>";
              html += "<td>"+dados[i].LINHA+"</td>";
              html += "</tr>";

              let modal = "";
              let pontos= dados[i].PONTOS;
              ////// Criando Modal \\\\\\
              modal += '<div class="modal fade modalRotas" id="maps-'+i+'" tabindex="-1" role="dialog" aria-labelledby="modalRotas" aria-hidden="true">';
              modal += ' <div class="modal-dialog" role="document" style="min-width: 80%;">';
              modal += '    <div class="modal-content">';
              modal += '      <div class="modal-header">';
              modal += '        <h5 class="modal-title" id="modalRotas">ROTA DA LINHA: '+dados[i].LINHA+'</h5>';
              modal += '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
              modal += '          <span aria-hidden="true">&times;</span>';
              modal += '        </button>';
              modal += '      </div>';
              modal += '      <div class="modal-body"><div id="map-'+i+'" style="width: 100%; height: 530px;"></div></div>';
              modal += '      <table class="table table-striped"><thead><tr style="padding: 0 20px;"><th scope="col" style="color: white;font-size: 20px;background-color: #062323;">ITINERÁRIO</th></tr></thead><tbody></tbody></table>';
              modal += '     <div id="bodyItiMaps-'+i+'" style="padding: 0 20px;"></div>'
              modal += '       <div id="carregaSpinner-'+i+'" style="text-align: center;"><div class="fa-3x"><i class="fas fa-spinner fa-spin"></i></div> </div>';
              modal += '      <div class="modal-footer">';
              modal += '       <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>';
              modal += '      </div></div></div><div id="directions-panel-'+i+'"></div></div>';
              ////// Final Modal \\\\\\

              $("#allModais").append(modal);
              ////// Criando mapa \\\\\\
              let trajetoOk = [];
              let c         = 0;

              waypNew[i] = [];

              for(let x=0;x<pontos.length;x++){ 
                
                let nome = pontos[x].NOME.replace(/"/g, '\\"');
                waypNew[i].push([pontos[x].LATITUDE, pontos[x].LONGITUDE, nome]);
                c++;
             
              }

              let jsonString = dados[i].TRAJETO[0][0];
              const jsonObject = JSON.parse(jsonString);
              jsonObject.forEach(coord => {
                trajetoOk.push([coord.lng, coord.lat]);
              });
              
              if(c > 0){

                $(`#maps-${i}`).append(`
                  <input type="hidden" id="i-${i}" name="i-${i}" value="${i}">
                  <input type="hidden" id="trajeto-${i}" name="trajeto-${i}" value="${JSON.stringify(trajetoOk)}">
                  <input type="hidden" id="c-${i}" name="c-${i}" value="${c}">
                  <input type="hidden" id="lat-${i}" name="lat-${i}" value="${ret.lat}">
                  <input type="hidden" id="lon-${i}" name="lon-${i}" value="${ret.lon}">
                  <input type="hidden" id="all-${i}" name="all-${i}" value="${all}">
                `);
                
              }
              
              lastShowBu++;
            } // end if

            if(ret.cont > 10 && i == 10){
              html += "<tr class='verMaisRotas' onclick='showMoreRoutes(this, 1, "+ret.lat+", "+ret.lon+", "+all+")'>";
              html += "<td colspan='2'>Mostrar Mais Itinerários</td>";
              html += "</tr>";
            }

          } // End FOr

        } else {
          swal({
            title: "ATENÇÃO",
            text: "Nenhuma resultado encontrado!",
            icon: "warning",
            button: "Fechar",
          });
        }
        
        $("#linhasItiner").html(html);

      } else {

        swal({
          title: "ATENÇÃO",
          text: "Nenhum resultado encontrado para o endereço informado!",
          icon: "warning",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
      
    },error: function(){
      swal({
        title: "ERRO",
        text: "Ocorreu um erro ao carregar, tente novamente!",
        icon: "error",
        button: "Fechar",
      });

      $("#carregando").removeClass('show');
    }
  });
}

$(document).on('shown.bs.modal', '.modalRotas' , function(){

  if($(this).hasClass('mapaCarregado')){
    return false;
  }

  const modelId = $(this).attr('id');
  var nId = modelId.replace(/\D/g, "");
  
  let i = $(`#${modelId}`).find(`#i-${nId}`).val();

  if(i){
    
    let trajeto = $(`#${modelId}`).find(`#trajeto-${nId}`).val();
    trajeto = JSON.parse(trajeto);
    let lat = $(`#${modelId}`).find(`#lat-${nId}`).val();
    let lon = $(`#${modelId}`).find(`#lon-${nId}`).val();
    let all = $(`#${modelId}`).find(`#all-${nId}`).val();
    initMap(i, lat, lon, false, all, trajeto);

    $(this).addClass('mapaCarregado');

  }

});

function getPaxItinerario(mat = '', nome = '', icIt = 0, isOwner = false)
{

  $("#modalPaxSelect").modal("hide");

  let name      = nome;
  let matricula = mat;
  let ic        = icIt;
  let url       = "/passageiro/seach/";

  if(!isOwner) {

    name      = $("#name").val();
    matricula = $("#matricula").val();
    ic        = $("#ic").val();

    if(name == "" && matricula == ""){
      swal({
        title: "ATENÇÃO",
        text: "Informe seu Nome ou sua Matrícula!",
        icon: "warning",
        button: "Fechar",
      });

      return false;
    }
  }

  if(name == "" && matricula == ""){
    swal({
      title: "ATENÇÃO",
      text: "Ocorreu um erro inesperado. Favor preencha novamente os dados!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $("#linhasItiner").html('');
  $("#allModais").html('');
  $("#carregando").addClass('show');

  $.ajax({ 
    url: url,
    method: 'post',
    data : { name, matricula, ic },
    dataType: 'json',
    success:function(ret){

      $("#carregando").removeClass('show');

      if(ret.retorn && ret.retorn.choise) {

        let pax = ret.retorn.data;
        $("#bodyModalPax").html("");
        let trs = "";

        for(let i=0; i < pax.length; i++){

          let mat = pax[i].MATRICULA_FUNCIONAL ? pax[i].MATRICULA_FUNCIONAL : "-";

          trs += '<tr class="cursor" onclick=\'getPaxItinerario("'+mat+'", "'+pax[i].NOME+'", '+ic+', true)\'>'+
          '<td>'+pax[i].NOME+'</td>'+
          '<td>'+mat+'</td>'+
          '</tr>';

        }

        $("#bodyModalPax").html(trs);
        $("#modalPaxSelect").modal("show");
        $("#carregando").removeClass('show');
        return false;

      } else if(ret.retorn && !ret.retorn.choise && ret.retorn.success){

        let html = "";

        if(ret.retorn.data.length){

          let dados= ret.retorn.data[0];
          let matric = dados.MATRICULA_FUNCIONAL ? dados.MATRICULA_FUNCIONAL : '-';
          let codig  = dados.CODIGO ? dados.CODIGO : '-';
          let linIda = "";
          let linVol = "";

          if(dados.PREFIXOLINHAIDA == null && dados.NOMELINHAIDA == null && dados.DESCRICAOINTINERARIOIDA == null){

            linIda = " - ";

          } else {

            let preProv = dados.PREFIXOLINHAIDA ? dados.PREFIXOLINHAIDA : "";
            let linProv = dados.NOMELINHAIDA ? dados.NOMELINHAIDA : "";
            let desProv = dados.DESCRICAOINTINERARIOIDA ? dados.DESCRICAOINTINERARIOIDA : "";
            linIda      = preProv +" - "+ linProv +" - "+ desProv;
          }

          if(dados.PREFIXOLINHAVOL == null && dados.NOMELINHAVOL == null && dados.DESCRICAOINTINERARIOVOL == null){

            linVol = " - ";

          } else {

            let preProv2 = dados.PREFIXOLINHAVOL ? dados.PREFIXOLINHAVOL : "";
            let linProv2 = dados.NOMELINHAVOL ? dados.NOMELINHAVOL : "";
            let desProv2 = dados.DESCRICAOINTINERARIOVOL ? dados.DESCRICAOINTINERARIOVOL : "";
            linVol       = preProv2 +" - "+ linProv2 +" - "+ desProv2;
          }

          $("#ItinIda").val(dados.ITINERARIO_ID_IDA);
          $("#ItinVolta").val(dados.ITINERARIO_ID_VOLTA);

          html += "<tr>";
          html += "<td style='font-weight: 700;'>NOME COMPLETO:</td>";
          html += "<td>"+dados.NOME+"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>CÓDIGO CARTÃO:</td>";
          html += "<td>"+ codig +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>MATRÍCULA:</td>";
          html += "<td>"+ matric +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>LINHA IDA:</td>";
          html += "<td>"+ linIda + " | Veja o Mapa:  <span onclick='openMapItine(1)' style='cursor:pointer'><i style='font-size: 25px;cursor:pointer;color: green;' class='fas fa-map-marked-alt'></i></span> </td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>LINHA VOLTA:</td>";
          html += "<td>"+ linVol + " | Veja o Mapa:  <span onclick='openMapItine(2)' style='cursor:pointer'><i style='font-size: 25px;cursor:pointer;color: green;' class='fas fa-map-marked-alt'></i></span> </td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>POLTRONA IDA:</td>";
          html += "<td>"+ ( dados.POL != null ? dados.POL : "-" ) +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>POLTRONA VOLTA:</td>";
          html += "<td>"+ ( dados.POLVOLTA != null ? dados.POLVOLTA : "-" ) +"</td>";
          html += "</tr>";

          $("#linhasItiner").html(html);

        } else {

          swal({
            title: "ATENÇÃO",
            text: "Ocorreu um erro inesperado. Tente novamente!",
            icon: "warning",
            button: "Fechar",
          });
          $("#carregando").removeClass('show');

          return false;
        }

        
      } else {

        swal({
          title: "ATENÇÃO",
          text: "Nenhum resultado encontrado para o filtro informado!",
          icon: "warning",
          button: "Fechar",
        });
        $("#carregando").removeClass('show');

        return false;
      }
      
    },error: function(){
      swal({
        title: "ERRO",
        text: "Ocorreu um erro ao carregar, tente novamente!",
        icon: "error",
        button: "Fechar",
      });

      $("#carregando").removeClass('show');
    }
  });

}

$(document).on('#modalPaxMapsIt hidden.bs.modal', function () {
  $("#mapUser").html('');
});


function openMapItine(tp)
{
  $("#carregaSpinner").show();
  $("#contentMapsIt").hide();
  $("#bodyItiMaps").html('');

  let id    = tp == 1 ? $("#ItinIda").val() : $("#ItinVolta").val();
  let html  = "";
  
  $("#modalPaxMapsIt").modal("show");

  $.ajax({
    url: "/passageiro/mapsUser",
    method: 'post',
    data : { id },
    dataType: 'json',
    success:function(ret){
     
      if(ret.html && ret.html.length){

        let dados = ret.html;
        totalBusca = ret.cont;
        html = dados[0].pontosIt.length > 0 ? dados[0].pontosIt[0].TRAJETO : "";

        for(let i=0; i < ret.cont; i++) {

          let pontos= dados[i].PONTOS;

          ////// Criando mapa \\\\\\
          let wayp      = [];
          let nmPointer = [];
          let tt        = pontos.length;
          let dif       = tt > 27 ? tt - 27 : 0;
          let c         = 0;
          
          let removeDots = tt - 25;

          for(let x=0;x<pontos.length;x++){ 
        
            wayp.push([pontos[x].LATITUDE, pontos[x].LONGITUDE]);
            c++;
         
          }

          if(c > 0){
            
            let met = c > 1 ? parseInt((c/2)) : 1;
            
            initMap(i, met, wayp, c, ret.lat, ret.lon, nmPointer, true);

            $("#carregaSpinner").hide();
            $("#contentMapsIt").show(); 

            document.getElementById('bodyItiMaps').innerHTML = html;
            
          }
          
        } // End FOr

      } else {

        swal({
          title: "ATENÇÃO",
          text: "Nenhuma resultado encontrado!",
          icon: "warning",
          button: "Fechar",
        });

        $("#carregaSpinner").hide();
        $("#contentMapsIt").show();
      }

    }
  });

}

function getPaxItinerarioEspecial(mat = '', nome = '', icIt = 0, isOwner = false)
{

  $("#modalPaxSelect").modal("hide");

  let name      = nome;
  let matricula = mat;
  let ic        = icIt;
  let url       = "/passageiro/seachEspecial/";

  if(!isOwner) {

    name      = $("#name").val();
    matricula = $("#matricula").val();
    ic        = $("#ic").val();

    if(name == "" && matricula == ""){
      swal({
        title: "ATENÇÃO",
        text: "Informe seu Nome ou sua Matrícula!",
        icon: "warning",
        button: "Fechar",
      });

      return false;
    }
  }

  if(name == "" && matricula == ""){
    swal({
      title: "ATENÇÃO",
      text: "Ocorreu um erro inesperado. Favor preencha novamente os dados!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $("#linhasItiner").html('');
  $("#allModais").html('');
  $("#carregando").addClass('show');

  $.ajax({ 
    url: url,
    method: 'post',
    data : { name, matricula, ic },
    dataType: 'json',
    success:function(ret){

      $("#carregando").removeClass('show');

      if(ret.retorn && ret.retorn.choise) {

        let pax = ret.retorn.data;
        $("#bodyModalPax").html("");
        let trs = "";

        for(let i=0; i < pax.length; i++){

          let mat = pax[i].MATRICULA_FUNCIONAL ? pax[i].MATRICULA_FUNCIONAL : "-";

          trs += '<tr class="cursor" onclick=\'getPaxItinerarioEspecial("'+mat+'", "'+pax[i].NOME+'", '+ic+', true)\'>'+
          '<td>'+pax[i].NOME+'</td>'+
          '<td>'+mat+'</td>'+
          '</tr>';

        }

        $("#bodyModalPax").html(trs);
        $("#modalPaxSelect").modal("show");
        $("#carregando").removeClass('show');
        return false;

      } else if(ret.retorn && !ret.retorn.choise && ret.retorn.success){

        let html = "";

        if(ret.retorn.data.length){

          let dados= ret.retorn.data[0];
          let matric = dados.MATRICULA_FUNCIONAL ? dados.MATRICULA_FUNCIONAL : '-';
          let codig  = dados.CODIGO ? dados.CODIGO : '-';
          let linIda = "";
          let linVol = "";

          if(dados.PREFIXOLINHAIDA == null && dados.NOMELINHAIDA == null && dados.DESCRICAOINTINERARIOIDA == null){

            linIda = " - ";

          } else {

            let preProv = dados.PREFIXOLINHAIDA ? dados.PREFIXOLINHAIDA : "";
            let linProv = dados.NOMELINHAIDA ? dados.NOMELINHAIDA : "";
            let desProv = dados.DESCRICAOINTINERARIOIDA ? dados.DESCRICAOINTINERARIOIDA : "";
            linIda      = preProv +" - "+ linProv +" - "+ desProv;

          }

          if(dados.PREFIXOLINHAVOL == null && dados.NOMELINHAVOL == null && dados.DESCRICAOINTINERARIOVOL == null){

            linVol = " - ";

          } else {

            let preProv2 = dados.PREFIXOLINHAVOL ? dados.PREFIXOLINHAVOL : "";
            let linProv2 = dados.NOMELINHAVOL ? dados.NOMELINHAVOL : "";
            let desProv2 = dados.DESCRICAOINTINERARIOVOL ? dados.DESCRICAOINTINERARIOVOL : "";
            linVol      = preProv2 +" - "+ linProv2 +" - "+ desProv2;

          }

          html += "<tr>";
          html += "<td style='font-weight: 700;'>NOME COMPLETO:</td>";
          html += "<td>"+dados.NOME+"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>CÓDIGO CARTÃO:</td>";
          html += "<td>"+ codig +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>MATRÍCULA:</td>";
          html += "<td>"+ matric +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>LINHA IDA:</td>";
          html += "<td>"+ linIda +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>POLTRONA IDA:</td>";
          html += "<td>"+ ( dados.POLIDA != null ? dados.POLIDA : "-" ) +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>LINHA VOLTA:</td>";
          html += "<td>"+ linVol +"</td>";
          html += "</tr>";
          html += "<tr>";
          html += "<td style='font-weight: 700;'>POLTRONA VOLTA:</td>";
          html += "<td>"+ ( dados.POLVOLTA != null ? dados.POLVOLTA : "-" ) +"</td>";
          html += "</tr>";

          $("#linhasItiner").html(html);

        } else {

          swal({
            title: "ATENÇÃO",
            text: "Ocorreu um erro inesperado. Tente novamente!",
            icon: "warning",
            button: "Fechar",
          });
          $("#carregando").removeClass('show');

          return false;
        }

        
      } else {

        swal({
          title: "ATENÇÃO",
          text: "Nenhum resultado encontrado para o filtro informado!",
          icon: "warning",
          button: "Fechar",
        });
        $("#carregando").removeClass('show');

        return false;
      }
      
    }
  });

  setTimeout(()=>{
    $("#carregando").removeClass('show');
  }, 20000);

}

function showMoreRoutes(obj, c = 1, lat, lon, all = 0)
{

  if(!$(obj).hasClass("verMaisRotas"))
  return false;

  $(obj).removeClass("verMaisRotas");
  $(obj).find('td').html("<hr>");

  let max   = (c+1) * 10;
  let dados = rotasEncon;
  let html  = "";

  for(let i=lastShowBu; i < totalBusca; i++) {

    if(i < max) {
      html += "<tr class='rotasEncontradas' data-toggle='modal' data-target='#maps-"+i+"' onclick='getItinetariosGer(\""+dados[i].CODIGO_INTEGRACAO+"\", "+i+")'>";
      html += "<td>"+dados[i].PREF+"</td>";
      html += "<td>"+dados[i].LINHA+"</td>";
      html += "</tr>";

      let modal = "";
      let pontos= dados[i].PONTOS;
      ////// Criando Modal \\\\\\
      modal += '<div class="modal fade modalRotas" id="maps-'+i+'" tabindex="-1" role="dialog" aria-labelledby="modalRotas" aria-hidden="true">';
      modal += ' <div class="modal-dialog" role="document" style="min-width: 80%;">';
      modal += '    <div class="modal-content">';
      modal += '      <div class="modal-header">';
      modal += '        <h5 class="modal-title" id="modalRotas">ROTA DA LINHA: '+dados[i].LINHA+'</h5>';
      modal += '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">';
      modal += '          <span aria-hidden="true">&times;</span>';
      modal += '        </button>';
      modal += '      </div>';
      modal += '      <div class="modal-body"><div id="map-'+i+'" style="width: 100%; height: 530px;"></div></div>';
      
      modal += '      <table class="table table-striped"><thead><tr style="padding: 0 20px;"><th scope="col" style="color: white;font-size: 20px;background-color: #062323;">ITINERÁRIO</th></tr></thead><tbody></tbody></table>';
      modal += '     <div id="bodyItiMaps-'+i+'" style="padding: 0 20px;"></div>'
      modal += '       <div id="carregaSpinner-'+i+'" style="text-align: center;"><div class="fa-3x"><i class="fas fa-spinner fa-spin"></i></div> </div>';

      modal += '      <div class="modal-footer">';
      modal += '       <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>';
      modal += '      </div></div></div><div id="directions-panel-'+i+'"></div></div>';
      ////// Final Modal \\\\\\
      $("#allModais").append(modal);
      ////// Criando mapa \\\\\\
      let trajetoOk = [];
      let c         = 0;

      waypNew[i] = [];

      for(let x=0;x<pontos.length;x++){ 
        
        let nome = pontos[x].NOME.replace(/"/g, '\\"');
        waypNew[i].push([pontos[x].LATITUDE, pontos[x].LONGITUDE, nome]);
        c++;
      
      }

      let jsonString = dados[i].TRAJETO[0][0];
      const jsonObject = JSON.parse(jsonString);
      jsonObject.forEach(coord => {
        trajetoOk.push([coord.lng, coord.lat]);
      });

      if(c > 0){

        $(`#maps-${i}`).append(`
          <input type="hidden" id="i-${i}" name="i-${i}" value="${i}">
          <input type="hidden" id="trajeto-${i}" name="trajeto-${i}" value="${JSON.stringify(trajetoOk)}">
          <input type="hidden" id="c-${i}" name="c-${i}" value="${c}">
          <input type="hidden" id="lat-${i}" name="lat-${i}" value="${lat}">
          <input type="hidden" id="lon-${i}" name="lon-${i}" value="${lon}">
          <input type="hidden" id="all-${i}" name="all-${i}" value="${all}">
        `);

      }
      
      lastShowBu++;
    } // end if

    if(totalBusca > max && i == max){
      let cou = c+1;
      html += "<tr class='verMaisRotas' onclick='showMoreRoutes(this, "+cou+", "+lat+", "+lon+", "+all+")'>";
      html += "<td colspan='2'>Mostrar Mais Itinerários</td>";
      html += "</tr>";
    }
    if(totalBusca == max && i == (max-1)){
      html += "<tr>";
      html += "<td colspan='2'><hr></td>";
      html += "</tr>";
    }

  } // End FOr

  $("#linhasItiner").append(html);
}

function parametersGrupo()
{
  let uniID = $("#unidadeID").val();

  //// CLEAR ALL \\\\
  $(".clearInput").val("");

  if ( uniID != "" )
  {
        
    $.ajax({
      url: "/parameterEscala/dataGroup",
      method: 'post',
      data : { "uniID": uniID },
      dataType: 'json',
      success:function(data){
    
        for(let i = 0; i < data.length; i++)
        {
          $("#md-" + data[i].mes).val( data[i].maxFolgaMes );
          $("#mds-" + data[i].mes).val( data[i].maxDiaSemFolga );
        }

      }
    });

  }

}

function changeMonthYear()
{
  let mes         = $("#mes").val();
  let ano         = $("#ano").val();
  let unidade     = $("#unidade").val();

  var currentTime = new Date();
  var month       = currentTime.getMonth() + 1;
  var year        = currentTime.getFullYear();

  if (year == ano && mes < month)
  {
    swal({
      title: "ATENÇÃO",
      text: "O mês selecionado não pode ser menor que o mês atual!",
      icon: "warning",
      button: "Fechar",
    });

    $("#mes").val(month).change();

    return false;
  }

  if (unidade == "")
  {
    swal({
      title: "ATENÇÃO",
      text: "Selecione uma unidade para poder ver a quantidade máxima de folgas no Mês!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  if(mes != "" && ano != "" && unidade != "")
  {
    //mes = (parseInt(mes) + 1) <= 12 ? (parseInt(mes) + 1) : 1;
    $.ajax({
      url: "/escala/monthYear",
      method: 'post',
      data : { mes, ano, unidade },
      dataType: 'json',
      success:function(data){
        
        $("#mxfm").val(data.mxfm);
        $("#mxsf").val(data.mxsf);
        $("#numberFolga").html(data.mxfm);

        if(data.tt)
        {

          $(".infTMQ").remove();

          let html  = "";
          let html2 = "";
          let days  = data.days;

          for(let i=1; i<= data.tt; i++)
          {
            if (days[i].color)
            {
              html  += '<th class="infTMQ letraDia" style="max-width: 1% !important;background-color:#b97800" diafds="' + days[i].letter + '">' + days[i].letter + '</th>';
              html2 += '<th class="infTMQ letraDia" style="max-width: 1% !important;background-color:#b97800">' + i + '</th>';
            }else {
              html  += '<th class="infTMQ letraDia" style="max-width: 1% !important;">' + days[i].letter + '</th>';
              html2 += '<th class="infTMQ letraDia" style="max-width: 1% !important;">' + i + '</th>';
            }
          }

          // Tratando colunas abaixo
          $("#turno1 tr").each(function(i,o)
          {
            let tds = $(o).find('.cursorHand').length;
            let idt =  $(o).find('.itenIdent').val();
            
            if(tds > 0)
            {
              if (tds > data.tt)
              { 
                // Remover ultimas tds
                for(let i = tds; i > data.tt; i-- )
                {
                  $(o).find('.cursorHand').last().remove();
                }

              } else { 
                // Add Tds
                for(let i = tds; i < data.tt; i++ )
                {
                  const tpFunc = idt != null && idt != undefined ? 1 : 2;
                  const id = idt != null && idt != undefined ? idt : 1;
                  const inputAdd = idt != null && idt != undefined ? '' : 
                  `<input type="hidden" name="t${i+1}-${id}[]" class=t${i+1}-${id} secl value="0" />`;
                  $(o).append(`<td class="isDiaTrabalho cursorHand" id="chooseEscala" v="0" title="Dia de Trabalho">
                    ${inputAdd} 
                    <div class="chooseEscala">
                        <div title="Turno" v="4" cl="t${(i+1)}" id="${id}" onclick="updateEscalaNew(this, ${tpFunc}, event)"></div>
                        <div title="Afastamento" cl="t${(i+1)}" v="2" id="${id}" onclick="updateEscalaNew(this, ${tpFunc}, event)"></div>
                        <div title="Férias" v="3" cl="t${(i+1)}" id="${id}" onclick="updateEscalaNew(this, ${tpFunc}, event)"></div>
                        <div title="Folga" v="1" cl="t${(i+1)}" id="${id}" onclick="updateEscalaNew(this, ${tpFunc}, event)"></div>
                      </div>
                    </td>`);
                  }
                }
              }
            });

          $(".funAppTMQ").after(html);
          $(".funAppTMQ2").html(html2);

        }
  
      }
    });

  }

  return true;
}

function addRE(t)
{
  // t é usado para turno que antes eram fixo as 3 tabelas
  let count = $(".jc th").length - 3;
  let l     = $(".ar-"+t).length;

  // ATENÇÃO: ESSA CLASS: are-"+t+"-"+l+, PRECISA SER A PRIMEIRA NESSE INPUT
  let input = "<input type='text' name='re[]' class='are-"+t+"-"+l+" persInRE ar-"+t+"' placeholder='Procurar..' />";
  let optSelec = "<select name='hour[]' class='selecTurno'><option value='0'>Selecione Horário</option><option value='1'>1º TURNO - ESCALA</option><option value='2'>2º TURNO - ESCALA</option><option value='3'>3º TURNO - ESCALA</option><option value='4'>ADM - ESCALA</option></select>";

  let html = '<tr re="" nome="">';
      html +='<td><span title="Remover Colaborador da Escala" class="btnLessColl" onclick="removeCollab(this)"><i class="fas fa-user-minus"></i></span> '+input+' </td>';
      html +='<td class="name nomeEscala"></td>';
                            
      html +='<td class="horario">'+optSelec+'</td>';

      for(let x=1; x <= 31; x++)
      {
        if(x < 29 )
          html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="${x}" t="${t}">
                    <input type="hidden" name="t${x}-${t}[]" class=t${x}-${t} secl value="0" />`; 

        if(count >= 29 && x == 29)
          html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="29" t="${t}">
                    <input type="hidden" name="t29-${t}[]" class=t29-${t} secl value="0" />`;

        if(count >= 30 && x == 30)
        html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="30" t="${t}">
                  <input type="hidden" name="t30-${t}[]" class=t30-${t} secl value="0" />`;

        if(count == 31 && x == 31)
        html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="31" t="${t}">
                  <input type="hidden" name="t31-${t}[]" class=t31-${t} secl value="0" />`;

        html+=`<div class="chooseEscala">
                <div title="Turno" v="4" onclick="updateEscalaNew(this, 2, event)"></div>
                <div title="Afastamento" v="2" onclick="updateEscalaNew(this, 2, event)"></div>
                <div title="Férias" v="3" onclick="updateEscalaNew(this, 2, event)"></div>
                <div title="Folga" v="1" onclick="updateEscalaNew(this, 2, event)"></div>
                </div>
              </td>`;
      }

      html +='</tr>';
  
  $("#turno"+t).append(html);

  if ($('select').length > 0) {
    $('select').not('[multiple]').select2({
      width:'100%',
      "language": {
      "noResults": function(){
          return "Nenhum resultado encontrado";
      }
      },
        escapeMarkup: function (markup) {
            return markup;
        }
    });
  }

  $('.are-'+t+"-"+l).on("blur", function()
  {
    const reSaved = $(this).parent().parent('tr').attr('re');
    if(reSaved != '' && $(this).val() == ''){
      $(this).val(reSaved);
    }
  });

  $('.are-'+t+"-"+l).on("keydown", function(e)
  {
    if(e.which == 13 || e.which == 9)
    {
      e.preventDefault();
      let gr = $("#grupo").val();
      $('.reMarkeup').remove();
      
      if(e.target.value != "" && gr != "" && gr != "Selecione um Grupo")
      {
        let re = e.target.value;

        //verificando se o RE informado já está na escala
        //e para caso esteja
        let existsRE = $(`#turno${t} tr[re="${re}"]`).length;
        if(existsRE != 0){
          swal({
            title: "ATENÇÃO",
            text: `Colaborador: ${$(`#turno${t} tr[re="${re}"]`).attr('nome')}\nRE: ${re}\nJÁ ESTÁ NA ESCALA`,
            icon: "warning",
            buttons: {
              cancel: 'Cancelar',
              confirm: 'Ver',
            }
          })
          .then((isConfirm) => {
            if (isConfirm) {
              $(`#turno${t} tr[re="${re}"]`).append('<div class="reMarkeup yellow" title="Fechar Marcador"></div>')
              $('html, body').animate({
                scrollTop: $(`#turno${t} tr[re="${re}"]`).offset().top - 25
              }, 300);
            }
          });
          return false;
        }
        
        $("#carregando").addClass('show');

        $.ajax({
          url: "/escala/collaboradorEscala",
          method: 'post',
          data : { re, gr },
          dataType: 'json',
          success:function(data){

            $("#carregando").removeClass('show');

            if(data.pax == false)
            {
              swal({
                title: "ATENÇÃO",
                text: "Colaborador não encontrado. Procure o RH para mais informações!",
                icon: "warning",
                button: "Fechar",
              });

            } else 
            {
              let c = e.target.classList[0];
              let f = data.pax.funcao ? data.pax.funcao : '-';
              $('.'+c).parent().parent().find('.name').html(`
              <div class="cargoHover" title="Clique 2 vezes para ocultar o cargo.">
                ${f}
              </div>
              ${data.pax.nome} <i class="fas fa-address-card verCargo" title="Ver Cargo"></i><i class="fa fa-random folgasRandom" aria-hidden="true" title="Criar Folgas Randomicamente"></i>`);
              $('.'+c).parent().parent('tr').attr({
                're': data.pax.re,
                'nome' : data.pax.nome
              });
            }
      
          }
        });

      } else if(gr == "" || gr == "Selecione um Grupo")
      {

        swal({
          title: "ATENÇÃO",
          text: "Selecione um Grupo antes de fazer a Busca pelo RE!",
          icon: "warning",
          button: "Fechar",
        });

      }

      return false;
    }
  });

}

$(document).on('click', '.reMarkeup', function(e){
  e.preventDefault();
  e.stopPropagation();
  $(this).fadeOut(function(){
    $(this).remove();
  });
});


function getPaxSetor()
{

  let st = $("#setores").val();

  if (st == "" || st == "Selecione")
  {
    swal({
      title: "ATENÇÃO",
      text: "Selecione um Setor!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $("#carregando").addClass('show');

  $.ajax({
    url: "/escala/paxSetor",
    method: 'post',
    data : { st  },
    dataType: 'json',
    success:function(data){
      
      if(data.pax == false)
      {
        swal({
          title: "ATENÇÃO",
          text: "Nenhum Colaborador encontrado nesse setor! Procure o RH para mais informações.",
          icon: "warning",
          button: "Fechar",
        });

      } else  {

        // t é usado para turno que antes eram fixo as 3 tabelas
        let t         = 1;
        let count     = $(".jc th").length - 3;
        let l         = $(".ar-"+t).length;
        let optSelec  = "<select name='hour[]' class='selecTurno'><option value='0'>Selecione Horário</option><option value='1'>1º TURNO - ESCALA</option><option value='2'>2º TURNO - ESCALA</option><option value='3'>3º TURNO - ESCALA</option><option value='4'>ADM - ESCALA</option></select>";

        if(data.pax.length && data.pax.length > 0)
        {

          let paxs = data.pax;

          for(let i = 0; i < paxs.length; i++)
          { // ATENÇÃO: ESSA CLASS: are-"+t+"-"+l+, PRECISA SER A PRIMEIRA NESSE INPUT
            let input = "<input type='text' name='re[]' class='are-"+t+"-"+l+" persInRE ar-"+t+"' placeholder='Procurar..' value='"+paxs[i].MATRICULA_FUNCIONAL+"' />";

            let html = '<tr>';
                html +='<td><span title="Remover Colaborador da Escala" class="btnLessColl" onclick="removeCollab(this)"><i class="fas fa-user-minus"></i></span> '+input+' </td>';
                html +='<td class="name">'+paxs[i].NOME+'</td>';
                html +='<td class="horario">'+optSelec+'</td>';

                for(let x=1; x <= 31; x++)
                {
                  if(x < 29 )
                    html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="${x}" t="${t}">
                              <input type="hidden" name="t${x}-${t}[]" class=t${x}-${t} secl value="0" />`; 

                  if(count >= 29 && x == 29)
                    html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="29" t="${t}">
                              <input type="hidden" name="t29-${t}[]" class=t29-${t} secl value="0" />`;

                  if(count >= 30 && x == 30)
                  html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="30" t="${t}">
                            <input type="hidden" name="t30-${t}[]" class=t30-${t} secl value="0" />`;

                  if(count == 31 && x == 31)
                  html +=`<td id="chooseEscala" class="isDiaTrabalho cursorHand" title="Dia de Trabalho" v="0" n="31" t="${t}">
                            <input type="hidden" name="t31-${t}[]" class=t31-${t} secl value="0" />`;

                  html+=`<div class="chooseEscala">
                          <div title="Turno" v="4" onclick="updateEscalaNew(this, 2, event)"></div>
                          <div title="Afastamento" v="2" onclick="updateEscalaNew(this, 2, event)"></div>
                          <div title="Férias" v="3" onclick="updateEscalaNew(this, 2, event)"></div>
                          <div title="Folga" v="1" onclick="updateEscalaNew(this, 2, event)"></div>
                          </div>
                        </td>`;
                }
                html +='</tr>';

            $("#turno"+t).append(html);

          }
          
        }

      }

      $("#carregando").removeClass('show');
    }
  });
}

function removeCollab(obj)
{

  const re = $(obj).closest('tr').attr('re');
  if(re == ''){
    confirmRemoveCollab(obj);
    return;
  }

  const nome = $(obj).closest('tr').attr('nome');

  swal({
    title: 'Remover da Escala?',
    text: `${nome} \n RE: ${re}`,
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {

    if (result) {
      confirmRemoveCollab(obj);
    }
  });
}

function confirmRemoveCollab(obj){

  // Identifica qual Turno foi removido
  let clasT   = $(obj).parent().find('input')[0].classList;
  let clasTra = clasT[0].split("-");
  let newClass= clasT.value.replace(clasT[0], "");

  // Remove
  $(obj).parent().parent().remove();

  // Ver quantos sobraram
  let l  = $(".ar-"+clasTra[1]).length;

  // Reordenar 
  $(".ar-"+clasTra[1]).each(function(v, i)
  {
    let conc = "are-"+clasTra[1]+"-"+v+" " + newClass;
    $(this)[0].className = conc;
  });

}

function removeCollabExist(obj, id)
{

  const re = $(obj).closest('tr').attr('re');
  const nome = $(obj).closest('tr').attr('nome');

  swal({
    title: 'Remover da Escala?',
    text: `${nome} \n RE: ${re}`,
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {

    if (result) {

      $.ajax({
        url: "/escala/collaboradorEscalaDelete",
        method: 'post',
        data : { id },
        dataType: 'json',
        success:function(data){

          if(data.pax == false)
          {
            swal({
              title: "ATENÇÃO",
              text: "Ocorreu um erro ao deletar, tente novamente!",
              icon: "warning",
              button: "Fechar",
            });

          } else {
            swal({
              title: "ATENÇÃO",
              text: "Deletado com sucesso!",
              icon: "success",
              button: "Fechar",
            });

            // Remove
            $(obj).parent().parent().remove();

          }
    
        }
      });

    }

  })

}

$('.tableEscala').on("click", "td#chooseEscala", function() {
    //verificando se a unidade foi selecionada antes de prosseguir
    if($('#unidade').length && $('#unidade').val() == '' || $('#mxfm').val() == ''){
      swal({
        title: "ATENÇÃO",
        text: 'Selecione a UNIDADE antes de prosseguir',
        icon: "warning",
        confirm: "OK",
      }).then(() => {
        $('#unidade').trigger('focus');
      });

      return false;
    }
      
    $('.tableEscala .chooseEscala').removeClass('show');
    $(this).children('.chooseEscala').addClass('show');  
    $('.tableEscala').addClass('chooseEscalaShow');

    $(this).children('.chooseEscala').children('div').each(function(){
      $(this).attr('title', `${$(this).attr('title').split('-')[0].trim()}`);
    });
    
    setTimeout(() => {
      
      const isMarked = $(this).hasClass('isDiaTrabalho') ? false : true;
      const tdV = $(this).attr('v');
      const tdBefore = $(this).prev('#chooseEscala');
      const tdBeforeV = $(tdBefore).attr('v');

      if(isMarked){
        $(this).children('.chooseEscala').children(`[v=${tdV}]`)
        .attr('title', `${$(this).children('.chooseEscala').children(`[v=${tdV}]`).attr('title')} - Limpar Seleção`)
      }

      if(tdBefore.length && $(this).children('.chooseEscala').hasClass('show')){
        
        if(isMarked){
          if(tdBeforeV == tdV){
            const diasAnteriores = $(this).prevUntil(`:not([v=${tdBeforeV}])`).length;
            $(this).children('.chooseEscala').children(`[v=${tdBeforeV}]`).not('[v=1]')
            .attr('title', `${$(this).children('.chooseEscala').children(`[v=${tdBeforeV}]`).attr('title')} - Sergure Ctrl para também remover ${diasAnteriores == 1 ? 'do Dia Anterior':`dos ${diasAnteriores} Dias Anteriores`}`);
          }
        }
        else{
          if($(tdBefore).hasClass('isDiaTrabalho')){
            const diasAnteriores = $(this).prevUntil(':not(.isDiaTrabalho)').length;
            $(this).children('.chooseEscala').children('div').not('[v=1]').each(function(){
              $(this).attr('title', `${$(this).attr('title').split('-')[0].trim()} - Segure Shift para também Adicionar ${diasAnteriores == 1 ? 'ao Dia Anterior Livre':`aos ${diasAnteriores} Dias Anteriores Livres`}`);
            });
          }
        }
      }
    }, 200);
    
});

//chamar função para criar folgas randomicamente
$(document).on('click', '.folgasRandom', function(e){
  e.preventDefault();
  e.stopPropagation();
   //verificando se a unidade foi selecionada antes de prosseguir
   if($('#unidade').length && $('#unidade').val() == '' || $('#mxfm').val() == ''){
    swal({
      title: "ATENÇÃO",
      text: 'Selecione a UNIDADE antes de prosseguir',
      icon: "warning",
      confirm: "OK",
    }).then(() => {
      $('#unidade').trigger('focus');
    });

    return false;
  }

  const toAply = $(this);
  let totalFolgas = $(toAply).closest('tr').find('.secl').length;
  
  //limpar caso tenha aplicado antes de criar randomicamente
  if(totalFolgas != 0){
    let folgasLimpar = 0;
    $(toAply).closest('tr').find('.cursorHand.secl').each(function(){
      $(this).closest('#chooseEscala').find('.chooseEscala').find('div[v=1]').trigger('click');
      folgasLimpar++;
      if(folgasLimpar == totalFolgas){
        const checaLoading = setInterval(()=>{
          if($(toAply).closest('tr').find('.loadingTrEscala').length == 0){
            clearInterval(checaLoading);
            aplyRandomFolga(toAply);
          }
        }, 100);
      }
    });
    return false;
  }
  
  aplyRandomFolga(toAply);
});

//funcao para pegar dias randomicamente
function getRandomDays(arr, num) {
  const shuffled = [...arr].sort(() => 0.5 - Math.random());

  return shuffled.slice(0, num);
}

//funcao que cria folgas randomicamente
function aplyRandomFolga(toAply){
  let totalFolgas = 0;
  let mxfm = $("#mxfm").val();
  let mxsf  = $("#mxsf").val();
 
  //pegar sábados e domingos
  let sabs = [];
  let doms = [];

  //confere se pode adicionar a array sabs e adiciona
  $(toAply).closest('table').find('thead').find('.letraDia[diafds=S]').each(function(){
    const indexAply = $(this).index()-3;
    const testAply = $(toAply).closest('tr').find(`.cursorHand:eq(${indexAply})`);
    if($(testAply).hasClass('isDiaTrabalho')){
      // $(testAply).addClass('fds');
      sabs.push(indexAply);
    }
  });

  //confere se pode adicionar a array doms e adiciona
  $(toAply).closest('table').find('thead').find('.letraDia[diafds=D]').each(function(){
    const indexAply = $(this).index()-3;
    const testAply = $(toAply).closest('tr').find(`.cursorHand:eq(${indexAply})`);
    if($(testAply).hasClass('isDiaTrabalho')){
      // $(testAply).addClass('fds');
      doms.push(indexAply);
    }
  });
  
  //selecionar entre os sábados de forma randomica e coloca na array diasParaAplicar
  getRandomDays(sabs, 1).map(function(key) {
    const diaAplyFolga = $(toAply).closest('tr').find(`.cursorHand:eq(${key})`);
    $(diaAplyFolga).closest('#chooseEscala').find('.chooseEscala').find('div[v=1]').trigger('click');
    totalFolgas++;
  });

  //selecionar entre os domingos de forma randomica e coloca na array diasParaAplicar
  getRandomDays(doms, 1).map(function(key) {
    const diaAplyFolga = $(toAply).closest('tr').find(`.cursorHand:eq(${key})`);
    $(diaAplyFolga).closest('#chooseEscala').find('.chooseEscala').find('div[v=1]').trigger('click');
    totalFolgas++;
  });
  
  //depois de ter aplicado um sábado e um domingo randomicamente adiciona mais dias de folga
  let diasTrabalho = [];
  setTimeout(()=>{
    $(toAply).closest('tr').find('.cursorHand.isDiaTrabalho:not(.fds)').each(function(){
      diasTrabalho.push($(this).index()-3);
    });
    
    for(totalFolgas = totalFolgas; totalFolgas < mxfm; totalFolgas++){
      getRandomDays(diasTrabalho, 1).map(function(key) {
        const index = diasTrabalho.findIndex(x => x === key);
        diasTrabalho.splice(index, 1);
        const diaAplyFolga = $(toAply).closest('tr').find(`.cursorHand:eq(${key})`);
        $(diaAplyFolga).closest('#chooseEscala').find('.chooseEscala').find('div[v=1]').trigger('click');       
        });
    }
    
  }, 300);

  const checaFolgasAplicadas = setInterval(()=>{
    const totalFolgasAplicadas = $(toAply).closest('tr').find('.cursorHand.isFolga').length;
    
    if(totalFolgasAplicadas == mxfm){
      clearInterval(checaFolgasAplicadas);
      
      $(toAply).closest('tr').find('.cursorHand.isFolga').each(function(){
        const antes = $(this).prevUntil(':not(.isDiaTrabalho)').length;
        const depois = $(this).nextUntil(':not(.isDiaTrabalho)').length;        
        if(antes > mxsf || depois > mxsf){
          $(toAply).closest('tr').find('.folgasRandom').trigger('click');
          return false;
        }
      });
    }
  }, 100);
}

$('body').on('click', function(e){
  if(e.target.id !== 'chooseEscala' && $('.tableEscala.chooseEscalaShow').length){
    $('.tableEscala .chooseEscala').removeClass('show');
    setTimeout(()=>{
      $('.tableEscala').removeClass('chooseEscalaShow');
    }, 300);
  }
});


//nova função serve tanto para update quanto para selected
//tp 1 = update, tp2 = selected
function updateEscalaNew(obj, tp, event){
  
  //pega as variaveis do objeto e do parent td dele
  let v = Number($(obj).attr('v'));
  const cl = $(obj).attr('cl');
  const id = Number($(obj).attr('id'));
  const parent = $(obj).parents('td');
  const parentV = Number(parent.attr('v'));

  //coloca um load na linha para evitar cliques enquanto está fazendo a lógica
  const loadID = Math.floor(Math.random() * 100);
  $(parent).closest('tr').append(`<div class="loadingTrEscala" id="${loadID}"></div>`);
  let atualClass = '';
  let newClass = '';
  let newTitle = '';

  //paramentro para aplicar em mais de um dia ao mesmo tempo
  let multipleSel = false;

  //parametro para apagar em mais de um dia ao mesmo tempo
  let multipleDel = false;

  //verifca se o obj e o parent td dele têm o mesmo valor, caso tenha seta como dia Dia de Trabalho
  if(v == parentV){
    v = 0;
    newClass = 'isDiaTrabalho';
    newTitle = 'Dia de Trabalho';
    if (event.ctrlKey && v != 1) {
      multipleDel = true;
      atualClass = $(parent).attr('class').replace('cursorHand', '');
    }
  }else{
    //se estiver com shift pressionado e não for dia de folga seta que deve aplicar 
    //os dias anteriores também
    if (event.shiftKey && v != 1) {
      multipleSel = true;
    }
    switch(v) {
      case 1:
        newClass = 'isFolga secl';
        newTitle = 'Folga';
        break;
      case 2:
        newClass = 'isAf';
        newTitle = 'Afastamento';
        break;
      case 3:
        newClass = 'isFe';
        newTitle = 'Férias';
        break;
      case 4:
        newClass = 'isTurno';
        newTitle = 'Turno';
        break;
    }
  }

  //se for folga verifica os paramentros
  if(v == 1){
    let mxfm = $("#mxfm").val();
    let ttf = $(parent).parent('tr').find('.secl').length;
    if(mxfm <= ttf)
      {
        swal({
          title: "ATENÇÃO",
          text: "Não foi possível adicionar a folga. O número máximo de folgas para o mês é de: " + mxfm + "!",
          icon: "warning",
          confirm: "Fechar",
        }).then(() => {
          $(parent).closest('tr').find(`.loadingTrEscala[id=${loadID}]`).remove();
        });
        
        return false;
      }
  }

  //se for selected trata aqui e não faz update
  if(tp == 2){
    $(parent).find('input').val(v);
    $(parent).removeClass().addClass(`${newClass} cursorHand`)
    .attr({
      'title': newTitle,
      'v': v,
    });
    //se for para aplicar os dias anteriores chama função
    if(multipleSel){
      checkMultipleSel(parent, v, null);
      multipleSel = false;
    }
    if(multipleDel){
      checkMultipleSel(parent, parentV, atualClass);
      multipleDel = false;
    }
    $(parent).closest('tr').find(`.loadingTrEscala[id=${loadID}]`).fadeOut(function(){
      $(this).remove();
    });
    return;
  }
  
  //se for update envia o ajax para fazer o update
  $.ajax({
    url: "/escala/updateItemEscala",
    method: 'post',
    data : { id, c:cl, v },
    dataType: 'json',
    success:function(){
      $(parent).removeClass().addClass(`${newClass} cursorHand`)
      .attr({
        'title': newTitle,
        'v' : v
      });
      //se for para aplicar os dias anteriores chama função
      if(multipleSel){
        checkMultipleSel(parent, v, null);
        multipleSel = false;
      }
      if(multipleDel){
        checkMultipleSel(parent, parentV, atualClass);
        multipleDel = false;
      }
      $(parent).closest('tr').find(`.loadingTrEscala[id=${loadID}]`).fadeOut(function(){
        $(this).remove();
      });
    }
  });
}


function checkMultipleSel(parent, v, atualClass){

  const before = atualClass == null ? $(parent).prevUntil(':not(.isDiaTrabalho)') : $(parent).prevUntil(`:not(.${atualClass})`);
  
  before.each(function(){
    $(this).children('.chooseEscala').children(`div[v=${v}]`).trigger('click');
  });

}

//tratamento para campos com required 
function checkRequired(){
  const reqFileds = $('form').find('[required]');
  let reqFiledsCount = 0;
  let reqFiledsText  = "";

  $(reqFileds).each(function(){
    if($(this).val() == '' || $(this).val() == 'Selecione'){
      let label = $(`label[for="${$(this).attr('id')}"]`).text().replace(':', '');
      reqFiledsCount++;
      reqFiledsText += `${label}\n`;
    }
  });
  if(reqFiledsCount != 0){
    swal({
      title: `${reqFiledsCount > 1 ? 'CAMPOS OBRIGATÓRIOS:' : 'CAMPO OBRIGATÓRIO:'}`,
      text: reqFiledsText,
      icon: "warning",
      confirm: "Voltar",
    });
    return false;
  }
  else{
    return true;
  }
}

// $(document).on('submit','form',function(e){
//   e.preventDefault();
//   const reqFileds = $(this).find('[required]');
//   let reqFiledsCount = 0;
//   let reqFiledsText  = "";
//     $(reqFileds).each(function(){
//       if($(this).val() == '' || $(this).val() == 'Selecione'){
//         let label = $(`label[for="${$(this).attr('id')}"]`).text().replace(':', '');
//         reqFiledsCount++;
//         reqFiledsText += `${label}\n`;
//       }
//     });
//     if(reqFiledsCount != 0){
//       swal({
//         title: `${reqFiledsCount > 1 ? 'CAMPOS OBRIGATÓRIOS:' : 'CAMPO OBRIGATÓRIO:'}`,
//         text: reqFiledsText,
//         icon: "warning",
//         confirm: "Voltar",
//       });
//     }
// });

async function saveEscala(tp)
{

  //verificando se a unidade foi selecionada antes de prosseguir
  if($('#unidade').length && $('#unidade').val() == '' || $('#mxfm').val() == ''){
    await swal({
      title: "ATENÇÃO",
      text: 'Selecione a UNIDADE válida antes de prosseguir',
      icon: "warning",
      confirm: "OK",
    }).then(() => {
      $('#unidade').trigger('focus');
    });

    return false;
  }

  //verifica se foi colocado Centro de Custo Antes de Prosseguir

  if($("#descricaoCC").length && $("#descricaoCC").val() == ''){
    await swal({
      title: "ATENÇÃO",
      text: 'Digite um Centro de Custo válido antes de prosseguir',
      icon: "warning",
      confirm: "OK",
    }).then(() => {
      $('#centroCusto').trigger('focus');
    });

    return false;
  }
  
  //variavel que confirma o envio
  let confirmSend = true;
  
  let persInRE  = $(".persInRE").length;
  let persInREempty = $(".persInRE").filter(function() { return $(this).val() == ""; }).length;
  let itenIdent = $(".itenIdent").length;
  let errors    = "";

  //verificando se tem colaboradores antes de prosseguir
  if(itenIdent == 0 && persInRE == 0 )
  {
    await swal({
      title: "ATENÇÃO",
      text: 'Selecione ao menos um Colaborador',
      icon: "warning",
      button: "Fechar",
    }).then(() => {
      $('.btnRE').trigger('click');
    });

    return false;
  }else{
    //verificando se os REs estão preenchidos
    if(persInREempty != 0)
    {
      await swal({
        title: "ATENÇÃO",
        text: persInREempty > 1 ? 'Todos REs precisam ser preenchidos' : '1 RE precisa ser preenchido',
        icon: "warning",
        button: "Fechar",
      });

      return false;
    }else{
      //verificando se os Res foram aplicados
      let errorReApply = 0;
      $(".persInRE").each(function(){
        if($(this).parent().parent().attr('re') == ''){
          errorReApply++;
        }
      });
      if(errorReApply != 0)
      {
        await swal({
          title: "ATENÇÃO",
          text: `${errorReApply} RE${errorReApply > 1 ? 's':''} precisa${errorReApply > 1 ? 'm':''} ser aplicado${errorReApply > 1 ? 's':''}`,
          icon: "warning",
          buttons: {
            cancel: 'Cancelar',
            confirm: 'Aplicar',
          }
        }).then((isConfirm) => {
          if (isConfirm) {
            $(".persInRE").each(function(){
              //aplicando os REs quando necessário
              if($(this).parent().parent().attr('re') == ''){
                let e = $.Event('keydown');
                e.which = 13;
                $(this).trigger(e);
              }
            });
          }
        });
        return false;
      }
      else{
        //verificar se os horarios estão escolhidos
        let errorSelecTurno = 0;
        let errorSelecTurnoTxt = "";
        $(".selecTurno").each(function(){
          if($(this).val() == '' || $(this).val() == 0){
            errorSelecTurno++;
            errorSelecTurnoTxt += `\n RE = ${$(this).parent().parent().attr('re')} - NOME = ${$(this).parent().parent().attr('nome')}\n`;
          }
        });
        if(errorSelecTurno != 0){
          await swal({
            title: "ATENÇÃO",
            text: `${errorSelecTurno > 1 ? 'Escolha o Turno dos seguintes colaboradores:' : 'Escolha o Turno do seguinte colaborador:'}\n${errorSelecTurnoTxt}`,
            icon: "warning",
            button: "Fechar",
          });
          return false;
        }
      } 
    } 
  }
  
  //caso esteja tentando mandar para o RH irá pedir para confirmar o envio
  //e tratar na variavel confirmSend
  if(tp == 2){
    
    confirmSend = false;
    await swal({
       title: 'Enviar para o RH?',
       buttons: {
          cancel: 'Cancelar',
          confirm: 'Sim',
        }
      })
      .then((isConfirm) => {
        if (isConfirm) {
          confirmSend = true;
        }else{
          confirmSend = false;
        }
      });
  }

  if(!confirmSend){return;}

  $("#carregando").addClass('show');

  
  if( $('#grupo').val() == "" || $('#grupo').val() == "Selecione um Grupo" ) errors += "Selecione um Grupo\n";
  
  //// VERIFICA SE ESTÃO RESPEITANDO O MINIMO DE DESCANSO CONFORME PARAMETRO \\\\
  let mxsf  = $("#mxsf").val();
  let names = "";
  let arrv1     = [];
  let arrv2     = [];
  let arrv3     = [];
  $("#turno1 tr").each(function(i, d)
  {
    $(d).find(`.reMarkeup.red`).remove();
    if( $(d).find('td').length > 0)
    {
      let sequen = 0;

      $(d).find('.cursorHand').each(function(p,o)
      {
        if(!$(o).hasClass('isDiaTrabalho'))
        {
          sequen = 0;
          
        } else {
          sequen++;
          if(sequen == 1 && $(o).nextUntil(':not(.isDiaTrabalho)').length >= mxsf){
            $(o).append(`<div class="reMarkeup red" title="Fechar Marcador"
            style="width: calc(${$(o).nextUntil(':not(.isDiaTrabalho)').length+1}*${$(o).outerWidth()}px);">
            <i class="fa fa-exclamation-triangle" style="margin-right: .5em" aria-hidden="true"></i> ${$(o).nextUntil(':not(.isDiaTrabalho)').length+1} DIAS SEM FOLGA</div>`);
          }          
        }

        if (sequen > mxsf )
        {
          
          let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();

          if ( arrv3.indexOf(matricRE) == -1)
          {
            names += "\n RE = " + matricRE + " - NOME = " + $(d).attr('nome');
            arrv3.push(matricRE);
          }

          return;
        }

      });
     
    }

  });

  //// VERIFICA SE ESTÃO RESPEITANDO O MAXIMO DE FOLGAS \\\\
  //// VERIFICA SE TEM FOLGA NO SABADO E DOMINGO \\\\
  let mxfm      = $("#mxfm").val();
  let namesMax  = "";
  let hasFolgSD = "";
  let hasFolgSDCount = 0;

  const delay = ms => new Promise(res => setTimeout(res, ms));

  let mes       = $("#mes").val();
  let ano       = $("#ano").val();
  
  await $("#turno1 tr").each(async function(i, d)
  {
    let totalTdTested = 0;
    if( $(d).find('td').length > 0)
    {
      const totalTdToTest = $(d).find('.cursorHand:not(.isDiaTrabalho)').length;
      
      let notHhasSa  = false;
      let notHhasDom = false;
  
      await $(d).find('.cursorHand').each( async function(p,o)
      {
        const diaLetra = $(d).closest('table').find('thead').find(`.letraDia:eq(${p})`).attr('diafds');
        

        if($(this).hasClass('isFolga') && diaLetra != null && diaLetra != undefined){
          if(diaLetra == 'S'){
            notHhasSa  = true;
          }
          if(diaLetra == 'D'){
            notHhasDom = true;
          }
        }

        totalTdTested++;
        if(totalTdTested == totalTdToTest){
          setTimeout(()=>{
            console.log(notHhasSa);
            console.log(notHhasDom);
            if ( !notHhasSa || !notHhasDom )
              {
                let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();
                hasFolgSD += "\n RE = " + matricRE + " - NOME = " + $(d).attr('nome');
                hasFolgSDCount++;
                return;
              }
          }, 300);
        }
        // if(!$(this).hasClass('isDiaTrabalho'))
        // {
        //   let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();
        //     let hasSa = await isWeekend( (p+1), mes, ano, 1);
        //     let hasDm = await isWeekend( (p+1), mes, ano, 2);

        //   if ( hasSa == 1){
            
        //     console.log("MATRIC =>  " + matricRE);
        //     console.log("DATE =>  " + (p+1) +"-"+ mes +"-"+ ano);
        //     console.log("notHhasSa =>  " + hasSa);
        //     notHhasSa  = true;
        //   }
    
        //   if (hasDm == 1){
            
        //     console.log("MATRIC =>  " + matricRE);
        //     console.log("DATE =>  " + (p+1) +"-"+ mes +"-"+ ano);
        //     console.log("notHhasDom =>  " + hasDm);
        //     notHhasDom = true;
        //   }
        //   totalTdTested++;
        // }
        //   if(totalTdTested == totalTdToTest){
        //     let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();
        //     console.log("matricRE =>  " + matricRE);
        //     console.log("notHhasSa =>  " + notHhasSa);
        //     console.log("notHhasDom =>  " + notHhasDom);

        //     if ( !notHhasSa || !notHhasDom )
        //     {
        //       hasFolgSD += "\n RE = " + matricRE + " - NOME = " + $(d).attr('nome');
        //       hasFolgSDCount++;
        //       return;
        //     }
        //   }
      });

      
    //  setTimeout(()=>{
    //   console.log('teste');
    //   let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();
    //   console.log("matricRE =>  " + matricRE);
    //   console.log("notHhasSa =>  " + notHhasSa);
    //   console.log("notHhasDom =>  " + notHhasDom);

    //   if ( !notHhasSa || !notHhasDom )
    //   {
    //     hasFolgSD += "\n RE = " + matricRE + " - NOME = " + $(d).attr('nome');
    //     hasFolgSDCount++;
    //     return;
    //   }

    //  },5000);
      
    }

  });

  $("#turno1 tr").each(function(i, d)
  {
  
    if( $(d).find('td').length > 0)
    {
      let ttdF       = 0;
 
      $(d).find('.cursorHand').each(function(p,o)
      {
    
        if( $(this).hasClass('isFolga') )
          ttdF++;

        if (ttdF > mxfm )
        {
          let matricRE = $(d).find('.persInRE').val() != null && $(d).find('.persInRE').val() != undefined ? $(d).find('.persInRE').val() : $(d).find('.reex').html();

          if ( arrv1.indexOf(matricRE) == -1)
          {
            namesMax += "\n RE = " + matricRE + " - NOME = " + $(d).attr('nome');
            arrv1.push(matricRE);
          }
         
          return;
        }

      });
    }

  });

  await delay(3000);

  $("#carregando").removeClass('show');

  if(names != "")
  {
    await swal({
      title: "ATENÇÃO",
      text: `${arrv3.length > 1 ? 'Os colaboradores':'O colaborador'}:
      ${names}
      ${arrv3.length > 1 ? 'não estão':'não está'} respeitando o período máximo de dias sem folga.\n
      *O período máximo de dias sem folgas são: ${mxsf}`,
      icon: "warning",
      button: "Fechar",
    }).then(() => {
      $('.reMarkeup.red').addClass('show');
    });
    return;
  }

  if(namesMax != "")
  {
    await swal({
      title: "ATENÇÃO",
      text: `${arrv1.length > 1 ? 'Os colaboradores':'O colaborador'}:
      ${namesMax}
      ${arrv1.length > 1 ? 'não estão':'não está'} respeitando o máximo de folgas.\n
      *O número máximo de folgas permitidas no mês são: ${mxfm}`,
      icon: "warning",
      button: "Fechar",
    });

  }

  if(hasFolgSD != "")
  {
    await swal({
      title: "ATENÇÃO",
      text: `${hasFolgSDCount > 1 ? 'Os colaboradores':'O colaborador'}:
      ${hasFolgSD}
      ${hasFolgSDCount > 1 ? 'precisam':'precisa'} ter ao menos 1 folga no Sábado e 1 folga no Domingo!`,
      icon: "warning",
      button: "Fechar",
    });

  }

  if( errors != '')
  {
    await swal({
      title: "ATENÇÃO",
      text: errors,
      icon: "warning",
      button: "Fechar",
    });

    return false;
  } 
  else if ( ( names != "" || namesMax != "" || hasFolgSD != "" ) && tp==2 ) 
  {
    // Se tentar enviar para o RH irá bloquear até ajustar a escala
    await swal({
      title: "ATENÇÃO",
      text: "Não foi possível enviar a Escala para o RH até ajustar as pendências informadas!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  } else {
    //verficando se os campos com required estão preenchidos
    if(!checkRequired()){
      return;
    }
    $("#typeEscale").val(tp);
    $("#sendFormEscala").submit();
  }

}

async function isWeekend(day, mes, ano, cons)
{
  let pro   = ano + "-" + mes + "-" + day;
  let retur = 0;
  //var date = new Date(pro);

  await $.ajax({
    url: "/rh/isWeekend",
    method: 'post',
    data : { pro },
    dataType: 'json',
    success:function(data){
      // console.log("Data ==>> " + data.isWeek);
      // console.log("RETURN > REF > " + pro);
      if (cons == 1 && data.isWeek == "S") // Verifica se é sabado
      {
        console.log("ENTROU SABADO> REF > " + pro);
        retur = 1;
      } else if (cons == 2 && data.isWeek == "D") // Verifica se é Domingo
      {
        console.log("ENTROU DOMINGO> REF > " + pro);
        retur = 1;
      } 
    }
 });

  // if ( (cons== 1 && date.getDay() == 6) || ( cons== 2 && date.getDay() == 0) )
  // {
  //   console.log("REF ===>> " + pro);
  //   console.log("TIPO ===>> " + cons);
  //   console.log("DATE ===>> " +  date.getDay());
  //   return true;
  // }
    
  // return false;
  
  return retur;
}

//pegar id da unidade quando seleciona gestor
async function getUnByLider(id){

  if(id == '' || id == 'Selecione'){
    $('#unidade').val('Selecione').change();
    return;
  }
  
  $.ajax({
    url: "/escala/getUnByLider",
    method: 'post',
    data : { id },
    dataType: 'json',
    success:function(data)
    {
      if(data != ''){
        if($(`#unidade option[value="${data}"]`).length){
          $('#unidade').val(data).change();
        }
      }else{
        $('#unidade').val('Selecione').change();
      }
    }
  });
}

function openInputFile(inp)
{
  $("#"+inp).trigger('click');
}

function sendFileImport(inp)
{
  $("#"+inp).submit();
}

function generateRelEsc(tp)
{

  if(tp > 0)
  {
    $("#tipo").val(tp);
    $("#sendFormRelEscala").submit();
  }

}

function sendResponseRh(tp)
{
  

  if(tp == 1 )
  { // Aprovado

    $("#carregando").addClass('show');

    let sdfe = $("#eghrsfr").val();
    $.ajax({
      url: "/rh/sendResponseRh",
      method: 'post',
      data : { sdfe, tp},
      dataType: 'json',
      success:function(data){

        $("#carregando").removeClass('show');

        if(data.pax)
        {
          swal({
            title: "SUCESSO",
            text: "Dados Salvo com Sucesso!",
            icon: "success",
            button: "Fechar",
          });

          setTimeout(()=>{

            window.location.href = "/rh/";
            
          }, 3000);

        } else {
          swal({
            title: "ATENÇÃO",
            text: "Ocorreu um erro, tente novamente!",
            icon: "warning",
            button: "Fechar",
          });
        }
      }
    });

  } else { // Recusado

    $("#modalMotive").modal('show');

  }
  return true;
}

function recusarMotivo()
{
  let motive  = $("#motivetxt").val();
  let sdfe    = $("#eghrsfr").val();
  let tp      = 2;

  $("#carregando").addClass('show');

  $.ajax({
    url: "/rh/sendResponseRh",
    method: 'post',
    data : { sdfe, tp, motive},
    dataType: 'json',
    success:function(data){

      $("#carregando").removeClass('show');

      $("#modalMotive").modal('hide');

      if(data.pax)
      {
        swal({
          title: "SUCESSO",
          text: "Dados Salvo com Sucesso!",
          icon: "success",
          button: "Fechar",
        });

        setTimeout(()=>{

          window.location.href = "/rh/";
          
        }, 3000);

      } else {
        swal({
          title: "ATENÇÃO",
          text: "Ocorreu um erro, tente novamente!",
          icon: "warning",
          button: "Fechar",
        });
      }
    }
  });

  return true;
}

function filterUnEscala()
{
  $("#formFilterEsc").submit();   
}

function filterNomeEscala(){
  const fNomeNow = $('#fNome').attr('fNomeNow');
  
  if($('#fNome').val() == fNomeNow || $('#fNome').val() == ''){
    return;
  }

  $("#formFilterEsc").submit();

}

function limparNomeEscala(){
  $('#fNome').val('');
  $("#formFilterEsc").submit();
}

$(document).on('click', '.verCargo', function(){
  $(this).parents('.nomeEscala').children('.cargoHover').addClass('show');
});

$(document).on('dblclick', '.cargoHover', function(){
  $(this).removeClass('show');
});

$(document).on('click', '.escalaCopiada', function(){
  $(this).fadeOut(function(){
    $(this).remove();
  });
});


function changePage(p)
{
  $("#pesc").val(p);
  $("#formFilterEsc").submit();
}
////////////////////////////////////////
/// USADO PARA FAZER O AUTOCOMPLETE \\\\
////////////////////////////////////////
"use strict";

function initialize() 
{
  var input = document.getElementById('enderecoToken');
  new google.maps.places.Autocomplete(input);

}

function removeLs() 
{
  var input = document.getElementById('enderecoToken');
  google.maps.event.clearInstanceListeners(input);

}

// url.search("rotas") != -1

// var url = window.location.href;
// if (url.indexOf('rotas') >= 0){ 
//   google.maps.event.addDomListener(window, 'load', initialize);
// }

function checkFind(text){

  var url = window.location.href;

  if (url.indexOf('rotas') >= 0){ 

    if(text.length > 10){
      initialize();
    }else{
      removeLs();
    }
    
  }

}

////////////////////////////////////////////////////////////////////////
///////////////////// USADO PARA FAZER OS POINTER \\\\\\\\\\\\\\\\\\\\\\
////////////////////////////////////////////////////////////////////////
function generateKML(trajeto) {
  
  let kml = '<?xml version="1.0" encoding="UTF-8"?>';
  kml += '<kml xmlns="http://www.opengis.net/kml/2.2">';
  kml += '<Document>';
  kml += '<name>Rota</name>';
  kml += '<description>Rota gerada</description>';
  kml += '<Placemark>';
  kml += '<name>Path</name>';
  kml += '<LineString>';
  kml += '<tessellate>1</tessellate>';
  kml += '<coordinates>';

  trajeto.forEach(point => {
    kml += `${point[0]},${point[1]},0 `;
  });

  kml += '</coordinates>';
  kml += '</LineString>';
  kml += '</Placemark>'; 
  kml += '</Document>';
  kml += '</kml>';

  const parser = new DOMParser();
  const kmlDoc = parser.parseFromString(kml, 'application/xml');

  return toGeoJSON.kml(kmlDoc);
}

let waypNew = [];

function initMap(i, lat, long, userIti = false, all = 0, trajeto) 
{

  let idElem = userIti ? "mapUser" : "map-"+i;
  const map = new google.maps.Map(document.getElementById(idElem), {
    minZoom: 10,
    zoom: 11,
    disableDefaultUI:true
  });

  const kmlContent = generateKML(trajeto);
  
  map.data.addGeoJson(kmlContent);
  map.data.setStyle({
    strokeColor: '#FF0000',
    strokeWeight: 2
  });

  const bounds = new google.maps.LatLngBounds();
  map.data.forEach(function(feature) {
    feature.getGeometry().forEachLatLng(function(latlng) {
      bounds.extend(latlng);
    });
  });
  map.fitBounds(bounds);

  var icons = {
    home: new google.maps.MarkerImage(
      '/assets/images/icon48.png',
      new google.maps.Size(32, 32),
      new google.maps.Point(0, 0),
      new google.maps.Point(12, 12),
      new google.maps.Size(24, 24)
    ),
    point: new google.maps.MarkerImage(
      '/assets/images/icon25.png',
      new google.maps.Size(32, 32),
      new google.maps.Point(0, 0),
      new google.maps.Point(16, 24),
      new google.maps.Size(32, 32)
    ),
    start: new google.maps.MarkerImage(
      '/assets/images/go.png',
      new google.maps.Size(64, 64),
      new google.maps.Point(0, 0),
      new google.maps.Point(16, 32),
      new google.maps.Size(32, 32)
    ),
    end: new google.maps.MarkerImage(
      '/assets/images/stop.png',
      new google.maps.Size(64, 64),
      new google.maps.Point(0, 0),
      new google.maps.Point(16, 32),
      new google.maps.Size(32, 32)
    )
  };
  
  waypNew[i].forEach((point, index, array) => {
    let icon = icons.point;
    let lat = point[0];
    let long = point[1];
    let title = point[2];
    if(index === 0){
      icon = icons.start;
      lat = trajeto[0][1];
      long = trajeto[0][0];
      title = `Início: ${title}`;
    }else if(index === array.length - 1){
      icon = icons.end;
      lat = trajeto[trajeto.length - 1][1];
      long = trajeto[trajeto.length - 1][0];
      title = `Final: ${title}`;
    }

    addMarker({ lat: lat, lng: long }, map, icon, title);
  });

  var bangalore = { lat: parseFloat(lat), lng: parseFloat(long) };
  if (!userIti && all == 0)
    addMarker(bangalore, map, icons.home);
}

var markers = [];

function addMarker(location, map, icon, title = false) {
  let titleOk;
  if (!title) {
    titleOk = ($('#enderecoToken').length && $('#enderecoToken').val() != '') ? $('#enderecoToken').val() : 'Home';
  } else {
    titleOk = title;
  }
  
  var marker = new google.maps.Marker({
    position: location,
    map: map,
    icon: icon
  });

  var contentString = `<div class="custom-infowindow"><h3>${titleOk}</h3></div>`;
  var infowindow = new google.maps.InfoWindow({
    content: contentString
  });

  // Adicionar um listener de clique para o marcador
  marker.addListener('click', function() {
    // Fechar todas as outras InfoWindows antes de abrir a atual
    closeAllInfoWindows();
    
    // Se a InfoWindow estiver fechada, abra-a; se estiver aberta, feche-a
    if (infowindow.getMap()) {
      infowindow.close();
    } else {
      infowindow.open(map, marker);
    }
  });

  // Evento para fechar a InfoWindow quando o mapa é clicado
  google.maps.event.addListener(map, 'click', function() {
    closeAllInfoWindows();
  });

  // Função para fechar todas as InfoWindows
  function closeAllInfoWindows() {
    // Iterar sobre todos os marcadores no mapa
    markers.forEach(function(marker) {
      // Se houver uma InfoWindow aberta, feche-a
      if (marker.infowindow && marker.infowindow.getMap()) {
        marker.infowindow.close();
      }
    });
  }

  // Adicionar a InfoWindow ao marcador para que possamos acessá-la posteriormente
  marker.infowindow = infowindow;

  // Adicionar o marcador à lista de marcadores
  markers.push(marker);
}

function myFunctionCopy(campo) 
{

  if(!$(`#${campo}`).length){
    return;
  }

  let texto = $(`#${campo}`).val();
  if(texto == '')
  return;

  let copyText = document.getElementById(campo);

  console.log(copyText);

  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");

  let msg = "Copiado com sucesso!";

  if(campo.includes('textLink')){
    msg = `Link copiado com sucesso: \n${texto}`;
  }

  if(campo.includes('textLinkEsp')){
    msg = `Link Especial copiado com sucesso: \n${texto}`;
  }

  swal({
    title: "SUCESSO",
    text: msg,
    icon: "success",
    button: "Fechar",
  });

}

function modalNovo(tipo){
  $('#edicaoHorario').val(0);
  $('#Hora').val('');
  $('#Pico').val(1);
  $('#Restaurante').val(1);
  $('#pico_almoco').val(1);
  $('#tipoHorario').val(tipo);
  $('#novoModal').modal('show');
}

function adicionaEditaHorario(){
  if($('#edicaoHorario').val() == 0){
    adicionar();
  }
  else{
    updateHorario();
  }
}

function adicionar() {
  let auxiliar = $('#auxiliar').val() -1;
  let tipoHorario = $('#tipoHorario').val();
  let varProBanco = '';
  let th = '';
  if(tipoHorario =='manha'){
    th = 1;
    varProBanco = 'dadosManha-';
  }  else if (tipoHorario == 'tarde'){
    th = 2;
    varProBanco = 'dadosTarde-';
  } else if (tipoHorario == 'noite') {
    th = 3;
    varProBanco = 'dadosNoite-';
  }
  let Horario = $('#Hora').val();
  let Horario2 = "'"+Horario+"'";
  let Pico = $('#Pico').val();
  let pic = Pico == 1 ? 'Não' : 'Sim';
  let Restaurante = $('#Restaurante').val();
  let rest = Restaurante == 1 ? 'Não' : 'Sim';
  let PicoAlmoc = $('#pico_almoco').val();
  let picoAlm = PicoAlmoc == 1 ? 'Não' : 'Sim';
  let randomKey = Math.floor(Math.random() * 10000001);


  if($('#dados-'+tipoHorario+'-'+auxiliar).length == 0){

      let textarea = '<textarea id="dados-'+tipoHorario+'-'+auxiliar+'" name="'+varProBanco+auxiliar+'" style="display: none"></textarea>'; 

      $('#contentTable').append(textarea);
    }


  let tableID = tipoHorario;

  let editMark = $('#editMark').val();
  if(editMark != ""){
    let obj = {
      idItem : editMark,
      tipo: tipoHorario,
      horario: Horario,
      restaurante: Restaurante,
      pico_almoco: PicoAlmoc,
      horario_pico: Pico

    }
    //salvando direto no banco o horário
    $.ajax({
      url: "/configuracoes/salvaHorario",
      method: 'post',
      data : obj,
      dataType: 'json',
      success:function(ret){
     
      }
    });
  }

  if(Horario == ''){
    swal({
      icon:'warning',
      text: "Digite um horário."
    });
    return;
  }

  
  $('#'+tableID).append('<tr role="row" class="odd">' +
    '<td class = "text-center">'+Horario+            

    '<td class = "text-center">'+pic+
    
    '<td class = "text-center">'+rest+

    '<td class = "text-center">'+picoAlm+


    '<td class="text-center">' +
    '<span class="btn btn-danger" title="Excluir" onclick="removerHorario(2, this, '+randomKey+', '+th+', 0, '+Horario2+')"><i class="fa fa-trash" ></i></span>' +
    '</td>' +
    '</tr>');

  let obj =  $('#dados-'+tableID+'-'+auxiliar).val() != '' ? JSON.parse($('#dados-'+tableID+'-'+auxiliar).val()) : [];

  let objArr = {
    Horario : Horario,
    Pico : Pico,
    Restaurante:Restaurante,
    TipoHorario: tipoHorario,
    PicoAlmoco : PicoAlmoc,
    randomKey : randomKey
  };

  obj.push(objArr);
  obj = JSON.stringify(obj);
  $('#dados-'+tableID+'-'+auxiliar).val(obj);

  $('#tipoHorario').val('');
  $('#novoModal').modal('hide');
  
}

function updateHorario(){
  
  let id = $('#edicaoHorario').val();
  let tipoHorario = $('#tipoHorario').val();
  let Horario = $('#Hora').val();
  //let Horario2 = "'"+Horario+"'";
  let Pico = $('#Pico').val();
  //let pic = Pico == 1 ? 'Não' : 'Sim';
  let Restaurante = $('#Restaurante').val();
  //let rest = Restaurante == 1 ? 'Não' : 'Sim';
  let PicoAlmoc = $('#pico_almoco').val();
  //let picoAlm = PicoAlmoc == 1 ? 'Não' : 'Sim';
  //let randomKey = Math.floor(Math.random() * 10000001);

   let tipo = '';
    if(tipoHorario == 1){
      tipo = 'manha';
    }else if(tipoHorario == 2){
      tipo = 'tarde';
    }else{
      tipo = 'noite';
    }

     if(Horario == ''){
        swal({
          icon:'warning',
          text: "Digite um horário."
        });
      return;
    }


  let obj = {
      idHorario : id,
      tipo: tipo,
      horario: Horario,
      restaurante: Restaurante,
      pico_almoco: PicoAlmoc,
      horario_pico: Pico

    }

  $.ajax({
      url: "/configuracoes/updateHorario",
      method: 'post',
      data : obj,
      dataType: 'json',
      success:function(ret){
        if(ret.success){
          $('#novoModal').modal('hide');
           swal({
              icon:'success',
              text: ret.msg
            });
          getDot(ret.idPonto);
        }
      }
    });

}

async function mouse_position(event){


  var posicaoX = event.pageX;
  var posicaoY = event.pageY;

  posiçãoReal = $("#divImg").offset();

  var localx = parseInt(posicaoX - posiçãoReal.left);
  var localy = parseInt(posicaoY - posiçãoReal.top);
  var editMark = $('#editMark').val();
  var auxiliar = $('#auxiliar').val();

  $('#manha').find('tbody').html('');
  $('#tarde').find('tbody').html('');
  $('#noite').find('tbody').html('');


  var idEdit = $('#idEdit').val();

  if(idEdit != '' && idEdit != undefined){
    auxiliar = idEdit;
    //ENTRA SÓ NA TELA DE EDIÇÃO


    let classe = '';
    $('#markerIconE-'+auxiliar).removeClass('circleLeft').removeClass('circleDown').removeClass('circleRight').removeClass('circleUp');
    // img = '<img onclick="editMarker('+auxiliar+')" id="markerIconE-'+auxiliar+'" src="/assets/images/mark.png" style="width: 100px; position: absolute;display: none" title="Você está aqui"><input type="hidden" id="posicaoIconeE-'+auxiliar+'" name="posicaoIconeE-'+auxiliar+'" value="">';

    // img = '<div onclick="editMarker('+auxiliar+')" id="markerIconE-'+auxiliar+'" class="'+classe+' circle" ></div><input type="hidden" id="posicaoIconeE-'+auxiliar+'" name="posicaoIconeE-'+auxiliar+'" value="">';

    //$('#divImg').append(img);

    var posicao = [];
    $('#markerIconE-'+auxiliar).removeClass("circleEditRight").removeClass("circleEditLeft").removeClass("circleEditUp").removeClass("circleEditDown");

   

    //verificando posição 
    if(localx <= 500 && (localy > 110 && localy <= 500)){
      $('#markerIconE-'+auxiliar).addClass('circleLeft');
      posicao['left'] =  localx + 17; //ajustando a posição
      posicao['top'] =  localy - 35 ; //ajustando a posição
    }

    if(localy < 110 && localy <= 500){
      $('#markerIconE-'+auxiliar).addClass('circleUp');
      posicao['left'] =  localx - 35; //ajustando a posição
      posicao['top'] =  localy + 20 ;
      
    }
    if(localy > 500){
      $('#markerIconE-'+auxiliar).addClass('circleDown');
      posicao['left'] =  localx - 35; //ajustando a posição
      posicao['top'] =  localy - 90 ; //ajustando a posição
    }

    if(localx > 500 && (localy > 110 && localy <= 500)){
      
      $('#markerIconE-'+auxiliar).addClass('circleRight');
      posicao['left'] =  localx - 83; //ajustando a posição
      posicao['top'] =  localy - 35 ; //ajustando a posição
    }

    let wid = $("#divImg").find('img').width();
    let heg = $("#divImg").find('img').height();

    let localyPor = posicao['top'] * 100 / heg;
    let localxPor = posicao['left'] * 100 / wid;

    let pos = {
      top: localy,
      left: localx,
      topPor: localyPor.toFixed(2),
      leftPor: localxPor.toFixed(2)
    }


    $('#markerIconE-'+auxiliar).css({top: posicao['top'], left: posicao['left']});
    $('#posicaoIconeE-'+auxiliar).val('');
    $('#posicaoIconeE-'+auxiliar).val(JSON.stringify(pos));
    $('#idEdit').val('');
    $('#editMark').val('');
  }
  else{

    if(editMark !== ""){
    auxiliar = editMark;
    }
    
  
    let classe = '';
    $('#markerIcon-'+auxiliar).removeClass('circleLeft').removeClass('circleDown').removeClass('circleRight').removeClass('circleUp');

    img = '<div onclick="editMarker('+auxiliar+')" id="markerIcon-'+auxiliar+'" class="'+classe+' circle" ></div><input type="hidden" id="posicaoIcone-'+auxiliar+'" name="posicaoIcone-'+auxiliar+'" value="">';

    if(editMark == ""){
      $('#divImg').append(img);
    }

    var posicao = [];
    $('#markerIcon-'+auxiliar).removeClass("circleEditRight").removeClass("circleEditLeft").removeClass("circleEditUp").removeClass("circleEditDown");

    
    //verificando posição 
    if(localx <= 500 && (localy > 110 && localy <= 500)){
      $('#markerIcon-'+auxiliar).addClass('circleLeft');
      posicao['left'] =  localx + 17; //ajustando a posição
      posicao['top'] =  localy - 35 ; //ajustando a posição
    }

    if(localy < 110 && localy <= 500){
      $('#markerIcon-'+auxiliar).addClass('circleUp');
      posicao['left'] =  localx - 35; //ajustando a posição
      posicao['top'] =  localy + 20 ;
      
    }
    if(localy > 500){
      $('#markerIcon-'+auxiliar).addClass('circleDown');
      posicao['left'] =  localx - 35; //ajustando a posição
      posicao['top'] =  localy - 90 ; //ajustando a posição
    }

    if(localx > 500 && (localy > 110 && localy <= 500)){
      $('#markerIcon-'+auxiliar).addClass('circleRight');
      posicao['left'] =  localx - 83; //ajustando a posição
      posicao['top'] =  localy - 35 ; //ajustando a posição
    }

    let wid = $("#divImg").find('img').width();
    let heg = $("#divImg").find('img').height();

    let localyPor = posicao['top'] * 100 / heg;
    let localxPor = posicao['left'] * 100 / wid;

    let pos = {
      top: localy,
      left: localx,
      topPor: localyPor.toFixed(2),
      leftPor: localxPor.toFixed(2)
    }


    $('#markerIcon-'+auxiliar).css({top: posicao['top'], left: posicao['left']});
    $('#posicaoIcone-'+auxiliar).val('');
    $('#posicaoIcone-'+auxiliar).val(JSON.stringify(pos));
    
    if(editMark !== ""){
      $('#editMark').val("");
    }else{
      auxiliar++;
      $('#auxiliar').val(auxiliar);
      $('#pontos').show();
    }
  } 

}

function editMarker(auxiliar){
  
  $('#editMark').val(auxiliar);
  $('#nome_ponto').val($('#nomeponto-'+auxiliar).val());
  $('.circle').each(function(){
    if($(this).hasClass("circleEditRight")){
      $(this).removeClass("circleEditRight").addClass('circleRight');
    }
    if($(this).hasClass("circleEditLeft")){
      $(this).removeClass("circleEditLeft").addClass('circleLeft');
    }
    if($(this).hasClass( "circleEditUp" )){
      $(this).removeClass("circleEditUp").addClass('circleUp');
    }
    if($(this).hasClass( "circleEditDown" )){
      $(this).removeClass("circleEditDown").addClass('circleDown');
    }
  });


  if($( "#markerIcon-"+auxiliar ).hasClass( "circleRight" )){
    $( "#markerIcon-"+auxiliar ).removeClass("circleRight").addClass('circleEditRight');
  }
  if($( "#markerIcon-"+auxiliar ).hasClass( "circleLeft" )){
    $( "#markerIcon-"+auxiliar ).removeClass("circleLeft").addClass('circleEditLeft');
  }
  if($( "#markerIcon-"+auxiliar ).hasClass( "circleUp" )){
    $( "#markerIcon-"+auxiliar ).removeClass("circleUp").addClass('circleEditUp');
  }
  if($( "#markerIcon-"+auxiliar ).hasClass( "circleDown" )){
    $( "#markerIcon-"+auxiliar ).removeClass("circleDown").addClass('circleEditDown');
  }



  let dados = $('#dados-manha-'+auxiliar).val() != undefined ? JSON.parse($('#dados-manha-'+auxiliar).val()) : [];
  $('#manha').find('tbody').html('');

  let dadosT = $('#dados-tarde-'+auxiliar).val() != undefined ? JSON.parse($('#dados-tarde-'+auxiliar).val()) : [];
  $('#tarde').find('tbody').html('');

  let dadosN = $('#dados-noite-'+auxiliar).val() != undefined ? JSON.parse($('#dados-noite-'+auxiliar).val()) : [];
  $('#noite').find('tbody').html('');


  for(let i in dados){
    let Horario = dados[i]['Horario'];
    let Horario2 = "'"+dados[i]['Horario']+"'";
    let hor = dados[i]['Horario'].toString();
    let Pico = dados[i]['Pico'];
    let pic = Pico == 1 ? 'Não' : 'Sim';
    let Restaurante = dados[i]['Restaurante'];
    let rest = Restaurante == 1 ? 'Não' : 'Sim';
    let PicoAlmoc = dados[i]['PicoAlmoco'];
    let picoAlm = PicoAlmoc == 1 ? 'Não' : 'Sim';
    let randomKey = Math.floor(Math.random() * 10000001);

    let data = JSON.stringify(dados[i]);

    $('#manha').find('tbody').append('<tr role="row" class="odd">' +
    '<td class = "text-center">'+Horario+            

    '<td class = "text-center">'+pic+
    
    '<td class = "text-center">'+rest+

    '<td class = "text-center">'+picoAlm+


    '<td class="text-center">' +
    '<span class="btn btn-danger" title="Excluir" onclick="removerHorario(2, this, '+randomKey+', 1, 0, '+Horario2+')"><i class="fa fa-trash" ></i></span>' +
    '</td>' +
    '</tr>');


  }

  for(let i in dadosT){
 
    let Horario = dados[i]['Horario'];
    let Horario2 = "'"+dados[i]['Horario']+"'";
    let hor = JSON.stringify(Horario);
    let Pico = dadosT[i]['Pico'];
    let pic = Pico == 1 ? 'Não' : 'Sim';
    let Restaurante = dadosT[i]['Restaurante'];
    let rest = Restaurante == 1 ? 'Não' : 'Sim';
    let PicoAlmoc = dadosT[i]['PicoAlmoco'];
    let picoAlm = PicoAlmoc == 1 ? 'Não' : 'Sim';
    let randomKey = Math.floor(Math.random() * 10000001);

    $('#tarde').find('tbody').append('<tr role="row" class="odd">' +
    '<td class = "text-center">'+Horario+            

    '<td class = "text-center">'+pic+
    
    '<td class = "text-center">'+rest+

    '<td class = "text-center">'+picoAlm+


    '<td class="text-center">' +
    '<span class="btn btn-danger" title="Excluir" onclick="removerHorario(2, this, '+randomKey+', 2, 0,'+Horario2+')"><i class="fa fa-trash" ></i></span>' +
    '</td>' +
    '</tr>');
  }

  for(let i in dadosN){
    
    let Horario = dados[i]['Horario'];
    let Horario2 = "'"+dados[i]['Horario']+"'";
    let Pico = dadosN[i]['Pico'];
    let pic = Pico == 1 ? 'Não' : 'Sim';
    let Restaurante = dadosN[i]['Restaurante'];
    let rest = Restaurante == 1 ? 'Não' : 'Sim';
    let PicoAlmoc = dadosN[i]['PicoAlmoco'];
    let picoAlm = PicoAlmoc == 1 ? 'Não' : 'Sim';
    let randomKey = Math.floor(Math.random() * 10000001);

    $('#noite').find('tbody').append('<tr role="row" class="odd">' +
    '<td class = "text-center">'+Horario+            

    '<td class = "text-center">'+pic+
    
    '<td class = "text-center">'+rest+

    '<td class = "text-center">'+picoAlm+


    '<td class="text-center">' +
    '<span class="btn btn-danger" title="Excluir" onclick="removerHorario(2, this, '+randomKey+', 3, 0, '+Horario2+')"><i class="fa fa-trash" ></i></span>' +
    '</td>' +
    '</tr>');
  }
  
}

function removerHorario(origem, idcampo, randomKey, tipo, idBanco, horario = false){
  
  swal({
    title: 'Deletar Horário',
    text: "Deseja realmente deletar esse registro?",
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {
    if (result) {
      if(origem == 2){
        let txtarea = '';
        let editMark = $('#editMark').val();
        let auxiliar = $('#auxiliar').val();
        if(editMark == '')editMark = auxiliar -1;
        if(tipo == 1)tipo = 'manha'
        else if (tipo == 2)tipo = 'tarde'
        else if (tipo == 3)tipo = 'noite'
            

         
        txtarea = JSON.parse($('#dados-'+tipo+"-"+editMark).val());           
            

            for(var i = 0; i < txtarea.length; i++){
      
              if(txtarea[i]['Horario'] == horario){

                txtarea.splice(i, 1);
              }
            }
           
            $(idcampo).parent().parent().fadeOut();
            $('#dados-'+tipo+"-"+editMark).val(JSON.stringify(txtarea));


          }
          else{
            $.get( "/configuracoes/removerHorario/id-"+idBanco, function( data ) {
              let ret = JSON.parse(data);
              if(!ret['success']){
                swal({
                  icon:'warning',
                  text:ret['msg']
                });
              }else{
                $(idcampo).parent().parent().fadeOut();
              }
            });
          }
        }
      })
  
}

function editarHorario(idBanco, tipoHor){


  $.get( "/configuracoes/getHorario/id-"+idBanco, function( data ) {
              let ret = JSON.parse(data);
              $('#edicaoHorario').val(ret.horario.id);
              $('#Hora').val(ret.horario.horario);
              $('#Pico').val(ret.horario.horario_pico);
              $('#Restaurante').val(ret.horario.restaurante);
              $('#pico_almoco').val(ret.horario.pico_almoco);
              $('#tipoHorario').val(tipoHor);
              $('#novoModal').modal('show');
            });
}

function getDot(id){
  $('#idEdit').val(id);
  $('#editMark').val(id);
  
  let anterior = $('#idEditAnterior').val();
  

  if(anterior != id && anterior != ""){
    if($( "#markerIconE-"+anterior ).hasClass( "circleEditRight" )){
      $( "#markerIconE-"+anterior ).removeClass("circleEditRight").addClass('circleRight');
    }
    if($( "#markerIconE-"+anterior ).hasClass( "circleEditLeft" )){
      $( "#markerIconE-"+anterior ).removeClass("circleEditLeft").addClass('circleLeft');
    }
    if($( "#markerIconE-"+anterior ).hasClass( "circleEditUp" )){
      $( "#markerIconE-"+anterior ).removeClass("circleEditUp").addClass('circleUp');
    }
    if($( "#markerIconE-"+anterior ).hasClass( "circleEditDown" )){
      $( "#markerIconE-"+anterior ).removeClass("circleEditDown").addClass('circleDown');
    }
  }

 

  if($( "#markerIconE-"+id ).hasClass( "circleRight" )){
    $( "#markerIconE-"+id ).removeClass("circleRight").addClass('circleEditRight');
  }
  if($( "#markerIconE-"+id ).hasClass( "circleLeft" )){
    $( "#markerIconE-"+id ).removeClass("circleLeft").addClass('circleEditLeft');
  }
  if($( "#markerIconE-"+id ).hasClass( "circleUp" )){
    $( "#markerIconE-"+id ).removeClass("circleUp").addClass('circleEditUp');
  }
  if($( "#markerIconE-"+id ).hasClass( "circleDown" )){
    $( "#markerIconE-"+id ).removeClass("circleDown").addClass('circleEditDown');
  }
  

   $.get( "/configuracoes/getDot/id-"+id, function( data ) {
              let ret = JSON.parse(data);
              $('#nome_ponto').val(ret['Item']['nome_ponto']);
              $('#dados-manha').html(ret['TRManha']);
              $('#dados-tarde').html(ret['TRTarde']);
              $('#dados-noite').html(ret['TRNoite']);
            });


   $('#idEditAnterior').val(id);

   
}

function salvarEdicao(){
  let idEdit = $('#idEdit').val();
  if(idEdit != '' && idEdit != undefined){
    let ponto = $('#nome_ponto').val();
    let id = $('#editMark').val();

    let marker = [];
    marker['top'] = $('#markerIconE-'+idEdit)[0]['style']['top'];
    marker['left'] = $('#markerIconE-'+idEdit)[0]['style']['left'];


    let posicaoIcone = $('#posicaoIconeE-'+idEdit).val();

    let posiIcon = [];
    posiIcon['top'] = JSON.parse(posicaoIcone)['top']+'px';
    posiIcon['left'] = JSON.parse(posicaoIcone)['left']+'px';

    

    
    if(marker['top'] == posiIcon['top'] && marker['left'] == posiIcon['left']){
      posicaoIcone = null;
    }
    
    //salva
    //ajax pra salvar nomeponto e posição do icone
    let obj = {
      id: id,
      nome_ponto : ponto,
      posicaoIcone: posicaoIcone
    }
    $.ajax({
      url: "/configuracoes/salvaEdicaoPonto",
      method: 'post',
      data : obj,
      dataType: 'json',
      success:function(ret){
        if($( "#markerIconE-"+id ).hasClass( "circleEditRight" )){
          $( "#markerIconE-"+id ).removeClass("circleEditRight").addClass('circleRight');
        }
        if($( "#markerIconE-"+id ).hasClass( "circleEditLeft" )){
          $( "#markerIconE-"+id ).removeClass("circleEditLeft").addClass('circleLeft');
        }
        if($( "#markerIconE-"+id ).hasClass( "circleEditUp" )){
          $( "#markerIconE-"+id ).removeClass("circleEditUp").addClass('circleUp');
        }
        if($( "#markerIconE-"+id ).hasClass( "circleEditDown" )){
          $( "#markerIconE-"+id ).removeClass("circleEditDown").addClass('circleDown');
        }
      }
    });

    document.location.reload(true);


    
  }else{
    addPonto();
  }

    $('#idEdit').val('');
    $('#markerIcon-'+idEdit).removeClass('imgBorder');
    $('#dados-manha').html('');
    $('#dados-tarde').html('');
    $('#dados-noite').html('');
    $('#nome_ponto').val('');
  
}

function addPonto(){
  let nomeponto = $('#nome_ponto').val();
  let aux = $('#auxiliar').val() - 1;
  let editMark = $('#editMark').val();

 

  if(editMark != '' && editMark != undefined){
    aux = editMark;
  }

  if(nomeponto != ""){
    if($('#nomeponto-'+aux).length == 0){

      let tr = '<tr><td class="nomeP-'+aux+'">'+nomeponto+'</td></tr>';
        tr += '<input type="hidden" name="nomeponto-'+aux+'" id="nomeponto-'+aux+'" value="'+nomeponto+'">'; 

        $('#contentTable').append(tr);
    }
    else{
      $('#nomeponto-'+aux).val(nomeponto);
      $('.nomeP-'+aux).html(nomeponto);
    }
    
  }
  $('#nome_ponto').val('');
  $('#manha').find('tbody').html('');
  $('#tarde').find('tbody').html('');
  $('#noite').find('tbody').html('');
  $('#editMark').val('');
}

function removePonto(){

  let aux = $('#auxiliar').val() - 1;
  let editMark = $('#editMark').val();

  let idEdit = $('#idEdit').val();

  
  if(aux == -1 && editMark == '' && idEdit == ''){
    swal({
          icon: "warning",
          text: "Selecione um ponto a excluir"
        });
    return;
  }

  if(idEdit != '' && idEdit != undefined){
    aux = idEdit;


    $.get( "/configuracoes/removePonto/id-"+aux, function( data ) {

                 
    });

  }

  $('.nomeP-'+aux).parent().remove();
  $('#nomeponto-'+aux).remove();
  $('#dados-manha-'+aux).remove();
  $('#dados-tarde-'+aux).remove();
  $('#dados-noite-'+aux).remove();
  $('#markerIcon-'+aux).remove();
  $('#posicaoIcone-'+aux).remove();
  $('#nome_ponto').val('');
  $('#manha').find('tbody').html('');
  $('#tarde').find('tbody').html('');
  $('#noite').find('tbody').html('');

  document.location.reload(true);
  
}

async function salvarTotemEuro(){
  let posicaoIcon = $('#posicaoIcone').val();
  let nomePonto = $('#nome_ponto').val();
  let LINK = $('#LINK').val();

    if(LINK != ''){
      if(posicaoIcon != ''){
        
         const htmlEl = document.getElementById("divImg");

          const canvas = await html2canvas(htmlEl, {
              useCORS: true
          });

          $('#Image_link').val(canvas.toDataURL());
          $('#formEuro').submit();
          
      }
      else{
        swal({
          icon: "warning",
          text: "Escolha um ponto no mapa"
        });
      }
    }
    else{
      swal({
        icon: "warning",
        text: "Gere um LINK clicando sobre o botão 'GERAR LINK'"
      });
    }
}

function deleteTokenEuro(id){

  swal({
    title: 'Deletar Totem',
    text: "Deseja realmente deletar esse registro?",
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {
    if (result) {
      window.location.href = "/configuracoes/totemDeleteEuro?id="+id;
    }
  });
} 

async function  makeScreenshot(selector="body") 
{
  return new Promise((resolve, reject) => {  
    let node = document.getElementById("divImg");

    html2canvas(node, { onrendered: (canvas) => {

      let pngUrl = canvas.toDataURL();      
      resolve(pngUrl);
    }});  
    
  });
  


}

function NomePonto(value){
  let id = $('#editMark').val();
  $('#markerIcon-'+id).html(value);
}

function NomePontoCreate(value){
  let id = $('#auxiliar').val() - 1;
  $('#markerIcon-'+id).html(value);
}

function typeUserCheck(tp)
{
  $(".userPermissions").hide();
  $(".relacoes").show();

  if(tp == 2)
    $(".userPermissions").show();

  if(tp == 3 || tp == 1)
    $(".relacoes").hide();

  
}

function filterTable(event) 
{
  var filter = event.target.value.toUpperCase();
  var rows = document.querySelector("#table tbody").rows;
  
  for (var i = 0; i < rows.length; i++) {
      var firstCol = rows[i].cells[0].textContent.toUpperCase();
      var secondCol = rows[i].cells[1].textContent.toUpperCase();
      var tercCol = rows[i].cells[2].textContent.toUpperCase();
      if (firstCol.indexOf(filter) > -1 || secondCol.indexOf(filter) > -1 || tercCol.indexOf(filter) > -1) {
          rows[i].style.display = "";
      } else {
          rows[i].style.display = "none";
      }      
  }
}

function getItin(obj, tp)
{
  let id = $(obj).val();
  let sen = $(obj).find(":selected").attr("sentido") ;
  let html = "<option value=''>Selecione</option>";
  let html2 = "<option value=''>Selecione</option>";

  //// Busca os dados \\\\
  $.ajax({
    url: "/cadastroPax/itiByLine",
    method: 'post',
    data : { id, tp, sen },
    dataType: 'json',
    success:function(ret){

      if(ret.itin){

        let dt = ret.itin.itid;
        let pe = ret.itin.pontosEmb ? ret.itin.pontosEmb : "";

        let tipo = selectTypeViagem(dt.TIPO);
        let sent = selectSentidoViagem(dt.SENTIDO);
        let from = pe != "" ? pe[0].NOME : " - ";
        let to   = pe != "" ? pe[pe.length - 1].NOME  : " - ";
        let txt  = "Tipo: " + tipo + " | Sentido: " + sent +  " | Trecho: " + dt.TRECHO + " | De: " + from + " | Para: "  + to;
        html += "<option value='"+dt.ID+"'>"+txt+"</option>";

        if(tp == 1 ){
          $("#itiIda").val( dt.ID );
        } else{
          $("#itiVolta").val( dt.ID );
        }
        
        if(pe.length)
        {
          for(let i = 0; i < pe.length; i++)
          {
            html2 += "<option value='"+pe[i].PONTO_REFERENCIA_ID+"'>"+pe[i].NOME+"</option>";
          }
        }

      }
      
      if(tp == 1 ){
       // $("#itiIda").html( html );
        $("#pontoEmbar").html( html2 );
      }
      else{
       // $("#itiVolta").html( html );
        $("#pontoDesmbar").html( html2 );
      }

    }
  });

}

function selectTypeViagem(tp)
{
  let ret = " - ";

  switch (tp) {
    case 0:
    case "0":
      ret = "Soltura";
      break;
    case 1:
    case "1":
      ret = "Recolhimento";
      break;
    case 2:
    case "2":
      ret = "Viagem";
      break;
    case 3:
    case "3":
      ret = "Extra";
      break;
    case 4:
    case "4":
      ret = "Turismo";
      break;
  }

  return ret;
}

function selectSentidoViagem(tp)
{
  let ret = " - ";

  switch (tp) {
    case 0:
    case "0":
      ret = "Ida";
      break;
    case 1:
    case "1":
      ret = "Volta";
      break;
    case 2:
    case "2":
      ret = "Unico";
      break;
  }

  return ret;
}

function filtrarPaxGroup()
{

  // let fbr  = $("#grupo").val();
  // let name = $("#inputFilter").val();
  // let name = $("#inputFilter").val();

  // if(name == "" && fbr == "")
  // {
  //   swal({
  //     title: "ATENÇÃO",
  //     text: "Preencha um dos filtros!",
  //     icon: "warning",
  //     button: "Fechar",
  //   });

  //   return false;
  // }

  // //// Busca os dados \\\\
  // $.ajax({
  //   url: "/cadastroPax/seachPax",
  //   method: 'post',
  //   data : { fbr, name },
  //   dataType: 'json',
  //   success:function(ret){
  //   
  //     $("#table tbody").html(ret.html);

  //   }
  // });


}

function openInputFile(idName)
{
  $("#"+idName).trigger('click');
}

function checkCod(name){

  if(name.trim() == ''){

    $("#codigo").prop('disabled', true);


  }else{

    $("#codigo").prop('disabled', false);

  }

}

//serve para validar quando cria e salva passageiros
function createPax(){

  let codigo = $("#codigo");

  const pic_front_smiling_input = $("#pic_front_smiling");

  if(pic_front_smiling_input.length){

    const pic_front_smiling = $(pic_front_smiling_input).val();
    const pic_front_serious = $("#pic_front_serious").val();
    const pic_right_perfil = $("#pic_right_perfil").val();
    const pic_left_perfil = $("#pic_left_perfil").val();

    const eyeglasses = $("#eyeglasses").val();
    const pic_front_smiling_eg = $("#pic_front_smiling_eg").val();
    const pic_front_serious_eg = $("#pic_front_serious_eg").val();
    const pic_right_perfil_eg = $("#pic_right_perfil_eg").val();
    const pic_left_perfil_eg = $("#pic_left_perfil_eg").val();

    if ((pic_front_smiling != 0 || pic_front_serious != 0 || pic_right_perfil != 0 || pic_left_perfil != 0) &&
      (pic_front_smiling == 0 || pic_front_serious == 0 || pic_right_perfil == 0 || pic_left_perfil == 0)) {

      swal({
        title: "ATENÇÃO",
        text: "Por favor, escolha todas as fotos.",
        icon: "warning",
        button: "Fechar",
      });

      return false;

    } else if (eyeglasses == 1 &&
        ((pic_front_smiling != 0 || pic_front_serious != 0 || pic_right_perfil != 0 || pic_left_perfil != 0) &&
        (pic_front_smiling_eg == 0 || pic_front_serious_eg == 0 || pic_right_perfil_eg == 0 || pic_left_perfil_eg == 0))) {

        swal({
          title: "ATENÇÃO",
          text: "Por favor, escolha todas as fotos e suas respectivas com óculos.",
          icon: "warning",
          button: "Fechar",
        });
    
        return false;

    } else if (eyeglasses == 1 && (pic_front_smiling_eg != 0 || pic_front_serious_eg != 0 || pic_right_perfil_eg != 0 || pic_left_perfil_eg != 0) &&
        (pic_front_smiling_eg == 0 || pic_front_serious_eg == 0 || pic_right_perfil_eg == 0 || pic_left_perfil_eg == 0)) {
      
          swal({
            title: "ATENÇÃO",
            text: "Por favor, escolha todas as fotos com óculos.",
            icon: "warning",
            button: "Fechar",
          });

          return false;
    }

  }

  if(codigo.length){
    if ($(codigo).prop('disabled')){
      swal({
        title: "ATENÇÃO",
        text: "O código é obrigatório, preencha o nome para poder colocar o código.",
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }
  
    if($(codigo).val() == ''){
      swal({
        title: "ATENÇÃO",
        text: "O código é obrigatório!",
        icon: "warning",
        button: "Fechar",
      });
      return false;
    }
  }

  $("#createPaxForm").submit();
  
}

function includeNewGroup()
{

  let groupUserID = $("#groupUserID").val();
  let groupNew    = $("#groupNew").val();

  if( groupNew == "")
  {

    swal({
      title: "ATENÇÃO",
      text: "Preencha o nome do novo Grupo!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $.ajax({
    url: "/cadastroPax/newgroup",
    method: 'post',
    data : { groupUserID, groupNew },
    dataType: 'json',
    success:function(ret)
    {

      if(ret.success)
      {
        $("#grupo").prepend('<option value="'+ret.id+'">'+ret.nome+'</option>');
        
        setTimeout( ()=>
        {
          $("#grupo option[value="+ret.id+"]").attr('selected', 'selected');
        }, 1000);
       
        swal({
          title: "ATENÇÃO",
          text: "Grupo Cadastrado com Sucesso!",
          icon: "success",
          button: "Fechar",
        });

      } else {

        swal({
          title: "ATENÇÃO",
          text: ret.msg,
          icon: "warning",
          button: "Fechar",
        });

      }

      $("#modalNewGroup").modal("hide");
    
    }
  });


}

function checkIfExistTag(cod, codExist=0, id = 0)
{

  const resetCode = $("#codigoorigin").length ? $("#codigoorigin").val() : '';

  if (cod != "" && cod != undefined)
  {

    $("#ativo").prop('disabled', false);
    
    $("#carregando").addClass('show');

    $.ajax({
      url: "/cadastroPax/existTag",
      method: 'post',
      data : {
        "cod": cod,
        "id": id,
        "codExist": codExist
      },
      dataType: 'json',
      success:function(data){

        if(data.status){

          if(data.pax){

            if(typeof data.pax === 'object'){

              let oldPax = data.pax.NOME;
              let newPAx = $("#name").val();

              swal({
                title: 'ATENÇÃO',
                text: "Esse Código já está em uso para o(a) Passageiro(a):\n\n"+oldPax+" \n\nDeseja encerrar a vigência atual deste código (mantendo o histórico de batidas), e criar uma nova para o(a) passageiro(a):\n\n"+newPAx,
                icon: 'warning',
                dangerMode: true,
                buttons: {
                  cancel: "Cancelar",
                  confirm: "Confirmar"
                },
              }).then((result) => {
    
                if (result) {
                  //
                } else {
                  $("#codigo").val(resetCode);
                  $("#ativo").val(0).change();
                  $("#ativo").prop('disabled', true);
                }
    
              });

            }
            

          }else{

            swal({
              title: "ERRO",
              text: "Código indisponível e pertence a outro Grupo.",
              icon: "error",
              button: "Fechar",
            }).then(() => {
    
              $("#codigo").val(resetCode);
  
            });

          }

        }else{

          swal({
            title: "ERRO",
            text: "Erro ao checar Código, por favor tente novamente.",
            icon: "error",
            button: "Fechar",
          }).then(() => {
    
            $("#codigo").val("");

          });

        }
        
        
        $("#carregando").removeClass('show');
      }
    });

  }
  
  return true;
}

function setGr(value){
  $('#gr').val(value);
}


if ($('select').length > 0) {
  $('select').not('[multiple]').not('#modelsDevices').not('#devicesDevices')
  .wrap('<div class="position-relative"></div>')
  .select2({
    width:'100%',
    "language": {
     "noResults": function(){
         return "Nenhum resultado encontrado";
     }
    },
      escapeMarkup: function (markup) {
          return markup;
      }
  });
}

$('.filtroSelect2').select2({
  width:'100%',
  minimumResultsForSearch: Infinity
});

$(document).on('click', '.msgsNotification .msgBtn:not(.ativo)', function(){
  $('.msgBtn.ativo').removeClass('ativo');
  $(this).addClass('ativo');
  $('.camposMsgsNotification').addClass('ativo');
  $('#carroatual, #novocarro').val(0).change();
  $('#tempo').val('');
  $('#carroatual option, #novocarro option').prop('disabled', false);
  const title = $(this)[0].childNodes[0].nodeValue.trim();
  const msg = $(this).attr('title');
  const msgId = $(this).attr('msgId');
  $('#title').val(title);
  $('#message').val(msg);
  if(msgId == 1){
    $('.carroatual, .tempo').css('display','block');
    $('.novocarro').css('display','none');
  }
  if(msgId == 2){
    $('.carroatual').css('display','block');
    $('.novocarro').css('display','block');
    $('.tempo').css('display','none');
  }
});

$(document).on('click', '.msgsNotification .msgBtn.ativo', function(){
  $(this).removeClass('ativo');
  $('.camposMsgsNotification').removeClass('ativo');
  $('#carroatual, #novocarro').val(0).change();
  $('#title').val('');
  $('#message').val('');
  $('#tempo').val('');
});

$(document).on('change', '.camposMsgsNotification #carroatual', function(){
  const carroAtual = $(this).val();
  const msgId = $('.msgsNotification .msgBtn.ativo').attr('msgId');
  let msg = $('.msgsNotification .msgBtn.ativo').attr('title');
  $('#novocarro option').prop('disabled', false);
  if(carroAtual != 0){
    const carroAtualTxt = $("option:selected", this).attr('nomeveiculo');
    msg = msg.replace('#CARROATUAL#', carroAtualTxt);
    $(`#novocarro option[value=${carroAtual}]`).prop('disabled', true);
  }

  if(msgId == 1 && $('#tempo').val() != ''){
    msg = msg.replace('#TEMPO#', setAtrasoPush($('#tempo').val()));
  }

  if(msgId == 2 && $('#novocarro').val() != 0){
    msg = msg.replace('#NOVOCARRO#', $("option:selected", $('#novocarro')).attr('nomeveiculo'));
  }
  $('#message').val(msg);
});

$(document).on('change', '.camposMsgsNotification #novocarro', function(){
  const novoCarro = $(this).val();
  const msgId = $('.msgsNotification .msgBtn.ativo').attr('msgId');
  let msg = $('.msgsNotification .msgBtn.ativo').attr('title');
  $('#carroatual option').prop('disabled', false);
  if(novoCarro != 0){
    const novoCarroTxt = $("option:selected", this).attr('nomeveiculo');
    msg = msg.replace('#NOVOCARRO#', novoCarroTxt);    
    $(`#carroatual option[value=${novoCarro}]`).prop('disabled', true);
  }
  if(msgId == 2 && $('#carroatual').val() != 0){
    msg = msg.replace('#CARROATUAL#', $("option:selected", $('#carroatual')).attr('nomeveiculo'));
  }
  $('#message').val(msg);
});

$(document).on('change', '.camposMsgsNotification #tempo', function(){
  const tempoTxt = setAtrasoPush($(this).val());
  let msg = $('.msgsNotification .msgBtn.ativo').attr('title');
  msg = msg.replace('#TEMPO#', tempoTxt); 
  if($('#carroatual').val() != 0){
    msg = msg.replace('#CARROATUAL#', $("option:selected", $('#carroatual')).attr('nomeveiculo'));
  }
  $('#message').val(msg);
});

function setAtrasoPush(tempo){
  let tempoTxt;
  if(tempo == 0){
    $('#tempo').val(1);
    tempoTxt = '1 minuto';
  }
  else if(tempo < 60){
    tempoTxt = `${tempo} minuto${tempo == 1 ? '':'s'}`;
    
  }
  else if(tempo >= 60){
    $('#tempo').val(60);
    tempoTxt = '1 hora';
  }
  return tempoTxt;
};

function sendMessageApp(link)
{

  if($('.msgBtn.ativo').length == 0){
    swal({
      title: "ATENÇÃO",
      text: "Selecione a mensagem que deseja enviar!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  if($("#message").val().includes("#")){
    swal({
      title: "ATENÇÃO",
      text: "Preencha os campos da mensagem!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  if ($("#title").val() == ""){

    swal({
      title: "ATENÇÃO",
      text: "Preencha o Título!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  if ($("#message").val() == ""){

    swal({
      title: "ATENÇÃO",
      text: "Preencha a mensagem!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }


  if ($("#lines").val() == ""){

    swal({
      title: "ATENÇÃO",
      text: "Selecione uma Linha!",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $("#sendFormNotify").attr('action', link);
  $("#sendFormNotify").submit();
}

function boardingPoints(vl)
{

  $("#dot").html('<option value="">Selecione</option>');
  
  if (vl != ""){

    $.ajax({
      url: "/notifications/boardingPoints",
      method: 'post',
      data : { vl },
      dataType: 'json',
      success:function(ret){
      
        $("#dot").html(ret.html);
  
      }
    });

  }

}

function gerarRelatorioReportRate(url, ajax = 0)
{

  let error = "";

  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if(
    $("#data_inicio").val() != "" && 
    $("#data_fim").val() != "" && 
    getDiffDates($("#data_fim").val(), $("#data_inicio").val()) > relDays
    ){
      swal({
        title: "ATENÇÃO",
        text: relDaysMsg,
        icon: "warning",
        button: "Fechar",
      });
    return false;
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let lines       = $("#lines").val();
  let veiculo     = $("#veiculo").val();
  
  if(ajax == 0) {

    window.open(url + "?dti="+data_inicio+"&dtf="+data_fim+"&lines="+lines+"&veiculo="+veiculo, '_blank');

  } else {

    $("#carregando").addClass('show');
    
    $("#bodyTable").html(''); 
    $("#paginacao").html("");

    $.ajax({
      url: url,
      method: 'post',
      data : {
        "data_inicio": data_inicio,
        "data_fim": data_fim,
        "lines":lines,
        "veiculo":veiculo
      },
      dataType: 'json',
      success:function(data){

        if(data.html){

          $("#bodyTable").html(data.html);

        } else {

          if(data.error != undefined){
            swal({
              title: "ATENÇÃO",
              text: data.error,
              icon: "warning",
              button: "Fechar",
            });
            $("#carregando").removeClass('show');
          } else {
            swal({
              title: "ATENÇÃO",
              text: "Nenhum resultado encontrado para os filtros usados!",
              icon: "warning",
              button: "Fechar",
            });
            $("#carregando").removeClass('show');
          }
          
          return false;
        }
        
        $("#carregando").removeClass('show');
      }
    });

    } /// END ELSE \\\

  setTimeout(()=>{
    $("#carregando").removeClass('show');
  }, 180000);
}

function setActiveMenu(qual){

  if(!$('.pageTitle i').length){
    let fatherTxt = false; 
    const subMenu =  $('.subMenu').find(`a[href="${qual}"]`);
    const father = $(subMenu).closest('.menuItem');

    const inTxt = $(subMenu).text().trim();

    if(father){
      $(father).addClass('current');
      fatherTxt = $(father).find('.mnon').first().text().trim();
    }

    $(subMenu).addClass('currentSub');
    if($('.pageTitle').length){
      const setIcon = $(subMenu).find('i').attr('class').replace('mnon', '');
      const setText = `<b class="h4">&#10148; ${inTxt}</b>`;      
      $('.pageTitle').prepend(`<i class="${setIcon}"></i> ${fatherTxt ? `${fatherTxt}` : ''} ${setText}`);
      setNotifyMsgs(inTxt);
    }
  }

}

window.addEventListener("wheel", event => {

  if($(event.target).closest('.newMenu').length == 1){
    event.preventDefault();
    event.stopPropagation();
    newMenuScroll(event);
  }

},{passive: false});

window.addEventListener("scroll", event => {
  if ($("#createPaxForm").length) {
    if (document.activeElement.id === "name" || document.activeElement.id === "codigo") {
      if (document.activeElement.value.trim() !== "") {
        document.activeElement.blur();
      }
    }
  }
}, { passive: false });


function newMenuScroll(evt) {

  const delta = Math.sign(evt.deltaY) * (-120);
  
  if (delta <= -30) {
    $('.newMenu').scrollTop( $('.newMenu').scrollTop() + 30 ); 
  }
  
  if (delta >= 30) {
    $('.newMenu').scrollTop( $('.newMenu').scrollTop() - 30 ); 
    
  }
}

$(document).on('mouseenter', '.menuItem', function(){
  $('.newMenu').addClass('expand');
  $(this).addClass('active');
  const subMenu = $(this).find('.subMenu');
  if(subMenu.length){
    $(subMenu).removeClass('subBottom');
    const subMenuHeight = $(subMenu)[0].getBoundingClientRect().height;
    const subMenuYposition = $(subMenu)[0].getBoundingClientRect().y;
    const newMenuPaddingBottom = parseInt($('.newMenu').css('padding-bottom'));
    if((subMenuYposition + subMenuHeight + newMenuPaddingBottom) > window.innerHeight){
      $(subMenu).addClass('subBottom');
    }
  }
  if(($(window).width() >= 769)){
    const elHeigh = $(this)[0].getBoundingClientRect().height;
    const elYPosition = $(this)[0].getBoundingClientRect().y;
    const newMenuPaddingBottom = parseInt($('.newMenu').css('padding-bottom'));
    const newMenuGap = parseInt($('.newMenu').css('gap'));
    if(elYPosition < elHeigh){
      $('.newMenu').animate({scrollTop: 
        $(this)[0].offsetTop - (elHeigh + newMenuGap)
      }, 300);
    }
    if(elYPosition + elHeigh > $('.newMenu').height() + newMenuPaddingBottom + elHeigh + newMenuGap){
      $('.newMenu').animate({scrollTop: 
        $('.newMenu').scrollTop() + elHeigh + newMenuGap
      }, 300);
    }
  }
  
});

$(document).on('mouseleave', '.menuItem', function(){
  $('.newMenu').removeClass('expand');
  $(this).removeClass('active');
});

$(document).on('change', '#appRegisterSelect', function(){
  const solicitar = $(this).val();
  if(solicitar == 0){
    $('#embarqueQrDiv').css('display','block');
    $('#solicitaCadastroDiv').css('display','none');
  }
  if(solicitar == 1){
    $('#embarqueQrDiv').css('display','none');
    $('#solicitaCadastroDiv').css('display','block');
    $('#embarqueQrDiv select').val(0).change();
  }

  $('#appRegisterSelectTi').html($(this).find(":selected").text());
});

$(document).on('change', '#embarqueQrDiv select', function(){
  const embarqueQr = $(this).val();
  if(embarqueQr == 1){
    $('#holdEmbarqueQr').css('display','block');
  }
  if(embarqueQr == 0){
    $('#holdEmbarqueQr').css('display','none');
    $('#mostraSentidoDiv select').val(0).change();
    $('#exigeCadDiv select').val(0).change();
    $('#exigeMotiveDiv select').val(0).change();
    $('#beep_embarque').val(1);
    $('#beep_desembarque').val(1);
  }
});

$('#beep_embarque_btn').on('click', function(){

  const setVal = $('#beep_embarque').val() == 1 ? 0 : 1;
  $('#beep_embarque').val(setVal);

  $(this).addClass(setVal == 1 ? 'fa-volume-up' : 'fa-volume-mute');
  $(this).removeClass(setVal == 1 ? 'fa-volume-mute' : 'fa-volume-up');

});

$('#beep_desembarque_btn').on('click', function(){

  const setVal = $('#beep_desembarque').val() == 1 ? 0 : 1;
  $('#beep_desembarque').val(setVal);

  $(this).addClass(setVal == 1 ? 'fa-volume-up' : 'fa-volume-mute');
  $(this).removeClass(setVal == 1 ? 'fa-volume-mute' : 'fa-volume-up');

});


function selectUserLinha(){

  const idOrigin = $(`#groupUserID option:selected`).attr('idorigin');

  $('#selectLinhaUser option').prop('selected', false);
  $('#selectLinhaUserPerm option').prop('selected', true);

  removerPermissao('LIN');

  if(idOrigin){

    $(`#selectLinhaUser option[grlinhaid="${idOrigin}"]`).prop('selected', true);

    concederPermissao('LIN');
  }

}

function selectUserLinhaFilter(){

  const idOrigin = $(`#groupUserIDLinhaOut option:selected`).attr('idorigin');
  
  if(idOrigin){

    $(`#selectLinhaUser .nomeGrupoSelect`).fadeOut();
    $(`#selectLinhaUser option`).fadeOut();

    let totalGrupo = $(`#selectLinhaUser option[grlinhaid=${idOrigin}]`).length;
    if(totalGrupo != 0){
      $(`#selectLinhaUser .nomeGrupoSelect[grlinhaidnome=${idOrigin}]`).fadeIn();
      $(`#selectLinhaUser option[grlinhaid=${idOrigin}]`).fadeIn();
    }

  }else{

    $("#selectLinhaUser .nomeGrupoSelect").each(function() {
      
      let grupoId = $(this).attr('grlinhaidnome');
      console.log(grupoId);
      let totalGrupo = $(`#selectLinhaUser option[grlinhaid=${grupoId}]`).length;

      if(totalGrupo != 0){
        $(this).fadeIn();
        $(`#selectLinhaUser option[grlinhaid=${grupoId}]`).fadeIn();
      }

    });

  }
  
}

function checkUserUpdates(qual){

  if(qual == "menus"){
    
    const originMenus = $('input[name^="menusUser"]:checked:enabled').serialize();
    $('#orginMenus').val(originMenus);
    
  }
}

function copyPass(campo)
{

  let texto = $(`#${campo}`).val();
  if(texto == '')
  return;

  const originalType =  $(`#${campo}`).attr('type');

  $(`#${campo}`).attr('type', 'text');

  let copyText = document.getElementById(campo);

  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");

  $(`#${campo}`).attr('type', originalType);

  swal({
    title: "SUCESSO",
    text: "Senha copiada com sucesso!",
    icon: "success",
    button: "Fechar",
  });

}

function genPassword(passwordLength, campo){

  const chars = '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  let password = '';

  for (var i = 0; i <= passwordLength; i++) {
  var randomNumber = Math.floor(Math.random() * chars.length);
  password += chars.substring(randomNumber, randomNumber +1);
  }

  $(campo).val(password);
}

function showHidePass(btn, campo){

  const type = $(campo).attr('type');

  if(type == 'password'){
    $(campo).attr('type', 'text');
    $(btn).attr('class','fas fa-eye-slash');
    $(btn).attr('title', 'Ocultar Senha');
  }else{
    $(campo).attr('type', 'password');
    $(btn).attr('class','fas fa-eye');
    $(btn).attr('title', 'Mostrar Senha');
  }

}

function criarUsuario(e){

  let name = $('#name').val();
  if(name == '')
  return;

  let email = $('#email').val();
  if(email == '')
  return;

  let password = $('#password').val();
  if(password == '')
  return;

  e.preventDefault();

  $("#carregando").addClass('show');
  
  let data = $('form').serializeArray();

  $.ajax({
    url: "/usuarios/salvarAjax",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      }).then(() => {

        if(ret.status){
          window.location.href = "/usuarios/";
        }
        
      });

      $("#carregando").removeClass('show');
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        }).then(() => {
          window.location.href = "/usuarios";
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

function atualizarUsuario(e){

  let email = $('#email').val();
  if(email == '')
  return;

  let name = $('#name').val();
  if(name == '')
  return;

  e.preventDefault();

  $("#carregando").addClass('show');
  
  let data = $('form').serializeArray();

  //checar se deve atualizar menus
  let updateMenus = 0;

  const userType = $('#type option:selected').val();

  if(userType == 2){
    const orginMenus = $('#orginMenus').val();
    const menus = $('input[name^="menusUser"]:checked:enabled').serialize();
    updateMenus = orginMenus != menus ? 1 : 0;
  }
  
  data.push({name:'updateMenus', value: updateMenus});

  
  //checar se deve atualizar linhas
  let originLinhas = $('#orginLinhas').val();
  let linhas = $('#idsLinhaPermission').val();

  originLinhas = originLinhas.split(",").sort().join(",");
  linhas = linhas.split(",").sort().join(",");
  let updateLinhas = originLinhas != linhas ? 1 : 0;
  data.push({name:'updateLinhas', value: updateLinhas});
  

  //checar se deve atualizar carros
  let originCarros = $('#orginCarros').val();
  let carros = $('#idsCardPermission').val();

  originCarros = originCarros.split(",").sort().join(",");
  carros = carros.split(",").sort().join(",");
  let updateCarros = originCarros != carros ? 1 : 0;
  data.push({name:'updateCarros', value: updateCarros});


  //checar se deve atualizar grupos
  let originGrupos = $('#orginGrupos').val();
  let grupos = $('#idsGrupoPermission').val();

  originGrupos = originGrupos.split(",").sort().join(",");
  grupos = grupos.split(",").sort().join(",");
  let updateGrupos = originGrupos != grupos ? 1 : 0;
  data.push({name:'updateGrupos', value: updateGrupos});
  

  $.ajax({
    url: "/usuarios/atualizarAjax",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      }).then(() => {

        if(ret.status){
          window.location.href = "/usuarios/";
        }
        
      });

      $("#carregando").removeClass('show');
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        }).then(() => {
          window.location.href = "/usuarios";
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

function atualizarUsuarioDados(e){

  let email = $('#email').val();
  if(email == '')
  return;

  let name = $('#name').val();
  if(name == '')
  return;

  e.preventDefault();

  let passwordAtual = $('#passwordAtual').val();
  let password = $('#password').val();

  if(password != '' && passwordAtual == ''){
    swal({
      title: "ATENÇÃO",
      text: "Para definir uma nova senha é necessário informar a senha atual!",
      icon: "warning",
      button: "OK",
    });

    return;
  }

  $("#carregando").addClass('show');
  
  let data = $('form').serializeArray();

  $.ajax({
    url: "/usuarios/usuarioDados",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      if(ret.status){
        $('#nomeBoasVindas').html(name);
      }

      let htmlContent = document.createElement("div");
      htmlContent.innerHTML = `<div>${ret.text}</div>`;

      swal({
        title: ret.title,
        content: htmlContent,
        icon: ret.icon,
        button: ret.button,
      }).then(() => {

        if(ret.status){
          window.location.href = "/";
        }
        
      });

      $("#carregando").removeClass('show');
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        }).then(() => {
          window.location.href = "/";
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

function showInsertCode(code = false){

  code = code ? code : '';

  let insertCodeContent = document.createElement("div");
  insertCodeContent.innerHTML = `<div class="row">
  <div class="col-12 col-md-8">
    <input id="tokenCodePass" type="text" class="form-control" name="tokenCodePass" value="${code}" autocomplete="off" autofocus="" placeholder="Código">
  </div>
    <div class="col-12 col-md-4">
      <button onclick="passwordUpdate()" type="button" class="btn btn-success form-control">VALIDAR</button>
    </div>
  </div>`;

  swal({

    title: "Insira o código:",
    content: insertCodeContent,
    buttons: false,
    closeOnClickOutside: false,
    closeOnEsc: false,
  
  });

}

function savePassUpdate(){

  const npassword = $('#npassword').val();
  const rnpassword = $('#rnpassword').val();

  if(npassword == '' || rnpassword == ''){
    return;
  }

  if(rnpassword !== npassword){

    swal({
      title: "ATENÇÃO",
      text: "As senhas não estão iguais",
      icon: "warning",
      button: "OK",
    }).then(() => {

      showChangePassword(npassword, rnpassword);

    });
  
    return;

  }

  let email = $('#email').val();
  
  $("#carregando").addClass('show');

  $.ajax({
    url: "/usuarios/passwordReset",
    method: 'post',
    data: {email:email, password:rnpassword},
    dataType: 'json',
    success:function(ret){

      swal({

        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,

      }).then(() => {

        if(ret.status){
          window.location.href = "/";
        }
  
      });

      $("#carregando").removeClass('show');
               
    },error: function(){
      
      swal({
        title: "ERRO",
        text: "Ocorreu um erro, tente novamente!",
        icon: "error",
        button: "Fechar",
      });

      $("#carregando").removeClass('show');
    }
  });

}

function showChangePassword(npassword = false, rnpassword = false){

  npassword = npassword ? npassword : '';
  rnpassword = rnpassword ? rnpassword : '';

  let resetPassContent = document.createElement("div");
  resetPassContent.innerHTML = `<div class="row">
  <div class="col-12" style="margin: auto;">
      <div class="userCadPass" style="--btnNumber: 1">
          <input value="${npassword}" id="npassword" type="password" class="form-control" name="npassword" required autocomplete="off" placeholder="Nova Senha">
          <i style="color: #252633 !important; border-color: #252633 !important;" onclick="showHidePass(this, '#npassword')" class="fas fa-eye" title="Mostar Senha"></i>
      </div>
  </div>
  <div class="col-12" style="margin: auto;">
      <div class="userCadPass" style="--btnNumber: 1">
          <input value="${rnpassword}" id="rnpassword" type="password" class="form-control" name="rnpassword" required autocomplete="off" placeholder="Repetir Nova Senha">
          <i style="color: #252633 !important; border-color: #252633 !important;" onclick="showHidePass(this, '#rnpassword')" class="fas fa-eye" title="Mostar Senha"></i>
      </div>
  </div>
  <div class="col-12 mt-3">
    <button onclick="savePassUpdate()" type="button" class="btn btn-success form-control">SALVAR</button>
  </div>
  </div>`;

  swal({

    title: "Redefina sua senha abaixo:",
    content: resetPassContent,
    buttons: false,
    closeOnClickOutside: false,
    closeOnEsc: false,
  
  });

}

function passwordUpdate(){

  let email = $('#email').val();

  let code = $('#tokenCodePass').val();

  if(code == ''){

    swal({
      title: "ATENÇÃO",
      text: "Insira o código!",
      icon: "warning",
      button: "OK",
    }).then(() => {

      showInsertCode();

    });
  
    return;
    
  }

  $("#carregando").addClass('show');

  $.ajax({
    url: "/usuarios/passwordReset",
    method: 'post',
    data: {email:email, code:code},
    dataType: 'json',
    success:function(ret){

      let htmlContent = document.createElement("div");
      htmlContent.innerHTML = `<div>
      ${ret.text}</div>`;

      if(ret.status){

        showChangePassword();

      }else{

        swal({

          title: ret.title,
          text: ret.text,
          icon: ret.icon,
          button: ret.button,
  
        }).then(() => {

          showInsertCode(code);
    
        });

      }

      $("#carregando").removeClass('show');
               
    },error: function(){
      
      swal({
        title: "ERRO",
        text: "Ocorreu um erro, tente novamente!",
        icon: "error",
        button: "Fechar",
      });

      $("#carregando").removeClass('show');
    }
  });

}

function passwordReset(e) {

  let email = $('#email').val();

  if(email == ''){
    swal({
      title: "ATENÇÃO",
      text: "Por favor informe seu e-mail para poder redefinir sua senha!",
      icon: "warning",
      button: "OK",
    });

    $('#hasCode').val(0);
  
    return;
  }

  e.preventDefault();

  const hasCode = $('#hasCode').val();

  if(hasCode == 1){

    showInsertCode();
    return;

  }

  $("#carregando").addClass('show');

  $.ajax({
    url: "/usuarios/passwordReset",
    method: 'post',
    data: {email:email},
    dataType: 'json',
    success:function(ret){

      let htmlContent = document.createElement("div");
      htmlContent.innerHTML = `<div>${ret.text}</div>`;

      swal({

        title: ret.title,
        content: htmlContent,
        icon: ret.icon,
        button: ret.button,

      }).then(() => {

        if(ret.status){

          $('#hasCode').val(1);

          showInsertCode();

        }
        
      });

      $("#carregando").removeClass('show');
               
    },error: function(){
      
      swal({
        title: "ERRO",
        text: "Ocorreu um erro, tente novamente!",
        icon: "error",
        button: "Fechar",
      });

      $("#carregando").removeClass('show');
    }
  });

}

function checkAllDados(){

  const allUsers = $("input[name='alterarDados[]']").length;
  const allChecked = $("input[name='alterarDados[]']:checked").length;

  $('#altDadosTodos').val(allUsers == allChecked ? 1 : 0);
  $('#btnAltDados').attr('title', allUsers == allChecked ? 'Limpar Todos' : 'Selecionar Todos');

}

function setAltDados(){

  const ids = $("input[name='alterarDados[]']")
    .map(function(){return $(this).val();}).get();

  if(ids.length == 0)
  return;
  
  const allEmpty = $('#altDadosTodos').val() == 0 ? true : false;

  const addRemove = allEmpty ? 1 : 0;

  $("#carregando").addClass('show');
  $('#altDadosTodos').val(2);

  $.ajax({
    url: "/usuarios/canAltDados",
    method: 'post',
    data: {addRemove:addRemove, userIDs:ids},
    dataType: 'json',
    success:function(ret){

      let htmlContent = document.createElement("div");
      htmlContent.innerHTML = `<div>
      ${ret ? `Permissão ${addRemove == 1 ? 'concedida' : 'removida'} com sucesso` :
      `Erro ao ${addRemove == 1 ? 'conceder' : 'remover'} permissão` 
      }
      ${addRemove == 1 ? 'para: ' : 'de: '}
      <br>
      <b>${ids.length} ${ids.length == 1 ? 'usuário' : 'usuários'}</b>
      </div>`;

      if(ret){
        $('.setAltDadosSingle').prop('checked', allEmpty).change();
      }

      swal({

        title: ret ? 'SUCESSO' : 'ERRO',
        content: htmlContent,
        icon: ret ? 'success' : 'error',
        button: 'OK',

      }).then(() => {

        $('#altDadosTodos').val(allEmpty ? 1 : 0);
        $('#btnAltDados').attr('title', allEmpty ? 'Limpar Todos' : 'Selecionar Todos');
  
      });

      $("#carregando").removeClass('show');
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });
}

function setAltDadosSingle(element, id){

  const altDadosTodos = $('#altDadosTodos').val()
  const canChange = altDadosTodos == 2 ? false : true;

  if(!canChange)
  return;

  const addRemove = $(element).is(':checked') ? 1 : 0;

  const nome = $(`td[id="userName-${id}"]`).html();
  $("#carregando").addClass('show');
  $('#altDadosTodos').val(2);
  
  $.ajax({
    url: "/usuarios/canAltDados",
    method: 'post',
    data: {addRemove:addRemove, userID:id},
    dataType: 'json',
    success:function(ret){

      let htmlContent = document.createElement("div");
      htmlContent.innerHTML = `<div>
      ${ret ? `Permissão ${addRemove == 1 ? 'concedida' : 'removida'} com sucesso` :
      `Erro ao ${addRemove == 1 ? 'conceder' : 'remover'} permissão` 
      }
      ${addRemove == 1 ? 'para: ' : 'de: '}
      <br>
      <b>${nome}</b>
      </div>`;

      if(!ret){
        $(element).prop('checked', addRemove == 1 ? false : true).change();
      }

      swal({

        title: ret ? 'SUCESSO' : 'ERRO',
        content: htmlContent,
        icon: ret ? 'success' : 'error',
        button: 'OK',

      }).then(() => {

        $('#altDadosTodos').val(altDadosTodos);
  
      });

      $("#carregando").removeClass('show');
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

$(document).on('dblclick', '.toMark', function(){
  const recId = $('#recId').val();
  const paxId = $(this).attr('paxId');
  const paxName = $(this).attr('paxName') ?? false;
  const isMarked = $(this).hasClass('marked');
  if(recId && paxId && !isMarked){
    $('.toMark').removeClass('marked');
    askRecognitionSet(recId, paxId, paxName);
  }
  $(this).toggleClass('marked');
});

function getStatisticsApp(){
  let start = $("#start").val();
  let end    = $("#end").val();

  if (start > end) {
    swal({
      title: "ATENÇÃO",
      text: "O Mês Início não pode ser maior que o Mẽs Fim!",
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  $("#getStatistics").submit();
}


function statisticsAppExcel(){


  let groupId = $("#groupId").val();
  let qrcode  = $("#qrcode").val();
  let codigo  = $("#codigo").val();
  let nomegr  = $("#nomegr").val();
  let start   = $("#start").val();
  let end     = $("#end").val();

  if (start > end) {
    swal({
      title: "ATENÇÃO",
      text: "O Mês Início não pode ser maior que o Mẽs Fim!",
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  $("#getExcel").remove();
  const src = `/app/statisticsAppexcel?groupId=${groupId}&qrcode=${qrcode}&codigo=${codigo}&nomegr=${nomegr}&start=${start}&end=${end}`;
  $('body').append(`<iframe style="display:none" src="${src}" name="getExcel" id="getExcel"></iframe>`);
  iniDownRels('excelStatisticsApp');

}

//funções excel estatísticas totens
function statisticsTotemExcel(){


  let groupId = $("#groupId").val();
  let nomegr  = $("#nomegr").val();
  let start   = $("#start").val();
  let end     = $("#end").val();

  if (start > end) {
    swal({
      title: "ATENÇÃO",
      text: "O Mês Início não pode ser maior que o Mẽs Fim!",
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  $("#getExcel").remove();
  const src = `/configuracoes/statisticsTotemExcel?groupId=${groupId}&nomegr=${nomegr}&start=${start}&end=${end}`;
  $('body').append(`<iframe style="display:none" src="${src}" name="getExcel" id="getExcel"></iframe>`);
  iniDownRels('excelStatisticsTotem');

}

function statisticsTotemPassageiroExcel(){
}

function statisticsTotemPassageiroEspecialExcel(){
}

function statisticsTotemEuroExcel(){
}
//fim funções excel estatísticas totens

function addDays(date, months = 1, days = false) {
  
  let dateCopy = new Date(date);
  const today = new Date();

  if(days){

    dateCopy.setDate(dateCopy.getDate() + days);

  }else{

    dateCopy.setDate(dateCopy.getDate() + 1);
    dateCopy.setMonth(dateCopy.getMonth() + months);

  }
  
  if(dateCopy > today){
    dateCopy = today;
  }
  
  let year = dateCopy.toLocaleString("default", { year: "numeric" });
  let month = dateCopy.toLocaleString("default", { month: "2-digit" });
  let day = dateCopy.toLocaleString("default", { day: "2-digit" });

  const formattedDate = `${year}-${month}-${day}`;

  return formattedDate;
}

$(document).on('change', '#data_inicio', function(){
  
  const isSintetico = $('#relSintetico').length ? true : false;
  const isConsolidado = $('#relConsolidado').length ? true : false;
  const isEmbSemCartao = $('#relEmbSemRfid').length ? true : false;
  const isAnalitico = $('#relAnalitico').length ? true : false;

  $("#data_fim").attr('min', $(this).val());
  // $("#data_fim").val($(this).val()).change();
  $("#data_fim").val('').change();

  if(isSintetico || isConsolidado || isEmbSemCartao){

    $("#data_fim").attr('max', addDays($(this).val(), 1));

  }

  if(isAnalitico){

    const hasMatricula = $('#matricula').val() != '';
    if(hasMatricula){
      $("#data_fim").attr('max', addDays($(this).val(), relMonth));
    }else{
      $("#data_fim").attr('max', addDays($(this).val(), 1 ,relDays));
    }
    
  }
  
});

$(document).on('change', '#data_fim', function(){


  const isAnalitico = $('#relAnalitico').length ? true : false;
  const isSintetico = $('#relSintetico').length ? true : false;
  const isConsolidado = $('#relConsolidado').length ? true : false;
  const isPaxTag = $('#cad_pax_tag').length ? true : false;

  if($(this).val() == ''){
    return;
  }

  let agenda = false;

  if((isAnalitico || isConsolidado || isSintetico) && isPaxTag){
    agenda = true;
  }

  if(agenda){

    let namesca = '';
    let namesc = '';

    let agendar;

    if(isAnalitico){

      const hasMatricula = $('#matricula').val() != '';

      if(hasMatricula){
        agendar = false;
      }else{
        agendar = getDiffDates($("#data_fim").val(), $("#data_inicio").val()) != 0 ? true : false;
      }

    }else{

      agendar = getDiffDates($("#data_fim").val(), $("#data_inicio").val()) > (relDays-1) ? true : false;
    
    }

    if(agendar){
      $('.avisarContainer').addClass('hideAviso');
    }else{
      $('.avisarContainer').removeClass('hideAviso');
    }

    if(isAnalitico){
      
      if(agendar){
        clearInterval(atualizarRelAnalitico);
        atualizarRelAnalitico = null;
      }else{
        if(atualizarRelAnalitico === null){
          atualizarRelAnalitico = setInterval(()=>{
            gerarRelatorioAnalitics(true);
          }, timeAtualiza);
        }
      }
      
      namesca = "agendarAnalitico()";
      namesc = "gerarRelatorioAnalitics()";

    }

    if(isConsolidado){

      if(agendar){
        clearInterval(atualizarRelConsolidado);
        atualizarRelConsolidado = null;
      }else{
        if(atualizarRelConsolidado === null){
          atualizarRelConsolidado = setInterval(()=>{
            gerarRelatorioConsolidado(true);
          }, timeAtualiza);
        }
      }
      
      namesca = "agendarConsolidado()";
      namesc = "gerarRelatorioConsolidado()";

    }

    if(isSintetico){

      if(agendar){
        clearInterval(atualizarGerarRelatorioSintetico);
        atualizarGerarRelatorioSintetico = null;
      }else{
        if(atualizarGerarRelatorioSintetico === null){
          atualizarGerarRelatorioSintetico = setInterval(()=>{
            gerarRelatorioSintetico(true);
          }, timeAtualiza);
        }
      }
        
      namesca = "agendarSintetico()";
      namesc = "gerarRelatorioSintetico()";

    }

    $('.btnBuscar').attr('title', agendar ? 'Agendar': 'Buscar');
    $('.btnBuscar').attr('onclick', agendar ? namesca : namesc);
    $('.btnBuscar i').attr('class', agendar ? 'fas fa-clipboard-list' : 'fa fa-search');
    $('.btnBuscar b').html(agendar ? 'Agendar': 'Buscar');

  }

});

$(document).on('blur', '#matricula', function(){

  const isAnalitico = $('#relAnalitico').length ? true : false;

  if(isAnalitico){
    
    const data_inicio = $('#data_inicio');
    const data_inicio_val = $(data_inicio).val();
    $(data_inicio).val(data_inicio_val).trigger('change');
    $('#data_fim').val(data_inicio_val).trigger('change');

    $('#relAnaliticoAvisoPass').css('display', $(this).val() !== '' ? 'flex' : 'none');
    $('#relAnaliticoAviso').css('display', $(this).val() === '' ? 'flex' : 'none');

  }

});

function notifyReady(screen = true, msg = ''){

  if($("#notifyReady").length && $("#notifyReady").is(':checked')){


    let theBody;

    if(msg == ''){
      theBody = screen ? $("#notificaScreen").val() : $("#notificaDownload").val();
    }else{
      theBody = msg;
    }
    
    const ut = new SpeechSynthesisUtterance(theBody);
    window.speechSynthesis.speak(ut);

    const title = portalName;

    const options = {
      body: theBody,
      lang: "pt-BR",
      vibrate: [200, 100, 200],
      icon: "/assets/images/notifyReadyIcon.png",
      badge: "/assets/images/notifyReadyIcon.png",
    };
    
    const notification = new Notification(title, options);
    notification.onclick = function () {
      window.focus();
    };
  }

}

function notifyReadyPermission(){
  if (!("Notification" in window)) {
    swal({
      title: "ATENÇÃO",
      text: "Seu navegador não suporta notificações na área de trabalho",
      icon: "warning",
      button: "Fechar",
    });

    $("#notifyReady").prop('checked', false).change();
  } else if (Notification.permission === "granted") {
    swal({
      title: "CONFIRMADO",
      text: `O ${portalName} irá avisar quando estiver tudo pronto!`,
      icon: "success",
      button: "OK",
    });
    
  } else if (Notification.permission !== "denied") {
    swal({
      title: "ATENÇÃO",
      text: `Permita notificações na área de trabalho para o ${portalName} poder avisar quando estiver tudo pronto.`,
      icon: "warning",
      button: "Fechar",
    });
    Notification.requestPermission().then((permission) => {
      if (permission === "granted") {
        swal({
          title: "CONFIRMADO",
          text: `O ${portalName} irá avisar quando estiver tudo pronto!`,
          icon: "success",
          button: "OK",
        });
      }else{
        $("#notifyReady").prop('checked', false).change();
      }
    });
  } else if (Notification.permission === "denied") {
    swal({
      title: "ATENÇÃO",
      text: `Parece que você, previamente, não permitiu que o ${portalName} enviasse notificações. \n Por favor altere as configurações manualente.`,
      icon: "warning",
      button: "Fechar",
    });

    $("#notifyReady").prop('checked', false).change();
  }
}

$(document).on('change', '#notifyReady', function(){
  
  if($(this).is(':checked')){
    notifyReadyPermission();
  }
 
});

function buildExcel(body, n = false, filterdOnly = false){

  iniDownRels();

  let name = n ? n : $('#downloadName').val();

  name = filterdOnly ? `${name} - Filtrado` : name;

  let hasTopHeader = $('.topHeader').length ? true : false;

  let topHead;

  if(hasTopHeader){
    topHead = '<tr>';

    $('.topHeader').find('th').each(function(){
      let colspan = $(this).prop("colSpan");
      let rowspan = $(this).prop("rowSpan");

      const especialcolspan = $(this).attr('especialcolspan');
      const especialrowspan = $(this).attr('especialrowspan');

      if (typeof especialcolspan !== 'undefined' && especialcolspan !== false) {
        colspan = especialcolspan;
      }

      if (typeof especialrowspan !== 'undefined' && especialrowspan !== false) {
        rowspan = especialrowspan;
      }

      topHead += `<th align="center" valign="middle" colspan="${colspan}" rowspan="${rowspan}">${$(this).text()}</th>`;
    });

    topHead += '</tr>';
  }
  
  let thead = '<tr>';

  $('.headExcel').find('th').each(function(){
    thead += `<td>${$(this).text()}</td>`;
  });

  thead += '</tr>';

  let tab_text=`<table style='color:black; border:1px solid black;' color='black' border='1px'>${hasTopHeader ? `${topHead}` : ''}${thead}`;
  let j=0;
  tab = document.getElementById(body);

  for(j = 0 ; j < tab.rows.length ; j++) {   
    let row = tab.rows[j];
    let rowText = '<tr>';

    for (let i = 0; i < row.cells.length; i++) {
      let cell = row.cells[i];
      let cellContent;

      if ($(cell).find('.picListRel').length > 0) {
        cellContent = `Só pode ser vista no ${portalName}`;
      }else{
        cellContent = $(cell).html().replace(/<br\s*\/?>/gi, ' - ');
      }

      let colspan = cell.getAttribute('colspan');
      if (colspan) {
        rowText += `<td align="center" valign="middle" colspan="${colspan}">${cellContent}</td>`;
      } else {
        rowText += `<td>${cellContent}</td>`;
      }
    }

    rowText += '</tr>';

    if ((filterdOnly && !$(row).hasClass('dn')) || !filterdOnly) {
      tab_text += rowText;
    }
  }

  // for(j = 0 ; j < tab.rows.length ; j++) {   
    
  //   if ((filterdOnly && !$(tab.rows[j]).hasClass('dn')) || !filterdOnly) {
  //     tab_text += tab.rows[j].innerHTML+"</tr>";
  //   }

  // }

  tab_text=tab_text+"</table>";
  tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");
  tab_text= tab_text.replace(/<img[^>]*>/gi,"");
  tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, "");

  const universalBOM = "\uFEFF";
  let a = window.document.createElement('a');
  a.setAttribute('href', 'data:application/vnd.ms-excel; charset=utf-8,' + encodeURIComponent(universalBOM+tab_text));
  a.setAttribute('download', `${name}.xls`);
  window.document.body.appendChild(a);
  a.click();
  a.remove();

  setTimeout(() => {
    endDownRels(true);
  }, 200);

}

function downloadRelScreen(body, n = false){

  let tab = document.getElementById(body);
  let numDnRows = $(tab).find('tr.dn').length;
  let totalRows = $(tab).find('tr').length;

  if (numDnRows > 0 && (totalRows > numDnRows)) {

    swal({
      title: 'ATENÇÃO',
      text: "Deseja fazer o download completo ou somente dos dados filtrados?",
      icon: 'warning',
      dangerMode: true,
      buttons: {
        filtrado: {
          text: "Filtrado",
          className:'btn-warning',
          value: "filtrado",
        },
        completo: {
          text: "Completo",
          className:'btn-success',
          value: "completo",
        }
      },
    }).then((value) => {

      if(value == 'filtrado'){
        buildExcel(body, n, true);
      }

      if(value == 'completo'){
        buildExcel(body, n);
      }
      
    });

    return false;

  }else{

    buildExcel(body, n);
    
  }

}

function timingRels(end = false, color){

  if(end){
    // $('.timingRels').css('width','100%');
    // $('.timingRels').css('background-color', color);
    // clearInterval(timeTimingRels);

    if(timerRels){

      clearInterval(timerRels);
      setTimeout(() => {

        $('.relsCronometer').removeClass('show');

        setTimeout(() => {
          $('.relsCronometer').text('00:00:00');
        }, 500);

      }, 1000);

    }
    
    // setTimeout(() => {
    //   $('.timingRels').css('width','1px');
    //   $('.timingRels').css('background-color','red');
    //   $('.timingRels').removeClass('show');
    // }, 1000);

    $("#carregando").removeClass('show');
    return;
  }

  // $('.timingRels').addClass('show');
  // w = 2;
  // timeTimingRels = setInterval(() => {
  //   w = w > 98 ? 2 : (w + 2);
  //   $('.timingRels').css('width',`${w}%`);
  // }, 1000);

  if(('.relsCronometer').length){
    startRelTimer();
  }
  
}

function startRelTimer() {

    $('.relsCronometer').addClass('show');
    var hours = 0,
        minutes = 0,
        seconds = 0;
    
        timerRels = setInterval(function() {
        seconds++;
        if (seconds >= 60) {
            seconds = 0;
            minutes++;
            if (minutes >= 60) {
                minutes = 0;
                hours++;
            }
        }

        var formattedTime = pad(hours) + ":" + pad(minutes) + ":" + pad(seconds);
        $(".relsCronometer").text(formattedTime);
    }, 1000);
}

function pad(number) {
    return (number < 10 ? '0' : '') + number;
}

function checkShowDownload(body){

  const isAnalitico = $('#relAnalitico').length ? true : false;
  
  if($(`#${body} > tr`).length != 0){

    if(isAnalitico){
      if($("#passNome").length){
        const passNome = $("#passNome").text().trim();
        if(passNome !== ''){
          $('.btnExcel').attr('onclick', `downloadRelScreen('${body}', 'Relatório Analítico - ${passNome}')`);
        }else{
          $('.btnExcel').attr('onclick', `downloadRelScreen('${body}')`);
        }
      }else{
        $('.btnExcel').attr('onclick', `downloadRelScreen('${body}')`);
      }
    }

    $('.btnExcel').show();
    $('.filterRelResultContainer').addClass('show');
  }else{
    $('.btnExcel').hide();
    $('.filterRelResultContainer').removeClass('show');
  }
}

function closeFilters(){
  
  $('.filtroDivNew.open').removeClass('open');

  setTimeout(() => {
    $('.agendamentosBtn.open').removeClass('open');
    $('.filtrosBtn.open').removeClass('open');
  }, 200);
  
}

// AGENDAMENTOS DE RELATÓRIOS

//ANALITICO
async function agendarAnalitico(viagemID = 0) {

  let error = "";
  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if($("input[name='grupo[]']:checked").length == 0){
    error += " Selecione pelo menos 1 Grupo.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let matricula   = $("#matricula").val();
  let previsto    = $("#previsto").val();
  let todosGrupos = $('#todosGrupos').is(':checked') ? 1 : 0;

  let gr1 = []; 
  $('input[name="grupo[]"]:checked').each(function() {
    gr1.push($(this).val());
  });

  let lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "grupo": gr1.join(', '),
    "todosGrupos": todosGrupos,
    "matricula":matricula,
    "previsto":previsto,
    "lns":lns.join(', '),
    "viagemID": viagemID
  };

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/relatorioAnalitico/agendar",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

      if(ret.novoAgendamento){
        $('.semagenda').remove();

        if(checkExistsAgendaDate().exists){

          $(`.dataAgenda[id=${checkExistsAgendaDate().dateId}]`).after(ret.novoAgendamento);

        }else{

          $('.listaAgendamentos').prepend(`<b class="bg-info p-1 dataAgenda" id="${checkExistsAgendaDate().dateId}">${checkExistsAgendaDate().dateTxt}</b>${ret.novoAgendamento}`);
        
        }

        $('.btnAgPendente .qtdAgenda').html((Number($('.btnAgPendente .qtdAgenda').html()) + 1));
        $('#agendamentosLeft').html((Number($('#agendamentosLeft').html()) - 1));
      }
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao agendar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });
}

//CONSOLIDADO
async function agendarConsolidado() {

  let error = "";
  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let sentido     = $("#sentido").val();
  let pontual     = $("#pontual").val();

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "lns": lns.join(', '),
    "sentido": sentido,
    "pontual": pontual
  };

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/relatorioConsolidado/agendar",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

      if(ret.novoAgendamento){
        $('.semagenda').remove();

        if(checkExistsAgendaDate().exists){

          $(`.dataAgenda[id=${checkExistsAgendaDate().dateId}]`).after(ret.novoAgendamento);

        }else{

          $('.listaAgendamentos').prepend(`<b class="bg-info p-1 dataAgenda" id="${checkExistsAgendaDate().dateId}">${checkExistsAgendaDate().dateTxt}</b>${ret.novoAgendamento}`);
        
        }

        $('.btnAgPendente .qtdAgenda').html((Number($('.btnAgPendente .qtdAgenda').html()) + 1));
        $('#agendamentosLeft').html((Number($('#agendamentosLeft').html()) - 1));
      }
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao agendar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });
}

//SINTÉTICO
async function agendarSintetico() {
  checkExistsAgendaDate().dateId
  let error = "";
  if($("#data_inicio").val() == ""){
    error += " Preencha a Data Início.\n";
  }

  if( $("#data_fim").val() == ""){
    error += " Preencha a Data Fim.\n";
  }

  if(error != ""){
    swal({
      title: "ATENÇÃO",
      text: "Por favor preencher os filtros: \n" + error,
      icon: "warning",
      button: "Fechar",
    });
    return false;
  }

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let pontual     = $("#pontual").val();

  var lns = []; 
  $('input[name="linhas[]"]:checked').each(function() {
    lns.push($(this).val());
  });

  if(lns == ''){
    $('input[name="linhas[]"]').each(function() {
      lns.push($(this).val());
    });
  }

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "lns": lns.join(', '),
    "pontual": pontual
  };

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/relatorioSintetico/agendar",
    method: 'post',
    data: data,
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

      if(ret.novoAgendamento){
        $('.semagenda').remove();

        if(checkExistsAgendaDate().exists){

          $(`.dataAgenda[id=${checkExistsAgendaDate().dateId}]`).after(ret.novoAgendamento);

        }else{

          $('.listaAgendamentos').prepend(`<b class="bg-info p-1 dataAgenda" id="${checkExistsAgendaDate().dateId}">${checkExistsAgendaDate().dateTxt}</b>${ret.novoAgendamento}`);
        
        }

        $('.btnAgPendente .qtdAgenda').html((Number($('.btnAgPendente .qtdAgenda').html()) + 1));
        $('#agendamentosLeft').html((Number($('#agendamentosLeft').html()) - 1));
      }
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao agendar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });
}

$(document).on('click', '#wpic', function(e){

  const checkbox = $(this);
  const ativo = $(checkbox).is(':checked');

  if(ativo && $('#wnpic').is(':checked')){
    $('#wnpic').prop('checked', false).change();
  }

});

$(document).on('click', '#wnpic', function(e){

  const checkbox = $(this);
  const ativo = $(checkbox).is(':checked');

  if(ativo && $('#wpic').is(':checked')){
    $('#wpic').prop('checked', false).change();
  }

});

//FUNÇÕES GERAIS AGENDAMENTOS
function checkExistsAgendaDate(dataAgenda = false){

  const date = new Date;
  const dateCheck = `${date.getDate() < 10 ? '0' : ''}${date.getDate()}-${date.getMonth()+1 < 10 ? '0' : ''}${date.getMonth()+1}-${date.getFullYear()}`;
  const dateTxt = `${date.getDate() < 10 ? '0' : ''}${date.getDate()}/${date.getMonth()+1 < 10 ? '0' : ''}${date.getMonth()+1}/${date.getFullYear()}`;
  
  const obj = {
    exists: $(`.dataAgenda[id=${dateCheck}]`).length ? true : false,
    dateId: dateCheck,
    dateTxt: dateTxt,
  };

  if(dataAgenda && ($(`.delDate-${dataAgenda}`).length + $(`.viewDate-${dataAgenda}`).length) == 0){
    $(`.dataAgenda[id=${dataAgenda}]`).remove();
  }

  return obj;

}

$('.btnAgPendente').on('click', function(){
  $('.agPendente').addClass('show');
  $('.agPronto').toggleClass('show');
});

$('.btnAgPronto').on('click', function(){
  $('.agPronto').addClass('show');
  $('.agPendente').toggleClass('show');
});

function expandAgenda(obj){
  $(obj).closest('li').toggleClass('open');
  $(obj).toggleClass('open');
  let title = $(obj).closest('li').hasClass('open') ? 'Fechar Detalhes' : 'Expandir Detalhes';
  $(obj).attr('title', title);
}

function excluirAgenda(id, dataAgenda, obj){

  const isAnalitico = $('#relAnalitico').length ? true : false;
  const isConsolidado = $('#relConsolidado').length ? true : false;
  const isSintetico = $('#relSintetico').length ? true : false;

  let url;

  if(isAnalitico){
   url = '/relatorioAnalitico/removerAgenda';
  }

  if(isConsolidado){
    url = '/relatorioConsolidado/removerAgenda';
  }

  if(isSintetico){
    url = '/relatorioSintetico/removerAgenda';
  }

  swal({
    title: 'Deletar Agendamento',
    text: "Deseja realmente deletar esse agendamento?",
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {
    if (result) {
      
      $("#carregando").addClass('show');
      $.ajax({
        url: url,
        method: 'get',
        data: {id:id},
        dataType: 'json',
        success:function(ret){
    
          swal({
            title: ret.title,
            text: ret.text,
            icon: ret.icon,
            button: ret.button,
          });
    
          if (ret.status){
            $(obj).closest('li').remove();
            $('.btnAgPendente .qtdAgenda').html((Number($('.btnAgPendente .qtdAgenda').html()) - 1));
            if(ret.today){
              $('#agendamentosLeft').html(ret.today);
            }
            checkExistsAgendaDate(dataAgenda);
          }
    
          $("#carregando").removeClass('show');
                   
        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }
          else{
            swal({
              title: "ERRO",
              text: "Ocorreu um erro ao agendar, tente novamente!",
              icon: "error",
              button: "Fechar",
            });
          }
    
          $("#carregando").removeClass('show');
        }
      });

    }
  });

}

function monitorToggle(valor){
  if(valor == 0){
    $('.monitorTag').addClass('hide');
  }else{
    $('.monitorTag').removeClass('hide');
  }
}

//funções para filtrar resultados de relatórios

$("#filterRelResult").on('focus', function(){
  closeFilters();
});

$("#filterRelResult").on('keyup', function(){
      
  let value = $(this).val().toLowerCase();

  $('tbody tr:not(.trHeight)').each(function(){
    
    if ($(this).text().toLowerCase().search(value) > -1) {
      $(this).show().removeClass('dn');
    } else {
      $(this).hide().addClass('dn');
    }

  });

  $('tbody tr.trHeight').each(function(){
    let $currentSeparator = $(this);
    let $nextNonSeparator = $currentSeparator.nextUntil('.trHeight').filter(':visible').not('.trHeight');

    if ($nextNonSeparator.length > 0) {
      $currentSeparator.show().removeClass('dn');
    } else {
      $currentSeparator.hide().addClass('dn');
    }

  });
   
});

//funções para versionamento CFG
function changeCgfVersionamento(){

  const vMajor = $('#vMajor').val();
  const vMinor = $('#vMinor').val();
  const vPatch = $('#vPatch').val();

  const orginalCgfVersionamento = $('#orginalCgfVersionamento').val();
  
  const newVersionamento = `${vMajor}.${vMinor}.${vPatch}`;

  const originalCgfVersion = Number($('#originalCgfVersion').val());
  
  $('#cgfVersionamento').val(newVersionamento);

  $('#cgfVersion').val(orginalCgfVersionamento != newVersionamento ? (originalCgfVersion + 1) : originalCgfVersion);

}

function changeVersionScreen(newControlVersion, newVersion){
  console.log(`Mudou versão: ${newControlVersion}/${newVersion}`);
  localStorage.setItem("cgfVersion", newControlVersion);
  $("#cgfVersionTxt").html(`Controle de Versão: ${newControlVersion} / `);
  $('#cgfVersionamentoTxt').html(newVersion);
}


//funções atualiar DB

//Para atualizar Veículos
function updateVeiculos(){

  $("#carregando").addClass('show');

  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateVeiculos'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//Para atualizar Grupos de Linhas
function updateGRLinhas(){

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateGRLinhas'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

                
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//Para atualizar Clientes(Grupos Controle Acesso)
function updateCAGrupo(){

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateCAGrupo'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

                
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//Para atualizar Linhas
function updateLinhas(){

$("#carregando").addClass('show');

  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateLinhas'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//Para atualizar Itinerários
function updateItine(){

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateItine'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

                
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//Para atualizar Viagens
function updateViagens(){

  $("#carregando").addClass('show');
  
  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateViagens'},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      $("#carregando").removeClass('show');

                
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

// Para atualizar Passageiros(Controle Acesso)
$('#modalCA').on('hidden.bs.modal', function () {
  $('#groupIdCa').val(0).change();
  $('.updateCA').prop('disabled', true);
});

function checkUpdateCA(grU){
  $('.updateCA').prop('disabled', grU == 0);
}

function updateCA(){

  const groupId = $('#groupIdCa').val();

  $("#carregando").addClass('show');

  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateCA', byGroup: groupId},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      if(ret.status){
        $('#cancelCa').trigger('click');
      }

      $("#carregando").removeClass('show');

               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

// Para atualizar TAGS
$('#modalRfid').on('hidden.bs.modal', function () {
  $('#groupId').val(0).change();
  $('.updateRfids').prop('disabled', true);
});

function checkUpdateRfid(grU){
  $('.updateRfids').prop('disabled', grU == 0);
}

function updateRfids(){

  const groupId = $('#groupId').val();

  $("#carregando").addClass('show');

  $.ajax({
    url: "/configuracoes/updateTables",
    method: 'post',
    data: {type: 'updateRfids', byGroup: groupId},
    dataType: 'json',
    success:function(ret){

      swal({
        title: ret.title,
        text: ret.text,
        icon: ret.icon,
        button: ret.button,
      });

      if(ret.status){
        $('#cancelRfid').trigger('click');
      }

      $("#carregando").removeClass('show');

               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }
      else{
        swal({
          title: "ERRO",
          text: "Ocorreu um erro ao atualizar, tente novamente!",
          icon: "error",
          button: "Fechar",
        });
      }

      $("#carregando").removeClass('show');
    }
  });

}

//funções dispositivos facial
$(document).on('click', '.switchDevice', function(e){
  
  e.preventDefault();

  const checkbox = $(this);
  const id = $(checkbox).attr('device_id');
  const name = $(checkbox).attr('device_name');
  const ativo = $(checkbox).is(':checked');

  const atTxt = ativo ? 'ATIVAR' : 'DESATIVAR';

  swal({
    title: name,
    text: `${ativo ? 'Ativar' : 'Desativar'} este dispositivo?`,
    icon: 'warning',
    dangerMode: false,
    buttons: {
      cancel: "Cancelar",
      confirm: {
        text: atTxt,
        className: ativo ? 'btn-success' : 'btn-danger'
      }
    },
  }).then((result) => {
    if (result) {

      $(checkbox).parent().append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
      $(checkbox).prop('disabled', true);
      $.ajax({
        url: '/cgfIdDevices/activeInactive',
        method: 'get',
        data: {id:id, ativo: ativo ? 1 : 0},
        dataType: 'json',
        success:function(ret){
    
          if (ret.status){
            $(checkbox).parent('label').attr('title', ativo ? 'Ativo' : 'Inativo');
            $(checkbox).parent('label').find('h6').text(ativo ? 'Ativo' : 'Inativo');
            $(checkbox).prop('checked', ativo).change();
          }
    
          setTimeout(() => {
            $(checkbox).parent().find('i.deviceSpin').remove();
            $(checkbox).prop('disabled', false);
          }, 500);
                   
        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }    
          setTimeout(() => {
            $(checkbox).parent().find('i.deviceSpin').remove();
            $(checkbox).prop('disabled', false);
          }, 500);
        }
      });
      
    }
  });
  
 
});

$(document).on('click', '.switchCircDevice', function(e){
  
  e.preventDefault();

  const checkbox = $(this);
  const id = $(checkbox).attr('device_id');
  const name = $(checkbox).attr('device_name');
  const circular = $(checkbox).is(':checked');

  const atTxt = circular ? 'TORNAR CIRULAR' : 'TORNAR NORMAL';

  swal({
    title: name,
    text: `Usar este dispositivo como ${circular ? 'circular' : 'normal'}?`,
    icon: 'warning',
    dangerMode: false,
    buttons: {
      cancel: "Cancelar",
      confirm: {
        text: atTxt,
        className: circular ? 'btn-success' : 'btn-danger'
      }
    },
  }).then((result) => {
    if (result) {

      $(checkbox).parent().append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
      $(checkbox).prop('disabled', true);
      $.ajax({
        url: '/cgfIdDevices/switchCirc',
        method: 'get',
        data: {id:id, circular: circular ? 1 : 0},
        dataType: 'json',
        success:function(ret){
    
          if (ret.status){
            $(checkbox).prop('checked', circular).change();
            if(circular){
              $(`.deviceItem[id=${name}]`).find('.deviceItemTitle').append('<span class="circEuro bg-info p-1 text-white"><i class="fas fa-sync-alt"></i>Circular<br>Eurofarma</span>');
            }else{
              $(`.deviceItem[id=${name}]`).find('.circEuro').remove();
              
            }
          }
    
          setTimeout(() => {
            $(checkbox).parent().find('i.deviceSpin').remove();
            $(checkbox).prop('disabled', false);
          }, 500);
                   
        },error: function(jqXHR){
          if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
            needLogin = true;
          }    
          setTimeout(() => {
            $(checkbox).parent().find('i.deviceSpin').remove();
            $(checkbox).prop('disabled', false);
          }, 500);
        }
      });
      
    }
  });
  
 
});

$(document).on('click', '.infoDeviceBtn', function(){

  if($(this).children('i').hasClass('fa-eye-slash')){
    $(this).removeClass('btn-danger').addClass('btn-info');
    $(this).children('i').removeClass('fa-eye-slash').addClass('fa-eye');
    $(this).attr('title', 'Ver Informações');
    $(this).find('b').html('Ver Informações');
    
  }else{
    $(this).removeClass('btn-info').addClass('btn-danger');
    $(this).children('i').removeClass('fa-eye').addClass('fa-eye-slash');
    $(this).attr('title', 'Ocultar Informações');
    $(this).find('b').html('Ocultar Informações');
  }

  $(this).closest('.infoDevice').toggleClass('open');

});

$(document).on('click', '.requestDeviceConfig', function(){

  
  const btn = $(this);
  const device_id = $(this).attr('device_id');
  const config_type = $(this).attr('config_type');

  $(btn).parent().append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
  $(btn).addClass('disabled');

  $.ajax({
    url: '/cgfIdDevices/requestConfig',
    method: 'post',
    data: {device_id:device_id, config_type: config_type},
    dataType: 'json',
    success:function(ret){

      if (ret.status){

        swal({
          title: "SUCESSO",
          text: ret.msg ?? "Solicitação enviada com sucesso!",
          icon: "success",
          button: "Fechar",
        });
        
      }else{
        swal({
          title: "ERRO",
          text: ret.msg ?? "Erro ao enviar solicitação.",
          icon: "error",
          button: "Fechar",
        });
      }

      setTimeout(() => {
        $(btn).parent().find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
      }, 500);
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }    
      setTimeout(() => {
        $(btn).parent().find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
      }, 500);
    }
  });


});

$(document).on('click', '.requestDeviceDetections', function(){

  
  const btn = $(this);
  const device_id = $(this).attr('device_id');

  $(btn).parent().append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
  $(btn).addClass('disabled');

  $.ajax({
    url: '/cgfIdDevices/requestDetections',
    method: 'post',
    data: {device_id:device_id},
    dataType: 'json',
    success:function(ret){

      if (ret.status){

        swal({
          title: ret.title ?? "SUCESSO",
          text: ret.msg ?? "Solicitação enviada com sucesso!",
          icon: ret.icon ?? "success",
          button: "Fechar",
        });

        if(ret.recCount && ret.recCount != 0){
          $(`#info_rec_${device_id}`).remove();
        }
        
      }else{
        swal({
          title: ret.title ?? "ERRO",
          text: ret.msg ?? "Erro ao enviar solicitação.",
          icon: ret.icon ?? "error",
          button: "Fechar",
        });
      }

      setTimeout(() => {
        $(btn).parent().find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
      }, 500);
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }    
      setTimeout(() => {
        $(btn).parent().find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
      }, 500);
    }
  });

});

$(document).on('click', '.showQrDevice', function(){

  if($(this).parent().hasClass('open')){
    $(this).attr('title', 'Ver QRCode');
    $(this).removeClass('btn-danger').addClass('btn-warning');
    $(this).children('i').removeClass('fa-window-close').addClass('fa-qrcode');
    
  }else{
    $(this).attr('title', 'Fechar QRCode');
    $(this).removeClass('btn-warning').addClass('btn-danger');
    $(this).children('i').removeClass('fa-qrcode').addClass('fa-window-close');
  }

  $(this).parent().toggleClass('open');
  $(this).closest('.infoDevice').find('.iframeQrDevice').toggleClass('show');

});


$(document).on('click', '.showDeviceLocalInstala', function(){

  if($(this).parent().hasClass('open')){
    $(this).attr('title', 'Ver no Mapa');
    $(this).removeClass('btn-danger').addClass('btn-success');
    $(this).children('i').removeClass('fa-window-close').addClass('fa-map');
    
  }else{
    const iframe = $(this).closest('.infoDevice').find('.iframeLocalInstala');
    if(iframe.length === 0){
      const src = $(this).attr('src');
      $(this).closest('.infoDevice').append(`<iframe class="iframeLocalInstala" src="${src}"></iframe>`);
    }
    $(this).attr('title', 'Fechar Mapa');
    $(this).removeClass('btn-success').addClass('btn-danger');
    $(this).children('i').removeClass('fa-map').addClass('fa-window-close');
  }

  $(this).parent().toggleClass('open');
  $(this).closest('.infoDevice').find('.iframeLocalInstala').toggleClass('show');

});

$(document).on('click', '.showDeviceLocalAtual', function(){

  if($(this).parent().hasClass('open')){
    $(this).attr('title', 'Ver no Mapa');
    $(this).removeClass('btn-danger').addClass('btn-success');
    $(this).children('i').removeClass('fa-window-close').addClass('fa-map');
    
  }else{
    const iframe = $(this).closest('.infoDevice').find('.iframeLocalAtual');
    if(iframe.length === 0){
      const src = $(this).attr('src');
      const device_id = $(this).attr('device_id');

      $(this).closest('.infoDevice').append(`<iframe id="iframeLocalAtual-${device_id}" class="iframeLocalAtual" src="${src}"></iframe>`);
    }
    $(this).attr('title', 'Fechar Mapa');
    $(this).removeClass('btn-success').addClass('btn-danger');
    $(this).children('i').removeClass('fa-map').addClass('fa-window-close');
  }

  $(this).parent().toggleClass('open');
  $(this).closest('.infoDevice').find('.iframeLocalAtual').toggleClass('show');

});

function startFaceDevices(){

  setTimeout(() => {

    $(`.deviceItem`).each(function () {
      const device_id = $(this).attr('id');
      $.ajax({
        url: '/cgfIdDevices/getVeicAndLocation',
        method: 'post',
        data: {device_id:device_id},
        dataType: 'json',
        success:function(ret){
    
          if (ret.status){

            if(ret.VEICULO){
              $(`#veic-${device_id}`).find('b').html(ret.VEICULO);
              $(`#veic_update_front_${device_id}`).html(ret.VEICULO);
            }else{
              $(`#veic_update_front_${device_id}`).html('<i class="fas fa-exclamation-triangle text-danger" title="Aparelho sem Veículo!"></i>');
            }

            if(ret.veic_update){
              $(`#veic-${device_id}`).find('i').html(`Atualizado em: ${ret.veic_update}`);
            }

            if(ret.latitude_now && ret.longitude_now){

              const src = `/map?latitude=${ret.latitude_now}&longitude=${ret.longitude_now}&title=${device_id}&titlePoint=Localização Atual&showTop=1&atualiza=1&showAddress=1`;

              if(!$(`#local-${device_id} .showDeviceLocalAtual`).length){

                $(`#local-${device_id}`).find('br').before(`<span title="Ver no Mapa" class="btn btn-success p-1 showDeviceLocalAtual" style="line-height:1;" device_id="${device_id}" src="${src}"><i class="fas fa-map"></i></span>`);
                
              }else{

                $(`#local-${device_id} .showDeviceLocalAtual`).attr('src', src);

              }

              if($(`#iframeLocalAtual-${device_id}`).length){

                const iframe = document.getElementById(`iframeLocalAtual-${device_id}`);
                iframe.contentWindow.postMessage({
                    latitude: Number(ret.latitude_now),
                    longitude: Number(ret.longitude_now)
                }, '*');
                
              }

              if(ret.loc_update){
                $(`#info_loc_${device_id}`).html('');
                $(`#local-${device_id}`).find('.loc_update').html(`Atualizado em: ${ret.loc_update}`);
                $(`#loc_update_front_${device_id}`).html(ret.loc_update);

                if(ret.more72){
                  $(`#info_loc_${device_id}`).html('<i class="fas fa-history"></i>Sem atualizações nas últimas 72 horas');
                }

              }else{

                $(`#info_loc_${device_id}`).html('<i class="fas fa-question-circle"></i>Sem informações de Localização');
                $(`#loc_update_front_${device_id}`).html('-');
              }

              if(ret.timezone){
                $(`#local-${device_id}`).find('b').html(`Timezone: ${ret.timezone}`);
              }

            }

            if(ret.more72_rec){
              if(!$(`#info_rec_${device_id}`).length){
                $(`.deviceItem[id=${device_id}]`).find('.deviceNoInfo').append(`<b id="info_rec_${device_id}">
                  <i class="fas fa-video-slash"></i>
                  Sem reconhecimentos<br>nas últimas 72 horas
                </b>`);
              }
            }else{
              $(`#info_rec_${device_id}`).remove();
            }

            if(ret.batteryLevel){
              $(`.deviceItem[id=${device_id}]`).find('.batteryLevel').css('width', `${ret.batteryLevel}%`);
              $(`.deviceItem[id=${device_id}]`).find('.batteryLevelNumber').html(`${ret.batteryLevel}%`);
              $(`.deviceItem[id=${device_id}]`).find('.batteryLevel').removeClass().addClass('batteryLevel').addClass(`${ret.batteryClass}`);
              $(`.deviceItem[id=${device_id}]`).find('.batteryState').html(ret.batteryState);
            }
            
          }
                   
        }
      });
    });

    setTimeout(() => {
      startFaceDevices();
    }, 60000);
    
  }, 30000);
}

$(document).on('click', '.seeDetect', function(){

  const btn = $(this);

  if($(btn).hasClass('disabled')){
    return;
  }

  const device_id = $(this).attr('device_id');
  const data = $(`#data-${device_id}`).val();     
  
  if(data == ""){
    swal({
      title: device_id,
      text: "Selecione uma data para ver os Reconhecimentos do dispositivo",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $(btn).append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
  $(btn).addClass('disabled');
  $(btn).attr('title', 'Carregando Reconhecimentos...');
  $(`#data-${device_id}`).prop('disabled', true);
  $(`#recognitions_${device_id}`).html('<span title="Fechar Reconhecimentos" class="btn p-1 btn-danger closeRecog" style="line-height:1;"><i class="fas fa-window-close"></i></span>');

  $.ajax({
    url: '/cgfIdDevices/getRecognitionsFace',
    method: 'post',
    data: {device_id:device_id, data:data},
    dataType: 'json',
    success:function(ret){

      if (ret.status){

        const recognitions = ret.recognitions;

        if(recognitions.length > 0){

          for (const recognition of recognitions) {

            const base64Image = recognition.img;
            const buffer = Uint8Array.from(atob(base64Image), c => c.charCodeAt(0)).buffer;
            const blob = new Blob([buffer], { type: 'image/png' });
            const imgUrl = URL.createObjectURL(blob);

            // const recognitionDiv = $(`<div class="recognition" id="${recognition.id}"><img src="${imgUrl}">
            // ${`<p>${recognition.formated_time}</p><p>${recognition.nome}</p>`}${recognition.controle_acesso_id == 0 ? `<span class="btn btn-warning setUserRec" recId="${recognition.id}">Definir Usuário</span>` : ''}</div>`);
            const recognitionDiv = $(`<div class="recognition" id="${recognition.id}"><img src="${imgUrl}">
            ${`<p>${recognition.formated_time}</p><p>${recognition.nome}</p>`}</div>`);
            $(`#recognitions_${device_id}`).append(recognitionDiv);

          }

          $(`#recognitions_${device_id}`).addClass('open');

        }

        
      }else{
        swal({
          title: ret.title ?? "ERRO",
          text: ret.msg ?? "Erro ao carregar.",
          icon: ret.icon ?? "error",
          button: "Fechar",
        });
      }

      setTimeout(() => {
        $(btn).find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
        $(btn).attr('title', 'Ver Reconhecimentos');
        $(`#data-${device_id}`).prop('disabled', false);
      }, 500);
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }    
      setTimeout(() => {
        $(btn).find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
        $(btn).attr('title', 'Ver Reconhecimentos');
        $(`#data-${device_id}`).prop('disabled', false);
      }, 500);
    }
  });

});

$(document).on('click', '.seeTryAgain', function(){

  const btn = $(this);

  if($(btn).hasClass('disabled')){
    return;
  }

  const device_id = $(this).attr('device_id');
  const data = $(`#data-ta-${device_id}`).val();     
  
  if(data == ""){
    swal({
      title: device_id,
      text: "Selecione uma data para ver os Tente Novamente do dispositivo",
      icon: "warning",
      button: "Fechar",
    });

    return false;
  }

  $(btn).append('<i class="fas fa-spinner fa-spin deviceSpin"></i>');
  $(btn).addClass('disabled');
  $(btn).attr('title', 'Carregando Tente Novamente...');
  $(`#data-ta-${device_id}`).prop('disabled', true);
  $(`#recognitions_${device_id}`).html('<span title="Fechar Tente Novamente" class="btn p-1 btn-danger closeRecog" style="line-height:1;"><i class="fas fa-window-close"></i></span>');

  $.ajax({
    url: '/cgfIdDevices/getTryAgainFace',
    method: 'post',
    data: {device_id:device_id, data:data},
    dataType: 'json',
    success:function(ret){

      if (ret.status){

        const recognitions = ret.recognitions;

        if(recognitions.length > 0){

          for (const recognition of recognitions) {

            const base64Image = recognition.img;
            const buffer = Uint8Array.from(atob(base64Image), c => c.charCodeAt(0)).buffer;
            const blob = new Blob([buffer], { type: 'image/png' });
            const imgUrl = URL.createObjectURL(blob);

            // const recognitionDiv = $(`<div class="recognition" id="${recognition.id}"><img src="${imgUrl}">
            // ${`<p>${recognition.formated_time}</p><p>${recognition.nome}</p>`}${recognition.controle_acesso_id == 0 ? `<span class="btn btn-warning setUserRec" recId="${recognition.id}">Definir Usuário</span>` : ''}</div>`);
            const recognitionDiv = $(`<div class="recognition" id="${recognition.id}"><img src="${imgUrl}">
            ${`<p>${recognition.formated_time}</p>`}</div>`);
            $(`#recognitions_${device_id}`).append(recognitionDiv);

          }

          $(`#recognitions_${device_id}`).addClass('open');

        }

        
      }else{
        swal({
          title: ret.title ?? "ERRO",
          text: ret.msg ?? "Erro ao carregar.",
          icon: ret.icon ?? "error",
          button: "Fechar",
        });
      }

      setTimeout(() => {
        $(btn).find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
        $(btn).attr('title', 'Ver Tente Novamente');
        $(`#data-ta-${device_id}`).prop('disabled', false);
      }, 500);
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }    
      setTimeout(() => {
        $(btn).find('i.deviceSpin').remove();
        $(btn).removeClass('disabled');
        $(btn).attr('title', 'Ver Tente Novamente');
        $(`#data-ta-${device_id}`).prop('disabled', false);
      }, 500);
    }
  });

});

$(document).on('click', '.closeRecog', function(){
  $(this).parent().removeClass('open');
});

$(document).on('click', '.setUserRec', function(){
  const recId = $(this).attr('recId');
  swal({
    title: 'Definir Usuário',
    text: 'Selecione o tipo de usuário:',
    icon: 'warning',
    dangerMode: true,
    buttons: {
      pax: {
        text: "Passageiro",
        className:'btn-warning',
        value: "pax",
      },
      moto: {
        text: "Motorista",
        className:'btn-primary',
        value: "moto",
      }
    },
  }).then((value) => {

    if(!value){
      return false;
    }

    const title = (value == 'pax') ? 'Definir Passageiro' : 'Definir Motorista';
    const txt = (value == 'pax') ? 'Passageiro novo ou existente?' : 'Motorista novo ou existente?';

    const toOpen = value;

    swal({
      title: title,
      text: txt,
      icon: 'warning',
      dangerMode: true,
      buttons: {
        pax: {
          text: "Existente",
          className:'btn-success',
          value: "exist",
        },
        moto: {
          text: "Novo",
          className:'btn-warning',
          value: "new",
        }
      },
    }).then((value) => {
  
      if(value == 'exist'){

        if(toOpen == 'pax'){
          window.open(`/cadastroPax?recId=${recId}`, '_blank');
        }
        
      }

      if(value == 'new'){
  
      }
      
    });
    
  });
  
});

$(document).on('change', '.changeVeicFace', function(){

  const isUpdating = $(this).attr('updating');

  if (isUpdating == '1') return;

  const select = $(this);
  $(select).attr('updating', '1');
  
  const iniVeic = $(this).attr('iniVeic');
  const newVeic = $(this).val();

  if(newVeic == "0"){
    $(select).val(iniVeic).change();
  }

  else if(newVeic !== iniVeic){

    const device_id = $(this).attr('device_id');
    let veicNome = $(this).find(":selected").text();
    veicNome = veicNome.trim();
        
    swal({
      title: device_id,
      text: "Trocar o veículo desse dispositivo? \n O aparelho deve estar com a tela de configurações aberta.",
      icon: 'warning',
      dangerMode: false,
      buttons: {
        cancel: "Cancelar",
        confirm: {
          text: "Trocar",
          className: 'btn-success'
        }
      },
    }).then((result) => {
      if (result) {
  
        $(select).prop('disabled', true);
        
        $.ajax({
          url: '/cgfIdDevices/updateFaceCar',
          method: 'post',
          data: {device_id:device_id, veiculo_id:newVeic},
          dataType: 'json',
          success:function(ret){
      
            if (ret.status){
              $(select).attr('iniVeic', newVeic);
              $(`#veic-${device_id}`).find('b').html(veicNome);
              $(`#veic_update_front_${device_id}`).html(veicNome);
              $(`#veic-${device_id}`).find('i').html(`Atualizado em: ${ret.veic_update}`);

            }else{

              swal({
                title: ret.title ?? "ERRO",
                text: ret.msg ?? "Erro ao atualizar veículo, tente novamente.",
                icon: ret.icon ?? "error",
                button: "Fechar",
              });

              $(select).val(iniVeic).change();

            }
      
            
            
            setTimeout(() => {
              $(select).prop('disabled', false);
            }, 500);
                     
          },error: function(jqXHR){
            if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
              needLogin = true;
            }    
            setTimeout(() => {
              $(select).prop('disabled', false);
            }, 500);
          }
        });
        
      }else{
        $(select).val(iniVeic).change();
      }
    });

  }

  $(select).attr('updating', '0');
 
});

$(document).on('click', '.btnLost72Device', function(){
  const checked = $('#lost72').is(':checked');
  $('#lost72').prop('checked', !checked).change();
  if(checked){
    $(this).removeClass('btn-danger').addClass('btn-light');
  }else{
    $(this).removeClass('btn-light').addClass('btn-danger');
  }

  changePage(1);
});

$(document).on('click', '.btnNoLocDevice', function(){
  const checked = $('#noLoc').is(':checked');
  $('#noLoc').prop('checked', !checked).change();
  if(checked){
    $(this).removeClass('btn-danger').addClass('btn-light');
  }else{
    $(this).removeClass('btn-light').addClass('btn-danger');
  }

  changePage(1);
});

$(document).on('click', '.btnNoRec72Device', function(){
  const checked = $('#noRec72').is(':checked');
  $('#noRec72').prop('checked', !checked).change();
  if(checked){
    $(this).removeClass('btn-danger').addClass('btn-light');
  }else{
    $(this).removeClass('btn-light').addClass('btn-danger');
    if($('#withRec72').is(':checked')){
      $('#withRec72').prop('checked', false).change();
      $('#withRec72').removeClass('btn-danger').addClass('btn-light');
    }
  }

  changePage(1);
});

$(document).on('click', '.btnWithRec72Device', function(){
  const checked = $('#withRec72').is(':checked');

  $('#withRec72').prop('checked', !checked).change();
  if(checked){
    $(this).removeClass('btn-success').addClass('btn-light');
  }else{
    $(this).removeClass('btn-light').addClass('btn-success');
    if($('#noRec72').is(':checked')){
      $('#noRec72').prop('checked', false).change();
      $('#noRec72').removeClass('btn-danger').addClass('btn-light');
    }
  }

  changePage(1);
});

$(document).on('click', '.btnCircLineDevice', function(){
  const checked = $('#circLine').is(':checked');
  $('#circLine').prop('checked', !checked).change();
  if(checked){
    $(this).removeClass('btn-info').addClass('btn-light');
  }else{
    $(this).removeClass('btn-light').addClass('btn-info');
  }

  changePage(1);
});

$(document).on('change', '.versionFilterDevice, #carrosFilter, #modelsDevices, #devicesDevices, .intDevices, .cadFilterDevice', function(){
  changePage(1);
});

//para setar reconhecimento para passageiro existente
function askRecognitionSet(recId, paxId, paxName){

  const text = `Deseja atribuir reconhecimento para ${paxName ? `\n${paxName}` : 'esse usuário'} ?`;

  swal({
    title: 'Definir Usuário',
    text: text,
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Deletar"
    },
  }).then((result) => {
    if (result) {



    }
  });
}

$(document).on('click', '.closerecimg', function(){
  $(this).parent().removeClass('show');
});

//para ampliar imagens de reconhecimentos nos relatórios
$(document).on('click', '.picListRel', function(){

  const recid = $(this).attr('recid');
  const imgSrc = $(this).attr('src');
  
  if($(`#recimg-${recid}`).length){
    $(`#recimg-${recid}`).addClass('show');
    return;
  }

  $('body').append(`<div class="recimg show" id="recimg-${recid}"><i class="fas fa-times-circle btn btn-danger h4 closerecimg"></i><img src="${imgSrc}"/></div>`);

});

$(document).on('click', '.showPaxEmb, .showPaxDesemb', function(){

  
  let trParent = $(this).closest('tr');
  let index = trParent.index();
  const isEmb = $(this).hasClass('showPaxEmb');
  const frameId = isEmb ? `map-${index}-emb` : `map-${index}-desemb`;

  if(!$(`#${frameId}`).length){
    const src = $(this).attr('src');
    $('body').append(`<div class="mapRel" id="${frameId}"><i class="fas fa-times-circle btn btn-danger h4 closeMapRel"></i><iframe src="${src}"></iframe></div>`);
    setTimeout(() => {
      $(`#${frameId}`).addClass('show');
    }, 200);
  }else{
    $(`#${frameId}`).addClass('show');
  }

});

$(document).on('click', '.closeMapRel', function(){
  $(this).parent().removeClass('show');
});


function changeTintColor(select, color, orginalColor, deviceId){
  
  const isUpdating = $(select).attr('updating');

  if (isUpdating == '1') return;
  
  $(select).attr('updating', '1');
  $(select).prop('disabled', true);
  $.ajax({
    url: '/cgfIdDevices/changeTintColor',
    method: 'post',
    data: {deviceId:deviceId, color:color},
    dataType: 'json',
    success:function(ret){

      if (!ret.status){
        $(select).val(orginalColor).change();
      }

      setTimeout(() => {
        $(select).prop('disabled', false);
      }, 500);
               
    },error: function(jqXHR){
      if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
        needLogin = true;
      }    
      setTimeout(() => {
        $(select).prop('disabled', false);
      }, 500);
    },complete: function(){
      $(select).attr('updating', '0');
    }
    
  });

}

async function gertarRelatorioCircEuro(){

  closeFilters();

  const url = '/relatorioCircularEuroFarma/getDadosCirEuro';

  const { signal } = getdataDashNewController;

  let data_inicio = $("#data_inicio").val();
  let data_fim    = $("#data_fim").val();
  let carro       = $("#carro").val();
  let distancia   = $("#distancia").val();

  $('.btnExcel').hide();

  $('.filterRelResultContainer').removeClass('show');

  const data = {
    "data_inicio": data_inicio,
    "data_fim": data_fim,
    "carro": carro,
    "distancia": Number(distancia)
  };

  setTimeout(() => {
    $('#abortGetRelsBtn').fadeIn();
  }, 100);

  timingRels();

  $("#bodyCircularEuro").html('');
  $('#relCircularEuro .customScroll, #relCircularEuro .tBodyScroll').removeClass('show');

  const settings = {
    signal,
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(data)
  };


  await fetch(url, settings)
  .then( resposta => {
  return resposta.json();
  })
  .then ( ret => {
      
    if(ret.html){

      $("#bodyCircularEuro").html(ret.html);

      setTimeout( () => {
        hasCustonScroll($('.customScroll'));
        checkShowDownload('bodyCircularEuro');
      },500);

      applyThWidth();

      notifyReady();

      timingRels(true, '#6aff2e');

    }else{

      $('.btnExcel').hide();

      $('.filterRelResultContainer').removeClass('show');

      if(ret.error != undefined){
        swal({
        title: "ATENÇÃO",
        text: ret.error,
        icon: "warning",
        button: "Fechar",
        });
        $("#carregando").removeClass('show');
        $('#abortGetRelsBtn').hide();
      } else {
        swal({
          title: "ATENÇÃO",
          text: "Nenhum resultado encontrado para os filtros usados!",
          icon: "warning",
          button: "Fechar",
        });
        $("#carregando").removeClass('show');
        $('#abortGetRelsBtn').hide();

        timingRels(true, 'yellow');
        return false;
      }

    }

    $("#carregando").removeClass('show');
    $('#abortGetRelsBtn').hide();

    closeFilters();

    }).catch((err) => {
      $('.btnExcel').hide();
      $('.filterRelResultContainer').removeClass('show');
      timingRels(true, 'yellow');
      $('#abortGetRelsBtn').hide();
      if(err.message.includes('is not valid JSON')){
          swal({
          title: 'Sessão Expirada',
          text: 'Por favor clique no botão a baixo e faça o login novamente',
          icon: 'warning',
          dangerMode: true,
          buttons: {
              confirm: "Fazer Login"
          },
          }).then(() => {
          window.location.href = "/login";
          });
      }
    });
};


//para paretros de gráficos
function txtGraphParam(element){
  const valor = element.value;
  const idColor = element.id.replace('Txt', 'Color');
  const label = $(`label[for="${element.id}"]`);
  const labelColor = $(`label[for="${idColor}"]`);
  label.text(`${valor} Texto:`);
  labelColor.text(`${valor} Cor:`);
}

function colorGraphParam(element){
  const cor = element.value;
  const idTxt = element.id.replace('Color', 'Txt');
  $(`#${idTxt}`).css({
    'color': getTextColorBasedOnBgColor(cor),
    'background-color': cor
  });

}

function getTextColorBasedOnBgColor(bgColor) {
  
  bgColor = bgColor.replace('#', '');
  let r = parseInt(bgColor.substring(0, 2), 16);
  let g = parseInt(bgColor.substring(2, 4), 16);
  let b = parseInt(bgColor.substring(4, 6), 16);
  
  let brightness = (r * 299 + g * 587 + b * 114) / 1000;
  return brightness > 128 ? '#000000' : '#FFFFFF';
}

$(document).on('click', '#graphDefault', function(e){

  const graphDefault = $(this).is(':checked') ? 1 : 0;

  if(graphDefault == 1){
    $('.overParam').css('display', 'block');
    $('.holdAppSelects').css('opacity', '.5');

    $.ajax({
      url: '/configuracoes/getGraphDefault',
      method: 'get',
      dataType: 'json',
      success:function(ret){

        if(ret.graphPontualColor){
          $("#graphPontualColor").val(ret.graphPontualColor).change();
        }

        if(ret.graphAdiantadoColor){
          $("#graphAdiantadoColor").val(ret.graphAdiantadoColor).change();
        }

        if(ret.graphAtrasadoColor){
          $("#graphAtrasadoColor").val(ret.graphAtrasadoColor).change();
        }

        if(ret.graphNesColor){
          $("#graphNesColor").val(ret.graphNesColor).change();
        }

        if(ret.graphAgendaColor){
          $("#graphAgendaColor").val(ret.graphAgendaColor).change();
        }

        if(ret.graphReColor){
          $("#graphReColor").val(ret.graphReColor).change();
        }

        if(ret.graphSreColor){
          $("#graphSreColor").val(ret.graphSreColor).change();
        }

        if(ret.graphBarraColor){
          $("#graphBarraColor").val(ret.graphBarraColor).change();
        }

        if(ret.graphPontualTxt){
          $("#graphPontualTxt").val(ret.graphPontualTxt);
          $('label[for="graphPontualTxt"]').text(`${ret.graphPontualTxt} Texto:`);
          $('label[for="graphPontualColor"]').text(`${ret.graphPontualTxt} Cor:`);
        }

        if(ret.graphAdiantadoTxt){
          $("#graphAdiantadoTxt").val(ret.graphAdiantadoTxt);
          $('label[for="graphAdiantadoTxt"]').text(`${ret.graphAdiantadoTxt} Texto:`);
          $('label[for="graphAdiantadoColor"]').text(`${ret.graphAdiantadoTxt} Cor:`);
        }

        if(ret.graphAtrasadoTxt){
          $("#graphAtrasadoTxt").val(ret.graphAtrasadoTxt);
          $('label[for="graphAtrasadoTxt"]').text(`${ret.graphAtrasadoTxt} Texto:`);
          $('label[for="graphAtrasadoColor"]').text(`${ret.graphAtrasadoTxt} Cor:`);
        }

        if(ret.graphNesTxt){
          $("#graphNesTxt").val(ret.graphNesTxt);
          $('label[for="graphNesTxt"]').text(`${ret.graphNesTxt} Texto:`);
          $('label[for="graphNesColor"]').text(`${ret.graphNesTxt} Cor:`);
        }

        if(ret.graphAgendaTxt){
          $("#graphAgendaTxt").val(ret.graphAgendaTxt);
          $('label[for="graphAgendaTxt"]').text(`${ret.graphAgendaTxt} Texto:`);
          $('label[for="graphAgendaColor"]').text(`${ret.graphAgendaTxt} Cor:`);
        }

        if(ret.graphReTxt){
          $("#graphReTxt").val(ret.graphReTxt);
          $('label[for="graphReTxt"]').text(`${ret.graphReTxt} Texto:`);
          $('label[for="graphReColor"]').text(`${ret.graphReTxt} Cor:`);
        }

        if(ret.graphSreTxt){
          $("#graphSreTxt").val(ret.graphSreTxt);
          $('label[for="graphSreTxt"]').text(`${ret.graphSreTxt} Texto:`);
          $('label[for="graphSreColor"]').text(`${ret.graphSreTxt} Cor:`);
        }
                 
      },error: function(jqXHR){
        if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
          needLogin = true;
        }
      }
    });
  }else{
    const idGroup = $('#idGroup').val();
    $.ajax({
      url: `/configuracoes/getGraphGroup?idGroup=${idGroup}`,
      method: 'get',
      dataType: 'json',
      success:function(ret){

        if(ret.graphPontualColor){
          $("#graphPontualColor").val(ret.graphPontualColor).change();
        }

        if(ret.graphAdiantadoColor){
          $("#graphAdiantadoColor").val(ret.graphAdiantadoColor).change();
        }

        if(ret.graphAtrasadoColor){
          $("#graphAtrasadoColor").val(ret.graphAtrasadoColor).change();
        }

        if(ret.graphNesColor){
          $("#graphNesColor").val(ret.graphNesColor).change();
        }

        if(ret.graphAgendaColor){
          $("#graphAgendaColor").val(ret.graphAgendaColor).change();
        }

        if(ret.graphReColor){
          $("#graphReColor").val(ret.graphReColor).change();
        }

        if(ret.graphSreColor){
          $("#graphSreColor").val(ret.graphSreColor).change();
        }

        if(ret.graphBarraColor){
          $("#graphBarraColor").val(ret.graphBarraColor).change();
        }

        if(ret.graphPontualTxt){
          $("#graphPontualTxt").val(ret.graphPontualTxt);
          $('label[for="graphPontualTxt"]').text(`${ret.graphPontualTxt} Texto:`);
          $('label[for="graphPontualColor"]').text(`${ret.graphPontualTxt} Cor:`);
        }

        if(ret.graphAdiantadoTxt){
          $("#graphAdiantadoTxt").val(ret.graphAdiantadoTxt);
          $('label[for="graphAdiantadoTxt"]').text(`${ret.graphAdiantadoTxt} Texto:`);
          $('label[for="graphAdiantadoColor"]').text(`${ret.graphAdiantadoTxt} Cor:`);
        }

        if(ret.graphAtrasadoTxt){
          $("#graphAtrasadoTxt").val(ret.graphAtrasadoTxt);
          $('label[for="graphAtrasadoTxt"]').text(`${ret.graphAtrasadoTxt} Texto:`);
          $('label[for="graphAtrasadoColor"]').text(`${ret.graphAtrasadoTxt} Cor:`);
        }

        if(ret.graphNesTxt){
          $("#graphNesTxt").val(ret.graphNesTxt);
          $('label[for="graphNesTxt"]').text(`${ret.graphNesTxt} Texto:`);
          $('label[for="graphNesColor"]').text(`${ret.graphNesTxt} Cor:`);
        }

        if(ret.graphAgendaTxt){
          $("#graphAgendaTxt").val(ret.graphAgendaTxt);
          $('label[for="graphAgendaTxt"]').text(`${ret.graphAgendaTxt} Texto:`);
          $('label[for="graphAgendaColor"]').text(`${ret.graphAgendaTxt} Cor:`);
        }

        if(ret.graphReTxt){
          $("#graphReTxt").val(ret.graphReTxt);
          $('label[for="graphReTxt"]').text(`${ret.graphReTxt} Texto:`);
          $('label[for="graphReColor"]').text(`${ret.graphReTxt} Cor:`);
        }

        if(ret.graphSreTxt){
          $("#graphSreTxt").val(ret.graphSreTxt);
          $('label[for="graphSreTxt"]').text(`${ret.graphSreTxt} Texto:`);
          $('label[for="graphSreColor"]').text(`${ret.graphSreTxt} Cor:`);
        }
                 
      },error: function(jqXHR){
        if(jqXHR.getResponseHeader("Content-Type").includes("text/html")) {
          needLogin = true;
        }
      }
    });
    $('.overParam').css('display', 'none');
    $('.holdAppSelects').css('opacity', '1');
  }
  
 
});