<main class="py-4">
    
    <div class="personContainer">

        <div class="card-body">
            <form method="POST" accept-charset="UTF-8" class="form-horizontal">
                

                <div class="card-create-header">
                    <h2 class="pageTitle"></h2>
                </div>

                <hr>

                <div class="card-create-body">

                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <input type="hidden" name="idUser" value="<?php echo $_SESSION["cLogin"]; ?>">
                            <label for="name" class="control-label">Nome:</label>
                            <input class="form-control" name="name" type="text" id="name" required value="<?php echo $userEdt['name']; ?>">
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <label for="email" class="control-label">Email:</label>
                            <input class="form-control" name="email" type="email" id="email" required value="<?php echo $userEdt['email']; ?>">
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <label for="passwordAtual" class="control-label">Senha Atual:</label>
                            <div class="userCadPass" style="--btnNumber: 1">
                                <input class="form-control" name="passwordAtual" type="password" id="passwordAtual">
                                <i onclick="showHidePass(this, '#passwordAtual')" class="fas fa-eye" title="Mostar Senha"></i>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <label for="password" class="control-label">Nova Senha:</label>
                            <div class="userCadPass" style="--btnNumber: 3">
                                <input class="form-control" name="password" type="password" id="password">
                                <i onclick="showHidePass(this, '#password')" class="fas fa-eye" title="Mostar Senha"></i>
                                <i onclick="genPassword(10, '#password')" class="fas fa-sync-alt" title="Atualizar"></i>
                                <i onclick="copyPass('password')" class="fas fa-copy" title="Copiar Senha"></i>
                            </div>
                        </div>

                    </div>

                </div>

                <hr>
                <div class="card-create-footer">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <a href="/" class="btn btn-danger w-100">Fechar</a>
                        </div>
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <button onclick="atualizarUsuarioDados(event)" class="btn btn-success w-100">Salvar</button>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
       
    </div>
</main>
