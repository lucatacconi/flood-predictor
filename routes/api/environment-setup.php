<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Ramsey\Uuid\Uuid;

foreach (glob(__DIR__ . '/../classes/*.php') as $filename){
    require_once $filename;
}

$app->group('/admin', function (RouteCollectorProxy $group) {

    $group->group('/environment-setup', function (RouteCollectorProxy $subGroup) {

        $subGroup->post('/db-preset', function (Request $request, Response $response, array $args) {

            if(empty($_ENV['SENSOR_DATA_DB_PATH'])){
                throw new Exception('SENSOR_DATA_DB_PATH not set in .env file.', 5);
            }

            if(empty($_ENV['ERROR_LOG_PATH'])){
                throw new Exception('ERROR_LOG_PATH not set in .env file.', 10);
            }

            $aDBPRESETs = $this->get('configs')["app_configs"]["db-preset"];
            if(empty($aDBPRESETs)){
                throw new Exception("ERROR - DB structure configuration not found", 15);
            }

            //Parameters read from body
            $params = [];
            if(!empty($request->getParsedBody())){
                $params = array_change_key_case($request->getParsedBody(), CASE_UPPER);
            }

            if ( isset($params['SIMULATE'])){
                $params['SIMULATE'] = filter_var($params['SIMULATE'], FILTER_VALIDATE_BOOLEAN);
            }else{
                $params['SIMULATE'] = true;
            }

            //Db connection
            try{
                $pdo = new \PDO($_ENV['SENSOR_DATA_DB_TYPE'].':'.$_ENV['SENSOR_DATA_DB_PATH']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            }catch (PDOException $e){
                throw new Exception('DB Connection error.', 20);
            }


            $aDATA = [];

            foreach($aDBPRESETs as $aDBPRESET_table_name => $aDBPRESET){

                $row = [];
                $row['TABLE_NAME'] = $aDBPRESET_table_name;
                $row['DROP_TABLE'] = false;
                $row['TABLE_PRESENCE'] = false;
                $row['TABLE_CREATED'] = false;

                $aDBVAR = [];
                $aDBVAR['TYPE'] = 'table';
                $aDBVAR['TABLE_NAME'] = $aDBPRESET_table_name;

                $result = false;
                try {
                    $stmt = $pdo->prepare(' select count(*) CNT from sqlite_master WHERE type = :type and name = :table_name; ');
                    $stmt->bindParam(':type', $aDBVAR['TYPE'], PDO::PARAM_STR);
                    $stmt->bindParam(':table_name', $aDBVAR['TABLE_NAME'], PDO::PARAM_STR);

                    $stmt->execute();
                    $result = $stmt->fetch();
                }
                catch (PDOException $e) {
                    throw new Exception("DB operation's error: " . $e->getMessage(), 25);
                }

                if(empty($result)){
                    throw new Exception("DB operation's error: No result found.", 30);
                }

                if($result['CNT'] == 1){
                    $row['TABLE_PRESENCE'] = true;
                }

                if(!$params['SIMULATE']){

                    $table_presente = $row['TABLE_PRESENCE'];

                    if($row['TABLE_PRESENCE']){
                        $row['DROP_TABLE'] = $aDBPRESET['drop-if-exist'];

                        if($aDBPRESET['drop-if-exist']){

                            try {
                                $stmt = $pdo->prepare(' drop table '.$aDBVAR['TABLE_NAME'].'; ');
                                $stmt->execute();
                            }
                            catch (PDOException $e) {
                                throw new Exception("DB operation's error: " . $e->getMessage(), 35);
                            }

                            $table_presente = false;
                        }
                    }

                    if(!$table_presente){

                        try {
                            $stmt = $pdo->prepare($aDBPRESET['create-sql']);
                            $stmt->execute();
                        }
                        catch (PDOException $e) {
                            throw new Exception("DB operation's error: " . $e->getMessage(), 40);
                        }

                        $row['TABLE_CREATED'] = true;
                    }
                }

                $aDATA[] = $row;
            }

            $response->getBody()->write(json_encode($aDATA, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $response->withStatus(200)
                            ->withHeader("Content-Type", "application/json");
        });
    });
});
