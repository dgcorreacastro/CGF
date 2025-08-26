<main class="py-4">
    <div class="personContainer">
        <div class="card-body">

            <div class=" ">
                <form method="POST" action="/parameterEscala/update" accept-charset="UTF-8" class="form-horizontal">
                    

                    <div class="card-create-header">
                        <h2 class="darkcyan"><i class="fas fa-cogs darkcyan"></i> Parâmetros</h2>
                    </div>
                    <hr>
                    <div class="card-create-body">
                        
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <label for="unidadeID" class="control-label">Unidade:</label>
                                <select id="unidadeID" name="unidadeID" class="form-control" onchange="parametersGrupo()">
                                    <option>Selecione</option>

                                    <?php foreach($unidades AS $uni): ?>

                                        <option value="<?php echo $uni->id; ?>"><?php echo $uni->descricao; ?></option>
                                    
                                    <?php endforeach; ?>
                                    
                                </select>
                            </div>

                            <hr style="width:100%">

                            <?php foreach($meses AS $k => $mes) { ?>

            
                                <div class="col-sm-4 col-xs-4">
                                    <label for="mes" class="control-label">Mês:</label>
                                    <input class="form-control" name="mes[]" type="text" value="<?php echo $mes; ?>" readonly />
                                </div>

                                <!-- <div class="col-sm-4 col-xs-4">
                                    <label for="maxFolga" class="control-label">Máx. Folga Mês:</label>
                                    <?php if( isset( $param ) && isset($param[$k]->maxFolgaMes) ) { ?>
                                        <input id="md-<?php echo $k?>" class="form-control clearInput" name="maxFolga[]" type="text" value="<?php echo $param[$k]->maxFolgaMes; ?>" />
                                    <?php } else { ?>
                                        <input id="md-<?php echo $k?>" class="form-control clearInput" name="maxFolga[]" type="text" />
                                    <?php }?>

                                </div>

                                <div class="col-sm-4 col-xs-4">
                                    <label for="maxSemFolga" class="control-label">Máx. Dia sem Folga:</label>
                                    <?php if( isset( $param ) && isset($param[$k]->maxDiaSemFolga) ) { ?>
                                        <input id="mds-<?php echo $k?>" class="form-control clearInput" name="maxSemFolga[]" type="text" value="<?php echo $param[$k]->maxDiaSemFolga; ?>" />
                                    <?php } else { ?>
                                        <input id="mds-<?php echo $k?>" class="form-control clearInput" name="maxSemFolga[]" type="text" />
                                    <?php }?>
                                </div> -->
                                <div class="col-sm-4 col-xs-4">
                                    <label for="maxFolga" class="control-label">Máx. Folga Mês:</label>
                                    <input id="md-<?php echo $k?>" class="form-control clearInput" name="maxFolga[]" type="text" />
                                </div>

                                <div class="col-sm-4 col-xs-4">
                                    <label for="maxSemFolga" class="control-label">Máx. Dia sem Folga:</label>
                                    <input id="mds-<?php echo $k?>" class="form-control clearInput" name="maxSemFolga[]" type="text" />
                                </div>

                            <?php } ?>

                        </div>
                    </div>

                    <hr>
                    
                    <div class="card-create-footer">
                        <div class="row">
                            <div class="row d-flex justify-content-end">
                                <a href="/setores/" class="btn btn-danger w-100">Fechar</a>
                            </div>
                            <div class="row d-flex justify-content-end">
                                <button class="btn btn-success w-100">Salvar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <hr>
   
        </div>
    </div>
</main>
</div>