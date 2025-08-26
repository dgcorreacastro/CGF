<main class="py-4">
    <div class="personContainer">
        <div class="card-body">
        <h2 class="pageTitle"></h2>
        <hr>
            <table id="table" data-toggle="table" class="table table-bordered">
                <thead>
                    <tr class="headerTr">
                        <th style="min-width: 80% !important; width: 80%;">Cliente</th>
                        <th style="width: 200px">Qtd Passageiro</th>
                        <th style="width: 100px;"></th>
                    </tr>
                </thead>
                <body>
                 <?php foreach($grLin as $gr): ?>
                    <tr>
                        <td><?php echo utf8_decode($gr['NOME']); ?></td>
                        <td align="center"><?php echo $gr['ttPax']; ?></td>
                        <td align="center">
                        <a href="/configuracoes/paxEdit?c=<?php echo $gr['id']; ?>" title="Editar Passageiros do Cliente" class="btn btn-sucess"><i class="fas fa-pencil-alt" style="color:#84ff00"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </thead>
                </body> 
            </table>
        </div>
    </div>
</main>
</div>

