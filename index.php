<?php

error_reporting(E_ALL);

try {

    include __DIR__ . "/services.php";

    require_once 'ShortOID.php';

    $app = new \Phalcon\Mvc\Micro($di);

    $app->get('/', function(){
        //phpinfo();
        $mid = new MongoId();
        echo 'mid= ' . $mid . '</br>';
        $fid = ShortOID::fixMongoId($mid);
        echo 'fid = ' . $fid . '</br>';
        $sid = ShortOID::encode($mid);
        echo 'sid = ' . $sid . '</br>';
        $rid = ShortOID::decode($sid);
        echo 'rid = ' . $rid . '</br>';
    });

    /*
        Create a new object
     */
    $app->post('/objects/{name}', function($name){
        // 数据保存在 $GLOBALS['HTTP_RAW_POST_DATA'] 中
        // http请求必须设置 Content-Type: application/json
        $json = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = json_decode($json, true);
        ShortOID::revert($data, true);
        try{
            $collection = (new MumuObject())->getConnection()->selectCollection($name);
            $collection->insert($data);
            echo 'success';
        }catch (\Exception $e){
            echo 'faild';
        }
    });

    /*
        Updating Objects
    */
    $app->put('/objects/{name}/{id}', function($name, $id){
        // put数据使用 file_get_contents("php://input") 获取
        // http请求必须设置 Content-Type: application/json
        $json = file_get_contents("php://input");
        $data = json_decode($json);
        if(ShortOID::revert($data, false)){
            $query = array('_id' => $data['_id']);
        }else{
            $objectId = ShortOID::decode($id);
            $query = array('_id' => new MongoId($objectId));
        }

        try{
            $collection = (new MumuObject())->getConnection()->selectCollection($name);
            $newData = array('$set' => $data);
            $result = $collection->update($query, $newData);
            echo 'success';
        }catch (\Exception $e){
            echo 'faild';
        }
    });


    /*
        Retrieving Objects
    */
    $app->get('/objects/{name}/{id}', function($name, $id){
        $objectId = ShortOID::decode($id);
        try{
            $collection = (new MumuObject())->getConnection()->selectCollection($name);
            $query = array('_id' => new MongoId($objectId));
            $result = $collection->findOne($query);
            ShortOID::replace($result); 
            echo json_encode($result);
        }catch (\Exception $e){
            echo 'faild';
        }
    });


    /*
        Queries
    */
    $app->get('/objects/{name}', function($name){
        // get参数为 where={json}，并使用urlencode处理
        try{
            $collection = (new MumuObject())->getConnection()->selectCollection($name);
            $query = $_GET['where'];
            $cursor = $collection->find(json_decode($query));

            $result = '[';
            foreach ($cursor as $doc) {
                ShortOID::replace($doc);
                $result .= json_encode($doc);
                if($cursor->hasNext()){
                    $result .= ',';
                }
            }
            $result .= ']';
            echo $result;
        }catch (\Exception $e){
            echo 'faild';
        }
    });


    /*
        Deleting Objects
    */
    $app->delete('/objects/{name}/{id}', function($name, $id){
        $objectId = ShortOID::decode($id);
        try{
            $collection = (new MumuObject())->getConnection()->selectCollection($name);
            $query = array('_id' => new MongoId($objectId));
            $result = $collection->remove($query);
            echo 'success';
        }catch (\Exception $e){
            echo 'faild';
        }
    });

    /*
        404, not found
    */
    $app->notFound(function () use ($app) {
        $app->response->setStatusCode(404, "Not Found")->sendHeaders();
        echo 'This is crazy, but this page was not found!';
        echo '<br><br>';
    });

    $app->handle();

} catch (\Exception $e) {
    echo $e->getMessage();
}
