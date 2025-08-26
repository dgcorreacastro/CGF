<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/configuracoes/atualizarTotemEuro" accept-charset="UTF-8" class="form-horizontal" id="formEuro">
            <input type="hidden" name="idToten" id="idToten" value="<?php echo  $totemEdt['geral']['id'] ?>">
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Editar Link</b></h2>
                </div>
                <hr>
                <div class="card-create-body">
                    <div class="row">
                        <div class="col-sm-5 col-xs-12">
                            <label for="ID_ORIGIN" class="control-label">Cliente:</label>

                            <select class="form-control" id="ID_ORIGIN" name="ID_ORIGIN">
                                <option value="<?php echo $totemEdt['geral']['grupo_linhas_id']; ?>">EUROFARMA</option>
                            </select>
                        </div> 
                    
                        <div class="col-sm-5 col-xs-12">
                            <label for="LINK" class="control-label">Link:</label>
                            <input class="form-control" name="LINK" type="text" id="LINK" 
                            value="<?php echo $totemEdt['geral']['link']; ?>">
                        </div>                       

                        <div class="col-sm-2 col-xs-12">
                            <label for="Ativo" class="control-label">Ativo:</label>
                            <select class="form-control filtroSelect2" id="Ativo" name="Ativo">
                                <option value="1" <?php echo $totemEdt['geral']['Ativo'] == 1 ? "selected" : ""; ?> >SIM</option>
                                <option value="2" <?php echo $totemEdt['geral']['Ativo'] == 2 ? "selected" : ""; ?> >NÃO</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div id="bodyMapsNew">
                        <div class="loadingMapa">
                            <h2>Carregando mapa...</h2>
                            <div class="progressBar">
                            <span id="progressNumber">0%</span>
                            </div>
                        </div>
                        <div id="appMapaEuro" class="loading">
                            
                            <input type="hidden" id="mapaBackground" value="<?php echo BASE_URL; ?>assets/images/pontos.jpg?<?php echo time(); ?>" />
                                <div id="telaRotaMapaEuro">
                                <div class="mapaEuroNew" id="mapaEuroNew">
                                    <?php 
                                    foreach($itens as $k => $mark){ 
                                    $pos = json_decode($mark['posicaoIcone']);
                                    $top = ($pos->top);
                                    $left = ($pos->left);
                                    $nomePonto = $mark['nome_ponto'];
                                    ?>
                                    <div class="pontoMapaEuroNew" id="<?php echo $mark['id'] ?>" idCount="<?php echo $k+1; ?>"
                                        style="
                                        top: <?php echo $top; ?>px; 
                                        left: <?php echo $left; ?>px;">
                                        <div class="menuPonto">
                                            <span acao="editarPonto">
                                            <i class="fas fa-edit"></i>
                                            Editar Ponto
                                            </span>
                                            <span acao="removerPonto">
                                            <i class="fas fa-trash-alt"></i>
                                            Remover Ponto
                                            </span>
                                        </div>
                                        <span class="loaderMapaEuroNew"></span>
                                        <i class="fa fa-window-close closePonto" aria-hidden="true"></i>
                                        <span class="nomePonto"><nome><?php echo $nomePonto; ?></nome></span>
                                        <div class="erroPonto">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <errorMsg></errorMsg>
                                        </div>
                                        <div class="dadosPonto">
                                            <div class="horariosMapaNew">
                                            <span class="tituloHorarios add" tipo="manha">Manhã</span>
                                            <ul class="manha"></ul>
                                            </div>

                                            <div class="horariosMapaNew">
                                            <span class="tituloHorarios add" tipo="tarde">Tarde</span>
                                            <ul class="tarde"></ul>
                                            </div>
                                            
                                            <div class="horariosMapaNew">
                                            <span class="tituloHorarios">Pico-Almoço</span>
                                            <ul class="picoAlmoco"></ul>
                                            </div>

                                            <div class="horariosMapaNew">
                                            <span class="tituloHorarios add" tipo="noite">Noite</span>
                                            <ul class="noite"></ul>
                                            </div>

                                            <div class="horariosMapaNew">
                                            <span class="tituloHorarios">Restaurante</span>
                                            <ul class="restaurante"></ul>
                                            </div>

                                        </div>
                                    </div>
                                    <?php } ?>
                                    <img class="logosPontos" src="<?php BASE_URL; ?>/assets/images/logos.png">
                                    <div class="legendasMapaNew">
                                    <span class="horaVerde">Horário Intermediário</span>
                                    <span class="horaAzul">Horário Fixo Restaurante</span>
                                    <span class="horaAmarelo">Horário de Pico</span>
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
                                <button id="gerarLinkPax" class="btn btn-warning w-100">GERAR LINK</button>
                            </div>
                            <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                                <a href="/configuracoes/totemEuro" class="btn btn-danger w-100">Fechar</a>
                            </div>
                            <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                                <button class="btn btn-success w-100" type="button" onclick="salvarTotemEuroNew()">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    window.onload = function(e){ 
                        setActiveMenu('/configuracoes/totemEuro/');
                    }
                </script>
            </div>
        </form>
    </div>
</main>