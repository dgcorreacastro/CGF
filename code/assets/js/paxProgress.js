var carregando = window.top.document.getElementsByClassName('carregando')[0];

function removeProgressInactivePax(){
  var paxProgress = window.top.document.getElementsByClassName('paxProgress')[0];
  var btnCancel = window.top.document.getElementsByClassName('cancelInactivePax')[0];
  $(btnCancel).trigger('click');
  $(paxProgress).remove();
}


function removeProgressImportPax(){
  var paxProgress = window.top.document.getElementsByClassName('paxProgress')[0];
  var btnCancel = window.top.document.getElementsByClassName('cancelImportPax')[0];
  $(btnCancel).trigger('click');
  $(paxProgress).remove();
}

function removeProgressErasePax(){
  var paxProgress = window.top.document.getElementsByClassName('paxProgress')[0];
  var btnCancel = window.top.document.getElementsByClassName('cancelErasePax')[0];
  $(btnCancel).trigger('click');
  $(paxProgress).remove();
}

$(document).on('click', '.successPax', function(){

  $('.li-success').show();

  if($(this).hasClass('view')){
    $('.li-error').show();
    $('.li-change').show();
  }else{
    $('.li-error').hide();
    $('.li-change').hide();
  }
  
  $(this).toggleClass('view');

});

$(document).on('click', '.errorPax', function(){

  $('.li-error').show();

  if($(this).hasClass('view')){
    $('.li-success').show();
    $('.li-change').show();
  }else{
    $('.li-success').hide();
    $('.li-change').hide();
  }

  $(this).toggleClass('view');

});

$(document).on('click', '.subPax', function(){
  
  $('.li-change').show();

  if($(this).hasClass('view')){
    $('.li-success').show();
    $('.li-error').show();
  }else{
    $('.li-success').hide();
    $('.li-error').hide();
  }
  
  $(this).toggleClass('view');

});

function changePax(id){

  let oldID = $(`#${id}-oldID`).val();
  let paxIdOrigin = $(`#${id}-paxIdOrigin`).val();
  let oldName = $(`#${id}-oldName`).val();
  let nome = $(`#${id}-nome`).val();
  let tag = $(`#${id}-tag`).val();
  let grupoID = $(`#${id}-grupoID`).val();
  let linhaIdaID = $(`#${id}-linhaIdaID`).val();
  let linhaVoltaID = $(`#${id}-linhaVoltaID`).val();
  let matricula = $(`#${id}-matricula`).val();
  let poltronaIda = $(`#${id}-poltronaIda`).val();
  let poltronaVolta = $(`#${id}-poltronaVolta`).val();
  let end = $(`#${id}-end`).val();

  const data = {
    "oldID": oldID,
    "paxIdOrigin": paxIdOrigin,
    "oldName": oldName,
    "nome": nome,
    "tag": tag,
    "grupoID": grupoID,
    "linhaIdaID": linhaIdaID,
    "linhaVoltaID": linhaVoltaID,
    "matricula": matricula,
    "poltronaIda": poltronaIda,
    "poltronaVolta": poltronaVolta,
    "end": end
  };

  swal({
    title: 'ATENÇÃO',
    text: `Deseja encerrar a vigência atual do código: ${tag} de ${oldName} (mantendo o histórico de batidas), e criar uma nova para ${nome}?`,
    icon: 'warning',
    dangerMode: true,
    buttons: {
      cancel: "Cancelar",
      confirm: "Confirmar"
    },
  }).then((result) => {

    if (result) {

      $(carregando).addClass('show');

      $.ajax({
        url: '/cadastroPax/changePax',
        method: 'post',
        data: data,
        dataType: 'json',
        success:function(ret){
    
          $(carregando).removeClass('show');

          if(ret.success){

            swal({
              title: "SUCESSO",
              text: ret.msg,
              icon: "success",
              button: "OK",
            }).then(() => {
      
            $('.subPax b').html((Number($('.subPax b').html()) - 1));
            $('.successPax b').html((Number($('.successPax b').html()) + 1));
            $(`#line-${id}`).replaceWith(`<div class="paxIten li-success" id="line-${id}" style="background: #28a745!important; color: white">
              <span>Nome: <b>${nome}</b> - </span><span>Cód. Cartão: <b>${tag}</b> - </span><span><b>${ret.msg}</b></span>
              ${ret.paxId ? `- <a href="/cadastroPax/edit?id=${ret.paxId}" target="_blank" class="btn btn-primary border-white">Ver Cadastro</a>`:''}
            </div>`);

            $(`#tdstatus-${id}`).replaceWith(`<td id="tdstatus-${id}">${ret.msg}</td>`);
              
            });

          }else{
            
            swal({
              title: "ERRO",
              text: ret.msg,
              icon: "error",
              button: "FECHAR",
            }).then(() => {
      
            $('.subPax b').html((Number($('.subPax b').html()) - 1));
            $('.errorPax b').html((Number($('.errorPax b').html()) + 1));
            $(`#line-${id}`).replaceWith(`<div class="paxIten li-error" id="line-${id}" style="background: #dc3545!important; color: white">
              <span>Nome: <b>${nome}</b> - </span><span>Cód. Cartão: <b>${tag}</b> - </span><span><b>${ret.msg}</b></span>
            </div>`);

            $(`#tdstatus-${id}`).replaceWith(`<td id="tdstatus-${id}">${ret.msg}</td>`);
              
            });
            
          }
                   
        },error: function(){

          swal({
            title: "ERRO",
            text: "Ocorreu um erro ao atualizar, tente novamente!",
            icon: "error",
            button: "Fechar",
          }).then(() => {
            window.top.location.href = "/";
          });
    
          $(carregando).removeClass('show');
        }
      });

    }

  });

}

function downloadImportStatus(){
  
  let thead = '<tr>';

  $('.headExcel').find('th').each(function(){
    thead += `<td>${$(this).text()}</td>`;
  });

  thead += '</tr>';

  let tab_text=`<table border='1px'>${thead}<tr>`;
  let j=0;
  tab = document.getElementById("bodyStatusImport");

  for(j = 0 ; j < tab.rows.length ; j++) 
  {     
      tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
  }

  tab_text=tab_text+"</table>";
  tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");
  tab_text= tab_text.replace(/<img[^>]*>/gi,"");
  tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, "");

  const universalBOM = "\uFEFF";
  let a = window.document.createElement('a');
  a.setAttribute('href', 'data:application/vnd.ms-excel; charset=utf-8,' + encodeURIComponent(universalBOM+tab_text));
  a.setAttribute('download', `importStatus.xls`);
  window.document.body.appendChild(a);
  a.click();
  a.remove();

}