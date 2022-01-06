<?php

/**
 * 
 * Description: It process licence plates using a filter by pmr or sector and It return a file with differences.
 * 
 * Author: Daniel Olivet Jiménez @daniolivet
 * 
 * Usage:
 *      Execute this script in the same directory where are this csv files.
 * 
 *      - SECTOR => php -f script_plates.php sector=<number_sector>fileCSV=<name_file1> fileCSV2=<name_file2>
 *      - PMR => php -f script_plates.php fileCSV=<name_file1> fileCSV2=<name_file2> pmr=1
 * 
 */

/* Constans */
define('NAME_FILE', 'new_plates.csv');

class MatriculasCSV {

    protected $pmr = false;
    protected $sector = 0;
    protected $csv;
    protected $csv2;
    protected $plates = [];
    protected $smassa_plates = [];
    protected $plates_diff = [];
    
    public function __construct( Array $args = [] ){
        array_shift($args);
        $cnf = [];

        $cnf = $this->getCnf($args, $cnf);

        if( !isset($cnf['fileCSV']) || !isset($cnf['fileCSV2']) ){
            die("The parametre fileCSV or fileCSV2 don't exists in execution of script.");
        }

        if( isset($cnf['pmr']) ) {
            $this->pmr = true;
        }

        if(isset($cnf['sector'])) {
            $this->sector = $cnf['sector'];
        }

        $this->csv = file($cnf['fileCSV']);
        $this->csv2 = file($cnf['fileCSV2']);
    }

    /**
     * @return void
     */
    public function getPlates() {
        if(!$this->getDiff()) {
            die("Don't exists differences" . PHP_EOL);
        }

        if( $this->createCSV($this->sector, $this->plates_diff) ) {
            echo "File created succesfully" . PHP_EOL;
        } else {
          echo "The file couldn't be created." . PHP_EOL;
        }
    }

    /**
     * @param $sector
     * @param $plates
     * @return bool
     */
    protected function createCSV($sector, $plates) {

        // if file don't exists create it.
        if( !file_exists(NAME_FILE) ) {
            $newCSV = fopen(NAME_FILE, 'w');
        }

        // if file exists delete it and create a new one.
        if( unlink(NAME_FILE) ) {
            $newCSV = fopen(NAME_FILE, 'w');
        }

        fputs($newCSV, "Identificador, Descripción". PHP_EOL);
        foreach($plates as $plate) {
            if($this->pmr) {
                fputs($newCSV, $plate.',Matricula PMR '.$plate . PHP_EOL);
            }else {
                fputs($newCSV, $plate.',Residente autorizado sector '.$sector.' vehiculo '.$plate . PHP_EOL);
            }
        }
        fclose($newCSV);

        return (isset($newCSV)) ? true : false;
    }

    /**
     * @param array $args
     * @param array $cnf
     * @return array|void
     */
    private function getCnf(Array $args, Array $cnf)
    {
        foreach ($args as $param) {

            list($key, $value) = explode('=', $param);

            if (empty($value)) {
                die($key . ' is empty.');
            }

            $cnf[$key] = $value;
        }
        return $cnf;
    }

    /**
     * @return bool|void
     */
    private function getDiff() {

        if($this->pmr) {
            while (array_shift($this->csv) && !empty($this->csv)) {
                $plate = preg_replace('/[^A-Za-z0-9]/', '', $this->csv[0]);
                if(!empty($plate)) {
                    array_push($this->plates, $plate);
                }
            }
        }else {
            while (array_shift($this->csv) && !empty($this->csv)) {

                list($sector, $description) = explode(',', $this->csv[0]);
                $plate = preg_replace('/[^A-Za-z0-9]/', '', $description);

                if ($sector == $this->sector) {
                    array_push($this->plates, $plate);
                }
            }
        }

        if (empty($this->plates)) {
            die("There aren't license plates associated with that sector." . PHP_EOL);
        }

        while (array_shift($this->csv2) && !empty($this->csv2)) {
            list($plates_smassa, $description) = explode(',', $this->csv2[0]);
            array_push($this->smassa_plates, $plates_smassa);
        }

        if (!empty($this->plates) && !empty($this->smassa_plates)) {
            $this->plates_diff = array_diff($this->plates, $this->smassa_plates);
        }

        return (isset($this->plates_diff)) ? true : false;
    }

}

$plates = new MatriculasCSV($argv);
$plates->getPlates();