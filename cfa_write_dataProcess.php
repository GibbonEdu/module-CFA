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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonCourseClassID=$_GET["gibbonCourseClassID"] ;
$cfaColumnID=$_GET["cfaColumnID"] ;
$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["address"]) . "/cfa_write_data.php&cfaColumnID=$cfaColumnID&gibbonCourseClassID=$gibbonCourseClassID" ;

if (isActionAccessible($guid, $connection2, "/modules/CFA/cfa_write_data.php")==FALSE) {
	//Fail 0
	$URL.="&updateReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	if (empty($_POST)) {
		$URL.="&updateReturn=fail5" ;
		header("Location: {$URL}");
	}
	else {
		//Proceed!
		//Check if school year specified
		if ($cfaColumnID=="" OR $gibbonCourseClassID=="") {
			//Fail1
			$URL.="&updateReturn=fail1" ;
			header("Location: {$URL}");
		}
		else {
			try {
				$data=array("cfaColumnID"=>$cfaColumnID, "gibbonCourseClassID"=>$gibbonCourseClassID); 
				$sql="SELECT * FROM cfaColumn WHERE cfaColumnID=:cfaColumnID AND gibbonCourseClassID=:gibbonCourseClassID" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				//Fail2
				$URL.="&updateReturn=fail2" ;
				header("Location: {$URL}");
				break ;
			}
			
			if ($result->rowCount()!=1) {
				//Fail 2
				$URL.="&updateReturn=fail2" ;
				header("Location: {$URL}");
			}
			else {
				$row=$result->fetch() ;
				$attachmentCurrent=$row["attachment"] ;
				$name=$row["name" ] ; 
				$count=$_POST["count"] ;
				$partialFail=FALSE ;
				$attainment=$row["attainment"] ;
				$gibbonScaleIDAttainment=$row["gibbonScaleIDAttainment"] ;
				$effort=$row["effort"] ;
				$gibbonScaleIDEffort=$row["gibbonScaleIDEffort"] ;
				$comment=$row["comment"] ;
				
				for ($i=1;$i<=$count;$i++) {
					$gibbonPersonIDStudent=$_POST["$i-gibbonPersonID"] ;
					//Attainment
					if ($attainment=="N" OR $gibbonScaleIDAttainment=="") {
						$attainmentValue=NULL ;
						$attainmentDescriptor=NULL ;
						$attainmentConcern=NULL ;
					}
					else {
						$attainmentValue=$_POST["$i-attainmentValue"] ;
					}
					//Effort
					if ($effort=="N" OR $gibbonScaleIDEffort=="") {
						$effortValue=NULL ;
						$effortDescriptor=NULL ;
						$effortConcern=NULL ;
					}
					else {
						$effortValue=$_POST["$i-effortValue"] ;
					}
					//Comment
					if ($comment!="Y") {
						$commentValue=NULL ;
					}
					else {
						$commentValue=$_POST["comment$i"] ;
					}
					$gibbonPersonIDLastEdit=$_SESSION[$guid]["gibbonPersonID"] ;
					$wordpressCommentPushID=NULL ;
					$wordpressCommentPushAction=NULL ;
					if (isset($_POST["$i-wordpressCommentPush"])) {
						$wordpressCommentPushID=substr($_POST["$i-wordpressCommentPush"], 0, strpos($_POST["$i-wordpressCommentPush"],"-")) ;
						$wordpressCommentPushAction=substr($_POST["$i-wordpressCommentPush"], (strpos($_POST["$i-wordpressCommentPush"],"-")+1)) ;
					}
					
					
					//SET AND CALCULATE FOR ATTAINMENT
					if ($attainment=="Y" AND $gibbonScaleIDAttainment!="") {
						//Without personal warnings
						$attainmentConcern="N" ;
						$attainmentDescriptor="" ;
						if ($attainmentValue!="") {
							$lowestAcceptableAttainment=$_POST["lowestAcceptableAttainment"] ;
							$scaleAttainment=$_POST["scaleAttainment"] ;
							try {
								$dataScale=array("attainmentValue"=>$attainmentValue, "scaleAttainment"=>$scaleAttainment); 
								$sqlScale="SELECT * FROM gibbonScaleGrade JOIN gibbonScale ON (gibbonScaleGrade.gibbonScaleID=gibbonScale.gibbonScaleID) WHERE value=:attainmentValue AND gibbonScaleGrade.gibbonScaleID=:scaleAttainment" ;
								$resultScale=$connection2->prepare($sqlScale);
								$resultScale->execute($dataScale);
							}
							catch(PDOException $e) { 
								$partialFail=TRUE ;
							}
							if ($resultScale->rowCount()!=1) {
								$partialFail=TRUE ;
							}
							else {
								$rowScale=$resultScale->fetch() ;
								$sequence=$rowScale["sequenceNumber"] ;
								$attainmentDescriptor=$rowScale["descriptor"] ;
							}
					
							if ($lowestAcceptableAttainment!="" AND $sequence!="" AND $attainmentValue!="") {
								if ($sequence>$lowestAcceptableAttainment) {
									$attainmentConcern="Y" ;
								}
							}
						}
					}
					
					//SET AND CALCULATE FOR EFFORT
					if ($effort=="Y" AND $gibbonScaleIDEffort!="") {
						$effortConcern="N" ;
						$effortDescriptor="" ;
						if ($effortValue!="") {
							$lowestAcceptableEffort=$_POST["lowestAcceptableEffort"] ;
							$scaleEffort=$_POST["scaleEffort"] ;
							try {
								$dataScale=array("effortValue"=>$effortValue, "scaleEffort"=>$scaleEffort); 
								$sqlScale="SELECT * FROM gibbonScaleGrade JOIN gibbonScale ON (gibbonScaleGrade.gibbonScaleID=gibbonScale.gibbonScaleID) WHERE value=:effortValue AND gibbonScaleGrade.gibbonScaleID=:scaleEffort" ;
								$resultScale=$connection2->prepare($sqlScale);
								$resultScale->execute($dataScale);
							}
							catch(PDOException $e) { 
								$partialFail=TRUE ;
							}
							if ($resultScale->rowCount()!=1) {
								$partialFail=TRUE ;
							}
							else {
								$rowScale=$resultScale->fetch() ;
								$sequence=$rowScale["sequenceNumber"] ;
								$effortDescriptor=$rowScale["descriptor"] ;
							}
						
							if ($lowestAcceptableEffort!="" AND $sequence!="" AND $effortValue!="") {
								if ($sequence>$lowestAcceptableEffort) {
									$effortConcern="Y" ;
								}
							}
						}
					}
					
					$time=time() ;
					
					$selectFail=false ;
					try {
						$data=array("cfaColumnID"=>$cfaColumnID, "gibbonPersonIDStudent"=>$gibbonPersonIDStudent); 
						$sql="SELECT * FROM cfaEntry WHERE cfaColumnID=:cfaColumnID AND gibbonPersonIDStudent=:gibbonPersonIDStudent" ;
						$result=$connection2->prepare($sql);
						$result->execute($data);
					}
					catch(PDOException $e) { 
						$partialFail=TRUE ;
						$selectFail=true ;
					}
					if (!($selectFail)) {
						if ($result->rowCount()<1) {
							try {
								$data=array("cfaColumnID"=>$cfaColumnID, "gibbonPersonIDStudent"=>$gibbonPersonIDStudent, "attainmentValue"=>$attainmentValue, "attainmentDescriptor"=>$attainmentDescriptor, "attainmentConcern"=>$attainmentConcern, "effortValue"=>$effortValue, "effortDescriptor"=>$effortDescriptor, "effortConcern"=>$effortConcern, "comment"=>$commentValue, "gibbonPersonIDLastEdit"=>$gibbonPersonIDLastEdit); 
								$sql="INSERT INTO cfaEntry SET cfaColumnID=:cfaColumnID, gibbonPersonIDStudent=:gibbonPersonIDStudent, attainmentValue=:attainmentValue, attainmentDescriptor=:attainmentDescriptor, attainmentConcern=:attainmentConcern, effortValue=:effortValue, effortDescriptor=:effortDescriptor, effortConcern=:effortConcern, comment=:comment, gibbonPersonIDLastEdit=:gibbonPersonIDLastEdit" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) { 
								$partialFail=TRUE ;
							}
						}
						else {
							$row=$result->fetch() ;
							//Update
							try {
								$data=array("cfaColumnID"=>$cfaColumnID, "gibbonPersonIDStudent"=>$gibbonPersonIDStudent, "attainmentValue"=>$attainmentValue, "attainmentDescriptor"=>$attainmentDescriptor, "attainmentConcern"=>$attainmentConcern, "effortValue"=>$effortValue, "effortDescriptor"=>$effortDescriptor, "effortConcern"=>$effortConcern, "comment"=>$commentValue, "gibbonPersonIDLastEdit"=>$gibbonPersonIDLastEdit, "cfaEntryID"=>$row["cfaEntryID"]); 
								$sql="UPDATE cfaEntry SET cfaColumnID=:cfaColumnID, gibbonPersonIDStudent=:gibbonPersonIDStudent, attainmentValue=:attainmentValue, attainmentDescriptor=:attainmentDescriptor, attainmentConcern=:attainmentConcern, effortValue=:effortValue, effortDescriptor=:effortDescriptor, effortConcern=:effortConcern, comment=:comment, gibbonPersonIDLastEdit=:gibbonPersonIDLastEdit WHERE cfaEntryID=:cfaEntryID" ;
								$result=$connection2->prepare($sql);
								$result->execute($data);
							}
							catch(PDOException $e) { 
								$partialFail=TRUE ;
							}
						}
					}
					
					//Attempt WordPress Comment Push
					if ($wordpressCommentPushAction!="" AND $wordpressCommentPushID!="") {
						$data="comment_post_ID=" . urlencode($wordpressCommentPushID) . "&author=" . urlencode(formatName($_SESSION[$guid]["title"], $_SESSION[$guid]["preferredName"], $_SESSION[$guid]["surname"], "Staff")) . "&email=" . urlencode($_SESSION[$guid]["email"]) . "&url=" . urlencode($_SESSION[$guid]["website"]) . "&comment=" . urlencode($commentValue) ;
						$params=array('http'=> array('method'=> 'POST','content'=> $data));
						$ctx=stream_context_create($params);
						$fp=@fopen($wordpressCommentPushAction, 'rb', false, $ctx);
						if (!$fp) {
							$partialFail=TRUE ;
						}
						if (@stream_get_contents($fp)===false) {
							$partialFail=TRUE ;
						}
					}
				}
				
				//Update column
				$description=$_POST["description"] ;
				$time=time() ;
				//Move attached file, if there is one
				if ($_FILES['file']["tmp_name"]!="") {
					//Check for folder in uploads based on today's date
					$path=$_SESSION[$guid]["absolutePath"] ;
					if (is_dir($path ."/uploads/" . date("Y", $time) . "/" . date("m", $time))==FALSE) {
						mkdir($path ."/uploads/" . date("Y", $time) . "/" . date("m", $time), 0777, TRUE) ;
					}
					$unique=FALSE;
					$count=0 ;
					while ($unique==FALSE AND $count<100) {
						$suffix=randomPassword(16) ;
						$attachment="uploads/" . date("Y", $time) . "/" . date("m", $time) . "/" . preg_replace("/[^a-zA-Z0-9]/", "", $name) . "_$suffix" . strrchr($_FILES["file"]["name"], ".") ;
						if (!(file_exists($path . "/" . $attachment))) {
							$unique=TRUE ;
						}
						$count++ ;
					}
				
					if (!(move_uploaded_file($_FILES["file"]["tmp_name"],$path . "/" . $attachment))) {
						//Fail 5
						$URL.="&updateReturn=fail5" ;
						header("Location: {$URL}");
					}
				}
				else {
					$attachment=$attachmentCurrent ;
				}
				$completeDate=$_POST["completeDate"] ;
				if ($completeDate=="") {
					$completeDate=NULL ;
					$complete="N" ;
				}
				else {
					$completeDate=dateConvert($guid, $completeDate) ;
					$complete="Y" ;
				}
				try {
					$data=array("attachment"=>$attachment, "description"=>$description, "completeDate"=>$completeDate, "complete"=>$complete, "cfaColumnID"=>$cfaColumnID); 
					$sql="UPDATE cfaColumn SET attachment=:attachment, description=:description, completeDate=:completeDate, complete=:complete WHERE cfaColumnID=:cfaColumnID" ;
					$result=$connection2->prepare($sql);
					$result->execute($data);
				}
				catch(PDOException $e) { 
					$partialFail=TRUE ;
				}
				
				//Return!
				if ($partialFail==TRUE) {
					//Fail 3
					$URL.="&updateReturn=fail3" ;
					header("Location: {$URL}");
				}
				else {
					//Success 0
					$URL.="&updateReturn=success0" ;
					header("Location: {$URL}");
				}
			}
		}
	}
}
?>