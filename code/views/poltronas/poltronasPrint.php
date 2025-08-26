<style>
   table {
      width: 100% !important;
      border-collapse: collapse !important;
    }

    @media print {
        .noPrint{
            display:none;
        }

        #table,
        h2,
        h4{
            color:black;
        }
        table,
        .table td, 
        .table th {
            border-color: black !important;
        }

        .TableCSS tr {
          border: 1px solid !important;
        }

        table {
          width: 100% !important;
          border-collapse: collapse !important;
        }

        table, th, td {
          border: 1px solid black !important;
        }


    }

    table, th, td {
      border: 1px solid black !important;
    }

    g{
        cursor: pointer;
    }

</style>
<main class="py-4">
  
  <div class="personContainer">
      <div class="card-body">
          <hr>
              <h4>Grupo: <?php echo $grLine['ac']['NOME']; ?></h4>
              <h4>Linha: <?php echo $grLine['line']['PREFIXO'] . " - ". $grLine['line']['NOME']; ?></h4>
          <hr>
          <div class="TableCSS">
          <table id="table" class="table table-striped">
            <thead>
              <tr class="headerTr">
                <th scope="col" style="width: 100%;">Nome</th>
                <th scope="col" style="width: 150px;min-width:150px">Matrícula</th>
                <th scope="col" style="width: 100px;min-width:100px">Poltrona</th>
              </tr>
            </thead>
            <tbody>
                <?php foreach($dataLinha as $dtl){ ?>
                <tr>
                  <td><?php echo $dtl['NOME']; ?></td>
                  <td><?php echo $dtl['MATRICULA_FUNCIONAL']; ?></td>
                  <td><?php echo $dtl['POLTRONA']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
          </table>
          </div>
          <hr>
      </div>
  </div>
  
</div>

</main>



</div>
<script>

window.print();

setTimeout(()=>{
    window.close();
}, 5000);

</script>