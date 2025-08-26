 <main class="py-4">
            
    <div class="personContainer">

        <div class="card-body">
            <h3><i class="fas fa-cogs "></i> Parâmetros - <?php echo $parameter->NOME;?></h3>
            <hr>
                <div class="col-md-12 col-sm-12 col-xs-12" style="text-align: center;">
                    <div class="x_panel">
                        <form method="POST" action="/grupos/updateParameter" accept-charset="UTF-8">
                            <input type="hidden" id="idGroup" name="idGroup" value="<?php echo $_GET['id']; ?>">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="row settings_section"> 
                                    <h6 class="w-100 text-left text-md-center pl-2 pl-md-0"><i class="fas fa-info-circle"></i> Não preencha valores que deseje usar os configurados em Parâmetros Gerais</h6>
                                    <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                        <label for="Distancia" class="form-label">Dist&acirc;ncia (METROS) / Totem:</label>
                                        <input class="form-control" name="Distancia" type="number" value="<?php echo $parameter->Distancia ?>" id="Distancia">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                        <label for="time_atualizar" class="form-label">Tempo atualizar (Minutos):
                                            <i title="Tempo, em minutos, que será usado para atualizar automaticamente informações em tela." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                        </label>
                                        <input id="time_atualizar" class="form-control" min="0" name="time_atualizar" type="number" value="<?php echo $parameter->time_atualizar ?>">
                                    </div>
                                    <div class="form-group col-md-4 col-sm-4 col-xs-12">
                                        <label for="ranger_dash" class="form-label">Tempo Ranger Dash (Minutos):
                                            <i title="Tempo, em minutos, que será aceito para menos ou para mais para calcular a pontualidade." style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                        </label>
                                        <input id="ranger_dash" class="form-control" min="0" name="ranger_dash" value="<?php echo $parameter->ranger_dash ?>" type="number">
                                    </div>
                                </div>
                                <hr>
                                <!-- cadastro passageiros -->
                                <div class="row justify-content-center settings_section">
                                    <h6 class="w-100 text-left text-md-center pl-2 pl-md-0">Tipo Controle de Acesso:</h6>
                                    <div class="form-group col-xs-12 col-md-auto">
                                        <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                                <label class="form-label mb-0"><i class="fas fa-portrait"></i> <?php echo APP_NAME; ?> ID</label>
                                                <label class="switch mb-0">
                                                    <input type="checkbox" id="cad_pax_pics" <?php echo $parameter->cad_pax_pics == 1 ? 'checked':'' ?> name="cad_pax_pics">
                                                    <span class="slider round"></span>
                                                </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-xs-12 col-md-auto">
                                        <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                                <label class="form-label mb-0"><i class="fas fa-tags"></i> <?php echo APP_NAME; ?> TAG</label>
                                                <label class="switch mb-0">
                                                    <input type="checkbox" id="cad_pax_tag" <?php echo $parameter->cad_pax_tag == 1 ? 'checked':'' ?> name="cad_pax_tag">
                                                    <span class="slider round"></span>
                                                </label>
                                        </div>
                                    </div>
                                
                                </div> 
                                <!-- fim cadastro passageiros -->
                                <hr>
                                <!-- informe diário -->
                                <div class="row justify-content-center settings_section">
                                <h6 class="w-100 text-left text-md-center pl-2 pl-md-0"><i class="fas fa-info-circle"></i> Será enviado para todos os usuários do Grupo <?php echo $parameter->NOME;?></h6>
                                    <div class="form-group col-xs-12 col-md-auto">
                                        <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                                <label class="form-label mb-0"><i class="fas fa-file mnon"></i> ENVIAR <?php echo INFO_TITULO; ?></label>
                                                <label class="switch mb-0">
                                                    <input type="checkbox" id="daily_info" <?php echo $parameter->daily_info == 1 ? 'checked':'' ?> name="daily_info">
                                                    <span class="slider round"></span>
                                                </label>
                                        </div>
                                    </div>                                
                                </div> 
                                <!-- fim informe diário -->
                            </div>
                            <hr>
                            <!-- gráficos -->
                            <div class="row justify-content-center settings_section" style="gap: 0 1em">
                                
                                <h5 class="w-100"><i class="fas fa-chart-area"></i> Gráficos</h5>
                                <div class="form-group col-xs-12 col-md-auto">
                                    <div class="form-group switch-group mb-0 mt-0 mt-md-3">
                                            <label class="form-label mb-0">Usar de Parâmetros Gerais</label>
                                            <label class="switch mb-0">
                                                <input type="checkbox" id="graphDefault" <?php echo $parameter->graphDefault == 1 ? 'checked':'' ?> name="graphDefault">
                                                <span class="slider round"></span>
                                            </label>
                                    </div>
                                </div>
                                <h6 class="w-100">Pontualidade de Chegada das Viagens</h6>
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphPontualTxt" class="form-label"><?php echo $parameter->graphPontualTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphPontualTxtColor;?>; background-color: <?php echo $parameter->graphPontualColor;?>;" name="graphPontualTxt" type="text" id="graphPontualTxt" value="<?php echo $parameter->graphPontualTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphPontualColor" class="form-label"><?php echo $parameter->graphPontualTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphPontualColor" name="graphPontualColor" value="<?php echo $parameter->graphPontualColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphAdiantadoTxt" class="form-label"><?php echo $parameter->graphAdiantadoTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphAdiantadoTxtColor;?>; background-color: <?php echo $parameter->graphAdiantadoColor;?>;" name="graphAdiantadoTxt" type="text" id="graphAdiantadoTxt" value="<?php echo $parameter->graphAdiantadoTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAdiantadoColor" class="form-label"><?php echo $parameter->graphAdiantadoTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAdiantadoColor" name="graphAdiantadoColor" value="<?php echo $parameter->graphAdiantadoColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphAtrasadoTxt" class="form-label"><?php echo $parameter->graphAtrasadoTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphAtrasadoTxtColor;?>; background-color: <?php echo $parameter->graphAtrasadoColor;?>;" name="graphAtrasadoTxt" type="text" id="graphAtrasadoTxt" value="<?php echo $parameter->graphAtrasadoTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAtrasadoColor" class="form-label"><?php echo $parameter->graphAtrasadoTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAtrasadoColor" name="graphAtrasadoColor" value="<?php echo $parameter->graphAtrasadoColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>

                                <div class="w-100"></div>

                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphNesTxt" class="form-label"><?php echo $parameter->graphNesTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphNesTxtColor;?>; background-color: <?php echo $parameter->graphNesColor;?>;" name="graphNesTxt" type="text" id="graphNesTxt" value="<?php echo $parameter->graphNesTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphNesColor" class="form-label"><?php echo $parameter->graphNesTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphNesColor" name="graphNesColor" value="<?php echo $parameter->graphNesColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>

                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphAgendaTxt" class="form-label"><?php echo $parameter->graphAgendaTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphAgendaTxtColor;?>; background-color: <?php echo $parameter->graphAgendaColor;?>;" name="graphAgendaTxt" type="text" id="graphAgendaTxt" value="<?php echo $parameter->graphAgendaTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphAgendaColor" class="form-label"><?php echo $parameter->graphAgendaTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphAgendaColor" name="graphAgendaColor" value="<?php echo $parameter->graphAgendaColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <hr class="w-100">

                                <h6 class="w-100">Registros de Embarque</h6>
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphReTxt" class="form-label"><?php echo $parameter->graphReTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphReTxtColor;?>; background-color: <?php echo $parameter->graphReColor;?>;" name="graphReTxt" type="text" id="graphReTxt" value="<?php echo $parameter->graphReTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphReColor" class="form-label"><?php echo $parameter->graphReTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphReColor" name="graphReColor" value="<?php echo $parameter->graphReColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>
                                
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphSreTxt" class="form-label"><?php echo $parameter->graphSreTxt;?> Texto:</label>
                                        <input class="form-control" style="color: <?php echo $parameter->graphSreTxtColor;?>; background-color: <?php echo $parameter->graphSreColor;?>;" name="graphSreTxt" type="text" id="graphSreTxt" value="<?php echo $parameter->graphSreTxt;?>" onkeyup="txtGraphParam(this)">
                                    </div>

                                    <div class="form-group">
                                        <label for="graphSreColor" class="form-label"><?php echo $parameter->graphSreTxt;?> Cor:</label>
                                        <input class="form-control" type="color" id="graphSreColor" name="graphSreColor" value="<?php echo $parameter->graphSreColor;?>" onchange="colorGraphParam(this)">
                                    </div>
                                </div>

                                <hr class="w-100">

                                <h6 class="w-100">Cartões não Utilizados nos Últimos 7 dias</h6>
                                <div class="holdAppSelects" style="opacity:<?php echo $parameter->graphDefault == 1 ? '.5':'1'?>;">
                                    <div class="overParam" style="display:<?php echo $parameter->graphDefault == 1 ? 'block':'none'?>;"></div>
                                    <div class="form-group">
                                        <label for="graphBarraColor" class="form-label">Cor das Barras:</label>
                                        <input class="form-control" type="color" id="graphBarraColor" name="graphBarraColor" value="<?php echo $parameter->graphBarraColor;?>">
                                    </div>
                                </div>
                                
                            </div> 
                            <!-- fim gráficos -->
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