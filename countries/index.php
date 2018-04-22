<?php

include "../models/CountryRepository.php";

$config = include("../db/config.php");
$db = new PDO($config["db"], $config["username"], $config["password"]);
$countries = new CountryRepository($db);


switch($_SERVER["REQUEST_METHOD"]) {
    case "GET":
        $result = $countries->getAll();
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