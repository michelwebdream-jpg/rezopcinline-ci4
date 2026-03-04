<?php
$titre = $titre ?? 'REZO+ PC INLINE | Compte créé';
$heading = $heading ?? 'Bienvenue dans REZO+ PC InLine';
$footing = $footing ?? footer_html();
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v=' . $assetVersion) ?>"/>
	<title><?= esc($titre) ?></title>
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
<body class="page-signup page-succes-creation">

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

            <h2 class="login-panel-title">Compte créé</h2>

            <div class="login-form-box signup-form-box succes-creation-box">
                <p class="login-helper-text succes-creation-intro"><strong>Votre compte vient d'être créé.</strong><br />Notez bien votre code d'identification REZO+ :</p>
                <p class="succes-creation-code"><?= esc($code_administrateur ?? '') ?></p>
                <p class="login-helper-text">Ce code sert aussi pour les applications REZO+ et REZO+ PC pour iPhone/iPad et Android.</p>
                <hr class="login-separator">
                <p class="login-helper-text">Votre clé de licence est valable jusqu'au : <strong><?= esc($date_fin_validite_licence ?? '') ?></strong></p>
                <p class="login-helper-text">Un mail de confirmation vient de vous être envoyé.</p>
                <p class="login-helper-text">Vous pouvez maintenant vous connecter à REZO+ PC InLine.</p>
                <p class="login-submit">
                    <a href="<?= base_url(); ?>signup/login" class="login-submit-link">Me connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>

<footer>
    <p class="footer"><?= $footing ?></p>
</footer>
</body>
</html>
