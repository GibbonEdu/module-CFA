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

function getCFARecord($guid, $connection2, $gibbonPersonID) {
	$output="" ;
	
	//Get alternative header names
	$attainmentAlternativeName=getSettingByScope($connection2, "Markbook", "attainmentAlternativeName") ;
	$attainmentAlternativeNameAbrev=getSettingByScope($connection2, "Markbook", "attainmentAlternativeNameAbrev") ;
	$effortAlternativeName=getSettingByScope($connection2, "Markbook", "effortAlternativeName") ;
	$effortAlternativeNameAbrev=getSettingByScope($connection2, "Markbook", "effortAlternativeNameAbrev") ;
	$showParentAttainmentWarning=getSettingByScope($connection2, "Markbook", "showParentAttainmentWarning" ) ; 
	$showParentEffortWarning=getSettingByScope($connection2, "Markbook", "showParentEffortWarning" ) ; 
	$alert=getAlert($connection2, 002) ;	
		
	//Get school years in reverse order
	try {
		$dataYears=array("gibbonPersonID"=>$gibbonPersonID); 
		$sqlYears="SELECT * FROM gibbonSchoolYear JOIN gibbonStudentEnrolment ON (gibbonStudentEnrolment.gibbonSchoolYearID=gibbonSchoolYear.gibbonSchoolYearID) WHERE (status='Current' OR status='Past') AND gibbonPersonID=:gibbonPersonID ORDER BY sequenceNumber DESC" ;
		$resultYears=$connection2->prepare($sqlYears);
		$resultYears->execute($dataYears);
	}
	catch(PDOException $e) { 
		$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
	}
	
	if ($resultYears->rowCount()<1) {
		$output.="<div class='error'>" ;
			$output.=_("There are no records to display.") ;
		$output.="</div>" ;
	}
	else {
		$results=FALSE ;
		while ($rowYears=$resultYears->fetch()) {
			//Get and output CFAs
			try {
				$dataCFA=array("gibbonPersonID1"=>$gibbonPersonID, "gibbonPersonID2"=>$gibbonPersonID, "gibbonSchoolYearID"=>$rowYears["gibbonSchoolYearID"]); 
				$sqlCFA="SELECT cfaColumn.*, cfaEntry.*, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class, gibbonPerson.dateStart FROM gibbonCourse JOIN gibbonCourseClass ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID) JOIN gibbonCourseClassPerson ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN cfaColumn ON (cfaColumn.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN cfaEntry ON (cfaEntry.cfaColumnID=cfaColumn.cfaColumnID) JOIN gibbonPerson ON (cfaEntry.gibbonPersonIDStudent=gibbonPerson.gibbonPersonID) WHERE gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID1 AND cfaEntry.gibbonPersonIDStudent=:gibbonPersonID2 AND gibbonSchoolYearID=:gibbonSchoolYearID AND completeDate<='" . date("Y-m-d") . "' ORDER BY completeDate DESC, gibbonCourse.nameShort, gibbonCourseClass.nameShort" ;
				$resultCFA=$connection2->prepare($sqlCFA);
				$resultCFA->execute($dataCFA);
			}
			catch(PDOException $e) { 
				$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
			}
			
			if ($resultCFA->rowCount()>0) {
				$results=TRUE ;
				$output.="<h4>" ;
					$output.=$rowYears["name"] ;
				$output.="</h4>" ;
				$output.="<table cellspacing='0' style='width: 100%'>" ;
					$output.="<tr class='head'>" ;
						$output.="<th style='width: 120px'>" ;
							$output.="Assessment" ;
						$output.="</th>" ;
						$output.="<th style='width: 75px; text-align: center'>" ;
							if ($attainmentAlternativeName!="") { $output.=$attainmentAlternativeName ; } else { $output.=_('Attainment') ; }
						$output.="</th>" ;
						$output.="<th style='width: 75px; text-align: center'>" ;
							if ($effortAlternativeName!="") { $output.=$effortAlternativeName ; } else { $output.=_('Effort') ; }
						$output.="</th>" ;
						$output.="<th>" ;
							$output.="Comment" ;
						$output.="</th>" ;
						$output.="<th style='width: 75px'>" ;
							$output.=_("Submission") ;
						$output.="</th>" ;
						
					$output.="</tr>" ;
			
					$count=0 ;
					while ($rowCFA=$resultCFA->fetch()) {
						if ($count%2==0) {
							$rowNum="even" ;
						}
						else {
							$rowNum="odd" ;
						}
						$count++ ;
						
						$output.="<tr class=$rowNum>" ;
							$output.="<td>" ;
								$output.="<span title='" . htmlPrep($rowCFA["description"]) . "'><b><u>" . $rowCFA["course"] . "." . $rowCFA["class"] . " " . $rowCFA["name"] . "</u></b></span><br/>" ;
								$output.="<span style='font-size: 90%; font-style: italic; font-weight: normal'>" ;
								if ($rowCFA["completeDate"]!="") {
									$output.="Marked on " . dateConvertBack($guid, $rowCFA["completeDate"]) . "<br/>" ;
								}
								else {
									$output.="Unmarked<br/>" ;
								}
								if ($rowCFA["attachment"]!="" AND file_exists($_SESSION[$guid]["absolutePath"] . "/" . $rowCFA["attachment"])) {
									$output.=" | <a 'title='Download more information' href='" . $_SESSION[$guid]["absoluteURL"] . "/" . $rowCFA["attachment"] . "'>More info</a>"; 
								}
								$output.="</span><br/>" ;
							$output.="</td>" ;
							if ($rowCFA["attainment"]=="N" OR ($rowCFA["gibbonScaleIDAttainment"]=="" AND $rowCFA["gibbonRubricIDAttainment"]=="")) {
								$output.="<td class='dull' style='color: #bbb; text-align: center'>" ;
									$output.=_('N/A') ;
								$output.="</td>" ;
							}
							else {
								$output.="<td style='text-align: center'>" ;
									$attainmentExtra="" ;
									try {
										$dataAttainment=array("gibbonScaleID"=>$rowCFA["gibbonScaleIDAttainment"]); 
										$sqlAttainment="SELECT * FROM gibbonScale WHERE gibbonScaleID=:gibbonScaleID" ;
										$resultAttainment=$connection2->prepare($sqlAttainment);
										$resultAttainment->execute($dataAttainment);
									}
									catch(PDOException $e) { 
										$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
									}
									if ($resultAttainment->rowCount()==1) {
										$rowAttainment=$resultAttainment->fetch() ;
										$attainmentExtra="<br/>" . _($rowAttainment["usage"]) ;
									}
									$styleAttainment="style='font-weight: bold'" ;
									if ($rowCFA["attainmentConcern"]=="Y" AND $showParentAttainmentWarning=="Y") {
										$styleAttainment="style='color: #" . $alert["color"] . "; font-weight: bold; border: 2px solid #" . $alert["color"] . "; padding: 2px 4px; background-color: #" . $alert["colorBG"] . "'" ;
									}
									else if ($rowCFA["attainmentConcern"]=="P" AND $showParentAttainmentWarning=="Y") {
										$styleAttainment="style='color: #390; font-weight: bold; border: 2px solid #390; padding: 2px 4px; background-color: #D4F6DC'" ;
									}
									$output.="<div $styleAttainment>" . $rowCFA["attainmentValue"] ;
										if ($rowCFA["gibbonRubricIDAttainment"]!="") {
											$output.="<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/CFA/cfa_view_rubric.php&gibbonRubricID=" . $rowCFA["gibbonRubricIDAttainment"] . "&gibbonCourseClassID=" . $rowCFA["gibbonCourseClassID"] . "&cfaColumnID=" . $rowCFA["cfaColumnID"] . "&gibbonPersonID=$gibbonPersonID&mark=FALSE&type=attainment&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/rubric.png'/></a>" ;
										}
									$output.="</div>" ;
									if ($rowCFA["attainmentValue"]!="") {
										$output.="<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'><b>" . htmlPrep(_($rowCFA["attainmentDescriptor"])) . "</b>" . _($attainmentExtra) . "</div>" ;
									}
								$output.="</td>" ;
							}
							if ($rowCFA["effort"]=="N" OR ($rowCFA["gibbonScaleIDEffort"]=="" AND $rowCFA["gibbonRubricIDEffort"]=="")) {
								$output.="<td class='dull' style='color: #bbb; text-align: center'>" ;
									$output.=_('N/A') ;
								$output.="</td>" ;
							}
							else {
								$output.="<td style='text-align: center'>" ;
									$effortExtra="" ;
									try {
										$dataEffort=array("gibbonScaleID"=>$rowCFA["gibbonScaleIDEffort"]); 
										$sqlEffort="SELECT * FROM gibbonScale WHERE gibbonScaleID=:gibbonScaleID" ;
										$resultEffort=$connection2->prepare($sqlEffort);
										$resultEffort->execute($dataEffort);
									}
									catch(PDOException $e) { 
										$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
									}
									if ($resultEffort->rowCount()==1) {
										$rowEffort=$resultEffort->fetch() ;
										$effortExtra="<br/>" . _($rowEffort["usage"]) ;
									}
									$styleEffort="style='font-weight: bold'" ;
									if ($rowCFA["effortConcern"]=="Y" AND $showParentEffortWarning=="Y") {
										$styleEffort="style='color: #" . $alert["color"] . "; font-weight: bold; border: 2px solid #" . $alert["color"] . "; padding: 2px 4px; background-color: #" . $alert["colorBG"] . "'" ;
									}
									$output.="<div $styleEffort>" . $rowCFA["effortValue"] ;
										if ($rowCFA["gibbonRubricIDEffort"]!="") {
											$output.="<a class='thickbox' href='" . $_SESSION[$guid]["absoluteURL"] . "/fullscreen.php?q=/modules/CFA/cfa_view_rubric.php&gibbonRubricID=" . $rowCFA["gibbonRubricIDEffort"] . "&gibbonCourseClassID=" . $rowCFA["gibbonCourseClassID"] . "&cfaColumnID=" . $rowCFA["cfaColumnID"] . "&gibbonPersonID=$gibbonPersonID&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='View Rubric' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/rubric.png'/></a>" ;
										}
									$output.="</div>" ;
									if ($rowCFA["effortValue"]!="") {
										$output.="<div class='detailItem' style='font-size: 75%; font-style: italic; margin-top: 2px'>" ;
											$output.="<b>" . htmlPrep(_($rowCFA["effortDescriptor"])) . "</b>" ;
											if ($effortExtra!="") {
												$output.=_($effortExtra) ;
											}
										$output.="</div>" ;
									}
								$output.="</td>" ;
							}
							
							if ($rowCFA["comment"]=="N" AND $rowCFA["uploadedResponse"]=="N") {
								$output.="<td class='dull' style='color: #bbb; text-align: left'>" ;
									$output.=_('N/A') ;
								$output.="</td>" ;
							}
							else {
								$output.="<td>" ;
									if ($rowCFA["comment"]!="") {
										$output.=$rowCFA["comment"] . "<br/>" ;
									}
									if ($rowCFA["response"]!="") {
										$output.="<a title='" . _('Uploaded Response') . "' href='" . $_SESSION[$guid]["absoluteURL"] . "/" . $rowCFA["response"] . "'>" . _('Uploaded Response') . "</a><br/>" ;
									}
								$output.="</td>" ;
							}
							
							if ($rowCFA["gibbonPlannerEntryID"]==0) {
								$output.="<td class='dull' style='color: #bbb; text-align: left'>" ;
									$output.=_('N/A') ;
								$output.="</td>" ;
							}
							else {
								try {
									$dataSub=array("gibbonPlannerEntryID"=>$rowCFA["gibbonPlannerEntryID"]); 
									$sqlSub="SELECT * FROM gibbonPlannerEntry WHERE gibbonPlannerEntryID=:gibbonPlannerEntryID AND homeworkSubmission='Y'" ;
									$resultSub=$connection2->prepare($sqlSub);
									$resultSub->execute($dataSub);
								}
								catch(PDOException $e) { 
									$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
								}
								if ($resultSub->rowCount()!=1) {
									$output.="<td class='dull' style='color: #bbb; text-align: left'>" ;
										$output.=_('N/A') ;
									$output.="</td>" ;
								}
								else {
									$output.="<td>" ;
										$rowSub=$resultSub->fetch() ;
										
										try {
											$dataWork=array("gibbonPlannerEntryID"=>$rowCFA["gibbonPlannerEntryID"], "gibbonPersonID"=>$gibbonPersonID); 
											$sqlWork="SELECT * FROM gibbonPlannerEntryHomework WHERE gibbonPlannerEntryID=:gibbonPlannerEntryID AND gibbonPersonID=:gibbonPersonID ORDER BY count DESC" ;
											$resultWork=$connection2->prepare($sqlWork);
											$resultWork->execute($dataWork);
										}
										catch(PDOException $e) { 
											$output.="<div class='error'>" . $e->getMessage() . "</div>" ; 
										}
										if ($resultWork->rowCount()>0) {
											$rowWork=$resultWork->fetch() ;
											
											if ($rowWork["status"]=="Exemption") {
												$linkText=_("Exemption") ;
											}
											else if ($rowWork["version"]=="Final") {
												$linkText=_("Final") ;
											}
											else {
												$linkText=_("Draft") . " " . $rowWork["count"] ;
											}
											
											$style="" ;
											$status="On Time" ;
											if ($rowWork["status"]=="Exemption") {
												$status=_("Exemption") ;
											}
											else if ($rowWork["status"]=="Late") {
												$style="style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'" ;
												$status=_("Late") ;
											}
											
											if ($rowWork["type"]=="File") {
												$output.="<span title='" . $rowWork["version"] . ". $status. " . sprintf(_('Submitted at %1$s on %2$s'), substr($rowWork["timestamp"],11,5), dateConvertBack($guid, substr($rowWork["timestamp"],0,10))) . "' $style><a href='" . $_SESSION[$guid]["absoluteURL"] . "/" . $rowWork["location"] ."'>$linkText</a></span>" ;
											}
											else if ($rowWork["type"]=="Link") {
												$output.="<span title='" . $rowWork["version"] . ". $status. " . sprintf(_('Submitted at %1$s on %2$s'), substr($rowWork["timestamp"],11,5), dateConvertBack($guid, substr($rowWork["timestamp"],0,10))) . "' $style><a target='_blank' href='" . $rowWork["location"] ."'>$linkText</a></span>" ;
											}
											else {
												$output.="<span title='$status. " . sprintf(_('Recorded at %1$s on %2$s'), substr($rowWork["timestamp"],11,5), dateConvertBack($guid, substr($rowWork["timestamp"],0,10))) . "' $style>$linkText</span>" ;
											}
										}
										else {
											if (date("Y-m-d H:i:s")<$rowSub["homeworkDueDateTime"]) {
												$output.="<span title='Pending'>" . _('Pending') . "</span>" ;
											}
											else {
												if ($rowCFA["dateStart"]>$rowSub["date"]) {
													$output.="<span title='" . _('Student joined school after assessment was given.') . "' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>" . _('NA') . "</span>" ;
												}
												else {
													if ($rowSub["homeworkSubmissionRequired"]=="Compulsory") {
														$output.="<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>" . _('Incomplete') . "</div>" ;
													}
													else {
														$output.=_("Not submitted online") ;
													}
												}
											}
										}	
									$output.="</td>" ;
								}
							}
						$output.="</tr>" ;
					}
				
				$output.="</table>" ;
			}
		}
		if ($results==FALSE) {
			$output.="<div class='error'>" ;
				$output.=_("There are no records to display.") ;
			$output.="</div>" ;
		}
	}
	
	return $output ;
}

