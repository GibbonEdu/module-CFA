<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql=array() ;
$count=0 ;

//v1.0.00 - FIRST VERSION, SO NO CHANGES
$sql[$count][0]="1.0.00" ;
$sql[$count][1]="" ;


//v1.1.00
$count++ ;
$sql[$count][0]="1.1.00" ;
$sql[$count][1]="ALTER TABLE `cfaColumn` ADD `uploadedResponse` ENUM('N','Y') NOT NULL DEFAULT 'N' AFTER `comment`;end
ALTER TABLE `cfaEntry` ADD `response` TEXT NOT NULL AFTER `comment`;end
ALTER TABLE `cfaEntry` CHANGE `response` `response` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;end
" ;

//v1.1.01
$count++ ;
$sql[$count][0]="1.1.01" ;
$sql[$count][1]="" ;

//v1.2.00
$count++ ;
$sql[$count][0]="1.2.00" ;
$sql[$count][1]="
UPDATE gibbonAction SET name='Manage CFAs_all', precedence=1 WHERE name='Manage CFAs' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='CFA');end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='CFA'), 'Manage CFAs_department', 0, 'Manage & Assess', 'Allows privileged users to edit CFA columns with departments they have Coordinator rights.', 'cfa_manage.php, cfa_manage_edit.php', 'cfa_manage.php', 'N', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N') ;end
" ;

//v1.3.00
$count++ ;
$sql[$count][0]="1.3.00" ;
$sql[$count][1]="
ALTER TABLE `cfaColumn` ADD `gibbonPlannerEntryID` INT(14) UNSIGNED ZEROFILL NULL DEFAULT NULL AFTER `uploadedResponse`;end
" ;

//v1.4.00
$count++ ;
$sql[$count][0]="1.4.00" ;
$sql[$count][1]="" ;

//v1.4.01
$count++ ;
$sql[$count][0]="1.4.01" ;
$sql[$count][1]="" ;

//v1.4.02
$count++ ;
$sql[$count][0]="1.4.02" ;
$sql[$count][1]="" ;

//v1.4.03
$count++ ;
$sql[$count][0]="1.4.03" ;
$sql[$count][1]="" ;


//v1.4.04
$count++ ;
$sql[$count][0]="1.4.04" ;
$sql[$count][1]="" ;

//v1.4.05
$count++ ;
$sql[$count][0]="1.4.05" ;
$sql[$count][1]="" ;

//v1.4.06
$count++ ;
$sql[$count][0]="1.4.06" ;
$sql[$count][1]="" ;

?>