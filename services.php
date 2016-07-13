<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;

define('OBJECTID', 'objectId');

$config = new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'mongo',
        'host'        => 'localhost',
        'username'    => '',
        'password'    => '',
        'dbname'      => 'fly3w',
        'charset'     => 'utf8'
    ),
    'application' => array(
        'controllersDir' => './controllers/',
        'modelsDir'      => './models/',
        'baseUri'        => '/'
    )
));

$loader = new \Phalcon\Loader();

$loader->registerDirs(
    array(
        $config->application->controllersDir,
        $config->application->modelsDir
    )
)->register();

$di = new FactoryDefault();

$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
}, true);

$di->set('mongo', function() use ($config) {
    $mongo = new MongoClient();
    return $mongo->selectDB($config->database->dbname);
}, true);

$di->set('collectionManager', function(){
  return new Phalcon\Mvc\Collection\Manager();
}, true);

$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});

$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();
    return $session;
});
