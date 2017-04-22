<?php

session_start();
require './autoloader.php';

# Omezený uživatel - spouštění uživatelských dotazů
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_SCHM', 'sql');

# Plná oprávnění (manipulace s tabulkami)
define('DB_ADMIN_USER', DB_USER);
define('DB_ADMIN_PASS', DB_PASS);



/** @var array $status Seznam chybových stavů */
$status = [
    'OK',                  // 0
    'Connection Error',     // 1
    'Database Error',       // 2
    'App Error',            // 3
    'Other Error',          // 4
    'Permission Error',     // 5
];

/** @var array $json_res Struktura JSON Result */
$json_res = [
    'code' => 0,
    'status' => $status[0],
    'cols' => [],
    'data' => [],
    'custom' => null,
];

/** @var array $exclude_words */
$exclude_words = [
    'TABLE',
    'DELETE',
    'INSERT',
    'UPDATE',
    'templates', // Šablony zadání
    'backup_', // Zálohovaná data sandbox tabulek
];

/** @var array $cols Názvy sloupců */
$cols = [];

/** @var Session $s */
$s = new Session('templates');
if (!isset($s->complete_templates)) {
    $s->complete_templates = [];
}
if (!isset($s->active_tid)) {
    $s->active_tid = 0;
}

/**
 * Funkce pro naplnění JSON Result a vyplyvnutí dat
 * dále funkce ukončí běh skriptu
 *
 * @param int   $code
 * @param array $data
 * @param mixed $custom
 */
function fillJsonAndExit($code, $data = [], $custom = null) {
    global $status, $json_res, $cols;
    $json_res = [
        'code' => $code,
        'status' => $status[$code],
        'cols' => $cols,
        'data' => $data,
        'custom' => $custom,
    ];

    echo json_encode($json_res);
    exit;
}

/**
 * Vrátí pole s názvy sloupců
 *
 * @param object|array $res
 */
function getColNames($res) {
    global $cols;
    // Získání prvního řádku, případně přetypovat
    if (is_object($res)) {
        $res = (array)$res[0];
    }
    else {
        $res = $res[0];
    }

    foreach ($res as $col => $data) {
        $cols[] = $col;
    }
}

/**
 * Funce pro navázání spojení a dotázání databáze
 *
 * @param string $query
 */
function dbConnectAndSendQuery($query) {
    # Nové spojení
    //$db = new Database('host', 'user', 'password', 'schema/table');
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_SCHM);

    # Kontrola spojení a zadání dotazu
    if ($db->everythinkIsOk()) {
        $res = $db->queryAllAsObject($query);
        # Kontrola dotazu a odeslání dat
        if ($db->everythinkIsOk()) {
            if (sizeof($res) > 0) {
                getColNames($res);
            }
            checkCorrectQuery($query, $res);
            fillJsonAndExit(0, $res);
        }
        else {
            fillJsonAndExit(2, $db->getExceptionMessage());
        }
    }
    else {
        fillJsonAndExit(1, $db->getExceptionMessage());
    }
}

/**
 * Zkontroluje query zda neobsahují klíčová slova jenž jsou uvedena v $exclude_words
 *
 * @param $query
 */
function checkQuery($query) {
    global $exclude_words;

    foreach ($exclude_words as $ew) {
        if (strpos(strtolower($query), strtolower($ew)) !== false) {
            fillJsonAndExit(3, 'Dotaz nesmí obsahovat: '.$ew);
        }
    }
}

/**
 * Funkce kontroluje zda-li zadané SQL je shodně s aktivní šablonou
 *
 * Vrací true v případě, že se jedná o správné řešení, false při opaku
 * Null se vrací v případě, že není aktivní žádná šablona
 *
 * @param $query
 * @param $result
 * @return bool|null
 */
function checkCorrectQuery($query, $result) {
    global $s;
    if (isset($s->active_tid) and $s->active_tid > 0 and isset($s->active_template_result) and isset($s->complete_templates)) {
        if ($s->active_template_result === json_encode($result)) {
            $tmp_array = $s->complete_templates;
            $tmp_array[$s->active_tid] = $query;
            $s->complete_templates = $tmp_array;
            return true;
        }
        else {
            return false;
        }
    }
    else {
        return null;
    }
}

# Run App

if (isset($_GET['request'])) {
    switch ($_GET['request']):
        # Spuštění SQL dotazu (předtím ještě se ověří zda-li neobsahuje zakázaná klíčová slova)
        case 'query':
            if (isset($_GET['sql'])) {
                checkQuery($_GET['sql']);
                dbConnectAndSendQuery($_GET['sql']);
            }
            else {
                fillJsonAndExit(3, 'Nebyl zadán parametr \'sql\'');
            }
            break;
        # Vrací seznam šablon
        case 'get_templates':
            $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_SCHM);
            $res = $db->queryAllAsObject('SELECT id, title FROM templates');
            fillJsonAndExit(0, $res);
            break;
        # Vrací detail konrétí šablony
        case 'get_template_detail':
            if (isset($_GET['tid'])) {
                $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_SCHM);
                $res = $db->queryOneAsObject('SELECT id, title, content FROM templates WHERE id = ?', $_GET['tid']);
                fillJsonAndExit(0, $res);
            }
            else {
                fillJsonAndExit(3, 'Nebyl zadán parametr \'tid\'');
            }
            break;
        # Aktivuje vybranou šalonu
        case 'activate_template':
            if (isset($_GET['tid'])) {
                $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_SCHM);
                $res = $db->queryOneAsObject('SELECT * FROM templates WHERE id = ?', $_GET['tid']);
                $s->active_tid = $res->id;
                $s->active_template_result = $res->result;
                $s->active_template_content = $res->content;
                $s->active_template_title = $res->title;
                $res = [
                    'active_tid' => $res->id,
                    'active_title' => $res->content,
                    'active_content' => $res->title,
                    'completed' => $s->complete_templates,
                ];
                fillJsonAndExit(0, $res);
            }
            else {
                fillJsonAndExit(3, 'Nebyl zadán parametr \'tid\'');
            }
            break;
        # Dotaz na data o aktivních a hotových šablonách
        case 'get_info_of_templates':
            if ($s->active_tid > 0) {
                $res = [
                    'active_tid' => $s->active_tid,
                    'active_title' => $s->active_template_title,
                    'active_content' => $s->active_template_content,
                    'completed' => $s->complete_templates,
                ];
            }
            else {
                $res = [
                    'active_tid' => null,
                    'active_title' => null,
                    'active_content' => null,
                    'completed' => null,
                ];
            }
            fillJsonAndExit(0, $res);
            break;
        # Přidání nové šablony
        case 'add_template':
            $admin_db = new Database(DB_HOST, DB_ADMIN_USER, DB_ADMIN_PASS, DB_SCHM);
            $res = $admin_db->query('SELECT * FROM tokens WHERE token = ? AND active = 1', $_POST['token']);
            if ($res > 0) {
                $res = $admin_db->insert('templates', [
                    'title' => $_POST['title'],
                    'content' => $_POST['content'],
                    'result' => $_POST['result'],
                ]);
                if ($res > 0) {
                    fillJsonAndExit(0, 'Scénář úspěšně uložen.');
                }
                else {
                    fillJsonAndExit(4, 'Scénař nebyl uložen, zkuste to prosím znovu.');
                }
            }
            else {
                fillJsonAndExit(5, 'Token zamítnut!');
            }
            break;
        default:
            fillJsonAndExit(3, 'Nevyhovující hodnota parametru \'request\'');
            break;
    endswitch;
}
else {
    fillJsonAndExit(3, 'Nebyl zadán parametr \'request\'');
}