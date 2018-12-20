<?php
/**
 * yii2-websocket
 *
 * @category  PHP
 * @package   Yii2
 * @copyright 2006-2018 YiiPlus Ltd
 * @license   https://github.com/yiiplus/yii2-websocket/licence.txt Apache 2.0
 * @link      http://www.yiiplus.com
 */
namespace yiiplus\websocket\workerman;

use Workerman\Worker;
use yiiplus\websocket\cli\Command as CliCommand;

/**
 * WebSocket Server Command
 *
 * 从命令行启动 WebSocket Server：
 *
 * ```bash
 *  yii websocket/start -h 173.18.19.1 -p 9503
 * ```
 *
 * @property Worker $_server       WebSocket Server
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class Command extends CliCommand
{
    /**
     * @var Worker WebSocket Server
     */
	protected $_server;

    public $worker_num = 4;

    /**
     * 启动 WebSocket Server
     *
     * @return null
     */
    public function actionStart()
    {
        $this->_server = new Worker('websocket://' . $this->host . ':' . $this->port);

        $this->_server->count = $this->worker_num;

        $this->_server->onConnect = [$this, 'connect'];

        $this->_server->onMessage = [$this, 'message'];

        $this->_server->onClose = [$this, 'close'];

        echo '[info] websocket service has started, host is ' . $this->host . ' port is ' . $this->port . PHP_EOL;

        Worker::runAll();
    }

    public function connect($connection) 
    {
        echo "[info] new connection\n";
    }

    public function message($connection, $data)
    {
        $connection->send('hello ' . $data);
    }

    public function close($connection)
    {
        echo "[close] connection closed\n";
    }
}
