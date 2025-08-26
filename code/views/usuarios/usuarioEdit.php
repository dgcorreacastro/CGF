<main class="py-4">
    
    <div class="personContainer">
        <div class="card-body">
            <form method="POST" accept-charset="UTF-8" class="form-horizontal">
                

                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; Editar</b></h2>
                </div>

                <hr>

                <div class="card-create-body">

                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <input type="hidden" name="idUser" value="<?php echo $userEdt['id']; ?>">
                            <label for="name" class="control-label">Nome:</label>
                            <input class="form-control" name="name" type="text" id="name" required value="<?php echo $userEdt['name']; ?>">
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <label for="email" class="control-label">Email:</label>
                            <input class="form-control" name="email" type="email" id="email" required value="<?php echo $userEdt['email']; ?>">
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <label for="password" class="control-label">Senha:</label>
                            <div class="userCadPass" style="--btnNumber: 3">
                                <input class="form-control" name="password" type="password" id="password">
                                <i onclick="showHidePass(this, '#password')" class="fas fa-eye" title="Mostar Senha"></i>
                                <i onclick="genPassword(10, '#password')" class="fas fa-sync-alt" title="Atualizar"></i>
                                <i onclick="copyPass('password')" class="fas fa-copy" title="Copiar Senha"></i>
                            </div>
                        </div>

                        <div class="col-sm-3 col-xs-12">
                            <label for="type" class="control-label">Tipo Usu&aacute;rio:</label>
                            <select class="form-control filtroSelect2" id="type" name="type" onchange="typeUserCheck(this.value)">
                                <option value="2" <?php echo ($userEdt['type'] == 2 ? 'selected' : ''); ?> >COMUM</option>
                                <option value="1" <?php echo ($userEdt['type'] == 1 ? 'selected' : ''); ?> >ADMIN</option>
                                <option value="3" <?php echo ($userEdt['type'] == 3 ? 'selected' : ''); ?> >MONITORAMENTO</option>
                            </select>
                        </div>

                        <div class="col-sm-3 col-xs-12">
                            <label for="ativo" class="control-label">Situa&ccedil;&atilde;o:</label>
                            <select class="form-control filtroSelect2" id="ativo" name="ativo">
                                <option value="1" <?php echo ($userEdt['ativo'] == 1 ? 'selected' : ''); ?>>Ativo</option>
                                <option value="0" <?php echo ($userEdt['ativo'] == 0 ? 'selected' : ''); ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="col-sm-6 col-xs-12 relacoes <?php echo ($userEdt['type'] == 1 OR $userEdt['type'] == 3 )? 'dn' : '' ?>">
                            <label for="groupUserID" class="control-label">Grupo Usuário:</label>
                            <select class="form-control" id="groupUserID" name="groupUserID">
                                <option value="0">Selecione</option>
                                <?php foreach($gruposUser AS $gru): ?>
                                    <option 
                                        value="<?php echo $gru['id']; ?>"
                                        <?php echo ($userEdt['groupUserID'] ==  $gru['id'] ? 'selected' : ''); ?>
                                    >
                                        <?php echo $gru['NOME']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-6 col-xs-12 userPermissions <?php echo ($userEdt['type'] == 1 OR $userEdt['type'] == 3 )? 'dn' : '' ?>">
                            <div class="holdFiltroSelect">
                                <label class="form-label">Menus Permitidos:</label>
                                <span class="filtroSelect" title="SELECIONAR MENUS PERMITIDOS" originaltxt="SELECIONAR MENUS PERMITIDOS" checkboxesFiltro="menuUserSel"><i class="fa fa-bars" aria-hidden="true"></i> <texto>SELECIONAR MENUS PERMITIDOS</texto></span>
                            </div>
                        </div>

                        <div class="col-sm-12 col-xs-12 userPermissions <?php echo ($userEdt['type'] == 1 OR $userEdt['type'] == 3 )? 'dn' : '' ?>">
                            <label class="form-label">Menus Selecionados:</label>
                            <div class="checkboxesFiltroSelecionados checksMenus" id="menuUserSel">
                            </div>
                        </div>

                        <div class="col-sm-12 col-xs-12 p-4 relacoes <?php echo ($userEdt['type'] == 1 OR $userEdt['type'] == 3 )? 'dn' : '' ?>">
                            <hr>
                            <h4>Usuário x Linha</h4>
                            <hr>
                            <div class="row max-0">
                                <div class="col-sm-5 col-xs-12 px-0">
                                    <select class="form-control" id="groupUserIDLinhaOut" onChange="selectUserLinhaFilter()" name="groupUserIDLinhaOut" style="min-width:100%;">
                                        <option value="0">Todos os Grupos</option>
                                        <?php foreach($gruposUser AS $gru): ?>
                                            <option value="<?php echo $gru['id']; ?>" idorigin="<?php echo $gru['ID_ORIGIN']; ?>"><?php echo $gru['NOME']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-5 col-xs-12 px-0"></div>
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                <select id="selectLinhaUser" multiple>
                                    <?php foreach($linhas AS $key => $li): ?>
                                        <?php if($key == 0): ?>
                                            <option disabled class="nomeGrupoSelect" grlinhaidnome="<?php echo $li['GRUPO_LINHA_ID']; ?>"><?php echo $li['nomeGrupo']; ?></option>
                                        <?php endif; ?>
                                        <?php if($key != 0 && $linhas[$key-1]['GRUPO_LINHA_ID'] != $li['GRUPO_LINHA_ID']): ?>
                                            <option disabled class="nomeGrupoSelect" grlinhaidnome="<?php echo $li['GRUPO_LINHA_ID']; ?>"><?php echo $li['nomeGrupo']; ?></option>
                                        <?php endif; ?>
                                        <option grlinhaid="<?php echo $li['GRUPO_LINHA_ID']; ?>" nome="<?php echo $li['nomeGrupo']." - ".$li['NOME']; ?>" value="<?php echo $li['id']; ?>"><?php echo $li['CODIGO_INTEGRACAO']; ?> - <?php echo $li['PREFIXO'];?> - <?php echo $li['NOME']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                                <div class="col-sm-2 col-xs-12 permissaoheig setasUser">
                                <p title="Conceder Permissão"><i onclick="concederPermissao('LIN')" class="fa fa-angle-double-right" style="font-size:36px"></i></p> <br>
                                    <p title="Retirar Permissão"><i onclick="removerPermissao('LIN')" class="fa fa-angle-double-left" style="font-size:36px"></i></p>
                                </div>
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                    <input type="hidden" id="idsLinhaPermission" name="idsLinhaPermission" value="<?php echo $linPerm; ?>" />
                                    <select id="selectLinhaUserPerm" multiple>
                                    <?php foreach($linhasIn AS $key => $lin): ?>
                                        <?php if($key == 0): ?>
                                            <option disabled class="nomeGrupoSelect" grlinhaidnome="<?php echo $lin['GRUPO_LINHA_ID']; ?>"><?php echo $lin['nomeGrupo']; ?></option>
                                        <?php endif; ?>
                                        <?php if($key != 0 && $linhasIn[$key-1]['GRUPO_LINHA_ID'] != $lin['GRUPO_LINHA_ID']): ?>
                                            <option disabled class="nomeGrupoSelect" grlinhaidnome="<?php echo $lin['GRUPO_LINHA_ID']; ?>"><?php echo $lin['nomeGrupo']; ?></option>
                                        <?php endif; ?>
                                        <option grlinhaid="<?php echo $lin['GRUPO_LINHA_ID']; ?>" value="<?php echo $lin['id']; ?>" nome="<?php echo $lin['nomeGrupo']." - ".$lin['NOME']; ?>"><?php echo $lin['CODIGO_INTEGRACAO']; ?> - <?php echo $lin['PREFIXO'];?> - <?php echo $lin['NOME']; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="hidden" id="orginLinhas" value="<?php echo $linPerm; ?>" />
                            </div>

                            <hr>
                            <h4>Usuário x Carro</h4>
                            <hr>
                            <div class="row">
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                    <select id="selectCarUser" multiple>
                                        <?php foreach($carros AS $car): ?>
                                            <option value="<?php echo $car['id']; ?>" nome="<?php echo $car['NOME']; ?>"><?php echo $car['TIPOVEICULO']; ?> - <?php echo $car['MARCA']; ?> - <?php echo $car['MODELO']; ?> - <?php echo $car['NOME']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-sm-2 col-xs-12 permissaoheig setasUser">
                                    <p title="Conceder Permissão"><i onclick="concederPermissao('CAR')" class="fa fa-angle-double-right" style="font-size:36px"></i></p> <br>
                                    <p title="Retirar Permissão"><i onclick="removerPermissao('CAR')" class="fa fa-angle-double-left" style="font-size:36px"></i></p>
                                </div>
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                    <input type="hidden" id="idsCardPermission" name="idsCardPermission" value="<?php echo $carPerm; ?>" />
                                    <select id="selectCarUserPerm" multiple>
                                        <?php foreach($carrosIn AS $carin): ?>
                                            <option value="<?php echo $carin['id']; ?>" nome="<?php echo $carin['NOME']; ?>"><?php echo $carin['TIPOVEICULO']; ?> - <?php echo $carin['MARCA']; ?> - <?php echo $carin['MODELO']; ?> - <?php echo $carin['NOME']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="hidden" id="orginCarros" value="<?php echo $carPerm; ?>"/>
                            </div>

                            <hr>
                            <h4>Usuário x Grupo</h4>
                            <hr>
                            <div class="row">
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                <select id="selectGrupoUser" multiple>
                                <?php foreach($grupos AS $gr): ?>
                                    <option value="<?php echo $gr['id']; ?>" nome="<?php echo $gr['NOME']; ?>"><?php echo $gr['NOME']; ?></option>
                                <?php endforeach; ?>
                                </select>
                                </div>
                                <div class="col-sm-2 col-xs-12 permissaoheig setasUser">
                                <p title="Conceder Permissão"><i onclick="concederPermissao('GRUPO')" class="fa fa-angle-double-right" style="font-size:36px"></i></p> <br>
                                    <p title="Retirar Permissão"><i onclick="removerPermissao('GRUPO')" class="fa fa-angle-double-left" style="font-size:36px"></i></p>
                                </div>
                                <div class="col-sm-5 col-xs-12 permissaoheig ctPerm">
                                    <input type="hidden" id="idsGrupoPermission" name="idsGrupoPermission" value="<?php echo $grPerm; ?>" />
                                    <select id="selectGrupoUserPerm" multiple>
                                    <?php foreach($gruposIn AS $grIn): ?>
                                        <option value="<?php echo $grIn['id']; ?>" nome="<?php echo $grIn['NOME']; ?>"><?php echo $grIn['NOME']; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="hidden" id="orginGrupos" value="<?php echo $grPerm; ?>" />
                            </div>         

                        </div>
                    
                    </div>

                </div>

                <hr>
                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="/usuarios/" class="btn btn-danger w-100">Fechar</a>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button onclick="atualizarUsuario(event)" class="btn btn-success w-100">Salvar</button>
                        </div>
                    </div>
                </div>
                
                <div class="checkboxesFiltro" id="menuUserSel">
                    <span class="titleCheckboxesFiltro" title="SELECIONAR MENUS"><i class="fa fa-users" aria-hidden="true"></i> SELECIONAR MENUS</span>
                    <i class="fa fa-window-close fechaCheckboxesFiltro" aria-hidden="true"></i>
                    <div class="buscaFiltro">
                        <input class="form-control buscaFiltroInput" type="text" placeholder="Digite aqui para filtrar..."/>
                    </div>
                    <div class="checkboxesFiltroLista">
                        <input type="checkbox" class="checkFiltro" id="m-1" value="1" icon="fa fa-home" checked disabled>
                        <label for="m-1">Dashboard</label>
                        <?php 
                        if (isset($menusSys)){
                        foreach($menusSys AS $me): 
                            if($me['link'] != "#" && $me['id'] > 1){
                        ?>
                        <input type="checkbox" <?php echo in_array($me['id'], $menuUser) ? 'checked' : ''  ?> class="checkFiltro" id="m-<?php echo $me['id']; ?>" value="<?php echo $me['id']; ?>" icon="<?php echo $me['icon']; ?>" name="menusUser[]" />
                        <label for="m-<?php echo $me['id']; ?>"><?php echo $me['descrip']; ?></label>
                        <?php } endforeach; }?>
                    </div>
                    <div class="checkboxesFiltroBts">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="limpaCheckFiltro" class="btn btn-warning w-100">Limpar</button>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="todosCheckFiltro" class="btn btn-primary w-100" type="button">Todos</button>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button id="okCheckFiltro" class="btn btn-success w-100" type="button">OK</button>
                        </div>
                    </div>
                    <input type="hidden" id="orginMenus" />
                </div>
            </form>
        </div>
        
    </div>
</main>
<script type="text/javascript">
    window.onload = function(e){ 
        checkFiltrosCount($('.checkboxesFiltro[id=menuUserSel]'));
        checkUserUpdates('menus');
    }
</script>