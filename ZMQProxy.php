<?php namespace RestExtension;

use CodeIgniter\Config\Config;
use Config\RestExtension;
use RestExtension\Core\Entity;

/**
 * Class ZMQProxy
 * @package RestExtension\ZMQ
 */
class ZMQProxy {

    const ZMQ_ACTION_CREATE = 'created';
    const ZMQ_ACTION_UPDATE = 'updated';
    const ZMQ_ACTION_DELETE = 'deleted';

    /** @var ZMQProxy */
    private static $instance;

    public static function getInstance(): ZMQProxy {
        if(!self::$instance) {
            self::$instance = new ZMQProxy();
            self::$instance->connect();
        }
        return self::$instance;
    }

    /** @var \ZMQContext */
    private $context;

    /** @var \ZMQSocket */
    private $socket;

    private function connect() {
        /** @var RestExtension $config */
        $config = Config::get('RestExtension');
        if(!isset($config->ZMQHost)) throw new \Exception("RestExtension, Config is missing \"ZMQHost\"");
        if(!isset($config->ZMQPort)) throw new \Exception("RestExtension, Config is missing \"ZMQPort\"");

        $this->context = new \ZMQContext();
        try {
            $this->socket = $this->context->getSocket(\ZMQ::SOCKET_PUSH);
            $this->socket->connect("tcp://{$config->ZMQHost}:{$config->ZMQPort}");
        } catch(\ZMQSocketException $e) {
            \DebugTool\Data::debug($e->getMessage());
        }
    }

    public function send(string $resourcePath, string $action, Entity $entity) {
        try {
            $this->socket->send(json_encode(
                [
                    'path' => $resourcePath,
                    'action' => $action,
                    'entity' => $entity->toArray()
                ]
            ));
        } catch(\ZMQSocketException $e) {
            \DebugTool\Data::debug($e->getMessage());
        }
    }

}
