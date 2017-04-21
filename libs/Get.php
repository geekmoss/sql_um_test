<?php

/**
 * Class Get
 *
 * @author J. Janeček
 *
 */
class Get {
    /** @var array */
    protected $array;

    public function __construct()
     {
         $this->array = $_GET;
     }

     /**
     * Vrací hodnotu z požadované proměnné
     * @param $var
     * @return mixed
     */
     public function __get($var)
     {
         if (!array_key_exists($var, $this->array)) {
             trigger_error("Variable <b>$var</b> does not exist", E_USER_NOTICE);
         } else {
             return $this->array[$var];
         }
     }

     /**
     * Zda-li proměnná existuje
     * @param $var
     * @return bool
     */
     public function __isset($var)
     {
         return isset($this->array[$var]);
     }
 }