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

use Yii;
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
 *      'websocket' => [
 *          'class' => 'yiiplus\websocket\workerman\WebSocket',
 *          'host' => '127.0.0.1',
 *          'port' => '9501',
 *      ],
 *      ...
 *  ],
 * ```
 *
 * 然后通过components的方式调用：
 *
 * ```php
 *  $websocketClient = \Yii::$app->websocket;
 *  $websocketClient->send(['channel' => 'yiiplus', 'message' => 'hello websocket!']);
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
     * @var string WebSocket 服务端HOST
     */
    public $host;

    /**
     * @var integer WebSocket 服务端端口号
     */
    public $port;

    /**
     * @var string WebSocket Request-URI
     */
    public $path = '/';

    /**
     * @var string Header Origin
     */
    public $origin = null;

    /**
     * @var array 客户端组件类配置，因为 workerman 不支持 php-fpm 运行环境下的同步客户端，所以 workerman 的使用 swoole 驱动的客户端
     */
    public $client = [
        'class' => 'yiiplus\websocket\swoole\WebSocket'
    ];

    /**
     * @var string command class name
     */
    public $commandClass = Command::class;

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
    }

    /**
     * 向服务器发送数据
     *
     * @param string $data   发送的数据
     * @param string $type   发送的类型
     * @param bool   $masked 是否设置掩码
     *
     * @return bool 是否发送成功的状态
     */
    public function send($data, $type = 'text', $masked = true)
    {
        // 创建 swoole WebSocket 客户端发送数据
        return Yii::createObject([
            'class' => $this->client['class'],
            'host' => $this->host,
            'port' => $this->port,
            'path' => $this->path,
            'origin' => $this->origin,
        ])->send($data, $type, $masked);
    }
}
