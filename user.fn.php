<?php

/**
 * chibi bbs gnuboard outLogin plugin
 *
 * @author kcopper8 <kcopper8@gmail.com>
 * @version 0.1.0
 * */

/**
 * 로그인할 때 필요한 멤버 레벨입니다. 이 레벨 이상의 멤버는 자동 로그인됩니다.
 */
define('K8_G4_REQUIRED_MEMBER_LEVEL', 6);

/**
 * 그누보드의 경로입니다. 스킨 파일의 위치가 아닌, 치비툴 index.php 의 위치를 기준으로 기재해야 합니다.
 */
define('K8_G4_PATH', '../board/');

/*************************************************************
 * 설정값 영역 종료
 ************************************************************ */

function k8_get_g4_session_string_in_chibi($g4_path) {
	$session_file_path = $g4_path .'/data/session/sess_'.session_id();
	if (file_exists($session_file_path)) {
		return file_get_contents($session_file_path);
	} else {
		return "";
	}
}

function k8_parse_session_string($str) {
	$r = array();
	while ($i = strpos($str, '|'))
	{
	    $k = substr($str, 0, $i);
	    $v = unserialize(substr($str, 1 + $i));
	    $str = substr($str, 1 + $i + strlen(serialize($v)));
	    $r[$k] = $v;
	}
	return $r;
}

function k8_select_g4_member_level($mb_id, $table_name) {
	    $query_result = mysql_query("select `mb_level` from $table_name where mb_id = TRIM('$mb_id') LIMIT 0,1");
			if (!mysql_num_rows($query_result)) {
				return FALSE;
			}

			return mysql_result($query_result, 0);
}

function k8_get_g4_member_level_in_chibi($g4_path) {
	$g4 = array();
	$g4['path'] = $g4_path;
	require_once("$g4[path]/config.php");  // 설정 파일

	$session_content = k8_get_g4_session_string_in_chibi($g4['path']);
	if (empty($session_content)) {
		return -1;
	}

	$g4_session_array =  k8_parse_session_string($session_content);
	if (!isset($g4_session_array['ss_mb_id'])) {
		return -1;
	}

	$g4_member_level = k8_select_g4_member_level($g4_session_array['ss_mb_id'], $g4['member_table']);
	if (!$g4_member_level) {
		return -1;
	}

	return $g4_member_level;
}

$ret_pos = strpos($_SERVER["SCRIPT_FILENAME"], 'admin.php');

if ($ret_pos === false && $_SESSION['session_key_cookie']!=md5($cid.'+'.session_id())) {
  $k8_g4_member_level = k8_get_g4_member_level_in_chibi(K8_G4_PATH);

  if ($k8_g4_member_level >= K8_G4_REQUIRED_MEMBER_LEVEL)  {
    $_SESSION['session_key_cookie']=md5($cid.'+'.session_id());
  }
}
?>
