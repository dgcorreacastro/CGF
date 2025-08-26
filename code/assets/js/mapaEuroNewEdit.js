//variavel de onde chama a função de carrega o ponto
const page = 'edit';

//salvar pontos no localstorage para não perder as alterações
let pontosSalvos = [];
$(document).ready(function(){
    let idToten = $('#idToten').val();
    if(idToten == 0){
        $("#carregando").addClass('show');
        const ID_ORIGIN = $('#ID_ORIGIN').val();
        
        let LINK      = "";
        let client   = $("#ID_ORIGIN").val();
        let clieName = $("#ID_ORIGIN option:selected").html();
        clieName     = clieName.split('-');
        let random   = Math.floor(Math.random() * 1000); 
        LINK = client +'-'+ clieName[0].replace(/\s/g, '') +'-'+ random;
        $("#LINK").val(LINK);

        const url = `/configuracoes/cadastrarTotemEuro?ID_ORIGIN=${ID_ORIGIN}&LINK=${LINK}`;
        fetch(url)
        .then( resposta => {
            return resposta.json();
        }).then ( ret => {
            if(ret.success){
                $('#idToten').val(ret.novaId);
                $('#btnSalvarEuro').prop("disabled", false);
            }
            else{
                window.location.href = "/configuracoes/totemEuro";
            }
            $("#carregando").removeClass('show');
        });
    }
        
    if (localStorage.getItem('pontosSalvos') !== null) {
        pontosSalvos = JSON.parse(localStorage.getItem('pontosSalvos'));
    }

    //colocar pontos do banco no localstorage caso não estejam
    $('.pontoMapaEuroNew').each(function(){
        let id = $(this).attr('id');
        let idCount = $(this).attr('idCount');
        if(id != idCount && !pontosSalvos.find(x => x.id === id, x => x.idToten === idToten)){
            
            const ponto = {
                idToten: idToten,
                id: id,
                idCount: idCount,
                nome: $(this).find('.nomePonto nome').text(),
                left: $(this).css('left'),
                top: $(this).css('top'),
                manha: [],
                tarde: [],
                picoAlmoco: [],
                noite: [],
                restaurante: []
            }
            pontosSalvos.push(ponto);
        }
        
    });

    localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));

    //carrega os pontos salvos
    pontosSalvos.map(function(ponto) {
        //checa se tem algo diferente nos pontos que estão no banco
        //em relação ao locastorage para salvar no banco
        const id = $(ponto).attr('id');
        const idCount = $(ponto).attr('idCount');
        if(ponto.idToten != idToten){return;}
        if(id != idCount){
            const pontoL = $(`.pontoMapaEuroNew[id=${ponto.id}]`);
            if($(pontoL).find('.nomePonto nome').text() != ponto.nome ||
                $(pontoL).css('top') != ponto.top || $(pontoL).css('left') != ponto.left){
                    savePonto(id, true);
                }
            $(pontoL)
            .css({'top': ponto.top, 'left': ponto.left})
            .find('.nomePonto nome').text(ponto.nome);
        }

        //se está no locastorage e não está no banco coloca os pontos no mapa
        //será salvo no banco quando tentar editar
        else{
            $('.mapaEuroNew').append(`<div class="pontoMapaEuroNew"
            id="${ponto.id}"
            idCount="${ponto.idCount}"
            style="top: ${ponto.top};
            left: ${ponto.left}">
                <div class="menuPonto">
                <span acao="editarPonto">
                    <i class="fas fa-edit"></i>
                    Editar Ponto
                </span>
                <span acao="removerPonto">
                    <i class="fas fa-trash-alt"></i>
                    Remover Ponto
                </span>
                </div>
                <span class="loaderMapaEuroNew"></span>
                <i class="fa fa-window-close closePonto" aria-hidden="true"></i>
                <span class="nomePonto"><nome>${ponto.nome}</nome></span>
                <div class="erroPonto">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    <errorMsg></errorMsg>
                </div>
                <div class="dadosPonto">
                <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="manha">Manhã</span>
                    <ul class="manha"></ul>
                </div>

                <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="tarde">Tarde</span>
                    <ul class="tarde"></ul>
                </div>
                
                <div class="horariosMapaNew">
                    <span class="tituloHorarios">Pico-Almoço</span>
                    <ul class="picoAlmoco"></ul>
                </div>

                <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="noite">Noite</span>
                    <ul class="noite"></ul>
                </div>

                <div class="horariosMapaNew">
                    <span class="tituloHorarios">Restaurante</span>
                    <ul class="restaurante"></ul>
                </div>

                </div>
            </div>`);
        }
    });
});

