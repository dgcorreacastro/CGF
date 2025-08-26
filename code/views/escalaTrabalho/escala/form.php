<div class="card-create-body">
    <div class="row">
        <!-- <div class="col-sm-1 col-xs-1">
            <h6>Filtro:</h6>
        </div> -->

        <input type="hidden" id="typeEscale" name="typeEscale" />
        <input type="hidden" id="mxfm" value="<?php echo $mxfm ?>" />
        <input type="hidden" id="mxsf" value="<?php echo $mxsf ?>" />

        <!-- <div class="col-sm-2 col-xs-12">
            <label for="setores" class="control-label">Centro Custo:</label>
            <select id="setores" name="setores" class="controlPerson">
                <option>Selecione</option>

                <?php foreach($setores AS $ms): ?>

                    <?php if(
                        ( isset($escal['escala']) && $escal['escala']->setor == $ms->id )
                    ) { ?>
                        
                        <option value="<?php echo $ms->id; ?>" selected><?php echo $ms->descricao; ?></option>
                    
                    <?php } else { ?>

                        <option value="<?php echo $ms->id; ?>"><?php echo $ms->descricao; ?></option>

                    <?php }?>
                   
                <?php endforeach; ?>
                
            </select>
        </div>
        <div class="col-sm-1 col-xs-1">
            <span class="btn btn-info" onclick="getPaxSetor()">FILTRAR</span>
        </div> -->
        
    </div>

    <div class="row">

        <div class="col-sm-4 col-xs-12">
            <label for="setor" class="control-label">Setor:</label>
            <?php if( isset($_SESSION['old'])) { ?>
                <input class="form-control" name="setor" type="text" value="<?php echo $_SESSION['old']['setor']; ?>" />
            <?php } else if( isset($escal['escala'])) { ?>
                <input class="form-control" name="setor" type="text" value="<?php echo $escal['escala']->setor; ?>" />
                <input id="id" class="form-control" name="id" type="hidden" value="<?php echo $escal['escala']->id; ?>" />
            <?php } else { ?>
                <input class="form-control" name="setor" type="text"/>
            <?php }?>
        </div>
        <div class="col-sm-4 col-xs-12">
            <label for="centroCusto" class="control-label">Centro de Custo:</label>
            <?php if( isset($_SESSION['old'])) { ?>
                <input class="form-control" id="centroCusto" name="centroCusto" type="text" value="<?php echo $_SESSION['old']['centro']; ?>" />
            <?php } else if( isset($escal['escala']) && $escal['escala']->centro != "" ) { ?>
                <input class="form-control" id="centroCusto" name="centroCusto" type="text" value="<?php echo $escal['escala']->centro; ?>" />
            <?php } else { ?>
                <input class="form-control" id="centroCusto" name="centroCusto" type="text" />
            <?php }?>
        </div>
        <div class="col-sm-4 col-xs-12">
            <label for="descricaoCC" class="control-label">Descrição Centro de Custo:</label>
            <?php if( isset($_SESSION['old'])) { ?>
                <input style="background-color: #b5b1b1" class="form-control" id="descricaoCC" name="descricaoCC" type="text" value="<?php echo $_SESSION['old']['descCC']; ?>" readonly />
            <?php } else if( isset($escal['escala']) && $escal['escala']->descCC != "" ) { ?>
                <input style="background-color: #b5b1b1" class="form-control" id="descricaoCC" name="descricaoCC" type="text" value="<?php echo $escal['escala']->descCC; ?>" readonly />
            <?php } else { ?>
                <input style="background-color: #b5b1b1" class="form-control" id="descricaoCC" name="descricaoCC" type="text" readonly />
            <?php }?>
        </div>
        <div class="col-sm-4 col-xs-12">
            <label for="lider" class="control-label">Gestor:</label>
            <?php if( isset($_SESSION['cFret']) && $_SESSION['cType'] == 1) { ?>
                <select id="lider" name="lider" class="form-control controlPerson" disabled>
             <?php } else { ?>
                <select id="lider" name="lider" class="form-control controlPerson" required>
                <!-- <select id="lider" name="lider" class="form-control controlPerson" required onchange="getUnByLider(this.value)"> -->
            <?php }?>
                <option>Selecione</option>

                <?php foreach($lider AS $ms): ?>

                    <?php if( ( isset($escal['escala']) && $escal['escala']->liderID == $ms->id ) ) { ?>
                        
                        <option value="<?php echo $ms->id; ?>" selected><?php echo $ms->nome; ?></option>

                    <?php } else if( isset($_SESSION['cFret']) && $_SESSION['cType'] == 1 && $_SESSION['cLogin'] == $ms->id ) { ?>

                        <option value="<?php echo $ms->id; ?>" selected><?php echo $ms->nome; ?></option>
                        
                    <?php } else { ?>

                        <option value="<?php echo $ms->id; ?>"><?php echo $ms->nome; ?></option>

                    <?php }?>
                    
                <?php endforeach; ?>
                
            </select>
        </div>

        <div class="col-sm-4 col-xs-12">
            <label for="unidadeID" class="control-label">Unidade:</label>
            <select name="unidadeID" id="unidade" class="form-control controlPerson" onchange="changeMonthYear()">
                <option value="">Selecione</option>
            
                <?php foreach($unidades AS $un): ?>
                    <option 
                        value="<?php echo $un->id; ?>"
                        <?php echo isset($escal['escala']->unidadeID) && $escal['escala']->unidadeID == $un->id ? 'selected' : ''; ?>
                    ><?php echo $un->descricao; ?></option>
                <?php endforeach; ?>
            </select>

        </div>
     
        <div class="col-sm-2 col-xs-12">
            <label for="mes" class="control-label">Mês:</label>
            <select id="mes" name="mes" class="form-control controlPerson" required onchange="changeMonthYear()">
                <option value="">Selecione</option>

                <?php foreach($meses AS $k => $ms): ?>
                    <option 
                        value="<?php echo ($k + 1); ?>"
                        <?php echo ($k + 1) == $mActu ? 'selected' : ''; ?>
                    ><?php echo $ms; ?></option>
                <?php endforeach; ?>
                
            </select>
        </div>
        <div class="col-sm-2 col-xs-12">
            <label for="ano" class="control-label">Ano:</label>
            <select id="ano" name="ano" class="form-control controlPerson" required onchange="changeMonthYear()">
                <option>Selecione</option>

                <?php foreach($ano AS $ms): ?>
                    <option 
                        value="<?php echo $ms; ?>"
                        <?php echo ($ms) == $aActu ? 'selected' : ''; ?>
                    ><?php echo $ms; ?></option>
                <?php endforeach; ?>
                
            </select>
        </div>
    

        <hr style="width:100%">

        <div class="tableDiv">
            <table id="table" data-toggle="table" class="table table-bordered tableEscala">
                <thead>
                    <tr class="headerTr jc">
                        <th style="width: 10% !important;min-width: 110px !important;" rowspan="2">
                            <span title="Adicionar Colaborador na Escala" class="btnRE" onclick="addRE(1)"><i class="fas fa-plus"></i></span>
                        RE</th>
                       
                        <th class="thNomeEscala" rowspan="2">NOME</th>
                        <th style="min-width: 115px !important; max-width: 115px !important;" rowspan="2" class="funAppTMQ">HORÁRIO</th>
                        <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                            <?php if( $daysMon[$i]['color'] ) { ?>
                                <th class="infTMQ letraDia" style="max-width: 1% !important;background-color:#b97800" diafds="<?php echo $daysMon[$i]['letter']; ?>"> <?php echo $daysMon[$i]['letter']; ?> </th>
                            <?php }else { ?>
                                <th class="infTMQ letraDia" style="max-width: 1% !important;"> <?php echo $daysMon[$i]['letter']; ?> </th>
                        <?php }}; ?>
                    </tr>
                    <tr class="headerTr funAppTMQ2">
                        <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                            <?php if( $daysMon[$i]['color'] ) { ?>
                                <th class="infTMQ" style="max-width: 1% !important;background-color:#b97800"> <?php echo $i; ?> </th>
                            <?php }else { ?>
                                <th class="infTMQ" style="max-width: 1% !important;"> <?php echo $i; ?> </th>
                         <?php }}; ?>
                    </tr>
                </thead>
                <tbody id="turno1">
                    <?php if(isset($escal['itemEscala'])){ ?>
                    <?php 
                        foreach($escal['itemEscala'] AS $key=>$itens){ ?>
                        <tr re="<?php echo $itens['re']; ?>" nome="<?php echo $itens['nome']; ?>">
                            <?php if(isset($copy)){ 
                                $tpUpdate = 2;
                            ?>
                            <td>
                                <span title="Remover Colaborador da Escala" class="btnLessColl" onclick="removeCollab(this)"><i class="fas fa-user-minus"></i></span>
                                <input type="text" name="re[]" class="are-1-<?php echo $key;?> persInRE ar-1" value="<?php echo $itens['re']; ?>" placeholder="Procurar.."> 
                            </td>
                            <?php } else{
                                $tpUpdate = 1;
                            ?>
                            <td>
                                <input type="hidden" value="<?php echo $itens['id']; ?>" class="itenIdent" />
                                <span title="Remover Colaborador da Escala" class="btnLessColl" onclick="removeCollabExist(this, <?php echo $itens['id'];?>)"><i class="fas fa-user-minus"></i></span>
                                <span class="reex"><?php echo $itens['re']; ?></span>
                            </td>
                            <?php } ?>
                            <td class="nomeEscala">
                                <div class="cargoHover" title="Clique 2 vezes para ocultar o cargo.">
                                    <?php echo $itens['funcao']; ?>
                                </div>
                                <?php echo $itens['nome']; ?>
                                <i class="fas fa-address-card verCargo" title="Ver Cargo"></i>
                                <i class="fa fa-random folgasRandom" aria-hidden="true" title="Criar Folgas Randomicamente"></i>
                            </td>
                            <?php if(isset($copy)){ ?>
                                <td class="horario">
                                    <select name="hour[]" class="selecTurno">
                                        <option value="0">Selecione Horário</option>
                                        <option value="1" <?php if($itens['TURNO'] == '1º Turno - ESCALA'):?> selected="selected" <?php endif; ?>>1º TURNO - ESCALA</option>
                                        <option value="2" <?php if($itens['TURNO'] == '2º Turno - ESCALA'):?> selected="selected" <?php endif; ?>>2º TURNO - ESCALA</option>
                                        <option value="3" <?php if($itens['TURNO'] == '3º Turno - ESCALA'):?> selected="selected" <?php endif; ?>>3º TURNO - ESCALA</option>
                                        <option value="4" <?php if($itens['TURNO'] == 'ADM - ESCALA'):?> selected="selected" <?php endif; ?>>ADM - ESCALA</option>
                                    </select>
                                </td>
                            <?php } else{ ?>
                                <td>
                                    <?php echo $itens['TURNO']; ?>
                                </td>
                            <?php } ?>
                            
                            <?php for($i=1;$i <= count($daysMon); $i++){ 
                                $col = $i;

                                if($itens['t'.$i] == 0){
                                    $classeItenEscala = 'isDiaTrabalho cursorHand';
                                    $titleEscala = 'Dia de Trabalho';
                                }

                                if($itens['t'.$i] == 1){
                                    $classeItenEscala = 'isFolga cursorHand secl';
                                    $titleEscala = 'Folga';
                                }

                                if($itens['t'.$i] == 2){
                                    $classeItenEscala = 'isAf cursorHand';
                                    $titleEscala = 'Afastamento';
                                }

                                if($itens['t'.$i] == 3){
                                    $classeItenEscala = 'isFe cursorHand';
                                    $titleEscala = 'Férias';
                                }

                                if($itens['t'.$i] == 4){
                                    $classeItenEscala = 'isTurno cursorHand';
                                    $titleEscala = 'Turno';
                                }
                                if(isset($copy)){ ?>
                                    <td id="chooseEscala" class="<?php echo $classeItenEscala;?>"  title="<?php echo $titleEscala; ?>" v="<?php echo $itens['t'.$i]; ?>" n="<?php echo $col;?>" t="1">
                                    <input type="hidden" name="<?php echo "t".$col;?>-1[]" class="<?php echo "t".$col;?>-1" secl value="<?php echo $itens['t'.$i]; ?>">
                                <?php } else{
                                ?>
                                <td class="<?php echo $classeItenEscala;?>" id="chooseEscala" v="<?php echo $itens['t'.$i]; ?>" title="<?php echo $titleEscala; ?>">
                                <?php } ?>
                                <div class="chooseEscala">
                                    <div title="Turno" v="4" cl="<?php echo "t".$col?>" id="<?php echo $itens['id'];?>" onclick="updateEscalaNew(this, <?php echo $tpUpdate; ?>, event)"></div>
                                    <div title="Afastamento" v="2" cl="<?php echo "t".$col?>" id="<?php echo $itens['id'];?>" onclick="updateEscalaNew(this, <?php echo $tpUpdate; ?>, event)"></div>
                                    <div title="Férias" v="3" cl="<?php echo "t".$col?>" id="<?php echo $itens['id'];?>" onclick="updateEscalaNew(this, <?php echo $tpUpdate; ?>, event)"></div>
                                    <div title="Folga" v="1" cl="<?php echo "t".$col?>" id="<?php echo $itens['id'];?>" onclick="updateEscalaNew(this, <?php echo $tpUpdate; ?>, event)"></div>
                                </div>
                            </td>
                            <?php }; ?>
                        <tr>
                    <?php }}; ?>

                </tbody> 
            </table>
            <div class="legendasEscala">
                <span>Legendas:</span>
                <div>
                    <div></div><span>Folga</span>
                </div>

                <div>
                    <div></div><span>Afastamento</span>
                </div>

                <div>
                    <div></div><span>Férias</span>
                </div>

                <div>
                    <div></div><span>Turno</span>
                </div>
            </div>   
        </div>
    </div>
</div>