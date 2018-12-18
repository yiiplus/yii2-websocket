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
namespace yiiplus\websocket\cli;

use yii\console\Controller;

/**
 * WebSocket Server Command
 *
 * @property object  $websocket     WebSocket compoent，通过 bootstrap 引导注入
 * @property string  $host          WebSocket服务端HOST，默认为'0.0.0.0'，此参数可以在命令行指定
 * @property integer $port          WebSocket端口号，默认为'9501'，此参数可以在命令行指定
 * @property string  $defaultAction 默认方法
 * @property object  $_server       WebSocket Server
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
abstract class Command extends Controller
{
    /**
     * @var object WebSocket compoent
     */
    public $websocket;

    /**
     * @var string WebSocket host
     */
    public $host = '0.0.0.0';

    /**
     * @var integer WebSocket 端口号
     */
    public $port = 9501;

    /**
     * @var string 默认方法
     */
    public $defaultAction = 'start';

    /**
     * @var \Swoole\WebSocket\Server
     */
    protected $_server;

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
     * 获取 WebSocket channel list
     *
     * @return null
     */
    public function actionList()
    {
        echo 'channels:' . PHP_EOL;

        foreach ($this->websocket->channels as $key => $channel) {
            echo '   - ' . $key . PHP_EOL;
        }
    }

    /**
     * 当服务器收到来自客户端的数据帧时会回调此函数
     *
     * @param object $server WebSocket Server
     * @param object $frame  frame对象，包含了客户端发来的数据帧信息
     *
     * @return null
     */
    public function message($server, $frame)
    {
        $class = $this->channelResolve($frame->data);
        call_user_func([$class, 'execute'], $server, $frame);
    }

    /**
     * channel 解析
     *
     * @param json $data 客户端传来的数据
     *
     * @return string channel 类名
     */
    public function channelResolve($data)
    {
        // 获取 channel
        $data = json_decode($data);
        if (!is_object($data) || !property_exists($data, 'channel')) {
            echo '[error] missing client data.' . PHP_EOL;
            return false;
        }
        if (!array_key_exists($data->channel, $this->websocket->channels)) {
            echo '[error] channel parameter parsing failed.' . PHP_EOL;
            return false;
        }

        // 判断 channel 绑定的类是否存在
        $className = $this->websocket->channels[$data->channel];
        if (!class_exists($className)) {
            echo '[error] ' . $className . ' class not found.' . PHP_EOL;
            return false;
        }

        // 验证 channel 类是否规范
        $reflectionClass = new \ReflectionClass($className);
        $class = $reflectionClass->newInstance();
        if (!($class instanceof \yiiplus\websocket\Channel)) {
            echo '[error] ' . $class. ' must be a ChannelInterface instance instead.' . PHP_EOL;
            return false;
        }

        return $className;
    }
}
