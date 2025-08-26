<main class="py-4">
    <div class="personContainer">
        
            <div class="card-body">
                <div class="card-create-header">
                    <h2 class="pageTitle"></h2>
                    <div class="filterRelResultContainer show">
                        <input type="text" id="filterRelResult" class="form-control" placeholder="Digite aqui para filtrar os carros..."/>
                    </div>
                </div>
                <hr>
                <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                    <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                        <thead>
                            <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                                <th scope="col">Tipo</th>
                                <th scope="col">Marca</th>
                                <th scope="col">Modelo</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Placa</th>
                                <th scope="col">Capacidade</th>
                                <th scope="col">Limite</th>
                                <th scope="col">QRCode</th>
                            </tr>
                        </thead>
                    </table>
                    <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                        <tbody>
                            <?php foreach($carros as $gr): ?>
                                <tr class="toMark">
                                    <td scope="col"><?php echo utf8_encode($gr['TIPOVEICULO']); ?></td>
                                    <td scope="col"><?php echo utf8_encode($gr['MARCA']); ?></td>
                                    <td scope="col"><?php echo utf8_encode($gr['MODELO']); ?></td>
                                    <td scope="col"><?php echo utf8_encode($gr['NOME']); ?></td>
                                    <td scope="col"><?php echo utf8_encode($gr['PLACA']); ?></td>
                                    <td scope="col"><?php echo $gr['CAPACIDADE_PASSAGEIROS']; ?></td>
                                    <td scope="col"><?php echo $gr['CAPACIDADE_LIMIT_PASSAGEIROS']; ?></td>
                                    <td scope="col">
                                        <a title="Ver QRCode" href="/app/printcarqrcode?qr=<?php echo $gr['ID_ORIGIN'] ?>&marca=<?php echo utf8_encode($gr['MARCA']); ?>&modelo=<?php echo utf8_encode($gr['MODELO']); ?>&placa=<?php echo utf8_encode($gr['PLACA']); ?>" class="btn btn-warning mx-2 editIcon" target="_blank"><i class="fas fa-qrcode"></i></a>
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