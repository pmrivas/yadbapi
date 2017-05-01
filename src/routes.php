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
  $ind->aMenu=array(array('hrmenu'=>"#",'menuPage'=>"browse",'classli'=>"",'icon'=>"fa fa-folder",'txmenu'=>"Estudios",'menuid'=>"menu1"),
                    array('hrmenu'=>"#",'classli'=>"treeview",'icon'=>"fa fa-envelope",'txmenu'=>"Enviados",'menuid'=>"menu2"),
                    array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-calendar",'txmenu'=>"Informes",'menuid'=>"menu3"));
  $ind->sMenues=array(array('menuid'=>"#menu2",'elems'=>array(array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-circle-o text-red",'txmenu'=>"Informantes"),
            array('hrmenu'=>"#",'classli'=>"",'icon'=>"fa fa-circle-o text-blue",'txmenu'=>"Médicos"))));
  return $response->withJson($ind);
});

$app->post('/study',function ($request,$response,$args) {
  $data=$request->getParsedBody();
  $answ="";
  $answers=array();
  $index=array();
  if ($data['stIUID']) {
    $qstring="--key 0020,000e --key 0008,0060 --key 0020,0011 --key 0020,1209 --key 0020,0013 --key 0020,1208 --key 0008,103E --key 0008,0021 --key 0008,0031 --key 0008,0018 --key 0020,000d=" . $data['stIUID'];
    $qstring.=" 2>&1";
    foreach($this->settings['pacs'] as $k=>$v) {
      $rpta=array();
      $xx=exec($v['images'] . $qstring,$rpta);
      foreach($rpta as $kk=>$vv) $answers[]=$vv;
    }
    $ares=array();
    $curr=array();
    foreach($answers as $k=>$v) {
      if (substr($v,0,1)=="(") {
        if (!isset($this->settings['dctags'][substr($v,1,strpos($v,")")-1)])) $field=substr($v,1,strpos($v,")")-1);
        else $field=$this->settings['dctags'][substr($v,1,strpos($v,")")-1)];
        if (strpos($v,"[")!==false) $curr[$field]=trim(substr($v,strpos($v,"[")+1,strpos($v,"]")-strpos($v,"[")-1));
        else $curr[$field]="";
      } else {
        if (substr($v,0,5)=="Find ") continue;
        $curr['serDate']=substr("00000000" . $curr['serDate'],-8);
        $curr['serTime']=substr("000000" . floor($curr['serTime']),-6);
        $curr['serDate']=date("d/m H:i",strtotime(substr($curr['serDate'],0,4) . "-" . substr($curr['serDate'],4,2) . "-" . substr($curr['serDate'],-2) . " " . substr($curr['serTime'],0,2) . ":" . substr($curr['serTime'],2,2) . ":00"));
        if (!isset($index[$curr['serUID']])) {
          $index[$curr['serUID']]=$curr;
          $index[$curr['serUID']]['first']=$curr['insUID'];
        } else {
          if (($curr['instNumber']*1>($curr['instCount']/2)) and (!isset($index[$curr['serUID']]['middle']))) $index[$curr['serUID']]['middle']=$curr['insUID'];
          $index[$curr['serUID']]['last']=$curr['insUID'];
        }
        $curr=array();
      }
      if (count($ares)>300) break; //Limit first 300 results...
    }
  }
  return $response->withJson(array('ok'=>1,"res"=>array_values($index)));
});
$app->get('/wado/{uid}/{cols}',function($request,$response,$args) {
  $image=shell_exec('wget -q -O - "http://192.168.6.102:8080/wado?requestType=WADO&studyUID=&seriesUID=&objectUID=' . $args['uid'] . '&frameNumber=1&columns=' . $args['cols'] . '"');
  $response->write($image);
  return $response->withHeader('Content-Type', 'image/jpg');
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
/****
ejemplos:
traer imagenes de serie..
0020,1209: x estudio
0020,1209: x placa
gdcmscu --find --studyroot --series 192.168.6.102 11112 --aetitle yadbrowser  --call DCM4CHEE --key 0020,000e --key 0008,0060 --key 0020,0011 --key 0020,1209 --key 0008,103E --key 0020,000d=1.2.840.113564.99.1.26403107351199.8.2016923144812977.255647.2

wado:
"http://192.168.6.102:8080/wado?requestType=WADO&studyUID=1.2.840.113564.99.1.26403107351199.8.2016923144812977.255647.2&seriesUID=1.3.46.670589.11.18801.5.0.5656.2016092316033537000&objectUID=1.3.46.670589.11.18801.5.0.5656.2016092316033595004&frameNumber=1"


Mover:
movescu --study --move PACSOLD --call DCM4CHEE  --aetitle PABLOHOME 192.168.6.102 11112 --key 0020,000d=1.2.840.113564.99.1.26403107351199.33.201610228711553.262132.2 --key 0008,0052="STUDY"


query a wl:

findscu -W  -k 0010,0010 -k ScheduledProcedureStepSequence[0].ScheduledProcedureStepStartDate=20161024 localhost 5555 -aec WLCSH -aet MRINTERA
findscu -W  -k 0010,0010 -k 0010,0020=35696 -k 0008,0050  -k ScheduledProcedureStepSequence[0].ScheduledProcedureStepStartDate=20161024 -k ScheduledProcedureStepSequence[0].Modality -k ScheduledProcedureStepSequence[0].ScheduledProcedureStepStartTime -k 0020,00d localhost 5555 -aec WLCSH -aet MRINTERA


findscu -W -k 0010,0020 -k ScheduledProcedureStepSequence[0].ScheduledProcedureStepStartDate=20161025 -k 0020,00d localhost 5555 -aec WLCSH -aet MRINTERA

findscu -W -k 0010,0020 -k ScheduledProcedureStepSequence[0].ScheduledProcedureStepStartDate=20161031 -k 0020,00d localhost 5555 -aec WLCSH -aet MRINTERA 2>&1 |grep --text "0020,000d" | awk -F [\]\[] '{ print $2 }' |sort |uniq -d

*/