<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2 class="pageTitle"></h2>
            <hr>
            <div id="filtros">
                <form id="formFilterEsc" method="GET" action="/userEscala/" accept-charset="UTF-8" class="form-horizontal">
                    <div class="row">
                        <?php if (!isset($_SESSION['cFret']) || ($_SESSION['cFret'] && $_SESSION['cType'] == 2) ) { ?>
                        <div class="col-sm-4 col-xs-6">
                            <select id="uniEsc" name="u" class="form-control controlPerson" onchange="filterUnEscala()">
                                <option value="0">Filtrar por Unidade</option>
                                <?php foreach($unidades AS $uni): ?>
                                <?php if( isset($_GET['u']) && $_GET['u'] == $uni->id ) { ?>
                                    <option value="<?php echo $uni->id; ?>" selected><?php echo $uni->descricao; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $uni->id; ?>"><?php echo $uni->descricao; ?></option>
                                <?php }?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4 col-xs-6">
                            <?php if(isset($_GET['fNome']) && $_GET['fNome'] != ""): ?>
                                <span class="fNomeBtn btn btn-warning" onclick="limparNomeEscala()">Limpar</span>
                            <?php else: ?>
                                <span class="fNomeBtn btn btn-info" onclick="filterNomeEscala()">Filtrar</span>
                            <?php endif; ?>
                            <input type="text" id="fNome" name="fNome" class="form-control" 
                                placeholder="Filtrar por nome" 
                                value="<?php if(isset($_GET['fNome'])) : echo $_GET['fNome'];  endif?>"
                                <?php if(isset($_GET['fNome']) && $_GET['fNome'] != ""): ?>
                                   fNomeNow="<?php echo $_GET['fNome']; ?>" 
                                <?php endif;?>/>
                        </div>
                            <?php $pgn = isset($_GET['p']) ? $_GET['p'] : 1; ?>
                            <input type="hidden" id="pesc" name="p" value="<?php echo $pgn ?>" />
                        </form>
                        <div class="col-sm-2 col-xs-12">
                            <form 
                                id="importFilesUser" 
                                method="POST" 
                                action="/userEscala/importUser" 
                                accept-charset="UTF-8" 
                                enctype="multipart/form-data"
                            >
                                <label class="btn_upload" for="fileUser">
                                    <text>IMPORTAR</text>
                                    <div class="uploadProgressTxt"></div>
                                </label>
                                <input class="subOnchage" id="fileUser" type="file" style="display:none;" name="fileUser" extOk="xls,xlsx,csv"/>
                            </form>
                        </div>
                        

                        <div class="col-sm-2 col-xs-12">                            
                            <span title="MODELO XLS" class="btn_download" url="/assets/files/MODELOCGFUSER.xlsx" filename="MODELOCGFUSER">
                                MODELO XLS
                                <div class="downloadProgress"></div>
                                <div class="downloadProgressTxt"></div>
                            </span>
                        </div>
                        <?php }; ?>
                    </div>
                   

                
            </div>
            <table id="table" data-toggle="table" class="table table-bordered">
                <thead>
                    <tr class="headerTr">
                        <th style="min-width: 300px !important;">Nome</th>
                        <th style="min-width: 300px !important; width:500px">Email</th>
                        <th style="min-width: 100% !important; width: 100%;">Unidade</th>

                        <?php if ( !isset($_SESSION['cFret']) ) { ?>
                            <th style="min-width: 100% !important; width: 100%;">Grupo</th>
                        <?php }; ?>

                        <th style="min-width: 70px !important; width: 150px"> 
                            <?php if (!isset($_SESSION['cFret']) || ($_SESSION['cFret'] && $_SESSION['cType'] == 2) ) { ?>
                            <a title="Novo Cadastro" href="/userEscala/create" class="btn btn-success">
                            <i class="fas fa-plus"></i></a>
                            <?php }; ?>
                        </th>
                    </tr>
                </thead>
                <body> 
                <?php foreach($ret AS $r): ?>
                    <tr id="<?php echo $r->id ?>">
                        <td><?php echo $r->nome; ?></td> 
                        <td><?php echo $r->email; ?></td> 
                        <td><?php echo $r->descricao; ?></td> 

                        <?php if ( !isset($_SESSION['cFret']) ) { ?>
                            <td><?php echo $r->grupoName; ?></td> 
                        <?php }; ?>

                        <td class="text-center">
                            <a title="Editar" href="/userEscala/edit?id=<?php echo $r->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                           
                            <?php if (!isset($_SESSION['cFret']) || ($_SESSION['cFret'] && $_SESSION['cType'] == 2) ) { ?>
                            <button title="Excluir" class="btn btn-danger editIcon" onclick="confirmDelet('Usuário/Líder', false, '/userEscala/delete', <?php echo $r->id ?>)"><i class="fas fa-trash"></i></button>
                            <?php }; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </body> 
            </table>
            <div id="paginate">
                 <?php if (!isset($_SESSION['cFret']) || ($_SESSION['cFret'] && $_SESSION['cType'] == 2) ) {  ?>
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
                <?php }; ?>
            </div>
        </div>
    </div>
</main>
</div>