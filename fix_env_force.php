<?php
/**
 * Script de correction FORCÉE du fichier .env
 * Force la correction de VERSION_DU_SOFT même si elle semble déjà correcte
 */

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    die("❌ Fichier .env non trouvé\n");
}

echo "📄 Lecture du fichier .env...\n\n";

$content = file_get_contents($envFile);
$lines = explode("\n", $content);

echo "🔍 Analyse des lignes:\n";
$versionLineFound = false;
$versionLineNum = -1;

foreach ($lines as $num => $line) {
    if (preg_match('/^VERSION_DU_SOFT\s*=/i', $line)) {
        $versionLineFound = true;
        $versionLineNum = $num;
        echo "Ligne " . ($num + 1) . ": " . trim($line) . "\n";
    }
}

if (!$versionLineFound) {
    echo "\n⚠️  Ligne VERSION_DU_SOFT non trouvée. Ajout à la fin du fichier...\n";
    $lines[] = 'VERSION_DU_SOFT="Version 5.0"';
} else {
    echo "\n🔧 Correction de la ligne " . ($versionLineNum + 1) . "...\n";
    // Forcer la correction - remplacer toute la ligne
    $lines[$versionLineNum] = 'VERSION_DU_SOFT="Version 5.0"';
    echo "✅ Ligne corrigée: " . $lines[$versionLineNum] . "\n";
}

// Écrire le fichier
file_put_contents($envFile, implode("\n", $lines));

echo "\n✅ Fichier .env sauvegardé !\n\n";

// Vérification
echo "📋 Vérification finale:\n";
$newContent = file_get_contents($envFile);
foreach (explode("\n", $newContent) as $num => $line) {
    if (preg_match('/VERSION_DU_SOFT/i', $line)) {
        echo "Ligne " . ($num + 1) . ": " . trim($line) . "\n";
        if (preg_match('/VERSION_DU_SOFT\s*=\s*["\']Version 5.0["\']/', $line)) {
            echo "  ✅ Format correct avec guillemets\n";
        } else {
            echo "  ❌ Format incorrect\n";
        }
    }
}

echo "\n✨ Terminé ! Testez maintenant: https://localhost/rezopcinline-ci4/public/\n";

