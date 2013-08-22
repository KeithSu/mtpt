<?php
require "include/bittorrent.php";
dbconn();
require_once(get_langfile_path());
loggedinorreturn();
//start apply for links
if ($_GET['action'] == "apply")
{
if (get_user_class() >= $applylink_class){
stdhead($lang_linksmanage['head_apply_for_links']);
begin_main_frame();
begin_frame($lang_linksmanage['text_apply_for_links'], true,10,"100%","center");
	print("<p align=left><b><font size=5>".$lang_linksmanage['text_rules']."</font></b></p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_one']."</p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_two']."</p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_three']."</p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_four']."</p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_five']."</p>\n");
	print("<p align=left>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp ".$lang_linksmanage['text_rule_six']."</p>\n");
	
	print("<p>".$lang_linksmanage['text_red_star_required']."</p>");
?>
<form method=post action="<?php echo $_SERVER["PHP_SELF"];?>">
<div table class=main border=1 cellspacing=0 cellpadding=5>
<div><div class=rowhead><?php echo $lang_linksmanage['text_site_name']?><font color=red>*</font></div><div class=rowfollow align=left><input type=text name=linkname style="width: 200px">&nbsp;&nbsp;<font class=small><?php echo $lang_linksmanage['text_sitename_note']?></font></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_url']?><font color=red>*</font></div><div class=rowfollow align=left><input type=text name=url style="width: 200px">&nbsp;&nbsp;<font class=small><?php echo $lang_linksmanage['text_url_note']?></font></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_title']?></div><div class=rowfollow align=left><input type=text name=title style="width: 200px">&nbsp;&nbsp;<font class=small><?php echo $lang_linksmanage['text_title_note']?></font></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_administrator']?><font color=red>*</font></div><div class=rowfollow align=left><input type=text name=admin style="width: 200px">&nbsp;&nbsp;<font class=small><?php echo $lang_linksmanage['text_administrator_note']?></font></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_email']?><font color=red>*</font></div><div class=rowfollow align=left><input type=text name=email style="width: 200px">&nbsp;&nbsp;<font class=small><?php echo $lang_linksmanage['text_email_note']?></font></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_reason']?><font color=red>*</font></div><div class=rowfollow align=left><textarea name=reason style="width: 400px" rows=10></textarea></div></div>
<div><div colspan=2 align=center><input type="hidden" name="action" value="newapply"><input type=submit value="<?php echo $lang_linksmanage['submit_okay']?>" class=btn><input type=reset class=btn value="<?php echo $lang_linksmanage['submit_reset']?>"></div></div>
</div>
</form>
<?php
	end_frame();
	end_main_frame();
	stdfoot();
}
else permissiondenied();
}
elseif ($_POST['action'] == "newapply")
{
if (get_user_class() >= $applylink_class){
$sitename = unesc($_POST["linkname"]);
$url = unesc($_POST["url"]);
$title = unesc($_POST["title"]);
$admin = unesc($_POST["admin"]);
$email = htmlspecialchars(trim($_POST['email']));
$email = safe_email($email);
$reason = unesc($_POST["reason"]);
if (!$sitename)
stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_no_sitename']);
elseif (!$url)
stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_no_url']);
elseif (!$admin)
stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_no_admin']);
elseif (!$email)
stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_no_email']);
elseif (!check_email($email))
	stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_invalid_email']);
elseif (!$reason)
	stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_no_reason']);
elseif (strlen($reason) < 20)
	stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_reason_too_short']);
else{
	$message = "[b]Sitename[/b]: ".$sitename."\n[b]URL[/b]: ".$url."\n[b]Title[/b]: ".$title."\n[b]Administrator: [/b]".$admin."\n[b]EMail[/b]: ".$email."\n[b]Reason[/b]: \n".$reason."\n";
	$message = sqlesc($message);
	$subject = $sitename." applys for links";
	$subject = sqlesc($subject);
	$added = "'" . date("Y-m-d H:i:s") . "'";
	$userid = $CURUSER['id'];
	sql_query("INSERT INTO staffmessages (sender, added, msg, subject) VALUES($userid, $added, $message, $subject)") or sqlerr(__FILE__, __LINE__);
	stderr($lang_linksmanage['std_success'], $lang_linksmanage['std_success_note']);
	}
}
else permissiondenied();
}

//start admin work
elseif (get_user_class() < $linkmanage_class)
	permissiondenied();
