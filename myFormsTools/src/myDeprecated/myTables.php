<?


/**
*  @deprecated
*  @package myTables
**/
use Gimi\myFormsTools\PckmyTables\myTable;

Class myTableForm_MYSQL extends myTableForm_MYSQL {

/**
    * Costruttore di classe Ottimizzato per mysql
  * 
    * @param    ADOConnection $conn  E' connessione ADODB
    * @param    string $qry  E' la query da lanciare
    * @param    string $parsTable  Opzionale e' il nome della classe css da associare al tag TABLE
    * @param    string $parsTR  Opzionale e' il nome della classe css da associare al tag TR
    * @param    string $parsTD  Opzionale e' il nome della classe css da associare al tag TD
    * @param    string $parsTH Opzionale sono i parametri da associare al tag TH
    **/
	 public function __construct(&$conn,$qry,$parsTable='',$parsTR='',$parsTD='',$parsTH='') {
		parent::__construct($conn,$qry,$parsTable,$parsTR,$parsTD,$parsTH);
	}



}



/**
 * Estensione specifica di myJQMyTableFilters specifica per myTables
 * @package myJQuery
 * @deprecated
 */

class myJQMyTableFilters extends myJQTableFilters{
    /**
     * @ignore
     */
    protected $myTable;

    /**
     * @ignore
     */final function __construct() {
     parent::__construct('');
    }


    /**
     * @ignore
     */function application(myTable &$myTable) {
     $this->myTable=&$myTable;
    }

    
    /**
     * @ignore
     */function get_id(){
     return '#'.$this->myTable->get_id();
    }

}




/**
 * Estensione specifica di myJQMyTableStyleEffects specifica per myTables
 * @package myJQuery
 * @deprecated
 *
 */
class myJQMyTableStyleEffects extends myJQTableStyleEffects{
    /**
     * @ignore
     */
    protected $myTable;

    /**
     * @ignore
     */final function __construct() {
     parent::__construct('');
    }

    /**
     * @ignore
     */function application(myTable &$myTable) {
     $this->myTable=&$myTable;
    }

   
    /**
     * @ignore
     */function get_id(){
     return '#'.$this->myTable->get_id();
    }
}






?>