//salvar edição de ponto e adicionar pontos
async function savePonto(id, estaNoBanco) {
    
    const pontoToSave = pontosSalvos.find(x => x.id === id, x => x.idToten === idToten);
    
    
    const nome = pontoToSave.nome;

    let posicaoIcone = {
        top: parseInt(pontoToSave.top),
        left: parseInt(pontoToSave.left)
    }
    posicaoIcone = JSON.stringify(posicaoIcone);

    const acao = estaNoBanco ? 'editar' : 'adicionar';

    const ponto = $(`.pontoMapaEuroNew[id=${id}]`);

    const idToten = $('#idToten').val();
    
    const url = `/configuracoes/salvaEdicaoPonto?acao=${acao}&idToten=${idToten}&id=${id}&nome_ponto=${nome}&posicaoIcone=${posicaoIcone}`;

    await fetch(url)
        .then( resposta => {
            return resposta.json();
        }).then ( ret => {
            if(ret.success){
                $(ponto).append('<div class="statusEditPonto sucesso"><i class="fas fa-check-circle"></i></div>');
                if(acao == 'adicionar'){
                    $(ponto).find('.loaderMapaEuroNew').css('display', 'none');
                    $('.backPontoOpen').remove();
                    $(ponto).attr('id', ret.novoId);
                    pontoToSave.id = ret.novoId;
                    localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
                    $(ponto).trigger('dblclick');
                }
            }
            else{
                $(ponto).append('<div class="statusEditPonto falha"><i class="fas fa-check-circle"></i></div>');
            }
            setTimeout(() => {
                $(ponto).find('.statusEditPonto').fadeOut(function(){
                    $(this).remove();
                });
            }, 500);
        });
}

$('#bodyMapsNew').on('contextmenu', function(e){
  e.preventDefault();
});

$(document).on('click', '.pontoMapaEuroNew', function(e){
    e.preventDefault();
    e.stopPropagation();
});

//longpress para editar os pontos
let edita;
$(document).on('mousedown', '.pontoMapaEuroNew:not(.open)', function(e){
    clearTimeout(edita);
    if(e.button === 0){
        $(this).removeClass('menuOpen');
        $(this).siblings().removeClass('menuOpen');
        edita = setTimeout(() => {
            clearTimeout(edita);
            $(this).addClass('editando');
            $('.mapaEuroNew').addClass('editando');
            
            const pontoPositionOld = {
                left: $(this).css('left'),
                top: $(this).css('top')
            }
            
            localStorage.setItem('pontoPositionOld', JSON.stringify(pontoPositionOld));
        }, 500);
    }else{
        $(this).toggleClass('menuOpen');
        $(this).siblings().removeClass('menuOpen');
    }
});

$(document).on('dblclick', '.pontoMapaEuroNew:not(.open)', function(e){
    $('.pontoMapaEuroNew, .mapaEuroNew').removeClass('editando');
    clearTimeout(edita);
    $(this).removeClass('menuOpen');
    pegarDadosPonto($(this));
});

$(document).on('mouseup', '.pontoMapaEuroNew:not(.open)', function(){
    $('.pontoMapaEuroNew, .mapaEuroNew').removeClass('editando');
    clearTimeout(edita);
});

$(document).on('mousemove', '.mapaEuroNew', function(e){
    e.preventDefault();
    e.stopPropagation();
    
    const pontoEditando = $('.pontoMapaEuroNew.editando');

    if(pontoEditando.length){
        const pontoPosition = {
        left: e.offsetX,
        top: e.offsetY
        }
        $(pontoEditando).css({
            'top': `${pontoPosition.top}px`,
            'left' : `${pontoPosition.left}px`
        });
    }
});


