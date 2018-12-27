# yii2-websocket

在 yii2 下运行 WebSocket 服务。

[![Latest Stable Version](https://poser.pugx.org/yiiplus/yii2-websocket/v/stable)](https://packagist.org/packages/yiiplus/yii2-websocket)
[![Total Downloads](https://poser.pugx.org/yiiplus/yii2-websocket/downloads)](https://packagist.org/packages/yiiplus/yii2-websocket)
[![License](https://poser.pugx.org/yiiplus/yii2-websocket/license)](https://packagist.org/packages/yiiplus/yii2-websocket)

## 驱动支持
- [swoole](docs/guide/dirver-swoole.md)
- ~~[workerman](docs/guide/dirver-workerman.md)~~

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

每个 channel 的功能都需要定义一个单独的类。例如，如果你需要为指定客户端推送一条消息，则该类可能如下所示：

```php
namespace xxx\channels;

class PushMessageChannel extends BaseObject implements \yiiplus\websocket\ChannelInterface
{
	public function execute($fd, $data)
	{
		return [
			$fd, // 第一个参数返回客户端ID，多个以数组形式返回
			$data // 第二个参数返回需要返回给客户端的消息
		];
	}

	public function close($fd)
	{
		return;
	}
}
```

以下是从客户端发送消息的方法：

```php
Yii::$app->websocket->send(['channel' => 'push-message', 'message' => '用户 xxx 送了一台飞机！']);
```

执行任务的确切方式取决于使用的驱动程序。 大多数驱动程序可以使用控制台命令运行，组件需要在应用程序中注册。

此命令启动一个守护进程，该守护进程维护一个 WebSocket Server，根据客户端发来的数据，处理相关 channel 的任务：

```bash
yii websocket/start
```

有关驱动程序特定控制台命令及其选项的更多详细信息，请参阅 [文档](docs/guide/)。
