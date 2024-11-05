<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myIBAN.
 */

namespace Gimi\myFormsTools\PckmyFields;

 


use Gimi\myFormsTools\PckmyJQuery\myJQuery;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMask;




/**
 * @see http://en.wikipedia.org/wiki/International_Bank_Account_Number#Validating_the_IBAN
 * @see http://www.swift.com/dsp/resources/documents/IBAN_Registry.pdf
 *
 * Supporta i 63 Stati indicati su wikipedia http://en.wikipedia.org/wiki/International_Bank_Account_Number#Validating_the_IBAN
 */
	
class myIBAN extends myText{
/** @ignore */
  protected $restrizioni_check=true, $stato_def=array(),$stato_forzato='';
/** @ignore */  
  protected static $internationals=array (
  'AL' =>
  array (
    0 => 'Albania',
    1 => '28',
    2 => '8n, 16c',
    3 => 'ALkk bbbs sssx cccc cccc cccc cccc',
    4 => 'b = National bank code  s = Branch code  x = National check digit  c = Account number',
  ),
  'AD' =>
  array (
    0 => 'Andorra',
    1 => '24',
    2 => '8n,12c',
    3 => 'ADkk bbbb ssss cccc cccc cccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),-
  'AT' =>
  array (
    0 => 'Austria',
    1 => '20',
    2 => '16n',
    3 => 'ATkk bbbb bccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'AZ' =>
  array (
    0 => 'Azerbaijan',
    1 => '28',
    2 => '4c,20n',
    3 => 'AZkk bbbb cccc cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'BE' =>
  array (
    0 => 'Belgium',
    1 => '16',
    2 => '12n',
    3 => 'BEkk bbbc cccc ccxx',
    4 => 'b = National bank code  c = Account number  x = National check digits',
  ),
  'BH' =>
  array (
    0 => 'Bahrain',
    1 => '22',
    2 => '4a,14c',
    3 => 'BHkk bbbb cccc cccc cccc cc',
    4 => 'b = National bank code  c = Account number',
  ),
  'BA' =>
  array (
    0 => 'Bosnia and Herzegovina',
    1 => '20',
    2 => '16n',
    3 => 'BAkk bbbs sscc cccc ccxx',
    4 => 'k = IBAN check digits (always 39)  b = National bank code  s = Branch code  c = Account number  x = National check digits',
  ),
  'BG' =>
  array (
    0 => 'Bulgaria',
    1 => '22',
    2 => '4a,6n,8c',
    3 => 'BGkk bbbb ssss ddcc cccc cc',
    4 => 'b = BIC bank code  s = Branch (BAE) number  d = Account type  c = Account number',
  ),
  'CR' =>
  array (
    0 => 'Costa Rica',
    1 => '21',
    2 => '17n',
    3 => 'CRkk bbbc cccc cccc cccc c',
    4 => 'b = bank code  c = Account number',
  ),
  'HR' =>
  array (
    0 => 'Croatia',
    1 => '21',
    2 => '17n',
    3 => 'HRkk bbbb bbbc cccc cccc c',
    4 => 'b = Bank code  c = Account number',
  ),
  'CY' =>
  array (
    0 => 'Cyprus',
    1 => '28',
    2 => '8n,16c',
    3 => 'CYkk bbbs ssss cccc cccc cccc cccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),
  'CZ' =>
  array (
    0 => 'Czech Republic',
    1 => '24',
    2 => '20n',
    3 => 'CZkk bbbb ssss sscc cccc cccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),
  'DK' =>
  array (
    0 => 'Denmark',
    1 => '18',
    2 => '14n',
    3 => 'DKkk bbbb cccc cccc cc',
    4 => 'b = National bank code  c = Account number',
  ),
  'DO' =>
  array (
    0 => 'Dominican Republic',
    1 => '28',
    2 => '4a,20n',
    3 => 'DOkk bbbb cccc cccc cccc cccc cccc',
    4 => 'b = Bank identifier  c = Account number',
  ),
  'EE' =>
  array (
    0 => 'Estonia',
    1 => '20',
    2 => '16n',
    3 => 'EEkk bbss cccc cccc cccx',
    4 => 'b = National bank code  s = Branch code  c = Account number  x = National check digit',
  ),
  'FO' =>
  array (
    0 => 'Faroe Islands[Note 4]',
    1 => '18',
    2 => '14n',
    3 => 'FOkk bbbb cccc cccc cx',
    4 => 'b = National bank code  c = Account number  x = National check digit',
  ),
  'FI' =>
  array (
    0 => 'Finland',
    1 => '18',
    2 => '14n',
    3 => 'FIkk bbbb bbcc cccc cx',
    4 => 'b = Bank and branch code  c = Account number  x = National check digit',
  ),
  'FR' =>
  array (
    0 => 'France[Note 5]',
    1 => '27',
    2 => '10n,11c,2n',
    3 => 'FRkk bbbb bggg ggcc cccc cccc cxx',
    4 => 'b = National bank code  g = Branch code (fr:code guichet)  c = Account number  x = National check digits',
  ),
  'GE' =>
  array (
    0 => 'Georgia',
    1 => '22',
    2 => '2c,16n',
    3 => 'GEkk bbcc cccc cccc cccc cc',
    4 => 'b = National bank code  c = Account number',
  ),
  'DE' =>
  array (
    0 => 'Germany',
    1 => '22',
    2 => '18n',
    3 => 'DEkk bbbb bbbb cccc cccc cc',
    4 => 'b = Bank and branch identifier (de:Bankleitzahl or BLZ)  c = Account number',
  ),
  'GI' =>
  array (
    0 => 'Gibraltar',
    1 => '23',
    2 => '4a,15c',
    3 => 'GIkk bbbb cccc cccc cccc ccc',
    4 => 'b = BIC bank code  c = Account number',
  ),
  'GR' =>
  array (
    0 => 'Greece',
    1 => '27',
    2 => '7n,16c',
    3 => 'GRkk bbbs sssc cccc cccc cccc ccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),
  'GL' =>
  array (
    0 => 'Greenland[Note 4]',
    1 => '18',
    2 => '14n',
    3 => 'GLkk bbbb cccc cccc cc',
    4 => 'b = National bank code  c = Account number',
  ),
  'GT' =>
  array (
    0 => 'Guatemala',
    1 => '28',
    2 => '4c,20c',
    3 => 'GTkk bbbb cccc cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number  Effective 1 July 2014',
  ),
  'HU' =>
  array (
    0 => 'Hungary',
    1 => '28',
    2 => '24n',
    3 => 'HUkk bbbs sssk cccc cccc cccc cccx',
    4 => 'b = National bank code  s = Branch code  c = Account number  x = National check digit',
  ),
  'IS' =>
  array (
    0 => 'Iceland',
    1 => '26',
    2 => '22n',
    3 => 'ISkk bbbb sscc cccc iiii iiii ii',
    4 => 'b = National bank code  s = Branch code  c = Account number  i = holder\'s kennitala (national identification number).',
  ),
  'IE' =>
  array (
    0 => 'Ireland',
    1 => '22',
    2 => '4c,14n',
    3 => 'IEkk aaaa bbbb bbcc cccc cc',
    4 => 'a = BIC bank code  b = Bank/branch code (sort code)  c = Account number',
  ),
  'IL' =>
  array (
    0 => 'Israel',
    1 => '23',
    2 => '19n',
    3 => 'ILkk bbbn nncc cccc cccc ccc',
    4 => 'b = National bank code  n = Branch number  c = Account number 13 digits (padded with zeros).',
  ),
  'IT' =>
  array (
    0 => 'Italy',
    1 => '27',
    2 => '1a,10n,12c',
    3 => 'ITkkx aaaaa bbbbb ccccc ccccccc',
    4 => 'x = Check char (CIN)  a = National bank code (it:Associazione bancaria italiana or Codice ABI )  b = Branch code (it:Coordinate bancarie or CAB - Codice d\'Avviamento Bancario)  c = Account number',
  ),
  'KZ' =>
  array (
    0 => 'Kazakhstan',
    1 => '20',
    2 => '3n,13c',
    3 => 'KZkk bbbc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'KW' =>
  array (
    0 => 'Kuwait',
    1 => '30',
    2 => '4a, 22c',
    3 => 'KWkk bbbb aaaa aaaa aaaa aaaa aaaa aa',
    4 => 'b = National bank code  a = Account number.',
  ),
  'LV' =>
  array (
    0 => 'Latvia',
    1 => '21',
    2 => '4a,13c',
    3 => 'LVkk bbbb cccc cccc cccc c',
    4 => 'b = BIC Bank code  c = Account number',
  ),
  'LB' =>
  array (
    0 => 'Lebanon',
    1 => '28',
    2 => '4n,20c',
    3 => 'LBkk bbbb aaaa aaaa aaaa aaaa aaaa',
    4 => 'b = National bank code  a = Account number',
  ),
  'LI' =>
  array (
    0 => 'Liechtenstein',
    1 => '21',
    2 => '5n,12c',
    3 => 'LIkk bbbb bccc cccc cccc c',
    4 => 'b = National bank code  c = Account number',
  ),
  'LT' =>
  array (
    0 => 'Lithuania',
    1 => '20',
    2 => '16n',
    3 => 'LTkk bbbb bccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'LU' =>
  array (
    0 => 'Luxembourg',
    1 => '20',
    2 => '3n,13c',
    3 => 'LUkk bbbc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'MK' =>
  array (
    0 => 'Macedonia',
    1 => '19',
    2 => '3n,10c,2n',
    3 => 'MKkk bbbc cccc cccc cxx',
    4 => 'k = IBAN check digits (always = "07")  b = National bank code  c = Account number  x = National check digits',
  ),
  'MT' =>
  array (
    0 => 'Malta',
    1 => '31',
    2 => '4a,5n,18c',
    3 => 'MTkk bbbb ssss sccc cccc cccc cccc ccc',
    4 => 'b = BIC bank code  s = Branch code  c = Account number',
  ),
  'MR' =>
  array (
    0 => 'Mauritania',
    1 => '27',
    2 => '23n',
    3 => 'MRkk bbbb bsss sscc cccc cccc cxx',
    4 => 'b = National bank code  s = Branch code (fr:code guichet)  c = Account number  x = National check digits Planned effective date 1 January 2012.',
  ),
  'MU' =>
  array (
    0 => 'Mauritius',
    1 => '30',
    2 => '4a,19n,3a',
    3 => 'MUkk bbbb bbss cccc cccc cccc cccc cc',
    4 => 'b = National bank code  s = Branch identifier  c = Account number',
  ),
  'MC' =>
  array (
    0 => 'Monaco',
    1 => '27',
    2 => '10n,11c,2n',
    3 => 'MCkk bbbb bsss sscc cccc cccc cxx',
    4 => 'b = National bank code  s = Branch code (fr:code guichet)  c = Account number  x = National check digits',
  ),
  'MD' =>
  array (
    0 => 'Moldova',
    1 => '24',
    2 => '2c,18n',
    3 => 'MDkk bbcc cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'ME' =>
  array (
    0 => 'Montenegro',
    1 => '22',
    2 => '18n',
    3 => 'MEkk bbbc cccc cccc cccc xx',
    4 => 'k = IBAN check digits (always = "25")  b = Bank code  c = Account number  x = National check digits',
  ),
  'NL' =>
  array (
    0 => 'Netherlands[Note 6]',
    1 => '18',
    2 => '4a,10n',
    3 => 'NLkk bbbb cccc cccc cc',
    4 => 'b = BIC Bank code  c = Account number',
  ),
  'NO' =>
  array (
    0 => 'Norway',
    1 => '15',
    2 => '11n',
    3 => 'NOkk bbbb cccc ccx',
    4 => 'b = National bank code  c = Account number  x = Modulo-11 national check digit',
  ),
  'PK' =>
  array (
    0 => 'Pakistan',
    1 => '24',
    2 => '4c,16n',
    3 => 'PKkk bbbb cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number  Effective 31 December 2012',
  ),
  'PS' =>
  array (
    0 => 'Palestinian Territory, Occupied',
    1 => '29',
    2 => '4c,21n',
    3 => 'PSkk bbbb xxxx xxxx xccc cccc cccc c',
    4 => 'b = National bank code  c = Account number  x = Not specified',
  ),
  'PL' =>
  array (
    0 => 'Poland',
    1 => '28',
    2 => '24n',
    3 => 'PLkk bbbs sssx cccc cccc cccc cccc',
    4 => 'b = National bank code  s = Branch code  x = National check digit  c = Account number,',
  ),
  'PT' =>
  array (
    0 => 'Portugal',
    1 => '25',
    2 => '21n',
    3 => 'PTkk bbbb ssss cccc cccc cccx x',
    4 => 'k = IBAN check digits (always = "50")  b = National bank code  s = Branch code  C = Account number  x = National check digit',
  ),
  'RO' =>
  array (
    0 => 'Romania',
    1 => '24',
    2 => '4a,16c',
    3 => 'ROkk bbbb cccc cccc cccc cccc',
    4 => 'b = BIC Bank code  c = Branch code and account number (bank-specific format)',
  ),
  'SM' =>
  array (
    0 => 'San Marino',
    1 => '27',
    2 => '1a,10n,12c',
    3 => 'SMkk xaaa aabb bbbc cccc cccc ccc',
    4 => 'x = Check char (it:CIN)  a = National bank code (it:Associazione bancaria italiana or Codice ABI)  b = Branch code (it:Coordinate bancarie or CAB - Codice d\'Avviamento Bancario)  c = Account number',
  ),
  'SA' =>
  array (
    0 => 'Saudi Arabia',
    1 => '24',
    2 => '2n,18c',
    3 => 'SAkk bbcc cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number preceded by zeros, if required',
  ),
  'RS' =>
  array (
    0 => 'Serbia',
    1 => '22',
    2 => '18n',
    3 => 'RSkk bbbc cccc cccc cccc xx',
    4 => 'b = National bank code  c = Account number  x = Account check digits',
  ),
  'SK' =>
  array (
    0 => 'Slovakia',
    1 => '24',
    2 => '20n',
    3 => 'SKkk bbbb ssss sscc cccc cccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),
  'SI' =>
  array (
    0 => 'Slovenia',
    1 => '19',
    2 => '15n',
    3 => 'SIkk bbss sccc cccc cxx',
    4 => 'k = IBAN check digits (always = "56")  b = National bank code  s = Branch code  c = Account number  x = National check digits',
  ),
  'ES' =>
  array (
    0 => 'Spain',
    1 => '24',
    2 => '20n',
    3 => 'ESkk bbbb gggg xxcc cccc cccc',
    4 => 'b = National bank code  g = Branch code  x = Check digits  c = Account number',
  ),
  'SE' =>
  array (
    0 => 'Sweden',
    1 => '24',
    2 => '20n',
    3 => 'SEkk bbbc cccc cccc cccc cccx',
    4 => 'b = National bank code  c = Account number  x = Check digit',
  ),
  'CH' =>
  array (
    0 => 'Switzerland',
    1 => '21',
    2 => '5n,12c',
    3 => 'CHkk bbbb bccc cccc cccc c',
    4 => 'b = National bank code  c = Account number',
  ),
  'TN' =>
  array (
    0 => 'Tunisia',
    1 => '24',
    2 => '20n',
    3 => 'TNkk bbss sccc cccc cccc cccc',
    4 => 'b = National bank code  s = Branch code  c = Account number',
  ),
  'TR' =>
  array (
    0 => 'Turkey',
    1 => '26',
    2 => '5n,17c',
    3 => 'TRkk bbbb bxcc cccc cccc cccc cc',
    4 => 'b = National bank code  x = Reserved for future use (currently "0")  c = Account number',
  ),
  'AE' =>
  array (
    0 => 'United Arab Emirates',
    1 => '23',
    2 => '3n,16n',
    3 => 'AEkk bbbc cccc cccc cccc ccc',
    4 => 'b = National bank code  c = Account number',
  ),
  'GB' =>
  array (
    0 => 'United Kingdom[Note 7]',
    1 => '22',
    2 => '4a,14n',
    3 => 'GBkk bbbb ssss sscc cccc cc',
    4 => 'b = BIC bank code  s = Bank and branch code (sort code)  c = Account number',
  ),
  'VG' =>
  array (
    0 => 'Virgin Islands, British',
    1 => '24',
    2 => '4c,16n',
    3 => 'VGkk bbbb cccc cccc cccc cccc',
    4 => 'b = National bank code  c = Account number',
  )
);

   public function __construct($nome='',$attributi='',$contenuto='') {
      myText::__construct($nome,$attributi,$contenuto);
      $this->set_maimin('Maiuscolo')->set_minlength(16)->set_maxlength(31,32);
  }

   public function set_value($valore) {
       parent::set_value(str_replace(' ','',$valore));
  }
	
     public function get_html(){
        self::rebuild_internationals();
        $masks=array();
		if($this->stato_def)
		                    {$stato=$this->stato_def[5][0];
		                     $specialMasks=array(0=>'',1=>'');
		                     foreach ($this->stato_def[5] as $i=>$k) 
		                                              { $specialMasks[0][]=$k[0].strtolower($k[0]);
		                                                $specialMasks[1][]=$k[1].strtolower($k[1]);
		                                                $masks["'$k'"]=preg_replace('/^aa/S','^$',$this->stato_def[7][$i]);
		                                              }
		                     $specials=array('^'=>implode('',array_unique($specialMasks[0])),
		                                     '$'=>implode('',array_unique($specialMasks[1])));
		                     }
		               else {$stato='IT';
		                     foreach (self::$internationals as $k=>$v) $masks["'$k'"]=$v[7];
		                     $specials=array();
		                    }
		if($this->get_value()) $stato=$this->get_nazione();
		               
		$this->add_myJQuery(new myJQInputMask("#{$this->get_id()}"))->set_mask($masks["'$stato'"],'_',$specials);
		
		
		$jq=new myJQuery("#{$this->get_id()}");
		
		$jq->add_code("if ((typeof myIBAN_FIELDS)+''=='undefined') 
									{
				                     myIBAN_FIELDS={};
				                     myIBAN_MASKS=".myJQuery::encode_array($masks,'{}').";
									}
				       myIBAN_FIELDS['{$this->get_id()}']='{$stato}'");
		
		$jq->add_code("{$jq->JQid()}.keyup(function(event)
													{var stato=this.value.substr(0,2).toUpperCase();
													 if(myIBAN_MASKS[stato]!==undefined &&
													 	myIBAN_FIELDS['{$this->get_id()}']!=stato
														){myIBAN_FIELDS['{$this->get_id()}']=stato;
													      var mask=myIBAN_MASKS[stato];
													      var value=this.value.toUpperCase().replace(/[ _]+/g,'');
													      this.value='';
													      this.size=mask.length+2;
													      this.maxlength=mask.length;
													      {$jq->JQid()}.unmask();
													      {$jq->JQid()}.mask(mask);
													      for (var j=0;j<value.length;j++)
													      		   {var press = jQuery.Event('keypress');
													      			press.which = value.charCodeAt(j);
																	{$jq->JQid()}.trigger(press);
													      	  		}

														}
													}
										   )");
		return parent::get_html().$jq;
	}

	/**
	 * Restituisce l'ABI contenuto nel IBAN  (ha senso solo per iBAN italiano)
	 * @return string
	 */
	 public function get_ABI(){
		return substr($this->get_value(),5,5);
	}

	/**
	 * Restituisce il CAB contenuto nel IBAN (ha senso solo per iBAN italiano)
	 * @return string
	 */
	  public function get_CAB(){
		return substr($this->get_value(),10,5);
	}


	/**
	 * Restituisce il CIN contenuto nel IBAN
	 * @return string
	 */
	 public function get_CIN(){
		return substr($this->get_value(),4,1);
	}

	/**
	 * Restituisce il CC contenuto nel IBAN
	 * @return string
	 */
	 public function get_CC(){
		return substr($this->get_value(),15,12);
	}


	/**
	 * Restituisce sigla Nazione contenuto nel IBAN
	 * @return string
	 */
	 public function get_nazione(){
		return substr($this->get_value(),0,2);
	}


    /**
	* Permette di rendere il check_errore piu' o meno restrittivo
	*
	* @param boolean $verifica   //verifica calcolo cin
    */
   public function set_restrizioni_check($verifica=true){
   	 $this->restrizioni_check=$verifica;
   	 return $this;
   }


  protected function get_errore_diviso_singolo() {
    $errore=parent::get_errore_diviso_singolo();
   	if ($errore) return $errore;
   	$valore=strtoupper($this->get_value());
   	if ($valore && $this->restrizioni_check && !$this->check_validita()) return "formalmente errato";
   }


   /**
    * Imposta lo stato di default per l'Iban
    * se ammissibile resituisce true altrimenti false
    * @param string $stato codice di due caratteri (può anche essere una sequenza divisa da virgola)
    * @param boolean $forza lo stato non puo' essere cambiato dall'utente (forzato a false se $stato è una sequenza divisa da virgola)
    * @return boolean
    */
   public function set_stato($stato,$forza=false){
  	/* 'IT' => array (
                    0 => 'Italy',
                    1 => '27',
                    2 => '1a,10n,12c',
                    3 => 'ITkk xaaa aabb bbbc cccc cccc ccc',
                    4 => 'x = Check char (CIN)  a = National bank code (it:Associazione bancaria italiana or Codice ABI )  b = Branch code (it:Coordinate bancarie or CAB - Codice d\'Avviamento Bancario)  c = Account number',
                  	5 => 'IT',
                  	6 => regExp
                  	7 => JQmask
                  )
  */
  	self::rebuild_internationals();
  	$stato=explode(',',strtoupper($stato));
  	if(!self::$internationals[$stato[0]]) return false;
  	$this->stato_def=array();
    foreach ($stato as $codice) 
            if(isset(self::$internationals[$codice]))
                     for($i=0;$i<=7;$i++) 
                            $this->stato_def[$i][]=self::$internationals[$codice][$i];
  	if($forza && count($stato)==1) 
  	            {$this->stato_forzato=$stato[0];    
  				 $this->stato_def[6][0]=preg_replace('/^\[A-Z\]\{2\}/',$stato[0],$this->stato_def[6][0]);
  				 $this->stato_def[7][0]=preg_replace('/^aa/',$stato[0],$this->stato_def[7][0]);
  				}
   	$this->set_minlength(min($this->stato_def[1]))
          		 ->set_maxlength(max($this->stato_def[1]),max($this->stato_def[1])+1)
          		 ->set_regexp('^('.implode('|',$this->stato_def[6]).')$');
  	return $this;
  }

  
  
  
  /**
   * @ignore
   */
  private static function rebuild_internationals() {
  	/* 'IT' =>	 array (
              	 		0 => 'Italy',
              	 		1 => '27',
              	 		2 => '1a,10n,12c',
              	 		3 => 'ITkk xaaa aabb bbbc cccc cccc ccc',
              	 		4 => 'x = Check char (CIN)  a = National bank code (it:Associazione bancaria italiana or Codice ABI )  b = Branch code (it:Coordinate bancarie or CAB - Codice d\'Avviamento Bancario)  c = Account number',
              	 )
  	*/
  if(self::$internationals['IT'][5]=='IT') return;
  foreach (self::$internationals as $stato=>&$componente) {
  	$parti=explode(',',trim((string) $componente[2]));
  	$preg="[A-Z]{2}[0-9]{2}";
  	$mask="aa99";
  	foreach ($parti as $parte)
			  	{$lung_parte=(int) $parte;
			  	 switch ($parte[strlen($parte)-1]) {
			  		case 'a':
			  			$preg.="[A-Za-z]{{$lung_parte}}";
			  			$mask.=str_repeat('a', $lung_parte);
			  			break;
			  		case 'c':
			  			$preg.="[0-9A-Za-z]{{$lung_parte}}";
			  			$mask.=str_repeat('*', $lung_parte);
			  			break;
			  		case 'n':
			  			$preg.="[0-9]{{$lung_parte}}";
			  			$mask.=str_repeat('9', $lung_parte);
			  			break;
			  		}
			  	}
	 $componente[5]=$stato;
	 $componente[6]=$preg;
	 $j=0;
	 for ($i=0;$i<strlen($componente[3]);$i++)
	     if($componente[3][$i]==' ') $componente[7].=' ';
	 				              	   else $componente[7].=$mask[$j++];

  	}
  }

  /** 
   * @ignore
    */
  private function check_validita()
	{
		//if(!$this->stato_def) $this->set_stato('IT');
	    self::rebuild_internationals();
		if(!isset(self::$internationals[$this->get_nazione()])) return false;
	//	echo $this->get_CIN(),' !=  ',self::contrcin($this->get_ABI().$this->get_CAB().$this->get_cc());
		if ($this->get_nazione()=='IT' && $this->get_CIN() !=  self::contrcin($this->get_ABI().$this->get_CAB().$this->get_cc()))  return false;

		$cc1 = substr($this->get_value(),4).$this->get_nazione()."00";
		$iban2="";
		$tabella63="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		for($iban1=0;$iban1<strlen($cc1);$iban1++) $iban2.=strpos($tabella63,$cc1[$iban1]);

		$div1="";
		for ($ciclo1 = 0; $ciclo1 < strlen($iban2); $ciclo1++)
			{
			$div1.=$iban2[$ciclo1];
			if ($div1<97) continue;
						  else   $div1=$div1%97;
			}

		$iban2=98 - $div1;
		$iban2="0". $iban2;
		$iban2=$iban2[strlen($iban2)-2].$iban2[strlen($iban2)-1];
	//	echo $iban2,' !=  ',substr($this->get_value(),2,2);

		if ($iban2 != substr($this->get_value(),2,2)) return false;
		return true;
	}

		/**
		 * @ignore
		 */
	private static function contrcin($cc)
		{
		$aa="A0B1C2D3E4F5G6H7I8J9K#L#M#N#O#P#Q#R#S#T#U#V#W#X#Y#Z#-#.# #";
		$bb="B1A0K#P#L#C2Q#D3R#E4V#O#S#F5T#G6U#H7M#I8N#J9W#Z#Y#X# #-#.#";
		$dd=0;
		for($ii=1;$ii<22;$ii+=2)$dd=$dd+floor(strpos($aa,$cc[$ii])/2);
		for($ii=0;$ii<22;$ii+=2)$dd=$dd+floor(strpos($bb,$cc[$ii])/2);
		$dd=$dd-(floor($dd/26)*26);
		return $aa[$dd*2];
		}

}