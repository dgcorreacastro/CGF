<main class="py-4">
    
    <div class="personpersonContainerEscala">
        <form id="sendFormEscala" method="POST" action="/escala/update" accept-charset="UTF-8" class="form-horizontal">
            <div class="card-body">

                <div class="card-create-header">
                    <h2 class="darkcyan"><i class="far fa-calendar-alt darkcyan"></i> Editar Escala 
                        <span id="maxFolgMes" style="font-size: 16px;float: right;margin-right: 20px;">Quantidade máxima de folgas permitidas no mês: 
                        <i id="numberFolga" style="background-color: black;padding: 5px 20px;"><?php echo $mxfm ?></i> </span>
                        <?php if( ( isset($escal['escala']) && $escal['escala']->efetivado == 4 ) ) { ?>
                            <span 
                                title="Verificar mensagem do RH" 
                                data-toggle="modal" data-target="#modalRecusa"
                                style="cursor:pointer">
                                    <i style="color:red;float: right;margin-right: 30px;" class="fas fa-exclamation-triangle"></i>
                                </span>
                        <?php }; ?>
                    </h2>
                </div>
                <hr>

                <?php include_once('form.php'); ?>

                <hr>
            </div>
            <div class="card-create-footer">
                <div class="row d-flex justify-content-end">
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <span class="btn btn-primary w-100" onclick="saveEscala(1)">Salvar</span>
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <span class="btn btn-info w-100" onclick="saveEscala(2)">Enviar RH</span> 
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <a href="/escala/" class="btn btn-danger w-100">Fechar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr>
   
    <!-- Modal -->
    <div class="modal fade" id="modalRecusa" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Motivo:</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <p><?php echo $escal['escala']->motive; ?></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        </div>
        </div>
    </div>
    </div>

</main>