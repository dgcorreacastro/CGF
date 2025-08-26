<main class="py-4">
    <div class="personContainer">

        <div class="card-body">
            <form method="POST" action="/terms/update" accept-charset="UTF-8" class="form-horizontal">
                
                <div class="card-create-header">
                    <h2 class="pageTitle"> <b class="h4">&#10148; <?php echo $title;?></b></h2>
                </div>
                <hr>
                <div class="card-create-body">
                    <div class="row">
                        <div class="col-sm-12 col-xs-12">
                            <input type="hidden" name="id" value="<?php echo $id; ?>" />
                            <label for="conteudo" class="control-label">Conteúdo:</label>
                            <textarea
                                id="txtEditor"
                                class="form-control" 
                                name="conteudo"
                            >
                            <?php echo $content; ?>
                            </textarea>
                        </div> 
                    </div>
                </div> 
                <hr>
                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="/terms" class="btn btn-danger w-100">Fechar</a>
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
</div>