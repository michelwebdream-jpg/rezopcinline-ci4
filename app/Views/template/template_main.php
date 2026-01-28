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
    
    <link rel="stylesheet" href="<?php echo base_url();?>css/style_main.css"/>
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
    
    
    <script type="text/javascript">var utilisateur_session = <?= json_encode($utilisateur ?? []) ?></script>
    <script type="text/javascript">var base_url = "<?= base_url() ?>";</script>
    <script type="text/javascript">var v_soft = "<?= getenv('VERSION_DU_SOFT') ?? 'Version 5.0' ?>";</script>
    
    <?= $map['js'] ?? '' ?>

    <script type="text/javascript" src="<?php echo base_url(); ?>js/maplabel.js"></script>
    
    <script type="text/javascript" src="<?php echo base_url();?>js/rezopcinline.js"></script>
    
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
<body>
    
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
                        <a href="<?php echo base_url();?>"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.jpg" width="100" height="100"></a>
                    <p style="text-align:center;margin-top:5px;margin-bottom:5px;"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.0' ?></p>
                </div>
                <div id="menu">
                    
                    <a href ="#" id="load_non_modal_page_activite"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_my_group_green.png" width="35" height="35"><br /> Activités / DPS</a>
                    <a href ="#" id="load_non_modal_page_mission"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_my_group_green.png" width="35" height="35"><br /> Missions </a>
                    <a href ="#" id="load_non_modal_page_markers"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_my_geo_green_petit.png" width="35" height="35"><br /> Ma position<br />Marqueurs fixes<br />Fichier KML/KMZ</a>
                    <a href ="#" id="load_non_modal_page_efface_markers_fixe"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_delete.png" width="35" height="35"><br /> Effacer tous les<br />marqueurs fixes</a>
                    <a href ="#" id="load_non_modal_page_centrage_carte"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_centrer.png" width="35" height="35"><br /> Centrer la carte </a>
                    <a href ="#" id="load_non_modal_page_recherche_adresse"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/loupe.png" width="35" height="35"><br /> Rechercher<br />une adresse </a>
                    <a id="lien_mes_documents" href="<?php echo base_url();?>mes_documents" target="_blank"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_documents.png" width="35" height="35"><br /> Mes documents </a>
                    <a href ="#" id="load_non_modal_page_parametres"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/button_setting.png" width="35" height="35"><br /> Réglages </a>
                    <?php if (isset($utilisateur) && isset($utilisateur['code_administrateur']) && $utilisateur['code_administrateur'] === 'ba7fd5f5'): ?>
                    <a href ="#" id="toggle_debug_window"><img border="0" alt="Debug" src="<?php echo base_url();?>images/warning_petit.png" width="35" height="35"><br /> Debug<br />Géolocalisation </a>
                    <?php endif; ?>
                </div>
                <div id="zone_affichage_coordonnees" style="font-size:10px;text-align:center;margin-bottom:5px;"></div>
                <div style="text-align:center;">
                    <a href ="#" id="load_non_modal_a_propos"> A propos & bug report </a>
                    <p class="copyright"><?php if(isset($footing)) echo $footing;?></p>
                </div>
            </div>
        
            <div id="my_map">
                <div class="div_header">
                    <div class="info_user_time">
                        <p><span id="nom_utilisateur"></span><br /><a href="<?= base_url('mon_compte') ?>" target="_blank">Mon compte</a> - <a href="<?= base_url('signup/logout') ?>">Déconnexion</a><br />
                        <span id="div_horloge"></span></p>  
                    </div>

                    <div id="my_connexion_internet">
                        <div id="id_connexion_internet"></div>
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
                <?php if (isset($utilisateur) && isset($utilisateur['code_administrateur']) && $utilisateur['code_administrateur'] === 'ba7fd5f5'): ?>
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