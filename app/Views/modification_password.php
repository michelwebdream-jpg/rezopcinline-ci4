<?php
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
	<title><?= esc($titre ?? 'REZO+ PC INLINE | Modifier mon mot de passe') ?></title>
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
<body class="page-modification-password">

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
                <a href="<?= base_url(); ?>" class="login-logo-link" title="Accueil"><img border="0" alt="Rezo+ pc inline" src="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png" width="80" height="80"></a>
            </div>

            <h2 class="login-panel-title">Modifier mon mot de passe</h2>

            <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <?php if (isset($succes)): ?>
            <div class="succes"><?= $succes ?></div>
            <?php endif; ?>

            <div class="login-form-box">
                <p class="login-helper-text" style="margin-bottom:12px;">
                    Saisissez votre mot de passe actuel puis le nouveau (5 caractères minimum).
                </p>

                <?= form_open('modification_password') ?>

                <div class="login-field login-field--pass">
                    <label for="text_input_mon_password_actuel" class="sr-only">Mot de passe actuel</label>
                    <input type="password" name="text_input_mon_password_actuel" id="text_input_mon_password_actuel" placeholder="Mot de passe actuel" value="<?= set_value('text_input_mon_password_actuel') ?>"/>
                    <?= form_error('text_input_mon_password_actuel', '<div class="error">', '</div>') ?>
                </div>

                <div class="login-field login-field--pass">
                    <label for="text_input_mon_password_new" class="sr-only">Nouveau mot de passe</label>
                    <input type="password" name="text_input_mon_password_new" id="text_input_mon_password_new" placeholder="Nouveau mot de passe (5 caractères min.)" value="<?= set_value('text_input_mon_password_new') ?>"/>
                    <?= form_error('text_input_mon_password_new', '<div class="error">', '</div>') ?>
                </div>

                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Valider" />
                </p>

                <p class="login-back-link">
                    <a href="<?= base_url(); ?>mon_compte">Retour à mon compte</a>
                </p>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <p class="footer"><?= $footing ?? '' ?></p>
</footer>
</body>
</html>
