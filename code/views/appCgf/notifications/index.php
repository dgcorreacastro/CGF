<main class="py-4" <?php echo $_SESSION['cType'] == 3 ? 'style="width: 100% !important;"' : ''; ?>>
    <div class="personContainer">
        <div class="card-body">

            <div class="card-create-header">
            <?php if($_SESSION['cType'] != 3){?>
                <h2 class="pageTitle"></h2>
            <?php }else{?>
                <h2><i class="fas fa-bell "></i> <?php echo APP_NAME;?> PASS <b class="h4">&#10148; Notificações</b></h2>
            <?php }?>
            </div>
            <hr>
            <h5 class="text-center">Selecione a mensagem que deseja enviar:</h5>
            <div class="msgsNotification">
                <?php foreach($msgs AS $msg): ?>
                    <span msgId="<?php echo $msg['id']; ?>" class="msgBtn" title="<?php echo $msg['msg']; ?>">
                        <?php echo $msg['titulo']; ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <div class="camposMsgsNotification">
                <div class="row" style="gap:1em; justify-content: space-evenly;">
                    <div class="col-sm-5 col-xs-12 carroatual">
                        <label for="carroatual" class="form-label">CARROATUAL:</label>
                        <select id="carroatual" class="form-control">
                            <option value="0">Selecionar</option>
                            <?php foreach($carros as $cr): ?>

                            <option nomeveiculo="<?php echo $cr['NOME'];?>" value="<?php echo $cr['ID_ORIGIN'] ?>"> <?php echo $cr['NOME'] . ' - ' . $cr['MODELO'] . ' - ' . $cr['MARCA'] ?></option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-sm-5 col-xs-12 tempo">
                        <label for="tempo" class="form-label">TEMPO (em minutos - min: 1, max: 60):</label>
                        <input type="number" id="tempo" class="form-control" min="0" step="5" max="60">
                    </div>

                    <div class="col-sm-5 col-xs-12 novocarro">
                        <label for="novocarro" class="form-label">NOVOCARRO:</label>
                        <select id="novocarro" class="form-control">
                            <option value="0">Selecionar</option>
                            <?php foreach($carros as $cr): ?>

                            <option nomeveiculo="<?php echo $cr['NOME'];?>" value="<?php echo $cr['ID_ORIGIN'] ?>"> <?php echo $cr['NOME'] . ' - ' . $cr['MODELO'] . ' - ' . $cr['MARCA'] ?></option>

                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <hr>
            <div class="wrapper">
                <form id="sendFormNotify" method="POST">
                    <div class="card-create-body">
                        <div class="row">

                            <div class="col-sm-12 col-xs-12">
                                <label for="title" class="control-label">Titulo:</label>
                                <input id="title" class="form-control" name="title" type="text" maxlength="50" placeholder="Selecione a mensagem nos botões acima" readonly="readonly"/>
                            </div>

                            <hr style="width:100%">

                            <div class="col-sm-12 col-xs-12">
                                <label for="message" class="control-label">Mensagem:</label>
                                <input id="message" class="form-control" name="message" type="text" maxlength="200" placeholder="Selecione a mensagem nos botões acima" readonly="readonly"/>
                            </div>

                            <hr style="width:100%">

                            <div class="col-sm-6 col-xs-12">
                                <label for="lines" class="control-label">Linha:</label> </br>
                                <select id="lines" name="lines" class="form-control" onchange="boardingPoints(this.value)">
                                    <option value="">Selecione</option>
                                    <?php foreach($linhas AS $lin): ?>
                                        <option value="<?php echo $lin['ID']; ?>"><?php echo $lin['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-sm-6 col-xs-12">
                                <label for="dot" class="control-label">Ponto Embarque: 
                                    <i title="Não selecione o Ponto se a mensagem for para todos da Linha!" style="color:red; font-size:15px" class="fas fa-question-circle"></i>
                                </label></br>
                                <select id="dot" name="dot" class="form-control">
                                    <option value="">Selecione</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <hr>
                    <div class="card-create-footer">

                    </div>
                    <div class="row d-flex justify-content-end">
                        <?php if($_SESSION['cType'] == 3){?>
                            <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                                <a href="/" class="btn btn-danger btn-ls w-100">VOLTAR</a>  
                            </div>
                        <?php }?>  
                        <div class="col-sm-auto col-xs-12 mt-sm-0 mb-3">
                            <span class="btn btn-warning btn-ls w-100" onclick="sendMessageApp('notifications/byPush')">ENVIAR PUSH</span>
                        </div>               
                    </div>
                </form>    
            </div>
            </div>
    </div>
</main>
</div>
<script type="text/javascript">
    window.onload = function(e){ 
        setActiveMenu('/notifications');
    }
</script>