<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2 class="pageTitle"></h2>
            <hr>
            <div>
                <h6>FILTROS:</h6>
                <hr>
                <div class="row col-sm-12 col-xs-12">
                    <form id="sendFormRelEscala" target="_blank" method="POST" action="/relatorioEscala/gerar" accept-charset="UTF-8" class="row col-sm-12 col-xs-12" >
                        <div class="col-sm-3 col-xs-12">
                            <label class="form-label">Unidade:</label>
                            <select id="uniEsc" name="unid" class="form-control controlPerson">
                                <option value="0">Selecione</option>
                                <?php foreach($unidades AS $uni): ?>
                                    <option value="<?php echo $uni->id; ?>"><?php echo $uni->descricao; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-3 col-xs-12">
                            <label class="form-label">Gestor:</label>
                            <select id="gestor" name="gestor" class="form-control controlPerson">
                                <option value="0">Selecione</option>
                                <?php foreach($users AS $ges): ?>
                                    <option 
                                        value="<?php echo $ges->id; ?>"
                                    ><?php echo $ges->nome; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div> 
                        <div class="col-sm-3 col-xs-12">
                            <label class="form-label">Mês:</label>
                            <select id="mes" name="mes" class="form-control controlPerson">
                                <?php foreach($meses AS $k => $ms): ?>
                                    <option 
                                        value="<?php echo $k; ?>"
                                        <?php echo $k == date("m") ? 'selected' : ''; ?>
                                    ><?php echo $ms; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-3 col-xs-12">
                            <label class="form-label">Ano:</label>
                            <select id="ano" name="ano" class="form-control controlPerson">
                                <?php foreach($ano AS  $an): ?>
                                    <option 
                                        value="<?php echo $an; ?>"
                                        <?php echo $an == date("Y") ? 'selected' : ''; ?>
                                    ><?php echo $an; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <hr style="width:100%">
                        <input type="hidden" name="tipo" id="tipo"/>
                        <div class="col-sm-12 col-xs-12">
                            <span class="btn btn-info" onclick="generateRelEsc(1)">Gerar SAP</span>
                            <span class="btn btn-warning" onclick="generateRelEsc(2)">Gerar Restaurante</span>
                            <span class="btn btn-secondary" onclick="generateRelEsc(3)">Gerar Taipastur</span>
                        </div>
                    </form>
                </div>
                <hr>
                
            </div>

        </div>
    </div>
</main>
</div>