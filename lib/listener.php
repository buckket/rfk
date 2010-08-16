<?php
function getListeners($start, $end){
    global $db;
    $times = array();
    $sqls = array("SELECT UNIX_TIMESTAMP(connected) as t
            FROM listenerhistory
            WHERE connected BETWEEN FROM_UNIXTIME($start) AND FROM_UNIXTIME($end)
            GROUP BY connected;
            ",
            "SELECT UNIX_TIMESTAMP(IF(disconnected IS NULL, NOW(), disconnected)) as t
            FROM listenerhistory
            WHERE IF(disconnected IS NULL, NOW(), disconnected) BETWEEN FROM_UNIXTIME($start) AND FROM_UNIXTIME($end)
            GROUP BY connected;");
    for($i = 0; $i < count($sqls); ++$i){
        $res = $db->query($sqls[$i]);
        //echo $sqls[$i];
        while($row = $db->fetch($res)) {
            $sql = 'SELECT count(*) as c
                    FROM listenerhistory
                    WHERE connected <= FROM_UNIXTIME('.$row['t'].')
                      AND IF(disconnected IS NULL, NOW(), disconnected) > FROM_UNIXTIME('.$row['t'].')';
            $res2 = $db->query($sql);
            //echo $sql;
            if($t = $db->fetch($res2)) {
                $times[$row['t']] = $t['c'];
                //echo $t['c'];
            }
            $db->free($res2);
        }
        $db->free($res);
    }
    //ksort($times);
    $avg = 0;
    $max = 0;
    foreach ($times as $key => $value) {
        $avg += $value;
        if($value > $max)
            $max = $value;
    }
    if(count($times) > 0){
        $avg = $avg/count($times);
    }
    return array($max, $avg);
}
?>