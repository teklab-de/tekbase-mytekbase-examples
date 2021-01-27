<?php

if (preg_match("/test.php/i", $_SERVER['PHP_SELF'])) { 
    header("Location: ../../../index.php");
	die();
}

if(!is_member($member)) {
	Login();
	die();
}

/* Subuser Access - Not yet implemented for /mytekbase/modules
if ($member[6] != 0 ) {
	$sgstats = $db->sql_fetchrow($db->sql_query("SELECT * FROM ".$prefix."_subusers_access WHERE subuserid='$member[0]' AND module='licenses'"));
}
if (($member[6] > 0 AND $sgstats[modview] == 1 AND ($member[5] == 1 OR $member[5] == 3)) OR ($member[6] == 0 AND ($member[5] == 1 OR $member[5] == 3))) {
*/

if ($op == "List") {
	global $ids, $newip, $newurl, $newpath, $chkhash, $chkhkey, $save;

	$version = "8000";
	$tekbase = tekbase();
	if ($tekbase != $version) {
		$membermsg = member_fileerror("licenses");
		echo $membermsg;
		die();
	}

	$ids = filter($ids, "", 1, 11, "num");
	
	$breadcrumb_atitle = ''._LICENSES.'';
	$breadcrumb_alink = 'List&of=licenses';

	include ("members/header.php");

	$member_icon = "key";
	$member_title = ''._LICENSES.'';
	$member_titlesecond = '';
	$member_assist = ''._ASSISTENTLICENSES.'';
	if (file_exists("mytekbase/members/tpl/content_header.tpl")) {
		include("mytekbase/members/tpl/content_header.tpl");
	}else{
		include("members/themes/$member_theme/tpl/content_header.tpl");
	}
    
   if ($member[6] == 0) {
		$memstats = $db->sql_fetchrow($db->sql_query("SELECT member FROM ".$prefix."_members WHERE id='$member[0]'"));
	}elseif ($member[6] > 0) {
		$memstats = $db->sql_fetchrow($db->sql_query("SELECT member FROM ".$prefix."_members WHERE id='$member[6]'"));
	}

    $json_array = [
      'fields' => [
        'customer' => $memstats[member];
      ],
      'db' => [
        'orderby' => 'siteurl',
        'sort' => 'ASC'
      ]
    ];

    $string = urlencode(json_encode($json_array));

    $ch = curl_init();
    // Set RESELLERID and API-Key
    // The best option would be to read settings from a DB
    curl_setopt($ch, CURLOPT_URL, 'https://api.tekbase.de/v1/reseller/RESELLERID/?json='.$string);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'authenticate: apikey=<API-Key>'
    ]);

    $list_response curl_exec($ch);
    
    curl_close($ch);
    
    if (!$list_response) {
        $membermsg = member_error(""._LICENSESMAINTENANCE."");
        echo $membermsg;
        include("members/footer.php");
        die();
    }

    $list_response = json_decode($list_response, true);
    
    
//	if (!$save) {
		/* Lite licenses - If there is none yet, it will be added.
        // Is not yet available in the Reseller API
		*/
        
        /* Commercial licenses - free second license
        // Is not yet available in the Reseller API
        */
