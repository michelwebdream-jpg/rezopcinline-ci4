#!/bin/bash
# Script de test pour le VirtualHost HTTPS rezopcinline-ci4.local

echo "🔒 Test de configuration HTTPS"
echo "=============================="
echo ""

# Test 1 : Vérifier les certificats
echo "1. Vérification des certificats SSL..."
if [ -f "/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1.pem" ]; then
    echo "   ✓ Certificat présent : rezopcinline-ci4.local+1.pem"
else
    echo "   ✗ Certificat absent"
    exit 1
fi

if [ -f "/Applications/MAMP/conf/apache/rezopcinline-ci4.local+1-key.pem" ]; then
    echo "   ✓ Clé privée présente : rezopcinline-ci4.local+1-key.pem"
else
    echo "   ✗ Clé privée absente"
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

# Test 3 : Vérifier les VirtualHosts
echo ""
echo "3. Vérification des VirtualHosts..."
HTTP_VHOST=$(/Applications/MAMP/Library/bin/httpd -S 2>&1 | grep -c "port 80.*rezopcinline-ci4.local")
HTTPS_VHOST=$(/Applications/MAMP/Library/bin/httpd -S 2>&1 | grep -c "port 443.*rezopcinline-ci4.local")

if [ "$HTTP_VHOST" -gt 0 ]; then
    echo "   ✓ VirtualHost HTTP (port 80) détecté"
else
    echo "   ✗ VirtualHost HTTP (port 80) NON détecté"
fi

if [ "$HTTPS_VHOST" -gt 0 ]; then
    echo "   ✓ VirtualHost HTTPS (port 443) détecté"
else
    echo "   ✗ VirtualHost HTTPS (port 443) NON détecté"
    exit 1
fi

# Test 4 : Test de connexion HTTPS
echo ""
echo "4. Test de connexion HTTPS..."
HTTP_CODE=$(curl -k -s -o /dev/null -w "%{http_code}" https://rezopcinline-ci4.local/ 2>&1)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ] || [ "$HTTP_CODE" = "301" ]; then
    echo "   ✓ Réponse HTTP $HTTP_CODE - HTTPS fonctionne"
else
    if [ "$HTTP_CODE" = "000" ]; then
        echo "   ⚠ Apache ne semble pas être en cours d'exécution"
        echo "   → Démarrez MAMP et réessayez"
    else
        echo "   ⚠ Réponse HTTP $HTTP_CODE"
    fi
fi

# Test 5 : Test de redirection HTTP vers HTTPS
echo ""
echo "5. Test de redirection HTTP vers HTTPS..."
REDIRECT=$(curl -I http://rezopcinline-ci4.local/ 2>&1 | grep -i "location.*https" | head -1)
if [ -n "$REDIRECT" ]; then
    echo "   ✓ Redirection HTTP → HTTPS active"
    echo "   → $REDIRECT"
else
    echo "   ⚠ Redirection HTTP → HTTPS non détectée (peut être normale si Apache n'est pas démarré)"
fi

# Test 6 : Vérifier la baseURL
echo ""
echo "6. Vérification de la baseURL..."
BASEURL_ENV=$(grep "app.baseURL" .env 2>/dev/null | head -1)
if echo "$BASEURL_ENV" | grep -q "https://rezopcinline-ci4.local"; then
    echo "   ✓ baseURL dans .env : $BASEURL_ENV"
else
    echo "   ⚠ baseURL dans .env ne contient pas https://rezopcinline-ci4.local"
fi

BASEURL_PHP=$(grep "baseURL" app/Config/App.php 2>/dev/null | grep -v "^[[:space:]]*//" | head -1)
if echo "$BASEURL_PHP" | grep -q "https://rezopcinline-ci4.local"; then
    echo "   ✓ baseURL dans App.php : $(echo $BASEURL_PHP | sed 's/.*baseURL.*= *\([^;]*\);.*/\1/')"
else
    echo "   ⚠ baseURL dans App.php ne contient pas https://rezopcinline-ci4.local"
fi

echo ""
echo "=============================="
echo "✅ Tests terminés !"
echo ""
echo "Pour accéder au site en HTTPS :"
echo "   → https://rezopcinline-ci4.local/"
echo ""
echo "Note : Le certificat est auto-signé par mkcert, donc votre navigateur"
echo "       devrait l'accepter automatiquement (pas d'avertissement)."
echo ""
echo "Si Apache n'est pas démarré, démarrez MAMP et réessayez."

