<?php
/**
 * Script pour corriger le fichier .env
 * Ajoute des guillemets autour des valeurs contenant des espaces
 */

$envFile = __DIR__ . '/.env';

if (!file_exists($envFile)) {
    echo "❌ Fichier .env non trouvé\n";
    exit(1);
}

$content = file_get_contents($envFile);
$lines = explode("\n", $content);
$fixed = [];
$changed = false;

foreach ($lines as $line) {
    $original = $line;
    
    // Ignorer les commentaires et les lignes vides
    if (preg_match('/^\s*#/', $line) || trim($line) === '') {
        $fixed[] = $line;
        continue;
    }
    
    // Vérifier si c'est une ligne de configuration (KEY=VALUE)
    if (preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
        $key = $matches[1];
        $value = trim($matches[2]);
        
        // Si la valeur contient des espaces et n'est pas déjà entre guillemets
        if (preg_match('/\s/', $value) && !preg_match('/^["\'].*["\']$/', $value)) {
            $value = '"' . $value . '"';
            $line = $key . '=' . $value;
            $changed = true;
            echo "✅ Corrigé: $key=$value\n";
        }
    }
    
    $fixed[] = $line;
}

if ($changed) {
    file_put_contents($envFile, implode("\n", $fixed));
    echo "\n✅ Fichier .env corrigé avec succès !\n";
} else {
    echo "✅ Aucune correction nécessaire\n";
}

