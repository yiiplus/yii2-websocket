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

namespace yiiplus\websocket\swoole;

use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yiiplus\websocket\cli\Command as CliCommand;

/**
 * WebSocket Server Command
 *
 * @property \Swoole\WebSocket\Server $_server       WebSocket Server
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class Command extends CliCommand
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

        echo '[info] websocket service has started, host is ' . $this->host . ' port is ' . $this->port . PHP_EOL;

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
        
        echo '[info] handshake success with fd ' . $request->fd . PHP_EOL;
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
        echo '[info] new connection, fd' . $request->fd . PHP_EOL;
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     *
     * @param object $server WebSocket Server
     * @param object $frame  frame对象，包含了客户端发来的数据帧信息
     *
     * @return null/bool
     */
    public function message($server, $frame)
    {
        $result = $this->triggerMessage($frame->fd, $frame->data);

        if (!$result) {
            return false;
        }

        list($fds, $data) = $result;

        foreach ($fds as $fd) {
            if (!$server->push($fd, $data)) {
                echo '[error] client_id ' . $fd . ' send failure.' . PHP_EOL;
                return false;
            }
    
            echo '[success] client_id ' . $fd . ' send success.' . PHP_EOL;
        }
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
        $this->triggerClose($fd);

        echo '[closed] client '. $fd . ' closed' . PHP_EOL;
    }
}
