<div class="card-create-body">
    <div class="row">
        <div class="col-sm-6 col-xs-12">
            <label for="name" class="control-label">Descrição:</label>
            <?php if( isset($_SESSION['old'])) { ?>
                <input class="form-control" name="descricao" type="text" id="descricao" required value="<?php echo $_SESSION['old']['descricao']; ?>" />
            <?php } else if( isset( $setor )) { ?>
                <input class="form-control" name="descricao" type="text" id="descricao" required value="<?php echo $setor->descricao; ?>" />
                <input class="form-control" name="id" type="hidden" value="<?php echo $setor->id; ?>" />
            <?php } else { ?>
                <input class="form-control" name="descricao" type="text" id="descricao" required />
            <?php }?>
        </div>
    </div>
</div>