<?php

if (preg_match("/admingroups.php/i", $_SERVER['PHP_SELF'])) { 
	header('HTTP/1.0 403 Forbidden');
	die();
}

$min_version = "8000"; // Define from which version the module is available
$tekbase = tekbase();
if ($tekbase < $version) { admin_fileerror("/mytekbase/admin/modules/test.php"); }
if (!is_admin($admin)) { admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED); }

if (file_exists("mytekbase/admin/languages/$admin[4]/test.php")) {
	include("mytekbase/admin/languages/$admin[4]/test.php");
}

$agstats = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_admin_access WHERE groupid='$admin[5]' AND module='admingroups'"));
if ($agstats AND $agstats[modview] != 1 AND $agstats[modnew] != 1 AND $agstats[modchange] != 1 AND $agstats[moddelete] == 1) {
	adminLogin($admin);
}

function adminTest($ids, $xxxx, $chkhash, $chkhkey, $save) {
	global $prefix, $db, $zahl, $torder, $tcounter, $admin, $wioption, $agstats, $panelactions;

	// filter(A, B, C, D, E);
	// A = Variable
	// B = "" or "nohtml"; nohtml = remove html code
	// C = Scan and replace variable; 1 = yes
	// D = Maximum character length
	// E = "" or "num"; num = only digits;
  
	$ids      = filter($ids, "", 1, 20, "num");
	$zahl     = filter($zahl, "", 1, 20, "num");
	$torder		= filter($torder, "", 1, 5);
	$tcounter	= filter($tcounter, "", 1, 5, "num");

	if ($save) {
		$sys_hash = md5("$admin[0]-$admin[1]-$chkhkey");
		if ($sys_hash != $chkhash OR $chkhkey != $admin[6]) {
			admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
		}

		$xxxx = filter($xxxx, "", 1, 30);

    	// Check if required fields are empty.
		if (!$xxxx) {
      		// administrator = Icon name (see admin panel -> widgets)
			admin_error(_TEST, _ASSISTENTTEST, "administrator", _NOTALLFIELDS, 1);
		}
	}

	if ($save == 1) {
		if ($agstats[modchange] == 1) {
			$logtitle = "LOGUP";
			$logtext = $xxxx;
			$urow = $db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_tablexyz WHERE xxxx='$xxxx' AND id!='$ids'"));
			if ($urow > 0) {
				admin_error(_TEST, _ASSISTENTTEST, "administrator", _DBNAMEEXIST, 1);
			}
      		$result = $db->sql_query("UPDATE ".$prefix."_tablexyz SET xxxx='$xxxx' WHERE id='$ids'");
      		if (!$result) {
        		$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBUPERROR);
        		admin_error(_TEST, _ASSISTENTTEST, "administrator", $lang_var, 1);
      		}else{
        		$dbcode = "ok_00001";
      		}
		}else{
			admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
		}
	}

	if ($save == 2) {
		if ($agstats[modnew] == 1) {
			$logtitle = "LOGNEW";
			$logtext = $title;
			$urow = $db->sql_numrows($db->sql_query("SELECT * FROM ".$prefix."_tablexyz WHERE xxxx='$xxxx'"));
			if ($urow > 0) {
				admin_error(_TEST, _ASSISTENTTEST, "administrator", _DBNAMEEXIST, 1);
			}
			$result = $db->sql_query("INSERT INTO ".$prefix."_admin_groups (id, title) VALUES (NULL, '$title')");
			if (!$result) {
				$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBSAVEERROR);
				admin_error(_TEST, _ASSISTENTTEST, "administrator", $lang_var, 1);
			}else{
				$dbcode = "ok_00002";
			}
		}else{
			admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
		}
	}

	if ($dbcode == "ok_00001" OR $dbcode == "ok_00002") {
		$panelactions->log_create($admin[1], 'TEST', $logtitle, $logtext, 2, 0);
	}

	include("admin/header.php");
	admin_title("administrator", _TEST, _ASSISTENTTEST, "");
	if ($dbcode == "ok_00001") {
		$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBUPDATED);
		admin_ok(_TEST, $lang_var, "administrator");	
	}
	if ($dbcode == "ok_00002") {
		$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBCREATED);
		admin_ok(_TEST, $lang_var, "administrator");	
	}

 	$total = $db->sql_fetchrow($db->sql_query("SELECT count(*) as total FROM ".$prefix."_tablexyz"));
	if ($zahl == "") {
		$zahl = 0;
	}

	$check_iconlist = 0;
	if ($agstats[modnew] == 1) {
		echo '<div>';
		$adminlink = 'admin.php?op=adminTestEdit&zahl='.$zahl.'&torder='.$torder.'&tcounter='.$tcounter;
		admin_icons("administrator", _TESTGNEW, $adminlink);
		$check_iconlist = 1;		
	}

  	// Links to other modules
	$adminicons = admin_moduleicons("admins", _ADMIN, "administrator", "adminAdmin", $check_iconlist);
	if ($adminicons != "") { $check_iconlist = 1; }

	if ($check_iconlist == 1) {
		echo '</div>
			  <div class="clear"></div>
			  <div class="smallline"></div>
			  <div class="dataspace"></div>';
	}

	if ($total[total] > 0) {
		if (!$tcounter) {
			$tcounter = $agstats[rowview];
		}

		$adminsites = admin_sites($sitetheme, "adminTest", $total[total], $torder, $tcounter, "");
		echo $adminsites;

		if ($torder == "") {
			$tableorder = "xxxx ASC";
		}elseif ($torder == "xu") {
			$tableorder = "xxxx ASC";
		}elseif ($torder == "xd") {
			$tableorder = "xxxx DESC";
		}

		echo '<form name="form_one" action="admin.php" method="post">
			  <ul class="datalist">
			  <li class="dataheader"><dl>
				<dd class="datahundred">&nbsp;
				  '._TABLETITLE.'
				  <a href="admin.php?op=adminTest&zahl='.$zahl.'&torder=xu&tcounter='.$tcounter.'"><i class="tekbase icon-up imgtitle" title="up"></i></a>
				  <a href="admin.php?op=adminTest&zahl='.$zahl.'&torder=xd&tcounter='.$tcounter.'"><i class="tekbase icon-down imgtitle" title="down"></i></a>
				</dd>
			  </dl></li>';

		$b = 0;
		$result = $db->sql_query("SELECT * FROM ".$prefix."_tablexyz ORDER BY ".$tableorder." LIMIT ".$zahl.", $tcounter");
		while($row = $db->sql_fetchrow($result)) {
			if ($b == 0) {
				echo '<li class="datarowa"><dl>';
			}else{
				echo '<li class="datarowb"><dl>';
			}
			echo '<dd class="datahundred">
					<div style="float:left;width:26px;">&nbsp;<input type="checkbox" name="boxid[]" value="'.$row[id].'"></div>
				    <div class="dataimg"><a href="admin.php?op=adminTestEdit&zahl='.$zahl.'&torder='.$torder.'&tcounter='.$tcounter.'&ids='.$row[id].'"><i class="tekbase icon-edit imgtitle btn" title="'._EDIT.'"></i></a></div>
				    <div class="datatxt">&nbsp;'.$row[xxxx].'</div>
				  </dd>
				  </dl>
				  </li>'; 

			if ($b == 0) {
				$b = 1;
			}else{
				$b = 0;
			}
		}

		if ($agstats[moddelete] == 1 OR $agstats[modnew] == 1) {
			$hkey = $admin[6];
			$hash = md5("$admin[0]-$admin[1]-$admin[6]");
			echo '<li class="datarowend"><dl>
					<dd>&nbsp;<input type="checkbox" id="allmsg" name="allmsg" value="">&nbsp;'._ALL.'</dd>
				  </dl>
				  </li>
				  <li class="datarowend"><dl>
					<dd>
					  &nbsp;<select name="setstatus" class="selectfield">
					  <option value="">'._PLEASESELECT.'</option>
					  <option value="">--</option>';

			if ($agstats[moddelete] == 1 ) {
				$lang_var = str_replace("%var%", _TESTPLURAL, _DELETESELECTED);
				echo '<option value="delete">'.$lang_var.'</option>';
			}
			
			echo '</select>
				  <input type="hidden" name="op" value="adminTestChange">
				  <input type="hidden" name="zahl" value="'.$zahl.'">
				  <input type="hidden" name="torder" value="'.$torder.'">
				  <input type="hidden" name="tcounter" value="'.$tcounter.'">
				  <input type="hidden" name="chkhkey" value="'.$hkey.'">
				  <input type="hidden" name="chkhash" value="'.$hash.'">
				  &nbsp;&nbsp;<a href="javascript:document.form_one.submit();" class="button_form" onclick="return confirm(\''._QUESTIONCHANGE.'\');">'._CHANGE.'</a>
				</dd>
			  </dl>
			  </li>';
		}

		echo '</ul>
			  </form>
			  <div class="clear"></div>
			  <div class="dataspace"></div>
			  '.$adminsites;
	}else{
		echo _NOENTRY;
	}
    include("admin/footer.php");
}


