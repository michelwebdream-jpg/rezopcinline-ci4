<?php
/**
 * Script de diagnostic et correction du fichier .env
 */

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ Fichier .env non trouvé à: $envFile\n";
    exit(1);
}

echo "📄 Lecture du fichier .env...\n\n";

$content = file_get_contents($envFile);
$lines = explode("\n", $content);

echo "🔍 Recherche de VERSION_DU_SOFT:\n";
$found = false;
foreach ($lines as $num => $line) {
    $lineNum = $num + 1;
    if (preg_match('/VERSION_DU_SOFT/i', $line)) {
        $found = true;
        echo "Ligne $lineNum: " . trim($line) . "\n";
        
        // Vérifier si elle a des guillemets
        if (preg_match('/VERSION_DU_SOFT\s*=\s*["\']/', $line)) {
            echo "  ✅ Déjà entre guillemets\n";
        } else {
            echo "  ❌ PAS entre guillemets - CORRECTION NÉCESSAIRE\n";
        }
    }
}

if (!$found) {
    echo "⚠️  Ligne VERSION_DU_SOFT non trouvée\n";
}

echo "\n🔧 Correction automatique...\n";

$fixed = [];
$changed = false;

foreach ($lines as $line) {
    $original = $line;
    
    // Corriger VERSION_DU_SOFT spécifiquement
    if (preg_match('/^VERSION_DU_SOFT\s*=\s*(.*)$/i', $line, $matches)) {
        $value = trim($matches[1]);
        // Si la valeur n'est pas entre guillemets
        if (!preg_match('/^["\'].*["\']$/', $value)) {
            $value = '"' . trim($value, '"\'') . '"';
            $line = 'VERSION_DU_SOFT=' . $value;
            $changed = true;
            echo "✅ Corrigé: $line\n";
        }
    }
    
    // Corriger toutes les autres valeurs avec espaces
    if (preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
        $key = $matches[1];
        $value = trim($matches[2]);
        
        // Ignorer les commentaires
        if (strpos($key, '#') === 0) {
            $fixed[] = $line;
            continue;
        }
        
        // Si la valeur contient des espaces et n'est pas entre guillemets
        if (preg_match('/\s/', $value) && !preg_match('/^["\'].*["\']$/', $value)) {
            $value = '"' . $value . '"';
            $line = $key . '=' . $value;
            if (!$changed) {
                $changed = true;
                echo "✅ Corrigé: $line\n";
            }
        }
    }
    
    $fixed[] = $line;
}

if ($changed) {
    file_put_contents($envFile, implode("\n", $fixed));
    echo "\n✅ Fichier .env corrigé et sauvegardé !\n";
    echo "\n📋 Vérification finale:\n";
    $newContent = file_get_contents($envFile);
    foreach (explode("\n", $newContent) as $num => $line) {
        if (preg_match('/VERSION_DU_SOFT/i', $line)) {
            echo "Ligne " . ($num + 1) . ": " . trim($line) . "\n";
        }
    }
} else {
    echo "✅ Aucune correction nécessaire\n";
}

echo "\n✨ Terminé !\n";

