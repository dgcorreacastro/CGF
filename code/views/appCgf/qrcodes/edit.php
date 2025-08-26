<main class="py-4">
    <div class="personContainer">

        <div class="card-body">
            <form method="POST" action="/app/update" accept-charset="UTF-8" class="form-horizontal">
               
                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Editar</b></h2>
                </div>
                <hr>
                <div class="card-create-body">
                    
                    <div class="row">

                        <div class="col-sm-9 col-xs-12">

                            <div class="row">

                                <div class="col-sm-9 col-xs-12">
                                    <label for="ID_ORIGIN" class="control-label">Cliente:</label></br>
                                    <select class="form-control" id="ID_ORIGIN" name="ID_ORIGIN" disabled>
                                    <?php foreach($grLin as $tot): ?>
                                        <option 
                                            value="<?php echo $tot['ID_ORIGIN']; ?>"
                                            <?php echo $tot['ID_ORIGIN'] == $linkapp->cliente_id ? 'selected' : ''; ?>
                                        ><?php echo utf8_decode(utf8_encode($tot['NOME'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    </select>
                                </div> 

                                <div class="col-sm-3 col-xs-12">
                                    <label for="codigo" class="control-label">Código:</label>
                                    <input class="form-control" name="id" type="hidden" value="<?php echo $linkapp->id ?>">
                                    <input class="form-control" name="codigo" type="text" id="codigo" value="<?php echo $linkapp->codigo ?>" readonly="readonly">
                                    <input class="form-control" name="qrcode" type="hidden" id="qrcode" value="<?php echo $linkapp->qrcode ?>">
                                    <input class="form-control" name="baseURL" type="hidden" id="baseURL" value="<?php echo BASE_URLB ?>">
                                    <input class="form-control" name="groupDefault" type="hidden" id="groupDefault" value="<?php echo $linkapp->groupDefault ?>">
                                    <input class="form-control" name="ID_ORIGIN_EDIT" type="hidden" id="ID_ORIGIN_EDIT" value="<?php echo $linkapp->cliente_id ?>">
                                </div>

                            </div>

                            <div class="row mt-2">

                                <div class="col-sm-9 col-xs-12">
                                    <div class="holdFiltroSelect">
                                        <label for="grupo" class="form-label">Grupo:</label>
                                        <span class="filtroSelect" title="SELECIONE UM GRUPO" originaltxt="SELECIONE UM GRUPO" checkboxesFiltro="gruposSel"><i class="fa fa-users" aria-hidden="true"></i> <texto>SELECIONE UM GRUPO</texto></span>
                                    </div>
                                    <hr style="width:100%">
                                    Grupos Selecionados: (Clique na <i class="fa fa-star" aria-hidden="true"></i> para definir como GRUPO PADRÃO*)
                                    <div class="checkboxesFiltroSelecionados" id="gruposSel">
                                    <?php foreach($grupos as $gr): 
                                        if(in_array($gr['ID_ORIGIN'], $groups)){
                                    ?>  
                                        <span title="<?php echo $gr['NOME'] ?>" idSel="gr-<?php echo $gr['ID_ORIGIN'] ?>"><i title="Remover <?php echo $gr['NOME'] ?>" class="fas fa-user-times removeFiltroSel"></i> <?php echo $gr['NOME'] ?></span>
                                    <?php };
                                    endforeach;
                                    ?>
                                    </div>
                                </div>

                                <div id="divQr" class="col-sm-3 col-xs-6"></div>

                            </div>
                        
                        </div>

                        <div class="col-sm-3 col-xs-12">

                            <label for="register" class="control-label">Solicitar Cadastro?
                                <i title="SIM se o cadatro for obrigatório para utilizar o App / NÃO para poder utilizar o App sem cadastro" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                            </label>
                            <select id="appRegisterSelect" name="register" class="form-control filtroSelect2">
                                <option value="1" <?php echo $linkapp->register == 1 ? 'selected' : '';?>>Sim</option>
                                <option value="0" <?php echo $linkapp->register == 0 ? 'selected' : '';?>>Não</option>
                            </select>

                            <div class="holdAppSelects">

                                <span class="holdAppSelectsTitulo">Solicitar Cadastro: <b id="appRegisterSelectTi"><?php echo $linkapp->register == 1 ? 'Sim' : 'Não';?></b></span>

                                <div id="solicitaCadastroDiv" style="margin-top: 1em; display:<?php echo $linkapp->register == 1 ? 'block' : 'none';?>">
                                    <label for="isCard" class="control-label">Tipo de Leitura:
                                        <i title="Escolha se utiliza Cartão ou Chaveiro" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <select name="isCard" class="form-control filtroSelect2" >
                                        <option value="1" <?php echo $linkapp->isCard == 1 ? 'selected' : '';?>>Cartão</option>
                                        <option value="0" <?php echo $linkapp->isCard == 0 ? 'selected' : '';?>>Chaveiro</option>
                                    </select>
                                </div>

                                <div id="embarqueQrDiv" style="margin-top: 1em; display:<?php echo $linkapp->register == 0 ? 'block' : 'none';?>">
                                    <label for="embarqueQr" class="control-label">Embarque QRCode?
                                        <i title="SIM para permitir embarque sem Cartão RFID / NÃO para que o uso do Cartão RFID seja obrigatório" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <select name="embarqueQr" class="form-control filtroSelect2">
                                        <option value="0" <?php echo $linkapp->embarqueQr == 0 ? 'selected' : '';?>>Não</option>
                                        <option value="1" <?php echo $linkapp->embarqueQr == 1 ? 'selected' : '';?>>Sim</option>
                                    </select>
                                </div>

                            </div>

                            <div class="holdAppSelects" id="holdEmbarqueQr" style="display:<?php echo $linkapp->embarqueQr == 1 ? 'block' : 'none';?>">
                            
                                <span class="holdAppSelectsTitulo">Embarque QRCode: <b id="embarqueQrSelectTi">Sim</b></span>

                                <div id="exigeCadDiv" style="margin-top: 1em;">
                                    <label for="exigeCad" class="control-label">Exigir Nome e Matrícula?
                                        <i title="SIM para poder realizar o Embarque por QRCode somente informando Nome Completo e Matrícula / NÃO para poder realizar sem informar" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <select name="exigeCad" class="form-control filtroSelect2">
                                        <option value="0" <?php echo $linkapp->exigeCad == 0 ? 'selected' : '';?>>Não</option>
                                        <option value="1" <?php echo $linkapp->exigeCad == 1 ? 'selected' : '';?>>Sim</option>
                                    </select>
                                </div>

                                <hr>

                                <div id="exigeMotiveDiv">
                                    <label for="exigeMotive" class="control-label">Exigir Motivo?
                                        <i title="SIM para poder realizar o Embarque por QRCode somente informando o motivo. / NÃO para poder realizar sem informar" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <select name="exigeMotive" class="form-control filtroSelect2">
                                        <option value="0" <?php echo $linkapp->exigeMotive == 0 ? 'selected' : '';?>>Não</option>
                                        <option value="1" <?php echo $linkapp->exigeMotive == 1 ? 'selected' : '';?>>Sim</option>
                                    </select>
                                </div>

                                <hr>

                                <div id="mostraSentidoDiv">
                                    <label for="mostraSentido" class="control-label">Sentidos Permitidos:
                                        <i title="SOMENTE IDA para poder realizar o Embarque por QRCode somente na Ida / IDA E VOLTA para poder realizar o Embarque por QRCode em ambos os sentidos" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <select name="mostraSentido" class="form-control filtroSelect2">
                                        <option value="0" <?php echo $linkapp->mostraSentido == 0 ? 'selected' : '';?>>Somente Ida</option>
                                        <option value="1" <?php echo $linkapp->mostraSentido == 1 ? 'selected' : '';?>>Ida e Volta</option>
                                    </select>
                                </div>

                                <hr>

                                <div id="avisoSonoroDiv">
                                    <label for="mostraSentido" class="control-label">Aviso Sonoro?
                                        <i title="Habilite ou desabilite os avisos sonoros para quando o passageiro realiza o Embarque/Desembarque por QRCode" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <hr class="mt-0">
                                
                                    <div class="row m-0">
                                        <div class="d-flex col col-6 flex-column align-items-center justify-content-center p-2">
                                        <input id="beep_embarque" name="beep_embarque" type="hidden" value="<?php echo $linkapp->beep_embarque;?>"/>    
                                        <b class="h6">Embarque</b>
                                            <i id="beep_embarque_btn" class="avisoSonoroApp fas <?php echo $linkapp->beep_embarque == 1 ? 'fa-volume-up' : 'fa-volume-mute';?>"></i>
                                        </div>

                                        <div class="d-flex col col-6 flex-column align-items-center justify-content-center p-2">
                                            <input id="beep_desembarque" name="beep_desembarque" type="hidden" value="<?php echo $linkapp->beep_desembarque;?>"/>
                                            <b class="h6">Desembarque</b>
                                            <i id="beep_desembarque_btn" class="avisoSonoroApp fas <?php echo $linkapp->beep_desembarque == 1 ? 'fa-volume-up' : 'fa-volume-mute';?>"></i>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                <hr>
                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="gerarQrcode" class="btn btn-warning w-100">GERAR QRCODE / Código</button>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button type="button" class="btn btn-danger w-100" onclick="closeLinkApp(<?php echo $linkapp->id ?>, '/app/qrcodes')">Fechar</button>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button type="button" class="btn btn-success w-100" onclick="saveLinkApp()">Salvar</button>
                        </div>
                    </div>
                </div>
                
                <div class="checkboxesFiltro" id="gruposSel">
                    <span class="titleCheckboxesFiltro" title="SELECIONAR GRUPOS"><i class="fa fa-users" aria-hidden="true"></i> SELECIONAR GRUPOS</span>
                    <i class="fa fa-window-close fechaCheckboxesFiltro" aria-hidden="true"></i>
                    <div class="buscaFiltro">
                        <input class="form-control buscaFiltroInput" type="text" placeholder="Digite aqui para filtrar..."/>
                    </div>
                    <div class="checkboxesFiltroLista">
                        <?php foreach($grupos as $gr): 
                            if(in_array($gr['ID_ORIGIN'], $groups)){
                        ?>
                        <input type="checkbox" checked class="grupoCheck checkFiltro" id="gr-<?php echo $gr['ID_ORIGIN'] ?>" value="<?php echo $gr['ID_ORIGIN'] ?>" name="grupo[]" />
                        <?php }else{?>
                        <input type="checkbox" class="grupoCheck checkFiltro" id="gr-<?php echo $gr['ID_ORIGIN'] ?>" value="<?php echo $gr['ID_ORIGIN'] ?>" name="grupo[]" />
                        <?php };?>
                        <label for="gr-<?php echo $gr['ID_ORIGIN'] ?>"><?php echo $gr['NOME'] ?>
                            <div class="btSetGroupDefault" title="Selecionar <?php echo $gr['NOME'] ?> como GRUPO PADRÃO">
                                SELECIONAR COMO PADRÃO
                            </div>
                            <div class="btRemoveGroupDefault" title="Desativar <?php echo $gr['NOME'] ?> como GRUPO PADRÃO">
                                GRUPO PADRÃO
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="checkboxesFiltroBts">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="limpaCheckFiltro" class="btn btn-warning w-100">Limpar</button>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="okCheckFiltro" class="btn btn-success w-100" type="button">OK</button>
                        </div>
                    </div>
                    <script type="text/javascript">
                        window.onload = function(e){ 
                            checkFiltrosCount($('.checkboxesFiltro[id=gruposSel]'));
                            saveQrCodeAppInfo();
                            let qrcode = new QRCode(document.getElementById("divQr"), {
                                text: '<?php echo BASE_URLB . 'app/qrcode?qr=' . $linkapp->qrcode ?>',
                                width: 300,
                                height: 300
                            });
                        }
                    </script>
                </div>
            </form>
        </div>
        
    </div>
</main>
</div>