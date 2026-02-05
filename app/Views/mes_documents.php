<?php
// CI4: Plus besoin de BASEPATH check
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link rel="apple-touch-icon" href="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.png">
    <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="<?php echo base_url();?>css/style_main.css"/>
    <link rel="stylesheet" href="<?php echo base_url();?>css/blueimp-gallery.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.13/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-latest.min.js"></script>
    <script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.13/js/jquery.dataTables.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="<?php echo base_url();?>js/sweetalert.min.js"></script> 
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>js/sweetalert.css">
    
	<title><?php echo $titre;?></title>

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
<div id="div_pour_loader">
    <div id="text_div_pour_loader"></div>
</div>
    
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <p class="description"></p>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
    
<div id="container">
    
    <div id="my_menu_documents">

        <div id="header_logo">
                <a href="<?php echo base_url();?>"><img border="0" alt="Rezo+ pc inline" src="<?php echo base_url();?>images/icone_final_rezo_plus_PC_inline128.jpg" width="100" height="100"></a>
            <p style="text-align:center;"><?= getenv('VERSION_DU_SOFT') ?? 'Version 5.0' ?></p>
        </div>
        <div style="text-align:center">
            <h2>Choisir un dossier utilisateur.</h2>
            <table id="tableau_document_utilisateur" class="display" cellspacing="0" >
                <thead>
                <tr>
                    <th>Dossier</th>
                </tr>
                </thead>
            </table>
            <p>Espace utilisé : <span id="text_poucent_utilise"></span></p>
            <div id="h_bar_size_document" style="margin-bottom:10px;"></div>
            <div style=""><a href ="#" id="bouton_supprimer_dossier_selectionne" class="bouton_fermer" style="display:none;"> Supprimer le dossier sélectionné. </a>
            </div>
        </div>
    </div>
        
    <div id="right_container">
        <div class="div_header_documents">
            <h1>Mes documents</h1>
            <hr />
        </div>
        <div id="links"></div>
    </div>
</div>
    
    <footer>
    
        <p class="footer"><?php if(isset($footing)) echo $footing;?></p>
        
    </footer>
    <!---<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>-->
    <script src="js/jquery.blueimp-gallery.min.js"></script>
    
<script>
    
    var table_document_utilisateur;
    var language_datatable= {
        processing:     "Traitement en cours...",
        search:         "Rechercher&nbsp;:",
        lengthMenu:    "Afficher _MENU_ &eacute;l&eacute;ments",
        info:           "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
        infoEmpty:      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
        infoFiltered:   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
        infoPostFix:    "",
        loadingRecords: "Chargement en cours...",
        zeroRecords:    "Aucun &eacute;l&eacute;ment &agrave; afficher",
        emptyTable:     "Aucune donnée disponible dans le tableau",
        paginate: {
            first:      "Premier",
            previous:   "Pr&eacute;c&eacute;dent",
            next:       "Suivant",
            last:       "Dernier"
        },
        aria: {
            sortAscending:  ": activer pour trier la colonne par ordre croissant",
            sortDescending: ": activer pour trier la colonne par ordre décroissant"
        }};
    var action_a_ouverture_galerie="";
    var datadirectoryContents;
    var repertoire_selectionne='';
    // base_url() inclut déjà /rezopcinline ; /dev/... est réécrit vers public/dev/ par .htaccess
    var base_url_galerie = '<?= rtrim(base_url('dev/rezo_galerie/'), '/') . '/' ?>';
    var base_url_thumb = 'thumb/';
    var backend  = 'backend.php';
    var code_pc='<?php echo (isset($utilisateur) && isset($utilisateur["code_administrateur"])) ? $utilisateur["code_administrateur"] : ''; ?>';
    // Utiliser base_url() pour garantir la bonne base (avec /rezopcinline en prod)
    var app_server_url = '<?= rtrim(base_url(), '/') ?>';
    var get_directory_tree_json_uri = '/dev/rezo_galerie/get_directory_tree_json.php';
    var efface_repertoire_uri = '/dev/rezo_code/efface_repertoire.php';
    var supprime_photo_galerie_uri = '/dev/rezo_code/supprime_photo_galerie.php';
    
function InitOverviewDataTable_document_utilisateur(){
  
  //initialize DataTables
  table_document_utilisateur = $('#tableau_document_utilisateur').DataTable({
      
      "autoWidth": false,
      "order": [[ 0, "desc" ]],
      "columnDefs": [ 
          {
            "targets": 0,
            "data": 'nom_repertoire',
            "defaultContent": ""
        }],
      language: language_datatable
  });
    
    
  $('#tableau_document_utilisateur').on( 'click', 'tr', function () {
        if ( $(this).hasClass('selected') ) {
            //$(this).removeClass('selected');
        }
        else {
            $('#bouton_supprimer_dossier_selectionne').show();
            
            table_document_utilisateur.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            
            repertoire_selectionne=table_document_utilisateur.row( $(this) ).data().full_data;
            affiche_gallerie();
        }
    });
    
    
}

    
function MyRepertoire(){
    this.nom_repertoire='';
    this.file_count=0;
    this.full_data='';
}

