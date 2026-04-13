<?php
// CI4: Plus besoin de BASEPATH check
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <?php $assetVersion = urlencode(config('App')->appVersion); ?>
    <link rel="stylesheet" href="<?= base_url('css/style_main.css?v='.$assetVersion) ?>"/>
    <link rel="stylesheet" href="<?= base_url('css/style.css?v='.$assetVersion) ?>"/>
    <title><?php if(isset($titre)) echo $titre;?></title>
    <!--<script src="https://code.jquery.com/jquery-1.10.2.js"></script>-->
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <!---<script src="//code.jquery.com/jquery-1.12.4.js"></script>-->
    <script type="text/javascript" src="<?php echo base_url(); ?>js/FileSaver.js"></script>
    
    <!---<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>-->
    
    <script src="<?php echo base_url(); ?>js/jspdf.min.js"></script>
    <script src="<?php echo base_url(); ?>js/jspdf.plugin.autotable.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/randomcolor/0.5.2/randomColor.min.js"></script>
    
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.13/css/jquery.dataTables.css">
  
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.js"></script>

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
    <script src="<?php echo base_url();?>js/sweetalert.min.js"></script> 
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>js/sweetalert.css">
    
    <script src="<?php echo base_url();?>js/js.cookie.js"></script>
    
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    
    <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script src="//cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js"></script>
    
    
    <?php
    $u = $utilisateur ?? [];
    if (!empty($u)) {
        $u['is_admin'] = is_admin($u);
    }
    ?>
    <script type="text/javascript">var utilisateur_session = <?= json_encode($u) ?></script>
    <script type="text/javascript">var base_url = "<?= base_url() ?>";</script>
    <script type="text/javascript">var v_soft = "<?= getenv('VERSION_DU_SOFT') ?? 'Version 5.1' ?>";</script>
    
    <?= $map['js'] ?? '' ?>

    <script type="text/javascript" src="<?= base_url('js/maplabel.js') ?>"></script>
    
    <script type="text/javascript" src="<?= base_url('js/rezopcinline.js?v='.$assetVersion) ?>"></script>
    
    <script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-19724464-1']);
		  _gaq.push(['_trackPageview']);
		
		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		
		</script>
    
