<main class="py-4">
    
<div class="personContainer">
    <div class="card-body">
        <form method="POST" action="/usuarios/updateParameter" accept-charset="UTF-8" class="form-horizontal">
            
        <div class="card-create-header">
            <h2 class="pageTitle"> <b class="h4">&#10148; E-mails para notificação</b></h2>
        </div>
        <hr>
        <div class="card-create-body">
            <div class="row">

                <div class="col-sm-12 col-xs-12">
                    <input type="hidden" name="idUser" value="<?php echo $idUser; ?>">
                    <label for="emails_notify" class="control-label">Emails para notificação: </label> </br>
                    <p style="font-size:13px;margin-top:-10px"><i>Separe os emails por ponto e vírgula (;), para que possa ser enviado para mais de um email.</i></p>

                    <textarea name="emails_notify" rows="5" style="width: 100%"><?php echo isset($parameter->emails_notify) ? trim( $parameter->emails_notify) : ''; ?> </textarea>
                </div>
        
                <!-- <div class="form-group col-md-4 col-sm-4 col-xs-12">
                    <label for="ranger_minutes" class="form-label">Tempo Ranger Dash (Minutos):</label>
                    <input class="form-control" min="0" name="ranger_minutes" 
                        value="<?php echo isset($parameter->ranger_minutes) ? $parameter->ranger_minutes : '' ?>" 
                        type="number"
                    >
                </div> -->

            </div>
        </div>
        <hr>
        <div class="card-create-footer">
            <div class="row d-flex justify-content-end">
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <a href="/usuarios/" class="btn btn-danger w-100">Fechar</a>
                </div>
                <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                    <button class="btn btn-success w-100">Salvar</button>
                </div>
            </div>
        </div>
        </form>
    </div>
    
</div>

</main>