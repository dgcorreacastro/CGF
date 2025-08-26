<main class="py-4">
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/userPhotos.css?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>" />
    <?php if($cad_pax_pics == 1):?>
        <script defer src="<?php echo BASE_URL; ?>assets/js/face-api.min.js"></script>
        <script defer src="<?php echo BASE_URL; ?>assets/js/userPhotos.js?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>"></script>
    <?php endif;?>
    <div class="personContainer" style="margin-top: 4em;">

        <?php
            $userPic = $pax['ca']['pic_front_smiling'] ?? '0';
            $style = ($userPic == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_front_smiling.png?' . time() . ');' : 'background-image: url(' . $userPic . ');';
            $spanText = ($userPic == '0') ? 'Use o App '.APP_NAME.' ID para adicionar fotos' : 'Ver Fotos';
        ?>

        <div class="userPhoto <?php if($cad_pax_pics == 0 || $userPic == '0'):?>disabled <?php endif;?>" style="<?php if($cad_pax_pics == 0 || $userPic == '0'):?>cursor: default; <?php endif;?><?php echo $style; ?>">
            <?php if($cad_pax_pics == 1):?>
                <span <?php if($userPic == '0'):?>class="showPhotoMsg" <?php endif;?>><?php echo $spanText; ?></span>
            <?php endif;?>
            <div class="monitorTag <?php echo $pax['ca']['monitor'] == '0' ? 'hide':'';?> ">
                <i class="fas fa-user-shield"></i>
                MONITOR
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="/cadastroPax/salvarEdit" accept-charset="UTF-8" class="form-horizontal" id="createPaxForm">
                
                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Editar</b></h2>
                    <p style="color: yellow;">*Altere os dados somente para o passageiro atual. Para cadastrar novo Passageiro <a href="/cadastroPax/create"><strong>CLIQUE AQUI</strong></a></p>
                <?php if($cad_pax_pics == 1 && ($pax['ca']['created_cgf_id'] != null || $pax['ca']['updated_cgf_id'] != null)):?>
                    <hr>
                    <div class="paxFromCGFId">
                        <?php if($pax['ca']['created_cgf_id'] != null):?><span class="bg-success p-1"><i class="fas fa-info-circle mr-2"></i>Criado por <?php echo APP_NAME; ?> ID em <?php echo date("d/m/Y H:i:s", strtotime($pax['ca']['created_cgf_id'])) ?></span><?php endif;?>
                        <?php if($pax['ca']['updated_cgf_id'] != null):?><span class="bg-warning p-1 text-dark"><i class="fas fa-info-circle mr-2"></i>Editado por <?php echo APP_NAME; ?> ID em <?php echo date("d/m/Y H:i:s", strtotime($pax['ca']['updated_cgf_id'])) ?></span><?php endif;?>
                   </div>
                <?php endif;?>
                </div>

                <hr>

                <div class="card-create-body">
                    <div class="row mx-0">
                        <input class="form-control" name="id" type="hidden" value="<?php echo $_GET['id'] ?>">
                        <div class="col-sm-<?php echo $cad_pax_tag == 1 ? '4' : '6';?> col-xs-12">
                            <label for="name" class="control-label">Nome:</label>
                            <input class="form-control" name="name" type="text" id="name" required value="<?php echo (preg_match('!!u', utf8_decode($pax['ca']['NOME']))) ? utf8_decode($pax['ca']['NOME']) : $pax['ca']['NOME'] ?>" onblur="checkCod(this.value)">
                        </div>
                        <?php if($cad_pax_tag == 1):?>
                            <div class="col-sm-2 col-xs-12">
                                <label for="codigo" class="control-label">Código:</label>
                                <input 
                                    class="form-control" 
                                    name="codigo" type="text" 
                                    id="codigo" 
                                    value="<?php echo $pax['ca']['TAG'] ?>"
                                    <?php echo $pax['ca']['NOME'] == '' ? 'disabled' : ''?>
                                    onblur="checkIfExistTag(this.value, <?php echo $pax['ca']['ID_ORIGIN'] ?? 0 ?>, <?php echo $_GET['id'] ?>)"
                                >
                                <input type="hidden" id="codigoorigin" value="<?php echo $pax['ca']['TAG'] ?>">
                            </div>
                        <?php endif;?>
                        <div class="col-sm-2 col-xs-12">
                            <label for="grupo" class="control-label">Grupo:</label>
                            <select id="grupo" name="grupo"  class="form-control" required>
                            <?php foreach($grupos AS $gr): ?>
                                <option 
                                    value="<?php echo $gr['ID_ORIGIN']; ?>"
                                    <?php echo ($gr['ID_ORIGIN'] == $pax['ca']['CONTROLE_ACESSO_GRUPO_ID']) ? 'selected' : '' ?>
                                >
                                <?php echo utf8_decode(utf8_encode($gr['NOME'])); ?> 
                            </option>
                            <?php endforeach; ?>
                            </select>
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

                        <div class="col-sm-2 col-xs-12">
                            <label for="monitor" class="control-label">Monitor:</label>
                            <select id="monitor" name="monitor"  class="form-control filtroSelect2" onchange="monitorToggle(this.value)">
                                <option value="0" <?php echo (0 == $pax['ca']['monitor']) ? 'selected' : '' ?>>NÃO</option>    
                                <option value="1" <?php echo (1 == $pax['ca']['monitor']) ? 'selected' : '' ?>>SIM</option>
                            </select>
                        </div> 

                        <div class="col-sm-10 col-xs-12">
                            <label for="polIda" class="control-label">End. Residência:</label>
                            <input class="form-control" name="residencia" type="text" value="<?php echo $pax['ca']['residencia'] ?>">
                        </div>

                        <hr style="width: 100%;">

                        <div class="col-sm-4 col-xs-12 btn-primary py-2">
                            <label for="linhaIda" class="control-label" >Linha IDA (Principal):</label>
                            <select class="form-control" id="linhaIda" name="linhaIda" onchange="getItin(this, 1)">
                                <option value="">Selecione</option>
                                <?php foreach($linhasIda AS $ln): ?>
                                    <option 
                                        value="<?php echo $ln['ID_ORIGIN']; ?>" 
                                        sentido="<?php echo $ln['SENTIDO']; ?>"
                                        <?php echo ($ln['ID_ORIGIN'] == $pax['ca']['LinhaIda']) ? 'selected' : '' ?>
                                    >
                                        <?php echo $ln['PREFIXO'] . " - " . $ln['NOME'] . " - " . $ln['DESCRICAO'] ." - ENTRADA"?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" id="itiIda" name="itiIda" value="<?php echo isset($pax['itiIda']['ID']) ? $pax['itiIda']['ID'] : 0; ?>" />
                        <input type="hidden" id="itiVolta" name="itiVolta" value="<?php echo isset($pax['itiVolta']['ID']) ? $pax['itiVolta']['ID'] : 0; ?>" />
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

                        <div class="col-sm-4 col-xs-12 btn-warning py-2">
                            <label for="linhaVolta" class="control-label">Linha VOLTA (Principal):</label>
                            <select class="form-control" id="linhaVolta" name="linhaVolta" onchange="getItin(this, 2)">
                                <option value="">Selecione</option>
                                <?php foreach($linhasVolta AS $ls): ?>
                                    <option 
                                        value="<?php echo $ls['ID_ORIGIN']; ?>" 
                                        sentido="<?php echo $ls['SENTIDO']; ?>"
                                        <?php echo ($ls['ID_ORIGIN'] == $pax['ca']['LinhaVolta']) ? 'selected' : '' ?>
                                    >
                                        <?php echo $ls['PREFIXO'] . " - " . $ls['NOME'] . " - " . $ls['DESCRICAO'] ." - RETORNO"?>
                                    </option>
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
                    <hr style="width: 100%;">
                    <br>
                    <h5>Linhas Adicionais:</h5>
                    <div class="row align-items-center justify-content-center">
                        <div class="col-sm-2 col-xs-12">
                            <span class="btn btn-primary w-100" onclick="incluirNovaLinhaIda()">Incluir Linha IDA</span>
                        </div>
                        <div class="col-sm-2 col-xs-12">
                            <span class="btn btn-warning w-100" onclick="incluirNovaLinhaVolta()">Incluir Linha VOLTA</span>
                        </div>
                    </div>
                    <hr>
                    <table class="table" style="color: white !important; border-collapse: separate; border-spacing: 0 5px;">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">Linha</th>
                            </tr>
                        </thead>
                        <tbody id="linesAdicional">

                            <?php if( isset( $pax['linhasAdic'] ) ){ ?>
                                <?php foreach($pax['linhasAdic'] AS $lines): ?>
                                    <tr class=<?php echo $lines->sentido == 1 ? 'btn-warning' : 'btn-primary';?>>
                                        <td style="vertical-align: middle; width:90%;">
                                            <select class="form-control" name="linhaExist[]">
                                            <?php 
                                                $linhas = $lines->sentido == 1 ? $linhasVolta : $linhasIda; 
                                                $sentidoDesc = $lines->sentido == 1 ? "RETORNO" : "ENTRADA";
                                            ?>
                                            <?php foreach($linhas AS $ln): ?>
                                                <option value="<?php echo $ln['ID_ORIGIN']; ?>" 
                                                    <?php echo ($ln['ID_ORIGIN'] == $lines->linha_id) ? 'selected' : '' ?>
                                                >
                                                    <?php echo $ln['PREFIXO'] . " - " . $ln['NOME'] . " - " . $ln['DESCRICAO'] ." - ".$sentidoDesc?>
                                                </option>
                                            <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style='text-align: center; vertical-align: middle;'>
                                            <span class='btn-danger p-2' style='cursor:pointer;' onclick='deleteLineExist(this, <?php echo $lines->id ?>)'>
                                                <i class='fas fa-trash-alt' style='font-size:18px;'></i>
                                            </span>                                
                                        </td>
                                    </tr>

                            <?php endforeach; }; ?>

                        </tbody>
                    </table>
                </div>

                <hr>

                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="<?php echo $_SESSION['prev']?>" class="btn btn-danger w-100">Fechar</a>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button type="button" class="btn btn-success w-100" onclick="createPax()">Salvar</button>
                        </div>
                    </div>
                </div>
                    
                
            </form>
        </div>

    </div>

    <?php if($cad_pax_pics == 1):?>
        <!-- User Photo -->
        <div class="userPhotoAct">
            <span title="Cancelar" id="cancelPicture" class="btn btn-danger">
                <i class="fas fa-window-close h4"></i>
            </span>

            <div class="paxQr">
                <div class="w-100">
                    <input id="iniMsgPic" type="hidden" value="Leia o QrCode abaixo no App <?php echo APP_NAME; ?> ID"/>
                    <h6 id="deviceAppId" style="width: fit-content; display: inline-block; margin-right: 1em;">Leia o QrCode abaixo no App <?php echo APP_NAME; ?> ID</h6>
                    <span title="Cancelar" class="btn btn-danger p-1 cancelTakeAppPic">Cancelar</span>
                </div>
            </div>

            <div class="paxPhotos row w-100 m-2 d-flex flex-row justify-content-center align-items-center">
                <h6 id="deviceAppId" class="mt-3 mt-md-0" style="width: fit-content; display: inline-block;">Use o App <?php echo APP_NAME; ?> ID para trocar as fotos</h6>
                <div class="allPics">

                    <div id="pic_front_smiling_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_front_smiling'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_front_smiling.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_front_smiling'] . ');'?>">
                        <span class="pic_description">Frontal Sorrindo</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_front_smiling'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_front_smiling">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_front_smiling" title="<?php echo ($pax['ca']['pic_front_smiling'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_front_smiling', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_front_smiling_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_front_smiling_upload" type="file" onchange="uploadUserPhoto('pic_front_smiling')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_front_serious_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_front_serious'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_front_serious.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_front_serious'] . ');'?>">
                        <span class="pic_description">Frontal Sério</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_front_serious'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_front_serious">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_front_serious" title="<?php echo ($pax['ca']['pic_front_serious'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_front_serious', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_front_serious_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_front_serious_upload" type="file" onchange="uploadUserPhoto('pic_front_serious')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_right_perfil_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_right_perfil'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_right_perfil.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_right_perfil'] . ');'?>">
                        <span class="pic_description">Perfil Direito</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_right_perfil'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_right_perfil">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_right_perfil" title="<?php echo ($pax['ca']['pic_right_perfil'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_right_perfil', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_right_perfil_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_right_perfil_upload" type="file" onchange="uploadUserPhoto('pic_right_perfil')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_left_perfil_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_left_perfil'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_left_perfil.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_left_perfil'] . ');'?>">
                        <span class="pic_description">Perfil Esquerdo</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_left_perfil'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_left_perfil">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_left_perfil" title="<?php echo ($pax['ca']['pic_left_perfil'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_left_perfil', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_left_perfil_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_left_perfil_upload" type="file" onchange="uploadUserPhoto('pic_left_perfil')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>
                    
                </div>

                <!-- <div class="form-group switch-group mb-2 mt-4">
                    
                    <label class="form-label mb-0">Usá Óculos:</label>
                    <label class="switch mb-0">
                        <input type="checkbox" id="eyeglassesSwitch" <?php echo $pax['ca']['eyeglasses'] == 1 ? 'checked':'' ?> name="eyeglassesSwitch" onchange="setEyeglasses(this.checked)">
                        <span class="slider round"></span>
                    </label>
                    
                </div> -->
                <hr class="w-100">
                <h6 class="w-100 mt-0" style="margin-bottom: -.5em; text-align: center; opacity: <?php echo ($pax['ca']['eyeglasses'] == 1) ? '1' : '.2';?>">Com Óculos:</h6>
                <div class="allPics egPic <?php echo ($pax['ca']['eyeglasses'] == 1) ? 'egPicShow' : 'egPicHide';?>">
                    
                    <div id="pic_front_smiling_eg_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_front_smiling_eg'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_front_smiling_eg.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_front_smiling_eg'] . ');'?>">
                        <span class="pic_description">Frontal Sorrindo</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_front_smiling_eg'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_front_smiling_eg">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_front_smiling_eg" title="<?php echo ($pax['ca']['pic_front_smiling_eg'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_front_smiling_eg', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_front_smiling_eg_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_front_smiling_eg_upload" type="file" onchange="uploadUserPhoto('pic_front_smiling_eg')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_front_serious_eg_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_front_serious_eg'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_front_serious_eg.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_front_serious_eg'] . ');'?>">
                        <span class="pic_description">Frontal Sério</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_front_serious_eg'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_front_serious_eg">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_front_serious_eg" title="<?php echo ($pax['ca']['pic_front_serious_eg'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_front_serious_eg', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_front_serious_eg_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_front_serious_eg_upload" type="file" onchange="uploadUserPhoto('pic_front_serious_eg')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_right_perfil_eg_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_right_perfil_eg'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_right_perfil_eg.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_right_perfil_eg'] . ');'?>">
                        <span class="pic_description">Perfil Direito</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_right_perfil_eg'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_right_perfil_eg">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_right_perfil_eg" title="<?php echo ($pax['ca']['pic_right_perfil_eg'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_right_perfil_eg', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_right_perfil_eg_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_right_perfil_eg_upload" type="file" onchange="uploadUserPhoto('pic_right_perfil_eg')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>

                    <div id="pic_left_perfil_eg_upload_preview" class="photoContainer" style="<?php echo ($pax['ca']['pic_left_perfil_eg'] == '0') ? 'background-image: url(' . BASE_URL . 'assets/images/pic_left_perfil_eg.png?' . time() . ');' : 'background-image: url(' . $pax['ca']['pic_left_perfil_eg'] . ');'?>">
                        <span class="pic_description">Perfil Esquerdo</span>
                        <!-- <span title="Remover Foto" class="btn btn-danger p-1 removeUserPhoto <?php echo ($pax['ca']['pic_left_perfil_eg'] == '0') ? 'disabled' : ''?>" style="position: absolute; left: -4px; bottom: -4px;" controle_acesso_id="<?php echo $pax['ca']['id'];?>" typeR="pic_left_perfil_eg">
                            <i class="fas fa-trash-alt h5"></i>
                        </span>
                        <span id="btn_up_pic_left_perfil_eg" title="<?php echo ($pax['ca']['pic_left_perfil_eg'] == '0') ? 'Tirar Foto' : 'Trocar Foto'?>" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;" onclick="uploadUserPhotoFromApp('pic_left_perfil_eg', <?php echo $pax['ca']['id'];?>)">
                            <i class="fas fa-camera h5"></i>
                        </span> -->
                        <!-- <label for="pic_left_perfil_eg_upload" title="Upload" class="btn btn-primary p-1" style="position: absolute; right: -4px; bottom: -4px;">
                            <input class="userPhotoUpload" id="pic_left_perfil_eg_upload" type="file" onchange="uploadUserPhoto('pic_left_perfil_eg')" accept=".jpg, .jpeg, .png">
                            <i class="fas fa-upload h5"></i>
                        </label> -->
                    </div>
                    
                </div>
                
            </div>
        </div>
    <?php endif;?>

</main>