<?php

function read_write_db($vars){

	if(isset($vars)){
		$type = $vars["type"];
		$action = $vars["action"];
		$table = $vars["table"];
		$column = $vars["column"];
		$select = is_array($vars["select"]) ? $vars["select"] : array("EID" => $vars["select"]);
		$json_nodes = $vars["json_nodes"];
		$values = $vars["values"];
		$decrypt = isset($vars["decrypt"]) ? $vars["decrypt"] : '';
	}else{
		echo 'keine vars';
	}
			
	global $db;
	
	switch ($action) {
		case "update":
			foreach ($select as $skey => $sentry) {
				$sql_query= $db->prepare("SELECT $column FROM $table where $skey LIKE $sentry");
				$sql_query->execute() or die(print_r($sql_query->errorInfo()));
				$sql_json = $sql_query->fetch(PDO::FETCH_ASSOC);

				include("tasks/update__".$type.".php");
				print_r("tasks/update__".$type.".php");
			}
		break;
		case "read":
			foreach ($select as $skey => $sentry) {
				$type = ($type == "json") ? $type : "";
				include("tasks/read__".$type.".php");
			}
		break;
		case "read_return":
			foreach ($select as $skey => $sentry) {
				$type = ($type == "json") ? $type : "";
				include("tasks/read_return__".$type.".php");
				return $json;
			}
		case "insert":
				$where_t = "";
				$value_t = "";
				
				$type = !empty($values) ? "" : $type;
				include("tasks/insert__".$type.".php");
		break;
		case "delete":
			include("tasks/delete__.php");
			
		break;
		case "mysql_update":
			include("tasks/mysql_update__.php");
		break;
	}
}
?>
