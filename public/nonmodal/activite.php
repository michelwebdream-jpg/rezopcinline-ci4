<?php $burl=$_POST['burl']; ?>


<div id="id_view_activite">
<h1 style=" overflow: hidden; float: left;">Mes activités / DPS</h1>
    <div id="curseur_pour_deplacer_page" class="curseur_pour_deplacer_page_class" style="float:right;display: flex;align-items: center;margin-left:8px;cursor:pointer;"><img src="<?php echo $burl; ?>images/bouton_move.png" />
</div>
    <div style="float:right;display: flex;align-items: center;"><a href ="#" id="bouton_fermer_non_modal" class="bouton_fermer"> Fermer </a>
</div>
<div style="float:right;display: flex;align-items: center;margin-right:10px;"><a href ="#" id="bouton_creer_nouvelle_activite" class="bouton_fermer"> + Créer une nouvelle activité / DPS</a></p>
</div>
<div class="separator"></div>
<hr />
<h2>Liste des activités enregistrées</h2>

<details close>
          <summary>Instructions</summary>
          <p style="text-align:center;font-size:14px;">Vous pouvez sélectionner une activité pour voir le détail en cliquant sur la ligne correspondante dans le tableau ci-dessous.</p>
</details>

<table id="tableau_activite_id" class="display" cellspacing="0" >
    <thead>
    <tr>
        <th>Activités</th>
        <th>Responsable PC</th>
        <th>Adresse de l'activité</th>
        <th>Nature du dispositif</th>
        <th>Remarques</th>
        <th>Moyens engagés</th>
        <th>Date de création</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    </thead>
    
</table>
<hr />
<h2>Détail de l'activité : <span id="titre_detail_activite"></span></h2>
<table id="tableau_detail_activite_id" class="display" cellspacing="0" >
    <thead>
    <tr>
        <th>Nom / Prénom</th>
        <th>Code REZO</th>
        <th>Indicatif</th>
        <th>Statut</th>
        <th>Action</th>
    </tr>
    </thead>
    
