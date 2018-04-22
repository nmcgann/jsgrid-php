<?php

include "../models/ClientRepository.php";

$config = include("../db/config.php");
$db = new PDO($config["db"], $config["username"], $config["password"]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$clients = new ClientRepository($db);

switch($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $data = [
            "name" => $_GET["name"],
            "address" => $_GET["address"],
            "country_id" => intval($_GET["country_id"]),
            "pageIndex" => intval($_GET["pageIndex"]),
            "pageSize" => intval($_GET["pageSize"]),
        ];
        $result = $clients->getAll($data);
        break;

    case "POST":
        $data = [
            "name" => $_POST["name"],
            "age" => intval($_POST["age"]),
            "address" => $_POST["address"],
            "married" => $_POST["married"] === "true" ? 1 : 0,
            "country_id" => intval($_POST["country_id"])
        ];
        $result = $clients->insert($data);
        break;

    case "PUT":
        parse_str(file_get_contents("php://input"), $_PUT);
        $data = [
            "id" => intval($_PUT["id"]),
            "name" => $_PUT["name"],
            "age" => intval($_PUT["age"]),
            "address" => $_PUT["address"],
            "married" => $_PUT["married"] === "true" ? 1 : 0,
            "country_id" => intval($_PUT["country_id"])
        ];
        $result = $clients->update($data);
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $_DELETE);

        $result = $clients->remove(intval($_DELETE["id"]));
        break;
    
    default:
        $result = [false, 'Unrecognised request'];   
}

if($result[0] === false){
    //error code for front end to handle
    http_response_code(422);    
}

header("Content-Type: application/json");
echo json_encode($result[1]);

/* end */
