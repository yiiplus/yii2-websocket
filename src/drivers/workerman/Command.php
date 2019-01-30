<?php
/**
 * yiiplus/yii2-websocket
 *
 * @category  PHP
 * @package   Yii2
 * @copyright 2018-2019 YiiPlus Ltd
 * @license   https://github.com/yiiplus/yii2-websocket/licence.txt Apache 2.0
 * @link      http://www.yiiplus.com
 */

namespace yiiplus\websocket\workerman;

use Workerman\Worker;
use yiiplus\websocket\cli\Command as CliCommand;

/**
 * WebSocket Server Command
 *
 * @property /Worker $_server WebSocket Server
 * @property integer $_server 设置 worker 进程数
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

    /**
     * @var integer 设置 worker 进程数
     */
    public $worker_num = 4;

    /**
     * 指定命令行参数，增加 worker_num 参数选项
     *
     * @param string actionID
     *
     * @return array 返回指定的参数
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'worker_num'
        ]);
    }

    /**
     * 为命令行的参数设置别名
     *
     * @return array 参数别名键值对
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'w' => 'worker_num'
        ]);
    }

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

        // 设置启动参数
        global $argv;
        $argv[1] = 'start';

        Worker::runAll();
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
     *
     * @param object $connection 客户端连接对象
     *
     * @return null
     */
    public function connect($connection) 
    {
        echo '[info] new connection, fd' . $connection->id . PHP_EOL;
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     *
     * @param object $connection 客户端连接对象
     * @param string $data       客户端发送的数据
     *
     * @return bool/null
     */
    public function message($connection, $data)
    {
        $result = $this->triggerMessage($connection->fd, $data);

        if (!$result) {
            return false;
        }

        list($fds, $data) = $result;

        foreach ($fds as $fd) {
            if (!$this->_server->connections[$fd]->send($data)) {
                echo '[error] client_id ' . $fd . ' send failure.' . PHP_EOL;
                return false;
            }

            echo '[success] client_id ' . $fd . ' send success.' . PHP_EOL;
        }
    }

    /**
     * 客户端断开连接
     *
     * @param object $connection 客户端连接对象
     *
     * @return null
     */
    public function close($connection)
    {
        $this->triggerClose($connection->id);

        echo '[closed] client '. $connection->id . ' closed' . PHP_EOL;
    }
}
