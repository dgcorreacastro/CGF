<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h2 class="darkcyan"><i class="fas fa-cogs darkcyan"></i> Listagem Funções</h2>
            <table id="table" data-toggle="table" class="table table-bordered">
                <thead>
                    <tr class="headerTr">
                        <th style="min-width: 100% !important; width: 100%;">Descrição</th>
                        <th style="min-width: 150px !important; width: 100%;">Grupo</th>
                        <th style="min-width: 70px !important; width: 150px"> 
                            <a title="Novo Cadastro" href="/funcoes/create" class="btn btn-success">
                            <i class="fas fa-plus"></i></a>
                        </th>
                    </tr>
                </thead>
                <tbody id="listEscala">
                <?php foreach($ret AS $r): ?>
                    <tr id="<?php echo $r->id ?>">
                        <td><?php echo $r->descricao; ?></td> 
                        <td><?php echo $r->grupo; ?></td> 
                        <td class="text-center">
                            <a title="Editar" href="/funcoes/edit?id=<?php echo $r->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                            <button title="Excluir" class="btn btn-danger editIcon" onclick="confirmDelet('Função', false, '/funcoes/delete', <?php echo $r->id ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody> 
            </table>
        </div>
    </div>
</main>
</div>