//	}

	if ($save) {
		$sys_hash = md5("$member[0]-$member[1]-$chkhkey");
		if ($sys_hash != $chkhash OR $chkhkey != $member[7]) {
			$membermsg = member_error(""._UNAUTHORIZEDACCESS."");
			echo $membermsg;
			include("members/footer.php");
			die();
		}
		$logyear = strftime("%Y", time());
		$logday = strftime("%d", time());
		$logmonth = strftime("%m", time());
		$logtime = time();
		
		$newip = filter($newip, "", 1, 32);	
        $newsite = filter($newsite, "", 1, 255);
        $newpath = filter($newpath, "", 1, 255);

		if (!$newip OR !$newurl OR !$newpath) {
			$membermsg = member_errorback(""._LICENSESNOTALLFIELDS."");
			echo ''.$membermsg.'';
			include ("members/footer.php");
			die();
		}
	}

	if ($save == 1) {
		$logtitle = "LOGUP";
		$logtext = "";
                
        // Check the array $list_response and select the row with id = $ids. Check ok? Set $urow = 1;
        // ......Code here......
        // 
		if ($urow == 1) {
            $ch = curl_init();
            // Set RESELLERID and API-Key
            curl_setopt($ch, CURLOPT_URL, 'https://api.tekbase.de/v1/reseller/RESELLERID/'.$ids.'/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
              'authenticate: apikey=<API-Key>'
              'Content-Type: application/json; charset=utf-8'
            ]);

            $json_array = [
              'siteip' => $newip,
              'siteurl' => $newurl,
              'sitepath' => $newpath
            ]; 

            $body = json_encode($json_array);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

            $response = curl_exec($ch);

            curl_close($ch);
                                
			if (!$response) {
				$result = $db->sql_query("INSERT INTO ".$prefix."_members_logs (id, member, module, title, text, day, month, year, date, status) VALUES (NULL, '$logmem', 'MLICENSES', '$logtitle', '$logtext', '$logday', '$logmonth', '$logyear', '$logtime', '1')");
				$membermsg = member_errorback(""._LICENSESDBUPERROR."");
				echo ''.$membermsg.'';
				include ("members/footer.php");
				die();
            }else{
                // Re-query the list of licenses.
                $json_array = [
                  'fields' => [
                    'customer' => 'kd10001'
                  ],
                  'db' => [
                    'orderby' => 'siteurl',
                    'sort' => 'ASC'
                  ]
                ];

                $string = urlencode(json_encode($json_array));

                $ch = curl_init();
                // Set RESELLERID and API-Key
                curl_setopt($ch, CURLOPT_URL, 'https://api.tekbase.de/v1/reseller/RESELLERID/?json='.$string);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                  'authenticate: apikey=<API-Key>'
                ]);

                $list_response = curl_exec($ch);
                    
                curl_close($ch);

                if (!$list_response) {
                    $membermsg = member_error(""._LICENSESMAINTENANCE."");
                    echo $membermsg;
                    include("members/footer.php");
                    die();
                }
            
                $list_response = json_decode($list_response, true);
                    
				$membermsg = member_ok(""._LICENSESDBUPDATED."");
				echo ''.$membermsg.'';
			}
		}else{
			$membermsg = member_error(""._LICENSESERROR."");
			echo ''.$membermsg.'';
			include ("members/footer.php");
			die();
		}
	}

	if (count($list_response) > 0) {
		echo '<script type="text/javascript">
$(document).ready(function(){
	$(".modallicbox").click(function(){
		$("#modalbox").find(".modal-body").html(\'<div class="loading-cube wh-lg"><div class="cube1 cube"></div><div class="cube2 cube"></div><div class="cube4 cube"></div><div class="cube3 cube"></div></div>\').load("members.php?op=Show&of=licenses&ids="+$(this).attr("data-id")+"&version="+$(this).attr("data-version"));
	});
});
</script>
		<div id="modalbox" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="APIInfo" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header bg-denim">
						<div class="close-circle" data-dismiss="modal" aria-hidden="true"><i class="close tekbase icon-delete_sign"></i></div>
						<h4 class="modal-title">'._LICENSES.'</h4>
					</div>
					<div class="modal-body"></div>
		   	    </div>
		   	</div>
		</div>
		<form name="form_one" action="members.php" method="post">
		<div class="datatablebox">
		<table cellpadding="0" cellspacing="0" border="0" class="display bg-denim" id="datatable" width="100%">
			<thead>
				<tr>
					<th class="content-box-header">'._TABLETITLE.'</th>
					<th class="content-box-header"><center><b>TekCMS</b></center></th>
					<th class="content-box-header"><center><b>TekSHOP</b></center></th>
					<th class="content-box-header"><center><b>TekBILL</b></center></th>
					<th class="content-box-header"><center><b>TekGWI</b></center></th>
					<th class="content-box-header"><center><b>TekRWI</b></center></th>
					<th class="content-box-header"><center><b>TekSWI</b></center></th>
					<th class="content-box-header"><center><b>TekVWI</b></center></th>
				</tr>
			</thead>
			<tbody>';
        
        // Output array $list_response as $row
        foreach ($list_response as $row) {      
            if ($row[version] == "lite") { $row[text] = "TekBASE lite"; }
            if ($row[version] == "privat") { $row[text] = "TekBASE privat"; }
            if ($row[version] == "std" OR $row[version] == "adv") { $row[text] = "TekBASE business"; }
			echo '<tr><td>
				    <div class="dataimg">';
			if ($row[status] == 1) {
				echo '<a href="members.php?op=Edit&mf=licenses&ids='.$row[id].'" class="tooltip-button btn btn-denim" data-placement="bottom" title="'._EDIT.'"><i class="tekbase icon-edit"></i></a>';
			}else{
				echo '<i class="tekbase icon-edit btn btn-default opacity-30"></i>';
			}
			if ($row[status] == 1 AND $row[siteurl] != "") {
				if ($row[version] != "lite") {
					echo '<a href="javascript:void(0)" class="tooltip-button btn btn-denim modallicbox" data-id="'.$row[id].'" data-version="8_0" data-toggle="modal" data-target="#modalbox" data-placement="bottom" title="License for 8.x"><span class="tekbase">8</span></a>';
				}else{
					echo '<a href="javascript:void(0)" class="tooltip-button btn btn-denim modallicbox" data-id="'.$row[id].'" data-version="8_0" data-toggle="modal" data-target="#modalbox" data-placement="bottom" title="License for 8.x"><span class="tekbase">8</span></a>';
				}
			}else{
				echo '<span class="tekbase btn btn-default opacity-30">8</span>';
			}
			echo '</div>
				  <div class="datatxt">&nbsp;'.$row[title].'</div><div class="clear"></div>';

			if ($row[siteurl] != "" AND $row[status] == 1) {
				echo '<div style="line-height:18px;" class="datatxt pad0A"><b>'._LICENSESURL.'</b>&nbsp;'.$row[siteurl].'</div>';
			}
			if ($row[siteurl] != "" AND $row[status] == 0) {
				echo '<div style="line-height:18px;" class="datatxt pad0A font-red"><b>'._LICENSESURLLOCK.'</b>&nbsp;'.$row[siteurl].'</div>';
			}
			
			echo '</td>
				  <td><div class="dataimgcenter">';

			if ($row[cms] == "1") {
				echo '<i class="tekbase icon-ok btn btn-success"></i>';
			}else{
				echo '<i class="tekbase icon-inactive_state btn btn-default opacity-30"></i>';
			}

			echo '</div></td>
				  <td><div class="dataimgcenter">';

			if ($row[shop] == "1") {
				echo '<i class="tekbase icon-ok btn btn-success"></i>';
			}else{
				echo '<i class="tekbase icon-inactive_state btn btn-default opacity-30"></i>';
			}

			echo '</div></td>
				  <td><div class="dataimgcenter">';

			if ($row[version] != "adv") { echo '<i class="tekbase icon-inactive_state btn btn-default opacity-30"></i>'; }
			if ($row[version] == "adv") { echo '<i class="tekbase icon-ok btn btn-success"></i>'; }

			echo '</div></td>
				  <td><div class="datatxtcenter">'.$row[gwislots].'</div></td>
				  <td><div class="datatxtcenter">'.$row[rwislots].'</div></td>
				  <td><div class="datatxtcenter">'.$row[swislots].'</div></td>
				  <td><div class="datatxtcenter">'.$row[vwislots].'</div></td>
				  </tr>';
		}

		echo '</tbody></table>
				 <div class="dataTables_select bg-denim"><br><br></div>
				 <div class="clear"></div>
				 </div>
				 </form>';
	}else{
		echo ''._LICENSESNOENTRY.'';
	}
	
    include ("members/footer.php");
}


