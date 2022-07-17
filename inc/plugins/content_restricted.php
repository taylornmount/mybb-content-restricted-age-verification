<?php
/***************************************************************************
 *
 *  CONTENT RESTRICTED (Age Accept To View Board)
 *  Author: c.widow
 *  Copyright: WidowCC © 2022 - Present
 *  
 *  Website: https://github.com/cryptic-widow
 *
 *  The author of this plugin is not responsible for damages caused by this
 *  plugin. Use at your own risk.
 *  
 *  This software is provided by the copyright holders and contributors “as is”
 *  and any express or implied warranties, including, but not limited to, the 
 *  implied warranties of merchantability and fitness for a particular purpose
 *  are disclaimed. In no event shall the copyright owner or contributors be 
 *  liable for any direct, indirect, incidental, special, exemplary, or 
 *  consequential damages (including, but not limited to, procurement of substitute
 *  goods or services; loss of use, data, or profits; or business interruption)
 *  however caused and on any theory of liability, whether in contract, strict 
 *  liability, or tort (including negligence or otherwise) arising in any way 
 *  out of the use of this software, even if advised of the possibility of such damage.
 *  
 *  Shout out to user 67332 on Stack Overflow for calculating bday vs today.
 *
 ***************************************************************************/

if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.");
}

function content_restricted_info() {
	return array(
		"name"			=> "Content Restricted",
		"description"		=> "Require users to certify their age via a form check stored as a cookie.",
		"website"		=> "https://widowcc.com/",
		"author"		=> "WidowCC",
		"authorsite"		=> "https://cwidow.carrd.co/",
		"version"		=> "2.0",
		"guid" 			=> "",
		"codename"		=> "",
		"compatibility" 	=> "16*,18*"
	);
}

function content_restricted_install() {
	global $mybb, $db;
	
	// SETTINGS GROUP
	$contentrestricted_group = array(
        	"name" => "contentrestricted",
        	"title" => "Content Restricted Settings",
        	"description" => "Edit the settings for the content restricted plugin.",
        	"disporder" => "1",
        	"isdefault" => "no",
        );
	$db->insert_query("settinggroups", $contentrestricted_group);
    	$gid = $db->insert_id();

	// INDIVIDUAL SETTINGS
	$contentrestricted_settings[] = array(
		"name" => "conres_age",
        	"title" => "Minimum Age Required",
        	"description" => "Input the age users must be in order to view your site.",
        	"optionscode" => "numeric",
        	"value" => "18",
        	"disporder" => "1",
        	"gid" => intval($gid)
        );
	$contentrestricted_settings[] = array(
		"name" => "conres_custommessage",
        	"title" => "Custom Message",
        	"description" => "Use this area to create a custom message within the template, HTML works here but BBCode does not. Leave blank if you do not want to use a custom message.",
        	"optionscode" => "textarea",
        	"value" => "",
        	"disporder" => "2",
        	"gid" => intval($gid)
        );
	foreach($contentrestricted_settings as $setting)
	{
		$db->insert_query("settings", $setting);
	}
	
	// REBUILD SETTINGS BECAUSE MYBB DOCS TOLD YOU TO
	rebuild_settings();
}

function content_restricted_is_installed() {
	global $mybb, $db;

	if(isset($mybb->settings['conres_age']))
	{
	    return true;
	}

	return false;
}

