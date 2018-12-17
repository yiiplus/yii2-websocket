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

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApp;
use yii\helpers\Inflector;
use yiiplus\websocket\WebSocket as BaseWebSocket;

/**
 * WebSocket Client 抽象类
 *
 * @author gengxiankun@126.com
 * @since 1.0.0
 */
abstract class WebSocket extends BaseWebSocket implements BootstrapInterface
{
	/**
     * FIXME @var string command class name
     */
    public $commandClass = Command::class;

	/**
     * FIXME 获取CommandId
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
        throw new InvalidConfigException('Queue must be an application component.');
    }

    /**
     * FIXME ConsoleApp引导
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
