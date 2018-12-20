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
use Workerman\Connection\AsyncTcpConnection;
use yiiplus\websocket\cli\WebSocket as CliWebSocket;

/**
 * Swoole WebSocket 客户端组建类
 *
 * 使用WebSocket客户端，需要注册此类到components配置中：
 *
 * ```php
 *  'components' => [
 *      ...
 *      'websocket_client' => [
 *          'class' => 'yiiplus\websocket\components\WebSocketClient',
 *          'host' => '127.0.0.1',
 *          'port' => '9501',
 *          'path' => '/',
 *          'origin' => null,
 *      ],
 *      ...
 *  ],
 * ```
 *
 * 然后通过components的方式调用：
 *
 * ```php
 *  $websocketClient = \Yii::$app->websocket_client;
 *  $websocketClient->connect();
 *  $websocketClient->send('TEST');
 * ```
 *
 * @property string        $host           WebSocket服务端HOST，此参数必须在components配置中设置
 * @property integer       $port           WebSocket服务端端口号，此参数必须在components配置中设置
 * @property string        $path           WebSocket Request-URI，默认为'/'，可通过components配置设置此参数
 * @property string        $origin         string Header Origin，默认为null，可通过components配置设置此参数
 * @property mixed         $returnData     返回数据
 * @property mixed         $_key Websocket Sec-WebSocket-Key
 * @property swoole_client $_socket        WebSocket客户端
 * @property mixed         $_buffer        用于对`recv`方法获取服务器接受到的数据进行缓存
 * @property mixed         $_connected     链接的状态
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class WebSocket extends CliWebSocket
{
    /**
     * @var string WebSocket服务端HOST
     */
    public $host;

    /**
     * @var integer WebSocket服务端端口号
     */
    public $port;

    /**
     * 对象初始化
     */
    public function init()
    {
    	parent::init();

        if (!isset($this->host)) {
            throw new InvalidParamException('Host parameter does not exist.');
        }

        if (!isset($this->port)) {
            throw new InvalidParamException('Port parameter does not exist.');
        }

        $worker = new Worker();

        $worker->onWorkerStart = function($worker){

           $con = new AsyncTcpConnection('ws://' . $this->host . ':' . $this->port);

            $con->onConnect = function($con) {
                $con->send('hello');
            };

            $con->onMessage = function($con, $data) {
                echo $data;
            };

            $con->connect();
        };

        Worker::runAll();
    }
}