function content_restricted_activate() {
	global $mybb, $db;

	// CREATE TEMPLATE & INSERT CONTENT
	$template = '<html>
<head>
<title>{$mybb->settings[\'bbname\']} - Content Restricted</title>
{$headerinclude}
</head>
<body>
{$header}
	<form action="misc.php" method="post">
	<input type="hidden" name="action" value="content_restricted" />
	<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<thead>
				<tr>
					<td class="thead"><strong>Content Restricted ({$mybb->settings[\'conres_age\']}+)</strong></td>
				</tr>
			</thead>
			<tbody style="text-align: center;">
				<tr>
					<td class="trow1">
						<strong>Some areas of {$mybb->settings[\'bbname\']} may contain material not suitable for those <u>under {$mybb->settings[\'conres_age\']} years of age</u>.</strong><br /><br />
						Age confirmation is necessary for access to content on this website. If you would still like to continue please confirm you are of the required age by verifying your birthday.<br /><br />
						By filling out this form you are certifying that you are of the required age to view the material on this website, which <em>could</em> contain explicit and/or uncensored content.<br /><br />
						<strong>Please enter your birthday to continue:</strong><br />
						<input type="text" class="textbox" name="date_day" size="2" maxlength="2" pattern="[0-9]{1,5}" />
						&nbsp;
						<select name="date_month">
							<option value="">&nbsp;</option>
							<option value="1">January</option>
							<option value="2">February</option>
							<option value="3">March</option>
							<option value="4">April</option>
							<option value="5">May</option>
							<option value="6">June</option>
							<option value="7">July</option>
							<option value="8">August</option>
							<option value="9">September</option>
							<option value="10">October</option>
							<option value="11">November</option>
							<option value="12">December</option>
						</select>
						&nbsp;
						<input type="text" class="textbox" name="date_year" size="4" maxlength="4" pattern="[0-9]{1,5}" />
						<br />
						<small><em>DD/MM/YYYY</em></small>
						<br />{$error}<br />
						<small>This data is for verification purposes only and is not stored on our database, it is stored as a cookie on your browser to determine your viewing preferences.</small>
					</td>
				</tr>
				<tr>
					<td class="tfoot">
							<input type="submit" class="button" name="submit" value="I understand and wish to continue." />
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	{$content_restricted_custom_message}
{$footer}
</body>
</html>';
	$insert_array = array(
		'title' => 'content_restricted',
		'template' => $db->escape_string($template),
		'sid' => '-1',
		'version' => '',
		'dateline' => time()
	);
	$db->insert_query('templates', $insert_array);

	$template = '<br />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<thead>
		<tr>
			<td class="thead{$expthead}"><strong>Message From {$mybb->settings[\'bbname\']} Team</strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="trow1">
				{$mybb->settings[\'conres_custommessage\']}
			</td>
		</tr>
	</tbody>
</table>';
	$insert_array = array(
		'title' => 'content_restricted_custom_message',
		'template' => $db->escape_string($template),
		'sid' => '-1',
		'version' => '',
		'dateline' => time()
	);
	$db->insert_query('templates', $insert_array);

	$template = '<html>
	<head>
		<title>{$mybb->settings[\'bbname\']} - Content Restricted</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<tbody>
				<tr>
					<td class="thead"><strong>Content Restricted ({$mybb->settings[\'conres_age\']}+)</strong></td>
				</tr>
				<tr>
					<td class="trow1" style="text-align: center;">
						You do not have permission to view this website because you do not meet this websites age requirement.
					</td>
				</tr>
			</tbody>
		</table>
		{$content_restricted_custom_message}
		{$footer}
	</body>
</html>';
	$insert_array = array(
		'title' => 'content_restricted_denied',
		'template' => $db->escape_string($template),
		'sid' => '-1',
		'version' => '',
		'dateline' => time()
	);
	$db->insert_query('templates', $insert_array);
}

function content_restricted_deactivate() {
	global $mybb, $db;

	// DELETE TEMPLATE & CONTENT
	$db->delete_query("templates", "title = 'content_restricted'");
	$db->delete_query("templates", "title = 'content_restricted_custom_message'");
	$db->delete_query("templates", "title = 'content_restricted_denied'");
}

function content_restricted_uninstall() {
	global $mybb, $db;

	// DELETE SETTINGS & THE SETTINGS GROUP
	$db->delete_query('settings', "name IN ('conres_age','conres_custommessage')");
	$db->delete_query('settinggroups', "name IN ('contentrestricted')");
	
	// REBUILD SETTINGS BECAUSE MYBB DOCS TOLD YOU TO
	rebuild_settings();
}

// HOOK GLOBAL END SO THIS EXECUTES ON A GLOBAL SCALE BEFORE ALLOWING SITE ACCESS
$plugins->add_hook('global_end', 'content_restricted_do_check');

function content_restricted_do_check() {
	global $mybb, $db, $theme, $templates, $header, $footer, $headerinclude;

		
	// CHECK TO SEE IF COOKIE IS SET THEN PROCEED AS NEEDED
	if (!isset($mybb->cookies['content_restricted'])) {
		// POST REQUEST METHOD FOR FORM
		if($mybb->request_method == "post") {
			// MYBB HAS TO HAVE THIS
			verify_post_check($mybb->input['my_post_key']);

			$age_required_query = $db->simple_select("settings", "*", "name='conres_age'");
			$age_required = $db->fetch_field($age_required_query, "value");

			$date_day = (int)$mybb->input['date_day'];
			$date_month = (int)$mybb->input['date_month'];
			$date_year = (int)$mybb->input['date_year'];

			$todays_date = date_create('today');
			$user_birthdate = date_create("$date_day-$date_month-$date_year");
			$user_age = date_diff($todays_date, $user_birthdate)->y;
			
			if ($user_age >= $age_required) {
				my_setcookie("content_restricted", true, 60*24*60*60, true);
				redirect('index.php');
			}
			else {
				my_setcookie("content_restricted", false, 60*24*60*60, true);
				redirect('index.php');
			}
		}
		if (!empty($mybb->settings['conres_custommessage'])) {
			eval("\$content_restricted_custom_message = \"".$templates->get("content_restricted_custom_message")."\";");
		}
		eval("\$content_restricted = \"".$templates->get("content_restricted")."\";");
		output_page($content_restricted);
		exit;
	}
	elseif ($mybb->cookies['content_restricted'] == false) {
		if (!empty($mybb->settings['conres_custommessage'])) {
			eval("\$content_restricted_custom_message = \"".$templates->get("content_restricted_custom_message")."\";");
		}
		eval("\$content_restricted_denied = \"".$templates->get("content_restricted_denied")."\";");
		output_page($content_restricted_denied);
		exit;
	}
}