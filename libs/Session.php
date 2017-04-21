<?php

/**
 * Třída Session tvoří jednoduché objektivní rozhraní pro globální promměnou $_SESSION
 *
 * Výhoda tohoto rozhraní je práce se sekcemi rozdělující velký prostor SESSION na jednotlivé sekce.
 * Minimalizuje se tím kolize názvů proměnných a případné přepisování dat.
 *
 * @author J. Janeček
 */
class Session {

    /** @var string */
    protected $group = '_F';
    /** @var string */
    protected $section;

    /**
     * @param string $section
     */
    public function __construct($section)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            trigger_error('Session is not started', E_USER_ERROR);
        }
        else {
            $this->section = $section;
            if (!$this->sectionIsExist()) {
                $_SESSION[$this->group][$this->section] = [];
            }
        }
    }

    /**
     * Vrací hodnotu z požadované proměnné
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        if (!array_key_exists($var, $_SESSION[$this->group][$this->section])) {
            trigger_error("Variable <b>$var</b> does not exist in section <b>$this->section</b>", E_USER_NOTICE);
        } else {
            return $_SESSION[$this->group][$this->section][$var];
        }

    }

    /**
     * Vytváří novou proměnnou a nastavuje jí hodnotu
     * @param string $var
     * @param mixed $value
     */
    public function __set($var, $value)
    {
        $_SESSION[$this->group][$this->section][$var] = $value;
    }

    /**
     * Zda-li proměnná existuje
     * @param $var
     * @return bool
     */
    public function __isset($var)
    {
        return isset($_SESSION[$this->group][$this->section][$var]);
    }

    /**
     * Odstranění proměnné
     * @param string $var
     */
    public function __unset($var)
    {
        unset($_SESSION[$this->group][$this->section]);
    }

    /**
     * Metoda tvočící novou sekci
     *
     * @return bool
     */
    protected function createSection()
    {
        if (!$this->sectionIsExist()) {
            $_SESSION[$this->group][$this->section] = array();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Zda-li sekce existuje
     *
     * @return bool
     */
    protected function sectionIsExist()
    {
        if (isset($_SESSION[$this->group][$this->section])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vyčistí a zníčí sekci
     *
     * @return bool
     */
    public function destroySection()
    {
        if ($this->sectionIsExist()) {
            unset($_SESSION[$this->group][$this->section]);
            return true;
        } else {
            return false;
        }
    }
}