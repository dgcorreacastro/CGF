<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/versionApp/update" accept-charset="UTF-8" class="form-horizontal">
        <div class="card-body">
            <div class="card-create-header">
                <h2 class="pageTitle"></h2>
            </div>
            <hr>
            <div class="card-create-body">
                <h4><i class="fab fa-android" aria-hidden="true"></i> Android</h4>
                <div class="row">
                    <div class="form-group col-md-2 col-sm-2 col-xs-12">
                        <label for="vAndroid" class="form-label">Versão Android:</label>
                        <input class="form-control" name="vAndroid" type="number" min="1" step="1" value="<?php echo $param['version_android'] ?>" id="vAndroid">
                    </div> 
                    <div class="form-group col-md-6 col-sm-6 col-xs-12">
                        <label for="urlAndroid" class="form-label">URL Android:</label>
                        <input class="form-control" name="urlAndroid" type="text" value="<?php echo $param['url_android'] ?>" id="urlAndroid">
                    </div> 
                </div>
            </div> 
            <hr>
            <div class="card-create-body">
                <h4><i class="fab fa-apple" aria-hidden="true"></i> IOS</h4>
                <div class="row">
                    <div class="form-group col-md-2 col-sm-2 col-xs-12">
                        <label for="vIos" class="form-label">Versão IOS:</label>
                        <input class="form-control" name="vIos" type="number" min="1" step="1" value="<?php echo $param['version_ios'] ?>" id="vIos">
                    </div> 
                    <div class="form-group col-md-6 col-sm-6 col-xs-12">
                        <label for="urlIos" class="form-label">URL IOS:</label>
                        <input class="form-control" name="urlIos" type="text" value="<?php echo $param['url_ios'] ?>" id="urlIos">
                    </div> 
                </div>
            </div> 
            <hr>
            <div class="card-create-body">
                <div class="row">
                    <div class="form-group col-md-8 col-sm-8 col-xs-12">
                        <label for="msgApp" class="form-label">Mensagem Nova Versão:</label>
                        <input class="form-control" name="msgApp" type="text" value="<?php echo $param['message_app'] ?>" id="msgApp">
                    </div> 
                </div>
            </div> 
            <hr>

            <div class="card-create-body">
                <div class="row justify-content-center">
                    <div class="form-group switch-group mb-0">
                        <label class="form-label mb-0">Permitir Escanear Código do Cartão/Chaveiro:</label>
                        <label class="switch mb-0">
                            <input type="checkbox" id="readCardApp" <?php echo $param['readCardApp'] == 1 ? 'checked':'' ?> name="readCardApp">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div> 
            <hr>
            <div class="card-create-footer">
                <div class="row d-flex justify-content-end">
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button class="btn btn-success w-100">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</main>
<script type="text/javascript">
    window.onload = function(e){ 
        setActiveMenu('/versionApp');
    }
</script>