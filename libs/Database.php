<?php

/**
 * Class Database
 *
 * Základ převzán z PDO Wrapperu od ITNETWORK.cz (devbook.cz)
 *
 * ===== EDIT FOR TrySQL =====
 *
 * Upravená třída pro možnost vracet stav spojení a chybné výsledky dotazů
 *
 * ===== END =====
 *
 * @author J. Janeček
 * @author itnetwork.cz
 */
class Database {

    /** @var PDO */
    private $pdo;

    /** @var bool */
    private $success = true;
    /** @var int|mixed */
    private $error_code = null;
    /** @var string */
    private $error_msg = '';

    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_EMULATE_PREPARES => false
    );

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $schema
     */
    public function __construct($host, $user, $pass, $schema) {
        try {
            $this->pdo = new PDO("mysql:dbname=$schema;host=$host", $user, $pass, $this->options);
        }
        catch (PDOException $e) {
            $this->success = false;
            $this->error_msg = $e->getMessage();
            $this->error_code = $e->getCode();
        }
    }

    /**
     * Spustí dotaz a vrátí PDO statement
     * @param array $params Pole, kde je prvním prvkem dotaz a dalšími jsou parametry
     * @return \PDOStatement PDO statement
     */
    private function executeStatement($params)
    {
        try {
            $query = array_shift($params);
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement;
        }
        catch (PDOException $e) {
            $this->success = false;
            $this->error_msg = $e->getMessage();
            $this->error_code = $e->getCode();
        }
    }

    /**
     * Spustí dotaz a vrátí počet ovlivněných řádků. Dále se předá libovolný počet dalších parametrů.
     * @param string $query Dotaz
     * @return int Počet ovlivněných řádků nebo false při neúspěchu
     */
    public function query($query) {
        $statement = $this->executeStatement(func_get_args());
        if ($this->success) {
            return $statement->rowCount();
        }
        else {
            return 0;
        }
    }

    /**
     * Spustí dotaz a vrátí objekt výsledku.
     * @param string $query Dotaz
     * @return PDOStatement
     */
    public function queryObject($query) {
        $statement = $this->executeStatement(func_get_args());
        return $statement;
    }

    /**
     * Spustí dotaz a vrátí z něj první sloupec prvního řádku. Dále se předá libovolný počet dalších parametrů.
     * @param string $query Dotaz
     * @return mixed Hodnota prvního sloupce z prvního řádku
     */
    public function querySingle($query) {
        $statement = $this->executeStatement(func_get_args());
        if ($this->success) {
            $data = $statement->fetch();
            return $data[0];
        }
        else {
            return 0;
        }
    }

    /**
     * Spustí dotaz a vrátí z něj první řádek. Dále se předá libovolný počet dalších parametrů.
     * @param string $query Dotaz
     * @return mixed Pole výsledků nebo false při neúspěchu
     */
    public function queryOne($query) {
        $statement = $this->executeStatement(func_get_args());
        if ($this->success) {
            return $statement->fetch(PDO::FETCH_ASSOC);
        }
        else {
            return 0;
        }
    }

    /**
     * Spustí dotaz a vrátí z něj první řádek. Dále se předá libovolný počet dalších parametrů.
     * @param string $query Dotaz
     * @return stdClass
     */
    public function queryOneAsObject($query) {
        $statement = $this->executeStatement(func_get_args());
        if ($this->success) {
            return $statement->fetchObject();
        }
        else {
            return 0;
        }
    }

    /**
     * Spustí dotaz a vrátí pole s jedtnolivými objekty reprezentující řádky
     * @param string $query Dotaz
     * @return mixed Pole řádků nebo false při neúspěchu
     */
    public function queryAllAsObject($query) {
        $statement = $this->executeStatement(func_get_args());
        if ($this->success) {
            return $statement->fetchAll(PDO::FETCH_OBJ);
        }
        else {
            return 0;
        }
    }

    /**
     * Umožňuje snadné vložení záznamu do databáze pomocí asociativního pole
     * @param string $table Název tabulky
     * @param array $data Asociativní pole, kde jsou klíče sloupce a hodnoty hodnoty
     * @return int Počet ovlivněných řádků
     */
    public function insert($table, $data) {
        $keys = array_keys($data);
        $this->checkIdentifiers(array($table) + $keys);
        $query = "
			INSERT "." INTO `$table` (`" . implode('`, `', $keys) . "`)
			VALUES (" . str_repeat('?,', count($data) - 1) . "?)
		";
        $params = array_merge(array($query), array_values($data));
        $statement = $this->executeStatement($params);
        if ($this->success) {
            return $statement->rowCount();
        }
        else {
            return 0;
        }
    }

    /**
     * Umožňuje snadnou modifikaci záznamu v databázi pomocí asociativního pole
     * @param string $table Název tabulky
     * @param array $data Asociativní pole, kde jsou klíče sloupce a hodnoty hodnoty
     * @param string $condition Řetězec s SQL podmínkou (WHERE)
     * @return mixed
     */
    public function update($table, $data, $condition) {
        $keys = array_keys($data);
        $this->checkIdentifiers(array($table) + $keys);
        $query = "
			UPDATE "." `$table` SET `".
            implode('` = ?, `', array_keys($data)) . "` = ?
			$condition
		";
        $params = array_merge(array($query), array_values($data), array_slice(func_get_args(), 3));
        $statement = $this->executeStatement($params);
        if ($this->success) {
            return $statement->rowCount();
        }
        else {
            return 0;
        }
    }

    /**
     * Vrátí poslední ID posledního záznamu vloženého pomocí INSERT
     * @return mixed Id posledního záznamu
     */
    public function getLastId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Ošetří string proti SQL injekci
     * @param string $string Řetězec
     * @return mixed Ošetřený řetězec
     */
    public function quote($string)
    {
        return $this->pdo->quote($string);
    }

    /**
     * Zkontroluje, zda identifikátory odpovídají formátu identifikátorů
     * @param array $identifiers Pole identifikátorů
     * @throws \Exception
     */
    private function checkIdentifiers($identifiers)
    {
        foreach ($identifiers as $identifier)
        {
            if (!preg_match('/^[a-zA-Z0-9\_\-]+$/u', $identifier))
                throw new Exception('Dangerous identifier in SQL query');
        }
    }

//    /**
//     * Vrací informace o chybě
//     * @return array
//     */
//    public function getPDOErrorInfo() {
//        return $this->pdo->errorInfo();
//    }
//
//    /**
//     * Vrací kod chyby
//     * @return mixed
//     */
//    public function getPDOErrorCode() {
//        return $this->pdo->errorCode();
//    }

    /**
     * Vrací false v případě, že nastala chyba
     * @return bool
     */
    public function everythinkIsOk() {
        return $this->success;
    }

    /**
     * Vrací kód zachycené chyby
     * @return int|mixed
     */
    public function getExceptionCode() {
        return $this->error_code;
    }

    /**
     * Vrací text zachycené chyby
     * @return string
     */
    public function getExceptionMessage() {
        return $this->error_msg;
    }

}