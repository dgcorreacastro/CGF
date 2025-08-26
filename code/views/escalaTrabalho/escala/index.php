<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2><i class="far fa-calendar-alt"></i> Listagem Escalas</h2>
            <hr>
            <div id="filtros">
                <form id="formFilterEsc" method="GET" action="/escala/" accept-charset="UTF-8" class="form-horizontal">
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <select id="uniEsc" name="u" class="form-control controlPerson">
                                <option value="0"> Unidade</option>
                                <?php foreach($unidades AS $uni): ?>
                                <?php if( isset($_GET['u']) && $_GET['u'] == $uni->id ) { ?>
                                    <option value="<?php echo $uni->id; ?>" selected><?php echo $uni->descricao; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $uni->id; ?>"><?php echo $uni->descricao; ?></option>
                                <?php }?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <select id="setEsc" name="s" class="form-control controlPerson">
                                <option value="0"> Setor</option>
                                <?php foreach($setores AS $set): ?>
                                <?php if( isset($_GET['s']) && $_GET['s'] == $set->id ) { ?>
                                    <option value="<?php echo $set->id; ?>" selected><?php echo $set->descricao; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $set->id; ?>"><?php echo $set->descricao; ?></option>
                                <?php }?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <select name="ld" class="form-control controlPerson">
                                <option value="0">Gestor</option>
                                <?php foreach($lider AS $lid): ?>
                                <?php if( isset($_GET['ld']) && $_GET['ld'] == $lid->id ) { ?>
                                    <option value="<?php echo $lid->id; ?>" selected><?php echo $lid->nome; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $lid->id; ?>"><?php echo $lid->nome; ?></option>
                                <?php }?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-2">

                        <div class="col-sm-2 col-xs-12">
                            <input type="number" name="mf" class="form-control" placeholder="Mês" min="1" max="12" 
                                value="<?php echo isset($_GET['mf']) && $_GET['mf'] > 0 ? $_GET['mf'] : ''; ?>"  />     
                        </div>
                        <div class="col-sm-2 col-xs-12">
                            <input type="number" name="af" class="form-control" placeholder="Ano" min="2021" 
                            value="<?php echo isset($_GET['af']) && $_GET['af'] > 0 ? $_GET['af'] : ''; ?>"  />     
                        </div>

                        <div class="col-sm-3 col-xs-12">
                            <select name="stts" class="form-control controlPerson filtroSelect2">
                                <option value="0">Status</option>
                                <option value="1" 
                                    <?php echo isset($_GET['stts']) && $_GET['stts'] == 1 ? 'selected' : ''; ?>
                                > Rascunho </option>
                                <option value="2" 
                                    <?php echo isset($_GET['stts']) && $_GET['stts'] == 2 ? 'selected' : ''; ?>
                                > Aguardando Aprovação</option>
                                <option value="3" 
                                    <?php echo isset($_GET['stts']) && $_GET['stts'] == 3 ? 'selected' : ''; ?>
                                > Efetivado</option>
                                <option value="4" 
                                    <?php echo isset($_GET['stts']) && $_GET['stts'] == 4? 'selected' : ''; ?>
                                > Negado</option>
                            </select>
                        </div>
                                 
                        <div class="col-sm-5 col-xs-12" style="text-align: right">
                            <button type="submit" class="btn btn-info"> FILTRAR </button>
                        </div>
                    </div>
                    <?php $pgn = isset($_GET['p']) ? $_GET['p'] : 1; ?>
                    <input type="hidden" id="pesc" name="p" value="<?php echo $pgn ?>" />
                    
                </form>
            </div>
            <hr>
            <table id="table" data-toggle="table" class="table table-bordered">
                <thead>
                    <tr class="headerTr">
                        <th style="min-width: 100% !important; width: 100%;">Setor</th>
                        <th style="min-width: 150px !important; width: 100%;">Mês/Ano</th>
                        <th style="min-width: 150px !important; width: 100%;">Gestor</th>
                        <th style="min-width: 150px !important; width: 100%;">Unidade</th>
                        <th style="min-width: 150px !important; width: 100%;">Status</th>
                        <th style="min-width: 70px !important; width: 150px"> 
                            <a title="Novo Cadastro" href="/escala/create" class="btn btn-success">
                            <i class="fas fa-plus"></i></a>
                        </th>
                    </tr>
                </thead>
                <tbody id="listEscala">
                <?php foreach($ret AS $r): 
                    
                    $classeTdStatus = '';
                    if ($r->efetivado == 1){
                        $classeTdStatus = 'rascunhoEscala';
                    }

                    if ($r->efetivado == 2){
                        $classeTdStatus = 'aguardaEscala';
                    }

                    if ($r->efetivado == 3){
                        $classeTdStatus = 'efetivadoEscala';
                    }

                    if ($r->efetivado == 4){
                        $classeTdStatus = 'negadoEscala';
                    }

                ?>
                    
                    <tr id="<?php echo $r->id ?>">
                        <td><?php echo $r->Setor; ?></td> 
                        <td><?php echo sprintf("%02d", $r->mes) . "/" . $r->ano; ?></td> 
                        <td><?php echo $r->Lider; ?></td> 
                        <td><?php echo $r->Unidade; ?></td> 
                        <td class="<?php echo $classeTdStatus; ?>"><?php echo $r->statusEscala; ?></td> 
                        <td class="text-center">
                            <?php if( in_array($r->efetivado, [1, 4]) ) { ?>
                                <a title="Editar" href="/escala/edit?id=<?php echo $r->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                                <button title="Excluir" class="btn btn-danger editIcon" onclick="confirmDelet('Escala', false, '/escala/delete', <?php echo $r->id ?>)"><i class="fas fa-trash"></i></button>
                            <?php }else{ ?>
                                <a title="Visualizar" href="/escala/show?id=<?php echo $r->id ?>" class="btn btn-primary editIcon"><i class="fas fa-eye"></i></a>
                                <?php if( $r->efetivado == 3 ) { ?>
                                    <a title="Imprimir" href="/escala/print?id=<?php echo $r->id ?>" class="btn btn-warning editIcon" target="_blank"><i class="fas fa-print"></i></a>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody> 
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
</div>