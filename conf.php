<?php
$kasutaja="d133853_annaoleks";
$parool="DorisOceana1323";
$andmebaas="d133853_resto";
$srverinimi="d133853.mysql.zonevs.eu";

$yhendus=new mysqli($srverinimi,$kasutaja,$parool,$andmebaas);
$yhendus->set_charset("utf8");
