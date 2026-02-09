<?php

/**
 * Retourne le HTML du footer (copyright avec lien Web-Dream).
 * L'année est lue depuis .env (COPYRIGHT_YEAR), sinon année courante.
 */
if (!function_exists('footer_html')) {
    function footer_html(): string
    {
        $year = getenv('COPYRIGHT_YEAR') ?: date('Y');
        return 'copyright@' . $year . ' <a href="https://www.web-dream.fr" target="_blank">Web-Dream</a>';
    }
}
