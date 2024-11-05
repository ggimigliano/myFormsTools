<?
/** @ignore */
use Gimi\myFormsTools\PckmyAjax\myRicercaAjax;
use Gimi\myFormsTools\PckmyFields\myField;
use Gimi\myFormsTools\PckmyFields\myUploadText;

if(!class_exists('googleMap',false)) include_once(dirname(__FILE__).'/../googleMap/googleMap.php');


/**
 * 
 * @author Gianluca
 * @deprecated
 */
class myGoogleAddress extends myRicercaAjax{
protected $parametri,$car=5;


   public function __construct($nome,$valore='',$classe='') {
    $this->myText=new myUploadText($nome,$valore);
	$this->set_MyType('myGoogleAddress');
	$this->id=$this->myText->get_id_istanza();
 }


   public function set_provincia($campo) {
  	$this->set_campo_input('provincia',$campo);
  	return $this;
  }

   public function set_comune($campo) {
  	$this->set_campo_input('comune',$campo);
  	return $this;
  }

   public function set_cap($cap) {
  	$this->cap=$cap;
  	return $this;
  }

   public function set_opzioni($caratteri){
  	$this->car=$caratteri;
  	return $this;
  }



  function &get_html() {
   $this->set_indicatore_attesa(false);
   if ($this->cap) $this->set_onselect_function("SelectedAdress_{$this->id}");
   parent::set_opzioni('/'.myField::get_MyFormsPath()."googleMap/ricerca_indirizzi.php",$this->car,$this->get_id(),array(' '));
   $out=parent::get_html();
   if ($this->cap) $out.="<script type=\"text/javascript\">
   			function SelectedAdress_{$this->id}(p1,p2) {
   			        try {  
   			     	v=p2.split('(');
					v=v[1].split(' ');
					capto=parseInt(''+v[0],10);
					capin=parseInt(0+document.getElementById('{$this->cap->get_id()}').value,10);
				 	if ( capin==0
					      ||
					     (capto-parseInt(capto/100,10)*100!=0 && capto>capin)  ||
					     (capto-parseInt(capto/100,10)*100!=0 && capin-parseInt(capin/100,10)*100==0) ||
					     (capto-parseInt(capto/100,10)*100!=capin-parseInt(capin/100,10)*100)  
					   )  	document.getElementById('{$this->cap->get_id()}').value=v[0];
					   } catch (Exception) {}       
				}
   		  </script>";
   return $out;
  }
  
}


?>