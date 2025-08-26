<main class="py-4">
    <div class="personContainer">
        <div class="card-body">

            <div class="card-create-header">
                <h2 class="pageTitle"></h2>
            </div>
            <hr>
            <div style=" display: flex; flex-direction: column; flex-wrap: nowrap; align-items: center;">
                <table id="table" class="table table-striped customScroll" style="position: sticky; top:0; z-index:3; margin-bottom: 0; max-width:fit-content;">
                <thead>
                    <tr class="headerTr applyWidth" style="background: #0468bf !important;">
                        <th scope="col">Termo</th>
                        <th scope="col">Editar</th>
                    </tr>
                </thead>
                </table>
                <table id="table" class="table table-striped tBodyScroll" style="max-width:fit-content;">
                    <tbody>
                        <tr class="toMark">
                            <td scope="col" style="width: 230px !important;">Termos de Uso</td> 
                            <td scope="col" style="width: 90px !important;" class="text-center">
                                <a title="Editar" href="/terms/edit?id=1" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td scope="col" style="width: 230px !important;">Política de Privacidade</td> 
                            <td scope="col" style="width: 90px !important;" class="text-center">
                                <a title="Editar" href="/terms/edit?id=2" class="btn btn-primary editIcon"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="wrapper1">
                    <div class="div1"></div>
                </div>
                <div class="wrapper1after"></div>
            </div>
            
        </div>
                    
    </div>
</div>

<script type="text/javascript">
    window.onload = function(e){ 
        setActiveMenu('/terms');
    }
</script>