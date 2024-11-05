<?php
/**
 * Contains Gimi\myFormsTools\PckmyFields\myDate.
 */

namespace Gimi\myFormsTools\PckmyFields;


use Gimi\myFormsTools\PckmyArrayObject\myArrayObject;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQDatepicker;
use Gimi\myFormsTools\PckmyJQuery\PckmyFields\myJQInputMaskExt;
use Gimi\myFormsTools\PckmyJQuery\myJQuery;

 
class myDate extends myInt  {
/** 
 * @ignore 
 */
protected $calendar, $minmaxcal,$xmlFormat=array('amg','-');

  /**
  *
  * 
  * @param	 string $nome E' il nome del campo
  * @param	 string $valore Valore da assegnare come default ACCETTATE SIA IN FORMATO GG/MM/AAAA CHE AAAA/MM/GG
  * @param	 string $classe e' la classe css da utilizzare
  */
   public function __construct($nome,$valore='',$classe='') {
  	myText::__construct($nome,$valore,$classe);
  	$this->is_numeric(false);
	$this->set_maxlength(10,10);
	//$this->set_style('width','6.5em');
	$this->set_MyType('MyDate');
  }




  /**
	* Restituisce il numero di giorni di un certo mese
	* @param	 int $month
	 * @param	 int $year
	 * @return	int
  */
   public static function giorni_mese($month, $year)
	 {$daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	  if ($month < 1 || $month > 12) return 0;
	  $d = $daysInMonth[$month - 1];
	  if ($month == 2)
	  { if ($year%4 == 0)
			{ if ($year%100 == 0)
				 { if ($year%400 == 0) $d = 29;
				 }
				 else $d = 29;
			}
	  }
	  return $d;
	 }

 
 
  public static function giorni_anno($anno) {
		if (myDate::giorni_mese(2,$anno)==29) return 366;
										 else return 365;
 }


 
 /**
  * Restituisce una parte della data
  * Es. volendo solo mese ed anno si puo' scrivere
  * <code>
  *  echo myDate::estrai_parte('1/3/2015',2); //  3
  * </code>
  *  @param  string $data se esplicitato il calcolo si applica alla data indicata, altrimenti si prende in considerazione la data memorizzata nel campo
  * @param	 '0'|'1'|'2' $quale Se 0 restituisce il giorno se 1 restituisce il mese, se 2 restituisce l'anno
  * @return	int
  */
  public static function estrai_parte($data,$quale) {
     $x=preg_split('/[^0-9]/',$data);
     return intval(preg_replace('/^0/', '', $x[$quale]));
 } 
 
 
 /**
  * Restituisce una parte della data in formato stringa con lo zero signigicativo
  * Es. volendo solo mese ed anno si puo' scrivere
  * <code>
  * echo myDate::estrai_parte_str('1/3/2015',2); //  03
  * </code>
  * @param     '0'|'1'|'2' $quale Se 0 restituisce il giorno se 1 restituisce il mese, se 2 restituisce l'anno
  *  @param    string $data se esplicitato il calcolo si applica alla data indicata, altrimenti si prende in considerazione la data memorizzata nel campo
  * @return     string
  */
  public static function estrai_parte_str($data,$quale) {
 	return sprintf('%02s',static::estrai_parte($quale,$data));
 }


  /**
	* Restituisce una parte della data
	 * Es. volendo solo mese ed anno si puo' scrivere
	 * <code>
	 *  $x=new myDate('data','1/02-2006');
	 *  echo $x->parte(1).'/'.$x->parte(2); //  1/2
	 * </code>
	  * @param	 '0'|'1'|'2' $quale Se 0 restituisce il giorno se 1 restituisce il mese, se 2 restituisce l'anno
	  * @return	int
	  */
  public function parte($quale,$data='') {
	return static::estrai_parte($data?$data:$this->get_value(), $quale);
 }


 /**
  * Restituisce una parte della data in formato stringa con lo zero significativo
  * Es. volendo solo mese ed anno si puo' scrivere
  * <code>
  *  $x=new myDate('data','1/02-2006');
  *  echo $x->parte_str(1).'/'.$x->parte_str(2); //  01/02
  * </code>
  * @param	 '0'|'1'|'2' $quale Se 0 restituisce il giorno se 1 restituisce il mese, se 2 restituisce l'anno
  * @return	int
  */
  public function parte_str($quale,$data='') {
     return static::estrai_parte_str($data?$data:$this->get_value(), $quale);
 }


