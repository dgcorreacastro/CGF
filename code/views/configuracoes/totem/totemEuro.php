<main class="py-4">
    <div class="personContainer">
        
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"></h2>
                    <div class="filterRelResultContainer">
                        <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar os totens..."/>
                    </div>
                </div>
                <hr>
                <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                    <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                        <thead>
                            <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                                <th scope="col">Cliente</th>
                                <th scope="col">Link</th>
                                <th scope="col">Qtd. Acessos <i title="Quantidade de acessos dentro do mês vigente" style="color:yellow; font-size:15px" class="fas fa-question-circle"></i></th>
                                <th scope="col">
                                    <a title="Novo Cadastro" href="/configuracoes/totemCreateEuro/" class="btn btn-success"><i class="fas fa-plus"></i></a>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                        <tbody id="bodyTotem">
                            <?php foreach($totem as $tot): ?>
                                <tr class="toMark" id="<?php echo $tot['id'] ?>">
                                    <td scope="col" style="width: 280px !important;"><?php echo $tot["NOME"] ?></td>
                                    <td scope="col" style="width: 510px !important;"><?php echo utf8_encode(BASE_URLB . 'passageiro/itinerarioEurofarma/' . $tot['link']); ?></td>
                                    <td scope="col" style="width: 130px !important;">
                                        <?php if($tot["isOld"]):?>
                                            <div class="d-flex flex-row flex-nowrap justify-content-between px-2 align-items-center">
                                                <?php echo $tot['acessos']; ?>
                                                <form action="/passageiro/statisticsTotemEuro" method="get">
                                                    <input type="hidden" name="groupId" value="<?php echo $tot["grupo_linhas_id"]; ?>"> 
                                                    <input type="hidden" name="nomegr" value="<?php echo $tot["NOME"] ?>">
                                                    <button title="Ver mais" class="btn btn-warning ml-2"><i class="fas fa-eye"></i></button>                    
                                                </form>
                                            </div>
                                        <?php endif;?>
                                    </td>
                                    <td scope="col">
                                    <span title="Copiar Link" class="btn btn-success mx-2 editIcon" onclick="myFunctionCopy('textLink-<?php echo $tot['id'] ?>')" style="cursor:pointer"><i class="fa fa-copy" style="color:white"></i></span>
                                        <?php if($tot["isOld"]):?>
                                            <a title="Editar" href="/configuracoes/totemEditEuro?id=<?php echo $tot['id'] ?>" class="btn btn-primary mx-2 editIcon"><i class="fas fa-edit"></i></a>
                                            <button title="Excluir" class="btn btn-danger mx-2 editIcon" onclick="confirmDelet('Link', '<?php echo $tot['NOME'] ?>', '/configuracoes/totemDeleteEuro', <?php echo $tot['id'] ?>)"><i class="fas fa-trash"></i></button>
                                        <?php endif;?>
                                        
                                        <input class="textLinkUrl" type="text" value="<?php echo utf8_encode(BASE_URLB . 'passageiro/itinerarioEurofarma/' . $tot['link']) ?>" id="textLink-<?php echo $tot['id'] ?>">                                        
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
        checkShowDownload('bodyTotem'); 
    }
</script>