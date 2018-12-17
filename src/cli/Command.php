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

use yii\base\Component;
use yii\console\Controller;

/**
 * WebSocket Server 命令行控制器抽象类
 *
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
}
