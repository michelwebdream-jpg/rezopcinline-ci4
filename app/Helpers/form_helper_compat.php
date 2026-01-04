<?php

/**
 * Helper de compatibilité pour les fonctions form CI3
 * Utilise les fonctions CI4 équivalentes
 */

if (!function_exists('form_error')) {
    /**
     * Affiche l'erreur de validation pour un champ
     * Compatible CI3
     */
    function form_error($field = '', $prefix = '', $suffix = '')
    {
        $validation = \Config\Services::validation();
        if ($validation->hasError($field)) {
            return $prefix . $validation->getError($field) . $suffix;
        }
        return '';
    }
}

if (!function_exists('set_value')) {
    /**
     * Retourne la valeur d'un champ (pour les formulaires)
     * Compatible CI3
     */
    function set_value($field, $default = '')
    {
        // CI4: Utiliser la fonction old() qui gère automatiquement les valeurs
        $value = old($field);
        if ($value !== null) {
            return esc($value);
        }
        // Si pas de valeur old, utiliser la valeur POST
        $request = \Config\Services::request();
        $postValue = $request->getPost($field);
        if ($postValue !== null) {
            return esc($postValue);
        }
        return esc($default);
    }
}

if (!function_exists('form_open')) {
    /**
     * Ouvre un formulaire
     * Compatible CI3
     */
    function form_open($action = '', $attributes = [], $hidden = [])
    {
        $url = base_url($action);
        
        // Convertir les attributs en string si c'est un array
        $attr = '';
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . esc($value) . '"';
            }
        } else {
            $attr = ' ' . $attributes;
        }
        
        // Gérer les champs cachés
        $hiddenFields = '';
        if (is_array($hidden)) {
            foreach ($hidden as $key => $value) {
                $hiddenFields .= '<input type="hidden" name="' . esc($key) . '" value="' . esc($value) . '" />' . "\n";
            }
        }
        
        return '<form action="' . esc($url) . '" method="post"' . $attr . '>' . "\n" . $hiddenFields;
    }
}

if (!function_exists('form_close')) {
    /**
     * Ferme un formulaire
     * Compatible CI3
     */
    function form_close($extra = '')
    {
        return '</form>' . $extra;
    }
}

if (!function_exists('anchor')) {
    /**
     * Crée un lien HTML
     * Compatible CI3
     */
    function anchor($uri = '', $title = '', $attributes = '')
    {
        $url = base_url($uri);
        $attr = '';
        
        if (is_string($attributes)) {
            // Parser les attributs comme "target=_blank"
            if (preg_match_all('/(\w+)=([^\s]+)/', $attributes, $matches)) {
                foreach ($matches[1] as $i => $key) {
                    $value = trim($matches[2][$i], '"\'');
                    $attr .= ' ' . $key . '="' . esc($value) . '"';
                }
            }
        } elseif (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . esc($value) . '"';
            }
        }
        
        return '<a href="' . esc($url) . '"' . $attr . '>' . esc($title) . '</a>';
    }
}

