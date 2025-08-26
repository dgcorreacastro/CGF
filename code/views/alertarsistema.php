<script src="<?php echo BASE_URL; ?>assets/js/sweetalert.min.js"></script>
<?php if(isset($success) && $success == true): ?>

<!-- <div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php echo $msg; ?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div> -->
  <script>
    swal({
      title: "SUCESSO",
      text: "<?php echo $msg; ?>",
      icon: "success",
      button: "OK",
    });
  </script>
<?php endif; ?>

<?php if(isset($success) && $success == false): ?>

<!-- <div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?php echo $msg; ?>
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div> -->
  <script>
    swal({
      title: "ERRO",
      text: "<?php echo $msg; ?>",
      icon: "error",
      button: "OK",
    });
  </script>
<?php endif; ?>

<?php if(isset( $_SESSION['ms'] ) && $_SESSION['ms'] != ""): ?>

  <!-- <div class="alert alert-success alert-dismissible fade show" role="alert" style="width: 80%;margin: 20px auto;">
    <?php echo $_SESSION['ms']; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div> -->

  <script>
    swal({
      title: "SUCESSO",
      text: `<?php echo $_SESSION['ms'].'\n';
      if(isset( $_SESSION['mokpaxupdate'] ) && count($_SESSION['mokpaxupdate']) > 0 ){
        echo count($_SESSION['mokpaxupdate']) > 1 ?
        count($_SESSION['mokpaxupdate']).' passageiros atualizados com sucesso.' : 'Passageiro atualizado com sucesso!';
      }
      ?>`,
      icon: "success",
      confirm: "OK",
      }).then((result) => {
      if(result){
        <?php
        if(isset( $_SESSION['checkCadPax'] ) && $_SESSION['checkCadPax'] ){
        ?>
        
        swal({
        title: "ATENÇÃO",
        text: `<?php 
        if(isset( $_SESSION['merrary'] ) && count($_SESSION['merrary']) > 0 ){
          echo 'FALTARAM CAMPOS OBRIGATÓRIOS:\n';
          foreach($_SESSION['merrary'] AS $merrary){
            $campos = implode(", ", $merrary['desc']);
            echo 'Linha '.$merrary['line'].': '.$campos.'\n';
          }
          echo '\n';
        }
        if(isset( $_SESSION['merrcad'] ) && count($_SESSION['merrcad']) > 0 ){
            echo 'Será necessário fazer esses cadastros manualmente:\n';
            foreach($_SESSION['merrcad'] AS $merrcad){
              echo 'Linha '.$merrcad['line'].': '.implode(", ", $merrcad['desc']).'\n';
            }
        }
        ?>`,
        icon: "warning",
        confirm: "OK"
        });
      
        <?php
        }
        ?>
      }
    });
  </script>

<?php endif; ?>

<?php if(isset( $_SESSION['merr'] ) && $_SESSION['merr'] != ""): ?>

  <!-- <div class="alert alert-danger alert-dismissible fade show" role="alert" style="width: 80%;margin: 20px auto;">
    <?php echo $_SESSION['merr']; ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div> -->

  <script>
    swal({
      title: "ERRO",
      text: "<?php echo $_SESSION['merr']; ?>",
      icon: "error",
      button: "OK",
    });
  </script>

<?php endif; ?>

<!-- <?php if(isset( $_SESSION['merrary'] ) && count($_SESSION['merrary']) > 0 ): ?>
  

  <div class="alert alert-danger alert-dismissible fade show" role="alert" style="width: 80%;margin: 20px auto;">

    <?php 

      foreach($_SESSION['merrary'] AS $merrary)
      {
        echo "O(s) seguinte(s) campo(s) são obrigatório(s): " .  implode(", ", $merrary['desc']) . " da Linha: " . $merrary['line'];
      } 
      
    ?>

    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>

<?php endif; ?> -->

<!-- <?php if(isset( $_SESSION['merrcad'] ) && count($_SESSION['merrcad']) > 0 ): ?>

