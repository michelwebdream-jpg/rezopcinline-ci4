<?php

namespace App\Controllers;

use App\Libraries\Googlemaps;
use CodeIgniter\Controller;

class Membres extends BaseController
{
    protected $session;
    protected $googlemaps;
    
    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->googlemaps = new Googlemaps();
    }
    
    public function index()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        // Initialize our map
        $config = [
            'center' => 'France',
            'zoom' => '5'
        ];
        
        $this->googlemaps->initialize($config);
        
        // Create the map
        $data['map'] = $this->googlemaps->create_map();
        
        $data['titre'] = 'REZO+ PC INLINE';
        $data['heading'] = 'Bienvenue dans REZO+ PC InLine';
        $data['footing'] = footer_html();
        $data['base_url'] = base_url();
        $data['utilisateur'] = $this->session->get('deliverdata');
        
        return view('template/template_main', $data);
    }
    
    public function mon_compte()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }
        
        $data['titre'] = 'REZOPCINLINE';
        $data['heading'] = 'Mon compte';
        $data['content'] = "mon_compte";
        $data['footing'] = footer_html();
        
        return view('template/template', $data);
    }

    /**
     * Carte plein écran pour afficher sur un deuxième écran.
     * GET /membres/carte-ecran2
     * ?format=json pour l'API (positions depuis REZO).
     */
    public function carteEcran2()
    {
        if (!$this->session->get('login') || !$this->session->get('logged')) {
            return redirect()->to('/signup/login');
        }

        $activeGeolocMinutes = 30;
        $rezoCodeColumn = 'moncode';
        $googleMapsApiKey = env('GOOGLE_MAPS_API_KEY', 'AIzaSyBfDAk5Xb1ZDwMDNj5qBitkVRSec3YlXic');
        $refreshIntervalSeconds = 10;

        $activeUsers = $this->fetchPositionsRezo($activeGeolocMinutes, $rezoCodeColumn);

        $codeAdmin = (string) ($this->session->get('deliverdata')['code_administrateur'] ?? '');
        $maPosition = null;
        foreach ($activeUsers as $u) {
            if (($u['code'] ?? '') === $codeAdmin && isset($u['latitude'], $u['longitude']) && ($u['latitude'] != 0 || $u['longitude'] != 0)) {
                $maPosition = ['code' => $u['code'], 'latitude' => $u['latitude'], 'longitude' => $u['longitude']];
                break;
            }
        }

        if (strtolower((string) $this->request->getGet('format')) === 'json') {
            return $this->response
                ->setContentType('application/json; charset=UTF-8')
                ->setBody(json_encode([
                    'active_geoloc_users' => $activeUsers,
                    'active_geoloc_minutes' => $activeGeolocMinutes,
                    'ma_position' => $maPosition,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $activeUsersWithCoords = array_values(array_filter($activeUsers, function ($u) {
            return isset($u['latitude'], $u['longitude']) && ($u['latitude'] != 0 || $u['longitude'] != 0);
        }));

        $data = [
            'activeUsersWithCoords' => $activeUsersWithCoords,
            'maPosition' => $maPosition,
            'googleMapsApiKey' => $googleMapsApiKey,
            'refreshIntervalSeconds' => $refreshIntervalSeconds,
        ];

        return view('membres/carte_ecran2', $data);
    }

    /**
     * Positions actives depuis la table REZO (dernières X minutes).
     */
    protected function fetchPositionsRezo(int $minutes, string $rezoCodeColumn): array
    {
        $activeUsers = [];
        $db = \Config\Database::connect();
        if (!$db->connect()) {
            return $activeUsers;
        }
        $col = in_array($rezoCodeColumn, ['code', 'code_administrateur', 'moncode'], true)
            ? $rezoCodeColumn : 'moncode';
        $sql = "SELECT `{$col}` AS code, derniere_inscription, latitude, longitude FROM REZO " .
               "WHERE derniere_inscription >= DATE_SUB(NOW(), INTERVAL ? MINUTE) " .
               "AND latitude IS NOT NULL AND longitude IS NOT NULL " .
               "ORDER BY derniere_inscription DESC";
        $res = $db->query($sql, [$minutes]);
        if (!$res) {
            return $activeUsers;
        }
        foreach ($res->getResultArray() as $row) {
            $lat = (float) ($row['latitude'] ?? 0);
            $lng = (float) ($row['longitude'] ?? 0);
            if ($lat !== 0.0 || $lng !== 0.0) {
                $activeUsers[] = [
                    'code' => (string) ($row['code'] ?? ''),
                    'derniere_inscription' => $row['derniere_inscription'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                ];
            }
        }
        return $activeUsers;
    }
}

