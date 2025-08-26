<main class="py-4">
        
    <div class="personContainer">
    <form method="POST" action="/cadastroPax/salvar" accept-charset="UTF-8" class="form-horizontal">
    <div class="card-body">

    <div class="card-create-header">
        <h2 class="pageTitle"></h2>
    </div>
    <hr>
    <div class="card-create-body">
        <div class="row">
            <div class="col-sm-4 col-xs-12">
                <label for="name" class="control-label">Nome:</label>
                <input class="form-control" name="name" type="text" id="name" required>
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="codigo" class="control-label">Código:</label>
                <input class="form-control" name="codigo" type="text" id="codigo">
            </div>
            <?php if(!isset($_SESSION['cFret'])){ ?>
                <div class="col-sm-3 col-xs-12">
                    <label for="grupo" class="control-label">Grupo:</label>
                    <select id="grupo" name="grupo"  class="form-control" required>
                    <?php foreach($grupos AS $gr): ?>
                        <option value="<?php echo $gr['ID_ORIGIN']; ?>"><?php echo utf8_encode($gr['NOME']); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div> 
            <?php } ; ?>

            <?php if(isset($_SESSION['cFret'])){ ?>
                <div class="col-sm-3 col-xs-12">
                    <label for="unidadeID" class="control-label">Unidade:</label>
                    <select id="unidadeID" name="unidadeID" class="form-control">
                        <option value="">Selecione</option>
                        <?php foreach($unidades AS $uni): ?>
                            <option 
                                value="<?php echo $uni->id; ?>"
                            ><?php echo $uni->descricao; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php } ; ?>

            <div class="col-sm-3 col-xs-12">
                <label for="cpf" class="control-label">CPF:</label>
                <input class="form-control" name="cpf" type="text" id="cpf">
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="matricula" class="control-label">Matrícula funcional:</label>
                <input class="form-control" name="matricula" type="text" id="matricula">
            </div>
            <!-- <div class="col-sm-3 col-xs-12">
                <label for="setorID" class="control-label">Setor:</label>
                <select class="form-control" id="setorID" name="setorID">
                    <option value="">Selecione</option>
                    <?php foreach($setor AS $set): ?>
                        <option value="<?php echo $set->id; ?>"
                        ><?php echo $set->descricao; ?></option>
                    <?php endforeach; ?>
                </select>
            </div> -->
            <div class="col-sm-2 col-xs-12">
                <label for="funcao" class="control-label">Função:</label>
                <input class="form-control" name="funcao" type="text">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="centroCusto" class="control-label">Centro de Custo:</label>
                <input class="form-control" name="centroCusto" type="text">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="descricaoCC" class="control-label">Descrição C.C.:</label>
                <input class="form-control" name="descricaoCC" type="text">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="usaFret" class="control-label">Usa Fretado? </label>
                <select name="usaFret" class="form-control filtroSelect2">
                    <option value="0">Selecione</option>
                    <option value="1">SIM</option>
                    <option value="2">NÃO</option>
                </select>
            </div>

            <hr style="width: 100%;">

            <div class="col-sm-4 col-xs-12">
                <label for="linhaIda" class="control-label" >Linha IDA:</label>
                <select class="form-control" id="linhaIda" name="linhaIda" onchange="getItin(1)">
                    <option value="0">Selecione</option>
                    <?php foreach($linhas AS $ln): ?>
                        <option value="<?php echo $ln['ID']; ?>" sentIda="<?php echo $ln['SENTIDO']; ?>">
                            <?php echo $ln['PREFIXO'] . " -" . $ln['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-8 col-xs-12">
                <label for="itiIda" class="control-label">Itinerário IDA:</label>
                <select class="form-control" id="itiIda" name="itiIda">
                    <option value="">Selecione</option>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="resEmbar" class="control-label">Residência Embarque:</label>
                <select class="form-control" id="resEmbar" name="resEmbar">
                    <option value="">Selecione</option>
                    <?php foreach($residEmb AS $rs): ?>
                        <option value="<?php echo $rs['ID']; ?>"><?php echo $rs['NOME']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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

            <div class="col-sm-4 col-xs-12">
                <label for="linhaVolta" class="control-label">Linha Volta:</label>
                <select class="form-control" id="linhaVolta" name="linhaVolta" onchange="getItin(2)">
                    <option value="">Selecione</option>
                    <?php foreach($linhas AS $ls): ?>
                        <option value="<?php echo $ls['ID']; ?>" sentVol="<?php echo $ls['SENTIDO']; ?>">
                            <?php echo $ls['PREFIXO'] . " -" . $ls['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-8 col-xs-12">
                <label for="itiVolta" class="control-label">Itinerário Volta:</label>
                <select class="form-control" id="itiVolta" name="itiVolta">
                    <option value="">Selecione</option>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="resDesmbar" class="control-label">Residência Desembarque:</label>
                <select class="form-control" id="resDesmbar" name="resDesmbar">
                    <option value="">Selecione</option>
                    <?php foreach($residEmb AS $rs2): ?>
                        <option value="<?php echo $rs2['ID']; ?>"><?php echo $rs2['NOME']; ?></option>
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
        </div>
        <hr style="width: 100%;">
        <div class="card-create-footer">
            <div class="row d-flex justify-content-end">
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <a href="/cadastroPax/" class="btn btn-danger w-100">Fechar</a>
                </div>
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <button class="btn btn-success w-100">Salvar</button>
                </div>
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    
                </div>
            </div>
        </div>
    </form>
    </div>

</main> 