<main class="py-4">

    <div class="personpersonContainerEscala">
        <form id="sendFormEscala" method="POST" action="/escala/store" accept-charset="UTF-8" class="form-horizontal">
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="darkcyan"><i class="far fa-calendar-alt darkcyan"></i> Copiar Escala
                    <span id="maxFolgMes" style="font-size: 16px;float: right;margin-right: 20px;">Quantidade máxima de folgas permitidas no mês: 
                        <i id="numberFolga" style="background-color: black;padding: 5px 20px;"><?php echo $mxfm ?></i> </span>
                </h2>
                </div>
                <hr>
                <div class="escalaCopiada" title="Clique aqui para fechar esse aviso.">
                    Atenção: Escala Copiada, verique todos os campos antes de editar.
                </div>

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
                            <a href="javascript:history.back(1);" class="btn btn-danger w-100">Cancelar</a>
                        </div>
                </div>
            </div>
        </form>
    </div>
    <hr>
   
</main>