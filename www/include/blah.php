<?php
require_once(dirname(__FILE__).'/../../lib/common-web.inc.php');
$times = array();

$sql = "SELECT UNIX_TIMESTAMP(connected) as t
        FROM listenerhistory
        GROUP BY connected LIMIT 1000";
$res = $db->query($sql);
while($row = $db->fetch($res)) {
    $times[$row['t']] = false;
}

$sql = "SELECT UNIX_TIMESTAMP(IF(disconnected IS NULL, NOW(), disconnected)) as t
        FROM listenerhistory
        GROUP BY disconnected LIMIT 1000";
$res = $db->query($sql);
while($row = $db->fetch($res)) {
    $times[$row['t']] = false;
}
$res->free();
ksort($times);
foreach ($times as $key => $value){
    $sql = 'SELECT count(*) as c
            FROM listenerhistory
            WHERE connected <= FROM_UNIXTIME('.$key.')
              AND IF(disconnected IS NULL, NOW(), disconnected) > FROM_UNIXTIME('.$key.')';
    $res = $db->query($sql);
    if($row = $db->fetch($res)) {
        $times[$key] = $row['c'];
    }
}

  include(dirname(__FILE__).'/../../lib/pChart/pData.class');
  include(dirname(__FILE__).'/../../lib/pChart/pChart.class');

  // Dataset definition
  $DataSet = new pData;
  $DataSet->AddPoint(array_values($times),"Serie2");
  $DataSet->AddPoint(array_keys($times),"Serie3");
  $DataSet->AddSerie("Serie2");
  $DataSet->SetAbsciseLabelSerie("Serie3");
  $DataSet->SetSerieName("Outgoing","Serie2");
  $DataSet->SetYAxisName("Call duration");
  $DataSet->SetYAxisFormat("time");
  $DataSet->SetXAxisFormat("date");

  // Initialise the graph
  $Test = new pChart(700,230);
  $Test->setFontProperties("Fonts/tahoma.ttf",8);
  $Test->setGraphArea(85,30,650,200);
  $Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
  $Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
  $Test->drawGraphArea(255,255,255,TRUE);
  $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);
  $Test->drawGrid(4,TRUE,230,230,230,50);

  // Draw the 0 line
  $Test->setFontProperties(dirname(__FILE__).'/../../lib/Fonts/tahoma.ttf',6);
  $Test->drawTreshold(0,143,55,72,TRUE,TRUE);

  // Draw the line graph
  $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
  $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

  // Finish the graph
  $Test->setFontProperties(dirname(__FILE__).'/../../lib/Fonts/tahoma.ttf',8);
  $Test->drawLegend(90,35,$DataSet->GetDataDescription(),255,255,255);
  $Test->setFontProperties(dirname(__FILE__).'/../../lib/Fonts/tahoma.ttf',10);
  $Test->drawTitle(60,22,"example 17",50,50,50,585);
  $Test->Stroke();
?>