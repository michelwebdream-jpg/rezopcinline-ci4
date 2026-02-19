<?php
$notice = $notice ?? null;
$isEdit = $notice !== null;
$id = $isEdit ? (int) $notice['id'] : 0;
$title = $isEdit ? ($notice['title'] ?? '') : '';
$content = $isEdit ? ($notice['content'] ?? '') : '';
$sort_order = $isEdit ? (int) ($notice['sort_order'] ?? 0) : 0;
$error = session('error');
?>
<div class="admin-card">
    <h2 style="margin-top: 0;"><?= $isEdit ? 'Modifier l\'annonce' : 'Ajouter une annonce' ?></h2>
    <?php if ($error): ?>
        <p style="color: #b91c1c; margin-bottom: 1rem;"><?= esc($error) ?></p>
    <?php endif; ?>

    <?= form_open(base_url('admin/login-notices/save'), ['id' => 'form-notice']) ?>
    <input type="hidden" name="id" value="<?= $id ?>" />
    <div style="margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Titre <span style="color: #b91c1c;">*</span></label>
        <input type="text" name="title" id="title" value="<?= esc($title) ?>" required maxlength="255" style="width: 100%; max-width: 400px; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 4px;" />
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="content" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Contenu</label>
        <div id="editor-wrap" style="min-height: 220px; background: #fff; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
        <textarea name="content" id="content" rows="4" style="display: none;"><?= esc($content) ?></textarea>
    </div>
    <div style="margin-bottom: 1rem;">
        <label for="sort_order" style="display: block; margin-bottom: 0.25rem; font-weight: 500;">Ordre d'affichage</label>
        <input type="number" name="sort_order" id="sort_order" value="<?= $sort_order ?>" min="0" style="width: 100px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px;" />
        <span style="color: #64748b; font-size: 0.9rem; margin-left: 0.5rem;">(plus le nombre est petit, plus l’annonce apparaît en premier)</span>
    </div>
    <div>
        <button type="submit" style="padding: 8px 18px; background: #0f766e; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">Enregistrer</button>
        <a href="<?= base_url('admin/login-notices') ?>" style="margin-left: 0.5rem; color: #64748b;">Annuler</a>
    </div>
    <?= form_close() ?>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('editor-wrap');
    if (!container) return;
    var editor = new Quill(container, {
        theme: 'snow',
        placeholder: 'Saisissez le contenu de l\'annonce…',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
    var contentField = document.getElementById('content');
    var html = contentField.value;
    if (html) editor.root.innerHTML = html;
    document.getElementById('form-notice').addEventListener('submit', function() {
        contentField.value = editor.root.innerHTML;
    });
});
</script>
