<?php

if (preg_match("/dbcreate_module.php/i", $_SERVER['PHP_SELF'])) { 
    header('HTTP/1.0 403 Forbidden');
    die();
}

$logyear = strftime("%Y", time());
$logday = strftime("%d", time());
$logmonth = strftime("%m", time());
$logtime = time();

$membermsg = "";
$databasename = "game_$options[id]";
$urow =  $db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_databases WHERE serverip='$options[serverip]' AND name='$databasename'"));
if ($urow == 0) {
	$databaseuser = mt_rand(999,99999);
	$databaseuser = "".$memstats[member]."_".$databaseuser."";
	$statuscode = tekwebexec($rsstats[sshdaemon], $rsstats[daemonpasswd], $rsstats[sshport], $rsstats[sshuser], $rsstats[path], $options[serverip], 'dbcreate', $memstats[member], $databasename, $databaseuser, $memstats[ftppasswd], '', '', '', '', '');
	if ($statuscode == "error_00001") {
		$membermsgtwo = member_errorback(_SSHSERVEROFFLINE);
	}
	if ($statuscode == "error_00002") {
		$membermsgtwo = member_errorback(_SSHKEYERROR);
	}
	if ($statuscode == "error_00003") {
		$membermsgtwo = member_errorback(_SSHBADLOGIN);
	}
	if ($statuscode == "error_00004") {
		$membermsgtwo = member_errorback(_SSHDAEMONBADLOGIN);
	}
	if ($statuscode == "error_00007" OR $statuscode == "error_00008") {
		$membermsgtwo = member_errorback(_DBCREATEINSTALLERROR);
	}
	if ($statuscode == "error_00009") {
		$membermsgtwo = member_errorback(_SSHUSERERROR);
	}
	if ($statuscode == "ok_00001") {
		$result = $db->sql_query("INSERT INTO ".$prefix."_databases (id, memberid, productid, serverip, name, login, rserverid) VALUES (NULL, '$memstats[id]', '$options[productid]', '$options[serverip]', '$databasename', '$databaseuser', '$rsstats[id]')");
		Header("Location: members.php?op=Database");
		die();
	}else{
		include ("members/header.php");
		$membertitle = _GSERVER.'&nbsp;-&nbsp;'.$options[serverip].':'.$options[serverport].'';
		$membermsg = member_title("gserver", $membertitle, _ASSISTENTGSERVER, "");
		echo $membermsg.$membermsgtwo;
		include ("members/footer.php");				  
	}
}else{
	Header("Location: members.php?op=Database");
	die();	
}

?>
