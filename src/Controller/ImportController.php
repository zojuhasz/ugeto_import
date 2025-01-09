<?php

/**
 * @file
 * Contains \Drupal\Import\Controller\ImportController.
 */
namespace Drupal\import\Controller;


const DATETIME_STORAGE_TIMEZONE = 'UTC';
const DATETIME_DATETIME_STORAGE_FORMAT = 'Y-m-d\\TH:i:s';

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use \Drupal\Core\Database\Database;
use \Drupal\Core\Datetime\DrupalDateTime;
//use Symfony\Component\HttpFoundation\Request;
//loadatok betöltése régi ugeto ből az új ugeto19 -be
class ImportController {
  
  
  public function content() {
  //nulláról feltöltés, sokáig fut!! 
  
  //n.a. rekord
  $na_nid=1;//n.a
  
  
  
  //előtörlés, csak ha kell, akkor viszont ki kell szedni a kommentezést
  //$types = array('lovak');$nids_query = db_select('node', 'n')->fields('n', array('nid'))->condition('n.type', $types, 'IN')->condition('n.nid', $na_nid, '!=')->range(0, 3000)->execute();$nids = $nids_query->fetchCol();entity_delete_multiple('node', $nids);
  
  \Drupal\Core\Database\Database::setActiveConnection('external');
  $db = \Drupal\Core\Database\Database::getConnection();
  
  //$query = \Drupal::entityQuery('lovak')->accessCheck(TRUE);
  $query = $db->select('lovak', 'lo');
  $query->leftJoin('lovak','ap','lo.apja = ap.neve && lo.apja_ev = ap.ev');
  $query->leftJoin('LO','nl','nl.SLONEV = lo.neve && nl.ISZULETESIEV = lo.ev');
  $query->fields('lo', ['no','jel_','jelx','neve','rekord','penz','ev','fedez', 'ellet','szine', 'neme', 'belyeg','utal', 'neve', 'orszag','tulajdonos','nevel', 'apja', 'apja_ev',
                 'apja_orszag','apja_2','apja_2_ev','anyja','anyja_ev','anyja_orszag','kimult','herel','jelek','iker','kiirt','elozo',
                 'jegyzet','imp_orsz','imp_kelt','exp_orsz','exp_kelt','men_ki','men_be','mum_ki','regi_rek','uj_rek','rekord_ok','uj_apa',
                 'uj_anya','fedezomen','aktiv','upgrd','egybevetes','lo_vagy_fed','author','authored_date','updater','updated_date','loazon','transzponder',
                 'ueln','megjegyzes','szures']);
  $query->addField('ap', 'no', 'ap_no');
  $query->addField('ap', 'neve', 'ap_neve');
  $query->addField('ap', 'ev', 'ap_ev');
  $query->addField('ap', 'orszag', 'ap_orszag');
  $query->addField('nl', 'ILOID', 'nlkft_iloid');
  $query->condition('lo.lo_vagy_fed', 'LO', '=');
  ////$query->condition('lo.no', 22129, '=');
  ////$query->condition('lo.ev', 2018, '>=');$query->condition('lo.ev', 2020, '<=');
  //$query->condition('lo.ev', 2020, '>='); //ez volt utoljára futtatva
  //$query->condition('lo.ev', 2024, '<'); //ez volt utoljára futtatva
  $query->condition('lo.neve', 'Manina H', '=');// Ez csak kamubol van, hogy ne csináljon semmit, ha véletlenül elindul. Mivel már van OH, nem fogja felvinni újra
  $query->orderBy('lo.ev', 'ASC');
  $results = $query->execute();
  
  Database::setActiveConnection();  
  $s=0;
  $ss=0;
  
  $dupilo="";
  while ($content = $results->fetchAssoc()) {
    $s++;
    // egyesével baírni a lovakat a dr8 -ba
    //print_r($content);
    
    //megnézni, hogy nincs-e már felvive ez a ló{
    $ker_ev=$content['ev'];
    $ker_orszag=$content['orszag'];
    $ker_neve=$content['neve'];
    $ker_apja=$content['apja'];
    $ker_anyja=$content['anyja'];
    if($ker_anyja==''){ // ha nincs anyja a regi db -ben, akkor az új db -ben nem hasonlitunk anyát, mert ott n.a. van, és nem üres
      $query = \Drupal::entityQuery('node')->accessCheck(TRUE)
      ->condition('type', 'lovak')
      ->condition('field_ev', $ker_ev)
      ->condition('field_neve', $ker_neve)
      //->condition('field_anyanev', $ker_anyja)
      ->condition('field_apanev', $ker_apja);
    }else{
      $query = \Drupal::entityQuery('node')->accessCheck(TRUE)
      ->condition('type', 'lovak')
      ->condition('field_ev', $ker_ev)
      ->condition('field_neve', $ker_neve)
      ->condition('field_anyanev', $ker_anyja)
      ->condition('field_apanev', $ker_apja);
    }        
    
     //$results = $query->execute();
     $dupi_num_rows = $query->count()->execute();
    //megnézni, hogy nincs-e már felvive ez a ló}
    
    if(!$dupi_num_rows){ //ha nincs még belőle, akkor, de csak akkor mehet a felvitel
      $ss++;
      $no=$content['no'];$neve=$content['neve'];$ev=$content['ev'];$orszag=$content['orszag'];
      $apja=$content['apja'];$apja_ev=$content['apja_ev']; $apja_orszag=$content['apja_orszag'];
      $anyja=$content['anyja'];$anyja_ev=$content['anyja_ev']; $anyja_orszag=$content['anyja_orszag'];
      $title=$neve; $fedez=$content['fedez']; $penz=$content['penz']; $rekord=$content['rekord'];
      $szine=$content['szine']; $lo_vagy_fed=$content['lo_vagy_fed'];$fedezomen=$content['fedezomen'];$imp_orsz=$content['imp_orsz'];$exp_orsz=$content['exp_orsz'];
      $neme=$content['neme'];
      $jel_=$content['jel_'];$jelx=$content['jelx']; $ellet=$content['ellet'];$belyeg=$content['belyeg'];
      $utal=$content['utal']; $nevel=$content['nevel'];$tulajdonos=$content['tulajdonos'];
      $apja_2=$content['apja_2']; $apja_2_ev=$content['apja_2_ev'];$kimult=$content['kimult'];
      $herel=$content['herel']; $jelek=$content['jelek'];$iker=$content['iker'];
      $kiirt=$content['kiirt']; $elozo=$content['elozo'];$jegyzet=$content['jegyzet'];
      $imp_kelt=$content['imp_kelt']; $exp_kelt=$content['exp_kelt'];$men_ki=$content['men_ki'];
      $mum_ki=$content['mum_ki']; $regi_rek=$content['regi_rek'];$uj_rek=$content['uj_rek'];
      $rekord_ok=$content['rekord_ok']; $uj_apa=$content['uj_apa'];$uj_anya=$content['uj_anya'];
      $aktiv=$content['aktiv']; $upgrd=$content['upgrd'];$egybevetes=$content['egybevetes'];
      $author=$content['author']; $authored_date=$content['authored_date'];$updater=$content['updater'];
      $updated_date=$content['updated_date']; $loazon=$content['loazon'];$transzponder=$content['transzponder'];
      $ueln=$content['ueln']; $megjegyzes=$content['megjegyzes'];$szures=$content['szures'];
      $nlkft_iloid=$content['nlkft_iloid'];
      if(empty(trim($szine))){$szine='?';}
      if(empty(trim($neme))){$neme='?';}
      if(empty(trim($lo_vagy_fed))){$lo_vagy_fed='?';}
      if(empty(trim($orszag))){$orszag='?';} 
      $ap_loazon_long=$apja.'--'.$apja_ev.'--'.$apja_orszag;
      $an_loazon_long=$anyja.'--'.$anyja_ev.'--'.$anyja_orszag;
      $loazon_long=$neve.'--'.$ev.'--'.$orszag;
      //print "<br>loazon_long:".$loazon_long;     
          
      //$query = \Drupal::entityQuery('node');
      $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
      //$query->condition('status', 1);
      $query->condition('type', 'lovak');
      $query->condition('field_loazon_long', $ap_loazon_long);
      $ap_entity_ids = $query->execute();
        
      
      $ap_nid=$na_nid;//n.a.
      foreach($ap_entity_ids as $key => $value){$ap_nid=$value;}
      
          
      //$query = \Drupal::entityQuery('node');
      $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
      //$query->condition('status', 1);
      $query->condition('type', 'lovak');
      $query->condition('field_loazon_long', $an_loazon_long);
      $an_entity_ids = $query->execute();
      $an_nid=$na_nid;//n.a.
      foreach($an_entity_ids as $key => $value){$an_nid=$value;}
        
      //print "<br>an:".$an_nid;
      
      
      $loazonki=$title.'--'.$ker_ev.'--'.$ker_orszag;
      
          // szine - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'szine')->condition('name', $szine)->accessCheck(FALSE)->execute();$term_szine = Term::loadMultiple($tids);
       // lo_vagy_fed - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'lo_vagy_fed')->condition('name', $lo_vagy_fed)->accessCheck(FALSE)->execute();$term_lo_vagy_fed = Term::loadMultiple($tids);
       // fedezomen - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'fedezomen')->condition('name', $fedezomen)->accessCheck(FALSE)->execute();$term_fedezomen = Term::loadMultiple($tids);
       // imp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'imp_orsz')->condition('name', $imp_orsz)->accessCheck(FALSE)->execute();$term_imp_orsz = Term::loadMultiple($tids);
       // exp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'exp_orsz')->condition('name', $exp_orsz)->accessCheck(FALSE)->execute();$term_exp_orsz = Term::loadMultiple($tids);
       // orszag - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'orszag')->condition('name', $orszag)->accessCheck(FALSE)->execute();$term_orszag = Term::loadMultiple($tids);
       // neme - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'neme')->condition('name', $neme)->accessCheck(FALSE)->execute();$term_neme = Term::loadMultiple($tids);
       
          
          $node = Node::create([
                  'type' => 'lovak', 'langcode' => 'hu', 'uid' => 1,
                  'moderation_state' => 'published', 'title' => $title, 'field_ev' => $ev,
                  'field_fedez' => $fedez, 'field_penz' => $penz, 'field_rekord' => $rekord,
                  'field_akti' => $aktiv,'field_belyeg' => $belyeg, 'field_egybevetes' => $egybevetes, 'field_ellet' => $ellet,
                  'field_elozo' => $elozo, 'field_exp_kelt' => $exp_kelt,'field_fedez' => $fedez,  'field_herel' => $herel, 'field_neve_tenyeszt' => $nevel,
                  'field_iker' => $iker, 'field_imp_kelt' => $imp_kelt,'field_jegyzet' => $jegyzet, 'field_jelek' => $jelek, 'field_jelx' => $jelx,
                  'field_jel_' => $jel_, 'field_kimult' => $kimult,'field_loazon' => $loazon,'field_loazon_long' => $loazon_long,'field_neve' => $neve,  'field_no' => $no,
                  'field_regi_rek' => $regi_rek,'field_szures' => $szures, 'field_transzponder' => $transzponder,
                  'field_ueln' => $ueln, 'field_uj_rek' => $uj_rek,'field_upgrd' => $upgrd, 'field_utal' =>$utal,
                  'field_szine' => $term_szine, 'szine',
                  'field_lo_vagy_fed' => $term_lo_vagy_fed,
                  'field_fedezomen' => $term_fedezomen,
                  'field_imp_orsz' => $term_imp_orsz,
                  'field_exp_orsz' => $term_exp_orsz,
                  'field_orszag' => $term_orszag,
                  'field_neme' => $term_neme,
                  'field_apa' => $ap_nid, 'field_anya' => $an_nid, 'field_apanev' => $apja, 'field_anyanev' => $anyja,
                  'field_nlkft_iloid' => $nlkft_iloid
                   ]);
          $node->save();
      
    }else{
      $dupilo.=$ker_ev.'-'.$ker_neve.'-'.$ker_apja.'-'.$ker_anyja;
    }
  }  
    return array(
      '#type' => 'markup',
      '#markup' => t('Érkezett új:'.$s.', Felvitt új:'.$ss.', Duplán fel nem vitt:'.$dupilo),
    );  
    
  
  }
  