  /**
	* Restituisce il numero di anni tra due date
	  * @param	 string $dal E' la data di inizio nel formato gg/mm/aaaa (se omesso inizia dal 1/1/1900)
	  * @param	 string $al E' la data di arrivo nel formato gg/mm/aaaa (se omesso usa il dato dell'oggetto mydate)
	  * @return	int
	  */
   public FUNCTION calcola_anni($dal,$al=''){
  	 if (!$al) $al=$this->get_value();
  	 $d1=new myDate('',$dal);
 	 $d2=new myDate('',$al);
 	 $segno=1;
 	 if($d1->get_formatted()>$d2->get_formatted())
 	 		{
 	 			$d1->set_value($al);
 	 			$d2->set_value($dal);
 	 			$segno=-1;
 	 		}

 	 $anni=$d2->get_parte(2)-$d1->get_parte(2);

 	 $mm1=$d1->get_parte(1);
 	 $mm2=$d2->get_parte(1);

 	 $gg1=$d1->get_parte(0);
 	 $gg2=$d2->get_parte(0);


 	 //inizio non inizia da 1 viene considerato parziale
 	 if($mm2<$mm1 ||  ($mm2==$mm1 && $gg2<$gg1) ) //stesso anno stesso mese
 	 			{//
 	 			  $anni--;
 	 			}


 	 return $anni*$segno;
 }
 
 /**
  * Restituisce il numero di giorni tra due date
  * @param	 string $dal E' la data di inizio nel formato gg/mm/aaaa (se omesso inizia dal 1/1/1900)
  * @param	 string $al E' la data di arrivo nel formato gg/mm/aaaa (se omesso usa il dato dell'oggetto mydate)
  * @return	int
  */
   function calcola_giorni($dal='1/1/1900',$al=''){
     if (!$al) $al=$this->get_value();
     return self::get_calcola_giorni($dal,$al);
 }

	/**
	 * 
	 * @param string $dal
	 * @param string $al
	 * @return NULL|int
	 */
    static function get_calcola_giorni($dal='1/1/1900',$al='1/1/1900'){
	 //echo "$dal $al<br />";
	 $d1=new Data('gma',$dal);$d2=new Data('gma',$al);
	 if ($d1->errore || $d2->errore ) return null;
	 if ($d1->get_value_formatted('amg')<$d2->get_value_formatted('amg'))
																	 {	$segno=1;
																		$dal=explode('/',$dal);
																		$al=explode('/',$al);
																	 }
																  else {$Dal=$dal; $segno=-1;
																		$dal=explode('/',$al);
																	    $al=explode('/',$Dal);
																	 }
		
		$inizio = new \DateTime(implode('-',$dal));
		$fine = new \DateTime(implode('-',$al));
		return $segno==1? $inizio->diff($fine)->days: -$inizio->diff($fine)->days;									 
		/*															 
		 if ($dal[2]==$al[2]) {//stesso anno
							 if($dal[1]==$al[1]) return ($al[0]-$dal[0])*$segno;
										  else {$tot=myDate::giorni_mese($dal[1],$dal[2])-$dal[0]; //numeri di giorni fino alla fine del mese di part
												$dal[1]++; //passo al mese success a quello di partenza
												 while ($dal[1]<$al[1])  {$tot+=myDate::giorni_mese($dal[1],$dal[2]);$dal[1]++;} //sommo i giorni fino a fine anno
												}
						}
					else {//anni diversi
						$tot+=myDate::giorni_mese($dal[1],$dal[2])-$dal[0]; //numeri di giorni fino alla fine del mese di part
						$dal[1]++; //passo al mese success a quello di partenza
						while ($dal[1]<=12)  {$tot+=myDate::giorni_mese($dal[1],$dal[2]);$dal[1]++;} //sommo i giorni fino a fine anno
						$dal[2]++;
						while ($dal[2]<$al[2]) {$tot+=myDate::giorni_anno($dal[2]);$dal[2]++;} //sommo i giorni fino all'anno corrente escl
						$i=1;
						while ($i<$al[1]) {$tot+=myDate::giorni_mese($i,$al[2]);$i++;		  } //sommo i giorni dei mesi dell'anno corrente
						  }
	  $tot+=$al[0];
	 return $tot*$segno; */
 }

