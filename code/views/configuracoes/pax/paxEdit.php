<main class="py-4">

    <?php if (isset( $infos['ret'] ) ) {  ?>
        <?php if (isset( $infos['ret']['success'] ) ) {  ?>
            <div style="margin: 20px 10%;" class="alert alert-success" role="alert">
            <?php echo $infos['ret']['msg'];  ?>
            </div>
        <?php } else if (isset( $infos['ret']['error'] ) ) {  ?>
            <div style="margin: 20px 10%;" class="alert alert-danger" role="alert">
                <?php echo $infos['ret']['msg'];  ?>
            </div>
        <?php 
            }
            unset($infos['ret']['success']);
            unset($infos['ret']['msg']);
            unset($infos['ret']['error']);
        }  ?>
    <div class="personContainer">
        <form method="POST" action="/configuracoes/atualizarPax" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
        <div class="card-body">
            <div class="card-create-header">
                <h2><i class="fas fa-table mnon" aria-hidden="true"></i> Passageiros | Cliente - <?php echo $infos['g']['name']; ?></h2>
            </div>
            <hr>
            <div class="card-create-body">
                <div class="row">
                    <div class="col-sm-3 col-xs-12">
                        <input type="hidden" name="id" value="<?php echo $infos['id']; ?>" />
                       
                        <div class="form-group">
                            <label>Planilha Cliente</label>
                            <label class="btn_upload" for="file">
                                <text>Escolha um Arquivo</text>
                                <div class="uploadProgressTxt"></div>
                            </label>
                            <input id="file" type="file" style="display:none;" name="file" extOk="xls,xlsx,csv"/>
                        </div>
                    </div> 
                    <div class="col-sm-3 col-xs-12">
                        <input type="hidden" name="id" value="<?php echo $infos['id']; ?>" />
                       
                        <div class="form-group">
                            <label>Modelo Planilha</label> <br>
                            <span title="BAIXAR MODELO" class="btn_download" url="/assets/files/MODELOCGFPAX.xlsx" filename="MODELOCGFPAX">
                                BAIXAR MODELO
                                <div class="downloadProgress"></div>
                                <div class="downloadProgressTxt"></div>
                            </span>
                        </div>
                    </div> 
                </div>
            </div> 
            <hr>
            <div class="card-create-footer">
                <div class="row">
                    <div class="col col-md-12 text-right">
                        <button class="btn btn-success">Salvar</button>
                        <a href="/configuracoes/tablePax" class="btn btn-secondary">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <hr>
        <h4>Listagem Passageiros do Cliente</h4>
        <table id="table" data-toggle="table" class="table table-bordered">
            <thead>
                <tr class="headerTr">
                    <th style="min-width: 80% !important; width: 80%;">Nome</th>
                    <th style="width: 200px">Cartão</th>
                    <th style="width: 200px">Matrícula</th>
                </tr>
            </thead>
            <body>
                <?php if(isset($infos['g']['pax'])): ?>
                <?php foreach($infos['g']['pax'] as $px): ?>
                <tr>
                    <td><?php echo utf8_decode($px['NamePax']); ?></td>
                    <td align="center"> <?php echo $px['CodCartao']; ?> </td>
                    <td align="center"> <?php echo $px['Matricula']; ?> </td>
                </tr>
            <?php endforeach; endif; ?>
            </thead>
            </body> 
        </table>
    </div>
</main>
<script type="text/javascript">
    window.onload = function(e){ 
        setActiveMenu('/configuracoes/tablePax');
    }
</script>
</div>