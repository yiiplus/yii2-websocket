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
namespace yiiplus\websocket\console;

use yii\base\Component;
use yii\console\Controller as BaseController;

/**
 * WebSocket Server 命令行控制器基类
 *
 * @property string  $host          WebSocket服务端HOST，默认为'0.0.0.0'，此参数可以在命令行指定
 * @property integer $port          WebSocket端口号，默认为'9501'，此参数可以在命令行指定
 * @property string  $defaultAction 默认方法
 * @property object  $_server       WebSocket Server
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class Controller extends BaseController
{
    /**
     * @var string Websocket host
     */
    public $host = '0.0.0.0';

    /**
     * @var integer Websocket端口号
     */
    public $port = 9501;

    /**
     * @var string 默认方法
     */
    public $defaultAction = 'start';

    /**
     * @var \Swoole\WebSocket\Server
     */
	abstract protected $_server;

    /**
     * 指定命令行参数
     *
     * @param string actionID
     *
     * @return array 返回指定的参数
     */
    public function options($actionID)
    {
        return [
            'host',
            'port'
        ];
    }

    /**
     * 为命令行的参数设置别名
     *
     * @return array 参数别名键值对
     */
    public function optionAliases()
    {
        return [
            'h' => 'host',
            'p' => 'port',
        ];
    }

    /**
     * 启动 WebSocket Server
     *
     * @return null
     */
    abstract public function actionStart();

    /**
     * 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数；设置onHandShake回调函数后不会再触发onOpen事件，需要应用代码自行处理
     *
     * @param Object $server  WebSocket Server
     * @param Object $request Websocket响应
     *
     * @return null
     */
    abstract public function open($server, $request) ;

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     *
     * @param object $server WebSocket Server
     * @param object $frame  swoole_websocket_frame对象，包含了客户端发来的数据帧信息 
     *
     * @return null
     */
    abstract public function message($server, $frame);

    /**
     * WebSocket客户端关闭后，在worker进程中回调此函数
     *
     * @param object  $server WebSocket Server
     * @param integer $fd     连接的文件描述符
     *
     * @return null
     */
    abstract public function close($server, $fd);
}
