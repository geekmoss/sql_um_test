<?php

/**
 * Třída pro nahrávání souborů libovolného typu
 *
 * @author J. Janeček
 */
class Upload {

    private $fileHandle;
    private $multiple;
    private $count = 1;

    private $numFileHanhle = -1;

    /**
     * @param string $inputName Název formulářového vstupu
     * @param bool   $isMultiple Zda-li se může nahrávat více jak jeden soubor
     */
    public function __construct($inputName, $isMultiple = false) {
        $this->fileHandle = (object)$_FILES[$inputName];
        $this->multiple = $isMultiple;
        if ($isMultiple) {
            $this->count = sizeof($_FILES[$inputName]['name']);
        }
    }

    /**
     * Vrací počet nahraných souborů
     *
     * @return int
     */
    public function getCountFiles() {
        return $this->count;
    }

    /**
     * Multiupload: Načte další soubor; Vrací false pokud již nejsou další soubory
     *
     * @return bool
     */
    public function nextFile() {
        $this->numFileHanhle++;
        if ($this->numFileHanhle+1 > $this->count) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Metoda pro zjištění zda-li nahraný soubor je v pořádku.
     *
     * @return bool
     */
    public function isOk() {
        if ($this->multiple) {
            if ($this->fileHandle->error[$this->numFileHanhle] == UPLOAD_ERR_OK AND filesize($this->fileHandle->tmp_name[$this->numFileHanhle]) == $this->fileHandle->size[$this->numFileHanhle]) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            if ($this->fileHandle->error == UPLOAD_ERR_OK AND filesize($this->fileHandle->tmp_name) == $this->fileHandle->size) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    /**
     * Vrací koncovku nahraného souboru (bez tečky).
     *
     * @return mixed
     */
    public function getExtension() {
        if ($this->multiple) {
            return pathinfo($this->fileHandle->name[$this->numFileHanhle], PATHINFO_EXTENSION);
        }
        else {
            return pathinfo($this->fileHandle->name, PATHINFO_EXTENSION);
        }
    }

    /**
     * Vrací MIME typ souboru.
     *
     * @return mixed
     */
    public function getType() {
        if ($this->multiple) {
            return $this->fileHandle->type[$this->numFileHanhle];
        }
        else {
            return $this->fileHandle->type;
        }
    }

    /**
     * Vrací název souboru (s koncovkou).
     *
     * @return mixed
     */
    public function getName() {
        if ($this->multiple) {
            return $this->fileHandle->name[$this->numFileHanhle];
        }
        else {
            return $this->fileHandle->name;
        }
    }

    /**
     * Vrací velikost souboru v bytech
     *
     * @return mixed
     */
    public function getSize() {
        if ($this->multiple) {
            return $this->fileHandle->size[$this->numFileHanhle];
        }
        else {
            return $this->fileHandle->size;
        }
    }

    /**
     * Metoda uloží nahraný soubor do vybraného adresáře pod zadaným názvem, vrací true při úspěchu.
     *
     *
     * @param string $where Cesta kam se soubor uloží s novým názvem
     * @return bool
     */
    public function saveUploadedFile($where) {
        if ($this->multiple) {
            return move_uploaded_file($this->fileHandle->tmp_name[$this->numFileHanhle], $where);
        }
        else {
            return move_uploaded_file($this->fileHandle->tmp_name, $where);
        }
    }
}