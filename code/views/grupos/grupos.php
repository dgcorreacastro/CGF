<main class="py-4">
    <div class="personContainer">
        
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"></h2>
                    <div class="filterRelResultContainer show">
                        <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar os grupos..."/>
                    </div>
                </div>
                <hr>
                <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                    <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                        <thead>
                            <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                                <th>Nome</th>
                                <th>Código</th>
                                <th>Parâmetros</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                        <tbody>
                            <?php foreach($grupos as $gr): ?>
                                <tr class="toMark">
                                    <td scope="col" style="width: 400px"><?php echo utf8_decode(utf8_encode($gr['NOME'])); ?></td>
                                    <td scope="col" style="width: 100px"><?php echo utf8_encode($gr['ID_ORIGIN']); ?></td>
                                    <td scope="col" style="width: 95px">
                                        <a title="Editar Parâmetros" href="/grupos/parameters?id=<?php echo $gr['id'] ?>" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="wrapper1">
                        <div class="div1"></div>
                    </div>
                    <div class="wrapper1after"></div>
                </div>
            </div>
        
    </div>
</main>