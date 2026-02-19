<?php
// CI4: Plus besoin de BASEPATH check
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
    
    <script src="<?php echo base_url();?>js/sweetalert.min.js"></script> 
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>js/sweetalert.css">
    
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

</head>
<body class="page-forgot">

<div id="container" class="forgot-container">
    <div class="login-hero">
        <div class="login-hero-content">
            <h1 class="login-hero-title">REZO+ PC Inline</h1>
            <hr class="login-separator">
            <p class="login-hero-subtitle">Plateforme opérationnelle</p>
            <hr class="login-separator">
            <p class="login-hero-tagline">Coordination et information en temps réel</p>
            <p class="login-hero-version"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.1' ?></p>
        </div>
    </div>

    <div class="login-panel-wrapper">
        

        <div class="login-panel">

            <div class="login-logo-top">
                <a href="<?php echo base_url();?>" class="login-logo-link" title="Accueil"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png" width="80" height="80"></a>
            </div>

            <h2 class="login-panel-title">Mot de passe oublié</h2>

            <?php if(isset($error)):?>
            <div class="error"><?php echo $error;?></div>
            <?php endif;?>
            <?php if(isset($succes)):?>
            <script>swal({   title: "Succès.",   text: "Un lien de réinitialisation a été envoyé à votre adresse email. Consultez votre boîte de réception (et les spams).",   type: "success",   showCancelButton: false,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Ok",   cancelButtonText: "", closeOnConfirm: true }, function(){   
                window.location = "<?= base_url(); ?>";   
        });</script>
            <div class="succes"><?php echo $succes;?></div>
            <?php endif;?>

            <div class="login-form-box">
                <p class="login-helper-text" style="margin-bottom:10px;">
                    Pour réinitialiser votre mot de passe REZO+ PC Inline, saisissez votre adresse email ci-dessous.
                    Vous recevrez un lien par email (valide 1 heure).
                </p>

                <?php echo form_open('envoi_password');?>

                <div class="login-field login-field--user">
                    <label for="text_input_mon_email" class="sr-only">Adresse email</label>
                    <input type="text" name="text_input_mon_email" id="text_input_mon_email" placeholder="Adresse email" value=""/>
                    <?php echo form_error('text_input_mon_email','<div class="error">','</div>');?>
                </div>

                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Envoyer" />
                </p>

                <p class="login-back-link">
                    <a href="<?php echo base_url();?>signup/login">Retour à la connexion</a>
                </p>

                <?php echo form_close();?>
            </div>
        </div>
    </div>
</div>

<footer>
    <p class="footer"><?php if(isset($footing)) echo $footing;?></p>
</footer>
</body>
</html>
