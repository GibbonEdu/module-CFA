<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql=array() ;
$count=0 ;

//v1.0.00 - FIRST VERSION, SO NO CHANGES
$sql[$count][0]="1.0.00" ;
$sql[$count][1]="" ;


//v1.1.00
$sql[$count][0]="1.1.00" ;
$sql[$count][1]="ALTER TABLE `cfaColumn` ADD `uploadedResponse` ENUM('N','Y') NOT NULL DEFAULT 'N' AFTER `comment`;end
ALTER TABLE `cfaEntry` ADD `response` TEXT NOT NULL AFTER `comment`;end
ALTER TABLE `cfaEntry` CHANGE `response` `response` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;end
" ;

?>