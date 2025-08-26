<main class="py-4">
    
    <div class="personpersonContainerEscala">
        <div class="card-body">
            <input type="hidden" id="typeEscale" name="typeEscale" />
            <div class="card-create-header">
                <h2><i class="fa fa-building"></i> Escala</h2>
            </div>
            <hr>
            <input type="hidden" id="eghrsfr" value="<?php echo $escal['escala']->id; ?>" />
            <div class="card-create-body">
                <div class="row">
                    <div class="col-sm-4 col-xs-12">
                        <label for="setor" class="control-label">Setor:</label>
                        <input class="form-control" type="text" value="<?php echo $escal['escala']->setor; ?>" disabled />
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <label for="centroCusto" class="control-label">Centro de Custo:</label>
                        <input class="form-control" type="text" value="<?php echo $escal['escala']->centro; ?>" disabled />
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <label for="descricaoCC" class="control-label">Descrição Centro de Custo:</label>
                        <input class="form-control" type="text" value="<?php echo $escal['escala']->descCC; ?>" disabled />
                    </div>
                    <div class="col-sm-4 col-xs-12">
                        <label for="lider" class="control-label">Gestor:</label>
                        <select class="form-control controlPerson" disabled>
                            <option>Selecione</option>

                            <?php foreach($lider AS $ms): ?>
                                <option value="<?php echo $ms->id; ?>"
                                <?php echo $ms->id == $escal['escala']->liderID ? 'selected' : ''; ?>
                                ><?php echo $ms->nome; ?></option>
                            <?php endforeach; ?>
                            
                        </select>
                    </div> 

                    <div class="col-sm-4 col-xs-12">
                        <label for="lider" class="control-label">Unidade:</label>
                        <select class="form-control controlPerson" disabled>
                            <option>Selecione</option>

                            <?php foreach($unidades AS $un): ?>
                                <option value="<?php echo $un->id; ?>"
                                <?php echo $un->id == $escal['escala']->unidadeID ? 'selected' : ''; ?>
                                ><?php echo $un->descricao; ?></option>
                            <?php endforeach; ?>
                            
                        </select>
                    </div>
                
                    <div class="col-sm-2 col-xs-12">
                        <label for="mes" class="control-label">Mês:</label>
                        <select class="form-control controlPerson" disabled>
                            <option>Selecione</option>

                            <?php foreach($meses AS $k => $ms): ?>
                                <option 
                                    value="<?php echo $k; ?>"
                                    <?php echo ($k + 1) ==  $escal['escala']->mes ? 'selected' : ''; ?>
                                ><?php echo $ms; ?></option>
                            <?php endforeach; ?>
                            
                        </select>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <label for="ano" class="control-label">Ano:</label>
                        <select class="form-control controlPerson" disabled>
                            <option>Selecione</option>

                            <?php foreach($ano AS $ms): ?>
                                <option 
                                    value="<?php echo $ms; ?>"
                                    <?php echo ($ms) ==  $escal['escala']->ano ? 'selected' : ''; ?>
                                ><?php echo $ms; ?></option>
                            <?php endforeach; ?>
                            
                        </select>
                    </div>

                    <hr style="width:100%">

                    <div class="tableDiv">
                        <table id="table" data-toggle="table" class="table table-bordered tableEscala">
                            <thead>
                                <tr class="headerTr jc">
                                    <th style="width: 10% !important;min-width: 110px !important;" rowspan="2">RE</th>
                                    <th class="thNomeEscala" rowspan="2">NOME</th>
                                    <th style="min-width: 115px !important; max-width: 115px !important;" rowspan="2" class="funAppTMQ">HORÁRIO <br> ESCALA</th>
                                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                                        <?php if( $daysMon[$i]['color'] ) { ?>
                                            <th class="infTMQ" style="max-width: 1% !important;background-color:#b97800"> <?php echo $daysMon[$i]['letter']; ?> </th>
                                        <?php }else { ?>
                                            <th class="infTMQ" style="max-width: 1% !important;"> <?php echo $daysMon[$i]['letter']; ?> </th>
                                <?php }}; ?>
                                </tr>
                                <tr class="headerTr">
                                    <?php for($i=1;$i <= count($daysMon); $i++){ ?>
                                    <?php if( $daysMon[$i]['color'] ) { ?>
                                        <th class="infTMQ" style="max-width: 1% !important;background-color:#b97800"> <?php echo $i; ?> </th>
                                    <?php }else { ?>
                                        <th class="infTMQ" style="max-width: 1% !important;"> <?php echo $i; ?> </th>
                                    <?php }}; ?>
                                </tr>
                            </thead>
                            <tbody id="turno1">
                                <?php foreach($escal['itemEscala'] AS $itens){ ?>
                                    <tr>
                                        <td><?php echo $itens['re']; ?></td>
                                        <td class="nomeEscala">
                                            <div class="cargoHover" title="Clique 2 vezes para ocultar o cargo.">
                                                <?php echo $itens['funcao']; ?>
                                            </div>
                                            <?php echo $itens['nome']; ?>
                                            <i class="fas fa-address-card verCargo" title="Ver Cargo"></i>
                                        </td>
                                        <td><?php echo $itens['TURNO']; ?></td>

                                        <?php for($i=1;$i <= count($daysMon); $i++){ 
                                            
                                            if($itens['t'.$i] == 0){
                                                $classeItenEscala = 'isDiaTrabalho';
                                                $titleEscala = 'Dia de Trabalho';
                                            }

                                            if($itens['t'.$i] == 1){
                                                $classeItenEscala = 'isFolga';
                                                $titleEscala = 'Folga';
                                            }
            
                                            if($itens['t'.$i] == 2){
                                                $classeItenEscala = 'isAf';
                                                $titleEscala = 'Afastamento';
                                            }
            
                                            if($itens['t'.$i] == 3){
                                                $classeItenEscala = 'isFe';
                                                $titleEscala = 'Férias';
                                            }
                                            
                                            if($itens['t'.$i] == 4){
                                                $classeItenEscala = 'isTurno';
                                                $titleEscala = 'Turno';
                                            }
                                            ?>
                                            <td class="<?php echo $classeItenEscala;?>" title="<?php echo $titleEscala; ?>">
                                        <?php }; ?>
                                    <tr>
                                <?php }; ?>
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

            <hr>
        </div>
        <div class="card-create-footer">
            <div class="row d-flex justify-content-end">
                
                <?php if( $escal['escala']->efetivado == 2 ){ ?>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <span class="btn btn-success w-100" onclick="sendResponseRh(1)">Aprovar</span>
                    </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <span class="btn btn-danger w-100" onclick="sendResponseRh(2)">Recusar</span>
                    </div> 
                <?php } else { ?>
                    <!-- <a title="Imprimir Para o Restaurante" href="/rh/printRes?id=<?php echo $escal['escala']->id ?>" class="btn btn-warning" target="_blank">Imprimir</a> -->
                <?php }; ?>
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <a href="/rh/" class="btn btn-secondary w-100">Fechar</a>
                </div>
                
            </div>
        </div>
    </div>
    <hr>
   
    <!-- Modal -->
    <div class="modal fade" id="modalMotive" tabindex="-1" role="dialog" aria-labelledby="modalMotiveLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalMotiveLabel">Motivo:</h5> 
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p>*Digite o motivo para recusar.</p>
            <textarea id="motivetxt" rows="4" cols="45" style="height: 99px; width:100%"> </textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="recusarMotivo()">Enviar</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        </div>
        </div>
    </div>
    </div>

</main>