	 /**
	* Restituisce il numero di  mesi tra due date
	  * @param	 string $dal E' la data di inizio nel formato gg/mm/aaaa (se omesso inizia dal 1/1/1900)
	  * @param	 string $al E' la data di arrivo nel formato gg/mm/aaaa (se omesso usa il dato dell'oggetto mydate)
	  * @param	 '0'|'1'|'2' $considera_giorni se:
	  * 						0 considera la differenza di mesi a prescindere dai giorni es.  1 == calcola_mesi('31/01/2012,'1/2/2012')
	  * 						1 considera il mese di arrivo solo se il giorno e superiore al giorno di partenza es.  0 == calcola_mesi('31/01/2012','1/2/2012',1) e 0==calcola_mesi(31/01/2012','29/2/2012',1)
	  * 						2 considera il mese di arrivo se il giorno e superiore al giorno di partenza o il mese di arrivo ha un numero di giorni del giorno di partenza ed il giorno di arrivo e' l'ultimo di quel mese es. 1==calcola_mesi(30/01/2012','29/2/2012',2) e 0==calcola_mesi(30/01/2012','28/2/2012',2)
	  * @return	int $classe e' la classe css da utilizzare
	  */
   public function calcola_mesi($dal='1/1/1900',$al='',$considera_giorni=0){
	 if (!$al) $al=$this->get_value();
	 $d1=new Data('gma',$dal);
	 $d2=new Data('gma',$al);
	 if ($d1->errore || $d2->errore ) return null;
	 if ($d1->get_value_formatted('amg')<$d2->get_value_formatted('amg'))
																	 {  $segno=1;
																		$dal=explode('/',$d1->get_value_formatted('gma'));
																		$al=explode('/',$d2->get_value_formatted('gma'));
																	 }
																  else {
																        $segno=-1;
																		$dal=explode('/',$d2->get_value_formatted('gma'));
																		$al=explode('/',$d1->get_value_formatted('gma'));
																	 }
																	 
																	 
	 if($considera_giorni==2 && myDate::giorni_mese($al[1],$al[2])==$al[0]) $al[0]=31;//se 2 e l'arrivo coincide con l'ultimo giorno del mese lo estendo a 31
	 if ($dal[2]==$al[2]) {//stesso anno
							 $tot=$al[1]-$dal[1]; //sommo i giorni fino a fine anno
						  }
					else {//anni diversi
							$dal[1]++; //passo al mese success a quello di partenza per non contarlo
						    while ($dal[1]<=12)  {$tot++;$dal[1]++;} //incremento fine anno
						    $dal[2]++;
							while ($dal[2]<$al[2]) {$tot+=12;$dal[2]++;} //sommo 12 mesi fino all'anno corrente escl
							$tot+=$al[1]; //sommo i giorni dei mesi dell'anno corrente
					  }
	 $tot=(int) $tot;
	 return ($considera_giorni>0 && $al[0]<$dal[0] ? --$tot : $tot)*$segno;
  }

   public function __call($m, $v){
      switch (strtolower($m)) {
          case 'sposta_data': { while(count($v)<3) $v[]=0;
                                return static::sposta_data_di($this->get_value(),$v[0],$v[1],$v[2]);
                              }
          case 'get_parte':return call_user_func_array(array($this,'parte'), $v);
          case 'get_numero_giorno_settimana':return static::num_giorno_settimana($v[0]?$v[0]:$this->get_value());
          case 'get_nome_giorno_settimana':return static::nom_giorno_settimana($v[0]?$v[0]:$this->get_value());
          case 'get_parte_str':return call_user_func_array(array($this,'parte_str'), $v);
          
      }
      parent::__call($m, $v);
  }
  
   public static function __callStatic($m, $v){
      switch (strtolower($m)) {
          case 'sposta_data':  return static::sposta_data_di($v[3],$v[0],$v[1],$v[2]);
          case 'get_parte':    return call_user_func_array('static::estrai_parte', $v);
          case 'get_parte_str':return call_user_func_array('static::estrai_parte_str', $v);
          case 'get_numero_giorno_settimana':return static::num_giorno_settimana($v[0]);
          case 'get_nome_giorno_settimana':return static::nom_giorno_settimana($v[0]);
      }
  }
  
  /**
  * Sposta il valore interno di un myDate di giorni mesi ed anni
  * <code>
  * $data=new myDate('','1/1/2000');
  * echo $data->sposta_di(1,1,0)->sposta_di(0,0,-1)->get_value(); //restituisce 02/02/1999
  * echo $data->get_value(); //restituisce 02/02/1999
  * </code>
  * IMPORTANTE: QUESTO METODO HA EFFETTO SOLO SE TUTTI I VALORI USATI HANNO LO STESSO SEGNO
  * @param	 int $ngiorni Positivo o negativo indica il numro di giorni da sommare o sottrarre.
  * @param	 int $nmesi Analogo ai giorni
  * @param	 int $nanni Analogo ai mesi
  * @param   string $formato di defaule 'gma'
  * @param   string $divisore di default '/'
  * @return	myDate
  */
   public function sposta_di($ngiorni=0,$nmesi=0,$nanni=0,$formato='gma',$divisore='/'){
      $this->set_value(static::sposta_data_di($this->get_value(),$ngiorni,$nmesi,$nanni,$formato,$divisore));   
    return $this;
  }  

