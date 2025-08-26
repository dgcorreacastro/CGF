<main class="py-4">
    
    <div class="personContainer">
        <form method="POST" action="/userEscala/store" accept-charset="UTF-8" class="form-horizontal">
            <div class="card-body">

                <div class="card-create-header">
                    <h2 class="darkcyan"><i class="fas fa-users darkcyan"></i> Cadastrar Usuário</h2>
                </div>
                <hr>

                <?php include_once('form.php'); ?>

                <hr>
            </div>
            <div class="card-create-footer">
                <div class="row d-flex justify-content-end">
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <a href="/userEscala/" class="btn btn-danger w-100">Fechar</a>
                    </div>
                    <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                        <button class="btn btn-success w-100">Salvar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr>
   
</main>