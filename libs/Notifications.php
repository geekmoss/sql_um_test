<?php

/**
 * Třída Notifications vytváří nástroj pro přidávání oznámení zobrazující se při následném načtení stránky
 *
 * Rozšířeno o možnot dodat název CSS třídy pro stylování různých typů oznámení.
 */
class Notifications {
    /** @const string Konstanta označující chybové oznámení */
    const BOOTSTRAP_ERROR = 'alert alert-danger';
    /** @var string Konstanta označující infomační oznámení */
    const BOOTSTRAP_INFO = 'alert alert-info';
    /** @var string Konstanta označující oznámení o úspěchu */
    const BOOTSTRAP_SUCCESS = 'alert alert-success';
    /** @var string Konstanta označující varovné oznámení */
    const BOOTSTRAP_WARNING = 'alert alert-warning';

    /** @var string Sekce v SESSION vyhrazená pro Notifications */
    private $section = 'Lib_Notification';

    /**
     * Inicializace sekce v SESSION
     */
    public function __construct() {
        if (!isset($_SESSION[$this->section])) {
            $_SESSION[$this->section] = array();
        }
    }

    /**
     * Metoda pro přidání nového oznámení
     * @param string $notification Obsah notifikace
     * @param string $type Název CSS Class - výchozí konstanta BOOTSTRAP_INFO
     */
    public function addNew($notification, $type = self::BOOTSTRAP_INFO) {
        $array = $_SESSION[$this->section];

        $array[] = [$notification, $type];

        $_SESSION[$this->section] = $array;
    }

    /**
     * Metoda pro získání oznámení ze SESSION
     * @return array
     */
    public function getNotifications() {
        if (sizeof($_SESSION[$this->section])) {
            $s = $_SESSION[$this->section];
            $this->clearBuffer();
            return $s;
        }
        else {
            return array();
        }
    }

    /**
     * Metoda pro získání vykreslených oznámeních
     *
     * @return string
     */
    public function getNotificationsInHTML() {
        if (sizeof($_SESSION[$this->section])) {
            $s = $_SESSION[$this->section];
            $this->clearBuffer();
            $string = '';
            foreach ($s as $n) {
                $string .= "<div class='$n[1]'>$n[0]</div>";
            }
            return $string;
        }
        else {
            return '';
        }
    }

    /**
     * Metoda čistící sekci v SESSION
     */
    private function clearBuffer() {
        $_SESSION[$this->section] = null;
    }
}