function adminTestChange($boxid, $setstatus, $chkhash, $chkhkey) {
	global $prefix, $db, $zahl, $torder, $tcounter, $admin, $agstats, $panelactions;

	$zahl		= filter($zahl, "", 1, 20, "num");
	$torder		= filter($torder, "", 1, 5);
	$tcounter	= filter($tcounter, "", 1, 5, "num");

	$sys_hash = md5("$admin[0]-$admin[1]-$chkhkey");
	if ($sys_hash != $chkhash OR $chkhkey != $admin[6]) {
		admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
	}
		
	if ($setstatus == "delete" AND $agstats[moddelete] == 1) {
		include("admin/header.php");
		$adminlink = 'admin.php?op=adminTest&zahl='.$zahl.'&torder='.$torder.'&tcounter='.$tcounter;
		admin_title("administrator", _TEST, _ASSISTENTTEST, "");
		admin_read(_CHANGESTATUS, $adminlink);
	
		echo '<br>
			  <ul class="datalist">
			  <li class="dataheader"><dl>
				<dd class="datafifty">&nbsp;
					'._TABLETITLE.'
				</dd>
				<dd class="datafifty">'._TABLESTATUS.'</dd>			
			  </dl></li>';
	}else{
		admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
	}

	$a = 0;
	
	for ($i=0; $i<count($boxid); $i++) {
		$ids = filter($boxid[$i], "", 1, 20, "num");
   		$teststats = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_tablexyz WHERE id='$ids'"));
		$logtitle = "LOGDEL";
		$logtext = $teststats[title];

		if ($a == 0) {
			echo '<li class="datarowa"><dl>';
			$a = 1;
		}else{
			echo '<li class="datarowb"><dl>';
			$a = 0;
		}
		echo '<dd class="datafifty"><div class="datatxt">&nbsp;'.$logtext.'</div></dd>
  			  <dd class="datafifty"><div class="datatxt">';
			
		$result = $db->sql_query("DELETE FROM ".$prefix."_admin_groups WHERE id='$ids'");
		if ($result) {
			$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBDELETED);
      		$panelactions->log_create($admin[1], 'TEST', $logtitle, $logtext, 2, 0);
			echo '<font class="okfont">'.$lang_var.'</font>';
		}else{
			$lang_var = str_replace("%var%", _TESTTHESINGULAR." "._TESTSINGULAR, _DBDELERROR);
			echo '<font class="errorfont">'.$lang_var.'</font>';
		}

		echo '</div></dd>
				  </dl>
				  </li>';
	}

	echo '</ul>';
	include("admin/footer.php");
}