  // Ha a szülö késöbb kerül az adatbázisba mint a leszármazott, akkor a konzisztencia nem fog magától helyreállni. Ez a fv. végigszalad az összes n.a. -os apával,
  // anyával rendelkező lovon, és megpróbál találni nekik az importáláskor beállított apanev, anyanev felhasználásával apát, anyát, majd eltárolja a nid -eket.  
  public function n_a_korrection($nid=0) {
    
    drupal_flush_all_caches();    
    //ha van $nid, akkor csak azt a lovat, ha nincs akkor az összeset, ahol vagy apa n.a. vagy anya n.a., tehát 22318 az id
    //$query = \Drupal::entityQuery('node');
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    //$query->condition('status', 1);
    $orgroup = $query->orConditionGroup()->condition('field_apa', 1)->condition('field_anya', 1);
    $query->condition('type', 'lovak');
    $query->condition($orgroup);
    $korr_entity_ids = $query->execute();
  
    if($nid!=0){
        $korr_entity_ids = array($nid => $nid);               
    }
    
    $s=0;
    $out='';
    $apkerszam=1;$aptalszam=0;$ankerszam=0;$antalszam=0;
    $aptalnevek='';
    $antalnevek='';
    foreach($korr_entity_ids as $korr_key => $korr_value){
      //print "<br>foreach";
      $s++;
      $nid=$korr_value;  
      $na_nid=1;
      //megnézni, hogy a szülők benne vannak-e az adatbázisban>
      //APA
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($nid);
    
      //echo $node->id();  // 123
      $ev = $node->get('field_ev')->value;
      $neve = $node->get('field_neve')->value;
      $apa_nid = $node->get('field_apa')->target_id;
      $anya_nid = $node->get('field_anya')->target_id;
      $apanev = $node->get('field_apanev')->value;
      $anyanev = $node->get('field_anyanev')->value;
      //$out.= '('.$neve.'-';
      //Ha az APA n.a., akkor megpróbálni megtalálni az adatbázisban 
      if($apa_nid == $na_nid && $apanev!=''){
        $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
         //$query->condition('status', 1);
        $query->condition('type', 'lovak');
         $query->condition('field_neve', $apanev);
        $ap_entity_ids = $query->execute();
         $ap_nid=$na_nid;//n.a.
        //$out.= 'ApKer '.$ap_nid;
        $apkerszam++;
         foreach($ap_entity_ids as $key => $value){
            $ap_nid=$value;
            //print $ap_nid;
            //Ha talált az adatbázisban apnev alapján rekordot, akkor a nid-et beírni a leszármazottnál az field_apa entiásba-ba!
            $nodeUpd = Node::load($nid); // Loading the Node by its Id.
            $nodeUpd->set('field_apa', $ap_nid);
            $nodeUpd->save(); // Saving the Node object.
            $out.= 'ApTal '.$ap_nid;
            $aptalszam++;
            $aptalnevek.='('.$neve.')';
        }
      }
      //Ha az ANYA n.a., akkor megpróbálni megtalálni az adatbázisban 
      if($anya_nid == $na_nid && $anyanev!=''){
        $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
         //$query->condition('status', 1);
        $query->condition('type', 'lovak');
         $query->condition('field_neve', $anyanev);
        $an_entity_ids = $query->execute();
         $an_nid=$na_nid;//n.a.
        //$out.= 'AnKer '.$ap_nid;
        $ankerszam++;
         foreach($an_entity_ids as $key => $value){
            $an_nid=$value;
            //print $an_nid;
            //Ha talált az adatbázisban anynev alapján rekordot, akkor a nid-et beírni a leszármazottnál az field_anya entiásba-ba!
            $nodeUpd = Node::load($nid); // Loading the Node by its Id.
            $nodeUpd->set('field_anya', $an_nid);
            $nodeUpd->save(); // Saving the Node object.
            $out.= 'AnTal '.$an_nid;
            $antalszam++;
            $antalnevek.='('.$neve.')';
        }
      }
      //$out.= ')';
      //megnézni, hogy a szülők benne vannak-e az adatbázisban<
    }  
    //'#markup' => t('ap_nid-an_nid: '.$ap_nid.'-'.$an_nid),
    
    $ownerEmail = 'zojuhaszlog@gmail.com';
    $subject ='ugeto D9 ÉLES Importkorrection';
    //$msg = 'create:'.$c.' update:'.$u;
    $out="\n".'osszes apanelkuli kereses:'.$apkerszam."\n".'apatalalas szama:'.$aptalszam."\n".'nevszerint:'.$aptalnevek.' '.$out;
    $out='osszes anyanelkuli kereses:'.$ankerszam."\n".'anyatalalas szama:'.$antalszam."\n".'nevszerint:'.$antalnevek.' '.$out;
    mail($ownerEmail,$subject,$out);
    
    return array('#type' => 'markup','#markup' => t('log:').$out);  
  }  
  
  
  
