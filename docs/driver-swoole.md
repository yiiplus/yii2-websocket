# Swoole 驱动

驱动程序使用 Swoole 的 WebSocket。

您需要安装 Swoole 扩展到你的php中。

在 [swoole example](../examples/swoole/) 上，实现了一个用户登陆，推送到所有客户端消息的例子。

客户端配置示例：

```php
 'components' => [
     ...
     'websocket' => [
         'class' => 'yiiplus\websocket\swoole\WebSocketClient',
         'host' => '173.18.19.1', // WebSocket host
         'port' => '9503', // WebSocket port
         'path' => '/', // WebSocket Request URI
         'origin' => null, // Header Origin
     ],
     ...
 ],
```
## Swoole WebSocket compoents

### 示例：

1. 连接 WebSocket Server

```php
\Yii::$app->websocket->connect();
```

2. 从客户端发送数据到 WebSocket 服务

```php
$websocketClient->send('TEST');
```

> `send`方法默认发送 text 格式的数据，此外还支持 binary bin ping 格式的数据，通过传入第二个参数指定即可。

## Swoole WebSocket Server

Swoole WebSocket服务端需要通过在console主体下继承 WebSocket 实现的 Swoole 的 `Controller`基础类，实现相关回调方法，实现相关业务逻辑：

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