function adminTestEdit($ids) {
	global $prefix, $db, $admin, $zahl, $torder, $tcounter, $agstats;

	if (($ids > 0 AND $agstats[modchange] != 1) OR ($ids < 1 AND $agstats[modnew] != 1)) {
		admin_error(_TEST, _ASSISTENTTEST, "lock", _ACCESSDENIED);
	}

	$ids		= filter($ids, "", 1, 20, "num");
	$zahl		= filter($zahl, "", 1, 20, "num");
	$torder		= filter($torder, "", 1, 5);
	$tcounter	= filter($tcounter, "", 1, 5, "num");

	$hkey		= $admin[6];
	$hash		= md5("$admin[0]-$admin[1]-$admin[6]");

	include("admin/header.php");
	admin_title("administrator", _TEST, _ASSISTENTTEST, "");
	echo '<table cellspacing="0" cellpadding="0" class="inputtable">
          <form name="form_one" action="admin.php" method="post">';

	if ($ids > 0) {
    	$teststats = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_tablexyz WHERE id='$ids'"));
    	echo '<input type="hidden" name="save" value="1">'; // UPDATE
	}else{
		echo '<input type="hidden" name="save" value="2">'; // INSERT
	}
	
	echo '<tr><td class="essential" style="width:220px;">'._TESTXXXX.'</td><td><input name="xxxx" type="text" class="inputfield" style="width:250px;" maxlength="30" value="'.$teststats[xxxx].'"></td></tr>
		<tr><td>'._TESTYYYY.'</td><td><input name="yyyy" type="text" class="inputfield" style="width:250px;" maxlength="30" value="'.$teststats[yyyy].'"></td></tr>
        <tr><td></td><td>
			<input type="hidden" name="op" value="adminTest"><input type="hidden" name="ids" value="'.$ids.'"><input type="hidden" name="zahl" value="'.$zahl.'">
		    <input type="hidden" name="torder" value="'.$torder.'"><input type="hidden" name="tcounter" value="'.$tcounter.'">
			<input type="hidden" name="chkhkey" value="'.$hkey.'"><input type="hidden" name="chkhash" value="'.$hash.'">
		    <div class="dataspace"></div>
		    <a href="javascript:document.form_one.submit();" class="button_form">'._SAVE.'</a>
        </td></tr>
		</form>
		</table>';
    include("admin/footer.php");
}


switch ($op) {
	
	case "adminTest":
	adminTest($ids, $xxxx, $chkhash, $chkhkey, $save);
	break;

	case "adminTestChange":
	adminTestChange($boxid, $setstatus, $chkhash, $chkhkey);
	break;

	case "adminTestEdit":
	adminTestEdit($ids);
	break;

}

?>
