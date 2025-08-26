<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/configuracoes/atualizarTotem" accept-charset="UTF-8" class="form-horizontal">
        <div class="card-create">
            <div class="card-create-header">
                <h2 class="pageTitle"> <b class="h4">&#10148; Editar</b></h2>
            </div>
            <hr>
            <div class="card-create-body">
                <div class="row">
                    <div class="col-sm-5 col-xs-12">
                        <label for="ID_ORIGIN" class="control-label">Cliente:</label>
                        <select class="form-control" id="ID_ORIGIN" name="ID_ORIGIN">
                        <?php foreach($grLin as $tot):?>
                            <option value="<?php echo $tot['ID_ORIGIN']; ?>" 
                                <?php echo ($tot['ID_ORIGIN'] == $totemEdt['ID_ORIGIN'] ? "selected" : ""); ?> 
                            >
                            <?php echo utf8_decode(utf8_encode($tot['NOME'])); ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div> 
                    <div class="col-sm-6 col-xs-12">
                        <label for="LINK" class="control-label">Link:</label>
                        <input class="form-control" name="LINK" type="text" id="LINK" value="<?php echo $totemEdt['LINK']; ?>">
                    </div>
                </div>
            </div> 
            <hr>
            <div class="card-create-footer">
                <div class="row d-flex justify-content-end">
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button id="gerarLink" class="btn btn-warning w-100">GERAR LINK</button>
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <a href="/configuracoes/totem" class="btn btn-danger w-100">Fechar</a>
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button class="btn btn-success w-100">Salvar</button>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                window.onload = function(e){ 
                    setActiveMenu('/configuracoes/totem/');
                }
            </script>
        </div>
        </form>
    </div>
</main>
</div>