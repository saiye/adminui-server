<?php

$lat='113.32775';
$lon='23.116433';
$sql='SELECT * FROM users WHERE latitude > '.$lat.'-1 AND latitude < '.$lat.'+1 AND longitude > '.$lon.'-1 AND longitude < '.$lon.'+1 ORDER BY ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(('.$lon.'* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 ASC LIMIT 10;';

echo $sql;