// Fonction pour traiter les données de répertoire (réutilisable)
function processDirectoryData(data) {
    // Si la réponse est une chaîne (erreur), la parser
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch (e) {
            // Si ce n'est pas du JSON, traiter comme une erreur
            if (data === "-1" || data === "-2") {
                swal('Pas de document disponible !','Vous ne possedez pas de document.','error');
                return;
            }
            console.error('Erreur de parsing JSON:', e, 'Data:', data);
            swal('Erreur réseau !','Format de réponse invalide du serveur.','error');
            return;
        }
    }
    
    // Vérifier si c'est une erreur JSON
    if (data && data.error) {
        console.error('Erreur du serveur:', data.error);
        swal('Erreur réseau !','Un problème est survenu: ' + data.error,'error');
        return;
    }

    if (data==="-1" || data==="-2")
    {
        swal('Pas de document disponible !','Vous ne possedez pas de document.','error');
    }else
    {
        
        var totalObj=0;
        
        // Vérifier que data.tree existe
        if (!data || !data.tree) {
            console.error('Format de réponse invalide - data.tree manquant:', data);
            swal('Erreur réseau !','Format de réponse invalide du serveur.','error');
            return;
        }
        
        var data_tree=data.tree;
        
        $.each(data_tree, function(index, element) {
            totalObj++;
        });
        if (totalObj>0){
            
            var dirs=data.tree.dirs;
            
            var total_directorie=0;
            for (var key1 in dirs) {
                 total_directorie++;
            }	
            if (total_directorie>0){
                
                for (var key in dirs) {
                     
                     var f_cout=0;
                     if (dirs[key]["files"]!=null){
                          f_cout=dirs[key]["files"].length;
                     }
                    
                     
                     var myrepertoire=new MyRepertoire();
                     
                    var temp=dirs[key]["folder"];
                    
                    var formatted_data=temp.slice(0,temp.length-11)+"<br />"+temp.slice(temp.length-8,temp.length) +"<br />"+"Nb document : "+ f_cout; 
                     myrepertoire.nom_repertoire=formatted_data;
                         
                     myrepertoire.full_data=dirs[key]["folder"];
                     myrepertoire.file_count=f_cout;
                     
                     datadirectoryContents.push(myrepertoire);
                 
                }
                //datadirectoryContents.sortOn("nom_repertoire",Array.DESCENDING);
                
                
                
                if (datadirectoryContents.length>0){
                    //table_document_utilisateur = $('#tableau_activite_id').DataTable();
                    table_document_utilisateur.clear();
                    table_document_utilisateur.rows.add(datadirectoryContents);
                    table_document_utilisateur.draw();
                    
                    if (action_a_ouverture_galerie!==''){
                        
                        table_document_utilisateur.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
                            var data = this.data();
                        
                             if (data.full_data===action_a_ouverture_galerie){
                                  table_document_utilisateur.row(rowIdx).nodes().to$().addClass('selected');
                             }
                        });
                        
                        repertoire_selectionne=action_a_ouverture_galerie;
                        affiche_gallerie();
                    }
                }else{
                    //table = $('#tableau_activite_id').DataTable();
                    table_document_utilisateur.clear();
                    table_document_utilisateur.draw();
                }

                if (parseInt(data.size_max_pc)>0){
                    
                    var size_max_pc=data.size_max_pc;
                    var size_of_user=data.size_of_user;
                    espace_utilise=100*(parseInt(size_of_user)/parseInt(size_max_pc));
                    $('#text_poucent_utilise').html(espace_utilise.toFixed(2)+'%');
                    $('#h_bar_size_document').progressbar({
                      value: espace_utilise
                    });
                }else{
                    $('#text_poucent_utilise').html('');
                    $('#h_bar_size_document').progressbar({
                      value: 0
                    });    
                }
                
            }else{
            swal("Pas de document disponible !","Vous ne possedez pas de document.","error");
            }
        }else{
            swal("Pas de document disponible !","Vous ne possedez pas de document.","error");
        }
    }
}