  /**
	* Sposta una data in forma di stringa di giorni mesi ed anni
  *  Es.
  * <code>
  * echo myDate::sposta_data_di('1/1/2015',1,1,0) 
  * </code>
  * IMPORTANTE: QUESTO METODO HA EFFETTO SOLO SE TUTTI I VALORI USATI HANNO LO STESSO SEGNO
  * @param	 string $data  a cui applicare lo spostamento
  * @param	 int $ngiorni Positivo o negativo indica il numro di giorni da sommare o sottrarre.
  * @param	 int $nmesi Analogo ai giorni
  * @param	 int $nanni Analogo ai mesi
  * @param   string $formato di defaule 'gma'
  * @param   string $divisore di default '/'
  * @return	string  data spostata nel formata gg/mm/aaaa (di default)
  */
  public static function sposta_data_di($data,$ngiorni=0,$nmesi=0,$nanni=0,$formato='gma',$divisore='/'){
	# IF ($ngiorni*$nmesi<0 || $ngiorni*$nanni<0 || $nanni*$nmesi<0 ) return $data;
	 $d=new myDate('',$data);
	 $d->set_notnull();
	 if($d->Errore()) return $data;
	 
	 $data=explode('/',$d->get_value());
	 
	 $data_modificata = new \DateTime(implode('-',$data));
	 
	 // Aggiungi giorni, mesi e anni alla data
	 if($ngiorni!=0) $data_modificata->modify("$ngiorni days");
	 if($nmesi!=0)   $data_modificata->modify("$nmesi months");
	 if($nanni!=0)   $data_modificata->modify("$nanni years");
	 
	 
	 return $d->set_value($data_modificata->format('d/m/Y'))->get_formatted($formato,$divisore);
	 
	 /*
	 if ($ngiorni>0 && $ngiorni+$data[0]>myDate::giorni_mese($data[1],$data[2]))
									{$scalare=0;
										do{
										  $ngiorni-=$scalare;
										  // se mese corrente è genn o feb e anno corrente è di 366 scalo 366
										  // se mese corrente è succ a feb ma anno succ è bisestile scalo 366
										if (($data[1]<=2 && myDate::giorni_anno($data[2])==366) ||
											 ($data[1]>2 && myDate::giorni_anno($data[2]+1)==366)) $scalare=366;
																																														  else $scalare=365;
										if ($ngiorni-$scalare>=0) $data[2]++;
											}
										while ($ngiorni-$scalare>=0);
									 $scalare=0;
									 while ($ngiorni+$data[0]>myDate::giorni_mese($data[1],$data[2]))
																{
																  $ngiorni-=myDate::giorni_mese($data[1],$data[2]);
																  $data[1]++;
																  if ($data[1]==13) {$data[1]=1; $data[2]++;}
																}
									}
	 if ($ngiorni<0 && $data[0]+$ngiorni<=0)
	                                   {$scalare=0;
	 								      do{
											 $ngiorni+=$scalare;
											if (($data[1]<=2 && myDate::giorni_anno($data[2]-1)==366) ||
												 ($data[1]>2 && myDate::giorni_anno($data[2])==366)) $scalare=366;
																																																				 else $scalare=365;
											if ($ngiorni+$scalare<=0) $data[2]--;
											}
										while ($ngiorni+$scalare<=0);
										while ($ngiorni+$data[0]<=0)
											{
											$data[1]--;
											if ($data[1]==0) {$data[1]=12; $data[2]--;}
											 $ngiorni+=myDate::giorni_mese($data[1],$data[2]);
											}
									}
	   if (abs($nmesi)>12) {
						  $s=$nmesi/abs($nmesi);
						  $nanni+=$s* (int) (abs($nmesi) /12);
						  $nmesi=$s* (abs($nmesi) %12);
						 }
		  //		  echo "Diventa $ngiorni,$nmesi,$nanni<br />";

	  If ($ngiorni+$nmesi+$nanni>0) {
						$data[0]+=$ngiorni;
						if ($data[0]>myDate::giorni_mese($data[1],$data[2]))
									{$data[0]-=myDate::giorni_mese($data[1],$data[2]);
									 $nmesi++;
									}
						$data[1]+=$nmesi;
						if ($data[1]>12)
									{$data[0]-=myDate::giorni_mese($data[1],$data[2]);
									}
						 $data[2]+=$nanni;
			 			}
			 		else {
						$data[2]+=$nanni;
						$data[1]+=$nmesi;
						if ($data[1]<=0)
										{ $data[2]--;
										  $data[1]+=12;
										 }
						 $data[0]+=$ngiorni;
						if ($data[0]<=0) {$data[1]--;
										  if ($data[1]<=0) {
														  $data[2]--;
														  $data[1]+=12;
														  }
										  $data[0]+=myDate::giorni_mese($data[1],$data[2]);
										 }
			 }

   $data[2]=sprintf('%04s',$data[2]+(int) ($data[1]/13));
   $data[1]=sprintf('%02s',($data[1]%12==0?12:$data[1]%12));
   $giornimese=myDate::giorni_mese($data[1],$data[2]);
   if ($data[0]>$giornimese) $data[0]=$giornimese;
   $data[0]=sprintf('%02s',$data[0]);
   return (new myDate('',implode('/',$data)))->get_formatted($formato,$divisore);
  */  
 }





/**
	* Restituisce la data odierna (del server web)
 *  @return string
 */
 public static  function get_oggi() {return date('d/m/Y');  }



/**
	* Restituisce numero corrispondente al giorno della settimana
  *  @param  string $data se esplicitato il calcolo si applica alla data indicata, altrimenti si prende in considerazione la data odierna
  *  @return int	0=>Domenica	.. 6 =>Sabato
  */
static function &num_giorno_settimana($data='') {
    if (!$data) $data=static::get_oggi();
    return (new \DateTime((new myDate('',$data))->get_formatted()))->format('w');
}

