<main class="py-4">
    <div class="personContainer">
        <form method="POST" action="/versionAppFace/update" accept-charset="UTF-8" class="form-horizontal">
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
                        <input class="form-control" name="vAndroid" type="number" min="1" step="1" value="<?php echo $param['version_android_face'] ?>" id="vAndroid">
                    </div> 
                    <div class="form-group col-md-6 col-sm-6 col-xs-12">
                        <label for="urlAndroid" class="form-label">URL Android:</label>
                        <input class="form-control" name="urlAndroid" type="text" value="<?php echo $param['url_android_face'] ?>" id="urlAndroid">
                    </div>
                    <div class="form-group col-md-2 col-sm-2 col-xs-12">
                        <label for="vAndroid" class="form-label">
                            Tempo de Envio:
                            <i title="Tempo(Em minutos) para os aplicativos tentarem enviar as detecções para o <?php echo APP_NAME;?>" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                        </label>
                        <input class="form-control" name="time_send_infos_face" type="number" min="2" step="1" max="15" value="<?php echo $param['time_send_infos_face'] ?>" id="time_send_infos_face">
                    </div> 
                </div>
            </div> 
            <hr> 
            <div class="card-create-body">
                <div class="row">
                    <div class="form-group col-md-8 col-sm-8 col-xs-12">
                        <label for="msgApp" class="form-label">Mensagem Nova Versão:</label>
                        <input class="form-control" name="msgApp" type="text" value="<?php echo $param['message_app_face'] ?>" id="msgApp">
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
        setActiveMenu('/versionAppFace');
    }
</script>