function get_list_directories(repertoire){
	
    ouvre_loader_general('Chargement des dossiers...');
    
	action_a_ouverture_galerie=repertoire;
	
	table_document_utilisateur.clear();
    table_document_utilisateur.draw();
	//start_loader("Recherche des documents disponibles...");
	
	datadirectoryContents=new Array();;
	
	$('#bouton_supprimer_dossier_selectionne').hide();
	
	var myURLrequest = app_server_url + get_directory_tree_json_uri + '?ImagePath=' + encodeURIComponent(code_pc);
	
	console.log('URL request:', myURLrequest);
	console.log('app_server_url:', app_server_url);
	console.log('code_pc:', code_pc);
    
    // Spécifier explicitement que la réponse est du JSON avec $.ajax pour un meilleur contrôle
    var jqxhr = $.ajax({
        url: myURLrequest,
        method: 'GET',
        dataType: 'json',
        timeout: 30000,
        // Accepter les erreurs JSON du serveur
        accepts: {
            json: 'application/json'
        }
    })

    .done(function(data, textStatus, jqXHR ) {
        console.log('Réponse reçue:', data);
        processDirectoryData(data);
    })
  .fail(function(jqXHR, textStatus, errorThrown) {
        table_document_utilisateur.clear();
        table_document_utilisateur.draw();
        console.error('Erreur AJAX:', {
            status: jqXHR.status,
            statusText: jqXHR.statusText,
            textStatus: textStatus,
            errorThrown: errorThrown,
            responseText: jqXHR.responseText,
            url: myURLrequest
        });
        
        // Si c'est une erreur de parsing JSON (status 200 mais erreur de parsing), essayer de parser manuellement
        if (jqXHR.status === 200 && jqXHR.responseText) {
            try {
                var parsedData = JSON.parse(jqXHR.responseText);
                console.log('JSON parsé manuellement, traitement des données...', parsedData);
                if (parsedData && parsedData.tree) {
                    // Réessayer avec les données parsées
                    processDirectoryData(parsedData);
                    return;
                }
            } catch (e) {
                console.error('Erreur de parsing JSON:', e);
            }
        }
        
     swal('Erreur réseau !','Un problème réseau est survenu.\nImpossible d\'actualiser les dossiers.\n\nStatus: ' + jqXHR.status + '\nErreur: ' + textStatus,'error');   

  })
  .always(function() {
ferme_loader_general();
  })
  ;    
}  
    
function supprime_repertoire() {
			
  var myURLrequest = app_server_url + efface_repertoire_uri;
    
    // Forcer une réponse texte (évite que jQuery "devine" un type et parse en objet)
    jqxhr_activite = $.ajax({
        url: myURLrequest,
        method: 'POST',
        dataType: 'text',
        data: { repertoire_a_supprimer: code_pc + "/" + repertoire_selectionne },
        timeout: 30000
    })

    .done(function(data, textStatus, jqXHR ) {

        var jsonString = (data !== undefined && data !== null) ? ('' + data) : '';
        // Normaliser la réponse (certaines versions renvoient return_txt=... ou des retours ligne)
        jsonString = jsonString.replace('return_txt=', '').trim();
						
        // Succès attendu: "1"
        if (jsonString === "1")
        {
            swal("Opération réussie !","Le dossier a été supprimé.","success");

            $('#links').html('');
            get_list_directories("");
        }
        else
        {
            console.error('Suppression dossier: réponse inattendue', myURLrequest, data, jsonString);
            swal("Erreur technique !","Le dossier n'a pas été supprimé.\n\nRéponse: " + jsonString,"error");
        }
            
    })
  .fail(function(jqXHR, textStatus, errorThrown) {
     console.error('Suppression dossier: AJAX fail', {
        url: myURLrequest,
        status: jqXHR.status,
        statusText: jqXHR.statusText,
        textStatus: textStatus,
        errorThrown: errorThrown,
        responseText: jqXHR.responseText
     });
     swal("Erreur technique !","Le dossier n'a pas été supprimé.\n\nStatus: " + jqXHR.status + "\nErreur: " + textStatus,"error");

  })
  .always(function() {

  })
  ;
}

function spec_image(){
    this.title='';
    this.href='';
    this.type='';
    this.thumbnail='';
}
    