 /**
  * Restituisce numero corrispondente al giorno della settimana del valore del campo
  *  @return int	0=>Domenica	.. 6 =>Sabato
  */
  function &get_num_giorno_settimana() {
      return static::num_giorno_settimana($this->get_value());
 }

 
  public function get_giorni_settimana(){
     $giorni=array();
     foreach (array('domenica','lunedì','martedì','mercoledì','giovedì','venerdì','sabato') as $giorno) $giorni[]=$this->trasl($giorno);
     return $giorni;
 }
 

 /**
  * Restituituisce il nome del giorno della settimana in italiano
  * @param string $data  se esplicitato il calcolo si applica alla data indicata, altrimenti si prende in considerazione la data odierna
  * @return string
  */
 static function &nom_giorno_settimana($data='') {
	if (!$data)   $data=static::get_oggi();
	$d=new myDate('',$data);
	$data=$d->get_value();
	$giorno=myDate::get_numero_giorno_settimana($data);
	if ($giorno===null) return null;
	$d=new myDate('');
	$giorni=$d->get_giorni_settimana();
	return $giorni[$giorno];
 }
 
 
 /**
  * Restituituisce il nome del giorno della settimana in italiano del valore del campo
  * @return string
  */
  function &get_nom_giorno_settimana() {
      if($this->get_value()===null) return null;
      $giorni=$this->get_giorni_settimana();
      return $giorni[$this->get_value()];
 }
 


 /**
	* Restituisce un array con le parti della data desiderate espresse in lettere
 * Es.
 * <code>
 *  $x=new myDate('data');
 *  $x->set_value($x->get_oggi());
 *  $parti=$x->inlettere(array('s','g','m','a'));
 *  echo $parti['s'].', '.$parti->g.' '.$parti['m'].' '.$parti->a;
 * </code>
 *
 * oppure
 *
 * <code>
 *  $parti=myDate::inlettere(array('s','g','m','a'),myDate::get_oggi());
 *  echo $parti['s'].', '.$parti->g.' '.$parti['m'].' '.$parti->a;
 * </code>
 *
 *
 *
 *  @param  array  $parti e' un array che indica le parti in lettere che si desiderano es. ('s','m') indica che si vuole il giorno della settimana e l'anno in lettere.
 * I possibili valori sono:
 * <pre>
 *  g=>numero del giorno espresso in lettere
 *  s=>Nome giorno settimana
 *  m=>Nome del mese
 *  mm=>numero del mese espresso in lettere
 *  a=>anno espresso in lettere
 * </pre>
 *  @param  string $data se esplicitato il calcolo si applica alla data indicata, altrimenti si prende in considerazione la data memorizzata nel campo
 *  @return myArrayObject
 */
 function &inlettere($parti=array('g','s','m','mm','a'),$data='') {
	if (!$data) $data=$this->get_value();
	$mesi=array();
	foreach (array('gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre') as $mese)
					if (is_object($this)) $mesi[]=$this->trasl($mese);
									else  $mesi[]=$mese;

	$v=new myArrayObject();
	foreach ($parti as $p) {
							if ($p=='m') {
										$mese=myDate::get_parte(1,$data);
										$v['m']=$mesi[$mese-1];
										}
							if ($p=='mm') {
										$mese=myDate::get_parte(1,$data);
										$v['mm']=myInt::inlettere($mese);
										}
							if ($p=='g') {
										 $giorno=myDate::get_parte(0,$data);;
										$v['g']=myInt::inlettere($giorno);
										}
							if ($p=='a') {
										$anno=myDate::get_parte(2,$data);
										$v['a']=myInt::inlettere($anno);
										}
							if ($p=='s') {
										$v['s']=myDate::get_nome_giorno_settimana($data);
										}
							}
	return $v;
	}