</head>
<body class="page-membre">
    
    <div id="div_pour_deplacement_marker"></div>
    <div id="div_pour_page_historique"></div>
    <div id="div_pour_page_a_propos"></div>
    
    
    <div class="loader_site"></div>
    <header>
   </header>
    
    <div class="separator"></div>
    <div id="container">


            <div id="my_menu">

                <div id="header_logo">
                        <a href="<?php echo base_url();?>"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.jpg" height="100"></a>
                    <p style="text-align:center;margin-top:5px;margin-bottom:5px;"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.1' ?></p>
                </div>
                <div id="menu">
                    <a href="#" id="load_non_modal_page_activite"><i class="menu-icon fa-solid fa-calendar-days" aria-hidden="true"></i>Activités / DPS</a>
                    <a href="#" id="load_non_modal_page_mission"><i class="menu-icon fa-solid fa-flag" aria-hidden="true"></i>Missions </a>
                    <a href="#" id="load_non_modal_page_markers"><i class="menu-icon fa-solid fa-location-dot" aria-hidden="true"></i>Ma position<br />Marqueurs fixes<br />Fichier KML/KMZ</a>
                    <a href="#" id="load_non_modal_page_efface_markers_fixe"><i class="menu-icon fa-solid fa-trash-can" aria-hidden="true"></i>Effacer tous les<br />marqueurs fixes</a>
                    <a href="#" id="load_non_modal_page_centrage_carte"><i class="menu-icon fa-solid fa-bullseye" aria-hidden="true"></i>Centrer la carte </a>
                    <a href="<?= base_url('membres/carte-ecran2') ?>" id="open_ecran2" title="Ouvrir la carte sur un deuxième écran"><i class="menu-icon fa-solid fa-display" aria-hidden="true"></i>Ouvrir la carte<br />sur l'écran 2 <span class="menu-badge-new">New</span></a>
                    <a href="#" id="load_non_modal_page_recherche_adresse"><i class="menu-icon fa-solid fa-magnifying-glass" aria-hidden="true"></i>Rechercher une adresse </a>
                    <a id="lien_mes_documents" href="<?php echo base_url();?>mes_documents" target="_blank"><i class="menu-icon fa-solid fa-folder" aria-hidden="true"></i>Mes documents </a>
                    <a href="#" id="load_non_modal_page_parametres"><i class="menu-icon fa-solid fa-gear" aria-hidden="true"></i>Réglages</a>
                    <?php if (is_admin($utilisateur ?? [])): ?>
                    <a href="<?= base_url('admin') ?>" target="_blank"><i class="menu-icon fa-solid fa-shield-halved" aria-hidden="true"></i>Administration</a>
                    <a href="#" id="toggle_debug_window"><i class="menu-icon fa-solid fa-bug" aria-hidden="true"></i>Debug Géolocalisation </a>
                    <?php endif; ?>
                </div>
                <div id="theme_switch_membre" class="theme_switch_membre" role="group" aria-label="Choisir le thème">
                            <button type="button" id="theme_btn_sombre" class="theme_switch_btn theme_switch_btn_sombre" title="Mode sombre">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            </button>
                            <button type="button" id="theme_btn_clair" class="theme_switch_btn theme_switch_btn_clair" title="Mode clair">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                            </button>
                </div>
                <div id="zone_affichage_coordonnees" style="font-size:10px;text-align:center;margin-bottom:5px;"></div>
                <div style="text-align:center;">
                    <a href ="#" id="load_non_modal_a_propos"> A propos & bug report </a>
                    <span id="global_footer_html" style="display:none" data-footer="<?= htmlspecialchars(isset($footing) ? $footing : '', ENT_QUOTES, 'UTF-8') ?>"></span>
                    
                </div>
            </div>
        
            <div id="my_map">
                <div class="div_header">
                    <div class="header_user_zone">
                        <div class="info_user_time">
                            <p><span id="nom_utilisateur"></span><br /><a href="<?= base_url('mon_compte') ?>" target="_blank">Mon compte</a> - <a href="<?= base_url('signup/logout') ?>" id="logout_link">Déconnexion</a><br />
                            <p><span id="div_horloge"></span></p>
                        </div>
                    </div>

                    <div id="my_connexion_internet">
                        <div id="id_connexion_internet">
                            <span id="id_connexion_internet_text"></span>
                            <canvas id="ping_chart" title="Latence ping (ms)"></canvas>
                        </div>
                        <div id="my_position_emise"></div>
                        <button id="bouton_modifier_mon_statut" class="bouton_fermer"> Modifier mon statuts </button>

                    </div>

                    <div id="header_info">
                        <div id="menu_nom_activite_container">
                            <div style="float:right;margin-left:10px;margin-top:10px;">
                                <div>
                                    
                                </div>
                                <div>
                                    
                                </div>
                            </div>
                            <div id="menu_nom_activite"></div>
                            <div>
                                <div style="display:inline-block">
                                    <a href ="#" id="bouton_menu_stop_activite" class="bouton_fermer" style="display:none"> Stop Activité </a>
                                </div>
                                <div style="display:inline-block">
                                    <a href ="#" id="load_non_modal_page_saisie_victime" class="bouton_fermer"> Déclarer une victime </a>
                                </div>
                                <div style="display:inline-block">
                                    <a href ="#" id="bouton_voir_bilan" class="bouton_fermer"> Voir le bilan des victimes</a>
                                </div>
                                <div style="display:inline-block">
                                    <a href ="#" id="bouton_menu_historique" class="bouton_fermer" style="display:none"> Historique </a>
                                </div>
                                <div style="display:inline-block">
                                    <a href ="#" id="bouton_voir_main_courante" class="bouton_fermer"> Main courante </a>
                                </div>
                            </div>
                        </div>
                        <div id="menu_info_text_container">
                            <div id="menu_info_text"><p></p></div>
                        </div>
                    </div>                                              

                </div>
                <div id ="content_non_modal"></div>
                <div id ="div_pour_page_parametres"></div>
                <div id ="page_slide_alert_photo"></div>
                <div id ="div_pour_page_erreur_reseau">
                    
                    <div style="display:block;text-align:center;">
                    <img border="0" alt="Erreur" src="<?php echo base_url();?>images/warning_petit.png" width="30" height="30">    
                    </div>
                    <div style="display:block;text-align:center;" id="texte_page_erreur"></div>
                        <div style="text-align:center;"><a href ="#" id="bouton_fermer_page_erreur" class="bouton_fermer"> Fermer </a>
                        </div>
                </div>
                <div id="div_pour_loader">
                    <div id="text_div_pour_loader"></div>
                </div>
                <div id="div_pour_recherche">
                <h2>Rechercher une adresse</h2>
                <input id="pac-input" class="controls" type="text" placeholder="Entrez votre recherche.">
                    <a href ="#" id="bouton_fermer_page_recherche" class="bouton_fermer"> fermer </a>
                    </div>
                <?php if (is_admin($utilisateur ?? [])): ?>
                <div id="div_debug_geolocalisation" style="display:none; position:fixed; top:100px; right:20px; width:600px; max-height:500px; background-color:#fff; border:2px solid #333; border-radius:5px; padding:15px; z-index:10000; box-shadow:0 4px 8px rgba(0,0,0,0.3); overflow:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid #ccc; padding-bottom:10px;">
                        <h2 style="margin:0; color:#333;">Debug - Trames Géolocalisation</h2>
                        <a href="#" id="bouton_fermer_debug" class="bouton_fermer" style="padding:5px 10px;">Fermer</a>
                    </div>
                    <div style="margin-bottom:10px;">
                        <button id="bouton_clear_debug" class="bouton_fermer" style="margin-right:10px;">Effacer</button>
                        <label><input type="checkbox" id="debug_auto_scroll" checked> Défilement automatique</label>
                    </div>
                    <div id="debug_content" style="font-family:monospace; font-size:11px; line-height:1.4; background-color:#f5f5f5; padding:10px; border:1px solid #ddd; border-radius:3px; max-height:350px; overflow-y:auto;">
                        <div style="color:#666; font-style:italic;">En attente de données...</div>
                    </div>
                </div>
                <?php endif; ?>
                <?= $map['html'] ?? '' ?>

            </div>


        </div>
    
  

    </body>
    
</html>