</table>
<script>
        



    
$(document).ready( function () {
    
// Fonction utilitaire pour échapper le HTML (utilisée pour le debug)
function escapeHtml(text) {
    if (!text) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

var table_activite;
var table_detail_activite;
var data_table_list_des_activites;
var data_table_list_des_detail_activites;
var data_init = [{"activite":"","utilisateur":"","date_creation":""}];

var erreur_update_detail_activite=0;
var activite_en_cours_de_detail;
var jqxhr_activite;
var jqxhr_detail_activite;
var mon_timer_activite = null; // Timer pour le rafraîchissement automatique
var affiche_detail_activite_en_cours = false; // Protection contre les appels multiples

var page_activite_fermer;    

function modifier_indicatif_utilisateur(data_pass,nouveau_indicatif){
    
    swal({   title: "Modification d'indicatif",   text: "Entrez le nouvel indicatif.",   type: "input",   showCancelButton: true,   closeOnConfirm: false,   animation: "slide-from-top",   inputPlaceholder: "Nouvel indicatif" }, function(inputValue){   if (inputValue === false) return false;      if (inputValue === "") {     swal.showInputError("Vous devez saisir un nouvel indicatif!");     return false   }      
    
    var $data={
            code_utilisateur_a_modifier:data_pass.code, 
            nouveau_indicatif:inputValue
        }
    
    var jqxhr = $.post(Global.APP_SERVER_URL+Global.UPDATE_INDICATIF_USER_URI,$data)

    .done(function(data, textStatus, jqXHR ) {

        var buf=data.replace('return_txt=','');

        if (buf!=="ok")
        {
            ouvre_alerte('Erreur réseau !<br />modification impossible...'); 
        }
        else
        {
            swal("Succès","L'indicatif a bien été modifié.","success");
        }
      })
  .fail(function(jqXHR, textStatus, errorThrown) {
      ouvre_alerte('Erreur réseau !<br />Modification impossible...'); 
      
  })
  .always(function() {

  });    
    
 });
    
    
}

    
function supprime_activite(id_activite_a_supprimer){
        var $data={
            mon_code:Global.code_administrateur, 
            id_de_lactivite:id_activite_a_supprimer
        }
    
    var jqxhr = $.post(Global.APP_SERVER_URL+Global.DELETE_ACTIVITE_URI,$data)

    .done(function(data, textStatus, jqXHR ) {

        var buf=data.replace('return_txt=','');

        if (buf==="-1")
        {
            ouvre_alerte('Erreur réseau !<br />Suppression impossible...'); 
				

        }else if (buf==="")
        {
				ouvre_alerte('Erreur réseau !<br />Suppression impossible...'); 
        }
        else if (buf==="1")
        {
            swal("Succès","L'activité a bien étée supprimée.","success");
            actualise_liste_des_activites();
        }
      })
  .fail(function(jqXHR, textStatus, errorThrown) {
      ouvre_alerte('Erreur réseau !<br />Suppression impossible...'); 
      
  })
  .always(function() {

  })
  ;        
}
    
function InitOverviewDataTable_activite(){
  
  //initialize DataTables
  table_activite = $('#tableau_activite_id').DataTable({
      
      "autoWidth": false,
      "order": [[ 6, "desc" ]],
      "columnDefs": [ 
          {
            "targets": 0,
            "data": 'activite',
            "defaultContent": "",
            'width': '10%'
        },
          {
            "targets": 1,
            "data": 'nom_responsable',
            "defaultContent": "N.C.",
            className: "td_center"
        },
          {
            "targets": 2,
            "data": 'adresse',
            "defaultContent": "N.C.",
            className: "td_center"
        },{
            "targets": 3,
            "data": 'nature_activite',
            "defaultContent": "N.C.",
            className: "td_center"
        },
          {
            "targets": 4,
            "data": 'remarque',
            "defaultContent": "N.C.",
            className: "td_center"
        },
          {
            "targets": 5,
            "data": 'utilisateur',
            "defaultContent": "",
            className: "td_center"
        },
          {
            "targets": 6,
            "data": 'date_creation',
            "defaultContent": ""
            
        },
          {
            "targets": 7,
            "data": null,
              "orderable": false,
            className: "td_center",
            "defaultContent": "<button id='button_supprimer'><img src='images/trash_recyclebin_empty_closed.png' width=20px height=20px></button>"
        },
          {
            "targets": 8,
            "data": null,
              "orderable": false,
            "defaultContent": "<button id='button_modifier'><img src='images/icon_edit.png' width=20px height=20px></button>",
            className: "td_center"
        },
          {
            "targets": 9,
            "data": null,
              "orderable": false,
            "defaultContent": "<button class='button_geolocaliser bouton_contour_rouge'>Géolocaliser</button>"
        },
          {
            "targets": 10,
            "data": null,
              "orderable": false,
            "defaultContent": "<button id='button_historique' class='bouton_contour_rouge'>Historique</button>"
        },
          {
            "targets": 11,
            "data": null,
              "orderable": false,
            "defaultContent": "<button id='button_bilan_activite' class='bouton_contour_rouge'>Bilan</button>"
        },
          {
            "targets": 12,
            "data": null,
              "orderable": false,
            "defaultContent": "<button id='button_main_courante_activite' class='bouton_contour_rouge'>Main courante</button>"
        } ],
      language: language_datatable
  });
    $('#tableau_activite_id tbody').on( 'click', '#button_supprimer', function (e) {
      
        e.stopPropagation();
        var data = table_activite.row( $(this).parents('tr') ).data();
        
        swal({   title: "ATTENTION !",   text: "Êtes-vous sûr de vouloir effacer cette activité ?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Oui",   cancelButtonText: "Non", closeOnConfirm: true }, function(){   
            supprime_activite(data.c6);
        });
        
        
        /*if (confirm('Êtes-vous sûr de vouloir effacer cette activité ?')) {
            supprime_activite(data.c6);
        }*/
        
    } );
    $('#tableau_activite_id tbody').on( 'click', '#button_modifier', function (e) {
        e.stopPropagation();
        var data = table_activite.row( $(this).parents('tr') ).data();
        ouvre_page_modifier_activiter(data);   
    } );
    $('#tableau_activite_id tbody').on( 'click', '.button_geolocaliser', function (e) {
        e.stopPropagation();
        e.preventDefault();
        console.log('Bouton géolocaliser cliqué (activité)');
        var data = table_activite.row( $(this).parents('tr') ).data();
        console.log('Données de l\'activité:', data);
        if (data && typeof lance_activite === 'function') {
            lance_activite(data);
        } else {
            console.error('Erreur: lance_activite n\'est pas une fonction ou data est invalide');
            if (!data) {
                console.error('data est null ou undefined');
            }
            if (typeof lance_activite !== 'function') {
                console.error('lance_activite n\'est pas définie');
            }
        }
    } );
    $('#tableau_activite_id tbody').on( 'click', '#button_historique', function (e) {
        e.stopPropagation();
        var data = table_activite.row( $(this).parents('tr') ).data();
        lit_historique_activite(data);
    } );
    $('#tableau_activite_id tbody').on( 'click', '#button_bilan_activite', function (e) {
        e.stopPropagation();
        var data = table_activite.row( $(this).parents('tr') ).data();
        ouvrir_page_bilan_victime(data.activite,data.c6,"activite");
    } );
    $('#tableau_activite_id tbody').on( 'click', '#button_main_courante_activite', function (e) {
        e.stopPropagation();
        var data = table_activite.row( $(this).parents('tr') ).data();
        ouvrir_page_main_courante(data.activite,data.c6,"activite");
    } );
  $('#tableau_activite_id').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        }
        else {
            table_activite.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');

            var temp1=table_activite.row( $(this) ).data().c7;

            var temp2=temp1.split(",");
            var temp3=temp2.join("{}");

            $('#titre_detail_activite').html(table_activite.row( $(this) ).data().activite);

            activite_en_cours_de_detail=temp3;

            affiche_detail_activite(temp3);
        }
    });

    actualise_liste_des_activites();
}
    
