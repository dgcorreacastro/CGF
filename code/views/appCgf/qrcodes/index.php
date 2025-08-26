<main class="py-4" <?php echo $_SESSION['cType'] == 3 ? 'style="width: 100% !important;"' : ''; ?>>
    <div class="personContainer">
        
        <div class="card-body">
            
            <div class="card-create-header">
                <?php if($_SESSION['cType'] != 3){?>
                    <h2 class="pageTitle"></h2>
                <?php }else{?>
                    <h2><i class="fa fa-qrcode "></i> <?php echo APP_NAME;?> PASS <b class="h4">&#10148; QrCodes</b></h2>
                <?php }?>
                <div class="filterRelResultContainer">
                    <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar os códigos..."/>
                </div>
            </div>
            <hr>
            <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                    <thead>
                        <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                            <th scope="col">Cliente</th>
                            <th scope="col">QRCode</th>
                            <th scope="col">Código</th>
                            <?php if($_SESSION['cType'] != 3){?>
                                <th scope="col">
                                    Instalações <?php echo APP_NAME;?> PASS <i title="Quantidade de instalações <?php echo $mes;?>" style="color:yellow; font-size:15px" class="fas fa-question-circle"></i>
                                </th>
                                <th scope="col">
                                    Acessos <?php echo APP_NAME;?> PASS <i title="Quantidade de acessos <?php echo $mes;?>" style="color:yellow; font-size:15px" class="fas fa-question-circle"></i>
                                </th>
                                <th scope="col"> 
                                    <a title="Novo Cadastro" href="/app/create" class="btn btn-success">
                                    <i class="fas fa-plus"></i></a>
                                </th>
                            <?php }?>
                        </tr>
                    </thead>
                </table>
                <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                    <tbody id="bodyQrCodes">
                        <?php foreach($apps AS $r): ?>
                            <tr class="toMark" id="<?php echo $r->id ?>">
                                <td scope="col" style="width: 420px !important;"><?php echo $r->cliente; ?></td> 
                                <td scope="col" style="width: 80px !important;">
                                    <a title="Ver QRCode" href="/app/printqrcode?qr=<?php echo $r->qrcode ?>&nomegr=<?php echo $r->cliente ?>" class="btn btn-warning editIcon" target="_blank"><i class="fas fa-qrcode"></i></a> 
                                </td> 
                                <td scope="col" style="width: 160px !important;"><?php echo $r->codigo; ?></td> 
                                <?php if($_SESSION['cType'] != 3){?>
                                    <td scope="col" style="width: 180px !important;">
                                        <div class="d-flex flex-row flex-nowrap justify-content-between px-2 align-items-center">
                                            <b><?php echo $r->instalacoes; ?></b>
                                            <form action="/app/statistics" method="get">
                                                <input type="hidden" name="groupId" value="<?php echo $r->groupId ?>">
                                                <input type="hidden" name="codigo" value="<?php echo $r->codigo ?>">
                                                <input type="hidden" name="qrcode" value="<?php echo $r->qrcode ?>"> 
                                                <input type="hidden" name="nomegr" value="<?php echo $r->cliente ?>">
                                                <button title="Ver mais" class="btn btn-warning ml-2"><i class="fas fa-eye"></i></button>                    
                                            </form>
                                        </div>
                                    </td> 
                                    <td scope="col" style="width: 180px !important;">
                                        <div class="d-flex flex-row flex-nowrap justify-content-between px-2 align-items-center">
                                            <b><?php echo $r->acessos; ?></b>
                                            <form action="/app/statistics" method="get">
                                                <input type="hidden" name="groupId" value="<?php echo $r->groupId ?>"> 
                                                <input type="hidden" name="nomegr" value="<?php echo $r->cliente ?>">
                                                <button title="Ver mais" class="btn btn-warning ml-2"><i class="fas fa-eye"></i></button>                    
                                            </form>
                                        </div>
                                    </td> 
                                    <td scope="col" style="width: 140px !important;">
                                        
                                        <a title="Editar" href="/app/edit?id=<?php echo $r->id ?>" class="btn btn-primary mx-2 editIcon"><i class="fas fa-edit"></i></a>                                            
                                        <button title="Excluir" class="btn btn-danger mx-2 editIcon" onclick="confirmDelet('Link APP', '<?php echo $r->cliente; ?>', '/app/delete', <?php echo $r->id ?>)"><i class="fas fa-trash"></i></button>
                                        
                                    </td>
                                <?php }?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="wrapper1">
                    <div class="div1"></div>
                </div>
                <div class="wrapper1after"></div>
            </div>
            <?php if($_SESSION['cType'] == 3){?>
                <hr>
                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="/" class="btn btn-danger btn-ls w-100">VOLTAR</a>  
                        </div>
                    </div>  
                </div>
            <?php }?> 
            
        </div>

    </div>
</main>

<script type="text/javascript">
    window.onload = function(e){ 
        setActiveMenu('/app/qrcodes');
        checkShowDownload('bodyQrCodes'); 
    }
</script>