   public function set_value($valore='') {
	 if (!$valore || preg_replace('#\/|\.|\-|0#','',$valore)=='') $valore='';
	 $valore=substr($valore,0,10);

	 $data=new Data("gma",$valore);
	

	 if ($data->errore && $valore) {$dataAmg=new Data("amg",$valore);
	 								 if (!$dataAmg->errore) {$data->errore=null;
														  $data->value=$dataAmg->get_value_formatted();
														  }
								  }
     if (!$data->errore)   $this->attributi['value']=$data->get_value_formatted();
					 else $this->attributi['value']=$valore;

	return $this;
  }

  /**
   *
   * Restituisce il valore del minimo impostato
   * @param bool $del_calendario se true restituisce il valore relativo al calendario
   * @return int
   */
   public function get_min($del_calendario=false){
      if($del_calendario && isset($this->minmaxcal['min'])) return $this->minmaxcal['min'];
  	return $this->min;
  }

  /**
   *
   * Restituisce il valore del massimo impostato
   * @param bool $del_calendario se true restituisce il valore relativo al calendario
   * @return int
   */
   public function get_max($del_calendario=false){
    if($del_calendario && isset($this->minmaxcal['max'])) return $this->minmaxcal['max'];
  	return $this->max;
  }



  /**
   * @see myInt::set_min()
   * @param string $min data minima consentita
   * @param bool  se true il minimo impostato viene esteso anche all'eventuale calendario
   */
   public function set_min($min,$anche_calendario=true) {
	 if (preg_replace('#\/|\.|\-#','',$min)=='') $min='';
	 if ($min=='0') $min='';
 	 $data=new Data("gma",$min);
 	 
	 if ($data->errore && $min)
							  {$dataAmg=new Data("amg",$min);
							 if (!$dataAmg->errore) {$data->errore=null;
													  $data->value=$dataAmg->get_value_formatted();
													}
							}
	 if (!$data->errore) {$this->min=$data->get_value_formatted('gma','/');
	 					 if($anche_calendario) $this->minmaxcal['min']=$this->min;
	 					 }
 				    else $data->errore=array("Data Minima non valida: %1%",$min);
	return $this;
  }

/**
 * @see myInt::set_max()
 * @param string $max data massima consentita
 * @param bool  se true il massimo impostato viene esteso anche all'eventuale calendario
 */
  public function set_max($max,$anche_calendario=true) {
	if (preg_replace('#\/|\.|\-#','',$max)=='') $max='';
	if ($max=='0') $max='';
    $data=new Data("gma",$max);
    if ($data->errore && $max) {$dataAmg=new Data("amg",$max);
    if (!$dataAmg->errore) {$data->errore=null;
						   $data->value=$dataAmg->get_value_formatted();
							}
	}
   if (!$data->errore) {$this->max=$data->get_value_formatted('gma','/');
   						if($anche_calendario) $this->minmaxcal['max']=$this->max;
   					   }
				 else $data->errore= array("Data Massima non valida: %1%",$max);
	return $this;
 }


/** @ignore*/
 function &get_value_DB() {
     return $this->get_formatted('amg','-');
 }



