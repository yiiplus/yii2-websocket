# 基本使用

## 配置

```php
return [
    'bootstrap' => [
        'websocket',
    ],
    'compoents' => [
        'websocket' => [
            'class' => '\yiiplus\websocket\<dirver>\WebSocket',
            'host' => '127.0.0.1',
            'port' => 9501,
            'channels' => [
                'push-message' => '\xxx\channels\PushMessageChannel', // 配置 channel 对应的执行类
            ],
      ],
    ],
];
```

## 定义 channel 执行类

每个 channel 的功能都需要定义一个单独的类，WebSocket Server 会通过客户端传来的 channel 参数解析。

例如，如果你需要为所有客户端推送一条消息，则该类可能如下所示：

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

> 定义好的执行类需要注册到 compoents 配置中的 [channel](#配置) 下。

当客户端断开连接时会触发所有 channels 下的 `close` 方法，用于清理客户端在服务器上与业务的绑定关系。

## 客户端发送 channel 消息，触发任务

```php
Yii::$app->websocket->send(['channel' => 'push-message', 'message' => '用户 xxx 送了一台飞机！']);
```

## 控制台执行

执行任务的确切方式取决于使用的驱动程序。 大多数驱动程序可以使用控制台命令运行，组件需要在应用程序中注册。

此命令启动一个守护进程，该守护进程维护一个 WebSocket Server，根据客户端发来的数据，处理相关 channel 的任务：

```bash
yii websocket/start
```
