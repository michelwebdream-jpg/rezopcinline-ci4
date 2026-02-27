<?php
$validation = $validation ?? null;
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v=' . $assetVersion) ?>"/>
	<title><?= esc($titre ?? 'REZO+ PC INLINE | Associer une nouvelle clé') ?></title>
</head>
<body class="page-login">

<div id="container" class="login-container">
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

            <h2 class="login-panel-title">Associer une nouvelle clé</h2>

            <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <p class="login-helper-text">
                Vous pouvez associer ici une nouvelle clé si vous avez acheté une licence avec une clé <strong>différente</strong>. Si vous avez simplement renouvelé votre licence existante en saisissant votre clé actuelle lors du paiement sur la boutique, une connexion normale suffira : la nouvelle date de validité sera automatiquement prise en compte.
            </p>
            <hr class="login-separator">

            <?= form_open('signup/associer_cle') ?>
            <div class="login-form-box">
                <div class="login-field login-field--user">
                    <label for="code" class="sr-only">Code REZO+</label>
                    <input type="text" name="code" id="code" placeholder="Code REZO+" value="<?= esc(set_value('code')) ?>"/>
                    <?= form_error('code', '<div class="error">', '</div>') ?>
                </div>
                <div class="login-field login-field--pass">
                    <label for="pass" class="sr-only">Mot de passe</label>
                    <input type="password" name="pass" id="pass" placeholder="Mot de passe" value="<?= esc(set_value('pass')) ?>"/>
                    <?= form_error('pass', '<div class="error">', '</div>') ?>
                </div>
                <div class="login-field login-field--pass">
                    <label for="nouvelle_cle" class="sr-only">Nouvelle clé de licence</label>
                    <input type="text" name="nouvelle_cle" id="nouvelle_cle" placeholder="Nouvelle clé de licence" value="<?= esc(set_value('nouvelle_cle')) ?>"/>
                    <?= form_error('nouvelle_cle', '<div class="error">', '</div>') ?>
                </div>
                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Associer cette clé" />
                </p>
            </div>
            <?= form_close() ?>

            <div class="login-panel-actions">
                <a href="<?= base_url(); ?>signup/login" class="login-secondary-btn">Retour à la connexion</a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p class="footer"><?= $footing ?? '' ?></p>
</footer>
</body>
</html>