 /**
	* Restituisce la  data nel formato prescelto
 *  @param string $formato è il formato desiderato e puo' essere una tra 'gma','mga','amg','agm' , di default e' 'amg'
 *  @param string $divisore è la stringa che deve dividere i vari campi, di default e' '-'
 *  @param string $valore e' la stringa in formato mydate accettabile (es gg/mm-aaaa) da formattare, se omesso usa il valore interno all'oggetto
 */
  public function get_formatted($formato='amg',$divisore='-',$valore='') {
 	if (!$formato) $formato='amg';
	if (!$divisore) $divisore="-";
 	$formato=strtolower($formato);
 	if (strlen($valore)==0 && is_object($this)) $valore=$this->get_value();
	$data=new Data("gma",$valore);
	if ($data->errore && $valore) {$dataAmg=new Data("amg",$valore);
								  if (!$dataAmg->errore) {$data->errore=null;
														  $data->value=$dataAmg->get_value_formatted();
														  }
								 }
    if (!$data->errore) return $data->get_value_formatted($formato,$divisore);
	return false;
 }


/**
	* Imposta la visualizzazione del calendario
 *  @param 'JQ'|'popup' $tipo setta il tipo di visualizzazione,se null non si visualizza calendario (default e' JQuery)
 *  @param string $titolo titolo da far comparire nella finestra
 *  @param string $css css da usare (percorso assoluto)
  */
   public function set_calendar($tipo='JQ',$titolo='',$css='') {
  	  $this->calendar=array('tipo'=>$tipo,'titolo'=>$titolo,'css'=>$css);
      if ($tipo=='JQ') {$this->add_myJQuery(new myJQDatepicker());
     			 		$myJQInputMask=new myJQInputMaskExt("#{$this->get_id()}");
      					$myJQInputMask->add_code("{$myJQInputMask->JQid()}.css('border-right','-1px').width(myJQCalcWidth({$myJQInputMask->JQid()},'88/88/8888')+24);");
      					$myJQInputMask->set_mask("date",'__/__/____');
      					$this->add_myJQuery($myJQInputMask);
      					}

      return $this;
 }


  public function get_js_chk($js = ''){
  		$js="valore=format_data(document.getElementById('{$this->get_id()}').value);
  		if(valore==null) return null;
  		";
  		if ($this->notnull)
		    $js.="if(strlen(valore)==0) return \"{$this->trasl('non può essere nullo')}\";";

		If (strlen($this->max)>0)
		   	$js.="if (valore!='' && valore>'{$this->get_formatted('amg','-',$this->max)}') return \"{$this->trasl('non può essere successiva al %1%',array('%1%'=>$this->max))}\";";

		if (strlen($this->min)>0)
			$js.="if (valore!='' && valore<'{$this->get_formatted('amg','-',$this->min)}') return \"{$this->trasl('non può essere antecedente al %1%',array('%1%'=>$this->min))}\";";

		if (strlen($this->maxlength)>0)
		   	$js.="if (valore!='' && strlen(valore)>{$this->maxlength}) return \"{$this->trasl('non può contenere più di %1% caratteri',array('%1%'=>$this->maxlength))}\";";

		if (strlen($this->minlength)>0)
			$js.="if (valore!='' && strlen(valore)<{$this->minlength}) return \"{$this->trasl('deve contenere almeno %1% caratteri',array('%1%'=>$this->minlength))}\";";
		return $js;
  	}


 /**
	* Restituisce il campo in html pronto per la visualizzazione
 *  @return string
 */
  	
  	protected function html5Settings($attrs=array()){
  	    $max=$this->max;$min=$this->min;
  	    $this->max=$this->min=null;
  	    myField::html5Settings();
  	    $this->max=$max;   $this->min=$min;
  	    
  	}

