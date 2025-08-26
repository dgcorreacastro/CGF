<!DOCTYPE html>
<html lang="pt-BR">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> 

      <meta http-equiv="Cache-Control" content="no-cache, no-store">
      <meta http-equiv="Pragma" content="no-cache, no-store">

      <meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
      <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

      <link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

      <title>Mapa Circular Eurofarma</title>

      <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
      
      <!-- Scripts -->
      <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>

      <script>
        let map;
        let directionsRenderer;
        let wayp = [];
        let busMarkers = {};
        function initMap() {
            
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 19,
                disableDefaultUI: true,
                mapTypeId: 'satellite'
            });

            var southwest = new google.maps.LatLng(-23.522719, -46.964694); // Canto inferior esquerdo
            var northeast = new google.maps.LatLng(-23.515951, -46.957699); // Canto superior direito

            // // Criar um objeto LatLngBounds para a área desejada
            var bounds = new google.maps.LatLngBounds(southwest, northeast);
            map.fitBounds(bounds);

            <?php foreach($pontos as $key => $ponto):?>
                // Adiciona um marcador com texto usando canvas
                var canvasMarker = document.createElement('canvas');
                var context = canvasMarker.getContext('2d');
                var font = 'bold 11px Arial';
                context.font = font;
                var text = '<?php echo $ponto['nome'];?>';
                var width = context.measureText(text).width;
                canvasMarker.width = width + 10; // Adiciona espaço para o texto
                canvasMarker.height = 20; // Altura do marcador
                context.font = font;

                context.fillStyle = '#f34423';
                context.fillRect(0, 0, canvasMarker.width, canvasMarker.height);
                
                context.fillStyle = 'white'; 

                context.translate(canvasMarker.width / 2, canvasMarker.height / 2);
                
                context.rotate((180 * Math.PI) / 180);

                context.fillText(text, -width / 2, 4); // Desenha o texto no centro do canvas
                
                var canvasUrl = canvasMarker.toDataURL(); // Converte o canvas para uma imagem

                var canvasIcon = {
                    url: canvasUrl,
                    scaledSize: new google.maps.Size(width + 10, 20), // Tamanho do marcador
                    origin: new google.maps.Point(0, 0), // Ponto de origem do ícone
                    anchor: new google.maps.Point(width - 6, -9), // Ponto de ancoragem do ícone
                };
                var canvasMarker = new google.maps.Marker({
                    position: {lat: <?php echo $ponto['latitude'];?>, lng: <?php echo $ponto['longitude'];?>},
                    map: map,
                    icon: canvasIcon,
                    zIndex: 2
                });

                var svgMarker = {
                  path: "M5 25c2 2 5 4 8 5 4 1 8-1 10-5 1-3 2-7-1-12-2-3-5-5-7-7-1 0-2-1-3-1-1-1-5-3-6-3-1 0-2-1-3-1 0 1 0 4 5 8 6 5 5 5 7 14z",
                  fillColor: "#f34423",
                  fillOpacity: 1,
                  strokeWeight: 0,
                  scale: 1,
                  anchor: new google.maps.Point(0, -0)
                };
                var marker = new google.maps.Marker({
                    position: {lat: <?php echo $ponto['latitude'];?>, lng: <?php echo $ponto['longitude'];?>},
                    map: map,
                    icon: svgMarker,
                    zIndex: 1 
                });
                
                wayp.push({
                    location: new google.maps.LatLng(<?php echo $ponto['latitude'];?>, <?php echo $ponto['longitude'];?>),
                    stopover: true
                });
            <?php endforeach;?>

            const request = {
                origin: wayp[0].location,
                destination: wayp[wayp.length - 1].location,
                waypoints: wayp,
                provideRouteAlternatives: false,
                optimizeWaypoints: true,
                travelMode: google.maps.TravelMode.DRIVING
            };
            const directionsService = new google.maps.DirectionsService();
            directionsService.route(request, (response, status) => {
              if (status === "OK") {
                directionsRenderer = new google.maps.DirectionsRenderer({
                    suppressMarkers: true, // Não exibir marcadores de pontos de passagem
                });
                directionsRenderer.setDirections(response);
                directionsRenderer.setMap(map);
                setTimeout(() => {
                  map.fitBounds(bounds);
                  createBusMarkers();
                }, 200);
              } else {
                console.error(`Erro na solicitação de rota ${index}: ${status}`);
              }
            });

        }

        function createBusMarkers() {
          <?php foreach($vans as $van):?>
            const position<?php echo $van['vanId']?> = {lat: <?php echo $van['vanlat'];?>, lng: <?php echo $van['vanlong'];?>};
            var svgMarker<?php echo $van['vanId']?> = {
              path: "M29 19c10,4 12,19 3,29 -3,3 -7,5 -11,6 -8,1 -16,-5 -17,-14 0,-4 1,-9 4,-14 5,-7 14,-10 21,-7zm-11 12c-2,0 -1,-3 1,-3 1,0 1,1 1,2 -1,1 -1,1 -2,1zm9 5c-2,0 -1,-3 1,-3 1,0 1,1 1,2 0,1 -1,1 -2,1zm0 3c-5,8 -4,7 -7,5 -2,-1 -8,-4 -8,-5 -1,-1 2,-6 3,-7l12 7zm-8 7c3,2 4,2 7,-1l1 1 1 -2 -1 -1 5 -9c0,-1 -1,-1 -1,-2l1 -3 -3 -2 -2 3 -5 -3 2 -2 -3 -2 -2 3c-1,-1 -2,-1 -2,0l-5 8 -2 -1 -1 2 2 1c-2,4 -1,5 2,6l-2 3 7 4 1 -3zm-16 -19c-3,6 -4,13 -2,18 3,8 9,13 18,13 7,-1 15,-4 20,-14 6,-11 6,-28 3,-39 0,-1 0,-4 -1,-5 -1,0 -7,3 -9,3 -5,3 -12,6 -17,10 -5,4 -10,8 -12,14z",
              fillColor: "<?php echo $van['iconColor']?> ",
              fillOpacity: 1,
              strokeWeight: 0,
              scale: 1,
              anchor: new google.maps.Point(40, 20)
            }
            var vanId = '<?php echo $van['vanId']; ?>';
            busMarkers[vanId] = new google.maps.Marker({
                position: position<?php echo $van['vanId']; ?>,
                map: map,
                icon: svgMarker<?php echo $van['vanId']; ?>,
                zIndex: 99999
            });
            // calcularDistanciaParaPontos(position<?php echo $van['vanId']?>, '<?php echo $van['vanId']?>');
            setTimeout(() => {
              checkBusPosition(position<?php echo $van['vanId']?>, '<?php echo $van['vanId']?>');
            }, 1000);
          <?php endforeach;?>
        }

        // function calcularDistanciaParaPontos(currentPosition, vanId, direcao = false) {
        //   const origem = currentPosition; 

        //   const destinos = (direcao && direcao === 'volta') ? wayp.slice().reverse().map(ponto => ponto.location) : wayp.map(ponto => ponto.location);

        //   const service = new google.maps.DistanceMatrixService();
          
        //   service.getDistanceMatrix({
        //       origins: [origem],
        //       destinations: destinos,
        //       travelMode: google.maps.TravelMode.DRIVING,
        //       unitSystem: google.maps.UnitSystem.METRIC,
        //   }, (response, status) => {
        //       if (status === 'OK') {
        //           // Itera sobre cada elemento da matriz de distância
        //           for (let i = 0; i < response.rows[0].elements.length; i++) {
        //               const elemento = response.rows[0].elements[i];
        //               // const distbefore = (i === response.rows[0].elements.length - 1)  ? response.rows[0].elements[i-1].distance.value : response.rows[0].elements[i+1].distance.value;
        //               // const durabefore = (i === response.rows[0].elements.length - 1) ? response.rows[0].elements[i-1].duration.value : response.rows[0].elements[i+1].duration.value;
        //               if (elemento.status === 'OK') {                          
        //                   const distancia = formatDistance(elemento.distance.value);
        //                   const duracao = Math.round((elemento.duration.value) / 60);
        //                   // const duracao = elemento.duration.text.replace(/minutos/g, "min");
        //                   $(`#ponto-${i}-van-${vanId} .dist`).html(distancia);
        //                   $(`#ponto-${i}-van-${vanId} .time`).html(`${duracao} min`);
        //                   console.log(`Distância para ponto ${i + 1}: ${distancia}, Duração: ${duracao}`);
        //               } else {
        //                   console.error(`Não foi possível calcular a distância para o ponto ${i + 1}`);
        //               }
        //           }
                  
        //       } else {
        //           console.error('Erro ao calcular distâncias:', status);
        //       }
        //   });
        // }

        function checkBusPosition(position, vanId){

          $.ajax({
            url: '/passageiro/itinerarioEurofarmaBus',
            method: 'post',
            data: {vanId:vanId},
            dataType: 'json',
            success:function(ret){
        
              if (ret.status){

                const newLat = Number(ret.vanLocation.vanlat);
                const newLng = Number(ret.vanLocation.vanlong);
                const newPosition = {lat: newLat, lng: newLng};
                
                if(busMarkers[vanId] && (newLat != position.lat || newLng != position.lng)){
                  busMarkers[vanId].setPosition(newPosition);

                  // const direcao = determinarDirecaoDoOnibus(position, newPosition);

                  // calcularDistanciaParaPontos(newPosition, vanId, direcao);
                }

                setTimeout(() => {
                  checkBusPosition(newPosition, vanId);
                }, 3000);
                
              }

              
                      
            }
          });
        }

        // Função para formatar a distância em metros se for menor que 1 km
        // function formatDistance(distanceInMeters) {
        //     if (distanceInMeters < 1000) {
        //         return `${distanceInMeters} m`;
        //     } else {
        //         const distanceInKilometers = distanceInMeters / 1000;
        //         if (distanceInKilometers < 10) {
        //             return `${distanceInKilometers.toFixed(1)} km`;
        //         } else {
        //             return `${Math.round(distanceInKilometers)} km`;
        //         }
        //     }
        // }

        // function determinarDirecaoDoOnibus(oldPosition, newPosition) {
            
        //     const primeiroPonto = wayp[0].location; 

        //     const distanciaPrimeiroPontoAntes = google.maps.geometry.spherical.computeDistanceBetween(oldPosition, primeiroPonto);
        //     const distanciaPrimeiroAgora = google.maps.geometry.spherical.computeDistanceBetween(newPosition, primeiroPonto);

        //     if (distanciaPrimeiroAgora > distanciaPrimeiroPontoAntes) {
        //         return 'ida';
        //     } else {
        //         return 'volta';
        //     }
        // }

        // function determinarDirecaoDoOnibus(currentPosition) {
        //     // Suponha que 'currentPosition' seja a posição atual do ônibus
        //     // const proximoPonto = wayp[0].location; // Próximo ponto na rota
        //     const primeiroPonto = wayp[0].location; // Primeiro Ponto

        //     const distanciaPrimeiroPontoAntes = google.maps.geometry.spherical.computeDistanceBetween(busPosition, primeiroPonto);
        //     const distanciaPrimeiroAgora = google.maps.geometry.spherical.computeDistanceBetween(currentPosition, primeiroPonto);

        //     console.log(distanciaPrimeiroPontoAntes);
        //     console.log(distanciaPrimeiroAgora);
            
        //     // // Calcula as distâncias entre a posição atual do ônibus e o próximo ponto e o ponto anterior
        //     // const distanciaProximoPonto = google.maps.geometry.spherical.computeDistanceBetween(currentPosition, proximoPonto);
        //     // const distanciaPontoAnterior = google.maps.geometry.spherical.computeDistanceBetween(currentPosition, wayp[wayp.length - 1].location);

        //     // Se a distância para o próximo ponto for menor que a distância para o ponto anterior,
        //     // o ônibus está indo na direção da rota
        //     if (distanciaPrimeiroAgora > distanciaPrimeiroPontoAntes) {
        //         return 'volta';
        //     } else {
        //         return 'ida';
        //     }
        // }

        $(document).on('click', '.seePrev', function(){
          $('.pontos').toggleClass('open');
        });

      </script>
      <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
      
      <style>
        body {
          display: block;
          touch-action: manipulation;
          position: relative;
          overflow: hidden !important;
          margin: 0 !important;
          padding: 0 !important;
          height: 100vh;
          width: 100vw;
        }
        body, body > * {
          cursor: none;
        }

        .mapOuter {
          display: flex;
          position: fixed;
          width: 100vw;
          height: 95vh;
          overflow: visible !important;
          transform: rotate(150deg) scale(1.275) translateX(0px);
          z-index: 2;
          flex-direction: row;
          justify-content: center;
          align-items: center;
        }

        #map {
          position: absolute;
          height: 154vh;
          width: 154vw;
          cursor: none !important;
        }

        #map div > * {
          cursor: none !important;
        }

        .logos {
          display: flex;
          position: fixed;
          z-index: 2;
          width: 100vw;
          height: 150px;
          padding: .5em;
          bottom: 0;
          left:0;
          justify-content: flex-end;
          align-items: flex-end;
          background: rgb(255,255,255);
          background: linear-gradient(0deg, rgba(255,255,255,1) 30%, rgba(255,255,255,0) 60%);
        }

        .logos .logosPontos {
          max-width:80%;
          object-fit: contain;
        }

        .pontos {
          display: flex;
          width: fit-content;
          z-index: 999999;
          bottom: 0;
          left: 0;
          margin-bottom: 5%;
          margin-left: 2.5%;
          padding: .5em;
          border-radius: .5em;
          background: white;
          position: absolute;
          flex-direction: row;
          flex-wrap: wrap;
          -webkit-user-select: none;
          -moz-user-select: none;
          -ms-user-select: none;
          user-select: none;
          box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
          justify-content: flex-start;
          align-items: flex-start;
          font-size: 15px;
          text-align: center;
        }

        ul{
          list-style: none;
          margin-bottom:0;
          margin-block-start: 0;
          padding-inline-start: 0;
          padding: .2em;
        }

        ul li{
          border-bottom: 1px solid lightgrey;
          padding: .1em;
        }

        li:nth-child(even) {
            background-color: #efefef;
        }

        .pontos p, .vansHold p{
          position: relative;
          margin: 0;
        }

        ul li:first-of-type, ul li:nth-child(2){
          background-color: var(--bgColor);
          color: var(--textColor);
          font-weight: bold;
        }

        .vansHold {
          position: relative;
          display: flex;
          flex-direction: row;
          flex-wrap: nowrap;
          justify-content: flex-start;
          width: 254px;
          overflow: auto;
          border-left: 1px solid lightgrey;
        }

        .vansHold ul {
          position:relative;
          display: flex;
          flex-direction: column;
          flex-grow: 1;
          width: max-content;
          flex-wrap: nowrap;
          justify-content: center;
        }

        .vansHold ul li:not(:first-of-type) {
          width: 120px;
          display: grid;
          grid-template-columns: 50% 50%;
          grid-template-rows: auto;
          justify-items: stretch;
          justify-content: start;
        }

        .vansHold ul li p:nth-child(2)::before, .vansHold ul::before {
          content: '';
          top: 0;
          left: -1px;
          height: 100%;
          position: absolute;
          width: 100%;
          border-left: 2px solid lightgrey;
        }

        .topdiv {
          display: flex;
          position: fixed;
          width: 100vw;
          height: 100vh;
          top: 0;
          left: 0;
          z-index: 99999;
          background: transparent !important;
          flex-direction: row;
          justify-content: center;
          align-items: flex-start;
        }

        .seePrev {
          display:none;
          margin-top: 1em;
        }

        .legendasVans {
          display: flex;
          flex-direction: row;
          flex-wrap: wrap;
          align-items: center;
          width: 100vw;
          position: fixed;
          padding: 1em;
          gap: .5em;
        }

        .legendasVans span {
          font-size: 15px;
          background-color: var(--bgColor);
          color: var(--textColor);
          font-weight: bold;
          padding: .5em;
        }

        .left {
          display:none;
          position: absolute;
          bottom: 0;
          right: 0;
          z-index: 2;
          width: 100%;
          height: 5%;
          transform: translateY(630%);
          background: rgb(255,255,255);
        }

        @media (max-width: 1024px){ 
          .pontos {
            margin-bottom: 2%;
            margin-left: 2%;
          }
        }

        @media (max-width: 920px){ 
          .logos {
            height: 90px;
          }

          @media (orientation: landscape) { 
            .left {
              display: block;
            }
            .logos .logosPontos {
              max-width:30%;
            }
            .mapOuter {
              margin-left:12%;
              transform: rotate(150deg) scale(1) translateX(14%) translateY(22%);
            }
          }

          .legendasVans {
            top:0;
            justify-content: flex-end;
          }
        }

        /* @media (max-width: 900px){ 
          
          .mapOuter {
            transform: rotate(150deg) scale(1.2) translateX(10%) translateY(10%);
          }          
          
        } */

        @media (max-width: 700px){ 
          .pontos {
            width: 300px;
            opacity: 0;
            visibility: hidden;
            margin-left: 50%;
            transform: translateX(-50%);
            transition: all 250ms;

          }
          .pontos.open {
            opacity: 1;
            visibility: visible;
          }
          .seePrev {
            display:flex;
          }

          .vansHold {
            width: 160px;
          }

          .mapOuter {
              transform: rotate(150deg) scale(1.1) translateX(0) translateY(5%);
          }         

        }

        @media (max-width: 420px){ 
          
          .pontos {
            margin-bottom:25%;  
          }

          .mapOuter {
              transform: rotate(150deg) scale(1.1) translateX(-10px);
          }

          .legendasVans {
            justify-content: center;
            height:100px;
            background: rgb(255,255,255);
            background: linear-gradient(180deg, rgba(255,255,255,1) 40%, rgba(255,255,255,0) 80%);
          }

          .logos {
            height:40%;
            justify-content: center;
          }
          
        }

        @media (min-width: 1600px){ 
          .pontos {
            margin-bottom: 10%;
            margin-left: 8%;
            transform: scale(1.3);
          }
        }

        @media (min-width: 1800px){ 
          .mapOuter {
              transform: rotate(150deg) scale(1.2) translateX(0px);
          }
        }

        
    </style>
  </head>
  <body oncontextmenu="return false;">
    <div class="topdiv">
      <div class="logos">
        <img class="logosPontos" src="<?php BASE_URL; ?>/assets/images/logos.png">
      </div>
      <!-- <span class="btn btn-warning seePrev">Ver Previsões</span> -->
       <div class="legendasVans">
        <?php foreach($vans as $van):?>
          <span style="--bgColor: <?php echo $van['iconColor']?>; --textColor: black"><?php echo $van['NomeVan']?></span>
        <?php endforeach;?>
       </div>
    </div>
    <!-- <div class="pontos">
      <ul class="even">
        <li style="--bgColor: white; --textColor: white">-</li>
        <li style="--bgColor: lightblue; --textColor: black">Ponto</li>
        <?php foreach($pontos as $key => $ponto):?>
          <li><?php echo $ponto['nome'];?></li>
        <?php endforeach;?>
      </ul>
      <div class="vansHold">
        <?php foreach($vans as $van):?>
          <ul>
            <li style="--bgColor: <?php echo $van['iconColor']?>; --textColor: black"><?php echo $van['NomeVan']?></li>
            <li style="--bgColor: lightblue; --textColor: black"><p>Dist.</p><p>Tempo</p></li>
            <?php foreach($pontos as $key => $ponto):?>
              <li id="ponto-<?php echo $key;?>-van-<?php echo $van['vanId'];?>"><p class="dist">-</p><p class="time">-</p></li>
            <?php endforeach;?>
          </ul>
        <?php endforeach;?>
      </div>
    </div> -->
    <div class="mapOuter">
      <div class="left"></div>
      <div id="map"></div>
    </div>
    <?php $apiKey = ($param['apiKey_active'] == 1) ? FRONTKEYGOOGLE . '&libraries=places&v=weekly' : 'xxxxxxxxxxxxxxxxxxxxx'; ?>
    <script async loading="async" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey?>&callback=initMap"></script>
  </body>
</html>