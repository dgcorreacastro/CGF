<main class="py-4">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/userPhotos.css?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>" />
    <?php if($cad_pax_pics == 1):?>
        <script defer src="<?php echo BASE_URL; ?>assets/js/face-api.min.js"></script>
        <script defer src="<?php echo BASE_URL; ?>assets/js/userPhotos.js?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>"></script>
    <?php endif;?>
    <div class="personContainer" style="margin-top: 4em;">
        <div class="userPhoto disabled" style="cursor: default; background-image: url(<?php echo BASE_URL; ?>assets/images/pic_front_smiling.png?<?php echo time(); ?>)">
            <?php if($cad_pax_pics == 1):?>
                <span class="showPhotoMsg">Use o App <?php echo APP_NAME; ?> ID para adicionar fotos</span>
            <?php endif;?>
            <div class="monitorTag hide">
                <i class="fas fa-user-shield"></i>
                MONITOR
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="/cadastroPax/salvar" accept-charset="UTF-8" class="form-horizontal" id="createPaxForm">

                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Adicionar</b></h2>
                </div>

                <hr>

                <div class="card-create-body">
                    <div class="row mx-0">
                        <div class="col-sm-<?php echo $cad_pax_tag == 1 ? '6' : '8';?> col-xs-12">
                            <label for="name" class="control-label">Nome:</label>
                            <input class="form-control" name="name" type="text" id="name" required onblur="checkCod(this.value)">
                        </div>
                        <?php if($cad_pax_tag == 1):?>
                            <div class="col-sm-2 col-xs-12">
                                <label for="codigo" class="control-label">Código:</label>
                                <input disabled class="form-control" name="codigo" type="text" id="codigo" onblur="checkIfExistTag(this.value)">
                            </div>
                        <?php endif;?>
                        <div class="col-sm-2 col-xs-12">
                            <label for="grupo" class="control-label" style="margin-top: -0.4rem">Grupo: 
                                <span 
                                    style="cursor: pointer;margin-left: 5px;color: deepskyblue;font-size: 20px;"
                                    title="Adicionar novo Grupo"
                                    data-toggle="modal" 
                                    data-target="#modalNewGroup"
                                > <i class="fas fa-plus-circle"></i> </span> 
                            </label>
                            <select id="grupo" name="grupo"  class="form-control" required>
                            <?php foreach($grupos AS $gr): ?>
                                <option value="<?php echo $gr['ID_ORIGIN']; ?>">
                                <?php echo $gr['NOME']; ?> 
                            </option>
                            <?php endforeach; ?>
                            </select>
                        </div> 

                        <div class="col-sm-2 col-xs-12">
                            <label for="matricula" class="control-label">Matrícula funcional:</label>
                            <input class="form-control" name="matricula" type="text" id="matricula">
                        </div>


                        <div class="col-sm-2 col-xs-12">
                            <label for="monitor" class="control-label">Monitor:</label>
                            <select id="monitor" name="monitor"  class="form-control filtroSelect2" onchange="monitorToggle(this.value)">
                                <option value="0">NÃO</option>    
                                <option value="1">Sim</option>
                            </select>
                        </div> 

                        <div class="col-sm-10 col-xs-12">
                            <label for="polIda" class="control-label">End. Residência:</label>
                            <input class="form-control" name="residencia" type="text">
                        </div>
                    
                        <hr style="width: 100%;">

                        <div class="col-sm-4 col-xs-12 btn-primary py-2">
                            <label for="linhaIda" class="control-label" >Linha IDA (Principal):</label>
                            <select class="form-control" id="linhaIda" name="linhaIda" onchange="getItin(this, 1)">
                                <option value="0">Selecione</option>
                                <?php foreach($linhasIda AS $ln): ?>
                                    <option value="<?php echo $ln['ID_ORIGIN']; ?>" sentido="<?php echo $ln['SENTIDO']; ?>">
                                        <?php echo $ln['PREFIXO'] . " - " . $ln['NOME'] . " - " . $ln['DESCRICAO'] ." - ENTRADA"?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" id="itiIda" name="itiIda"/>
                        <input type="hidden" id="itiVolta" name="itiVolta"/>
                        <div class="col-sm-4 col-xs-12">
                            <label for="pontoEmbar" class="control-label">Ponto de Embarque:</label>
                            <select class="form-control" id="pontoEmbar" name="pontoEmbar">
                                <option value="">Selecione</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <label for="polIda" class="control-label">Poltrona IDA:</label>
                            <input class="form-control" name="polIda" type="text" id="polIda">
                        </div>

                        <hr style="width: 100%;">

                        <div class="col-sm-4 col-xs-12 btn-warning py-2">
                            <label for="linhaVolta" class="control-label">Linha VOLTA (Principal):</label>
                            <select class="form-control" id="linhaVolta" name="linhaVolta" onchange="getItin(this, 2)">
                                <option value="">Selecione</option>
                                <?php foreach($linhasVolta AS $ls): ?>
                                    <option value="<?php echo $ls['ID_ORIGIN']; ?>" sentido="<?php echo $ls['SENTIDO']; ?>">
                                        <?php echo $ls['PREFIXO'] . " - " . $ls['NOME'] . " - " . $ls['DESCRICAO'] ." - RETORNO"?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <label for="pontoDesmbar" class="control-label">Ponto de Desembarque:</label>
                            <select class="form-control" id="pontoDesmbar" name="pontoDesmbar">
                                <option value="">Selecione</option>
                            </select>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <label for="polVolta" class="control-label">Poltrona Volta:</label>
                            <input class="form-control" name="polVolta" type="text" id="polVolta">
                        </div>

                    </div>
                    <hr style="width: 100%;">
                    <br>
                    <h5>Linhas Adicionais:</h5>
                    <div class="row align-items-center justify-content-center">
                        <div class="col-sm-2 col-xs-12">
                            <span class="btn btn-primary w-100" onclick="incluirNovaLinhaIda()">Incluir Linha IDA</span>
                        </div>
                        <div class="col-sm-2 col-xs-12">
                            <span class="btn btn-warning w-100" onclick="incluirNovaLinhaVolta()">Incluir Linha VOLTA</span>
                        </div>
                    </div>
                    <hr>
                    <table class="table" style="color: white !important; border-collapse: separate; border-spacing: 0 5px;">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">Linha</th>
                            </tr>
                        </thead>
                        <tbody id="linesAdicional"></tbody>
                    </table>
                </div>

                <hr>

                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="<?php echo $_SESSION['prev']?>" class="btn btn-danger w-100">Fechar</a>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button type="button" class="btn btn-success w-100" onclick="createPax()">Salvar</button>
                        </div>
                    </div>
                </div>
                    
                
            </form>
        </div>

    </div>

    <div class="modal fade" id="modalNewGroup" tabindex="-1" role="dialog" aria-labelledby="modalNewGroupLabel" aria-hidden="true" style="color: black;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNewGroupLabel">Novo Grupo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                    <div class="col-sm-12 col-xs-12">
                        <label for="groupUserID" class="control-label">Grupo Usuário:</label>
                        <select class="form-control" id="groupUserID" name="groupUserID">
                            <option value="0">Selecione</option>
                            <?php foreach($gruposUser AS $gru): ?>
                                <option value="<?php echo $gru['id']; ?>"><?php echo $gru['NOME']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>  
                <?php }; ?>  
                <div class="col-sm-12 col-xs-12">
                    <label for="groupNew" class="control-label">Nome Grupo:</label>
                    <input class="form-control" id="groupNew" type="text">
                </div>      
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="includeNewGroup()">Salvar</button>
            </div>
        </div>
    </div>

</main>