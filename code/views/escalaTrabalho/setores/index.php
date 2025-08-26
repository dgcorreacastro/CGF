<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
            <h4>Lista Setores</h4>
            <table id="table" data-toggle="table" class="table table-bordered">
                <thead>
                    <tr class="headerTr">
                        <th style="min-width: 100% !important; width: 100%;">Descrição</th>
                        <th style="min-width: 70px !important; width: 150px"> 
                            <a title="Novo Cadastro" href="/setores/create" class="btn btn-success">
                            <i class="fas fa-plus"></i></a>
                        </th>
                    </tr>
                </thead>
                <body>
                <?php foreach($ret AS $r): ?>
                    <tr id="<?php echo $r->id ?>">
                        <td><?php echo $r->descricao; ?></td> 
                        <td class="text-center">
                            <a title="Editar" href="/setores/edit?id=<?php echo $r->id ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                            <button title="Excluir" class="btn btn-danger editIcon" onclick="confirmDelet('Setor', false, '/setores/delete', <?php echo $r->id ?>)"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </body> 
            </table>
        </div>
    </div>
</main>
</div>