else{
if ($_GET['action'] == "del") {
$id = 0 + $_GET['id'];
if (!$id) { header("Location: linksmanage.php"); die();}
$result = sql_query ("SELECT * FROM links where id = '".$id."'");
if ($row = mysql_fetch_array($result))
do {
sql_query ("DELETE FROM links where id = '".$row["id"]."'") or sqlerr(__FILE__, __LINE__);
} while($row = mysql_fetch_array($result));
	$Cache->delete_value('links');
header("Location: linksmanage.php");
die();
}

if ($_POST['action'] == "editlink") {
	$name = ($_POST['linkname']);
	$url = ($_POST['url']);
	$title = ($_POST['title']);
if (!$name && !$url && !$title) { header("Location: linksmanage.php"); die();}
	sql_query("UPDATE links SET name = ".sqlesc($_POST['linkname']).", url = ".sqlesc($_POST['url']).", title = ".sqlesc($_POST['title'])." WHERE id = '".$_POST['id']."'") or sqlerr(__FILE__, __LINE__);
	$Cache->delete_value('links');
header("Location: linksmanage.php");
die();
}

if ($_POST['action'] == "add")
{
	if ($_POST["linkname"] == "" || $_POST["url"] == "" || $_POST["title"] == "")
	stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_missing_form_data']);

	$linkname = sqlesc($_POST["linkname"]);
	$url = sqlesc($_POST["url"]);
	$title = sqlesc($_POST["title"]);


	sql_query("INSERT INTO links (name, url, title) VALUES($linkname, $url, $title)") or sqlerr(__FILE__, __LINE__);
	$res = sql_query("SELECT id FROM links WHERE name=$linkname");
	$Cache->delete_value('links');
	$arr = mysql_fetch_row($res);
	if (!$arr)
	stderr($lang_linksmanage['std_error'], $lang_linksmanage['std_unable_creating_new_link']);
	header("Location: linksmanage.php");
	die;
}
stdhead($lang_linksmanage['std_links_manage']);

?>
<h1><?php echo $lang_linksmanage['text_add_link']?></h1>
<form method=post action="<?php echo $_SERVER["PHP_SELF"];?>">
<div border=1 cellspacing=0 cellpadding=5>
<div><div class=rowhead><?php echo $lang_linksmanage['text_site_name']?></div><div><input type=text name=linkname style="width: 200px"></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_url']?></div><div><input type=text name=url style="width: 200px"></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_title']?></div><div><input type=text name=title style="width: 200px"></div></div>
<div><div colspan=2 align=center><input type="hidden" name="action" value="add"><input type=submit value="<?php echo $lang_linksmanage['submit_okay']?>" class=btn></div></div>
</div>
</form>
<?php
echo '<h1>'.$lang_linksmanage['text_manage_links'].'</h1>';
echo '<div width="80%"  border="0" align="center" cellpadding="2" cellspacing="0">';
echo "<div><div class=colhead align=left>".$lang_linksmanage['text_site_name']."</div><div class=colhead>".$lang_linksmanage['text_url']."</div><div class=colhead>".$lang_linksmanage['text_title']."</div><div class=colhead align=center>".$lang_linksmanage['text_modify']."</div></div>";
$result = sql_query ("SELECT * FROM links ORDER BY id ASC");
if ($row = mysql_fetch_array($result)) {
do {
echo "<div><div>".$row["name"]."</div><div>".$row["url"]."</div><div>".$row["title"]. "</div><div align=center nowrap><b><a href=\"".$PHP_SELF."?action=edit&id=".$row["id"]."\">".$lang_linksmanage['text_edit']."</a>&nbsp;|&nbsp;<a href=\"javascript:confirm_delete('".$row["id"]."', '".$lang_linksmanage['js_sure_to_delete_link']."', '');\"><font color=red>".$lang_linksmanage['text_delete']."</font></a></b></div></div>";
} while($row = mysql_fetch_array($result));
} else {print "<div><div colspan=4>".$lang_linksmanage['text_no_links_found']."</div></div>";}
echo "</div>";
?>
<?php if ($_GET['action'] == "edit") {
$id = 0 + ($_GET["id"]);
$result = sql_query ("SELECT * FROM links where id = ".sqlesc($id));
if ($row = mysql_fetch_array($result)) {
?>
<h1><?php echo $lang_linksmanage['text_edit_link']?></h1>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<div border=1 cellspacing=0 cellpadding=5>
<div><div class=rowhead><?php echo $lang_linksmanage['text_site_name']?></div><div><input type=text name=linkname size=40 value="<?php echo $row['name'];?>"></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_url']?></div><div><input type=text name=url size=40 value="<?php echo $row["url"];?>"></div></div>
<div><div class=rowhead><?php echo $lang_linksmanage['text_title']?></div><div><input type=text name=title size=40 value="<?php echo $row["title"];?>"></div></div>
<div><div colspan=2 align=center><input type="hidden" name=id value="<?php echo $row["id"];?>"><input type="hidden" name="action" value="editlink"><input type=submit value="<?php echo $lang_linksmanage['submit_okay']?>" class=btn></div></div>
</div>
</form>
<?php
}
}
stdfoot();
}