function affiche_gallerie(){
    
    var $data={
        base_url : base_url_galerie,
        base_url_thumb : base_url_thumb,
        code_pc : code_pc,
        code_moyen : repertoire_selectionne
    }
    
    var jqxhr = $.post(base_url_galerie+backend,$data)

    .done(function(data, textStatus, jqXHR ) {

       
        
       var index_taille_user=data.indexOf("</items>");
	   var size_of_user=data.slice(index_taille_user+8,data.length)
	
       data=data.slice(0,index_taille_user+8);
	
        $xml = $( $.parseXML( data ) );

        var array_of_image=new Array();
        $('#links').html('');
        $('#links').html('<ul id="og-grid" class="og-grid"></ul>');
        
        $xml.find('items > item').each(function(index,elem){
            
            var $entry = $(this);
                
            
            var spec_image_pass= {
                title:$entry.attr('label'),
                href:($entry.attr('source')).replace('thumb/',''),
                type: 'image/jpeg',
                thumbnail:$entry.attr('source')
            };
            var bouton_sup_image='<div id="container_bouton_sup_document"><a href="#" id="bouton_supprimer_document"><img src="<?php echo base_url();?>images/trash_recyclebin_empty_closed.png" alt="Supprimer le document"></a></div>';
            
            $('#og-grid').append('<li>'+bouton_sup_image+'<a href="'+spec_image_pass.href+'" title="'+spec_image_pass.title+'" data-gallery data-description="This is a banana."><img src="'+spec_image_pass.thumbnail+'" title="'+spec_image_pass.title+'" alt="'+spec_image_pass.title+'"></a><br />'+spec_image_pass.title+'</li>');
            
             array_of_image.push(spec_image_pass);
        });
        
        $( "#og-grid" ).find('li').hover(function() {
            $(this).find('#container_bouton_sup_document').show('fast');
        },function() {
            $(this).find('#container_bouton_sup_document').hide('fast');
        });
        
        $( "#og-grid" ).find('li').each(function() {
            var src_path=$(this).children('a').children('img').attr('title');
            
            $(this).find('#container_bouton_sup_document').click(function() {
                
        swal({   title: "ATTENTION !",   text: "Ce document va être supprimés.\n\nEtes-vous sûr ?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Oui",   cancelButtonText: "Non", closeOnConfirm: false }, function(){   supprime_document(src_path); });
                
        /*if (confirm('ATTENTION !\nCe document va être supprimés.\n\nEtes-vous sûr ?')){
            supprime_document(src_path);   
        }*/
        });
    });
        
        
      })
  .fail(function(jqXHR, textStatus, errorThrown) {
      swal('Erreur réseau !','Affichage des documents impossible...','error'); 
      
  })
  .always(function() {

  })
  ;        
}

function supprime_document (photo_a_supprimer) {
			
	//		parent.start_loader("Suppression du document en cours ..");
			
    var $data={
        repertoire_a_supprimer : code_pc+"/"+repertoire_selectionne,
		fichier_a_supprimer : photo_a_supprimer
    }
    
    // Forcer une réponse texte (évite que jQuery "devine" un type et parse en objet)
    var jqxhr = $.ajax({
        url: app_server_url + supprime_photo_galerie_uri,
        method: 'POST',
        dataType: 'text',
        data: $data,
        timeout: 30000
    })

    .done(function(data, textStatus, jqXHR ) {
        var buf = (data !== undefined && data !== null) ? ('' + data) : '';
        buf = buf.replace('return_txt=', '').trim();

        if (buf === "1") {
            swal('Succès !','Suppression du document effectuée.','success'); 
            get_list_directories(repertoire_selectionne);
        } else {
            console.error('Suppression document: réponse inattendue', app_server_url + supprime_photo_galerie_uri, data, buf);
            swal('Erreur !','Suppression du document impossible...\n\nRéponse: ' + buf,'error'); 
        }
       
 
    })
  .fail(function(jqXHR, textStatus, errorThrown) {
      console.error('Suppression document: AJAX fail', {
        url: app_server_url + supprime_photo_galerie_uri,
        status: jqXHR.status,
        statusText: jqXHR.statusText,
        textStatus: textStatus,
        errorThrown: errorThrown,
        responseText: jqXHR.responseText
      });
      swal('Erreur réseau !','Suppression du document impossible...\n\nStatus: ' + jqXHR.status + '\nErreur: ' + textStatus,'error'); 
      
  })
  .always(function() {

  })
  ;        
    
}
    
function ouvre_loader_general(texte){
    $('#text_div_pour_loader').html('<p>'+texte+'</p>');
    $('#div_pour_loader').slideDown('fast');
}
function ferme_loader_general(){
    $('#div_pour_loader').slideUp('fast');
}    
    
$(document).ready( function () {
    
     InitOverviewDataTable_document_utilisateur();
    
    var rep='<?php if(isset($repertoire_a_ouvrir)) echo $repertoire_a_ouvrir;?>';
    //swal(rep);
    get_list_directories(rep);
    
    $('#h_bar_size_document').progressbar({
      value: 0
    });    
    
    $('#bouton_supprimer_dossier_selectionne').click(function() {
        swal({   title: "ATTENTION !",   text: "Ce dossier ainsi que tous les documents associés vont être supprimés.\n\nEtes-vous sûr ?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Oui",   cancelButtonText: "Non", closeOnConfirm: false }, function(){   supprime_repertoire(); });
        
        /*if (confirm('ATTENTION !\nCe dossier ainsi que tous les documents associés vont être supprimés.\n\nEtes-vous sûr ?')){
            supprime_repertoire();   
        }*/
    });
    
    
});
    
</script>
    
</body>
</html>
