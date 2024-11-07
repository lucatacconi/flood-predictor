<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable("../");
$dotenv->load();

try {

    if(empty($_ENV['SENSOR_DATA_DB_PATH'])){
        throw new Exception('SENSOR_DATA_DB_PATH not set in .env file.');
    }

    if(empty($_ENV['ERROR_LOG_PATH'])){
        throw new Exception('ERROR_LOG_PATH not set in .env file.');
    }

    //Db connection
    $dbconn = new SQLite3($_ENV['SENSOR_DATA_DB_PATH']);

    //Log error file
    $error_log = fopen($_ENV['ERROR_LOG_PATH'], "a") or throw new Exception('Error file open failed.');


    //Create or reset DB

    $sql_drop = '
        DROP TABLE IF EXISTS "read_data_history";
    ';

    $result = $dbconn->query($sql_drop);

    if($result == false){
        fwrite($error_log, date('Y-m-d H:i:s')." - Error: " . $dbconn->lastErrorMsg());
        throw new Exception($dbconn->lastErrorMsg());
    }

    $sql_create = '
        CREATE TABLE "read_data_history" (
            "RDHS_ID" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
            "RDHS_READ_LOG" text NOT NULL,
            "RDHS_DATE_INS" text NOT NULL
        );
    ';

    $result = $dbconn->query($sql_create);

    if($result == false){
        fwrite($error_log, date('Y-m-d H:i:s')." - Error: " . $dbconn->lastErrorMsg());
        throw new Exception($dbconn->lastErrorMsg());
    }


} catch (Exception $e) {
    die('Error: '.  $e->getMessage());
}

echo 'DB Deployed';














