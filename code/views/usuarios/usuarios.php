<main class="py-4">

    <div class="personContainer">

        <div class="card-body">

            <div class="card-create-header">
                <h2 class="pageTitle"></h2>
                <div class="filterRelResultContainer show">
                    <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar os usuários..."/>
                </div>
            </div>

            <hr>

            <div class="card-create-body" style="display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                    <thead>
                        <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                            <th scope="col">Nome</th>
                            <th scope="col">Email</th>
                            <th scope="col">Situação</th>
                            <th scope="col" class="text-center">
                                <a title="Novo Cadastro" href="/usuarios/create" class="btn btn-success"><i class="fas fa-plus"></i></a>
                            </th>
                            <th scope="col">
                                <button title="Selecionar Todos" class="btn btn-info btn-sm" onclick="setAltDados()" id="btnAltDados">Alterar Dados</button>
                                <input type="hidden" value="0" id="altDadosTodos"/>
                            </th>
                        </tr>
                    </thead>
                </table>
                <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                    <tbody>
                        <?php foreach($users as $urs): ?>
                            <tr class="toMark" id="<?php echo $urs['id'] ?>">
                                <td scope="col" id="userName-<?php echo $urs['id'] ?>" style="width: 550px">
                                    <?php echo (preg_match('!!u', utf8_decode($urs['name']))) ? utf8_decode($urs['name']) : $urs['name']; ?>
                                    <?php 
                                        if($urs['type'] == 1)
                                        echo '<i title="Administrador" class="fas fa-star ml-2" style="font-size: 20px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>';

                                        if($urs['type'] == 2)
                                        echo '<i title="Usuário Comum" class="fas fa-user ml-2" style="font-size: 20px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>';

                                        if($urs['type'] == 3)
                                        echo '<i title="Monitoramento" class="fas fa-broadcast-tower ml-2" style="font-size: 20px; color: #ffc107; text-shadow: -1px 2px 0px rgb(0 0 0 / 30%);"></i>';
                                    ?>
                                </td>
                                <td scope="col" style="width: 280px"><?php echo utf8_encode($urs['email']); ?></td>
                                <td scope="col" style="width: 100px"><?php echo ($urs['ativo'] == 1 ? "Ativo" : "Inativo"); ?> </td>
                                <td scope="col" style="width: 120px">
                                    
                                    <a title="Editar" href="/usuarios/editar?id=<?php echo $urs['id'] ?>" class="btn btn-primary mx-2 editIcon"><i class="fas fa-edit"></i></a>
                                    
                                    <button title="Excluir" class="btn btn-danger mx-2 editIcon" onclick="confirmDelet('Usuário', '<?php echo (preg_match('!!u', utf8_decode($urs['name']))) ? utf8_decode($urs['name']) : $urs['name']; ?>', '/usuarios/deletar', <?php echo $urs['id'] ?>)"><i class="fas fa-trash"></i></button>
                                    
                                </td>
                                <td scope="col" style="width: 140px">
                                    <?php if($urs['type'] != 3):?>
                                    <label class="switch mb-0" style="transform: scale(.8);">
                                        <input class="setAltDadosSingle" type="checkbox" onchange="setAltDadosSingle(this, <?php echo $urs['id'] ?>)" value="<?php echo $urs['id'] ?>" <?php echo $urs['alterar_dados'] == 1 ? 'checked':'' ?> name="alterarDados[]">
                                        <span class="slider round"></span>
                                    </label>
                                    <?php endif;?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="wrapper1">
                    <div class="div1"></div>
                </div>
                <div class="wrapper1after"></div>
            </div>
      
        </div>

    </div>

</main>
<script type="text/javascript">
    window.onload = function(e){ 
        checkAllDados();
    }
</script>