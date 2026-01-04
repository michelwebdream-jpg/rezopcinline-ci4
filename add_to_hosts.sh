#!/bin/bash
# Script pour ajouter rezopcinline-ci4.local à /etc/hosts
# Ce script doit être exécuté avec sudo

if grep -q "rezopcinline-ci4.local" /etc/hosts; then
    echo "rezopcinline-ci4.local est déjà présent dans /etc/hosts"
else
    echo "127.0.0.1 rezopcinline-ci4.local www.rezopcinline-ci4.local" | sudo tee -a /etc/hosts
    echo "✓ Entrée ajoutée dans /etc/hosts"
fi

