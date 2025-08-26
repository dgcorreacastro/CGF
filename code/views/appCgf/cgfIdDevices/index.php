<main class="py-4" <?php echo $_SESSION['cType'] == 3 ? 'style="width: 100% !important;"' : ''; ?>>
    <div class="personContainer">
        <div class="card-body">
            
            <?php if($_SESSION['cType'] != 3){?>
                <h2 class="pageTitle"></h2>
            <?php }else{?>
                <h2><i class="fa fa-qrcode "></i> <?php echo APP_NAME;?> ID <b class="h4">&#10148; Aparelhos</b></h2>
            <?php }?>
            <hr>
            <form id="formFilterEsc" method="GET" action="/cgfIdDevices" accept-charset="UTF-8" class="form-horizontal row justify-content-center px-2">
                <?php $pag = isset($_GET['p']) ? $_GET['p'] : 1; ?>
                <input type="hidden" id="pesc" name="p" value="<?php echo $pag ?>" />
                <div class="col-sm-3 col-xs-12 mb-3">
                    <label for="device_id" class="control-label mb-3">Aparelho:</label>
                    <select id="devicesDevices" name="device_id" class="form-control">
                        <option value="0">Filtrar por Aparelho</option>
                        <?php foreach($selDevices AS $selDevice): ?>
                            <option value="<?php echo $selDevice->id; ?>"
                            <?php echo (isset($_GET['device_id']) && $_GET['device_id'] == $selDevice->id) ? 'selected' : '';?> model="<?php echo $selDevice->model;?>">
                            <?php echo $selDevice->device_id; ?> 
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-12 mb-3">
                    <label for="model" class="control-label mb-3">Modelo:</label>
                    <select id="modelsDevices" name="model" class="form-control">
                        <option value="">Filtrar por Modelo</option>
                        <?php foreach($modelos AS $modelo): ?>
                            <option value="<?php echo $modelo->model; ?>"
                            <?php echo (isset($_GET['model']) && $_GET['model'] == $modelo->model) ? 'selected' : '';?> data-image="<?php echo $modelo->img; ?>">
                            <?php echo $modelo->model; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-12 mb-3">
                    <label for="carrosFilter" class="control-label mb-3">Carro:</label>
                    <select id="carrosFilter" name="carrosFilter" class="form-control">
                        <option value="">Filtrar por Carro</option>
                        <option value="nocar"
                        <?php echo (isset($_GET['carrosFilter']) && $_GET['carrosFilter'] == "nocar") ? 'selected' : '';?>>
                            SEM CARRO
                        </option>
                            <?php foreach($carros AS $carro): 
                                if($carro['ID_ORIGIN'] < 0)
                                    continue;
                            ?>
                            <option value="<?php echo $carro['ID_ORIGIN']; ?>"
                            <?php echo (isset($_GET['carrosFilter']) && $_GET['carrosFilter'] == $carro['ID_ORIGIN']) ? 'selected' : '';?>>
                            <?php echo $carro['NOME']." - ".$carro['PLACA']; ?> 
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-12 mb-3">
                    <label for="version" class="control-label mb-3">Versão do App:</label>
                    <select id="version" name="version" class="form-control versionFilterDevice">
                        <option value="">Filtrar por Versão</option>
                            <?php foreach($selectVersion AS $version):?>
                            <option value="<?php echo $version->app_version; ?>"
                            <?php echo (isset($_GET['version']) && $_GET['version'] == $version->app_version) ? 'selected' : '';?>>
                            <?php echo $version->app_version; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-sm-2 col-xs-12 mb-3">
                    <label for="cad" class="control-label mb-3">Tipo Aparelho:</label>
                    <select id="cad" name="cad" class="form-control cadFilterDevice">
                        <option value="">Filtrar por Tipo</option>
                        <option value="det" <?php echo (isset($_GET['cad']) && $_GET['cad'] == "det") ? 'selected' : '';?>>Detecção</option>
                        <option value="cad" <?php echo (isset($_GET['cad']) && $_GET['cad'] == "cad") ? 'selected' : '';?>>Cadastro</option>
                    </select>
                </div>

                <div class="col-sm-auto col-xs-12 mt-3 mb-3">
                    <span style="gap: 1em" class="btnLost72Device btn btn-<?php echo isset($_GET['lost72']) ? "danger" : "light";?> w-100 d-flex justify-content-start align-items-center">
                        <i class="fas fa-history h4"></i><h6 class="text-left">Sem atualizações nas<br>últimas 72 horas</h6>
                        <input class="dn" type="checkbox" id="lost72" <?php echo isset($_GET['lost72']) ? 'checked':'' ?> name="lost72">
                    </span>
                </div>

                <div class="col-sm-auto col-xs-12 mt-3 mb-3">
                    <span style="gap: 1em" class="btnNoRec72Device btn btn-<?php echo isset($_GET['noRec72']) ? "danger" : "light";?> w-100 d-flex justify-content-start align-items-center">
                        <i class="fas fa-video-slash h4"></i><h6 class="text-left">Sem reconhecimentos<br>nas últimas 72 horas</h6>
                        <input class="dn" type="checkbox" id="noRec72" <?php echo isset($_GET['noRec72']) ? 'checked':'' ?> name="noRec72">
                    </span>
                </div>

                <div class="col-sm-auto col-xs-12 mt-3 mb-3">
                    <span style="gap: 1em" class="btnWithRec72Device btn btn-<?php echo isset($_GET['withRec72']) ? "success" : "light";?> w-100 d-flex justify-content-start align-items-center">
                        <i class="fas fa-video h4"></i><h6 class="text-left">Com reconhecimentos<br>nas últimas 72 horas</h6>
                        <input class="dn" type="checkbox" id="withRec72" <?php echo isset($_GET['withRec72']) ? 'checked':'' ?> name="withRec72">
                    </span>
                </div>

                <div class="col-sm-auto col-xs-12 mt-3 mb-3">
                    <span style="gap: 1em" class="btnCircLineDevice btn btn-<?php echo isset($_GET['circLine']) ? "info" : "light";?> w-100 d-flex justify-content-start align-items-center">
                        <i class="fas fa-sync-alt h4"></i><h6 class="text-left">Linha<br>Circular</h6>
                        <input class="dn" type="checkbox" id="circLine" <?php echo isset($_GET['circLine']) ? 'checked':'' ?> name="circLine">
                    </span>
                </div>

                <!-- <div class="col-sm-auto col-xs-12 mt-3 mb-3">
                    <span style="gap: 1em" class="btnNoLocDevice btn btn-<?php echo isset($_GET['noLoc']) ? "danger" : "light";?> w-100 d-flex justify-content-start align-items-center">
                        <i class="fas fa-question-circle h4"></i><h6 class="text-left">Sem informações<br>de Localização</h6>
                        <input class="dn" type="checkbox" id="noLoc" <?php echo isset($_GET['noLoc']) ? 'checked':'' ?> name="noLoc">
                    </span>
                </div> -->

                <div class="col-sm-auto switch-group-col col-xs-12 mt-2">
                    <label class="control-label text-center">Mostrar inativos?</label> 
                    <label class="switch">
                        <input class="intDevices" type="checkbox" id="int" <?php echo isset($_GET['int']) ? 'checked':'' ?> name="int">
                        <span class="slider round"></span>
                    </label>
                </div>

            </form>
            <hr>

            <div class="devicesList">
                <?php foreach($devices as $device): ?>
                    <div class="deviceItem" id="<?php echo $device->device_id; ?>">

                        <div class="recognitionsDevice" id="recognitions_<?php echo $device->device_id ?>"></div>
                        
                        <label class="switch" title="<?php echo ($device->ativo == 1) ? 'Ativo' : 'Inativo'; ?>">
                            <input class="switchDevice" device_id="<?php echo $device->id; ?>" device_name="<?php echo $device->device_id; ?>" type="checkbox" <?php echo ($device->ativo == 1) ? 'checked' : '';?> id="ativo-<?php echo $device->id;?>" name="ativo-<?php echo $device->id;?>">
                            <span class="slider round"></span>
                            <h6><?php echo ($device->ativo == 1) ? 'Ativo' : 'Inativo'; ?></h6>
                        </label>
                        <div class="deviceItemTitle" <?php if($device->cad == 1) echo 'style="height:125px;"'?>>

                            <div class="battery" title="Bateria">
                                <div class="batteryLevel <?php echo $device->batteryClass ?? '';?>" style="width: <?php echo $device->batteryLevel ?? '0';?>%;"></div>
                                <b class="batteryLevelNumber"><?php echo $device->batteryLevel ?? '';?>%</b>
                                <b class="batteryState"><?php echo $device->batteryStateOk;?></b>
                            </div>

                            <?php if($device->circular == 1 && $device->cad == 0):?>
                                <span class="circEuro bg-info p-1 text-white"><i class="fas fa-sync-alt"></i>Circular<br>Eurofarma</span>
                            <?php endif;?>

                            <?php if($device->cad == 1):?>
                                <span class="circEuro bg-info p-1 text-white"><i class="fas fa-id-card-alt"></i>Cadastro</span>
                            <?php endif;?>

                            <div class="deviceNoInfo">
                                <?php if ($device->cad == 0):?>
                                    <b id="info_loc_<?php echo $device->device_id;?>">
                                        <?php if (!isset($device->loc_update) || (isset($device->loc_update) && (time() - strtotime($device->loc_update)) > (72 * 3600))){

                                            if(!isset($device->loc_update)):?>
                                                <i class="fas fa-question-circle"></i>
                                                Sem informações de Localização
                                            <?php else:?>
                                                <i class="fas fa-history"></i>
                                                Sem atualizações nas últimas 72 horas
                                            <?php endif;
                                        }
                                        ?>
                                    </b>
                                    <?php if(!isset($device->latest_real_time)):?>
                                        <b id="info_rec_<?php echo $device->device_id;?>">
                                            <i class="fas fa-video-slash"></i>
                                            Sem reconhecimentos<br>nas últimas 72 horas
                                        </b>
                                    <?php endif;?>
                                <?php endif;?>
                            </div>

                            <?php
                                $deviceModel = $device->model;
                                $deviceImage = '';
                                foreach ($modelos as $modelo) {
                                    if ($modelo->model === $deviceModel) {
                                        $deviceImage = $modelo->img;
                                        break;
                                    }
                                }
                            ?>
                            <img src="<?php echo $deviceImage; ?>" alt="<?php echo $device->device_id; ?>" />
                            
                            <p>
                                <?php echo $device->device_id; ?>
                            </p>

                        </div>
                        <br>
                        <section <?php if($device->cad == 1) echo 'style="height:460px;"'?>>
                            <div class="row w-100 m-0 my-3 p-0">
                                <div class="col-12 mb-0 d-flex flex-row flex-wrap justify-content-center align-items-center">
                                    <span class="w-100 p-1 text-center" style="border-radius: .25rem"><i class="fas fa-history"></i> <b id="loc_update_front_<?php echo $device->device_id;?>"><?php echo isset($device->loc_update) ? date("d/m/Y - H:i", strtotime($device->loc_update)) : ' - ';?></b></span>
                                </div>
                                <?php if($device->cad == 0):?>
                                    <div class="col-12 mb-0 d-flex flex-row flex-wrap justify-content-center align-items-center">
                                        <span class="w-100 p-1 text-center" style="border-radius: .25rem">
                                            <i class="fas fa-bus"></i>
                                            <b id="veic_update_front_<?php echo $device->device_id;?>"><?php echo $device->VEICULO ?? '<i class="fas fa-exclamation-triangle text-danger" title="Aparelho sem Veículo!"></i>';?></b>
                                        </span>
                                    </div>
                                    <div class="col-12 mb-2 d-flex flex-row flex-wrap justify-content-center align-items-center">
                                        <span class="btn btn-primary w-100 requestDeviceConfig actBtn" device_id="<?php echo $device->device_id; ?>" config_type="1">Abrir Configurações</span>
                                    </div>
                                    <div class="col-12 mb-2 d-flex flex-row flex-wrap justify-content-center align-items-center">
                                        <span class="btn btn-warning w-100 requestDeviceConfig actBtn" device_id="<?php echo $device->device_id; ?>" config_type="0">Fechar Configurações</span>
                                    </div>
                                    <div class="col-12 mb-2 d-flex flex-row flex-wrap justify-content-center align-items-center">
                                        <span class="btn btn-success w-100 requestDeviceDetections actBtn" device_id="<?php echo $device->device_id; ?>">Solicitar Reconhecimentos</span>
                                    </div>
                                    
                                    <div class="col-12 mb-2" style="display: grid;grid-template-columns: calc(80% - .5em) 20%;width:100%;gap: 0 .5em;justify-items: start;">
                                        <label for="data-<?php echo $device->device_id ?>" class="form-label mb-1">Ver Reconhecimentos:</label><div></div>
                                        <input class="form-control w-100 dateSeeDetect" device_id="<?php echo $device->device_id; ?>" name="data-<?php echo $device->device_id ?>" type="date" value="" id="data-<?php echo $device->device_id ?>">
                                        <span title="Ver Reconhecimentos" style="font-size: 1rem !important" class="btn btn-success w-100 seeDetect d-flex justify-content-center align-content-center" device_id="<?php echo $device->device_id; ?>"><i class="fas fa-calendar-check" style="line-height: inherit;"></i></span>
                                    </div>

                                    <div class="col-12 mb-2" style="display: grid;grid-template-columns: calc(80% - .5em) 20%;width:100%;gap: 0 .5em;justify-items: start;">
                                        <label for="data-ta-<?php echo $device->device_id ?>" class="form-label mb-1">Ver Tente Novamente:</label><div></div>
                                        <input class="form-control w-100 dateSeeDetect" device_id="<?php echo $device->device_id; ?>" name="data-ta-<?php echo $device->device_id ?>" type="date" value="" id="data-ta-<?php echo $device->device_id ?>">
                                        <span title="Ver Tentar Novamente" style="font-size: 1rem !important" class="btn btn-success w-100 seeTryAgain d-flex justify-content-center align-content-center" device_id="<?php echo $device->device_id; ?>"><i class="fas fa-calendar-check" style="line-height: inherit;"></i></span>
                                    </div>
                                <?php endif;?>
                                
                            </div>
                            <br>
                        </section>
                        
                        <section class="infoDevice">
                            <span class="btn btn-info infoDeviceBtn" title="Ver Informações"><b>Ver Informações</b> <i class="fas fa-eye"></i></span>    
                            <?php if($device->cad == 0):?>
                                <div class="groupFaceVeic">
                                    <p class="infoDeviceGroup border-0 mt-0" id="veic-<?php echo $device->device_id ?>">Veículo: 
                                        <?php if(isset($device->VEICULO)):?>
                                            <b class="bg-success text-white p-1"><?php echo $device->VEICULO;?></b>
                                            <br>
                                            <i class="bg-warning text-dark p-1">Atualizado em: <?php echo date("d/m/Y - H:i", strtotime($device->veic_update)) ;?></i>
                                        <?php else:?><b class="bg-success text-white p-1"> - </b><br><i class="bg-warning text-dark p-1"> - </i><?php endif;?>
                                    </p>
                                    <select title="<?php echo (isset($device->VEICULO)) ? 'Trocar Veículo' : 'Selecionar Veículo';?>" id="veics-<?php echo $device->device_id ?>" name="veics-<?php echo $device->device_id ?>" class="form-control changeVeicFace" iniVeic="<?php echo $device->veiculo_id ?? 0;?>" device_id="<?php echo $device->device_id ?>" updating="0">
                                        <option value="0"><?php echo (isset($device->VEICULO)) ? 'Trocar Veículo' : 'Selecionar Veículo';?></option>
                                            <?php foreach($carros AS $carro): 
                                                if($carro['ID_ORIGIN'] < 0)
                                                    continue;
                                            ?>
                                            <option value="<?php echo $carro['ID_ORIGIN']; ?>"
                                            <?php echo (isset($device->veiculo_id) && $device->veiculo_id == $carro['ID_ORIGIN']) ? 'selected' : '';?>>
                                            <?php echo $carro['NOME']." - ".$carro['PLACA']; ?> 
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>  
                            <?php endif;?>                          
                            <p class="infoDeviceGroup" id="local-<?php echo $device->device_id ?>">Localização Atual: 
                                <?php if(isset($device->latitude_now) && isset($device->longitude_now)):?>
                                    <span title="Ver no Mapa" class="btn btn-success p-1 showDeviceLocalAtual" style="line-height:1;" device_id="<?php echo $device->device_id ?>" src="/map?latitude=<?php echo $device->latitude_now ?>&longitude=<?php echo $device->longitude_now ?>&title=<?php echo $device->device_id ?>&titlePoint=Localização Atual&showTop=1&atualiza=1&showAddress=1"><i class="fas fa-map"></i></span>
                                    <br>
                                    <b>Timezone: <?php echo $device->timezone?></b>
                                    <br>
                                    <i class="loc_update bg-warning text-dark p-1">Atualizado em: <?php echo date("d/m/Y - H:i", strtotime($device->loc_update)) ;?></i>
                                <?php else:?><b></b><br><i class="loc_update bg-warning text-dark p-1"> - </i><?php endif;?>
                            </p>
                            <p class="infoDeviceGroup">Cor Foto Target:
                                <input type="color" id="tintColorTarget-<?php echo $device->id ?>" name="tintColorTarget-<?php echo $device->id ?>" updating="0" onchange="changeTintColor(this, this.value, '<?php echo $device->tintColorTarget;?>', <?php echo $device->id ?>)" value="<?php echo $device->tintColorTarget;?>">
                            </p>
                            <p>QRCode Password: <span title="Ver QRCode" class="btn btn-warning p-1 showQrDevice" style="line-height:1;"><i class="fas fa-qrcode"></i></span></p>
                            <p>ID. do Aparelho: <b><?php echo $device->device_id;?></b></p>
                            <p>Data de instalação: <b><?php echo date("d/m/Y - H:i", strtotime($device->created_at)) ;?></b></p>
                            <p>Modelo: <b><?php echo $device->model;?></b></p>
                            <p>Versão Instalada: <b><?php echo $device->app_version;?></b></p>
                            <p>Local de Instalação: <span title="Ver no Mapa" class="btn btn-success p-1 showDeviceLocalInstala" style="line-height:1;" src="/map?latitude=<?php echo $device->latitude ?>&longitude=<?php echo $device->longitude ?>&title=<?php echo $device->device_id ?>&titlePoint=Local de Instalação&showTop=1&showAddress=1"><i class="fas fa-map"></i></span></p>               
                            <?php if($device->cad == 0):?>
                                <hr>
                                <div>
                                <label class="switch" title="<?php echo ($device->circular == 1) ? 'Circular Eurofarma' : ''; ?>">
                                    <input class="switchCircDevice" device_id="<?php echo $device->id; ?>" device_name="<?php echo $device->device_id; ?>" type="checkbox" <?php echo ($device->circular == 1) ? 'checked' : '';?> id="circular-<?php echo $device->id;?>" name="circular-<?php echo $device->id;?>">
                                    <span class="slider round"></span>
                                    <h6 style="width: max-content;">Circular Eurofarma?</h6>
                                </label>
                                </div>
                            <?php endif;?>
                            <iframe class="iframeQrDevice" src="/app/printqrcode?qr=<?php echo $device->device_id ?>&nomegr=<?php echo $device->device_id ?>&type=device&show_print=1"></iframe>
                        </section>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if($ttPages > 1) :?>
                <hr>
                <div id="paginate">
                            <?php echo "<ul class='pagination'>";

                            $c = 0; 
                            $sr = false;
                            $er = false;
                            for($i=1;$i <= $ttPages; $i++)
                            { 
                                if($pag != 1 && !$sr){
                                    echo '<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)" > << </a></li>';
                                    $sr = true;
                                }

                                if($pag == $i){
                                    echo '<li class="page-item active"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                    $c++;
                                }

                                if($i > $pag && ($i == ( $pag + 1) || $i == ( $pag + 2) || $i == ( $pag + 3) || ($pag == 1 && $i == ( $pag + 4)) ) && $c < 5) {
                                    echo '<li class="page-item"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                    $c++;
                                }

                                if(isset($pag) && $i < $pag && ($i == ( $pag - 1) || $i == ( $pag - 2)  || $i == ( $pag - 3) || ($pag == $ttPages && $i == ( $pag - 4) )  ) && $c < 5) {
                                    echo '<li class="page-item"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                    $c++;
                                }
                                
                            }
                            if(isset($pag) && $pag != $ttPages && !$er){
                                echo '<li class="page-item"><a href="#" onclick="changePage('.$ttPages.')" class="page-link"> >> </a></li>';
                                $er = true;
                            }
                            
                            echo "</ul>";
                    ?>
                </div>
            <?php endif;?>

            <?php if($_SESSION['cType'] == 3){?>
                <hr> 
                <div class="col col-12 d-flex justify-content-end" style="gap:1em;">
                    <a href="/" class="btn btn-danger btn-ls">VOLTAR</a>  
                </div>
            <?php }?>  
        </div>
    </div>
