################################################# EXPRESS INSTALLATION #################################################
* Copy files from ~CUSTOMERPORTAL source folder to the CUSTOMERPORTAL root folder and files from ~VTIGER source folder to the VTIGER root folder.
* Rewrite if necessary.
************************************************************************************************************************************


################################################# CUSTOM INSTALLATION #################################################
* FILE ~CUSTOMERPORTAL_ROOT/index.php
* AROUND LINE 96 REPLACE

$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
$fileContent = $client->call('get_pdf', $params, $Server_Path, $Server_Path);
$fileType ='application/pdf';
$fileContent = $fileContent[0];
$filesize = strlen(base64_decode($fileContent));
$filename = "$block.pdf";

* WITH FOLLOWING:

$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid", 'language'=>$_SESSION["portal_login_language"]);
$fileContent = $client->call('get_pdfmaker_pdf', $params, $Server_Path, $Server_Path);
//if something went wrong within the get_pdf_maker function then call the standard function get_pdf   
if($fileContent[0] != "failure")
{
    $fileType ='application/pdf';
    $filename = $fileContent[0];
    $fileContent = $fileContent[1];
    $filesize = strlen(base64_decode($fileContent));
}
else
{
    $params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
    $fileContent = $client->call('get_pdf', $params, $Server_Path, $Server_Path);
    $fileType ='application/pdf';
    $fileContent = $fileContent[0];
    $filesize = strlen(base64_decode($fileContent));
    $filename = "$block.pdf";
}

************************************************************************************************************************************
* FILE soap/customerportal.php
* AROUND LINE 274 AFTER

$server->register(
	'get_pdf',
	array('id'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);

* ADD FOLLOWING:

$server->register(
	'get_pdfmaker_pdf',
	array('id'=>'xsd:string','block'=>'xsd:string','contactid'=>'xsd:string','sessionid'=>'xsd:string','language'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);
	
* AROUND LINE 1980 BEFORE

function get_salesorder_name($id)

* ADD FOLLOWING:

function get_pdfmaker_pdf($id,$block,$customerid,$sessionid,$language)
{ 
    if(!file_exists("modules/PDFMaker/models/checkGenerate.php"))
        return array("failure");

    global $adb;
    global $vtiger_current_version, $site_URL;
    global $current_user,$log,$default_language;
    global $currentModule,$mod_strings,$app_strings,$app_list_strings;
    $log->debug("Entering customer portal function get_pdfmaker_pdf");
    $isPermitted = check_permission($customerid,$block,$id);
    if($isPermitted == false) {
    	return array("failure");
    }

	if(!validateSession($customerid,$sessionid))
        return array("failure");

	require_once("config.inc.php");
	$current_user = Users::getActiveAdminUser();
	
	$currentModule = $block;
	$current_language = $default_language;
	$app_strings = return_application_language($current_language);
	$app_list_strings = return_app_list_strings_language($current_language);
	$mod_strings = return_module_language($current_language, $currentModule);
    
    $sql = "SELECT a.templateid 
            FROM vtiger_pdfmaker AS a
            INNER JOIN vtiger_pdfmaker_settings AS b USING(templateid)
            WHERE a.module=? AND is_portal='1'";
    $params = array($currentModule);    
    $result = $adb->pquery($sql, $params);
    $templateid = $adb->query_result($result,0,"templateid");
    if($templateid == "")
        return array("failure");         
    
    $_REQUEST['relmodule']= $block;
	$_REQUEST['record']= $id;
	$_REQUEST['commontemplateid']= $templateid;
	$_REQUEST['is_portal']= 'true';
	
    if(file_exists("languages/".$language."/".$block.".php"))
        $_REQUEST['language'] = $language;
    else
        $_REQUEST['language'] = "en_us";
    
    $request = new Vtiger_Request($_REQUEST, $_REQUEST);
    
    include_once("modules/PDFMaker/models/checkGenerate.php");
    $checkGenerate = new PDFMaker_checkGenerate_Model();
    $Data = $checkGenerate->generate($request);
   
    if (isset($Data["content"]) && $Data["content"] != "") 
    {
        $filecontents[] = $Data["filename"];
        $filecontents[] = base64_encode($Data["content"]);
    }
    else
    {
        return array("failure");
    }
    
    $log->debug("Exiting customer portal function get_pdfmaker_pdf");
    return $filecontents; 
}