function sidebarExtra($guid, $connection2, $gibbonCourseClassID, $mode="manage", $highestAction="") {
	$output="" ;
	
	$output.="<h2>" ;
	$output.=_("View Classes") ;
	$output.="</h2>" ;
	
	$selectCount=0 ;
	$output.="<form method='get' action='" . $_SESSION[$guid]["absoluteURL"] . "/index.php'>" ;
		$output.="<table class='smallIntBorder' cellspacing='0' style='width: 100%; margin: 0px 0px'>" ;	
			$output.="<tr>" ;
				$output.="<td style='width: 190px'>" ; 
					if ($mode=="write") {
						$output.="<input name='q' id='q' type='hidden' value='/modules/CFA/cfa_write.php'>" ;
					}
					else { 
						$output.="<input name='q' id='q' type='hidden' value='/modules/CFA/cfa_manage.php'>" ;
					}
					$output.="<select name='gibbonCourseClassID' id='gibbonCourseClassID' style='width:161px'>" ;
						if ($mode=="write" OR ($mode=="manage" AND $highestAction=="Manage CFAs_all")) { //Full listing
							$output.="<option value=''></option>" ;
								try {
									$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"]); 
									$sqlSelect="SELECT gibbonCourseClass.gibbonCourseClassID, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class FROM gibbonCourseClassPerson JOIN gibbonCourseClass ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) JOIN gibbonCourse ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID) WHERE gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPersonID=:gibbonPersonID ORDER BY course, class" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { }
								$output.="<optgroup label='--" . _('My Classes') . "--'>" ;
								while ($rowSelect=$resultSelect->fetch()) {
									$selected="" ;
									if ($rowSelect["gibbonCourseClassID"]==$gibbonCourseClassID AND $selectCount==0) {
										$selected="selected" ;
										$selectCount++ ;
									}
									$output.="<option $selected value='" . $rowSelect["gibbonCourseClassID"] . "'>" . htmlPrep($rowSelect["course"]) . "." . htmlPrep($rowSelect["class"]) . "</option>" ;
								}
							$output.="</optgroup>" ;
						
							if ($mode=="manage") {
								try {
									$dataSelect=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"]); 
									$sqlSelect="SELECT gibbonCourseClass.gibbonCourseClassID, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class FROM gibbonCourseClass JOIN gibbonCourse ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID) WHERE gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY course, class" ;
									$resultSelect=$connection2->prepare($sqlSelect);
									$resultSelect->execute($dataSelect);
								}
								catch(PDOException $e) { }
								$output.="<optgroup label='--" . _('All Classes') . "--'>" ;
									while ($rowSelect=$resultSelect->fetch()) {
										$selected="" ;
										if ($rowSelect["gibbonCourseClassID"]==$gibbonCourseClassID AND $selectCount==0) {
											$selected="selected" ;
											$selectCount++ ;
										}
										$output.="<option $selected value='" . $rowSelect["gibbonCourseClassID"] . "'>" . htmlPrep($rowSelect["course"]) . "." . htmlPrep($rowSelect["class"]) . "</option>" ;
									}
								$output.="</optgroup>" ;
							}
						}
						else {
							try {
								$dataSelect=array("gibbonPersonID"=>$_SESSION[$guid]["gibbonPersonID"], "gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"]); 
								$sqlSelect="SELECT gibbonCourseClass.gibbonCourseClassID, gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class FROM gibbonCourseClass JOIN gibbonCourse ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID) JOIN gibbonDepartment ON (gibbonCourse.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE gibbonDepartmentStaff.gibbonPersonID=:gibbonPersonID AND gibbonDepartmentStaff.role='Coordinator' AND gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY course, class" ;
								$resultSelect=$connection2->prepare($sqlSelect);
								$resultSelect->execute($dataSelect);
							}
							catch(PDOException $e) { }
							$output.="<optgroup label='--" . _('Departmental Classes') . "--'>" ;
								while ($rowSelect=$resultSelect->fetch()) {
									$selected="" ;
									if ($gibbonCourseClassID!="") {
										if ($rowSelect["gibbonCourseClassID"]==$gibbonCourseClassID AND $selectCount==0) {
											$selected="selected" ;
											$selectCount++ ;
										}
									}
									$output.="<option $selected value='" . $rowSelect["gibbonCourseClassID"] . "'>" . htmlPrep($rowSelect["course"]) . "." . htmlPrep($rowSelect["class"]) . "</option>" ;
								}
							$output.="</optgroup>" ;
						}
					 $output.="</select>" ;
				$output.="</td>" ;
				$output.="<td class='right'>" ;
					$output.="<input type='submit' value='" . _('Go') . "'>" ;
				$output.="</td>" ;
			$output.="</tr>" ;
		$output.="</table>" ;
	$output.="</form>" ;
	
	return $output ;
}

?>
