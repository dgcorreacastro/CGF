<main class="py-4">
            
    <div class="personContainer">

        <div class="card-body">
            <h2 class="pageTitle"> <b class="h4">Gerais</b></h2>
            <hr>
                <div class="col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                    <div class="x_panel">
                    <form method="POST" action="/configuracoes/parametroAtualizar" accept-charset="UTF-8"><input name="_method" type="hidden" value="PUT">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="row settings_section"> 
                                <h6 class="w-100 text-left text-md-center pl-2 pl-md-0"><i class="fas fa-info-circle"></i> Também podem ser configurados por Grupo, caso não sejam esses valores serão aplicados.</h6>
                                <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                    <label for="Distancia" class="form-label">Dist&acirc;ncia (METROS) / Totem:</label>
                                    <input class="form-control" name="Distancia" type="number" value="<?php echo $param['Distancia'] ?>" id="Distancia">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                    <label for="time_atualizar" class="form-label">Tempo atualizar (Minutos):
                                        <i title="Tempo, em minutos, que será usado para atualizar automaticamente informações em tela." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <input id="time_atualizar" class="form-control" min="0" name="time_atualizar" type="number" value="<?php echo $param['time_atualizar'] ?>">
                                </div>
                                <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                    <label for="ranger_dash" class="form-label">Tempo Ranger Dash (Minutos):
                                        <i title="Tempo, em minutos, que será aceito para menos ou para mais para calcular a pontualidade." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <input id="ranger_dash" class="form-control" min="0" name="ranger_dash" value="<?php echo $param['ranger_dash'] ?>" type="number">
                                </div>
                            </div>
                            <hr>
                            <div class="row justify-content-center settings_section"> 
                                <div class="form-group col-md-3 col-sm-3 col-xs-12">
                                    <label for="inactiveGroups" class="form-label">IDS Grupos Inativos:
                                        <i title="(Coloque separados por vírgula. Ex: 123,525)" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <input class="form-control" name="inactiveGroups" type="text" value="<?php echo $param['inactiveGroups'] ?>" id="inactiveGroups">
                                </div>

                                <div class="form-group col-md-3 col-sm-3 col-xs-12">
                                    <label for="rel_days" class="form-label">Intervalo Dias Relatórios:
                                        <i title="Intervalo Máximo de dias permitido para consulta de relatórios." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <input class="form-control" name="rel_days" type="number" min="1" step="1" value="<?php echo $param['rel_days'] ?>" id="rel_days">
                                </div> 

                                <div class="form-group col-md-3 col-sm-3 col-xs-12">
                                    <label for="qtd_agendas" class="form-label">Agendamentos Relatórios:
                                        <i title="Quantidade Máxima de Agendamentos de Relátorios permitidos por dia, por tipo de relatório, por usuário." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <input class="form-control" name="qtd_agendas" type="number" min="1" step="1" value="<?php echo $param['qtd_agendas'] ?>" id="qtd_agendas">
                                </div> 
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-stopwatch"></i> Cronômetro Relatórios</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="show_rel_timer" <?php echo $param['show_rel_timer'] == 1 ? 'checked':'' ?> name="show_rel_timer">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>
                            </div>                    
                            <hr>
                            <!-- tabelas update veltrac -->
                            <div class="row justify-content-center settings_section">
                                
                                <h5 class="w-100"><i class="fas fa-database"></i> Tabelas Update Veltrac <i title="Deixe ativas somente as tabelas que o <?php echo APP_NAME;?> deve atualizar com dados da Veltrac" style="color:red; font-size:15px" class="fas fa-question-circle"></i></h5>
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-bus"></i> Importar Veículos</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_veic_veltrac" <?php echo $param['get_veic_veltrac'] == 1 ? 'checked':'' ?> name="get_veic_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>

                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-users"></i> Importar Grupos</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_gr_veltrac" <?php echo $param['get_gr_veltrac'] == 1 ? 'checked':'' ?> name="get_gr_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>

                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-shopping-bag"></i> Importar Clientes</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_cag_veltrac" <?php echo $param['get_cag_veltrac'] == 1 ? 'checked':'' ?> name="get_cag_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>
                                
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-route"></i> Importar Linhas</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_linha_veltrac" <?php echo $param['get_linha_veltrac'] == 1 ? 'checked':'' ?> name="get_linha_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>

                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-road"></i> Importar Itinerários</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_iti_veltrac" <?php echo $param['get_iti_veltrac'] == 1 ? 'checked':'' ?> name="get_iti_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>

                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-traffic-light"></i> Importar Viagens do Dia</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_trips_veltrac" <?php echo $param['get_trips_veltrac'] == 1 ? 'checked':'' ?> name="get_trips_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>

                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fas fa-people-arrows"></i> Importar Passageiros</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_pax_veltrac" <?php echo $param['get_pax_veltrac'] == 1 ? 'checked':'' ?> name="get_pax_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>
                                
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3" title="As TAGS só poderão ser atualizadas manualmente em: Configurações > Atualizar DB">
                                            <label class="form-label mb-0"><i class="fas fa-tags"></i> Exportar TAGS</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="get_tag_veltrac" <?php echo $param['get_tag_veltrac'] == 1 ? 'checked':'' ?> name="get_tag_veltrac">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>
                                
                            </div> 
                            <!-- fim tabelas update veltrac -->
                            <hr>
                            <!-- apis google -->
                            <div class="row justify-content-center settings_section">
                                <div class="row w-100 justify-content-center align-items-center my-3" style="gap: 1em;">
                                    <img width="110" src="<?php echo BASE_URL; ?>assets/images/google_logo.svg">
                                    <h3 class="mb-0">APIS</h3>
                                </div>
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0"><i class="fab fa-google-play"></i> API <?php echo PORTAL_NAME;?> Ativa</label>
                                            <label class="switch mb-0">
                                            <input type="checkbox" id="apiKey_active" <?php echo $param['apiKey_active'] == 1 ? 'checked':'' ?> name="apiKey_active">
                                                <span class="slider round"></span>
                                            </label>
                                        
                                    </div>
                                </div>
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                        <label class="form-label mb-0"><i class="fab fa-google-play"></i> API App <?php echo APP_NAME;?> PASS Ativa</label>
                                        <label class="switch mb-0">
                                            <input type="checkbox" id="apiKey_app_active" <?php echo $param['apiKey_app_active'] == 1 ? 'checked':'' ?> name="apiKey_app_active">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- fim apis google -->
                            <hr>
                            <!-- gráficos -->
                            <div class="row justify-content-center settings_section" style="gap: 0 1em">
                                
                                <h5 class="w-100"><i class="fas fa-chart-area"></i> Gráficos</h5>
                                <h6 class="w-100">Pontualidade de Chegada das Viagens</h6>
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphPontualTxt" class="form-label"><?php echo $param['graphPontualTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphPontualTxtColor'];?>; background-color: <?php echo $param['graphPontualColor'];?>;" name="graphPontualTxt" type="text" id="graphPontualTxt" value="<?php echo $param['graphPontualTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphPontualColor" class="form-label"><?php echo $param['graphPontualTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphPontualColor" name="graphPontualColor" value="<?php echo $param['graphPontualColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphAdiantadoTxt" class="form-label"><?php echo $param['graphAdiantadoTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphAdiantadoTxtColor'];?>; background-color: <?php echo $param['graphAdiantadoColor'];?>;" name="graphAdiantadoTxt" type="text" id="graphAdiantadoTxt" value="<?php echo $param['graphAdiantadoTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAdiantadoColor" class="form-label"><?php echo $param['graphAdiantadoTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAdiantadoColor" name="graphAdiantadoColor" value="<?php echo $param['graphAdiantadoColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphAtrasadoTxt" class="form-label"><?php echo $param['graphAtrasadoTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphAtrasadoTxtColor'];?>; background-color: <?php echo $param['graphAtrasadoColor'];?>;" name="graphAtrasadoTxt" type="text" id="graphAtrasadoTxt" value="<?php echo $param['graphAtrasadoTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAtrasadoColor" class="form-label"><?php echo $param['graphAtrasadoTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAtrasadoColor" name="graphAtrasadoColor" value="<?php echo $param['graphAtrasadoColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                <div class="w-100"></div>
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphNesTxt" class="form-label"><?php echo $param['graphNesTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphNesTxtColor'];?>; background-color: <?php echo $param['graphNesColor'];?>;" name="graphNesTxt" type="text" id="graphNesTxt" value="<?php echo $param['graphNesTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphNesColor" class="form-label"><?php echo $param['graphNesTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphNesColor" name="graphNesColor" value="<?php echo $param['graphNesColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>

                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphAgendaTxt" class="form-label"><?php echo $param['graphAgendaTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphAgendaTxtColor'];?>; background-color: <?php echo $param['graphAgendaColor'];?>;" name="graphAgendaTxt" type="text" id="graphAgendaTxt" value="<?php echo $param['graphAgendaTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAgendaColor" class="form-label"><?php echo $param['graphAgendaTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAgendaColor" name="graphAgendaColor" value="<?php echo $param['graphAgendaColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <hr class="w-100">

                                <h6 class="w-100">Registros de Embarque</h6>
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphReTxt" class="form-label"><?php echo $param['graphReTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphReTxtColor'];?>; background-color: <?php echo $param['graphReColor'];?>;" name="graphReTxt" type="text" id="graphReTxt" value="<?php echo $param['graphReTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphReColor" class="form-label"><?php echo $param['graphReTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphReColor" name="graphReColor" value="<?php echo $param['graphReColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphSreTxt" class="form-label"><?php echo $param['graphSreTxt'];?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $param['graphSreTxtColor'];?>; background-color: <?php echo $param['graphSreColor'];?>;" name="graphSreTxt" type="text" id="graphSreTxt" value="<?php echo $param['graphSreTxt'];?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphSreColor" class="form-label"><?php echo $param['graphSreTxt'];?> Cor:</label>
                                        <input class="form-control" type="color" id="graphSreColor" name="graphSreColor" value="<?php echo $param['graphSreColor'];?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>

                                <hr class="w-100">

                                <h6 class="w-100">Cartões não Utilizados nos Últimos 7 dias</h6>
                                <div class="holdAppSelects">
                                    <div class="form-group">
                                        <label for="graphBarraColor" class="form-label">Cor das Barras:</label>
                                        <input class="form-control" type="color" id="graphBarraColor" name="graphBarraColor" value="<?php echo $param['graphBarraColor'];?>">
                                    </div>
                                </div>
                                
                            </div> 
                            <!-- fim gráficos -->
                            <hr>
                            <!-- versionamento -->
                            <div class="row justify-content-center settings_section">
                                <div class="form-group d-flex flex-column col-md-5 col-sm-5 col-xs-12">

                                    <?php
                                        $splitVersionamento = explode('.', $param['cgfVersionamento']); 
                                    ?>
                                    <label for="cgfVersionamento" class="form-label">Versionamento <?php echo APP_NAME; ?>:
                                        <i title="Código de Versionamento: Verde: Nova Versão, Azul: Nova Funcionalidade da Versão Atual, Vermelho: Correção de erros na Versão Atual" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                    </label>
                                    <div class="d-flex w-100 flex-row flex-nowrap align-items-center justify-content-center align-self-center m-0">

                                        <div class="p-2 bg-success m-2 rounded" style="width: 33%">
                                            <input onchange="changeCgfVersionamento()" class="form-control" name="vMajor" type="number" min="<?php echo $splitVersionamento[0] ?>" step="1" value="<?php echo $splitVersionamento[0] ?>" id="vMajor">
                                        </div>

                                        <div class="p-2 bg-info m-2 rounded" style="width: 33%">
                                            <input onchange="changeCgfVersionamento()" class="form-control" name="vMinor" type="number" min="0" step="1" value="<?php echo $splitVersionamento[1] ?>" id="vMinor">
                                        </div>

                                        <div class="p-2 bg-danger m-2 rounded" style="width: 33%">
                                            <input onchange="changeCgfVersionamento()" class="form-control" name="vPatch" type="number" min="0" step="1" value="<?php echo $splitVersionamento[2] ?>" id="vPatch">
                                        </div>

                                    </div>
                                    <input name="cgfVersionamento" type="hidden" value="<?php echo $param['cgfVersionamento'] ?>" id="cgfVersionamento" readonly>
                                    <input type="hidden" id="orginalCgfVersionamento" value="<?php echo $param['cgfVersionamento'] ?>">
                                    <input name="cgfVersion" type="hidden" value="<?php echo $param['cgfVersion'] ?>" id="cgfVersion">
                                    <input type="hidden" name="orginalCgfVersionamento" id="originalCgfVersion" value="<?php echo $param['cgfVersion'] ?>">
                                </div>
                            </div>
                             <!-- fim versionamento -->
                            <hr>
                            <div class="card-create-footer">
                                <div class="row d-flex justify-content-end">
                                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                                        <button type="submit" class="btn btn-primary w-100">Salvar</button>
                                    </div>
                                </div>
                            </div>
                    </form>
                    </div>
                </div>
        </div>
    </div>

</main>

<?php if(isset($_SESSION['versionChange'])):?>

    <script>
        window.onload = function(e){
            changeVersionScreen('<?php echo $_SESSION['controlVersionChange'];?>', '<?php echo $_SESSION['versionChange'];?>');
        }
    </script>
    
<?php 
    unset($_SESSION['clearCache']);
    unset($_SESSION['controlVersionChange']);
    unset($_SESSION['versionChange']);
    endif;
?>