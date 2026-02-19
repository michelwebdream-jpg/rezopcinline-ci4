<?php
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?= base_url() ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?= base_url() ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
	<title><?= esc($titre ?? 'REZO+ PC INLINE') ?></title>
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
<body class="page-signup">

<div id="container" class="signup-container">
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

    <div class="login-panel-wrapper signup-panel-wrapper">
        

        <div class="login-panel signup-panel">
        
            <div class="login-logo-top">
                <a href="<?= base_url(); ?>" class="login-logo-link" title="Accueil"><img border="0" alt="Rezo+ pc inline" src="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png" width="80" height="80"></a>
            </div>
            
            <h2 class="login-panel-title">Créer un compte</h2>

            <?php if(isset($error)):?>
            <div class="error"><?php echo $error;?></div>
            <?php endif;?>

            <div class="login-form-box signup-form-box">
                <p class="login-helper-text signup-intro">
                    Veuillez remplir tous les champs ci-dessous pour créer votre compte REZO+ PC Inline. Une clé de licence valide est nécessaire. Si vous n'en possédez pas, vous pouvez en acquérir une <a href="http://www.web-dream.fr/app/downloads/cle-de-licence-rezo-pc-inline-1-an/" target="_blank" rel="noopener">ici</a>
                </p>
                <hr class="login-separator">
                <p class="login-helper-text" style="margin-bottom:12px;"><em>Tous les champs sont obligatoires.</em></p>

                <?php echo form_open('signup');?>

                <div class="signup-fields-grid">
                    <div class="signup-field signup-field--user">
                        <input type="text" name="text_input_mon_nom" id="text_input_mon_nom" placeholder="Mon nom" value="<?php echo set_value('text_input_mon_nom');?>"/>
                        <?php echo form_error('text_input_mon_nom','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--user">
                        <input type="text" name="text_input_mon_prenom" id="text_input_mon_prenom" placeholder="Mon prénom" value="<?php echo set_value('text_input_mon_prenom');?>"/>
                        <?php echo form_error('text_input_mon_prenom','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--phone">
                        <input type="text" name="text_input_mon_telephone" id="text_input_mon_telephone" placeholder="Mon téléphone" value="<?php echo set_value('text_input_mon_telephone');?>"/>
                        <?php echo form_error('text_input_mon_telephone','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--email signup-field--email-first">
                        <input type="text" name="text_input_mon_mail" id="text_input_mon_mail" placeholder="Mon e-mail" value="<?php echo set_value('text_input_mon_mail');?>"/>
                        <span class="signup-field-hint">(ATTENTION : il doit être identique à l'email d'achat)</span>
                        <?php echo form_error('text_input_mon_mail','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--email">
                        <input type="text" name="text_input_mon_mail2" id="text_input_mon_mail2" placeholder="Confirmer votre e-mail" value="<?php echo set_value('text_input_mon_mail2');?>"/>
                        <?php echo form_error('text_input_mon_mail2','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--indicatif">
                        <input type="text" name="text_input_mon_indicatif" id="text_input_mon_indicatif" placeholder="Mon indicatif" value="<?php echo set_value('text_input_mon_indicatif');?>"/>
                        <?php echo form_error('text_input_mon_indicatif','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--pass">
                        <input type="password" name="text_input_mon_password1" id="text_input_mon_password1" placeholder="Mon mot de passe" value="<?php echo set_value('text_input_mon_password1');?>"/>
                        <?php echo form_error('text_input_mon_password1','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--pass">
                        <input type="password" name="text_input_mon_password2" id="text_input_mon_password2" placeholder="Confirmer le mot de passe" value="<?php echo set_value('text_input_mon_password2');?>"/>
                        <?php echo form_error('text_input_mon_password2','<div class="error">','</div>');?>
                    </div>
                    <div class="signup-field signup-field--key signup-field--full">
                        <input type="text" name="text_input_cle_de_licence" id="text_input_cle_de_licence" placeholder="Ma clé de licence" value="<?php echo set_value('text_input_cle_de_licence');?>"/>
                        <?php echo form_error('text_input_cle_de_licence','<div class="error">','</div>');?>
                    </div>
                </div>

                <?php $currentMarker = (int) set_value('marker', 1); ?>
                <input type="hidden" name="marker" id="marker" value="<?= $currentMarker ?>" />

                <div class="signup-marker-preview-row">
                    <div class="signup-marker-preview">
                        <img src="<?= base_url(); ?>images/marker_user_<?= $currentMarker ?>.png" alt="Icône sélectionnée" id="marker-preview-img">
                    </div>
                    <button type="button" class="signup-marker-button" id="marker-choose-btn">Choisir mon icône</button>
                </div>

                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Valider" />
                </p>
                <?php echo form_close();?>
            </div>

            <p class="login-back-link signup-back-link">
                <a href="<?= base_url(); ?>signup/login">Me connecter à REZO+ PC Inline</a>
            </p>
        </div>
    </div>
</div>

<div class="signup-marker-modal" id="signup-marker-modal" aria-hidden="true">
    <div class="signup-marker-modal-backdrop" data-marker-close></div>
    <div class="signup-marker-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="signup-marker-modal-title">
        <h3 id="signup-marker-modal-title">Choisir mon icône</h3>
        <p class="signup-marker-modal-text">Cliquez sur une icône pour la sélectionner.</p>
        <div class="signup-marker-grid">
            <?php for ($i = 1; $i <= 27; $i++): ?>
                <button type="button" class="signup-marker-cell signup-marker-choice" data-marker="<?= $i ?>">
                    <img src="<?= base_url(); ?>images/marker_user_<?= $i ?>.png" alt="Icône <?= $i ?>">
                </button>
            <?php endfor; ?>
        </div>
        <button type="button" class="signup-marker-close" data-marker-close>Fermer</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var baseUrl = '<?= base_url(); ?>';
    var markerInput = document.getElementById('marker');
    var previewImg = document.getElementById('marker-preview-img');
    var openBtn = document.getElementById('marker-choose-btn');
    var modal = document.getElementById('signup-marker-modal');

    if (!markerInput || !previewImg || !openBtn || !modal) {
        return;
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    openBtn.addEventListener('click', openModal);

    modal.querySelectorAll('[data-marker-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    modal.querySelectorAll('.signup-marker-choice').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var value = this.getAttribute('data-marker');
            if (!value) return;
            markerInput.value = value;
            previewImg.src = baseUrl + 'images/marker_user_' + value + '.png';
            closeModal();
        });
    });
});
</script>

<footer>
    <p class="footer"><?php if(isset($footing)) echo $footing;?></p>
</footer>
</body>
</html>
