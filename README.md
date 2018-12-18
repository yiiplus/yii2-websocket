# yii2-websocket

在 yii2 下运行 WebSocket 服务。

它目前支持基于 [swoole](www.swoole.com) 的 WebSocket 服务。

## 安装

安装此扩展程序的首选方法是通过 [composer](http://getcomposer.org/download/).

编辑运行

```bash
php composer.phar require --prefer-dist yiiplus/yii2-websocket "^1.0.0"
```

或添加配置到项目目录下的`composer.json`文件的 require 部分

```
"yiiplus/yii2-websocket": "^1.0.0"
```

## 基础使用

WebSocket服务端需要通过在console主体下继承 WebSocket 实现的`Controller`基础类，实现相关回调方法，实现相关业务逻辑：

```php
use yiiplus\websocket\swoole\Controller;

class PushController extends Controller
{
     public function message($server, $frame) 
     {
         foreach ($server->connections as $fd) {
             $server->push($fd, $frame->data);
         }
     }  
}
```

运行此命令，启动 WebSocket 服务：

```bash
yii push -h 173.18.19.1 -p 9503
```

使用WebSocket客户端，需要注册`WebSocket`组件类到 components 配置中：

```php
 'components' => [
     ...
     'websocket' => [
         'class' => 'yiiplus\websocket\swoole\WebSocketClient',
         'host' => '173.18.19.1',
         'port' => '9503',
         'path' => '/',
         'origin' => null,
     ],
     ...
 ],
```

然后通过 compoents 的方式调用：

```php
$websocketClient = \Yii::$app->websocket;
$websocketClient->connect();
$websocketClient->send('TEST');
```