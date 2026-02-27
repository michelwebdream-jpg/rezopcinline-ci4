<?php
$utilisateur = $utilisateur ?? [];
$icon = (int) ($utilisateur['icone_administrateur'] ?? 1);
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?= base_url(); ?>images/icone_final_rezo_plus_PC_inline128.png">
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
	<title><?= esc($titre ?? 'REZO+ PC INLINE | Mon compte') ?></title>
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
<body class="page-mon-compte">

<div id="container" class="signup-container mon-compte-container">
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
            <h2 class="login-panel-title">Mon compte</h2>

            <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
            <?php endif; ?>
            <?php if (isset($succes)): ?>
            <div class="succes"><?= $succes ?></div>
            <?php endif; ?>
            <?php if (isset($succes_licence)): ?>
            <div class="succes"><?= $succes_licence ?></div>
            <?php endif; ?>
            <?php if (isset($error_licence)): ?>
            <div class="error"><?= $error_licence ?></div>
            <?php endif; ?>

            <div class="login-form-box signup-form-box">
                <p class="login-helper-text">Vous pouvez modifier certaines informations de votre compte.</p>
                <hr class="login-separator">

                <?= form_open('mon_compte') ?>

                <div class="signup-fields-grid">
                    <div class="signup-field signup-field--user">
                        <input type="text" name="text_input_mon_nom" id="text_input_mon_nom" placeholder="Mon nom" value="<?= esc(set_value('text_input_mon_nom', $utilisateur['nom_administrateur'] ?? '')) ?>"/>
                        <?= form_error('text_input_mon_nom', '<div class="error">', '</div>') ?>
                    </div>
                    <div class="signup-field signup-field--user">
                        <input type="text" name="text_input_mon_prenom" id="text_input_mon_prenom" placeholder="Mon prénom" value="<?= esc(set_value('text_input_mon_prenom', $utilisateur['prenom_administrateur'] ?? '')) ?>"/>
                        <?= form_error('text_input_mon_prenom', '<div class="error">', '</div>') ?>
                    </div>
                    <div class="signup-field signup-field--phone">
                        <input type="text" name="text_input_mon_telephone" id="text_input_mon_telephone" placeholder="Mon téléphone" value="<?= esc(set_value('text_input_mon_telephone', $utilisateur['telephone_administrateur'] ?? '')) ?>"/>
                        <?= form_error('text_input_mon_telephone', '<div class="error">', '</div>') ?>
                    </div>
                    <div class="signup-field signup-field--email signup-field--email-first">
                        <input type="text" name="text_input_mon_mail" value="<?= esc($utilisateur['mail_administrateur'] ?? '') ?>" disabled placeholder="Mon e-mail"/>
                        <span class="signup-field-hint signup-field-hint--muted">(non modifiable)</span>
                    </div>
                    <div class="signup-field signup-field--indicatif">
                        <input type="text" name="text_input_mon_indicatif" id="text_input_mon_indicatif" placeholder="Mon indicatif" value="<?= esc(set_value('text_input_mon_indicatif', $utilisateur['indicatif_administrateur'] ?? '')) ?>"/>
                        <?= form_error('text_input_mon_indicatif', '<div class="error">', '</div>') ?>
                    </div>
                    <div class="signup-field signup-field--key">
                        <input type="text" name="text_input_mon_code" value="<?= esc($utilisateur['code_administrateur'] ?? '') ?>" disabled placeholder="Mon code REZO+ PC Inline"/>
                        <span class="signup-field-hint signup-field-hint--muted">(non modifiable)</span>
                    </div>
                    <div class="signup-field signup-field--key signup-field--full">
                        <input type="text" name="text_input_validite_cle_de_licence" value="<?= esc($utilisateur['date_fin_validite_licence'] ?? '') ?>" disabled placeholder="Validité de ma clé de licence"/>
                        <span class="signup-field-hint signup-field-hint--muted">(non modifiable)</span>
                    </div>
                </div>

                <input type="hidden" name="marker" id="marker" value="<?= (int) set_value('marker', $icon) ?>" />

                <div class="signup-marker-preview-row">
                    <div class="signup-marker-preview">
                        <img src="<?= base_url(); ?>images/marker_user_<?= (int) set_value('marker', $icon) ?>.png" alt="Icône sélectionnée" id="marker-preview-img">
                    </div>
                    <button type="button" class="signup-marker-button" id="marker-choose-btn">Choisir mon icône</button>
                </div>

                <p class="login-submit">
                    <input id="button_submit" type="submit" value="Valider" />
                </p>
                <?= form_close() ?>
            </div>

            <hr class="login-separator">
            <h3 class="login-panel-subtitle">Mettre à jour ma licence</h3>
            <p class="login-helper-text">Si vous avez une nouvelle clé de licence (ex. après un achat 1 an ou 6 mois), vous pouvez l’associer à ce compte.</p>
            <?= form_open('mon_compte') ?>
            <input type="hidden" name="update_licence_submit" value="1" />
            <div class="signup-fields-grid" style="max-width: 100%;">
                <div class="signup-field signup-field--key">
                    <input type="password" name="update_licence_mot_de_passe" id="update_licence_mot_de_passe" placeholder="Votre mot de passe" value=""/>
                </div>
                <div class="signup-field signup-field--key">
                    <input type="text" name="update_licence_nouvelle_cle" id="update_licence_nouvelle_cle" placeholder="Nouvelle clé de licence" value="<?= esc(set_value('update_licence_nouvelle_cle')) ?>"/>
                </div>
            </div>
            <?= form_error('update_licence_mot_de_passe', '<div class="error">', '</div>') ?>
            <?= form_error('update_licence_nouvelle_cle', '<div class="error">', '</div>') ?>
            <p class="login-submit">
                <input type="submit" value="Associer cette clé" />
            </p>
            <?= form_close() ?>

            <p class="login-back-link signup-back-link">
                <?= anchor('modification_password', 'Modifier mon mot de passe', 'target="_blank"') ?>
            </p>
        </div>
    </div>
</div>

<div class="signup-marker-modal" id="mon-compte-marker-modal" aria-hidden="true">
    <div class="signup-marker-modal-backdrop" data-marker-close></div>
    <div class="signup-marker-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="mon-compte-marker-modal-title">
        <h3 id="mon-compte-marker-modal-title">Choisir mon icône</h3>
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
    var modal = document.getElementById('mon-compte-marker-modal');

    if (!markerInput || !previewImg || !openBtn || !modal) return;

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
    <p class="footer"><?= $footing ?? '' ?></p>
</footer>
</body>
</html>
