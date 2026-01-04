#!/bin/bash
# Script pour corriger le format de l'entrée dans /etc/hosts

echo "🔧 Correction du format de /etc/hosts..."
echo ""

# Vérifier si l'entrée existe déjà
if grep -q "rezopcinline-ci4.local" /etc/hosts; then
    echo "✓ Entrée trouvée dans /etc/hosts"
    
    # Vérifier le format
    if grep -q "^127\.0\.0\.1.*rezopcinline-ci4\.local" /etc/hosts; then
        echo "✓ Format correct"
        exit 0
    else
        echo "⚠ Format incorrect détecté"
        echo ""
        echo "L'entrée doit être corrigée manuellement."
        echo ""
        echo "Veuillez exécuter cette commande :"
        echo "sudo sed -i '' 's/## Local - End ##127\.0\.0\.1 rezopcinline-ci4\.local/## Local - End ##\n127.0.0.1 rezopcinline-ci4.local/' /etc/hosts"
        echo ""
        echo "Ou éditez /etc/hosts manuellement et assurez-vous que cette ligne est sur une nouvelle ligne :"
        echo "127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local"
        exit 1
    fi
else
    echo "✗ Entrée absente de /etc/hosts"
    echo "Ajout de l'entrée..."
    echo "127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local" | sudo tee -a /etc/hosts
    echo "✓ Entrée ajoutée"
fi

echo ""
echo "Vérification du cache DNS..."
sudo killall -HUP mDNSResponder 2>/dev/null
echo "✓ Cache DNS vidé"

echo ""
echo "Test de résolution..."
if ping -c 1 rezopcinline-ci4.local > /dev/null 2>&1; then
    echo "✓ Résolution DNS fonctionne"
else
    echo "⚠ Résolution DNS échoue - attendez quelques secondes et réessayez"
fi

