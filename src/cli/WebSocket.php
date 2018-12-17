<?php
namespace yiiplus\websocket\cli;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApp;
use yii\helpers\Inflector;
use yii\websocket\WebSocket as BaseWebSocket;

/**
 * Queue with CLI.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
abstract class WebSocket extends BaseWebSocket implements BootstrapInterface
{
	/**
     * @var string command class name
     */
    public $commandClass = Command::class;

	/**
     * @return string command id
     * @throws
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
     * @inheritdoc
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
