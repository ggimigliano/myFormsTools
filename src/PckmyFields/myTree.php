<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myTree.
 */

namespace Gimi\myFormsTools\PckmyFields;






/**
 *
 * Questo oggetto permette di creare dei campi nascosti "ma visibili"
		* Si puo' usare in alternativa ad un myMulticheck quando le opzioni sono tante
		* e soprattutto hanno un'organizzazione gerarchica
	 	*
	 	*
	 	* <code>
		  * $albero=array('lazio'=>array('','Lazio'),
		  *											'rm'=>array('lazio','Roma'),
		  *											'fr'=>array('lazio','Frosinone'),
		  *											'ri'=>array('lazio','Rieti'),
		  *											'lt'=>array('lazio','Latina'),
		  *											'H501'=>array('rm','Roma',1),
		  *											'H244'=>array('rm','Mentana',1),
		  *											'H324'=>array('rm','Monterotondo',1),
		  *											'H421'=>array('fr','Arpino',1),
		  *											'H445'=>array('fr','Isola Liri',1),
		  *											'friuli'=>array('','Friuli Venezia Giulia'),
		  *											'ud'=>array('friuli','Udine'),
		  *											'pn'=>array('friuli','Pordenone'),
		  *											'C444'=>array('pn','Pordenone',1),
		  *											'CR34'=>array('pn','Altro comune in prov. Pordenone',1)
		  *											);
		  *
	 	* $miotree=new myTree('mio','H501',$albero); //istanzio l'oggetto inizializzando con il valore ricevuto in get
	 	* echo $miotree->get_html();
* </code>
*/ 
	
class myTree extends myMultiTree  {

		 public function __construct($nome,$valore='',$albero='',$classe=''){
			myMultiTree::__construct($nome,$valore,$albero,$classe);
		}

		  /**
		  * Imposta l'albero su cui costruire il tutto
		  * 
		  * <code>
		  *			 $albero=array('lazio'=>array('','Lazio'),
		  *										  'rm'=>array('lazio','Roma',0,'/icons/provincia.gif'),
		  *											'fr'=>array('lazio','Frosinone'),
		  *											'ri'=>array('lazio','Rieti'),
		  *											'lt'=>array('lazio','Latina'),
		  *											'H501'=>array('rm','Roma' ,1,'/icons/comune.gif'),
		  *											'H244'=>array('rm','Mentana',1),
		  *											'H324'=>array('rm','Monterotondo',1),
		  *											'H421'=>array('fr','Arpino',1),
		  *											'H445'=>array('fr','Isola Liri',1,'/icons/comune.gif'),
		  *											'friuli'=>array('','Friuli Venezia Giulia'),
		  *											'ud'=>array('friuli','Udine'),
		  *											'pn'=>array('friuli','Pordenone',0,'/icons/provincia.gif'),
		  *											'C444'=>array('pn','Pordenone',1,'/icons/comune.gif'),
		  *										    'CR34'=>array('pn','Altro comune in prov. Pordenone',1,'/icons/comune.gif')
		  *											);
		  * $miotree->set_opzioni($albero);
		  * echo $miotree->get_html();
		  *
		  * </code>
		  * Si tratta ovviamente di un array di array, ogni sottoarray rappresenta un nodo in cui: <br />
		  * 'lazio'=>array('','Lazio'),<br />
		  * In questo caso 'lazio' e' il valore associato al nodo in cui 'Lazio' sarà  la descrizione che comparirà.<br />
		  * Non essendo valorizzato il primo valore questo nodo sarà  una "radice" in particolare non selezionabile<br />
		  * 'rm'=>array('lazio','Roma',0,'/icons/provincia.gif'),<br />
		  * In quest'altro caso 'rm'  e' il valore associato al nodo in cui 'Roma' sarà  la descrizione che comparirà.<br />
		  * Inoltre la presenza dello 0 indica che non è selezionabile e che <b>Qualora fosse una foglia</b> l'icona da usare e' '/icons/provincia.gif' altrimenti non c'è icona<br />
		  * Questo sarà  un "figlio" del nodo 'Lazio' ma non sarà  ancora selezionabile<br />
		  * 'H324'=>array('rm','Monterotondo',1),<br />
		  * In quest'altro caso 'H324' e' il valore associato al nodo in cui 'Monterotondo' sarà  la descrizione che comparirà.<br />
		  * Questo sarà  un "figlio" del nodo 'Roma' ed e' selezionabile (terzo valore =1 o =true), questo fa si che si possa scegliere di rendere selezionabili sia le foglie che i rami che le radici <br />
		  *
	 	* @param	 array $albero
	 	*
	 	*/
		   public function set_Opzioni($albero = Array()) {
			    $this->opzioni=$this->set_Opzioni_tree($albero);
			    $attr=array();
    			if (is_array($this->campi)) foreach ($this->campi as $i=>$v)  $attr[$i]=$v->get_attributi();
    			unset($this->myFields['campo__istanze'][$this->get_name()]);
    			$this->campi=array();
    			if (is_array($this->opzioni)) 
    					  foreach ($this->opzioni as $label=>&$valore)
                						 if ($label)
                						  { $this->campi[$label]=new mySingleRadio($this->get_name(),$valore,!isset($this->attributi['class'])?'':$this->attributi['class']);
                							if ($attr && isset($attr[$label]) && $attr[$label]) 
                							                   {if(isset($attr[$label]['id']))    unset($attr[$label]['id']);
                												if(isset($attr[$label]['value'])) unset($attr[$label]['value']);
                											    $this->campi[$label]->set_attributo($attr[$label]);
                												$this->campi[$label]->set_attributo($this->attributi_opzioni[$label]);
                												}
                						   if ($this->get_value()==$valore) $this->campi[$label]->set_checked();
                						  }
    			return $this;
		  }


		   public function isMultiple(){
  				return false;
  			}



	/** @ignore*/
	 function &get_value_DB() {
			  return myField::get_value_DB();
	 }


	 function &get_value(){
	 	 return myField::get_value();
	 }
	  public function set_value($v){
	     return myField::set_value($v);
	 }

		/**
	* L'elenco si trasforma in un elenco di link
		  *
	 	* @param	 string $url indica l'url di destinazione alla quale verrà automativamente accodato il valore cliccato
	 	*
	 	**/
		   public function Set_Links($url,$add_random=false) {
		  	       $this->showlinks=array($url,$add_random);
				   return $this;
		  }

   		/**
	* elimina dal/i valore/i impostati quelli non presenti nelle opzioni
		   * attenzione a non usarla prima di aver settato tutte le opzioni valide
		   * altrimenti si annulla il valore
		   */
		  public function clean_value() {
		          $selezionabili=array();
                  $opzioni=(array)$this->get_opzioni();
                  foreach ($opzioni as $v=>&$foglia) {
                      $tupla=array_values($foglia); 
                      if($tupla[2]) $selezionabili[$v]=true;
                  }
                 if (!is_scalar($this->get_value()) || !$selezionabili[$this->get_value()]) $this->set_value(null);

        }


		  /**
	* setta in automatico il primo valore valido
			 */
		   public function autovalue() {
		  		myMultiTree::autovalue();
		  }

}