if ($op == "Edit") {
	global $ids, $siteurlip, $save;

	$ids = filter($ids, "", 1, 11, "num");
	
	$hkey = $member[7];
	$hash = md5("$member[0]-$member[1]-$member[7]");	
	
	$breadcrumb_atitle = ''._LICENSES.'';
	$breadcrumb_alink = 'List&of=licenses';
	
	include ("members/header.php");
	
    
    /* Subuser Access - Not yet implemented for /mytekbase/modules
	if ($subserverstats[active] == 0 AND $subserverstats[modchange] == 0 AND $subserversstats[active] == 0 AND $subserversstats[modchange] == 0 AND $member[6] > 0) {
		$member_icon = "lock";
		$member_title = ''._LICENSES.'';
		$member_titlesecond = '';
		$member_assist = ''._ASSISTENTLICENSES.'';	
		if (file_exists("mytekbase/members/tpl/content_header.tpl")) {
			include("mytekbase/members/tpl/content_header.tpl");
		}else{
			include("members/themes/$member_theme/tpl/content_header.tpl");
		}
		$membermsg = member_errorback(""._ACCESSDENIED."");
		echo $membermsg;
	   	include ("members/footer.php");
		die();
	}
    */
	
	$member_icon = "key";
	$member_title = ''._LICENSES.'';
	$member_titlesecond = '';
	$member_assist = ''._ASSISTENTLICENSES.'';
	if (file_exists("mytekbase/members/tpl/content_header.tpl")) {
		include("mytekbase/members/tpl/content_header.tpl");
	}else{
		include("members/themes/$member_theme/tpl/content_header.tpl");
	}
	
    // Create input fields for IP, Domain and Path
    // Tip: In the own TekBASE edit the /members/themes/.../login_box.tpl and show the siteip, siteurl and path.
    // Send $newip, $newurl, $newpath
    //<input type="hidden" name="op" value="List"><input type="hidden" name="of" value="licenses">
	//<input type="hidden" name="ids" value="'.$ids.'"><input type="hidden" name="save" value="1">
    //<input type="hidden" name="chkhkey" value="'.$hkey.'"><input type="hidden" name="chkhash" value="'.$hash.'">
    
    // Next API Update: Search for IP, URL to show the path.

    include ("members/footer.php");
}