  //régi.ugeto LO táblából kigyüjti a nemrég (14 days ago) frissülteket, vagy felvitteket és beimportálja az új adatbázis NODE -ba.
  public function upgrade() {

  drupal_flush_all_caches();    
  //n.a. rekord
  $na_nid=1;//n.a
  //$days_ago='14 days ago';
  $days_ago='14 days ago';
  $days_ig='0 days ago';
  //\Drupal\Core\Datetime\DrupalDateTime;
  
  \Drupal\Core\Database\Database::setActiveConnection('external');
  $db = \Drupal\Core\Database\Database::getConnection();
  
//return ;  
  //Kigyűjteni a nemrég frissülteket. authored_date, updated_date mezőket kell figyelni. Beállítani egy limit-et range(0,200), hogy nehogy belefulladjon
  $date_ago = new DrupalDateTime($days_ago);
  $date_ago->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
  $curdate_ago = $date_ago->format(DATETIME_DATETIME_STORAGE_FORMAT);
  
  $date_ig = new DrupalDateTime($days_ig);
  $date_ig->setTimezone(new \DateTimezone(DATETIME_STORAGE_TIMEZONE));
  $curdate_ig = $date_ig->format(DATETIME_DATETIME_STORAGE_FORMAT);
  
  $query = $db->select('lovak', 'lo');
  $query->leftJoin('lovak','ap','ap.neve = lo.apja');
  $query->leftJoin('LO','nl','nl.SLONEV = lo.neve && nl.ISZULETESIEV = lo.ev');
  $query->fields('lo', ['no','jel_','jelx','neve','rekord','penz','ev','fedez', 'ellet','szine', 'neme', 'belyeg','utal', 'neve', 'orszag','tulajdonos','nevel', 'apja', 'apja_ev',
                 'apja_orszag','apja_2','apja_2_ev','anyja','anyja_ev','anyja_orszag','kimult','herel','jelek','iker','kiirt','elozo',
                 'jegyzet','imp_orsz','imp_kelt','exp_orsz','exp_kelt','men_ki','men_be','mum_ki','regi_rek','uj_rek','rekord_ok','uj_apa',
                 'uj_anya','fedezomen','aktiv','upgrd','egybevetes','lo_vagy_fed','author','authored_date','updater','updated_date','loazon','transzponder',
                 'ueln','megjegyzes','szures']);
  $query->addField('ap', 'no', 'ap_no');$query->addField('ap', 'neve', 'ap_neve');$query->addField('ap', 'ev', 'ap_ev');$query->addField('ap', 'orszag', 'ap_orszag');
  $query->addField('nl', 'ILOID', 'nlkft_iloid');
  $query->condition('lo.lo_vagy_fed', 'LO', '=');
  $query->condition('lo.ev', 0, '!=');
  $query->condition('lo.anyja', '', '!=');
  //$query->condition('lo.neve', 'Ulan Bator Baba', '=');
  $orCondition = $query->orConditionGroup()->condition('lo.authored_date', $curdate_ago, '>')->condition('lo.updated_date', $curdate_ago, '>');
  //$query->condition('lo.authored_date', $curdate, '>');
  //$query->condition('lo.updated_date', $curdate, '>');
  $query->condition($orCondition);
  $query->condition('lo.authored_date', $curdate_ig, '<');
  $query->condition('lo.updated_date', $curdate_ig, '<');
  
  $query->orderBy('lo.authored_date', 'ASC');
  //$query->range(0, 10);
  $results = $query->execute();
  
  Database::setActiveConnection();  
  $s=0;
  $c=0;
  $u=0;
  $printstr='';
  while ($content = $results->fetchAssoc()) {
    $s++;       
    $no=$content['no'];$neve=$content['neve'];$ev=$content['ev'];$orszag=$content['orszag'];
    $apja=$content['apja'];$apja_ev=$content['apja_ev']; $apja_orszag=$content['apja_orszag'];
    $anyja=$content['anyja'];$anyja_ev=$content['anyja_ev']; $anyja_orszag=$content['anyja_orszag'];
    $title=$neve; $fedez=$content['fedez']; $penz=$content['penz']; $rekord=$content['rekord'];
    $szine=$content['szine']; $lo_vagy_fed=$content['lo_vagy_fed'];$fedezomen=$content['fedezomen'];$imp_orsz=$content['imp_orsz'];$exp_orsz=$content['exp_orsz'];
    $neme=$content['neme'];
    $jel_=$content['jel_'];$jelx=$content['jelx']; $ellet=$content['ellet'];$belyeg=$content['belyeg'];
    $utal=$content['utal']; $nevel=$content['nevel'];$tulajdonos=$content['tulajdonos'];
    $apja_2=$content['apja_2']; $apja_2_ev=$content['apja_2_ev'];$kimult=$content['kimult'];
    $herel=$content['herel']; $jelek=$content['jelek'];$iker=$content['iker'];
    $kiirt=$content['kiirt']; $elozo=$content['elozo'];$jegyzet=$content['jegyzet'];
    $imp_kelt=$content['imp_kelt']; $exp_kelt=$content['exp_kelt'];$men_ki=$content['men_ki'];
    $mum_ki=$content['mum_ki']; $regi_rek=$content['regi_rek'];$uj_rek=$content['uj_rek'];
    $rekord_ok=$content['rekord_ok']; $uj_apa=$content['uj_apa'];$uj_anya=$content['uj_anya'];
    $aktiv=$content['aktiv']; $upgrd=$content['upgrd'];$egybevetes=$content['egybevetes'];
    $author=$content['author']; $authored_date=$content['authored_date'];$updater=$content['updater'];
    $updated_date=$content['updated_date']; $loazon=$content['loazon'];$transzponder=$content['transzponder'];
    $ueln=$content['ueln']; $megjegyzes=$content['megjegyzes'];$szures=$content['szures'];
    $nlkft_iloid=$content['nlkft_iloid'];
    if(empty(trim($szine))){$szine='?';}
    if(empty(trim($neme))){$neme='?';}
    if(empty(trim($lo_vagy_fed))){$lo_vagy_fed='?';}
    if(empty(trim($orszag))){$orszag='?';} 
    $ap_loazon_long=$apja.'--'.$apja_ev.'--'.$apja_orszag;
    $an_loazon_long=$anyja.'--'.$anyja_ev.'--'.$anyja_orszag;
    $printstr.="<br>neve szine iloid:".$neve."-".$szine."-".$nlkft_iloid;     
         
    //megnézni, hogy a szülők benne vannak-e az adatbázisban>
    //APA
    //$query = \Drupal::entityQuery('node');
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    //$query->condition('status', 1);
    $query->condition('type', 'lovak');
    $query->condition('field_loazon_long', $ap_loazon_long);
    $ap_entity_ids = $query->execute();
    $ap_nid=$na_nid;//n.a.
    foreach($ap_entity_ids as $key => $value){$ap_nid=$value;}
    //ANYA         
    //$query = \Drupal::entityQuery('node');
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    //$query->condition('status', 1);
    $query->condition('type', 'lovak');
    $query->condition('field_loazon_long', $an_loazon_long);
    $an_entity_ids = $query->execute();
    $an_nid=$na_nid;//n.a.
    $created_neve='';
    foreach($an_entity_ids as $key => $value){$an_nid=$value;}
    //megnézni, hogy a szülők benne vannak-e az adatbázisban<
     
     
     
     
    //Kikeresni az új node entitást egyesével (neve, ev, orszag, anyja alapján)
    $bundle='lovak';
    
    
    //$node = \Drupal::entityTypeManager()->getStorage('node');
    //$term = Term::load($node->get('field_orszag')->target_id);
    
    //print_r($node);
    
    //$query = \Drupal::entityQuery('node');
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    $query->condition('type', $bundle);
    $query->condition('field_neve', $neve);
    $query->condition('field_ev', $ev);
    $query->condition('field_orszag.entity.name', $orszag);
    $query->condition('field_anyanev', $anyja);
    $upgrd_entity_ids = $query->execute();
    $updated_neve='';
    foreach($upgrd_entity_ids as $key => $value){$nidUpd=$value;}  
    
    $num_rows = $query->count()->execute();
    
    if ($num_rows == 0) {
      //Ha nem talál, akkor CREATE
      $c++;
      $title=$neve;
      $created_neve.=','.$neve;
      //print "<br>NEMTALÁL: ".$neve."-".$ev."-".$orszag."-".$anyja."-".$ap_nid."-".$an_nid;
      //print "<br>";
      if(isset($anyja) && $anyja!=''){
        
       // szine - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'szine')->condition('name', $szine)->accessCheck(FALSE)->execute();$term_szine = Term::loadMultiple($tids);
       // lo_vagy_fed - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'lo_vagy_fed')->condition('name', $lo_vagy_fed)->accessCheck(FALSE)->execute();$term_lo_vagy_fed = Term::loadMultiple($tids);
       // fedezomen - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'fedezomen')->condition('name', $fedezomen)->accessCheck(FALSE)->execute();$term_fedezomen = Term::loadMultiple($tids);
       // imp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'imp_orsz')->condition('name', $imp_orsz)->accessCheck(FALSE)->execute();$term_imp_orsz = Term::loadMultiple($tids);
       // exp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'exp_orsz')->condition('name', $exp_orsz)->accessCheck(FALSE)->execute();$term_exp_orsz = Term::loadMultiple($tids);
       // orszag - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'orszag')->condition('name', $orszag)->accessCheck(FALSE)->execute();$term_orszag = Term::loadMultiple($tids);
       // neme - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'neme')->condition('name', $neme)->accessCheck(FALSE)->execute();$term_neme = Term::loadMultiple($tids);
        
              
      $node = Node::create(['type' => 'lovak', 'langcode' => 'hu', 'uid' => 1,'moderation_state' => 'published', 'title' => $title, 'field_ev' => $ev,'field_fedez' => $fedez, 'field_penz' => $penz, 'field_rekord' => $rekord, 'field_akti' => $aktiv,'field_belyeg' => $belyeg, 'field_egybevetes' => $egybevetes, 'field_ellet' => $ellet,
                'field_elozo' => $elozo, 'field_exp_kelt' => $exp_kelt,'field_fedez' => $fedez,'field_nevel_tenyeszt' => $nevel,  'field_herel' => $herel,'field_iker' => $iker, 'field_imp_kelt' => $imp_kelt,'field_jegyzet' => $jegyzet, 'field_jelek' => $jelek, 'field_jelx' => $jelx,'field_jel_' => $jel_, 'field_kimult' => $kimult,'field_loazon' => $loazon,
                'field_loazon_long' => $title.'--'.$ev.'--'.$orszag,'field_neve' => $neve,  'field_no' => $no, 'field_regi_rek' => $regi_rek,'field_szures' => $szures, 'field_transzponder' => $transzponder,
                'field_ueln' => $ueln, 'field_uj_rek' => $uj_rek,'field_upgrd' => $upgrd, 'field_utal' =>$utal,'field_szine' => $term_szine, 'field_lo_vagy_fed' => $term_lo_vagy_fed, 'lo_vagy_fed',
                'field_fedezomen' => $term_fedezomen,'field_imp_orsz' => $term_imp_orsz,'field_exp_orsz' => $term_exp_orsz,
                'field_orszag' => $term_orszag,'field_neme' => $term_neme,'field_apa' => $ap_nid, 'field_anya' => $an_nid, 'field_apanev' => $apja, 'field_anyanev' => $anyja,'field_nlkft_iloid' => $nlkft_iloid]);
      $node->save();
      }      
    }elseif($num_rows == 1){
      //Ha talál, akkor UPDATE
      $u++;
      $updated_neve.=','.$neve;
      //if($neve=="Urus Caf"){
      $nodeUpd = Node::load($nidUpd); // Loading the Node by its Id.
      //$nodeUpd->set('title', 'New title updated');
      //$nodeUpd->set('body', 'The body text has been updated');
      $nodeUpd->set('field_fedez', $fedez);
      $nodeUpd->set('field_penz', $penz);
      $nodeUpd->set('field_rekord', $rekord);
      $nodeUpd->set('field_akti', $aktiv);
      $nodeUpd->set('field_belyeg', $belyeg);
      $nodeUpd->set('field_egybevetes', $egybevetes);
      $nodeUpd->set('field_ellet', $ellet);
      $nodeUpd->set('field_elozo', $elozo);
      $nodeUpd->set('field_exp_kelt', $exp_kelt);
      $nodeUpd->set('field_fedez', $fedez);
      $nodeUpd->set('field_nevel_tenyeszt', $nevel);
      $nodeUpd->set('field_herel', $herel);
      $nodeUpd->set('field_iker', $iker);
      $nodeUpd->set('field_imp_kelt', $imp_kelt);
      $nodeUpd->set('field_exp_kelt', $exp_kelt);
      $nodeUpd->set('field_jegyzet', $jegyzet);
      $nodeUpd->set('field_jelek', $jelek);
      $nodeUpd->set('field_jelx', $jelx);
      $nodeUpd->set('field_jel_', $jel_);
      $nodeUpd->set('field_exp_kelt', $exp_kelt);
      $nodeUpd->set('field_kimult', $kimult);
      $nodeUpd->set('field_no', $no);
      $nodeUpd->set('field_regi_rek', $regi_rek);
      $nodeUpd->set('field_szures', $szures);
      $nodeUpd->set('field_transzponder', $transzponder);
      $nodeUpd->set('field_ueln', $ueln);
      $nodeUpd->set('field_uj_rek', $uj_rek);
      $nodeUpd->set('field_upgrd', $upgrd);
      $nodeUpd->set('field_utal', $utal);
      
      
      
       // szine - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'szine')->condition('name', $szine)->accessCheck(FALSE)->execute();$term_szine = Term::loadMultiple($tids);
       // lo_vagy_fed - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'lo_vagy_fed')->condition('name', $lo_vagy_fed)->accessCheck(FALSE)->execute();$term_lo_vagy_fed = Term::loadMultiple($tids);
       // fedezomen - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'fedezomen')->condition('name', $fedezomen)->accessCheck(FALSE)->execute();$term_fedezomen = Term::loadMultiple($tids);
       // imp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'imp_orsz')->condition('name', $imp_orsz)->accessCheck(FALSE)->execute();$term_imp_orsz = Term::loadMultiple($tids);
       // exp_orsz - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'exp_orsz')->condition('name', $exp_orsz)->accessCheck(FALSE)->execute();$term_exp_orsz = Term::loadMultiple($tids);
       // orszag - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'orszag')->condition('name', $orszag)->accessCheck(FALSE)->execute();$term_orszag = Term::loadMultiple($tids);
       // neme - Entitás lekérdezés a kifejezések betöltéséhez
       $tids = \Drupal::entityQuery('taxonomy_term')->condition('vid', 'neme')->condition('name', $neme)->accessCheck(FALSE)->execute();$term_neme = Term::loadMultiple($tids);
       
      
      
      $nodeUpd->set('field_szine', $term_szine, 'szine');
      $nodeUpd->set('field_lo_vagy_fed', $term_lo_vagy_fed, 'field_lo_vagy_fed');
      $nodeUpd->set('field_fedezomen', $term_fedezomen, 'fedezomen');
      $nodeUpd->set('field_imp_orsz', $term_imp_orsz, 'orszag');
      $nodeUpd->set('field_exp_orsz', $term_exp_orsz, 'orszag');
      $nodeUpd->set('field_szine', $term_szine, 'szine');
      $nodeUpd->set('field_neme', $term_neme, 'neme');
      $nodeUpd->set('field_apa', $ap_nid);
      $nodeUpd->set('field_anya', $an_nid);
      $nodeUpd->set('field_apanev', $apja);
      $nodeUpd->set('field_anyanev', $anyja);
      $nodeUpd->set('field_nlkft_iloid', $nlkft_iloid);
      $nodeUpd->set('field_loazon_long', $title.'--'.$ev.'--'.$orszag);
      
      $nodeUpd->save(); // Saving the Node object.
      //} 
    
    }else{
      //print "<br>TÖBBESTALÁLAt!!!!!: ";
    }    
    
       
  
  }
  $msg='';    
  $ownerEmail = 'zojuhaszlog@gmail.com';
  $subject ='ugeto D9 ÉLES Importupgrd'.' --osszes:'.$s.' --create:'.$c.' --upgr:'.$u;
  $msg.= 'osszes:'.$s."\n".'create:'.$c.$created_neve."\n".'upgr:'.$u.$updated_neve;
       
  mail($ownerEmail,$subject,$msg);
    
  
  return array(
      '#type' => 'markup',
      '#markup' => t(' osszes:'.$s.' <br>create:'.$c.$created_neve.' <br>upgr:'.$u.$updated_neve),
  );  
    
  
  }
  
