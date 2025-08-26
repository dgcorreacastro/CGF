<!DOCTYPE html>
<html lang="pt-BR">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <meta http-equiv="Cache-Control" content="no-cache, no-store">
      <meta http-equiv="Pragma" content="no-cache, no-store">

      <meta name="description" content="Sistema de Controle de Fretamento do Grupo TP Transportes">
      <meta name="keywords" content="Frotas, fretamento, onibus, controle de fretamento, TP Transporte.">

      <link rel="shortcut icon" href="/assets/favicon/favicon.ico" type="image/x-icon">

      <title><?php echo APP_NAME; ?> - Mapa - <?php echo $title; ?></title>

      <link rel="stylesheet" type="text/css" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" />
      
      <!-- Scripts -->
      <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script>

        <script>

            let marker;
            let map;
            let panorama;
            let geocoder;

            function initPanorama() {
                var fenway = {lat: <?php echo $latitude;?>, lng: <?php echo $longitude;?>};
                panorama = new google.maps.StreetViewPanorama(
                    document.getElementById('pano'), {
                        position: fenway,
                        pov: {
                            heading: 34,
                            pitch: 10
                        },
                        disableDefaultUI: true,
                        addressControl: false,
                        zoomControl: false,
                        fullscreenControl: false,
                    });
            }

            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 15,
                    center: {lat: <?php echo $latitude;?>, lng: <?php echo $longitude;?>},
                    disableDefaultUI: true
                });

                marker = new google.maps.Marker({
                    position: {lat: <?php echo $latitude;?>, lng: <?php echo $longitude;?>},
                    map: map,
                    title: '<?php echo $titlePoint;?>'
                });

                <?php if($showaddress == 1):?>

                    <?php if(!$displayName):?>
                        geocoder = new google.maps.Geocoder();

                        geocoder.geocode({ 'location': {lat: <?php echo $latitude;?>, lng: <?php echo $longitude;?>} }, function(results, status) {
                                    
                            if (status === 'OK') {
                                if (results[0]) {
                                    var locationName = results[0].formatted_address;
                                    $('#displayName').html(locationName);
                                }
                            }
                        });
                    <?php else:?>
                        $('#displayName').html('<?php echo $displayName;?>');
                    <?php endif;?>
                <?php endif;?>

                <?php if($showPano == 1):?> 
                    initPanorama();
                <?php endif;?>
                
            }

            let iniLat = <?php echo $latitude;?>;
            let iniLong =  <?php echo $longitude;?>;

            <?php if($atualiza == 1):?>
                window.addEventListener('message', function(event) {
                    
                    if (event.data.latitude && event.data.longitude) {

                        if(event.data.latitude != iniLat || event.data.longitude != iniLong){

                            iniLat = event.data.latitude;
                            iniLong = event.data.longitude;

                            var newPosition = {
                            lat: event.data.latitude,
                            lng: event.data.longitude
                            };
                            marker.setPosition(newPosition);
                            map.setCenter(newPosition);
                            <?php if($showPano == 1):?> 
                                panorama.setPosition(newPosition);
                            <?php endif;?>

                            <?php if($showaddress == 1):?>
                                geocoder.geocode({ 'location': newPosition }, function(results, status) {

                                    if (status === 'OK') {
                                        if (results[0]) {
                                            var locationName = results[0].formatted_address;
                                            $('#displayName').html(locationName);
                                        }
                                    }
                                });
                            <?php endif;?>

                        }

                    }
                });
            <?php endif;?>


        </script>
      <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
      
      <style>
        body {
            display: flex;
            width: 100%;
            height: 100%;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
            overflow: hidden !important;
        }
        #map {
            height: <?php echo ($showPano == 1) ? 'calc(50vh + 2.5em)' : 'calc(100vh + 2.5em)'?>;
            width: 100%;
            margin-top: -1.5em;
            z-index: 2;
        }
        #pano {
            height:<?php echo ($showPano == 1) ? '50vh' : '0'?>;
            width: 100%;
        }

        <?php if($showaddress == 1):?>
            #displayName {
                position: absolute;
                z-index: 3;
                padding: .5em;
                top: <?php echo $topName;?>%;
                right: 0;
                width: 100%;
                font-size: 10px;
                font-weight: bold;
                color: white;
                background: rgba(0,0,0, .5);
                box-shadow: rgba(9, 30, 66, 0.25) 0px 4px 8px -2px, rgba(9, 30, 66, 0.08) 0px 0px 0px 1px;
                transition: all 250ms;
            }
        <?php endif;?>

        <?php if($showtop == 1):?>
            #pano::before {
                content: '';
                display: block;
                position: absolute;
                z-index: 2;
                width: 100%;
                height: 35%;
                background: rgb(255,255,255);
                background: linear-gradient(180deg, rgba(255,255,255,1) 55%, rgba(255,255,255,0) 100%);
            }
        <?php endif;?>
    </style>
  </head>
  <body>
    <?php if($showaddress == 1):?><i id="displayName"></i><?php endif;?>
    <div id="pano"></div>
    <div id="map"></div>
    <?php $apiKey = ($param['apiKey_active'] == 1) ? FRONTKEYGOOGLE . '&libraries=places&v=weekly' : 'xxxxxxxxxxxxxxxxxxxxx'; ?>
    <script async loading="async" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey?>&callback=initMap"></script>
  </body>
</html>