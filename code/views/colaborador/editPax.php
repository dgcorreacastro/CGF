<main class="py-4">
        
    <div class="personContainer">
    <form method="POST" action="/cadastroPax/salvarEdit" accept-charset="UTF-8" class="form-horizontal">
    <div class="card-body">

    <div class="card-create-header">
        <h2 class="pageTitle"></h2>
    </div>
    <hr>
    <div class="card-create-body">
        <div class="row">
            <input class="form-control" name="id" type="hidden" value="<?php echo $_GET['id'] ?>">
            <div class="col-sm-4 col-xs-12">
                <label for="name" class="control-label">Nome:</label>
                <input class="form-control" name="name" type="text" id="name" required value="<?php echo $pax['ca']['NOME'] ?>">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="codigo" class="control-label">Código:</label>
                <input class="form-control" name="codigo" type="text" id="codigo" value="<?php echo $pax['ca']['TAG'] ?>">
            </div>

            <?php if(!isset($_SESSION['cFret'])){ ?>
                <div class="col-sm-3 col-xs-12">
                    <label for="grupo" class="control-label">Grupo:</label>
                    <select id="grupo" name="grupo"  class="form-control" required>
                    <?php foreach($grupos AS $gr): ?>
                        <option 
                            value="<?php echo $gr['ID_ORIGIN']; ?>"
                            <?php echo ($gr['ID_ORIGIN'] == $pax['ca']['CONTROLE_ACESSO_GRUPO_ID']) ? 'selected' : '' ?>
                        ><?php echo utf8_encode($gr['NOME']); ?></option>
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
                                <?php echo $uni->id == $pax['ca']['unidadeID'] ? 'selected' : '' ?>
                            ><?php echo $uni->descricao; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php } ; ?>

            <div class="col-sm-3 col-xs-12">
                <label for="cpf" class="control-label">CPF:</label>
                <input class="form-control" name="cpf" type="text" id="cpf" value="<?php echo $pax['ca']['cpf'] ?>">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="matricula" class="control-label">Matrícula funcional:</label>
                <input class="form-control" name="matricula" type="text" id="matricula" value="<?php echo $pax['ca']['MATRICULA_FUNCIONAL'] ?>">
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="ativo" class="control-label">Ativo:</label>
                <select id="ativo" name="ativo"  class="form-control filtroSelect2" required>
                    <option value="1" <?php echo (1 == $pax['ca']['ATIVO']) ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?php echo (0 == $pax['ca']['ATIVO']) ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div> 

            <!-- <div class="col-sm-3 col-xs-12">
                <label for="setorID" class="control-label">Setor:</label>
                <select class="form-control" id="setorID" name="setorID">
                    <option value="">Selecione</option>
                    <?php foreach($setor AS $set): ?>
                        <option value="<?php echo $set->id; ?>"
                        <?php echo ($set->id == $pax['ca']['setorID']) ? 'selected' : '' ?>
                        ><?php echo $set->descricao; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3 col-xs-12">
                <label for="funcaoID" class="control-label">Função:</label>
                <select class="form-control" id="funcaoID" name="funcaoID">
                    <option value="">Selecione</option>
                    <?php foreach($function AS $fun): ?>
                        <option value="<?php echo $fun->id; ?>"
                        <?php echo ($fun->id == $pax['ca']['funcaoID']) ? 'selected' : '' ?>
                        ><?php echo $fun->descricao; ?></option>
                    <?php endforeach; ?>
                </select>
            </div> -->

            <div class="col-sm-2 col-xs-12">
                <label for="funcao" class="control-label">Função:</label>
                <input 
                    class="form-control" 
                    name="funcao" 
                    type="text"
                    value="<?php echo $pax['ca']['funcao'] ?>"
                >
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="centroCusto" class="control-label">Centro de Custo:</label>
                <input 
                    class="form-control" 
                    name="centroCusto" 
                    type="text"
                    value="<?php echo $pax['ca']['centro_custo'] ?>"
                >
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="descricaoCC" class="control-label">Descrição C.C.:</label>
                <input 
                    class="form-control" 
                    name="descricaoCC" 
                    type="text"
                    value="<?php echo $pax['ca']['descricaoCentro'] ?>"
                >
            </div>
            <div class="col-sm-2 col-xs-12">
                <label for="usaFret" class="control-label">Usa Fretado? </label>
                <select name="usaFret" class="form-control filtroSelect2">
                    <option value="0">Selecione</option>
                    <option 
                        value="1"
                        <?php echo (1 == $pax['ca']['usaFret']) ? 'selected' : '' ?>
                    >SIM</option>
                    <option 
                        value="2"
                        <?php echo (2 == $pax['ca']['usaFret']) ? 'selected' : '' ?>
                    >NÃO</option>
                </select>
            </div>

            <hr style="width: 100%;">

            <div class="col-sm-4 col-xs-12">
                <label for="linhaIda" class="control-label" >Linha IDA:</label>
                <select class="form-control" id="linhaIda" name="linhaIda" onchange="getItin(1)">
                    <option value="">Selecione</option>
                    <?php foreach($linhas AS $ln): ?>
                        <option 
                            value="<?php echo $ln['ID']; ?>" 
                            sentIda="<?php echo $ln['SENTIDO']; ?>"
                            <?php echo ($ln['ID'] == $pax['ca']['LinhaIda']) ? 'selected' : '' ?>
                        >
                            <?php echo $ln['PREFIXO'] . " -" . $ln['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-8 col-xs-12">
                <label for="itiIda" class="control-label">Itinerário IDA:</label>
                <select class="form-control" id="itiIda" name="itiIda">
                    <?php if(isset($pax['itiIda']['ID'])){ ?>
                        <option value="<?php echo $pax['itiIda']['ID']; ?>">
                            <?php echo $pax['itiIda']['DESCRICAO']; ?>
                        </option>
                    <?php }; ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="resEmbar" class="control-label">Residência Embarque:</label>
                <select class="form-control" id="resEmbar" name="resEmbar">
                    <option value="">Selecione</option>
                    <?php foreach($residEmb AS $rs): ?>
                        <option value="<?php echo $rs['ID']; ?>"
                        <?php echo ($rs['ID'] == $pax['ca']['ponto_referencia_id_resid_embar']) ? 'selected' : '' ?>
                        >
                            <?php echo $rs['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="pontoEmbar" class="control-label">Ponto de Embarque:</label>
                <select class="form-control" id="pontoEmbar" name="pontoEmbar">
                    <option value="">Selecione</option>
                    <?php foreach($pax['pontosEmb'] AS $peb): ?>
                        <option value="<?php echo $peb['ID']; ?>"
                        <?php echo ($peb['ID'] == $pax['ca']['ponto_referencia_id_embarque']) ? 'selected' : '' ?>
                        ><?php echo $peb['NOME']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="polIda" class="control-label">Poltrona IDA:</label>
                <input class="form-control" name="polIda" type="text" id="polIda" value="<?php echo $pax['ca']['POLTRONAIDA'] ?>">
            </div>

            <hr style="width: 100%;">

            <div class="col-sm-4 col-xs-12">
                <label for="linhaVolta" class="control-label">Linha Volta:</label>
                <select class="form-control" id="linhaVolta" name="linhaVolta" onchange="getItin(2)">
                    <option value="">Selecione</option>
                    <?php foreach($linhas AS $ls): ?>
                        <option 
                            value="<?php echo $ls['ID']; ?>" 
                            sentVol="<?php echo $ls['SENTIDO']; ?>"
                            <?php echo ($ls['ID'] == $pax['ca']['LinhaVolta']) ? 'selected' : '' ?>
                        >
                            <?php echo $ls['PREFIXO'] . " -" . $ls['NOME']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-8 col-xs-12">
                <label for="itiVolta" class="control-label">Itinerário Volta:</label>
                <select class="form-control" id="itiVolta" name="itiVolta">
                    <?php if(isset($pax['itiVolta']['ID'])){ ?>
                        <option value="<?php echo $pax['itiVolta']['ID']; ?>">
                            <?php echo $pax['itiVolta']['DESCRICAO']; ?>
                        </option>
                        <?php } ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="resDesmbar" class="control-label">Residência Desembarque:</label>
                <select class="form-control" id="resDesmbar" name="resDesmbar">
                    <option value="">Selecione</option>
                    <?php foreach($residEmb AS $rs2): ?>
                        <option value="<?php echo $rs2['ID']; ?>"
                        <?php echo ($rs2['ID'] == $pax['ca']['ponto_referencia_id_resid_desem']) ? 'selected' : '' ?>
                        ><?php echo $rs2['NOME']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="pontoDesmbar" class="control-label">Ponto de Desembarque:</label>
                <select class="form-control" id="pontoDesmbar" name="pontoDesmbar">
                    <option value="">Selecione</option>
                    <?php foreach($pax['pontosDEmb'] AS $peb): ?>
                        <option value="<?php echo $peb['ID']; ?>"
                        <?php echo ($peb['ID'] == $pax['ca']['ponto_referencia_id_desembarque']) ? 'selected' : '' ?>
                        ><?php echo $peb['NOME']; ?> </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-xs-12">
                <label for="polVolta" class="control-label">Poltrona Volta:</label>
                <input class="form-control" name="polVolta" type="text" id="polVolta" value="<?php echo $pax['ca']['POLTRONAVOLTA'] ?>">
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