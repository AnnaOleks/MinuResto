<?php
$parool = 'opilane';
$sool = 'cool';
$krypt = crypt($parool, $sool);
echo $krypt;