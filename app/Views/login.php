<?php
// CI4: Plus besoin de BASEPATH check
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>

    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
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
            <?php
            $login_notices = $login_notices ?? [];
            $login_notice_duration = (int) ($login_notice_duration ?? 8);
            if (!empty($login_notices)):
                $duration_ms = $login_notice_duration * 1000;
                $first = $login_notices[0];
                $notices_json = htmlspecialchars(json_encode(array_map(function ($n) {
                    return ['title' => $n['title'] ?? '', 'content' => $n['content'] ?? ''];
                }, $login_notices), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="login-hero-notice-block" id="login-hero-notice-block" data-notices="<?= $notices_json ?>" data-duration="<?= (int) $duration_ms ?>">
                <div class="login-hero-notice-wrap">
                    <div class="login-hero-notice">
                        <span class="login-hero-notice-title" id="login-hero-notice-title"><?= esc($first['title'] ?? '') ?></span>
                        <div class="login-hero-notice-body" id="login-hero-notice-body"><?= $first['content'] ?? '' ?></div>
                    </div>
                </div>
                <?php if (count($login_notices) > 1): ?>
                <div class="login-hero-notice-bullets" id="login-hero-notice-bullets" aria-hidden="true">
                    <?php foreach ($login_notices as $i => $notice): ?>
                    <span class="login-hero-notice-bullet<?= $i === 0 ? ' login-hero-notice-bullet-active' : '' ?>" data-index="<?= (int) $i ?>"></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
<script>
(function() {
    var block = document.getElementById('login-hero-notice-block');
    if (!block) return;
    var dataNotices = block.getAttribute('data-notices');
    if (!dataNotices) return;
    var notices;
    try { notices = JSON.parse(dataNotices); } catch (e) { return; }
    if (!notices.length) return;
    var wrap = block.querySelector('.login-hero-notice-wrap');
    var noticeEl = block.querySelector('.login-hero-notice');
    var titleEl = document.getElementById('login-hero-notice-title');
    var bodyEl = document.getElementById('login-hero-notice-body');
    if (!titleEl || !bodyEl || !wrap || !noticeEl) return;
    var bullets = document.getElementById('login-hero-notice-bullets');
    var bulletEls = bullets ? bullets.querySelectorAll('.login-hero-notice-bullet') : [];
    var duration = parseInt(block.getAttribute('data-duration') || '8000', 10);
    var current = 0;
    var isTransitioning = false;

    function applyContent(index) {
        var n = notices[index];
        titleEl.textContent = n.title || '';
        bodyEl.innerHTML = n.content || '';
        bulletEls.forEach(function(el, i) { el.classList.toggle('login-hero-notice-bullet-active', i === index); });
    }

    function setNotice(index) {
        index = index % notices.length;
        if (index === current && notices.length === 1) return;
        if (notices.length === 1) {
            current = index;
            applyContent(current);
            return;
        }
        if (isTransitioning) return;
        isTransitioning = true;
        var nextIndex = index;

        wrap.style.height = wrap.offsetHeight + 'px';
        noticeEl.classList.add('login-hero-notice-fade-out');

        function onFadeEnd() {
            noticeEl.removeEventListener('transitionend', onFadeEnd);
            current = nextIndex;
            applyContent(current);
            var newHeight = wrap.scrollHeight;
            wrap.style.height = newHeight + 'px';
            noticeEl.classList.remove('login-hero-notice-fade-out');

            function finishHeight() {
                wrap.style.height = '';
                isTransitioning = false;
            }
            var heightTimeout = setTimeout(finishHeight, 400);
            wrap.addEventListener('transitionend', function te(e) {
                if (e.propertyName !== 'height') return;
                wrap.removeEventListener('transitionend', te);
                clearTimeout(heightTimeout);
                finishHeight();
            });
        }
        noticeEl.addEventListener('transitionend', function te(e) {
            if (e.propertyName !== 'opacity') return;
            noticeEl.removeEventListener('transitionend', te);
            onFadeEnd();
        });
    }

    if (notices.length > 1) {
        setInterval(function() { setNotice(current + 1); }, duration);
        bulletEls.forEach(function(el, i) {
            el.addEventListener('click', function() { setNotice(i); });
        });
    }
})();
</script>
</body>
</html>