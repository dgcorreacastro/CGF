<main class="py-4">
    <div class="personContainer">
        
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"> <button title="Baixar Excel" type="button" class="btn btn-success btnExcel py-2 px-3 m-1 dn" onclick="downloadRelScreen('bodyLinhas', 'Linhas')"><i class="fas fa-file-excel" style="font-size:22px;color:white"></i></button></h2>
                    <div class="filterRelResultContainer">
                        <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar as linhas..."/>
                    </div>
                </div>
                <hr>
                <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                    <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                        <thead>
                            <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                                <th scope="col">Prefixo</th>
                                <th scope="col">Sentido</th>
                                <th scope="col">Nome</th>
                            </tr>
                            <tr class="dn headExcel">
                                <th style="width: 180px;">Prefixo</th>
                                <th style="width: 150px;">Sentido</th>
                                <th style="width: 400px">Nome</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                        <tbody id="bodyLinhas">
                            <?php foreach($linhas as $gr): ?>
                                <tr class="toMark">
                                    <td scope="col" style="width: 120px !important;"><?php echo utf8_encode($gr['PREFIXO']); ?></td>
                                    <td scope="col" style="width: 90px !important;"><?php echo ($gr['SENTIDO'] == 0?"IDA":"VOLTA"); ?></td>
                                    <td scope="col" style="width: 400px !important;"><?php echo utf8_decode(utf8_encode($gr['NOME'])); ?></td>
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

<script>
  window.onload = function(e){ 
    checkShowDownload('bodyLinhas'); 
  }
</script>