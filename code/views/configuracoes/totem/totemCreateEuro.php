<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/configuracoes/atualizarTotemEuro" accept-charset="UTF-8" class="form-horizontal" id="formEuro">
            <input type="hidden" name="idToten" id="idToten" value="0">
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Adicionar</b></h2>
                </div>
                <hr>
                <div class="card-create-body">
                    <div class="row">
                        <div class="col-sm-5 col-xs-12">
                            <label for="ID_ORIGIN" class="control-label">Cliente:</label>

                            <select class="form-control" id="ID_ORIGIN" name="ID_ORIGIN">
                                <option value="11">EUROFARMA</option>
                            </select>
                        </div> 
                    
                        <div class="col-sm-5 col-xs-12">
                            <label for="LINK" class="control-label">Link:</label>
                            <input class="form-control" name="LINK" type="text" id="LINK" value="">
                        </div>                       

                        <div class="col-sm-2 col-xs-12">
                            <label for="Ativo" class="control-label">Ativo:</label>
                            <select class="form-control filtroSelect2" id="Ativo" name="Ativo">
                                <option value="1">SIM</option>
                                <option value="2">NÃO</option>
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
                                <button id="btnSalvarEuro" class="btn btn-success w-100" disabled type="button" onclick="salvarTotemEuroNew()">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                window.onload = function(e){ 
                    setActiveMenu('/configuracoes/totemEuro/');
                }
            </script>
        </form>
    </div>
</main>

