<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/configuracoes/totemCadastrarUser" accept-charset="UTF-8" class="form-horizontal">
        <div class="card-body">
            <div class="card-create-header">
            <h2 class="pageTitle"> <b class="h4">&#10148; Adicionar Link Passageiro</b></h2>
            </div>
            <hr>
            <div class="card-create-body">
                <div class="row">
                    <div class="col-sm-5 col-xs-12">
                        <label for="ID_ORIGIN" class="control-label">Cliente:</label>
                        <select class="form-control" id="ID_ORIGIN" name="ID_ORIGIN">
                        <?php foreach($grLin as $tot): ?>
                            <option value="<?php echo $tot['ID_ORIGIN'];?>"><?php echo utf8_decode(utf8_encode($tot['NOME'])); ?></option>
                        <?php endforeach; ?>
                    </select>
                    </div> 
                    <div class="col-sm-6 col-xs-12">
                        <label for="LINK" class="control-label">Link Passageiro:</label>
                        <input class="form-control" name="LINK" type="text" id="LINK">
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="holdFiltroSelect">
                            <label for="grupo" class="form-label">Grupo:</label>
                            <span class="filtroSelect" title="SELECIONE UM GRUPO" originaltxt="SELECIONE UM GRUPO" checkboxesFiltro="gruposSel"><i class="fa fa-users" aria-hidden="true"></i> <texto>SELECIONE UM GRUPO</texto></span>
                        </div>
                        <hr style="width:100%">
                        Grupos Selecionados:
                        <div class="checkboxesFiltroSelecionados" id="gruposSel">
                        
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card-create-footer">
                <div class="row d-flex justify-content-end">
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button id="gerarLinkPax" class="btn btn-warning w-100">GERAR LINK</button>                        
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <a href="/configuracoes/totemUser" class="btn btn-danger w-100">Fechar</a>
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button class="btn btn-success w-100">Salvar</button>
                    </div>
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
                <?php foreach($grupos as $gr): ?>
                <input type="checkbox" class="grupoCheck checkFiltro" id="gr-<?php echo $gr['ID_ORIGIN'] ?>" value="<?php echo $gr['ID_ORIGIN'] ?>" name="grupo[]" />
                <label for="gr-<?php echo $gr['ID_ORIGIN'] ?>">
                    <?php echo $gr['NOME'] ?>
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
                    setActiveMenu('/configuracoes/totemUser/');
                }
            </script>
        </div>
        </form>
    </div>
</main>
</div>