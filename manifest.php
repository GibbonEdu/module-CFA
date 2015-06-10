<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basica variables
$name="Credentials" ;
$description="The CFA module allows schools to run a program of Common Formative Assessments." ;
$entryURL="cfa_view.php" ;
$type="Additional" ;
$category="Assess" ;
$version="1.0.00" ;
$author="Ross Parker" ;
$url="http://rossparker.org" ;

//Module tables
$moduleTables[0]="CREATE TABLE `cfaColumn` (
  `cfaColumnID` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `gibbonCourseClassID` int(8) unsigned zerofill NOT NULL,
  `groupingID` int(8) unsigned zerofill DEFAULT NULL COMMENT 'A value used to group multiple markbook columns.',
  `name` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `attainment` enum('Y','N') NOT NULL DEFAULT 'Y',
  `gibbonScaleIDAttainment` int(5) unsigned zerofill DEFAULT NULL,
  `effort` enum('Y','N') NOT NULL DEFAULT 'Y',
  `gibbonScaleIDEffort` int(5) unsigned zerofill DEFAULT NULL,
  `gibbonRubricIDAttainment` int(8) unsigned zerofill DEFAULT NULL,
  `gibbonRubricIDEffort` int(8) unsigned zerofill DEFAULT NULL,
  `comment` enum('Y','N') NOT NULL DEFAULT 'Y',
  `complete` enum('N','Y') NOT NULL,
  `completeDate` date DEFAULT NULL,
  `gibbonPersonIDCreator` int(10) unsigned zerofill NOT NULL,
  `gibbonPersonIDLastEdit` int(10) unsigned zerofill NOT NULL,
   PRIMARY KEY (`cfaColumnID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" ;

$moduleTables[1]="CREATE TABLE `cfaEntry` (
  `cfaEntryID` int(12) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `cfaColumnID` int(10) unsigned zerofill NOT NULL,
  `gibbonPersonIDStudent` int(10) unsigned zerofill NOT NULL,
  `attainmentValue` varchar(10) DEFAULT NULL,
  `attainmentDescriptor` varchar(100) DEFAULT NULL,
  `attainmentConcern` enum('N','Y','P') DEFAULT NULL COMMENT '''P'' denotes that student has exceed their personal target',
  `effortValue` varchar(10) DEFAULT NULL,
  `effortDescriptor` varchar(100) DEFAULT NULL,
  `effortConcern` enum('N','Y') DEFAULT NULL,
  `comment` text,
  `gibbonPersonIDLastEdit` int(10) unsigned zerofill NOT NULL,
   PRIMARY KEY (`cfaEntryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;" ;


//Action rows
$actionRows[0]["name"]="Manage CFAs" ;
$actionRows[0]["precedence"]="0";
$actionRows[0]["category"]="Manage" ;
$actionRows[0]["description"]="Allows privileged users to create and manage CFA columns." ;
$actionRows[0]["URLList"]="cfa_manage.php, cfa_manage_add.php, cfa_manage_edit.php, cfa_manage_delete.php, cfa_manage_data.php" ;
$actionRows[0]["entryURL"]="cfa_manage.php" ;
$actionRows[0]["defaultPermissionAdmin"]="Y" ;
$actionRows[0]["defaultPermissionTeacher"]="N" ;
$actionRows[0]["defaultPermissionStudent"]="N" ;
$actionRows[0]["defaultPermissionParent"]="N" ;
$actionRows[0]["defaultPermissionSupport"]="N" ;
$actionRows[0]["categoryPermissionStaff"]="Y" ;
$actionRows[0]["categoryPermissionStudent"]="N" ;
$actionRows[0]["categoryPermissionParent"]="N" ;
$actionRows[0]["categoryPermissionOther"]="N" ;

?>