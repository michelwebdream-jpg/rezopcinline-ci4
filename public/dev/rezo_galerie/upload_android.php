<?php

header('Expires: Thu, 01 Jan 1970 00:00:00 GMT, -1');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

// PHP 8.x: ne pas afficher les erreurs (mais les logger côté serveur)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function remove_accents($val)
{
    $val = iconv('UTF-8','ASCII//TRANSLIT',$val);
    
    return $val;
}

/*function remove_accents1($str){
  return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

function remove_accents2($string) {
    if ( !preg_match('/[\x80-\xff]/', $string) )
        return $string;

    $chars = array(
    // Decompositions for Latin-1 Supplement
    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
    chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
    chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
    chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
    chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
    chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
    chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
    chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
    chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
    chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
    chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
    chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
    chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
    chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
    chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
    chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
    chr(195).chr(191) => 'y',
    // Decompositions for Latin Extended-A
    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
    chr(197).chr(190) => 'z', chr(197).chr(191) => 's'
    );

    $string = strtr($string, $chars);

    return $string;
}*/

// AUTOLOAD CLASSES (PHP 8.x compatible)
if (!function_exists('autoload_classes')) {
    function autoload_classes($class_name) {
        $file = __DIR__ . '/classes/class_' . $class_name . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}
spl_autoload_register('autoload_classes');

function ecrit_imaga_dans_bdd($imgname_pass,$code_user)
{
    // Sur certains environnements (serveur de test sans BDD), on ne veut pas bloquer l'upload.
    try {
        if (!class_exists('DbConnect')) {
            return;
        }
        $db = new DbConnect();
        if (method_exists($db, 'show_errors')) {
            $db->show_errors();
        }
        if (method_exists($db, 'query')) {
            $db->query("SET NAMES 'utf8'");
            $imgname_pass = (string) $imgname_pass;
            $code_user = (string) $code_user;
            // NOTE: ancien comportement: stocke le chemin disque dans la BDD.
            $sql = "UPDATE `REZO` SET `document_envoye`='" . addslashes($imgname_pass) . "' WHERE `moncode`='" . addslashes($code_user) . "';";
            $db->query($sql);
        }
    } catch (Throwable $e) {
        // Ne pas casser l'upload en cas de problème DB
        error_log('[upload_android.php] DB update skipped: ' . $e->getMessage());
    }
    
}
function makeThumbnails($updir, $img)
{
    $thumbnail_width = 150;
    $thumbnail_height = 150;
    $thumb_beforeword = "thumb";
    $arr_image_details = getimagesize("$updir" .'/'. "$img"); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($arr_image_details[2] == 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }
    if ($imgt) {
        $old_image = $imgcreatefrom("$updir" . '/' . "$img");
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, "$updir" . '/thumb/' . "$img");
    }
}

function GetDirectorySize($path){
    $bytestotal = 0;
    $path = realpath($path);
    if($path!==false){
        try {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                $bytestotal += $object->getSize();
            }
        } catch (Throwable $e) {
            // ignorer erreurs de lecture
        }
    }
    return $bytestotal;
}


error_reporting(E_ALL);

if(isset($_POST['ImageName']) && isset($_POST['ImagePath']) && isset($_POST['FinalPath'])){

    // ------------------------------------------------------------
    // Option B: on n'accepte plus un chemin disque fourni par le client.
    // ImagePath doit représenter le code PC (ou un chemin qui le contient).
    // Les fichiers sont enregistrés dans: public/dev/rezo_galerie/<CODE_PC>/<DOSSIER>/
    // ------------------------------------------------------------

    $imgNameRaw = (string) $_POST['ImageName'];
    $imagePathRaw = (string) $_POST['ImagePath'];
    $finalPathRaw = (string) $_POST['FinalPath'];

    // Extraire un "code PC" depuis ImagePath (supporte ancien format: "../rezo_galerie/<code>")
    $codePc = basename(str_replace('\\', '/', trim($imagePathRaw)));
    $codePc = preg_replace('/[^a-zA-Z0-9_-]/', '', $codePc);

    if ($codePc === '') {
        echo "-3";
        exit;
    }

    $baseDir = __DIR__ . '/' . $codePc;
    if (!is_dir($baseDir)) {
        // créer le dossier code_pc si absent
        @mkdir($baseDir, 0777, true);
    }

    if (is_dir($baseDir)) {
        
        $final_path = remove_accents($finalPathRaw);
        $final_path = trim((string) $final_path);
        // éviter traversals / slashs
        $final_path = str_replace(['..', '\\', "\0"], '', $final_path);
        $final_path = trim($final_path, "/ \t\n\r\0\x0B");
        // restreindre aux chars acceptables (dossier)
        $final_path = preg_replace('/[^a-zA-Z0-9 _\\-\\.]/', '_', $final_path);
        if ($final_path === '') {
            $final_path = 'uploads';
        }
        
        if (!file_exists($baseDir . "/" . $final_path)) {
            mkdir($baseDir . "/" . $final_path, 0777, true);
        }
        if (!file_exists($baseDir . "/" . $final_path . "/thumb")) {
            mkdir($baseDir . "/" . $final_path . "/thumb", 0777, true);
        }
    
        // sécuriser le nom du fichier
        $imgName = basename($imgNameRaw);
        $imgName = remove_accents($imgName);
        $imgName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $imgName);
        $imgName = ltrim($imgName, '.');

        $ext = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) {
            echo "-1";
            exit;
        }

        $imgname = $baseDir . "/" . $final_path . "/" . $imgName;

        $b64 = isset($_POST['base64']) ? (string) $_POST['base64'] : '';
        $b64 = str_replace(' ', '+', $b64);
        // Support data URL: data:image/jpeg;base64,....
        if (strpos($b64, 'base64,') !== false) {
            $b64 = substr($b64, strpos($b64, 'base64,') + 7);
        }
        $imsrc = base64_decode($b64, true);
        if ($imsrc === false || $imsrc === '') {
            echo "-1";
            exit;
        }

        $size_of_file=strlen($imsrc);


        $size_of_directory=GetDirectorySize($baseDir);
        
        $future_size=$size_of_directory+$size_of_file;

        if(isset($_POST['size_limite_galerie'])){
            
            $size_limite=intval($_POST['size_limite_galerie'])*1024*1024; /* en Mo */
            
           // echo '$size_of_file : '.$size_of_file;
            //echo '$size_of_directory : '.$size_of_directory;
            //echo 'size limite : '.$size_limite;
            //echo '$future_size : '.$future_size;
            
            if ($future_size<$size_limite)
            {
                $fp = fopen($imgname, 'w');
                fwrite($fp, $imsrc);
                if(fclose($fp)){
                    makeThumbnails($baseDir . "/" . $final_path, $imgName);
                    if(isset($_POST['codeuser'])){
                        ecrit_imaga_dans_bdd($imgname, (string) $_POST['codeuser']);
                    }
                    echo "1";
                }else{
                    echo "-1";
                }    
            }else{
                    echo "-2";
            }
        }else{
            echo "-1";
        }
    }else{
        echo "-3";
    }
}else{
    echo "-1";
}

?>