function lit_historique_activite(data_pass){
	
    affiche_page_historique(data_pass.activite,null,"activite");
	lit_historique(data_pass.activite);
    
}
    
function affiche_detail_activite(liste_des_codes){
    // PROTECTION: Vérifier si une requête est déjà en cours
    if (affiche_detail_activite_en_cours) {
        console.warn('affiche_detail_activite: Une requête est déjà en cours, appel ignoré');
        return;
    }
    
    // PROTECTION: Vérifier que la page est toujours ouverte
    if (page_activite_fermer) {
        console.warn('affiche_detail_activite: Page fermée, arrêt du rafraîchissement');
        if (mon_timer_activite != null) {
            clearTimeout(mon_timer_activite);
            mon_timer_activite = null;
        }
        return;
    }
    
    // Annuler le timer précédent s'il existe
    if (mon_timer_activite != null) {
        clearTimeout(mon_timer_activite);
        mon_timer_activite = null;
    }
    
    // Marquer qu'une requête est en cours
    affiche_detail_activite_en_cours = true;
    
    // Annuler la requête précédente si elle existe
    if (jqxhr_detail_activite) {
        jqxhr_detail_activite.abort();
    }
    
    // Envoyer les mêmes paramètres que recherche_membre_activite() pour que le serveur mette à jour correctement les statuts
    var $data={
        liste_des_codes:liste_des_codes,
        code_PC:Global.code_administrateur+"--rezo-wd-ozer--"+Global.indicatif_administrateur,
        plateforme:"REZO PC Inline"
    };
    
    // Ajouter à la fenêtre de debug (uniquement si le code correspond)
    if (Global.code_administrateur === 'ba7fd5f5' && $('#div_debug_geolocalisation').is(':visible')) {
        var debugContent = $('#debug_content');
        var timestamp = new Date().toLocaleTimeString('fr-FR');
        var debugEntry = '<div style="margin-bottom:15px; padding:10px; background-color:#fff; border-left:3px solid #2196F3; border-radius:3px;">';
        debugEntry += '<div style="font-weight:bold; color:#2196F3; margin-bottom:5px;">[' + timestamp + '] <span style="background-color:#2196F3; color:#fff; padding:2px 6px; border-radius:3px; font-size:11px;">affiche_detail_activite()</span> - Envoi de requête</div>';
        debugEntry += '<div style="margin-bottom:5px;"><strong>URL:</strong> ' + Global.APP_SERVER_URL + Global.INFO_ACTIVITE_URI + '</div>';
        debugEntry += '<div style="margin-bottom:5px;"><strong>Données envoyées:</strong> ' + JSON.stringify($data) + '</div>';
        debugEntry += '</div>';
        
        debugContent.append(debugEntry);
        
        // Défilement automatique si activé
        if ($('#debug_auto_scroll').is(':checked')) {
            debugContent.scrollTop(debugContent[0].scrollHeight);
        }
        
        // Limiter à 50 entrées maximum
        var entries = debugContent.children('div');
        if (entries.length > 50) {
            entries.first().remove();
        }
    }
    
    jqxhr_detail_activite = $.post(Global.APP_SERVER_URL+Global.INFO_ACTIVITE_URI, $data)

    .done(function(data, textStatus, jqXHR ) {
        // Ajouter à la fenêtre de debug (uniquement si le code correspond)
        if (Global.code_administrateur === 'ba7fd5f5' && $('#div_debug_geolocalisation').is(':visible')) {
            var debugContent = $('#debug_content');
            var timestamp = new Date().toLocaleTimeString('fr-FR');
            var debugEntry = '<div style="margin-bottom:15px; padding:10px; background-color:#fff; border-left:3px solid #4CAF50; border-radius:3px;">';
            debugEntry += '<div style="font-weight:bold; color:#4CAF50; margin-bottom:5px;">[' + timestamp + '] <span style="background-color:#4CAF50; color:#fff; padding:2px 6px; border-radius:3px; font-size:11px;">affiche_detail_activite()</span> - Réception de données</div>';
            debugEntry += '<div style="margin-bottom:5px;"><strong>URL:</strong> ' + Global.APP_SERVER_URL + Global.INFO_ACTIVITE_URI + '</div>';
            debugEntry += '<div style="margin-bottom:5px;"><strong>Données envoyées:</strong> ' + JSON.stringify($data) + '</div>';
            debugEntry += '<div style="margin-bottom:5px;"><strong>Réponse brute:</strong></div>';
            debugEntry += '<div style="background-color:#fff; padding:8px; border:1px solid #ddd; border-radius:3px; white-space:pre-wrap; word-wrap:break-word; max-height:200px; overflow-y:auto; font-size:10px;">' + escapeHtml(data.substring(0, 5000)) + (data.length > 5000 ? '\n... (tronqué)' : '') + '</div>';
            debugEntry += '</div>';
            
            debugContent.append(debugEntry);
            
            // Défilement automatique si activé
            if ($('#debug_auto_scroll').is(':checked')) {
                debugContent.scrollTop(debugContent[0].scrollHeight);
            }
            
            // Limiter à 50 entrées maximum
            var entries = debugContent.children('div');
            if (entries.length > 50) {
                entries.first().remove();
            }
        }

        var buf=data.replace('return_txt=','');

        if (buf=="-1")
        {
            ouvre_alerte('Erreur réseau !<br />Actualisation impossible...'); 

        }else if (buf=="")
        {
				ouvre_alerte('Erreur réseau !<br />Actualisation impossible...'); 
        }
        else
        {
                var page_en_cours=table_detail_activite.page();
                var page_max=table_detail_activite.page.info().pages;
            
            
                table_detail_activite.clear();
                table_detail_activite.draw();

                var trame2=buf.split("\n");


                var code2= new Array(trame2.length);
                var statut2 = new Array(trame2.length);
                var nom2= new Array(trame2.length);
                var prenom2= new Array(trame2.length);
                var indicatif2= new Array(trame2.length);

                data_table_list_des_detail_activites= new Array();
                for (i=0; i<trame2.length;i++)
                {
                    // Ignorer les lignes vides (comme dans recherche_membre_activite)
                    if (!trame2[i] || trame2[i].trim() === '') {
                        continue;
                    }
                    
                    var temp2=trame2[i].split("><");
                    
                    // Vérifier qu'on a au moins 5 éléments (code, statut, nom, prenom, indicatif)
                    if (temp2.length < 5) {
                        console.warn('affiche_detail_activite: Ligne', i, 'n\'a pas assez d\'éléments (attendu: 5, reçu:', temp2.length, ')');
                        continue;
                    }
                    
                    code2[i]=temp2[0];
                    statut2[i]=temp2[1];
                    nom2[i]=temp2[2];
                    prenom2[i]=temp2[3];
                    indicatif2[i]=temp2[4];

                    data_table_list_des_detail_activites.push({nom:nom2[i] +" " + prenom2[i], code:code2[i], indicatif:indicatif2[i], statut:statut2[i], c4:""});
                }

                if (data_table_list_des_detail_activites.length>0){

                    table_detail_activite.clear();
                    table_detail_activite.rows.add(data_table_list_des_detail_activites);
                    table_detail_activite.draw();
                    if (page_en_cours<=page_max){
                        table_detail_activite.page(page_en_cours).draw('page');
                    }else{
                        table_detail_activite.page(page_max).draw('page');
                    }
                    
                }else{

                    table_detail_activite.clear();
                    table_detail_activite.draw();
                }
                erreur_update_detail_activite=0;
        }
		        
      })
  .fail(function(jqXHR, textStatus, errorThrown) {
      // Ajouter l'erreur à la fenêtre de debug (uniquement si le code correspond)
      if (Global.code_administrateur === 'ba7fd5f5' && $('#div_debug_geolocalisation').is(':visible')) {
          var debugContent = $('#debug_content');
          var timestamp = new Date().toLocaleTimeString('fr-FR');
          var debugEntry = '<div style="margin-bottom:15px; padding:10px; background-color:#fff; border-left:3px solid #f44336; border-radius:3px;">';
          debugEntry += '<div style="font-weight:bold; color:#f44336; margin-bottom:5px;">[' + timestamp + '] <span style="background-color:#f44336; color:#fff; padding:2px 6px; border-radius:3px; font-size:11px;">affiche_detail_activite()</span> - ERREUR AJAX</div>';
          debugEntry += '<div style="margin-bottom:5px;"><strong>URL:</strong> ' + Global.APP_SERVER_URL + Global.INFO_ACTIVITE_URI + '</div>';
          debugEntry += '<div style="margin-bottom:5px;"><strong>Status:</strong> ' + jqXHR.status + '</div>';
          debugEntry += '<div style="margin-bottom:5px;"><strong>Text Status:</strong> ' + textStatus + '</div>';
          debugEntry += '<div style="margin-bottom:5px;"><strong>Error Thrown:</strong> ' + errorThrown + '</div>';
          if (jqXHR.responseText) {
              debugEntry += '<div style="margin-bottom:5px;"><strong>Response Text:</strong></div>';
              debugEntry += '<div style="background-color:#fff; padding:8px; border:1px solid #ddd; border-radius:3px; white-space:pre-wrap; word-wrap:break-word; font-size:10px;">' + escapeHtml(jqXHR.responseText.substring(0, 1000)) + '</div>';
          }
          debugEntry += '</div>';
          
          debugContent.append(debugEntry);
          
          // Défilement automatique si activé
          if ($('#debug_auto_scroll').is(':checked')) {
              debugContent.scrollTop(debugContent[0].scrollHeight);
          }
          
          // Limiter à 50 entrées maximum
          var entries = debugContent.children('div');
          if (entries.length > 50) {
              entries.first().remove();
          }
      }
      
      erreur_update_detail_activite++;
      if (erreur_update_detail_activite>2){
          erreur_update_detail_activite=0;
           table_detail_activite.clear();
            table_detail_activite.draw();
          ouvre_alerte('Erreur réseau !<br />Un problème réseau est survenu.<br />Impossible d\'afficher le détail de l\'activité.');  
      }
     
  })
  .always(function() {
      // Libérer le verrou après la fin de la requête (succès ou échec)
      affiche_detail_activite_en_cours = false;
      
      // Vérifier que la page est toujours ouverte avant de programmer le prochain appel
      if (!page_activite_fermer && activite_en_cours_de_detail) {
          mon_timer_activite = setTimeout(function(){
              affiche_detail_activite(activite_en_cours_de_detail);
          }, 2000);
      } else {
          console.log('affiche_detail_activite: Page fermée ou aucune activité sélectionnée, pas de nouveau rafraîchissement');
          mon_timer_activite = null;
      }
  })
  ;
    
    
}
function actualise_liste_des_activites(){
    
    jqxhr_activite = $.post(Global.APP_SERVER_URL+Global.REFRESH_ACTIVITE_URI,{ mon_code:Global.code_administrateur})

    .done(function(data, textStatus, jqXHR ) {

        data=data.replace('return_txt=','');

        
        
        if (data==="-1"){
            table = $('#tableau_activite_id').DataTable();
            table.clear();
            table.draw();
            return;
        }
        
        var trame=data.split("\n");

        var qte= new Array();
        var titre = new Array();
        var membres= new Array();
        var date= new Array();
        var id= new Array();
        var nom_responsable= new Array();
        var adresse= new Array();
        var remarque= new Array();
        var nature_activite= new Array();
        var liens_kml= new Array();
        var marqueurs_fixes= new Array();
        
        data_table_list_des_activites= new Array();

        for (i=0; i<trame.length;i++)
            {
                var temp=trame[i].split("><");
                qte[i]=temp[0];
                titre[i]=temp[1];
                membres[i]=temp[2];
                date[i]=temp[3];
                id[i]=temp[4];
                nom_responsable[i]=temp[5];
                adresse[i]=temp[6];
                remarque[i]=temp[7];
                nature_activite[i]=temp[8];
                liens_kml[i]=temp[9];
                marqueurs_fixes[i]=temp[10];
                
                data_table_list_des_activites.push({activite:titre[i], nom_responsable:nom_responsable[i],adresse:adresse[i], nature_activite:nature_activite[i], remarque:remarque[i], utilisateur:qte[i], date_creation:date[i],c6:id[i], c7:membres[i], liens_kml:liens_kml[i], marqueurs_fixes:marqueurs_fixes[i]});
            }
        if (data_table_list_des_activites.length>0){
            
            jQuery.fn.dataTable.moment( 'DD-MM-YYYY HH:mm:ss' );
            
            table = $('#tableau_activite_id').DataTable();
            table.clear();
            table.rows.add(data_table_list_des_activites);
            table.draw();
        }else{
            table = $('#tableau_activite_id').DataTable();
            table.clear();
            table.draw();
        }
        for (i=0;i<data_table_list_des_activites.length;i++){
        
            var adresse=data_table_list_des_activites[i].adresse;
            adresse=adresse.replace(/ <br> /g, '\n');
            data_table_list_des_activites[i].adresse=adresse;
            
            var remarque=data_table_list_des_activites[i].remarque;
            remarque=remarque.replace(/ <br> /g, '\n');
            data_table_list_des_activites[i].remarque=remarque;
            
            var nature_activite=data_table_list_des_activites[i].nature_activite;
            nature_activite=nature_activite.replace(/ <br> /g, '\n');
            data_table_list_des_activites[i].nature_activite=nature_activite;
            
            var liens_kml=data_table_list_des_activites[i].liens_kml;
            liens_kml=liens_kml.replace(/ <br> /g, '\n');
            data_table_list_des_activites[i].liens_kml=liens_kml;
            
            var marqueurs_fixes=data_table_list_des_activites[i].marqueurs_fixes;
            marqueurs_fixes=marqueurs_fixes.replace(/ <br> /g, '\n');
            data_table_list_des_activites[i].marqueurs_fixes=marqueurs_fixes;
            
        }
    })
  .fail(function(jqXHR, textStatus, errorThrown) {
        table_activite.clear();
        table_activite.draw();
     ouvre_alerte('Erreur réseau !<br />Un problème réseau est survenu.<br />Impossible d\'actualiser les activités');   

  })
  .always(function() {

  })
  ;
    
}
function InitOverviewDataTable_detail_activite(){
  
  //initialize DataTables
  table_detail_activite = $('#tableau_detail_activite_id').DataTable({
      
      "autoWidth": false,
      "columnDefs": [ 
          {
            "targets": 0,
            "data": 'nom',
            "defaultContent": "",
            className: "td_center"
        },
          {
            "targets": 1,
            "data": 'code',
            "defaultContent": "",
            className: "td_center"
        },
          {
            "targets": 2,
            "data": 'indicatif',
            "defaultContent": "",
            className: "td_center"
        },
          {
            "targets": 3,
            "data": 'statut',
            "defaultContent": "",
            "className": "td_center",      
        },
          {
            "targets": 4,
            "data": null,
              "orderable": false,
            "defaultContent": "<button id='button_modifier_indicatif'>Modifier l'indicatif</button>",
            className: "td_center",
              
        }],
        "rowCallback": function( row, data, index ) {
            var statu = data.statut;

            if (statu === 'inactif') {
                $('td', row).css('background-color', '#e36c54');
            }else if (statu === 'actif'){
                $('td', row).css('background-color', '#53ab5b');
            }else{
                $('td', row).css('background-color', 'inherit');
            }
        },
      language: language_datatable
  });
  $('#tableau_detail_activite_id tbody').on( 'click', '#button_modifier_indicatif', function (e) {
        e.stopPropagation();
        var data = table_detail_activite.row( $(this).parents('tr') ).data();
        modifier_indicatif_utilisateur(data);   
    } );
}
    
    // Fonction pour arrêter le rafraîchissement automatique (appelée depuis l'extérieur)
    window.stop_affiche_detail_activite = function() {
        page_activite_fermer = true;
        
        // Annuler les requêtes en cours
        if (jqxhr_activite) {
            jqxhr_activite.abort();
        }
        if (jqxhr_detail_activite) {
            jqxhr_detail_activite.abort();
        }
        
        // Nettoyer le timer
        if (mon_timer_activite != null) {
            clearTimeout(mon_timer_activite);
            mon_timer_activite = null;
        }
        
        // Réinitialiser le verrou
        affiche_detail_activite_en_cours = false;
    };
    
    $('#id_view_activite').on('remove',function(){ 
        window.stop_affiche_detail_activite();
        
        table_detail_activite.clear();
        table_detail_activite.draw();
        table_activite.clear();
        table_activite.draw();
    });
    
    $('#bouton_creer_nouvelle_activite').click(function() {
        ouvre_page_modifier_activiter(null);   
    });
    
    page_activite_fermer=false;
    
    InitOverviewDataTable_activite();
    InitOverviewDataTable_detail_activite();
    
});
    
</script>