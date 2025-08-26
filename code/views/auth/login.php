<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/js/checkCgfVersion.js?v=<?php echo $_SESSION['cgfVersion'] ?? 1;?>"></script>
<div class="container">
    <section class="login-section">
        <div class="container">
            <div id="loginDiv" class="card card-container">
                <div class="container-fluid">
                	<div class="row">
                		<div class="col-sm-12">
                        
                			<form method="POST" action="<?php echo BASE_URL; ?>login/validar/">                
                                <h3 id="titleLogin"><?php echo APP_NAME; ?></h3>
                                <?php if(isset($_SESSION['forbidden'])){?>

                                    <div class="errosContainer login">
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
                                            <p class="bg bg-warning text-dark p-2 text-left"><?php echo $_SESSION['forbidden']['msg'];?></p>
                                            <?php if($_SESSION['forbidden']['showLogin']):?>
                                            <div class="buttons-con">
                                                <div class="action-link-wrap">
                                                    
                                                    <a href="/" class="link-button">Login</a>
                                                </div>
                                            </div>
                                            <?php endif;?>
                                        </div>
                                    </div>

                                    <?php 
                                    unset($_SESSION['forbidden']);
                                    }else{?>
                                    <div class="form-group row">
                                        <div class="col-md-8" style="margin: auto;">
                                            <input id="email" type="email" class="form-control" name="email" value="" required autocomplete="email" autofocus placeholder="Email">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-md-8" style="margin: auto;">
                                            <div class="userCadPass" style="--btnNumber: 1">
                                                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="Senha">
                                                <i onclick="showHidePass(this, '#password')" class="fas fa-eye" title="Mostar Senha"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0 justify-content-center">
                                        <div class="col-md-8" style="text-align: center;">
                                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2"> ENTRAR </button>
                                        </div>
                                        <div class="col-md-8 mt-4" style="text-align: center;">
                                            <button onclick="passwordReset(event)" type="button" class="btn btn-warning btn-sm w-100"> ESQUECI MINHA SENHA </button>
                                            <input type="hidden" id="hasCode" value="0" />
                                        </div>
                                    </div>
                                <?php }?>
                            </form>
                		</div>
                		
                	</div>
                </div>
            </div>
        </div>
    </section>
</div>


</div>