<?php
// Routes

/*
$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});*/


$app->get('/init', function ($request, $response, $args) { //Get init info...
  $this->logger->info("yadbapi init");
	$ind=new stdClass;
	$ind->user=new stdClass;
  $ind->user->name="Demo User";
  $ind->user->usrlogo="//dummyimage.com/160x160/000/fff.jpg";
  $ind->user->FullName="Demo User <small>Please Login </small>";
  $ind->user->hrbutton1="javascript:return signUp();";
  $ind->user->button1="Sign Up";
  $ind->user->hrbutton2="javascript:return logIn();";
  $ind->user->button2="Login";
  $ind->footer = new stdClass;
  $ind->footer->version=$this->settings['product']['version'];
  $ind->footer->copyRght=$this->settings['product']['copyRght'];
  $ind->title=$this->settings['product']['title'];
  $ind->LogoLg=$this->settings['product']['LogoLg'];
  $ind->LogoMini=$this->settings['product']['LogoMini'];
  $ind->lang=$this->settings['product']['lang'];
  $ind->aMenu=array(array('hrmenu'=>"#",'menuPage'=>"Browser",'classli'=>"",'icon'=>"fa fa-folder",'txmenu'=>"Estudios",'menuid'=>"menu1"),
                    array('hrmenu'=>"#",'classli'=>"treeview",'icon'=>"fa fa-envelope",'txmenu'=>"Enviados",'menuid'=>"menu2"),
                    array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-calendar",'txmenu'=>"Informes",'menuid'=>"menu3"));
  $ind->sMenues=array(array('menuid'=>"#menu2",'elems'=>array(array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-circle-o text-red",'txmenu'=>"Informantes"),
            array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-circle-o text-blue",'txmenu'=>"Médicos"))));
  return $response->withJson($ind);
});


$app->post('/search',function ($request,$response,$args) {
  $qparams=array('patName','patId','patIssuer','stDate','stTime','modality','stDescr','refDoc','institution','availability','retAET','patBirth','pathSex','stIUID');
  $data=$request->getParsedBody();
  $query=array();
  foreach ($data as $k=>$v) {
    $v=str_replace("\"","",str_replace("'","",$v)); //Strip double and single quotes
    if (in_array($k,array('patName','patIssuer','stDescr','refDoc'))) $query[$k]=filter_var($v, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW) . "*";
    else if ($k=="modality") $query[$k]=substr($v,0,3);
    else if ($k=="patId") $query[$k]=$v*1;
    else if ($k=="stDate") { //Dates exception...
      if (strlen($v)<>35) {
        $v=date("d/m/Y H:i", strtotime("-2 HOURS")) . " - " . date("d/m/Y H:i");
      }
      $from=date("Ymd",strtotime(substr($v,0,10)));
      $to=date("Ymd",strtotime(substr($v,-16,10)));
      $ftime=substr($v,11,2) . substr($v,14,2) . "00";
      $ttime=substr($v,-5,2) . substr($v,-2) . "00";
      if ($from<>$to) $query['stDate']=$from."-".$to;
      else $query['stDate']=$from;
    }
  }
  $qstring="";
  foreach($qparams as $k=>$v) {
    if ((!empty($query[$v])) and (strlen($query[$v])>1)) {
      $qstring.=" --key \"" . array_search($v,$this->settings['dctags']) . "=" . $query[$v] . "\" ";
    } else $qstring.="--key " . array_search($v,$this->settings['dctags']) . " ";
  }
  $qstring.=" 2>&1";
  $answers=array();
  foreach($this->settings['pacs'] as $k=>$v) {
    $rpta=array();
    $xx=exec($v['qstring'] . $qstring,$rpta);
    foreach($rpta as $kk=>$vv) $answers[]=$vv;
  }
  $ares=array();
  $curr=array();
  $index=array();
  foreach($answers as $k=>$v) {
    if (substr($v,0,1)=="(") {
      if (!isset($this->settings['dctags'][substr($v,1,strpos($v,")")-1)])) $field=substr($v,1,strpos($v,")")-1);
      else $field=$this->settings['dctags'][substr($v,1,strpos($v,")")-1)];
      if (strpos($v,"[")!==false) $curr[$field]=trim(substr($v,strpos($v,"[")+1,strpos($v,"]")-strpos($v,"[")-1));
      else $curr[$field]="";
    } else {
      if (substr($v,0,5)=="Find ") continue;
      if (!empty($curr['stTime'])) {
        $time=floor($curr['stTime']);
        if ((($curr['stDate']==$from) and ($time<$ftime)) or (($curr['stDate']==$to) and ($time>$ttime))) continue;
      }
      $curr['serie']=1;
      $curr['stDate']=substr("00000000" . $curr['stDate'],-8);
      $curr['stTime']=substr("000000" . floor($curr['stTime']),-6);
      $curr['stDate']=date("d/m H:i",strtotime(substr($curr['stDate'],0,4) . "-" . substr($curr['stDate'],4,2) . "-" . substr($curr['stDate'],-2) . " " . substr($curr['stTime'],0,2) . ":" . substr($curr['stTime'],2,2) . ":00"));
      if (!isset($index[$curr['patId']])) $index[$curr['patId']]=$curr;
      else {
        $index[$curr['patId']]['serie']+=1;
      }
      $curr=array();
    }
    if (count($ares)>300) break; //Limit first 300 results...
  }
  return $response->withJson(array('ok'=>1,"res"=>array_values($index)));
});