  // Kiszürni a duplán szereplö lovakat
  // Alapból ki van herélve, a KIKOMMENTEZNI részt kell kikommentezni, ha éles
  // ugeto.com/hellokaduplalovak
  public function dupla_lovak($nid=0) {
    
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    //$query->condition('status', 1);
    $query->condition('type', 'lovak');
    //$query->sort('field_neve' , 'DESC'); 
    $lovak_ids = $query->execute();
    $egylo_parameters=array();
    $out='';
    $s=0;
    foreach($lovak_ids as $key => $value){
      $nid=$value;
      //print "<br>".$nid;
       $s++;
      $na_nid=1;
     
      //minden azonosito parametert kiszedünk     
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($nid);
      $ev = $node->get('field_ev')->value;
      $neve = $node->get('field_neve')->value;
      $apa_nid = $node->get('field_apa')->target_id;
      $anya_nid = $node->get('field_anya')->target_id;
      $parameters_str=$neve.$ev.$apa_nid.$anya_nid;
      //megnézzük, hogy van-e már a tömbben, ha nincs eltároljuk, hogy lehessen keresni a duplázódókat
      $talalt_key=array_search($parameters_str, $egylo_parameters);
      if(!$talalt_key){
           $egylo_parameters[]=$parameters_str;
      }else{
           //KIKOMMENTEZNI, HA ÉLES
           //$node->delete($node);
           $out.="<br>Töröltem, mert több van:".$neve." ".$nid."IGAZÁBÓL NEM TÖRÖLTEM MERT AHHOZ KI KELL KOMMENTELNI-HelloController.php-462.sor";
      }
    }
    //print_r($egylo_parameters);
    return array('#type' => 'markup','#markup' => t('duplalovak:').$out);  
  }
  
