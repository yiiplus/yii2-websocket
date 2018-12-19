# yii2-websocket

在 yii2 下运行 WebSocket 服务。

它目前支持基于 [swoole](https://www.swoole.com) 的 WebSocket 服务。

文档位于 [docs/guide/README.md](docs/guide/README.md)。

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

## 基本使用

每个 channel 的功能都需要定义一个单独的类。例如，如果你需要为所有客户端推送一条消息，则该类可能如下所示：

```php
namespace common\channels;

class PushMessageChannel extends BaseObject implements \yiiplus\websocket\ChannelInterface
{
  public function execute($server, $frame)
  {
	foreach ($server->connections as $fd) {
	  $server->push($fd, json_decode($frame->data)->message);
	}
  }
}
```

以下是从客户端发送消息的方法：

```php
$websocket = Yii::$app->websocket;

$websocket->connect;

$websocket->send(json_encode([
  'channel' => 'push-message', // 指定渠道，需要提前配置渠道对应的 channel 类
  'message' => '用户 xxx 送了一台飞机！'
]));
```

执行任务的确切方式取决于使用的驱动程序。 大多数驱动程序可以使用控制台命令运行，组件需要在应用程序中注册。

此命令启动一个守护进程，该守护进程维护一个 WebSocket Server，根据客户端发来的数据，处理相关 channel 的任务：

```bash
yii websocket/start
```

有关驱动程序特定控制台命令及其选项的更多详细信息，请参阅文档。