//adicionar e posicionar pontos
$(document).on('click', '.mapaEuroNew', function(e){
    e.preventDefault();
    e.stopPropagation();
    if($('.pontoMapaEuroNew.open').length){return;}
    $('.pontoMapaEuroNew').removeClass('menuOpen');
    const pontoEditando = $('.pontoMapaEuroNew.editando');

    const pontoPosition = {
        left: e.offsetX,
        top: e.offsetY
    }

    if(pontoEditando.length){

        $(pontoEditando).css({
            'top': `${pontoPosition.top}px`,
            'left' : `${pontoPosition.left}px`
        }).addClass('checaPosicao');

        
        const pontoPositionOld = JSON.parse(localStorage.getItem('pontoPositionOld'));
        if(!checaPontoOver($(pontoEditando))){
            $(pontoEditando).css({
                'top': pontoPositionOld.top,
                'left' : pontoPositionOld.left
            }).removeClass('checaPosicao');
        }else{
            let id = $(pontoEditando).attr('id');
            const idCount = $(pontoEditando).attr('idCount');
            id = id != idCount ? id : idCount;
            const idToten = $('#idToten').val();
            pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).left = `${pontoPosition.left}px`;
            pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).top = `${pontoPosition.top}px`;
            localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
            $(pontoEditando).removeClass('checaPosicao');

            const estaNoBanco = id != idCount ? true : false;
            savePonto(id, estaNoBanco);
        }
        localStorage.removeItem('pontoPositionOld');

    }else{
        
        $('.mapaEuroNew').append(`<div class="pontoMapaEuroNewTeste"
            style="top: ${pontoPosition.top}px;
            left: ${pontoPosition.left}px">
                <div class="menuPonto">
                  <span acao="editarPonto">
                    <i class="fas fa-edit"></i>
                    Editar Ponto
                  </span>
                  <span acao="removerPonto">
                    <i class="fas fa-trash-alt"></i>
                    Remover Ponto
                  </span>
                </div>
                <span class="loaderMapaEuroNew"></span>
                <i class="fa fa-window-close closePonto" aria-hidden="true"></i>
                <span class="nomePonto"><nome>Nome do Ponto</nome></span>
                <div class="erroPonto">
                  <i class="fa fa-info-circle" aria-hidden="true"></i>
                  <errorMsg></errorMsg>
                </div>
                <div class="dadosPonto">
                  <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="manha">Manhã</span>
                    <ul class="manha"></ul>
                  </div>

                  <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="tarde">Tarde</span>
                    <ul class="tarde"></ul>
                  </div>
                  
                  <div class="horariosMapaNew">
                    <span class="tituloHorarios">Pico-Almoço</span>
                    <ul class="picoAlmoco"></ul>
                  </div>

                  <div class="horariosMapaNew">
                    <span class="tituloHorarios add" tipo="noite">Noite</span>
                    <ul class="noite"></ul>
                  </div>

                  <div class="horariosMapaNew">
                    <span class="tituloHorarios">Restaurante</span>
                    <ul class="restaurante"></ul>
                  </div>

                </div>
            </div>`);
                            
        if(!checaPontoOver($('.pontoMapaEuroNewTeste'))){
            swal({
                title: "ATENÇÃO",
                text: 'Evite colocar pontos muito próximos',
                icon: "warning",
                confirm: "OK",
              }).then(() => {
                $('.pontoMapaEuroNewTeste').remove();
              });
        }else{
            $('.pontoMapaEuroNewTeste').removeClass('pontoMapaEuroNewTeste')
                                        .addClass('pontoMapaEuroNew')
                                        .attr('id', `${$('.pontoMapaEuroNew').length}`)
                                        .attr('idCount', `${$('.pontoMapaEuroNew').length}`);
            
                const idToten = $('#idToten').val();
                const ponto = {
                idToten: idToten,
                id: `${$('.pontoMapaEuroNew').length}`,
                idCount: `${$('.pontoMapaEuroNew').length}`,
                nome: 'Nome do Ponto',
                left: `${pontoPosition.left}px`,
                top: `${pontoPosition.top}px`,
                manha: [],
                tarde: [],
                picoAlmoco: [],
                noite: [],
                restaurante: []
            }
            pontosSalvos.push(ponto);
            localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
        }
    }

    $('.pontoMapaEuroNew, .mapaEuroNew').removeClass('editando');
});

$(document).on('mousedown', '.menuPonto, .menuLiHora', function(e){
    e.preventDefault();
    e.stopPropagation();
});