  // A dupla lovak miatti törlés miatti hiányzó apák, anyák cseréje 22318-re
  // alapbol ki van herélve, a KIKOMMENTEZNI részt kell kikommentezni, ha éles
  // ugeto.com/hellokatorolt
  public function torolt_miatti($nid=0) {
    
    $query = \Drupal::entityQuery('node')->accessCheck(TRUE);
    //$query->condition('status', 1);
    $query->condition('type', 'lovak');
    $query->condition('field_neve', 'Amour Angus'); // EZT KELL KIKOMMENTEZNI, HOGY VÉGIGMENNEJ MINDENEN
    //$query->sort('field_neve' , 'DESC'); 
    $lovak_ids = $query->execute();
    $egylo_parameters=array();
    $out='';
    $s=0;
    foreach($lovak_ids as $key => $value){
      $nid=$value;
      $out=$nid;
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($nid);
      $ev = $node->get('field_ev')->value;
      $neve = $node->get('field_neve')->value;
      $apa_nid = $node->get('field_apa')->target_id;
      $anya_nid = $node->get('field_anya')->target_id;
      //print "<br>a:";
      //print_r($node);
      $values_apa = \Drupal::entityQuery('node')->condition('nid', $apa_nid)->execute();
      $node_exists_apa = !empty($values_apa);
      $values_anya = \Drupal::entityQuery('node')->condition('nid', $anya_nid)->execute();
      $node_exists_anya = !empty($values_anya);
      
      //ha nincs ilyen apa_nid a nod -ban, akkor beírni a ló-nak, hogy field_apa = 22318
      if(!$node_exists_apa){
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd = Node::load($nid); // Loading the Node by its Id.
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd->set('field_apa', 22318);
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd->save(); // Saving the Node object.
        $out.=" Neve:".$neve." Apa: ".$node_exists_apa." Anya:".$node_exists_anya;
      }
      //ha nincs ilyen anya_nid a nod -ban, akkor beírni a ló-nak, hogy field_anya = 22318
      if(!$node_exists_anya){
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd = Node::load($nid); // Loading the Node by its Id.
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd->set('field_anya', 22318);
        //KIKOMMENTEZNI, HA ÉLES $nodeUpd->save(); // Saving the Node object.
        $out.=" Neve:".$neve." Apa: ".$node_exists_apa." Anya:".$node_exists_anya;
      }   
      
      
    }
    //print_r($egylo_parameters);
    return array('#type' => 'markup','#markup' => t('beirva:').$out);  
  }
  
