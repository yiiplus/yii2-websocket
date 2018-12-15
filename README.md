# yii2-websocket
使用yii2封装 WebSocket 扩展

## 安装

安装此扩展程序的首选方法是通过 [composer](http://getcomposer.org/download/).

编辑运行

```bash
php composer.phar require --prefer-dist yiiplus/yii2-websocket "^1.0.0"
```

或添加配置到项目目录下的composer.json文件的require部分

```
"yiiplus/yii2-websocket": "^1.0.0"
```

## WebSocket Server

调用端通过在console主体下继承`WebSocketServerController`类，实现相关回调方法，实现相关业务逻辑：

```php
use yiiplus\websocket\controllers\WebSocketServerController;

class PushController extends WebSocketServerController
{
     public function message(\Swoole\WebSocket\Server $server, $frame) 
     {
         foreach ($server->connections as $fd) {
             $server->push($fd, $frame->data);
         }
     }  
}
```

命令行启动 WebSocket Server：

```bash
./yii push -h 173.18.19.1 -p 9503
```

## WebSocket Client

使用WebSocket客户端，需要注册`WebSocketClient`类到components配置中：

```php
 'components' => [
     ...
     'websocket_client' => [
         'class' => 'yiiplus\websocket\components\WebSocketClient',
         'host' => '173.18.19.1',
         'port' => '9503',
         'path' => '/',
         'origin' => null,
     ],
     ...
 ],
```

然后通过components的方式调用：

```php
$websocketClient = \Yii::$app->websocket_client;
$websocketClient->connect();
$websocketClient->send('TEST');
```
