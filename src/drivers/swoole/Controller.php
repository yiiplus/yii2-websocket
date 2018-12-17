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
namespace yiiplus\websocket\swoole;

use yii\base\Component;
use yiiplus\websocket\console\Controller as ConsoleController;

/**
 * WebSocket Server 命令行控制器基类
 *
 * 调用端通过在console主体下继承此类，实现相关回调方法，实现相关业务逻辑：
 *
 * ```php
 *  use yiiplus\websocket\controllers\WebSocketServerController;
 *
 *  class PushController extends WebSocketServerController
 *  {
 *       public function message(\Swoole\WebSocket\Server $server, $frame) 
 *       {
 *           foreach ($server->connections as $fd) {
 *               $server->push($fd, $frame->data);
 *           }
 *       }  
 *  }
 * ```
 *
 * 从命令行启动 WebSocket Server：
 *
 * ```bash
 *  ./yii push -h 173.18.19.1 -p 9503
 * ```
 *
 * @property string                   $host          WebSocket服务端HOST，默认为'0.0.0.0'，此参数可以在命令行指定
 * @property integer                  $port          WebSocket端口号，默认为'9501'，此参数可以在命令行指定
 * @property string                   $defaultAction 默认方法
 * @property \Swoole\WebSocket\Server $_server       WebSocket Server
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class Controller extends ConsoleController
{
    /**
     * @var \Swoole\WebSocket\Server
     */
	protected $_server;

    /**
     * 启动 WebSocket Server
     *
     * @return null
     */
    public function actionStart()
    {
        $this->_server = new \Swoole\WebSocket\Server($this->host, $this->port);

        $this->_server->on('handshake', [$this, 'user_handshake']);

        $this->_server->on('open', [$this, 'open']);

        $this->_server->on('message', [$this, 'message']);

        $this->_server->on('close', [$this, 'close']);

        echo 'websocket service has started, host is ' . $this->host . ' port is ' . $this->port . PHP_EOL;

        $this->_server->start();
    }

    /**
     * WebSocket建立连接后进行握手，通过onHandShake事件回调
     *
     * @param swoole_http_request  $request  Websocket请求
     * @param swoole_http_response $response Websocket响应
     *
     * @return bool 握手状态
     */
    public function user_handshake(\swoole_http_request $request, \swoole_http_response $response)
    {
        $sec_websocket_key = $request->header['sec-websocket-key'] ?? null;

        //自定定握手规则，没有设置则用系统内置的（只支持version:13的）
        if (!isset($sec_websocket_key))
        {
            //'Bad protocol implementation: it is not RFC6455.'
            $response->end();
            return false;
        }
        if (0 === preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $sec_websocket_key)
            || 16 !== strlen(base64_decode($sec_websocket_key))
        )
        {
            //Header Sec-WebSocket-Key is illegal;
            $response->end();
            return false;
        }

        $key = base64_encode(sha1($sec_websocket_key
            . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true));
        $headers = array(
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $key,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive'             => 'off',
        );
        foreach ($headers as $key => $val)
        {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();
        
        echo "server: handshake success with fd{$request->fd}\n";
        return true;
    }

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数；设置onHandShake回调函数后不会再触发onOpen事件，需要应用代码自行处理
     *
     * @param swoole_websocket_server $server  WebSocket Server
     * @param swoole_http_response    @request Websocket响应
     *
     * @return null
     */
    public function open(\swoole_websocket_server $server, \swoole_http_response $request) 
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     *
     * @param swoole_websocket_server $server WebSocket Server
     * @param swoole_websocket_frame. $frame  swoole_websocket_frame对象，包含了客户端发来的数据帧信息 
     *
     * @return null
     */
    public function message(\Swoole\WebSocket\Server $server, \swoole_websocket_frame $frame) 
    {
        echo "received " . strlen($frame->data) . " bytes\n";
    }

    /**
     * WebSocket客户端关闭后，在worker进程中回调此函数
     *
     * @param swoole_websocket_server $server WebSocket Server
     * @param integer                 $fd     连接的文件描述符
     *
     * @return null
     */
    public function close(\Swoole\WebSocket\Server $server, $fd) 
    {
        echo "client {$fd} closed\n";
    }
}
