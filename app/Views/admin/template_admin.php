<?php
$utilisateur = $utilisateur ?? [];
$content = $content ?? 'admin/dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?= base_url('images/icone_final_rezo_plus_PC_inline128.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
    <title><?= esc($titre ?? 'Administration') ?></title>
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 220px; background: #1e293b; color: #e2e8f0;
            padding: 1rem 0; flex-shrink: 0;
        }
        .admin-sidebar h2 {
            font-size: 1rem; margin: 0 1rem 1rem; padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.2); color: #94a3b8;
        }
        .admin-sidebar a {
            display: block; padding: 0.6rem 1rem; color: #e2e8f0; text-decoration: none;
            transition: background .2s;
        }
        .admin-sidebar a:hover { background: rgba(255,255,255,0.1); }
        .admin-sidebar a.active { background: #334155; font-weight: 500; }
        .admin-main {
            flex: 1; padding: 2rem; background: #f1f5f9; overflow: auto;
        }
        .admin-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
        }
        .admin-header h1 { margin: 0; font-size: 1.5rem; color: #334155; }
        .admin-user { font-size: 0.9rem; color: #64748b; }
        .admin-user a { color: #0ea5e9; }
        .admin-card {
            background: #fff; border-radius: 8px; padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2>Administration</h2>
            <a href="<?= base_url('admin') ?>" class="<?= ($content === 'admin/dashboard') ? 'active' : '' ?>">Dashboard</a>
            <a href="<?= base_url('signup/membres') ?>">← Retour à l'app</a>
            <a href="<?= base_url('admin/connected') ?>" class="<?= ($content === 'admin/connected') ? 'active' : '' ?>">Utilisateurs connectés</a>
            <a href="<?= base_url('admin/login-notices') ?>" class="<?= (strpos($content ?? '', 'admin/login_notices') === 0) ? 'active' : '' ?>">Annonces page de connexion</a>
            <a href="<?= base_url('admin/deploy') ?>" class="<?= (strpos($content ?? '', 'admin/deploy') === 0) ? 'active' : '' ?>">Déploiement</a>
            <a href="<?= base_url('signup/logout') ?>">Déconnexion</a>
        </aside>
        <main class="admin-main">
            <div class="admin-header">
                <h1><?= esc($titre ?? 'Administration') ?></h1>
                <div class="admin-user">
                    <?= esc($utilisateur['nom_administrateur'] ?? '') ?> <?= esc($utilisateur['prenom_administrateur'] ?? '') ?>
                    — <a href="<?= base_url('signup/logout') ?>">Déconnexion</a>
                </div>
            </div>
            <?= view($content, get_defined_vars()) ?>
        </main>
    </div>
</body>
</html>
