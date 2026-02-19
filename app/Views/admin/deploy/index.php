<?php
$git = $git ?? [];
$config_ok = $config_ok ?? false;
$remote_base = $remote_base ?? '';
$remote_base_real = $remote_base_real ?? '';
$ftp_error = $ftp_error ?? null;
$deploy_output = $deploy_output ?? '';
$files = $git['files'] ?? [];
$error = $git['error'] ?? '';
$project_root = $project_root ?? '';
$dist_base = $remote_base_real !== '' ? $remote_base_real : $remote_base;
?>
<div class="admin-card">
    <h2 style="margin-top: 0;">Déploiement sur la production</h2>
    <p style="color: #64748b; margin-bottom: 1rem;">
        Envoie sur le serveur de production (FTP) uniquement les fichiers modifiés depuis le début de la branche active (par rapport à <code><?= esc($git['ref_branch'] ?? 'main') ?></code>).
        Cette page n’est disponible qu’en local.
    </p>

    <?php if (!$config_ok): ?>
        <div style="padding: 1rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; margin-bottom: 1rem;">
            <strong>Configuration FTP manquante</strong>
            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                Ajoutez dans votre fichier <code>.env</code> : <code>PRODUCTION_FTP_HOST</code>, <code>PRODUCTION_FTP_USER</code>, <code>PRODUCTION_FTP_PATH</code>.
                Pour lancer le déploiement depuis cette page, définissez aussi <code>PRODUCTION_FTP_PASSWORD</code> (optionnel en ligne de commande).
            </p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color: #b91c1c; margin-bottom: 1rem;"><?= esc($error) ?></p>
    <?php else: ?>
        <div style="margin-bottom: 1rem;">
            <p style="margin: 0 0 0.25rem 0;"><strong>Branche active</strong> : <code><?= esc($git['branch'] ?? '') ?></code></p>
            <p style="margin: 0 0 0.25rem 0;"><strong>Référence</strong> : <code><?= esc($git['ref_branch'] ?? 'main') ?></code></p>
            <p style="margin: 0 0 0.25rem 0;"><strong>Fichiers modifiés</strong> : <?= count($files) ?></p>
        </div>

        <?php if (count($files) === 0): ?>
            <p style="color: #64748b;">Aucun fichier modifié depuis le début de la branche. Rien à déployer.</p>
        <?php else: ?>
            <div style="margin-bottom: 1rem;">
                <strong>Dry-run — Fichiers qui seraient envoyés :</strong>
                <?php if ($ftp_error !== null && $ftp_error !== ''): ?>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #b45309;">Chemin distant (config) : connexion FTP non effectuée — <?= esc($ftp_error) ?></p>
                <?php elseif ($remote_base_real !== ''): ?>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #0f766e;">Chemin distant : chemin réel sur le serveur FTP (PWD).</p>
                <?php endif; ?>
                <div style="overflow-x: auto; max-height: 360px; overflow-y: auto; margin-top: 0.5rem;">
                    <table style="width: 100%; border-collapse: collapse; font-family: monospace; font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Chemin local</th>
                                <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Chemin distant</th>
                                <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc; white-space: nowrap;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $f): ?>
                                <tr>
                                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; vertical-align: top;"><?= esc($project_root ? $project_root . '/' . $f : $f) ?></td>
                                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; vertical-align: top;"><?= esc($dist_base !== '' ? $dist_base . '/' . $f : '—') ?></td>
                                    <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; vertical-align: top;">
                                        <?php if ($config_ok): ?>
                                            <?= form_open(base_url('admin/deploy/file'), ['style' => 'display:inline;']) ?>
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="file" value="<?= esc($f, 'attr') ?>">
                                            <button type="submit" onclick="return confirm('Envoyer uniquement ce fichier sur le serveur de production ?');" style="padding: 3px 8px; font-size: 0.8rem; background: #0369a1; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
                                                Envoyer
                                            </button>
                                            <?= form_close() ?>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: #9ca3af;">FTP non configuré</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($config_ok): ?>
                <p style="font-size: 0.9rem; color: #64748b; margin: 1rem 0 0 0;">Aucun fichier n’est envoyé sur le serveur tant que vous n’avez pas cliqué sur le bouton ci-dessous.</p>
                <?= form_open(base_url('admin/deploy/run'), ['style' => 'margin-top: 0.5rem;']) ?>
                <?= csrf_field() ?>
                <button type="submit" onclick="return confirm('Envoyer ces fichiers sur le serveur de production ?');" style="padding: 8px 20px; background: #b91c1c; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                    Déployer sur la production
                </button>
                <?= form_close() ?>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($deploy_output !== ''): ?>
<div class="admin-card" style="margin-top: 1.5rem;">
    <h3 style="margin-top: 0;">Résultat du dernier déploiement</h3>
    <pre style="margin: 0; padding: 1rem; background: #1e293b; color: #e2e8f0; border-radius: 6px; overflow-x: auto; font-size: 0.85rem; white-space: pre-wrap; word-break: break-all;"><?= esc($deploy_output) ?></pre>
</div>
<?php endif; ?>

<div class="admin-card" style="margin-top: 1rem;">
    <h3 style="margin-top: 0; font-size: 1rem;">Configuration (.env)</h3>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
        Variables optionnelles : <code>REF_BRANCH</code> (défaut : main), <code>PRODUCTION_FTP_SSL</code> (true pour FTPS), <code>PRODUCTION_FTP_PORT</code> (défaut 21).
        Le chemin distant affiché en « réel » est obtenu par connexion FTP (PWD) si <code>PRODUCTION_FTP_PASSWORD</code> est défini.
        Le script utilisé est <code>deploy-branch-changes.sh</code> à la racine du projet.
    </p>
</div>
