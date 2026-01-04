#!/bin/bash
# Script de test pour le VirtualHost rezopcinline-ci4.local

echo "🔍 Test de configuration du VirtualHost"
echo "========================================"
echo ""

# Test 1 : Vérifier /etc/hosts
echo "1. Vérification de /etc/hosts..."
if grep -q "rezopcinline-ci4.local" /etc/hosts; then
    echo "   ✓ rezopcinline-ci4.local présent dans /etc/hosts"
else
    echo "   ✗ rezopcinline-ci4.local ABSENT de /etc/hosts"
    echo "   → Exécutez : sudo ./add_to_hosts.sh"
    exit 1
fi

# Test 2 : Vérifier la syntaxe Apache
echo ""
echo "2. Vérification de la syntaxe Apache..."
if /Applications/MAMP/Library/bin/httpd -t > /dev/null 2>&1; then
    echo "   ✓ Syntaxe Apache OK"
else
    echo "   ✗ Erreur de syntaxe Apache"
    /Applications/MAMP/Library/bin/httpd -t
    exit 1
fi

# Test 3 : Vérifier que le VirtualHost est chargé
echo ""
echo "3. Vérification du VirtualHost..."
if /Applications/MAMP/Library/bin/httpd -S 2>&1 | grep -q "rezopcinline-ci4.local"; then
    echo "   ✓ VirtualHost rezopcinline-ci4.local détecté"
else
    echo "   ✗ VirtualHost rezopcinline-ci4.local NON détecté"
    exit 1
fi

# Test 4 : Vérifier que le dossier public existe
echo ""
echo "4. Vérification du dossier public..."
if [ -f "/Applications/MAMP/htdocs/rezopcinline-ci4/public/index.php" ]; then
    echo "   ✓ index.php présent dans public/"
else
    echo "   ✗ index.php ABSENT dans public/"
    exit 1
fi

# Test 5 : Test de connexion HTTP (si Apache est en cours d'exécution)
echo ""
echo "5. Test de connexion HTTP..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -H "Host: rezopcinline-ci4.local" http://127.0.0.1/ 2>&1)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "301" ]; then
    echo "   ✓ Réponse HTTP $HTTP_CODE - Le site répond correctement"
elif [ "$HTTP_CODE" = "000" ]; then
    echo "   ⚠ Apache ne semble pas être en cours d'exécution"
    echo "   → Démarrez MAMP et réessayez"
else
    echo "   ⚠ Réponse HTTP $HTTP_CODE"
    echo "   → Vérifiez que MAMP est démarré et que Apache fonctionne"
fi

# Test 6 : Vérifier la baseURL
echo ""
echo "6. Vérification de la baseURL..."
BASEURL_ENV=$(grep "app.baseURL" .env 2>/dev/null | head -1)
BASEURL_PHP=$(grep "baseURL" app/Config/App.php 2>/dev/null | grep -v "^[[:space:]]*//" | head -1)

if echo "$BASEURL_ENV" | grep -q "rezopcinline-ci4.local"; then
    echo "   ✓ baseURL dans .env : $BASEURL_ENV"
else
    echo "   ⚠ baseURL dans .env ne contient pas rezopcinline-ci4.local"
fi

if echo "$BASEURL_PHP" | grep -q "rezopcinline-ci4.local"; then
    echo "   ✓ baseURL dans App.php : $(echo $BASEURL_PHP | sed 's/.*baseURL.*= *\([^;]*\);.*/\1/')"
else
    echo "   ⚠ baseURL dans App.php ne contient pas rezopcinline-ci4.local"
fi

echo ""
echo "========================================"
echo "✅ Tests terminés !"
echo ""
echo "Pour accéder au site :"
echo "   → http://rezopcinline-ci4.local/"
echo ""
echo "Si Apache n'est pas démarré, démarrez MAMP et réessayez."

