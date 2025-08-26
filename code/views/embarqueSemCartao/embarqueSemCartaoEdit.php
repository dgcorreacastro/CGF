<main class="py-4">
    
<div class="personContainer">
<form method="POST" action="/embarqueSemCartao/atualizar" accept-charset="UTF-8" class="form-horizontal">
<div class="card-body">
<input type="hidden" name="id" value="<?php echo $embarqueEdt['id']; ?>">
<div class="card-create-header">
    <h2 class="pageTitle"> - Editar</h2>
</div>
<hr>
<div class="card-create-body">
    <div class="row">
<div class="col-sm-3 col-xs-12">
<label for="data" class="control-label">Data:</label>
<input class="form-control" name="data" type="date" value="<?php echo $embarqueEdt['data']; ?>">
</div>
<div class="col-sm-3 col-xs-12">
<label for="horario_embarque" class="control-label">Hor&aacute;rio Embarque:</label>
<input class="form-control" name="horario_embarque" type="time" id="horario_embarque" value="<?php echo $embarqueEdt['horario_embarque']; ?>">
</div>
<div class="col-sm-3 col-xs-12">
<label for="numero_talao" class="control-label">N&ordm; do Tal&atilde;o:</label>
<input class="form-control" name="numero_talao" type="text" id="numero_talao" value="<?php echo $embarqueEdt['numero_talao']; ?>">
</div>
<div class="col-sm-3 col-xs-12">
<label for="prefixo_veiculo_id" class="control-label">Prefixo Ve&iacute;culo:</label>
<select class="form-control" id="prefixo_veiculo_id" name="prefixo_veiculo_id">
    <?php foreach($prefVeiculo as $prefv): ?>
        <option value="<?php echo $prefv['id']; ?>"
              <?php echo ($prefv['id'] == $embarqueEdt['prefixo_veiculo_id'] ? "selected" : ""); ?> 
            ><?php echo utf8_decode(utf8_encode($prefv['NOME'])); ?></option>
    <?php endforeach; ?>
</select>
</div>
<div class="col-sm-6 col-xs-12">
<label for="cliente_id" class="control-label">Cliente:</label>
<select class="form-control" id="cliente_id" name="cliente_id">
    <?php foreach($cliente as $cli): ?>
        <option value="<?php echo $cli['id']; ?>"
            <?php echo ($cli['id'] == $embarqueEdt['cliente_id'] ? "selected" : ""); ?> 
            ><?php echo utf8_decode(utf8_encode($cli['NOME'])); ?></option>
    <?php endforeach; ?>
</select>
</div>
<div class="col-sm-6 col-xs-12">
<label for="nome_passageiro" class="control-label">Nome Passageiro:</label>
<input class="form-control phone" name="nome_passageiro" type="text" id="nome_passageiro" value="<?php echo $embarqueEdt['nome_passageiro']; ?>">
</div>
<div class="col-sm-6 col-xs-12">
<label for="linha_id" class="control-label">Linha:</label>
<select class="form-control" id="linha_id" name="linha_id">
    <?php foreach($linhas as $li): ?>
        <option value="<?php echo $li['id']; ?>"
            <?php echo ($li['id'] == $embarqueEdt['linha_id'] ? "selected" : ""); ?> 
            ><?php echo utf8_decode(utf8_encode($li['NOME'])) .' - '. utf8_decode(utf8_encode($li['PREFIXO'])); ?></option>
    <?php endforeach; ?>
</select>
</div>
<div class="col-sm-3 col-xs-12">
<label for="registro_passageiro" class="control-label">Registro Passageiro:</label>
<input class="form-control cellphone" name="registro_passageiro" type="text" id="registro_passageiro" value="<?php echo $embarqueEdt['registro_passageiro']; ?>">
</div>
<div class="col-sm-3 col-xs-12">
<label for="grupo_acesso_id" class="control-label">Grupo Acesso:</label>
<select class="form-control" id="grupo_acesso_id" name="grupo_acesso_id">
    <?php foreach($grupoAcesso as $grA): ?>
        <option value="<?php echo $grA['id']; ?>"
            <?php echo ($grA['id'] == $embarqueEdt['grupo_acesso_id'] ? "selected" : ""); ?> 
            ><?php echo utf8_decode(utf8_encode($grA['NOME'])); ?></option>
    <?php endforeach; ?>
</select>
</div>
</div>
</div>
<hr>
<div class="card-create-footer">
    <div class="row d-flex justify-content-end">
        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
            <a href="/embarqueSemCartao/" class="btn btn-danger w-100">Fechar</a>
        </div>
        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
            <button class="btn btn-success w-100">Salvar</button>
        </div>
    </div>
</div>
</div>
</form>
</div>

</main>