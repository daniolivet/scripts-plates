<?php 

/* Constans */
define('NAME_FILE', 'matriculas_nuevas.csv');

class MatriculasCSV {

    protected $sector;
    protected $csv;
    protected $plates = [];

    public function __construct($args = null){
        array_shift($args);
        $cnf = [];

        foreach($args as $param) {
            
            list($key,$value) = array_filter(explode('=', $param));
    
            if( empty($value) ) {
                die( $key.' esta vacio.');
            }

            $cnf[$key] = $value;
        }
    
        
        if( file_exists($cnf['fileCSV']) ){
            $this->csv = file($cnf['fileCSV']);
        }

        $this->sector = $cnf['sector'];

    }

    public function getPlatesBySector() {

        while( array_shift($this->csv) && !empty($this->csv) ) {

            list($sector, $description) = explode(',', $this->csv[0]);
            
            if( $sector == $this->sector ) {
                array_push($this->plates, $description);
            }
        }

        if( $this->createCSV($this->sector, $this->plates) ) {
            echo "Fichero creado con éxito";
        }

    }

    protected function createCSV($sector, $plates) {

        // if file don't exists create it.
        if( !file_exists(NAME_FILE) ) { 
            $newCSV = fopen(NAME_FILE, 'w');
        } 

        // if file exists delete it and create a new one.
        if( unlink(NAME_FILE) ) {
            $newCSV = fopen(NAME_FILE, 'w');
        }

        fputs($newCSV, "Sector, Matricula". PHP_EOL); 
        foreach($plates as $plate) {
            fputs($newCSV, $sector.','.$plate);
        }
        fclose($newCSV);


        return true;
    }

}

$matriculas = new MatriculasCSV($argv);
$matriculas->getPlatesBySector();

?>