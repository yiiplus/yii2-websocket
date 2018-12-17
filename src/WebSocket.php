<?php
namespace yiiplus\websocket;

use Yii;
use yii\base\Component;

abstract class WebSocket extends Component
{
	abstract public function connect();

	abstract public function send($data, $type = 'text', $masked = false);
}