<div class="alert alert-danger alert-dismissible fade show" role="alert" style="width: 80%;margin: 20px auto;">

  <?php 

    foreach($_SESSION['merrcad'] AS $merrcad)
    {
      echo "ATENÇÂO: " .  implode(", ", $merrcad['desc']) . ". Será necessário fazer esses cadastros manualmente.";
    } 
    
  ?>

  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>

<?php endif; ?> -->

<?php if(isset( $_SESSION['checkSendInactivePax'] ) && $_SESSION['checkSendInactivePax']): ?>

<script>
  swal({
    title: "ATENÇÃO",
    text: `<?php 
    echo $_SESSION['msErrCount'];
    if(isset( $_SESSION['merrarypaxupdate'] ) && count($_SESSION['merrarypaxupdate']) > 0 ){
      echo 'FALTARAM CAMPOS OBRIGATÓRIOS:\n';
      foreach($_SESSION['merrarypaxupdate'] AS $merrarypaxupdate){
        $campos = implode(", ", $merrarypaxupdate['desc']);
        echo 'Linha '.$merrarypaxupdate['line'].': '.$campos.'\n';
      }
      echo '\n';
      // foreach($_SESSION['merrarypaxupdate'] AS $merrarypaxupdate){
      //   $campos = implode(", ", $merrarypaxupdate['desc']);
      //   $campoCount = substr_count( $campos, ", ") +1; 
      //   echo 'Linha '.$merrarypaxupdate['line'].':\n';
      //   $msgObri = $campoCount > 1 ? 
      //   'Os seguintes campos são obrigatórios:' :
      //   'O seguinte campo é obrigatório:';
      //   echo $msgObri.'\n';
      //   echo $campos.'.\n\n';
      // }
    }
    if(isset( $_SESSION['merrpaxupdate'] ) && count($_SESSION['merrpaxupdate']) > 0 ){
        echo 'ERRO AO INATIVAR:\n';
        foreach($_SESSION['merrpaxupdate'] AS $merrpaxupdate){
          
          echo 'Linha '.$merrpaxupdate['line'].': '.$merrpaxupdate['paxError'].'\n';
        }
      // foreach($_SESSION['merrpaxupdate'] AS $merrpaxupdate){
      //   echo 'Linha '.$merrpaxupdate['line'].':\n';
      //   echo $merrpaxupdate['paxError'].'.\n\n';
      // }
    }
    ?>`,
    icon: "warning",
    confirm: "OK",
  }).then((result) => {
    if(result){
        <?php
        if(isset( $_SESSION['mokpaxupdate'] ) && count($_SESSION['mokpaxupdate']) > 0 ){
        ?>
        
        swal({
          title: "SUCESSO",
          text: `<?php echo count($_SESSION['mokpaxupdate']) > 1 ?
          count($_SESSION['mokpaxupdate']).' passageiros atualizados com sucesso.' : '1 Passageiro atualizado com sucesso!';?>`,
          text: `<?php 
            foreach($_SESSION['mokpaxupdate'] AS $mokpaxupdate){
              
              echo 'Linha '.$mokpaxupdate['line'].':\n';
              echo $mokpaxupdate['paxError'].'.\n\n';
            } ?>`,
          icon: "success",
          button: "OK",
        });
      
        <?php
        }
        ?>
      }
    });
</script>

<?php endif; ?>

<?php

  unset($success);
  unset($msg);
  unset($_SESSION['ms']);
  unset($_SESSION['merr']);
  unset($_SESSION['checkCadPax']);
  unset($_SESSION['merrary']);
  unset($_SESSION['merrcad']);
  unset($_SESSION['checkSendInactivePax']);
  unset($_SESSION['merrarypaxupdate']);
  unset($_SESSION['merrpaxupdate']);
  unset($_SESSION['mokpaxupdate']);
  unset($_SESSION['msErrCount']);

?>