     public function get_Html () {
     $this->set_style('width','6.5em');
     $this->html5Settings(); 
  	 $get_html=isset($this->Metodo_ridefinito['get_Html']['metodo'])?$this->Metodo_ridefinito['get_Html']['metodo']:null; if ($get_html!='') return $this->$get_html(isset($this->Metodo_ridefinito['get_Html']['parametri'])?$this->Metodo_ridefinito['get_Html']['parametri']:null);
  	 if($this->masked && !$this->calendar) $this->set_calendar('JQ');
  	 $out= '<input '.$this->stringa_attributi().'/>';
  	 /*if ($this->calendar['tipo']=='JQ') Sposta i campi quando non è in tabella
  	                     $out= '<div style="float:left;width:8em">'.$out.'</div><br style="clear:both" />';*/
	 if ($this->calendar['tipo']=='popup' && !$this->get_showonly() && !$this->attributi['readonly'])
	 					{
						$docroot=  self::get_MyFormsPath();
						$js=myPopUp::js_always_top();
		  			    if (!$this->myFields['static']['js_src'][get_class($this)])
						 			{
									  $js.='<script type="text/javascript" >
									       //<!--
											 function calendarioPopup(id_field,url) {
											 		if((id_field).disabled) return;
											 		MyPopUp=window.open(url+"&old_val="+(id_field).value+"&tt="+Math.random(),"cal","height=250,width=250,status=no,scrollbars=auto,resizable=yes,titlebar=no,location=no");
											 		setFocuses();
				 								    return false;
											 }
									      //-->
										  </script>';
									  $this->myFields['static']['js_src'][get_class($this)]=1;
									 }
						//$out.="$js<span class='none' style='white-space:nowrap'>&nbsp;&nbsp;<a title='".$this->trasl('Click qui per selezionare dal calendario')."' href='#' id=\"popup_calendar_{$this->get_id()}\"  onclick='return calendarioPopup(\"/$docroot"."calendar.php?id=".$this->attributi['id']."&amp;tipo=".$this->calendar['tipo']."&amp;titolo=".$this->calendar['titolo']."&amp;css=".$this->calendar['css']."&amp;value=".$this->get_value()."&amp;lang=".($this->dizionario?$this->dizionario[0]->get_al():'it')."&amp;max={$this->max}&amp;min={$this->min}\")' onkeypress='return calendarioPopup(this,\"/$docroot"."calendar.php?id=".$this->attributi['id']."&amp;tipo=".$this->calendar['tipo']."&amp;titolo=".$this->calendar['titolo']."&amp;css=".$this->calendar['css']."&amp;value=".$this->get_value()."&amp;max={$this->max}&amp;min={$this->min}\")'><img src='/{$docroot}icone/cal.gif' alt='{$this->trasl('Click qui per selezionare dal calendario')}' style='border:0px'/></a></span>";
						$out="$js<span class='none' style='margin-left:-20px;white-space:nowrap' id=\"calend_span_{$this->get_id()}\">$out&nbsp;&nbsp;<a title=\"{$this->trasl('Click qui per selezionare dal calendario')}\"  href='#'
										onclick='return calendarioPopup(\"{$this->get_id()}\",\"/{$docroot}calendar.php?id={$this->get_id()}&amp;tipo={$this->calendar['tipo']}&amp;titolo={$this->calendar['titolo']}&amp;css={$this->calendar['css']}&amp;value={$this->get_value()}&amp;lang=".($this->dizionario?$this->dizionario[0]->get_al():'it')."&amp;max={$this->max}&amp;min={$this->min}\")'
										onkeypress='return calendarioPopup(\"{$this->get_id()}\",\"/{$docroot}calendar.php?id={$this->get_id()}&amp;tipo={$this->calendar['tipo']}&amp;titolo={$this->calendar['titolo']}&amp;css={$this->calendar['css']}&amp;value={$this->get_value()}&amp;lang=".($this->dizionario?$this->dizionario[0]->get_al():'it')."&amp;max={$this->max}&amp;min={$this->min}\")'
										style='border:0px'/><img src='/{$docroot}icone/cal.gif' alt=\"{$this->trasl('Click qui per selezionare dal calendario')}\" style='border:0px' id=\"calend_icon_{$this->get_id()}\" /></a>
										</span>";
					  }

	$jsCommon=$this->get_js_common();//if(!$this->myFields['static']['common']) {$jsCommon=$this->get_js_common();$this->myFields['static']['common']=true;}
	$jq=new myJQuery("#{$this->get_id()}");
	return $jsCommon.$this->jsAutotab().$this->send_html($out).$jq;
 }



 protected function get_errore_diviso_singolo() {
  $valore=trim((string) $this->get_value());
  if (!$this->notnull && $valore==='') return '';
  if ($this->notnull && $valore==='') return 'non può essere nullo';
  if ($this->maxlength && strlen($valore)>$this->maxlength) return array('non può contenere più di %1% caratteri',$this->maxlength);
  if ($this->minlength && strlen($valore)<$this->maxlength) return array('deve contenere almeno %1% caratteri',$this->minlength);

  $d=new Data("gma",$valore);
  if ($d->errore) return $d->errore;

    $M=new myDate("",$this->min);

   if ($valore && $this->min) {
  	$mese=$M->inlettere(array('m'));
  	$min=intval($M->get_parte(0)).' '.$this->trasl($mese['m']).' '.intval($M->get_parte(2));
    if ($d->get_value_formatted('amg','-')<$M->get_formatted()) return array('non può essere antecedente al %1%',$min);
  }

  if ($valore && $this->max) {
  	$M->set_value($this->max);
  	$mese=$M->inlettere(array('m'));
  	$max=intval($M->get_parte(0)).' '.$this->trasl($mese['m']).' '.intval($M->get_parte(2));
    if ($d->get_value_formatted('amg','-')>$M->get_formatted()) return array( 'non può essere successiva al %1%',$max);
  	}
  }

  function &get_xml_value(){
  	  $v=$this->get_formatted($this->xmlFormat[0],$this->xmlFormat[1]);
  	  return $v;
  }

   public function set_xml_format($formato='amg',$divisore='-'){
  	  $this->xmlFormat=array($formato,$divisore);
  }
}