//remover e editar pontos
$(document).on('mousedown, click', '.menuPonto span', function(e){
    e.preventDefault();
    e.stopPropagation();
    const acao = $(this).attr('acao');
    const idToten = $('#idToten').val();
    //remover
    if(acao == 'removerPonto'){
        const nomePonto = $(this).closest('.pontoMapaEuroNew').find('.nomePonto nome').text();
        swal({
            title: `Deletar: ${nomePonto}`,
            text: "Deseja realmente deletar esse ponto?",
            icon: 'warning',
            dangerMode: true,
            buttons: {
            cancel: "Cancelar",
            confirm: "Deletar"
        },
        }).then((result) => {
            if (result) {
                const pontoDeletar = $(this).closest('.pontoMapaEuroNew');
                let id = $(pontoDeletar).attr('id');
                const idCount = $(pontoDeletar).attr('idCount');
                id = id != idCount ? id : idCount;
                
                if(id != idCount){
                    //ponto
                    const ponto = $(this).closest('.pontoMapaEuroNew');
                    //loader no ponto
                    $(ponto).find('.loaderMapaEuroNew').css('display', 'block');
                    //backdrop
                    $('#mapaEuroNew').append('<div class="backPontoOpen"></div>');
                    const getDotsController = new AbortController();
                    const { signal } = getDotsController;

                    const url = `/configuracoes/removePonto?id=${id}`;

                    const espera = setTimeout(() => {
                        getDotsController.abort(); 
                    }, 10000);

                    fetch(url, { signal })
                    .then( resposta => {
                        return resposta.json();
                    })
                    .then ( ret => {
                        clearTimeout(espera);
                        $(ponto).find('.loaderMapaEuroNew, .dadosPonto').css('display', 'none');
                        if(ret.success){
                            $(this).closest('.pontoMapaEuroNew').remove();
                            pontosSalvos = pontosSalvos.filter(x => {
                                return x.id != id && x.idToten == idToten;
                            });
                            localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
                            swal({
                                title: "SUCESSO",
                                text: 'Ponto removido com sucesso!',
                                icon: "success",
                                confirm: "OK",
                              }).then(() => {
                                $('.backPontoOpen').remove();
                              });
                        }else{
                            swal({
                                title: "ATENÇÃO",
                                text: 'Erro ao remover o ponto, tente novamente.',
                                icon: "error",
                                confirm: "OK",
                              }).then(() => {
                                $('.backPontoOpen').remove();
                              });
                        }
                    })
                    .catch(() => {
                        clearTimeout(espera);
                        $('.backPontoOpen').remove();
                        $(ponto).find('.loaderMapaEuroNew, .dadosPonto').css('display', 'none');
                        swal({
                            title: "ATENÇÃO",
                            text: 'Erro ao remover o ponto, tente novamente.',
                            icon: "error",
                            confirm: "OK",
                          }).then(() => {
                            $('.backPontoOpen').remove();
                          });
                    }); 
                }else{
                    $(this).closest('.pontoMapaEuroNew').remove();
                    
                    pontosSalvos = pontosSalvos.filter(x => {
                        return x.id != id && x.idToten == idToten;
                    });
                    localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
                    swal({
                        title: "SUCESSO",
                        text: 'Ponto removido com sucesso!',
                        icon: "success",
                        confirm: "OK",
                      }).then(() => {
                        $('.backPontoOpen').remove();
                      });
                }
            }
        });
    }
    
    //editar
    if(acao == 'editarPonto'){
        pegarDadosPonto($(this).closest('.pontoMapaEuroNew'));
    }

    $(this).closest('.pontoMapaEuroNew').toggleClass('menuOpen');

});

//editar nome do ponto
$(document).on('dblclick', '.pontoMapaEuroNew.open .nomePonto:not(.editNome)', function(e){
    $(this).closest('.pontoMapaEuroNew').addClass('editandoPonto');
    $(this).find('nome').css('display','none');
    $(this).addClass('editNome');
    $(this).append(`<input type="text" id="editNomePonto" value="${$(this).text()}">`);
});


$(document).on('dblclick', '.pontoMapaEuroNew.open .nomePonto.editNome', function(e){
    const pontoNome = $('#editNomePonto').val();

    //impede de salvar em branco
    if(pontoNome == ''){
        swal({
            title: "EDIÇÃO DE NOME",
            text: 'O nome do ponto não pode ficar em branco!',
            icon: "warning",
            confirm: "OK",
        }).then((result) => {
            if(result){
                $('#editNomePonto').val($(this).find('nome').text());
            }
        });
        return;
    }

    let id = $(this).closest('.pontoMapaEuroNew').attr('id');
    const idCount = $(this).closest('.pontoMapaEuroNew').attr('idCount');
    id = id != idCount ? id : idCount;
    $(this).closest('.pontoMapaEuroNew').removeClass('editandoPonto');
    $(this).removeClass('editNome');
    $(this).find('nome').removeAttr('style');
    $('#editNomePonto').remove();

    //salva se for diferente o que já estava
    if(pontoNome != $(this).find('nome').text()){
        $(this).find('nome').text(pontoNome);
        const idToten = $('#idToten').val();
        pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).nome = pontoNome;
        localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
        const estaNoBanco = id != idCount ? true : false;
        savePonto(id, estaNoBanco);
    }
});

$(document).on('dblclick', '.pontoMapaEuroNew.open .nomePonto.editNome input', function(e){
    e.stopPropagation();
});

$(document).on('blur', '.pontoMapaEuroNew.open .nomePonto.editNome input', function(e){
    e.stopPropagation();
    $('.pontoMapaEuroNew.open .nomePonto.editNome').trigger('dblclick');
});

$(document).on('keydown', '.pontoMapaEuroNew.open .nomePonto.editNome input', function(e){
    e.stopPropagation();
    if(e.which == 13 || e.which == 9){
        $('.pontoMapaEuroNew.open .nomePonto.editNome').trigger('dblclick');
    }
});


