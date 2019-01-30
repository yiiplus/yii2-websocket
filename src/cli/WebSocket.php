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

namespace yiiplus\websocket\cli;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApp;
use yii\helpers\Inflector;
use yiiplus\websocket\WebSocket as BaseWebSocket;


/**
 * WebSocket Client 抽象类
 *
 * @property string $commandClass   command class name
 * @property array  $commandOptions of additional options of command
 * @property array  $channels       channel 执行类
 *
 * @author gengxiankun@126.com
 * @since 1.0.0
 */
abstract class WebSocket extends BaseWebSocket implements BootstrapInterface
{
	/**
     * @var string command class name
     */
    public $commandClass = Command::class;

    /**
     * @var array of additional options of command
     */
    public $commandOptions = [];

    /**
     * @var array channel 执行类
     */
    public $channels = [];

	/**
     * 获取CommandId
     *
     * @return string command id
     *
     * @throws yii\base\InvalidConfigException
     */
    protected function getCommandId()
    {
        foreach (Yii::$app->getComponents(false) as $id => $component) {
            if ($component === $this) {
                return Inflector::camel2id($id);
            }
        }
        throw new InvalidConfigException('WebSocket must be an application component.');
    }

    /**
     * ConsoleApp 引导程序
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApp) {
            $app->controllerMap[$this->getCommandId()] = [
                'class' => $this->commandClass,
                'websocket' => $this,
            ] + $this->commandOptions;
        }
    }
}