</main>
</div>
<script>
    window.onload = function(e){ 
        
        $('#modelsDevices').select2({
            width:'100%',
            "language": {
            "noResults": function(){
                return "Nenhum resultado encontrado";
            }
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            templateResult: formatModel,
            templateSelection: formatModel
        });

        function formatModel(model) {
            var $model = $('<span>' + model.text + '</span>');
            if ($(model.element).data('image')) {
                var imageHtml = '<img src="' + $(model.element).data('image') + '" class="img-thumbnail" width="50" height="50" />';
                $model.prepend(imageHtml);
            }
            return $model;
        }

        $('#devicesDevices').select2({
            width:'100%',
            "language": {
            "noResults": function(){
                return "Nenhum resultado encontrado";
            }
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            templateResult: formatDevice,
            templateSelection: formatDevice
        });

        
        function formatDevice(device) {
            var $device = $('<span>' + device.text + '</span>');
            if (device.element && $(device.element).attr('model')) {
                var modelValue = $(device.element).attr('model');
                var modelOption = $('#modelsDevices').find('option[value="' + modelValue + '"]');
                if (modelOption.length && $(modelOption).data('image')) {
                    var imageHtml = '<img src="' + $(modelOption).data('image') + '" class="img-thumbnail" width="50" height="50" />';
                    $device.prepend(imageHtml);
                }
            }
            return $device;
        }

        <?php if(!isset($_GET['int'])):?>
            document.getElementById('int').checked = false;
        <?php endif;?>

        startFaceDevices();

    }

    
</script>