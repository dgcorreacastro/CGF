<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2 class="pageTitle"> <b class="h4"> Manualmente</b></h2>
            <hr>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">

                        <div class="row justify-content-center m-0"> 

                            <?php if($get_veic_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="updateVeiculos()"><i class="fas fa-bus mnon"></i> Importar Veículos</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_gr_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-info btn-lg w-100" onclick="updateGRLinhas()"><i class="fas fa-users mnon"></i> Importar Grupos</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_cag_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-dark btn-lg w-100" onclick="updateCAGrupo()"><i class="fas fa-shopping-bag"></i> Importar Clientes</button>
                                </div>
                            <?php endif;?>
                            
                            <?php if($get_linha_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-warning btn-lg w-100" onclick="updateLinhas()"><i class="fas fa-route mnon"></i> Importar Linhas</button>
                                </div>
                            <?php endif;?>
                            
                            <?php if($get_iti_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-success btn-lg w-100" onclick="updateItine()"><i class="fas fa-road"></i> Importar Itinerários</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_trips_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-light btn-lg w-100" onclick="updateViagens()"><i class="fas fa-traffic-light"></i> Importar Viagens do Dia</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_pax_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-danger btn-lg w-100" data-toggle="modal" data-target="#modalCA"><i class="fas fa-people-arrows mnon"></i> Importar Passageiros</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_tag_veltrac == 1):?>
                                <div class="form-group col-xs-12 col-md-auto mx-3 my-0 mt-3" style="min-width: 300px;">
                                    <button type="button" class="btn btn-secondary btn-lg w-100" data-toggle="modal" data-target="#modalRfid"><i class="fas fa-tags"></i> Exportar TAGS</button>
                                </div>
                            <?php endif;?>

                            <?php if($get_veic_veltrac == 0 && $get_linha_veltrac == 0 && $get_gr_veltrac == 0 && $get_cag_veltrac == 0 && $get_iti_veltrac == 0 && $get_trips_veltrac == 0 && $get_tag_veltrac == 0 && $get_pax_veltrac == 0):?>
                                
                                <div class="d-flex w-100 flex-row flex-wrap align-items-center justify-content-center align-self-center m-0">
                                    <div class="col-xs-12 col-md-auto m-0 p-0">
                                        Nenhuma tabela selecionada para atualizar. Ative as tabelas para atualizar em:
                                    </div>
                                    <div class="col-xs-12 col-md-auto m-0 mt-3 mt-md-0">
                                        <a href="/configuracoes/parametro" class="btn btn-warning w-100"><i class="fas fa-cogs mnon"></i> Configurações > Parâmetros</a>
                                    </div>
                                </div>
                                
                            <?php endif;?>

                        </div>

                    </div>
                </div>
            <hr>
        </div>
    </div>

</main>

<?php if($get_pax_veltrac == 1):?>
    <!-- Modal de CA -->
    <div class="modal fade" id="modalCA" role="dialog" aria-labelledby="modalCALabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalCALabel"></h5><br>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                    <div class="col-sm-12 col-xs-12">
                        <label for="groupIdCa" class="control-label">Selecione o Grupo Usuário:</label>
                        <select class="form-control" id="groupIdCa" name="groupIdCa" onChange="checkUpdateCA(this.value)">
                            <option value="0">Selecione</option>
                            <?php foreach($gruposUser AS $gru): ?>
                                <option value="<?php echo $gru['id']; ?>"><?php echo utf8_decode(utf8_encode($gru['NOME'])); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="dbType" id="dbType" value="">
                        <hr>
                    </div>  
                    <?php }; ?>  
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelCa">Cancelar</button>
            <button type="button" class="btn btn-danger updateCA" disabled onclick="updateCA()">Atualizar</button>
        </div>
        </div>
    </div>
    </div>
<?php endif;?>

<?php if($get_tag_veltrac == 1):?>
    <!-- Modal de RFID -->
    <div class="modal fade" id="modalRfid" role="dialog" aria-labelledby="modalRfidLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalRfidLabel"></h5><br>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                    <div class="col-sm-12 col-xs-12">
                        <label for="groupId" class="control-label">Selecione o Grupo Usuário:</label>
                        <select class="form-control" id="groupId" name="groupId" onChange="checkUpdateRfid(this.value)">
                            <option value="0">Selecione</option>
                            <?php foreach($gruposUser AS $gru): ?>
                                <option value="<?php echo $gru['id']; ?>"><?php echo utf8_decode(utf8_encode($gru['NOME'])); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="dbType" id="dbType" value="">
                        <hr>
                    </div>  
                    <?php }; ?>  
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelRfid">Cancelar</button>
            <button type="button" class="btn btn-danger updateRfids" disabled onclick="updateRfids()">Atualizar</button>
        </div>
        </div>
    </div>
    </div>
<?php endif;?>