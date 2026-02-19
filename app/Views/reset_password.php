<?php
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
	<title><?php echo $titre ?? 'Réinitialisation du mot de passe'; ?></title>
</head>
<body>

<div id="container">
    <p style="text-align:center;">
    <a href="<?php echo base_url();?>"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png" width="100" height="100"></a>
    </p>
    <p style="font-size:10px;text-align:center;"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.1' ?></p>

	<div id="container_formulaire">
        <div id="formulaire">
            <p style="font-size:18px;text-align:center;">Réinitialisation du mot de passe</p>
            <?php if (!empty($error)): ?>
            <div class="error"><?php echo esc($error); ?></div>
            <?php endif; ?>
            <p style="text-align:center;">Choisissez un nouveau mot de passe (au moins 5 caractères).</p>
            <hr/>
            <div style="height:15px;"></div>

            <?php echo form_open('signup/reset_password'); ?>
                <input type="hidden" name="token" value="<?php echo esc($token ?? ''); ?>" />
                <label for="text_input_mon_password_new">Nouveau mot de passe</label>
                <input type="password" name="text_input_mon_password_new" id="text_input_mon_password_new" value="" autocomplete="new-password" />
                <?php echo form_error('text_input_mon_password_new', '<div class="error">', '</div>'); ?>

                <label for="text_input_mon_password_confirm">Confirmer le mot de passe</label>
                <input type="password" name="text_input_mon_password_confirm" id="text_input_mon_password_confirm" value="" autocomplete="new-password" />
                <?php echo form_error('text_input_mon_password_confirm', '<div class="error">', '</div>'); ?>

                <p style="margin-top:20px;"><input id="button_submit" type="submit" value="Réinitialiser le mot de passe" /></p>
            <?php echo form_close(); ?>

            <p style="text-align:center;font-size:12px;"><a href="<?php echo base_url(); ?>signup/login">Retour à la connexion</a></p>
        </div>
        <div style="height:30px;"></div>
	</div>
</div>

<footer>
    <p class="footer"><?php if (isset($footing)) echo $footing; ?></p>
</footer>
</body>
</html>
