<div class="card-create-body">
    <div class="row">
       
        <div class="col-sm-6 col-xs-12">
            <label for="nome" class="control-label">Nome:</label>
            <?php if( isset($_SESSION['old']) && !isset( $lider )) { ?>
                <input class="form-control" name="nome" type="text" required value="<?php echo $_SESSION['old']['nome']; ?>" />
            <?php } else if( isset( $lider )) { ?>
                <input class="form-control" name="nome" type="text" <?php echo $disabled; ?> value="<?php echo $lider->nome; ?>" />
                <input class="form-control" name="id" type="hidden" value="<?php echo $lider->id; ?>" />
            <?php } else { ?> 
                <input class="form-control" name="nome" type="text" required />
            <?php }?>
        </div>

        <div class="col-sm-6 col-xs-12">
            <label for="email" class="control-label">Email:</label>
            <?php if( isset($_SESSION['old'])) { ?>
                <input class="form-control" name="email" type="text" required value="<?php echo $_SESSION['old']['email']; ?>" />
            <?php } else if( isset( $lider )) { ?>
                <input class="form-control" name="email" type="text" <?php echo $disabled; ?> value="<?php echo $lider->email; ?>" />
            <?php } else { ?>
                <input class="form-control" name="email" type="text" required />
            <?php }?>
        </div>

        <div class="col-sm-6 col-xs-12">
            <label for="pass" class="control-label">Senha:</label>
            <input class="form-control" name="pass" type="text" />
        </div>

        <div class="col-sm-3 col-xs-12">
            <label for="unidadeID" class="control-label">Unidade:</label>
            <select name="unidadeID" class="form-control" <?php echo $disabled; ?>>
                <option>Selecione</option>

                <?php foreach($unidades AS $uni): ?>

                    <?php if(
                        ( isset($_SESSION['old']) && $_SESSION['old']['unid'] == $uni->id ) || 
                        ( isset($lider) && $lider->unidadeID == $uni->id )
                    ) { ?>
                        
                        <option value="<?php echo $uni->id; ?>" selected><?php echo $uni->descricao; ?></option>
                    
                    <?php } else { ?>

                        <option value="<?php echo $uni->id; ?>"><?php echo $uni->descricao; ?></option>

                    <?php }?>
                <?php endforeach; ?>
                
            </select>
        </div>

        <div class="col-sm-3 col-xs-12">
            <label for="type" class="control-label">Tipo:</label>
            <select name="type" class="form-control filtroSelect2" <?php echo $disabled; ?>>

                <?php if(
                    ( isset($_SESSION['old']) && $_SESSION['old']['type'] == 1 ) || 
                    ( isset($lider) && $lider->type == 1 )
                ) { ?>
                    <option value="1" selected>Gestor</option>
                <?php } else { ?>
                    <option value="1">Gestor</option>
                <?php }?>

                <?php if(
                    ( isset($_SESSION['old']) && $_SESSION['old']['type'] == 2 ) || 
                    ( isset($lider) && $lider->type == 2 )
                ) { ?>
                    <option value="2" selected>RH</option>
                <?php } else { ?>
                    <option value="2">RH</option>
                <?php }?>

            </select>
        </div>
    </div>
    <div class="row" style="margin-top:1em;">
        <?php if( !isset($_SESSION['cFret']) && $_SESSION['cType'] == 1 ) { ?>

        <div class="col-sm-6 col-xs-12">
            <label for="groupID" class="control-label">GRUPO:</label>
            <select name="groupID" class="form-control" <?php echo $disabled; ?>>

                  <?php foreach($grupos AS $grup): ?>

                    <?php if( isset($lider) && $lider->grupoID == $grup['id']  ) { ?>
                        
                        <option value="<?php echo $grup['id']; ?>" selected><?php echo $grup['NOME']; ?></option>
                    
                    <?php } else { ?>

                        <option value="<?php echo $grup['id']; ?>"><?php echo $grup['NOME']; ?></option>

                    <?php }?>

                <?php endforeach; ?>

            </select>
        </div>

        <?php }?>

    </div>
</div>