//editar horários
$(document).on('dblclick', '.horariosMapaNew.show ul li', function(e){
    e.stopPropagation();
    const tipo = $(this).parent().attr('class');
    const hora = this.childNodes[0].nodeValue;
    const id = $(this).attr('id');
    const novo = $(this).attr('novo');
    const classe = $(this).attr('class');

    let checked_pico = '';
    let checked_restaurante = '';
    let checked_picoA =  '';

    if(classe == 'horaAmarelo'){
        checked_pico = 'checked';
    }

    if(tipo == 'restaurante' || classe == 'horaAzul'){
        checked_restaurante = 'checked';
    }

    if(tipo == 'picoAlmoco'){
        checked_picoA = 'checked';
    }

    $(this).addClass('editandoHoraLi');
    
    $('.pontoMapaEuroNew.open').append(`<div class="addHoraForm" tipo="${tipo}" id="${id}" novo="${novo}">
        <label for="horario" class="control-label text-dark">
            Editar Horário:
        </label>
        <div class="inputHolder">
            <input name="horario" type="time" id="horario" value="${hora}">
            <button type="button" class="btn btn-primary addHoraBtn" onclick="addHora('edit')">Editar</button>
            <button type="button" class="btn btn-danger removeHoraBtn" onclick="removeHora()">Remover</button>
            <button type="button" class="btn btn-secondary closeAddHora">Fechar</button>
        </div>
        <div class="optionHolder">
            <label for="horario_pico"><input class="horaOptCheck" type="checkbox" id="horario_pico" ${checked_pico} name="horario_pico"> Horário de Pico</label>
            <label for="restaurante"><input class="horaOptCheck" type="checkbox" id="restaurante" ${checked_restaurante} name="restaurante"> Restaurante</label>
            <label for="pico_almoco"><input class="horaOptCheck" type="checkbox" id="pico_almoco" ${checked_picoA} name="pico_almoco"> Pico-Almoço</label>
        </div>
    </div>`);
});

//adicinar horarios
$(document).on('click', '.addHorario', function(e){
    e.stopPropagation();
    const titulo = $(this).attr('title');
    const tipo = $(this).parent().attr('tipo');
    $('.pontoMapaEuroNew.open').append(`<div class="addHoraForm" tipo="${tipo}" novo="sim">
        <label for="horario" class="control-label text-dark">
            ${titulo}:
        </label>
        <div class="inputHolder">
            <input name="horario" type="time" id="horario" value="">
            <button type="button" class="btn btn-primary addHoraBtn" onclick="addHora('add')">Adicionar</button>
            <button type="button" class="btn btn-secondary closeAddHora">Fechar</button>
        </div>
        <div class="optionHolder">
            <label for="horario_pico"><input class="horaOptCheck" type="checkbox" id="horario_pico" name="horario_pico"> Horário de Pico</label>
            <label for="restaurante"><input class="horaOptCheck" type="checkbox" id="restaurante" name="restaurante"> Restaurante</label>
            <label for="pico_almoco"><input class="horaOptCheck" type="checkbox" id="pico_almoco" name="pico_almoco"> Pico-Almoço</label>
        </div>
    </div>`);
});

//fechar edicão e adição de horários
$(document).on('click', '.closeAddHora', function(e){
    $('.editandoHoraLi').removeClass('editandoHoraLi');
    $(this).closest('.addHoraForm').fadeOut(function(){
        $(this).remove();
    });
});

$(document).on('click', '.addHoraForm', function(e){
    e.stopPropagation();
});

//função que remove horários
function removeHora(){
    swal({
        title: `Deletar Horário`,
        text: "Deseja realmente deletar esse horário?",
        icon: 'warning',
        dangerMode: true,
        buttons: {
        cancel: "Cancelar",
        confirm: "Deletar"
    },
    }).then((result) => {
        if (result) {
            const id = $('.addHoraForm').attr('id');
            const novo = $('.addHoraForm').attr('novo');
            const tipo = $('.addHoraForm').attr('tipo');
            $('body').append('<div class="backPontoOpen"><span class="loaderMapaEuroNew" style="display:block"></span></div>');
            
            //verifica se está salvo banco para remover do banco caso esteja
            if(novo == 'nao'){
                const url = `/configuracoes/removerHorario?id=${id}`;
                fetch(url)
                .then( resposta => {
                    return resposta.json();
                }).then ( ret => {
                    if(ret.success){
                        swal({
                            title: "SUCESSO",
                            text: 'Horário removido com sucesso!',
                            icon: "success",
                            confirm: "OK",
                        }).then(() => {
                            $('.backPontoOpen').remove();
                            $('.editandoHoraLi').remove();
                            $('.addHoraForm').fadeOut(function(){
                                $(this).remove();
                                removeHoraLocal(tipo, id);
                            });
                        });
                    }
                    else{
                        swal({
                            title: "ATENÇÃO",
                            text: 'Erro ao remover o horário, tente novamente.',
                            icon: "error",
                            confirm: "OK",
                        }).then(() => {
                            $('.backPontoOpen').remove();
                        });
                    }
                });
            }else{
                swal({
                    title: "SUCESSO",
                    text: 'Horário removido com sucesso!',
                    icon: "success",
                    confirm: "OK",
                }).then(() => {
                    $('.backPontoOpen').remove();
                    $('.editandoHoraLi').remove();
                    $('.addHoraForm').fadeOut(function(){
                        $(this).remove();
                        removeHoraLocal(tipo, id);
                    });
                });
            }   
        }
         
    });
}

