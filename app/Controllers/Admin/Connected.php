<?php

namespace App\Controllers\Admin;

use App\Models\LoginConnectionModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

/**
 * Intégration "Utilisateurs connectés" dans le back-office admin.
 * Réplique la logique de public/check_connected.php (sessions, géoloc, charge BDD, carte).
 */
class Connected extends BaseAdmin
{
    protected int $activeMinutes = 15;
    protected int $sessionLifetime = 7200;
    protected int $activeGeolocMinutes = 30;
    protected string $rezoCodeColumn = 'moncode';
    protected int $mapRefreshIntervalSeconds = 5;
    protected bool $mapAutoRecenteringDefault = true;
    protected LoginConnectionModel $loginConnectionModel;
    protected int $loginConnectionsPerPage = 10;

    public function __construct()
    {
        parent::__construct();
        $this->loginConnectionModel = model(LoginConnectionModel::class);
    }

    /**
     * Page principale : liste des connectés, actifs géoloc, charge BDD, carte.
     * ?format=json pour l’API (rafraîchissement carte / charge BDD).
     */
    public function index(): ResponseInterface
    {
        $redirect = $this->ensureAdmin();
        if ($redirect !== null) {
            return $redirect;
        }

        $format = strtolower((string) $this->request->getGet('format')) === 'json' ? 'json' : 'html';

        $sessionPath = config('Session')->savePath;
        $windowSeconds = $this->activeMinutes * 60;
        $cutoff = time() - $windowSeconds;

        $users = $this->readSessionsFromPath($sessionPath, $cutoff);
        $activeUsers = [];
        $dbLoad = null;
        $loginStats = ['total' => 0, 'last_24h' => 0, 'last_7d' => 0];
        $recentLoginConnections = [];
        $loginConnectionsPager = null;

        $db = \Config\Database::connect();
        if ($db->connect()) {
            $activeUsers = $this->fetchActiveGeolocUsers($db);
            $dbLoad = $this->fetchDbLoad($db);
        }

        try {
            $nowParis = Time::now('Europe/Paris');
            $since24h = $nowParis->subHours(24)->toDateTimeString();
            $since7d = $nowParis->subDays(7)->toDateTimeString();

            $loginStats = [
                'total' => (int) $this->loginConnectionModel->builder()->countAllResults(),
                'last_24h' => (int) $this->loginConnectionModel->builder()
                    ->where('connected_at >=', $since24h)
                    ->countAllResults(),
                'last_7d' => (int) $this->loginConnectionModel->builder()
                    ->where('connected_at >=', $since7d)
                    ->countAllResults(),
            ];

            $recentLoginConnections = $this->loginConnectionModel
                ->orderBy('connected_at', 'DESC')
                ->paginate($this->loginConnectionsPerPage, 'login_connections');
            $loginConnectionsPager = $this->loginConnectionModel->pager;
        } catch (\Throwable $e) {
            log_message('warning', 'Admin Connected: login connections table unavailable.');
        }

        $googleMapsApiKey = env('GOOGLE_MAPS_API_KEY', 'AIzaSyBfDAk5Xb1ZDwMDNj5qBitkVRSec3YlXic');

        if ($format === 'json') {
            return $this->response
                ->setContentType('application/json; charset=UTF-8')
                ->setBody(json_encode([
                    'count'                  => count($users),
                    'users'                  => $users,
                    'active_geoloc_count'    => count($activeUsers),
                    'active_geoloc_users'   => $activeUsers,
                    'active_geoloc_minutes'  => $this->activeGeolocMinutes,
                    'window_seconds'         => $windowSeconds,
                    'active_minutes'         => $this->activeMinutes,
                    'db_load'                => $dbLoad,
                    'login_stats'            => $loginStats,
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $activeUsersWithCoords = array_values(array_filter($activeUsers, function ($u) {
            return isset($u['latitude'], $u['longitude']) && ($u['latitude'] != 0 || $u['longitude'] != 0);
        }));

        $data = [
            'titre'           => 'Utilisateurs connectés',
            'content'         => 'admin/connected',
            'utilisateur'     => $this->session->get('deliverdata') ?? [],
            'users'           => $users,
            'activeUsers'     => $activeUsers,
            'activeUsersWithCoords' => $activeUsersWithCoords,
            'dbLoad'          => $dbLoad,
            'activeMinutes'   => $this->activeMinutes,
            'activeGeolocMinutes' => $this->activeGeolocMinutes,
            'mapRefreshIntervalSeconds' => $this->mapRefreshIntervalSeconds,
            'mapAutoRecenteringDefault' => $this->mapAutoRecenteringDefault,
            'googleMapsApiKey' => $googleMapsApiKey,
            'jsonUrl'         => base_url('admin/connected') . '?format=json',
            'loginStats'      => $loginStats,
            'recentLoginConnections' => $recentLoginConnections,
            'loginConnectionsPager' => $loginConnectionsPager,
        ];

        return $this->response->setBody(view('admin/template_admin', $data));
    }

    /**
     * Lit les sessions actives depuis le répertoire des fichiers de session.
     * Utilise session_decode en préservant la session courante (admin).
     */
    protected function readSessionsFromPath(string $sessionPath, int $cutoff): array
    {
        $users = [];
        if (!is_dir($sessionPath)) {
            return $users;
        }

        $savedId = session_id();
        if ($savedId !== '') {
            session_write_close();
        }

        session_id(uniqid('', true));
        session_start();

        $files = glob($sessionPath . '/*');
        foreach ($files as $file) {
            if (!is_file($file) || filemtime($file) < $cutoff) {
                continue;
            }
            $raw = @file_get_contents($file);
            if ($raw === false || $raw === '') {
                continue;
            }
            $_SESSION = [];
            if (@session_decode($raw) === false) {
                continue;
            }
            if (empty($_SESSION['logged']) || empty($_SESSION['login'])) {
                continue;
            }
            $deliver = $_SESSION['deliverdata'] ?? [];
            $users[] = [
                'code'          => (string) ($_SESSION['login'] ?? ''),
                'nom'           => (string) (is_array($deliver) ? ($deliver['nom_administrateur'] ?? '') : ''),
                'prenom'        => (string) (is_array($deliver) ? ($deliver['prenom_administrateur'] ?? '') : ''),
                'mail'          => (string) (is_array($deliver) ? ($deliver['mail_administrateur'] ?? '') : ''),
                'indicatif'     => (string) (is_array($deliver) ? ($deliver['indicatif_administrateur'] ?? '') : ''),
                'last_activity' => (int) filemtime($file),
            ];
        }

        session_destroy();
        if ($savedId !== '') {
            session_id($savedId);
            session_start();
        }

        return $users;
    }

    /**
     * Utilisateurs actifs (géolocalisation récente) depuis la table REZO.
     */
    protected function fetchActiveGeolocUsers($db): array
    {
        $activeUsers = [];
        $col = in_array($this->rezoCodeColumn, ['code', 'code_administrateur', 'moncode'], true)
            ? $this->rezoCodeColumn : 'moncode';
        $sql = "SELECT `{$col}` AS code, derniere_inscription, latitude, longitude FROM REZO " .
               "WHERE derniere_inscription >= DATE_SUB(NOW(), INTERVAL ? MINUTE) " .
               "AND latitude IS NOT NULL AND longitude IS NOT NULL " .
               "ORDER BY derniere_inscription DESC";
        $res = $db->query($sql, [$this->activeGeolocMinutes]);
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

    /**
     * Charge BDD (SHOW GLOBAL STATUS + max_connections).
     */
    protected function fetchDbLoad($db): ?array
    {
        $res = $db->query('SHOW GLOBAL STATUS');
        if (!$res) {
            return null;
        }
        $status = [];
        foreach ($res->getResultArray() as $row) {
            $status[$row['Variable_name'] ?? $row[0] ?? ''] = $row['Value'] ?? $row[1] ?? '';
        }
        $maxConn = null;
        $res2 = $db->query("SHOW VARIABLES LIKE 'max_connections'");
        if ($res2) {
            $r = $res2->getRowArray();
            if ($r) {
                $maxConn = (int) ($r['Value'] ?? $r[1] ?? 0);
            }
        }
        return [
            'threads_connected' => (int) ($status['Threads_connected'] ?? 0),
            'threads_running'   => (int) ($status['Threads_running'] ?? 0),
            'connections'        => (int) ($status['Connections'] ?? 0),
            'questions'          => (int) ($status['Questions'] ?? 0),
            'slow_queries'      => (int) ($status['Slow_queries'] ?? 0),
            'uptime_seconds'     => (int) ($status['Uptime'] ?? 0),
            'max_connections'    => $maxConn,
        ];
    }
}