  // Hiányzik a tenyésztö.Pótlása
  // ugeto.com/helloka_nincs_tenyeszto
  // alapbol ki van herélve, a KIKOMMENTEZNI részt kell kikommentezni, ha éles
  public function nincs_tenyeszto($nid=0) {
    
      
    \Drupal\Core\Database\Database::setActiveConnection('external');
    $db = \Drupal\Core\Database\Database::getConnection();
    
    //Kigyűjteni az összes lovat
      
    $query = $db->select('lovak', 'lo');
    $query->fields('lo', ['no','nevel','neve']);
    
    //itt egy elöszürés van hogy ne legyen olyan sok rekord
    $query->condition('lo.no', 21000, '>');
    $results_nevel = $query->execute();
    Database::setActiveConnection();  
    $out='';
    while ($content = $results_nevel->fetchAssoc()) {
     
      //$s++;
      // egyesével baírni a lovakat a dr8 -ba
      //print_r($content);
      //$no=$content['no'];$neve=$content['neve'];$ev=$content['ev'];$orszag=$content['orszag'];
      $no=$content['no'];
      if($no>21000 && $no<23000){
      $nevel=$content['nevel'];
      $neve=$content['neve'];
      $bundle='lovak';
      $query = \Drupal::entityQuery('node');
      $query->condition('type', $bundle);
      $query->condition('field_no', $no);
      $upgrd_entity_ids = $query->execute();
      foreach($upgrd_entity_ids as $key => $value){
        $nidUpd=$value;
        $out.=$no.'-'.$neve.'-'.$nevel.' ';
        //print "nevel:".$nevel;
        
        ////////////////////////////////////////////////////////////////////////////////////
        //KÖVETKEZÖ 3 SORT KELL KI-BE KOMMENTELNI
        //$nodeUpd = Node::load($nidUpd); // Loading the Node by its Id.
        //$nodeUpd->set('field_nevel_tenyeszt', $nevel);
        //$nodeUpd->save(); // Saving the Node object.
        ///////////////////////////////////////////////////////////////////////////////////
      }  
      }
    }
   return array('#type' => 'markup','#markup' => t('beirva:').$out);  
  }
  
}