if ($op == "Show") {
	global $ids, $version;

	$ids = filter($ids, "", 1, 11, "num");
	$version = filter($version, "", 1, 2, "num");

	if ($ids > 0) {
        $ch = curl_init();
        // Set RESELLERID and API-Key
        curl_setopt($ch, CURLOPT_URL, 'https://api.tekbase.de/v1/reseller/12345/$ids/');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authenticate: apikey=<API-Key>'
        ]);

        $response = curl_exec($ch);

        curl_close($ch);
        
        if (!$response) {
            $membermsg = member_error(""._LICENSESMAINTENANCE."");
			echo $membermsg;
        }else{
            $response = json_decode($response, true);
            $response_key = preg_replace('/\r\n|\r|\n/', "<br>", $response[key]);
            echo '<script type="text/javascript">
$(document).ready(function(){
$.fn.OneClickSelect = function () {
  return $(this).on("click", function () {
    var range, selection;

    if (window.getSelection) {
      selection = window.getSelection();
      range = document.createRange();
      range.selectNodeContents(this);
      selection.removeAllRanges();
      selection.addRange(range);
    } else if (document.body.createTextRange) {
      range = document.body.createTextRange();
      range.moveToElementText(this);
      range.select();
    }
  });
};
	$(".lickey").click(function(){
		$(this).OneClickSelect();
	});
});
</script>
<div class="lickey">'.$response_key.'</div>';
		}
	}
}

/* Subuser Access - Redirect
}else{
    include ("members/header.php");
	$member_icon = "lock";
	$member_title = ''._LICENSES.'';
	$member_titlesecond = '';
	$member_assist = ''._ASSISTENTLICENSES.'';
	if (file_exists("mytekbase/members/tpl/content_header.tpl")) {
		include("mytekbase/members/tpl/content_header.tpl");
	}else{
		include("members/themes/$member_theme/tpl/content_header.tpl");
	}
	$membermsg = member_errorback(""._ACCESSDENIED."");
	echo $membermsg;
   	include ("members/footer.php");
	die();
}
*/
?>
