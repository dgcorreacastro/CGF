<main class="py-4" style="width: 100% !important; color:#242832 !important">
    <h2 class="title"><span>C</span>ENTRO DE <span style="margin-left:.1em">G</span>ESTÃO DO <span style="margin-left:.1em">F</span>RETAMENTO</h2>
    <div class="row d-flex justify-content-center w-100 p-4 m-0 mt-4" style="gap:2em;">
        <?php if(isset($_SESSION['forbidden'])){?>

        <div class="errosContainer">
            <svg class="errosSvg" width="350px" height="500px" viewBox="0 0 837 1045" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <path d="M353,9 L626.664028,170 L626.664028,487 L353,642 L79.3359724,487 L79.3359724,170 L353,9 Z" id="Polygon-1" stroke="#007FB2" stroke-width="6"></path>
                    <path d="M78.5,529 L147,569.186414 L147,648.311216 L78.5,687 L10,648.311216 L10,569.186414 L78.5,529 Z" id="Polygon-2" stroke="#EF4A5B" stroke-width="6"></path>
                    <path d="M773,186 L827,217.538705 L827,279.636651 L773,310 L719,279.636651 L719,217.538705 L773,186 Z" id="Polygon-3" stroke="#795D9C" stroke-width="6"></path>
                    <path d="M639,529 L773,607.846761 L773,763.091627 L639,839 L505,763.091627 L505,607.846761 L639,529 Z" id="Polygon-4" stroke="#F2773F" stroke-width="6"></path>
                    <path d="M281,801 L383,861.025276 L383,979.21169 L281,1037 L179,979.21169 L179,861.025276 L281,801 Z" id="Polygon-5" stroke="#36B455" stroke-width="6"></path>
                </g>
            </svg>
            <div class="message-box">
                <h1><?php echo $_SESSION['forbidden']['code'];?></h1>
                <p class="bg bg-warning text-dark p-2"><?php echo $_SESSION['forbidden']['msg'];?></p>
                <div class="buttons-con">
                    <div class="action-link-wrap">
                        <a onclick="history.back(-1)" class="link-button link-back-button">Voltar</a>
                        <a href="/" class="link-button">Home</a>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        unset($_SESSION['forbidden']);
        }else{
            if(isset($_SESSION['userMenu'])){
                foreach($_SESSION['userMenu'] as $menu){?>
                    <a href="<?php echo $menu['link'];?>" class="col-12 col-md-3 btn btn-warning p-3 d-flex flex-column">
                        <i class="<?php echo $menu['icon'];?> h3" aria-hidden="true"></i>
                        <b class="h6"><?php echo $menu['description'];?></b>
                    </a>
                <?php }
        }}?>
            
    </div>
</main>