//funcão que adiciona e edita horários
function addHora(acao){
    const horario = $('#horario').val();
    if(horario == '' || horario == '00:00'){
        $('.closeAddHora').trigger('click');
        return;
    }
    $('body').append('<div class="backPontoOpen"><span class="loaderMapaEuroNew" style="display:block"></span></div>');
    const id = acao == 'add' ? $('.horariosMapaNew ul li').length + 1 : $('.addHoraForm').attr('id');
    const novo = $('.addHoraForm').attr('novo');
    let tipo = $('.addHoraForm').attr('tipo');
    const horario_pico = $('#horario_pico').is(':checked') ? 2 : 1;
    const restaurante = $('#restaurante').is(':checked') ? 2 : 1;
    const pico_almoco = $('#pico_almoco').is(':checked') ? 2 : 1;
    
    let title;
    let classe;
    let addTo;
    let manhaArr = [];
    let tardeArr = [];
    let noiteArr = [];
    let picoAlmocoArr = [];
    let restauranteArr = [];
    
    $('.pontoMapaEuroNew.open').find(`.manha li:not([id=${id}])`).each(function(){
        const horarioOld = {
            id: $(this).attr('id'),
            hora: $(this).text(),
            tipo: 'manha',
            title: $(this).attr('title'),
            classe: $(this).attr('class'),
            novo: $(this).attr('novo')
        }
        manhaArr.push(horarioOld);
    });

    $('.pontoMapaEuroNew.open').find(`.tarde li:not([id=${id}])`).each(function(){
        const horarioOld = {
            id: $(this).attr('id'),
            hora: $(this).text(),
            tipo: 'tarde',
            title: $(this).attr('title'),
            classe: $(this).attr('class')
        }
        tardeArr.push(horarioOld);
    });

    $('.pontoMapaEuroNew.open').find(`.picoAlmoco li:not([id=${id}])`).each(function(){
        const horarioOld = {
            id: $(this).attr('id'),
            hora: $(this).text(),
            tipo: 'picoAlmoco',
            title: $(this).attr('title'),
            classe: $(this).attr('class')
        }
        picoAlmocoArr.push(horarioOld);
    });
    
    $('.pontoMapaEuroNew.open').find(`.noite li:not([id=${id}])`).each(function(){
        const horarioOld = {
            id: $(this).attr('id'),
            hora: $(this).text(),
            tipo: 'noite',
            title: $(this).attr('title'),
            classe: $(this).attr('class')
        }
        noiteArr.push(horarioOld);
    });
    
    $('.pontoMapaEuroNew.open').find(`.restaurante li:not([id=${id}])`).each(function(){
        const horarioOld = {
            id: $(this).attr('id'),
            hora: $(this).text(),
            tipo: 'restaurante',
            title: $(this).attr('title'),
            classe: $(this).attr('class')
        }
        restauranteArr.push(horarioOld);
    });

    //banco só aceita manha tarde e noite
    let tipoBanco;
    
    let checaTipo = new Date(`1970/01/01 ${horario}:00`);
    checaTipo = checaTipo.getHours(horario);
    

    if (checaTipo < 12) {
        tipoBanco = 'manha';
    } else if (checaTipo < 18) {
        tipoBanco = 'tarde';
    } else {
        tipoBanco = 'noite';
    }

    if(tipoBanco == 'manha' || tipoBanco == 'tarde'){
        if(pico_almoco == 2){
            addTo = 'picoAlmocoArr';
            title = 'Horário de Pico-Almoço';
            classe = 'horaAmarelo';
            tipo = 'picoAlmoco';
        }
        else{
            addTo = `${tipoBanco}Arr`;
            if(horario_pico == 2 && restaurante == 1){
                title = 'Horário de Pico';
                classe = 'horaAmarelo';
                tipo = `${tipoBanco}`;
            }
            else if(restaurante == 2 && horario_pico == 1){
                title = 'Horário Fixo Restaurante';
                classe = 'horaAzul';
                tipo = `${tipoBanco}`;
            }else{
                title = 'Horário Intermediário';
                classe = 'horaVerde';
                tipo = `${tipoBanco}`;
            }
        }
    }
    
    else if(tipoBanco == 'noite'){
        if(restaurante == 2){
            addTo = 'restauranteArr';
            title = 'Horário Fixo Restaurante';
            classe = 'horaAzul';
            tipo = 'restaurante';
        }
        else{
            addTo = 'noiteArr';
            if(horario_pico == 2 && restaurante == 1){
                title = 'Horário de Pico';
                classe = 'horaAmarelo';
                tipo = `${tipoBanco}`;
            }
            else if(restaurante == 2 && horario_pico == 1){
                title = 'Horário Fixo Restaurante';
                classe = 'horaAzul';
                tipo = `${tipoBanco}`;
            }else{
                title = 'Horário Intermediário';
                classe = 'horaVerde';
                tipo = `${tipoBanco}`;
            }
        }
    }

    const horarioAdd = {
        id: id,
        hora: horario,
        tipo: tipo,
        title: title,
        classe: classe,
        novo: novo
    }
    

    if(addTo == 'manhaArr'){
        manhaArr.push(horarioAdd);
        ordernarArray(manhaArr);
        
        $('.pontoMapaEuroNew.open').find('.manha').html('');
        manhaArr.map(function(horario) {
            addHoraUL($('.pontoMapaEuroNew.open'), horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
        });
    }

    if(addTo == 'tardeArr'){
        tardeArr.push(horarioAdd);
        ordernarArray(tardeArr);

        $('.pontoMapaEuroNew.open').find('.tarde').html('');
        tardeArr.map(function(horario) {
            addHoraUL($('.pontoMapaEuroNew.open'), horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
        });
    }

    if(addTo == 'noiteArr'){
        noiteArr.push(horarioAdd);
        ordernarArray(noiteArr);
        
        $('.pontoMapaEuroNew.open').find('.noite').html('');
        noiteArr.map(function(horario) {
            addHoraUL($('.pontoMapaEuroNew.open'), horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
        });
    }

    if(addTo == 'picoAlmocoArr'){
        picoAlmocoArr.push(horarioAdd);
        ordernarArray(picoAlmocoArr);
        
        $('.pontoMapaEuroNew.open').find('.picoAlmoco').html('');
        picoAlmocoArr.map(function(horario) {
            addHoraUL($('.pontoMapaEuroNew.open'), horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
        });
    }

    if(addTo == 'restauranteArr'){
        restauranteArr.push(horarioAdd);
        ordernarArray(restauranteArr);
        
        $('.pontoMapaEuroNew.open').find('.restaurante').html('');
        restauranteArr.map(function(horario) {
            addHoraUL($('.pontoMapaEuroNew.open'), horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
        });
    }
   
    let idPonto = $('.pontoMapaEuroNew.open').attr('id');
   
    const url = `/configuracoes/salvaHorarioNew?acao=${acao}&id=${id}&idPonto=${idPonto}&tipo=${tipoBanco}&restaurante=${restaurante}&pico_almoco=${pico_almoco}&horario_pico=${horario_pico}&horario=${horario}`;

    fetch(url)
    .then( resposta => {
        return resposta.json();
    }).then ( ret => {
        if(ret.success){
            swal({
                title: "SUCESSO",
                text: `Horário ${acao == 'add' ? 'adicionado' : 'editado'} com sucesso!`,
                icon: "success",
                confirm: "OK",
            }).then((result) => {
                if(result){
                    $('.backPontoOpen').remove();
                    $('.editandoHoraLi').remove();
                    if(acao == 'add'){
                        
                        if(tipo == 'manha'){
                            manhaArr.find(x => x.id === id).id = ret.novoId;
                        }
                    
                        if(tipo == 'tarde'){
                            tardeArr.find(x => x.id === id).id = ret.novoId;
                        }
                    
                        if(tipo == 'noite'){
                            noiteArr.find(x => x.id === id).id = ret.novoId;
                        }
                    
                        if(tipo == 'picoAlmoco'){
                            picoAlmocoArr.find(x => x.id === id).id = ret.novoId;
                        }
                    
                        if(tipo == 'restaurante'){
                            restauranteArr.find(x => x.id === id).id = ret.novoId;
                        }
                    }
                    $('.addHoraForm').fadeOut(function(){
                        $(this).remove();
                        editHoraLocal('manha', manhaArr);
                        editHoraLocal('tarde', tardeArr);
                        editHoraLocal('noite', noiteArr);
                        editHoraLocal('picoAlmoco', picoAlmocoArr);
                        editHoraLocal('restaurante', restauranteArr);
                    });
                }
            });
        }
        else{
            swal({
                title: "ATENÇÃO",
                text: `Erro ao ${acao == 'add' ? 'adicionar' : 'editar'} horário!`,
                icon: "error",
                confirm: "OK",
            }).then((result) => {
                if(result){
                    $('.backPontoOpen').remove();
                    $('.editandoHoraLi').remove();
                    $('.addHoraForm').fadeOut(function(){
                        $(this).remove();
                    });
                }
            });
        }
    });
    
}

//função que ordena horarios
function ordernarArray(array){
    array.sort(function(a, b) {
        return Date.parse('1970/01/01 ' + a.hora.slice(0, -2) + ' ' + a.hora.slice(-2)) - Date.parse('1970/01/01 ' + b.hora.slice(0, -2) + ' ' + b.hora.slice(-2))
    });
}

//funcao para remover rorarios do localStorage
function removeHoraLocal(tipo, id){
    let idPonto = $('.pontoMapaEuroNew.open').attr('id');
    const idToten = $('#idToten').val();

    let removeHora = [];

    if(tipo == 'manha'){
        removeHora = pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).manha.filter(x => {
            return x.id != id;
        });
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).manha = removeHora; 
    }

    if(tipo == 'tarde'){
        removeHora = pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).tarde.filter(x => {
            return x.id != id;
        });
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).tarde = removeHora; 
    }

    if(tipo == 'noite'){
        removeHora = pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).noite.filter(x => {
            return x.id != id;
        });
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).noite = removeHora; 
    }

    if(tipo == 'picoAlmoco'){
        removeHora = pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).picoAlmoco.filter(x => {
            return x.id != id;
        });
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).picoAlmoco = removeHora; 
    }

    if(tipo == 'restaurante'){
        removeHora = pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).restaurante.filter(x => {
            return x.id != id;
        });
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).restaurante = removeHora; 
    }

    localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
}

