<?php
// CI4: Plus besoin de BASEPATH check
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>

    <?php $assetVersion = '2026.02.11-1'; // version manuelle des assets CSS/JS ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
   
	<title><?php echo $titre;?></title>
    <script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-19724464-1']);
		  _gaq.push(['_trackPageview']);
		
		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>
    <!---  ba7fd5f5 --->
</head>
<body class="page-login">

<div id="container" class="login-container">
    <div class="login-hero">
        <div class="login-hero-content">
            <h1 class="login-hero-title">REZO+ PC Inline</h1>
            <hr class="login-separator"></hr>
            <p class="login-hero-subtitle">Plateforme opérationnelle</p>
            <hr class="login-separator"></hr>
            <p class="login-hero-tagline">Coordination et information en temps réel</p>
            <p class="login-hero-version"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.1' ?></p>
            <div class="login-hero-notice">
                <span class="login-hero-notice-title">Votre plateforme évolue !</span>
                <p>Nous avons repensé le design de votre espace pour vous offrir une expérience plus moderne, plus fluide et plus confortable.</p>
                <p>Découvrez également une nouvelle fonctionnalité :<br /><br />
                🌗 le mode clair / sombre, désormais accessible depuis le menu principal, pour adapter l’affichage à vos préférences.</p>
                <p><i><br />Bonne découverte !</i></p>
            </div>
        </div>
    </div>

    <div class="login-panel-wrapper">
        
        <div class="login-panel">

            <div class="login-logo-top">
                <a href="<?php echo base_url();?>" class="login-logo-link" title="Accueil"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png" width="80" height="80"></a>
            </div>


            <h2 class="login-panel-title">Connexion sécurisée</h2>

            <?php if(isset($success) && $success):?>
            <div class="succes"><?php echo $success;?></div>
            <?php endif;?>
            <?php if(isset($error)):?>
            <div class="error"><?php echo $error;?></div>
            <?php endif;?>

            <?php echo form_open('signup/login');?>

            <div class="login-form-box">
                <div class="login-field login-field--user">
                    <label for="code" class="sr-only">Identifiant</label>
                    <input type="text" name="code" id="code" placeholder="Code REZO+" value="<?php echo set_value('code');?>"/>
                    <?php echo form_error('code','<div class="error">','</div>');?>
                </div>

                <div class="login-field login-field--pass">
                    <label for="pass" class="sr-only">Mot de passe</label>
                    <input type="password" name="pass" id="pass" placeholder="Mot de passe" value="<?php echo set_value('pass');?>"/>
                    <?php echo form_error('pass','<div class="error">','</div>');?>
                </div>

                <p class="login-forgot">
                    <?php echo anchor('envoi_password','Mot de passe oublié ?','target=_blank');?>
                </p>

                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Se connecter" />
                </p>
            </div>

            <?php echo form_close();?>

            <div class="login-panel-actions">
                <p class="login-helper-text">
                    <u>Si vous venez d'acheter une licence</u>, vous devez d'abord créer un compte ici.
                </p>
                <a href="<?php echo base_url();?>signup" class="login-secondary-btn">Créer un compte</a>

                <p class="login-helper-text">
                    Des problèmes de connexion ? Visitez notre guide de démarrage ici.
                </p>
                <a href="https://www.web-dream.fr/app/guide-de-demarrage-rapide-rezo/" target="_blank" class="login-secondary-btn login-secondary-btn--outline">Guide de démarrage rapide</a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p class="footer"><?php if(isset($footing)) echo $footing;?></p>
</footer>
</body>
</html>