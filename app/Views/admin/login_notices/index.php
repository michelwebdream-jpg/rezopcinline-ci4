<?php
$notices = $notices ?? [];
$display_duration_seconds = (int) ($display_duration_seconds ?? 8);
$success = session('success');
$error = session('error');
?>
<div class="admin-card">
    <h2 style="margin-top: 0;">Annonces de la page de connexion</h2>
    <?php if ($success): ?>
        <p style="color: #0f766e; margin-bottom: 1rem;"><?= esc($success) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: #b91c1c; margin-bottom: 1rem;"><?= esc($error) ?></p>
    <?php endif; ?>

    <div style="margin-bottom: 1.5rem;">
        <h3 style="margin: 0 0 0.5rem 0; font-size: 1rem;">Durée d'affichage (secondes)</h3>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.5rem;">Temps d'affichage de chaque annonce avant passage à la suivante.</p>
        <?= form_open(base_url('admin/login-notices/save-config'), ['style' => 'display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;']) ?>
        <input type="number" name="display_duration_seconds" value="<?= (int) $display_duration_seconds ?>" min="1" max="300" style="width: 80px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px;" />
        <span style="color: #64748b;">secondes</span>
        <button type="submit" style="padding: 6px 14px; background: #334155; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Enregistrer</button>
        <?= form_close() ?>
    </div>

    <p style="margin-bottom: 1rem;">
        <a href="<?= base_url('admin/login-notices/add') ?>" style="display: inline-block; padding: 8px 16px; background: #0f766e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 500;">Ajouter une info</a>
    </p>

    <?php if (empty($notices)): ?>
        <p style="color: #64748b;">Aucune annonce. Le bloc ne s'affichera pas sur la page de connexion. Cliquez sur « Ajouter une info » pour en créer une.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Ordre</th>
                        <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Titre</th>
                        <th style="padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Aperçu</th>
                        <th style="padding: 0.5rem 0.75rem; text-align: right; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notices as $n): ?>
                        <tr>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= (int) ($n['sort_order'] ?? 0) ?></td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= esc($n['title'] ?? '') ?></td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; max-width: 280px; font-size: 0.9rem; color: #64748b;">
                                <?= esc(substr(strip_tags($n['content'] ?? ''), 0, 80)) ?>…
                            </td>
                            <td style="padding: 0.5rem 0.75rem; border-bottom: 1px solid #e2e8f0; text-align: right;">
                                <a href="<?= base_url('admin/login-notices/edit/' . (int) $n['id']) ?>" style="margin-right: 0.5rem; color: #0ea5e9;">Modifier</a>
                                <a href="<?= base_url('admin/login-notices/delete/' . (int) $n['id']) ?>" style="color: #b91c1c;" onclick="return confirm('Supprimer cette annonce ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
