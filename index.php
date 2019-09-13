<?php
header('Content-type: text/plain; charset=windows-1251');
require_once('backender/db_cfg.php');
require_once('functions.php');
if (isset($_GET['route'])) {
	$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
	if (mysqli_connect_errno()) { 
		printf("Failed. Error: %s\n", mysqli_connect_error()); 
		exit; 
	}
	$db->set_charset('cp1251');
	$url_exp = explode('/', $_GET['route']);
	if ($url_exp[0]=="users" && $url_exp[2]=="services") {
		if (is_numeric($url_exp[1]) && is_numeric($url_exp[3])) {
			$user = $url_exp[1];
			$service = $url_exp[3];
			$result = $db->query("SELECT * FROM `tarifs` WHERE `tarif_group_id` = $service");
			$res_arr = array("result"=>"OK");
			$res_tarifs = array();
			while ($row = $result->fetch_assoc()) {
				$title = explode(" ", $row['title']);
				$res_arr["tarifs"] = array("title"=>$title[0], "link"=>$row['link'], "speed"=>$row['speed']);
				$new_payday = strtotime("today midnight +".$row['pay_period']." month")."+0300";; 
				$res_tarifs[] = array("ID"=>$row['ID'], "title"=>$row['title'], "price"=>intval($row['price']), "pay_period"=>$row['pay_period'], "new_payday"=>$new_payday, "speed"=>$row['speed']);
		  }
		  $res_arr["tarifs"]["tarifs"] = $res_tarifs;
		  echo iconv('utf-8', 'windows-1251', json_encode(conv($res_arr), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		} else {
			printf("Invalid request!");
		}
	} else {
		printf("Invalid request!");
	}
	$db->close();
} elseif ($_SERVER['REQUEST_METHOD']=="PUT") {
	$data = array();
	$exploded = explode('&', file_get_contents('php://input'));
	foreach ($exploded as $pair) {
		$item = explode('=', $pair);
		if (count($item) == 2) {
			$data[urldecode($item[0])] = urldecode($item[1]);
		}
	}
	if (isset($data['tarif_id'])) {
		$exp_url = explode('/', $_SERVER['REQUEST_URI']);
		$user = 0;
		$service = 0;
		for ($i=0 ; $i<count($exp_url) ; $i++) {
			if ($exp_url[$i]=="users")
				$user = $exp_url[$i+1];
			if ($exp_url[$i]=="services")
				$service = $exp_url[$i+1];
			if ($user!=0 && $service!=0){
				$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
				if (mysqli_connect_errno()) { 
					printf("Failed. Error: %s\n", mysqli_connect_error()); 
					exit; 
				}
				$db->set_charset('cp1251');
				$date = date("Y-m-d");
				if ($db->query("UPDATE `services` SET `tarif_id`=".$data['tarif_id'].", `payday`='".$date."' WHERE `user_id`=".$user)===TRUE) {
					echo '{"result": "ok"}';
				} else {
					echo '{"result": "error"}';
				}
				$db->close();
			} else {
				echo '{"result": "error"}';
			}
		}
	} else {
		echo '{"result": "error"}';
	}
} else {
	printf("Empty request!");
}