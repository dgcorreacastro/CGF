<main class="py-4">
    <div class="personContainer">
        <h2 class="pageTitle"></h2>
        <hr>
      
            <div class="row" style="padding-left: 20px;">
                <form id="formFilterEsc" method="GET" action="/colaborador" accept-charset="UTF-8" class="form-horizontal row col-sm-10 col-xs-10">
                <div class="col-sm-4 col-xs-12">
                    <?php $nam = isset($_GET['n']) ? $_GET['n'] : ""; ?>
                    <label for="n" class="control-label">Nome:</label>
                    <input class="form-control" name="n" type="text" value="<?php echo $nam; ?>">
                </div>
                <?php if(!isset($_SESSION['cFret'])){ ?>
                <div class="col-sm-5 col-xs-12">
                    <label for="nagrme" class="control-label">Grupo:</label>
                    <select id="gr" name="gr" class="form-control">
                        <option value="">Selecione um Grupo</option>
                        <?php foreach($grupos AS $gr): ?>
                            <option value="<?php echo $gr['ID_ORIGIN']; ?>"><?php echo utf8_encode($gr['NOME']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php } ; ?>

                <?php if(isset($_SESSION['cFret'])){ ?>
                    <?php $unP = isset($_GET['u']) ? $_GET['u'] : ""; ?>
                    <div class="col-sm-4 col-xs-12">
                        <label for="u" class="control-label">Unidade:</label>
                        <select id="u" name="u" class="form-control">
                            <option value="">Selecione</option>
                            <?php foreach($unidades AS $uni): ?>
                                <option 
                                    value="<?php echo $uni->id; ?>"
                                    <?php echo $unP == $uni->id ? 'selected': ''; ?>
                                ><?php echo $uni->descricao; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php } ; ?>

                <?php $pgn = isset($_GET['p']) ? $_GET['p'] : 1; ?>
                <input type="hidden" id="pesc" name="p" value="<?php echo $pgn ?>" />

                <div class="col-sm-2 col-xs-12">
                    <button class="btn btn-primary" style="margin-top:30px" onclick="filtrarPaxGroup()">Buscar</button>
                </div>
                </form>

                <div class="col-sm-2 col-xs-2">
                    <button class="btn btn-warning" style="margin-top:30px" data-toggle="modal" data-target="#exampleModal">IMPORTAR</button>
                </div>
            </div>
       
        <hr>
        <div class="card-body">
            <div class="wrapper1">
                <div class="div1"></div>
            </div>
            <table id="table" data-toggle="table" class="table table-bordered customScroll">
            <thead>
                <tr class="headerTr">
                    <th style="min-width: 100% !important; width: 100%;">Nome</th>
                    <th style="min-width: 100px !important; width: 100%;">RE</th>
                    <th style="min-width: 100% !important; width: 160px !important;">Função</th>
                    <th style="min-width: 160px !important; width: 160px !important;">Unidade</th>
                    <!-- <th class="text-center" style="min-width: 60px !important; width: 160px !important;"> </th> -->
                </tr>
            </thead>
            <body id="bodyPax">
                <?php foreach($paxs as $pxs): ?>
                    <tr>
                        <td><?php echo utf8_encode($pxs->nome); ?></td>
                        <td><?php echo utf8_encode($pxs->re); ?></td>
                        <td><?php echo $pxs->funcao; ?> </td>
                        <td><?php echo $pxs->descricao; ?> </td>
                        <!-- <td class="text-center">
                            <a title="Editar" href="/colaborador/edit?id=<?php echo $pxs->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                        </td> -->
                    </tr>
                <?php endforeach; ?>
            </body>
            </table>
            <div id="paginate">
                <?php 
                    if($ttPages > 1) {
                        echo "<ul class='pagination' style='float: right;'>";

                        $c = 0; //limit de imagens page
                        $sr = false;
                        $er = false;
                        for($i=1;$i <= $ttPages; $i++)
                        { 
                            if($pgn != 1 && !$sr){
                                echo '<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)" > << </a></li>';
                                $sr = true;
                            }

                            if($pgn == $i){
                                echo '<li class="page-item active"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                $c++;
                            }

                            if($i > $pgn && ($i == ( $pgn + 1) || $i == ( $pgn + 2) || $i == ( $pgn + 3) || ($pgn == 1 && $i == ( $pgn + 4)) ) && $c < 5) {
                                echo '<li class="page-item"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                $c++;
                            }


                            if(isset($pgn) && $i < $pgn && ($i == ( $pgn - 1) || $i == ( $pgn - 2)  || $i == ( $pgn - 3) || ($pgn == $ttPages && $i == ( $pgn - 4) )  ) && $c < 5) {
                                echo '<li class="page-item"><a href="#" onclick="changePage('.$i.')" class="page-link">'.$i.'</a></li>';
                                $c++;
                            }
                            // if(isset($pgn) && $pgn > 5 &&  $c < 5) {
                            //     echo '<li class="page-item">...</a></li>';
                            // } 
                            // if( $c == 5 && $pgn < (( $ttPages - 1)) ) {
                            //     echo '<li class="page-item"><a style="color: black;" class="page-link" >...</a></li>';
                            // } 
                            
                        }
                        if(isset($pgn) && $pgn != $ttPages && !$er){
                            echo '<li class="page-item"><a href="#" onclick="changePage('.$ttPages.')" class="page-link"> >> </a></li>';
                            $er = true;
                        }
                        
                        echo "</ul>";
                    } 
                ?>
            </div>
        </div>
    </div>
</main>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Importação de Colaboradores</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="sendFilePax" method="POST" action="/colaborador/sendFilePax" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
            <div class="row">
                <div class="form-group col-sm-4 col-xs-12 mt-3">
                    <label class="control-label">Selecione o Arquivo (XLS/XLSX):</label> <br>
                    <label class="btn_upload" for="filePax">
                        <text>Escolha um Arquivo</text>
                        <div class="uploadProgressTxt"></div>
                    </label>
                    <input id="filePax" type="file" style="display:none;" name="filePax" extOk="xls,xlsx"/>
                </div>
                <div class="form-group col-sm-3 col-xs-12 mt-3">
                    <label>Modelo Planilha</label> <br>
                    <span title="BAIXAR MODELO" class="btn_download" url="/assets/files/MODELOCGFIMPORTCOLAB.xlsx" filename="MODELOCGFIMPORTCOLAB">
                        BAIXAR MODELO
                        <div class="downloadProgress"></div>
                        <div class="downloadProgressTxt"></div>
                    </span>
                </div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="sendPaxImport()">Importar</button>
      </div>
    </div>
  </div>
</div>