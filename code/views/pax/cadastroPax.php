<main class="py-4">

    <div class="personContainer">

        <div class="card-body">

            <div class="card-create-header">
                <h2 class="pageTitle"></h2>
                <hr>

                <?php if($recId):?>
                    <h4>Clique 2 vezes no passageiro para definir o reconhecimento</h4><hr><?php endif;?>
                <div class="row w-100 ml-0 mr-0 justify-content-center">
                    <form id="formFilterEsc" method="GET" action="/cadastroPax" accept-charset="UTF-8" class="form-horizontal row ml-0 mr-0">
                        <div class="col-sm-6 col-xs-12">
                            <label for="n" class="control-label">Nome:</label>
                            <input class="form-control" name="n" type="text" value="<?php echo isset($_GET['n']) ? $_GET['n'] : '' ?>"> 
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <label for="mat" class="control-label">Matrícula:</label>
                            <input class="form-control" name="mat" type="text" value="<?php echo isset($_GET['mat']) ? $_GET['mat'] : ""; ?>">  
                        </div>
                        <?php if($cad_pax_tag == 1 || (isset($_SESSION['cType']) && $_SESSION['cType'] == 1)):?>
                            <div class="col-sm-6 col-xs-12">
                                <label for="cod" class="control-label">Código:</label>
                                <input class="form-control" name="cod" type="text" value="<?php echo isset($_GET['cod']) ? $_GET['cod'] : ""; ?>">  
                            </div>
                        <?php endif;?>
                        <?php if(!isset($_SESSION['cFret'])){ ?>
                            <div class="col-sm-6 col-xs-12">
                                <label for="grme" class="control-label">Grupo:</label>
                                <select id="grme" class="form-control" onChange="setGr(this.value)">
                                    <option value="">Filtrar por Grupo</option>
                                    <?php foreach($grupos AS $grs): ?>
                                        <option value="<?php echo $grs['ID_ORIGIN']; ?>"
                                        <?php echo (isset($_GET['gr']) && $_GET['gr'] == $grs['ID_ORIGIN']) ? 'selected' : '';?>>
                                        <?php echo utf8_decode(utf8_encode($grs['NOME'])); ?> 
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php } ; ?>
                        <?php $pag = isset($_GET['p']) ? $_GET['p'] : 1; ?>
                        <input type="hidden" id="pesc" name="p" value="<?php echo $pag ?>" />
                        <input type="hidden" id="gr" name="gr" value="<?php echo $gr ?>" />
                        <?php if($recId):?>
                            <input type="hidden" id="recId" name="recId" value="<?php echo $recId ?>" />
                        <?php endif;?>
                        <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                            <label class="control-label text-center">Mostrar inativos?</label> 
                            <label class="switch">
                                <input type="checkbox" id="int" <?php echo isset($_GET['int']) ? 'checked':'' ?> name="int">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                            <label class="control-label text-center">Sem Grupos?</label> 
                            <label class="switch">
                                <input type="checkbox" id="withoutGroups" <?php echo isset($_GET['withoutGroups']) ? 'checked':'' ?> name="withoutGroups">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <?php if($cad_pax_pics == 1 || (isset($_SESSION['cType']) && $_SESSION['cType'] == 1)):?>
                            <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                                <label class="control-label text-center">Com Fotos?</label> 
                                <label class="switch">
                                    <input type="checkbox" id="wpic" <?php echo isset($_GET['wpic']) ? 'checked':'' ?> name="wpic">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                                <label class="control-label text-center">Sem Fotos?</label> 
                                <label class="switch">
                                    <input type="checkbox" id="wnpic" <?php echo isset($_GET['wnpic']) ? 'checked':'' ?> name="wnpic">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                                <label class="control-label text-center">SÓ <?php echo APP_NAME; ?> ID?</label> 
                                <label class="switch">
                                    <input type="checkbox" id="cgfid" <?php echo isset($_GET['cgfid']) ? 'checked':'' ?> name="cgfid">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        <?php endif;?>
                        <div class="col-sm-auto switch-group-col col-xs-12 mt-3">
                            <label class="control-label text-center">SÓ <?php echo APP_NAME; ?> PASS?</label> 
                            <label class="switch">
                                <input type="checkbox" id="autocad" <?php echo isset($_GET['autocad']) ? 'checked':'' ?> name="autocad">
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="col-sm-<?php echo $cad_pax_tag == 0 ? $cad_pax_pics == 1 ? '6' : '8' : '4';?> col-xs-12 d-flex flex-row flex-wrap align-content-center justify-content-end">Mostrando <b>&nbsp;<?php echo $ttOnPage;?>&nbsp;</b> de <b>&nbsp;<?php echo $total;?> </b></div>
                        <div class="col-sm-2 col-xs-12 d-flex flex-row flex-wrap align-content-center">
                            <button title="Clique ou aperte Enter para aplicar os filtros." type="button" class="btn btn-primary w-100" id="btnSearchPax" style="align-self: flex-end;" onclick="changePage(1)">Buscar</button>
                        </div>
                    </form>
                    
                </div>
                <hr>
                <div class="btsCadastroPax">
                    <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){ ?>
                        <div class="col-sm-auto col-xs-12">
                            <a class="btn btn-success p-2 w-100" title="Novo Cadastro" href="/cadastroPax/create"><i class="fas fa-user-plus"></i> ADICIONAR</a>
                        </div>
                    <?php } ?>
                    <?php if($cad_pax_tag == 1 || (isset($_SESSION['cType']) && $_SESSION['cType'] == 1)):?>
                        <div class="col-sm-auto col-xs-12">
                            <button class="btn btn-warning p-2 w-100" data-toggle="modal" data-target="#modalImportPax"><i class="fas fa-file-import"></i> IMPORTAR</button>
                        </div>
                        <div class="col-sm-auto col-xs-12">
                            <button class="btn btn-danger p-2 w-100" data-toggle="modal" data-target="#modalSendInactivePax"><i class="fas fa-eject"></i> DESATIVAÇÃO EM LOTE</button>
                        </div>
                    <?php endif;?>
                    <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                        <div class="col-sm-auto col-xs-12">
                            <button class="btn btn-danger p-2 w-100" data-toggle="modal" data-target="#modalEraseBase"><i class="fas fa-eraser"></i> LIMPAR BASE</button>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <hr>

            <div class="card-create-body" style="display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:2; margin-bottom: 0; max-width:fit-content;">
                    <thead id="thead">
                    <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                        <th scope="col">Nome</th>
                        <th scope="col">Matrícula</th>
                        <th scope="col">Código</th>
                        <th scope="col">Status</th>
                        <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){ ?>
                            <th cope="col">Editar</th>
                        <?php } ?>
                    </tr>
                    </thead>
                </table>
                <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                    <tbody id="bodyPax">
                        <?php foreach($paxs as $pxs): ?>
                            <tr class="toMark" paxId="<?php echo $pxs->id;?>" paxName="<?php echo (preg_match('!!u', utf8_decode($pxs->NOME))) ? utf8_decode($pxs->NOME) : $pxs->NOME; ?>">
                                <td scope="col" style="width: <?php echo ($pxs->picture && $pxs->picture !== 0) ? '520' : '400';?>px !important; <?php echo ($pxs->picture && $pxs->picture !== 0) ? 'height: 107px !important;' : '';?>">
                                    <?php echo (preg_match('!!u', utf8_decode($pxs->NOME))) ? utf8_decode($pxs->NOME) : $pxs->NOME; ?>
                                    <?php 
                                        if($pxs->CGFPASS == 'SIM')
                                        echo '<i title="'.APP_NAME.' Pass - Autocadastramento" class="fas fa-mobile-alt ml-2" style="font-size: 20px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>';
                                    ?>
                                    <?php 
                                        if($pxs->monitor == '1')
                                        echo '<i title="Monitor" class="fas fa-user-shield ml-2" style="font-size: 20px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>';
                                    ?>
                                    <?php if($pxs->picture && $pxs->picture !== 0):?>
                                        <img class="picListCadPax" src="<?php echo $pxs->picture;?>">
                                    <?php endif;?>
                                </td>
                                <td scope="col" style="width: 200px !important;" ><?php echo utf8_encode($pxs->MATRICULA_FUNCIONAL); ?></td>
                                <td scope="col" style="width: 200px !important;"><?php echo $pxs->TAG; ?> </td>
                                <td scope="col" style="width: 80px !important;"><?php 
                                if($pxs->ATIVO == 1)
                                    echo "Ativo";
                                else
                                    echo "Inativo";
                                ?></td>
                                <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] != 1){ ?>
                                    <td scope="col">
                                        <a title="Editar" href="/cadastroPax/edit?id=<?php echo $pxs->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                                        </td>
                                <?php } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="wrapper1">
                    <div class="div1"></div>
                </div>
                <div class="wrapper1after"></div>
            </div>

            <?php if($ttPages > 1) :?>
                <hr>
                <div class="card-create-footer">
                    <div id="paginate">
                        <?php echo "<ul class='pagination'>";

                            $c = 0; //limit de imagens page
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
                            
                            echo "</ul>";?>
                    </div>
                </div>
            <?php endif;?>
                
        </div>

    </div>

    <!-- Modal de importação para adicionar passageiros -->
    <div class="modal fade" id="modalImportPax" role="dialog" aria-labelledby="modalImportPaxLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="modalImportPaxLabel">Importação de Passageiros</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
            <form id="sendFilePax" method="POST" action="/cadastroPax/importPax" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data" target="inactivePaxProgress">
                <div class="row">
                    <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                        <div class="col-sm-12 col-xs-12">
                            <label for="groupID" class="control-label">1&ordm;: Selecione o Grupo Usuário(Somente Grupos <?php echo APP_NAME; ?> TAG estarão na lista):</label>
                            <select class="form-control" id="groupID" name="groupID" onChange="checkImportPax(this.value)">
                                <option value="0">Selecione</option>
                                <?php foreach($gruposUser AS $gru): ?>
                                    <option value="<?php echo $gru['id']; ?>"><?php echo utf8_decode(utf8_encode($gru['NOME'])); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="groupIDName" id="groupIDName" value="">
                            <hr>
                        </div>  
                    <?php }; ?>  
                    <div class="col-sm-12 col-xs-12 mt-2">
                        <h6>Use sempre o modelo mais atualizado da planilha:</h6>
                    </div>
                    <div class="form-group col-sm-4 col-xs-12 mt-3 mb-0 <?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? 'seletorPax disabled' : '';?>">
                        <label><?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? '2&ordm;' : '1&ordm;';?>: Modelo Planilha</label> <br>
                        <span id="modelImportPax" title="BAIXAR MODELO" filename="MODELOCGFIMPORT">
                            BAIXAR MODELO
                            <div class="downloadProgress"></div>
                            <div class="downloadProgressTxt"></div>
                        </span>
                    </div>
                    <div class="form-group col-sm-5 col-xs-12 mt-3 mb-0 seletorPax disabled">
                        <label class="control-label"><?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? '3&ordm;' : '2&ordm;';?>: Selecione o Arquivo (XLS/XLSX):</label> <br>
                        <label class="btn_upload" for="filePax">
                            <text>Escolha um Arquivo</text>
                            <div class="uploadProgressTxt"></div>
                        </label>
                        <input id="filePax" type="file" style="display:none;" name="filePax" extOk="xls,xlsx"/>
                    </div>
                    <div class="col-sm-12 col-xs-12" style="margin-top: -1em !important;">
                        <label class="labelSelect datasContainer">
                            <input id="subAll" type="checkbox" name="subAll" checked>
                                <b>Substituir cadastros com código repetido</b>
                            <i title="Deixe essa opção marcada para substituir os cadastros com código repetido automaticamente ou desmarque para decidir depois quais cadastros serão substituídos." style="color:green; font-size:15px" class="fas fa-question-circle"></i>
                        </label>
                    </div>
                </div>
            </form>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary cancelImportPax" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-success sendFilePax" disabled onclick="sendPaxImport()">Importar</button>
            </div>
        </div>
        </div>
    </div>
  
  <!-- Modal de desativação em lote -->
  <div class="modal fade" id="modalSendInactivePax" role="dialog" aria-labelledby="modalSendInactivePaxLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalSendInactivePaxLabel">Desativação de passageiros em Lote</h5><br>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="sendInactivePax" method="POST" action="/cadastroPax/inactivePax" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data" target="inactivePaxProgress">
              <div class="row">
                  <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
                      <div class="col-sm-12 col-xs-12">
                          <label for="groupIDdesativa" class="control-label">1&ordm;: Selecione o Grupo Usuário(Somente Grupos <?php echo APP_NAME; ?> TAG estarão na lista):</label>
                          <select class="form-control" id="groupIDdesativa" name="groupIDdesativa" onChange="checkDesativePax(this.value)">
                              <option value="0">Selecione</option>
                              <?php foreach($gruposUser AS $gru): ?>
                                  <option value="<?php echo $gru['id']; ?>"><?php echo utf8_decode(utf8_encode($gru['NOME'])); ?></option>
                              <?php endforeach; ?>
                          </select>
                          <input type="hidden" name="groupIDNameDesativa" id="groupIDNameDesativa" value="">
                          <hr>
                      </div>  
                  <?php }; ?>  
                  <div class="col-sm-12 col-xs-12 mt-2">
                      <h6>Use sempre o modelo mais atualizado da planilha:</h6>
                  </div>
                  <div class="form-group col-sm-4 col-xs-12 mt-3 <?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? 'seletorPax disabled' : '';?>">
                      <label><?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? '2&ordm;' : '1&ordm;';?>: Modelo Planilha</label> <br>
                      <span id="modelDesativaPax" title="BAIXAR MODELO" class="btn_download" url="/assets/files/MODELOCGFDESATIVARLOTE.xlsx" filename="MODELOCGFDESATIVARLOTE" onclick="releaseDesactive()">
                          BAIXAR MODELO
                          <div class="downloadProgress"></div>
                          <div class="downloadProgressTxt"></div>
                      </span>
                  </div>
                  <div class="form-group col-sm-5 col-xs-12 mt-3 seletorPax disabled">
                      <label class="control-label"><?php echo (isset($_SESSION['cType']) && $_SESSION['cType'] == 1) ? '3&ordm;' : '2&ordm;';?>:Selecione o Arquivo (XLS/XLSX):</label> <br>
                      <label class="btn_upload" for="fileInactivePax">
                          <text>Escolha um Arquivo</text>
                          <div class="uploadProgressTxt"></div>
                      </label>
                      <input id="fileInactivePax" type="file" style="display:none;" name="fileInactivePax" extOk="xls,xlsx"/>
                  </div>
              </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary cancelInactivePax" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger sendInactivePax" disabled onclick="sendInactivePax()">Desativar</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Modal para limpeza de base -->
  <?php if(isset($_SESSION['cType']) && $_SESSION['cType'] == 1){ ?>
        <div class="modal fade" id="modalEraseBase" role="dialog" aria-labelledby="modalEraseBaseLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
              <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalEraseBaseLabel">Limpeza de Base</h5><br>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form id="eraseBasePax" method="POST" action="/cadastroPax/eraseBasePax" accept-charset="UTF-8" class="form-horizontal" target="inactivePaxProgress">
                      <div class="row">
                          <div class="col-sm-12 col-xs-12">
                              <label for="groupIDerase" class="control-label">1&ordm;: Selecione o Grupo Usuário:</label>
                              <select class="form-control" id="groupIDerase" name="groupIDerase" onChange="checkErasePax(this.value)">
                                  <option value="0">Selecione</option>
                                  <?php foreach($gruposUserClean AS $gru): ?>
                                      <option value="<?php echo $gru['id']; ?>"><?php echo utf8_decode(utf8_encode($gru['NOME'])); ?></option>
                                  <?php endforeach; ?>
                              </select>
                              <input type="hidden" name="groupIDNameErase" id="groupIDNameErase" value="">
                              <div class="col-sm-12 col-xs-12 mt-2">
                                  <h6><i title="ATENÇÃO" style="color:red; font-size:15px" class="fas fa-exclamation-triangle"></i> Todos os passageiros do Grupo selecionado serão inativados!</h6>
                              </div>
                          </div>  
                      </div>
                  </form>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary cancelErasePax" data-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-danger eraseBasePax" disabled onclick="eraseBasePax()">Limpar</button>
              </div>
              </div>
          </div>
        </div>
  
<?php } ?>

</main>
<script>
    // Limpar o estado do checkbox quando a página for carregada
    window.onload = function() {
        
        <?php if(!isset($_GET['gr']) || $_GET['gr'] == ''):?>
            document.getElementById('grme').value = "";
        <?php endif;?>
        <?php if(!isset($_GET['int'])):?>
            document.getElementById('int').checked = false;
        <?php endif;?>
        <?php if(!isset($_GET['withoutGroups'])):?>
            document.getElementById('withoutGroups').checked = false;
        <?php endif;?>
        <?php if(!isset($_GET['autocad'])):?>
            document.getElementById('autocad').checked = false;
        <?php endif;?>
        
    };
</script>