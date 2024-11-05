<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myRadio.
 */

namespace Gimi\myFormsTools\PckmyFields;




class myRadio extends myMultiCheck{
/** @ignore */
protected $campi, $attributi_opzioni=array(),$restrizioni_check=true;


	 /**
  	  * 
	  * @param	 string $nome E' il nome del campo
	  * @param	 int/string $valore da assegnare come default
	  * @param	 array $opzioni E' un array associativo con le opzioni Titolo=>valore
	  * @param	 string $classe e' la classe css da utilizzare
	  */
	  public function __construct($nome,$valore='',$opzioni=array(),$classe=''){
				$this->set_MyType('MyRadio');
				myField::__construct($nome,$valore);
				if ($classe) $this->set_attributo('class',$classe);
				if (is_array($nome) && $nome['opzioni']) $opzioni=$nome['opzioni'];
				if (count($opzioni)>0) $this->set_Opzioni($opzioni);
				$this->set_MyType('MyRadio');
				$this->Richiede_tag_label=false;
			  }

	/**
			 * Permette di rendere il check_errore piu' o meno restrittivo
			 */
	   public function set_restrizioni_check($stato){
				$this->restrizioni_check=$stato;
				return $this;
			}



	 /**
	* Non attiva in questa classe*/
	 public function set_autotab () {return $this;}

	 public function set_name($nome, $cambia_id = true) {
	    myField::set_name($nome, $cambia_id );
		if ($this->campi) foreach (array_keys($this->campi) as $name) if ($this->campi[$name]->get_name()) $this->campi[$name]->set_name($nome);
		return $this;
		//echo "<pre>";	print_r($this->campi);
	 }


	  public function get_name() {
		return  myField::get_name();
	  }



    /**
	* Restituisce il titolo associato al valore
	  * @param	  string $valore se omesso si usa il valore predefinito del selettore
	  * @return	 string $titolo e' il titolo visualizzato
	  */
	   public function get_Titolo($valore='') {
					 $opzioni=array_flip($this->get_opzioni());
					 return $opzioni[($valore?$valore:$this->get_value())];
	  }



	   public function set_Opzioni($opzioni=array()) {
	  			$this->opzioni=$opzioni;
	  			$attr=array();
	  			if(is_array($this->opzioni)) foreach ($this->opzioni as &$w) $v=trim((string) $w);
				if (is_array($this->campi))
					 foreach ($this->campi as $i=>$v)  $attr[$i]=$v->get_attributi();
					 unset($this->myFields['campo__istanze'][$this->get_name()]);
					 $this->campi=array();
					 if (is_array($opzioni)) {
						  foreach ($opzioni as $label=>&$valore)
							 if ($label!=='')
							  {//unset($this->campo__istanze[$this->get_name()]);
							 //			echo $this->get_name()." $label=>$valore<br />";
                                $this->campi[$label]=new mySingleRadio($this->get_name(),$valore,!isset($this->attributi['class'])?'':$this->attributi['class']);
								if ($attr && isset($attr[$label]) && $attr[$label]) 
								                   {if(isset($attr[$label]['id']))    unset($attr[$label]['id']);
													if(isset($attr[$label]['value'])) unset($attr[$label]['value']);
												    $this->campi[$label]->set_attributo($attr[$label]);
													$this->campi[$label]->set_attributo($this->attributi_opzioni[$label]);
													}
							   if ($this->get_value()==$valore) $this->campi[$label]->set_checked();
							  }
			  }
			return $this;
		 }


		  /**
	* elimina dal/i valore/i impostati quelli non presenti nelle opzioni
			 * attenziene a non usarla prima di aver settato tutte le opzioni valide
			 * altrimenti si annulla il valore
			 */
		  public function clean_value() {
				$opzioni=@array_flip($this->opzioni);
				if (!$opzioni) $opzioni=array();
				$valore=$this->get_value();
				if (!isset($opzioni[$valore]))$this->set_value(null);
				return $this;
		  }



		   public function set_value($valore) {
		  	  myField::set_value($valore);
		 	  if ($this->campi)
					foreach (array_keys($this->campi) as $label)
							if (trim((string) $this->get_value())===trim((string) $valore)) $this->campi[$label]->set_checked();
													                 else $this->campi[$label]->set_unchecked();
		      return $this;
		  }


		   public function isMultiple(){
  				return false;
  			}





		  protected function get_errore_diviso_singolo() {
					$valore=$this->get_value();
					if ($this->notnull && strlen(trim((string) $valore))==0) return 'non può essere nullo';
					if($this->restrizioni_check && $this->get_value())
						{
						$opz=array_flip($this->get_opzioni());
						if(!isset($opz[$this->get_value()])) return 'non è accettabile';
						}
		  }


		  function &get_value() {
		  			 return myField::get_value();
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
	  * Imposta attributi delle singole opzioni es style, class
	  *
	  * @param array $opzioni Titoli (non i valori) delle opzioni da personalizzare
	  * @param string $attributo  nome dell'attributo da settare
	  * @param string $valore	  valore dell'attributo
	  */
	  public function set_attributo_opzioni($opzioni,$attributo,$valore) {
				foreach ($opzioni as $v)
										{
										if (!$this->attributi_opzioni[$v]) $this->attributi_opzioni[$v]=new myTag();
										$this->attributi_opzioni[$v]->set_attributo($attributo,$valore);
										if ($this->campi[$v]) $this->campi[$v]->set_attributo($attributo,$valore);
										}
				return $this;
	 }

	  public function get_js_chk($js = ''){
  		// if ($this->notnull) return "if(strlen(myGetValueCampo('{$this->get_id()}','radio',null))==0) return \"{$this->trasl('non può essere nullo')}\";";
  		  			 	//else
  		  			 	//il notnull andrebbe controllato su tutte le opzioni
  		  			 	return "return null;";
  		}


  		function &get_xml_value(){
  			return $this->get_value();
  		}

  		function &get_xml(){
  			return myField::get_xml();
		}
}