//função pra salvar horarios no locaStorage
function editHoraLocal(tipo, arr){
    
    let idPonto = $('.pontoMapaEuroNew.open').attr('id');
    const idToten = $('#idToten').val();
    
    if(tipo == 'manha'){
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).manha = arr;
    }

    if(tipo == 'tarde'){
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).tarde = arr;
    }

    if(tipo == 'noite'){
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).noite = arr;
    }

    if(tipo == 'picoAlmoco'){
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).picoAlmoco = arr;
    }

    if(tipo == 'restaurante'){
        pontosSalvos.find(x => x.id === idPonto, x => x.idToten === idToten).restaurante = arr;
    }

    localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
}

//funcao para salvar
function salvarTotemEuroNew(){
    
    let LINK = $('#LINK').val();
  
      if(LINK == ''){
        swal({
            icon: "warning",
            text: "Gere um LINK clicando sobre o botão 'GERAR LINK'"
          });
      }else{
        const idToten = $('#idToten').val();
        
        //remover pontos do toten do localStorage antes salvar
        pontosSalvos = pontosSalvos.filter(x => {
            return x.idToten != idToten;
        });
        localStorage.setItem('pontosSalvos', JSON.stringify(pontosSalvos));
        $('#formEuro').submit();
      }
      
  }

  //funcao que checa horas salvas no localstorage quando abre pontos
  function checaHorasSalvas(id, ponto){
    //retorna true para buscar no banco

    const idToten = $('#idToten').val();
    if(!pontosSalvos.find(x => x.id === id, x => x.idToten === idToten) || 
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).manha.length == 0 &&
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).tarde.length == 0 &&
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).picoAlmoco.length == 0 &&
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).noite.length == 0 &&
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).restaurante.length == 0){
      return true;
    }
    //retorna false e preenche com os dados salvos no localstorage
    else{
        pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).manha.map(function(horario) {
        addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
      });
  
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).tarde.map(function(horario) {
        addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
      });
  
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).picoAlmoco.map(function(horario) {
        addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
      });
  
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).noite.map(function(horario) {
        addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
      });
  
      pontosSalvos.find(x => x.id === id, x => x.idToten === idToten).restaurante.map(function(horario) {
        addHoraUL(ponto, horario.tipo, horario.id, horario.title, horario.classe, horario.hora, horario.novo);
      });
  
      $(ponto).find('.horariosMapaNew').addClass('show');
      
      return false;
    }
  }

//funcoes que checam se os pontos estão se sobrepondo
function checaPontoOver(elment){
    let errorPontoOver = 0;
    $('.pontoMapaEuroNew:not(.checaPosicao)').each(function(){
        if(pontoOver($(elment), $(this))){
            errorPontoOver++;
        };
    });
    return errorPontoOver == 0 ? true : false;
}

function pontoOver(el1, el2) {
    const pontoOver1 = el1[0].getBoundingClientRect();
    const pontoOver2 = el2[0].getBoundingClientRect();
  
    return !(
      pontoOver1.top > pontoOver2.bottom ||
      pontoOver1.right < pontoOver2.left ||
      pontoOver1.bottom < pontoOver2.top ||
      pontoOver1.left > pontoOver2.right
    );
}

  
