# Workerman 驱动

驱动程序使用 Workerman 的 WebSocket。

您需要安装 [Workerman](https://www.workerman.net/) 扩展到你的php中。

## 配置实例

```php
return [
	'bootstrap' => [
		'websocket',
	],
	'compoents' => [
		'websocket' => [
			'class' => '\yiiplus\websocket\workerman\WebSocket',
			'host' => '127.0.0.1',
			'port' => 9501,
			'channels' => [
				...
			],
		],
	],
];
```

## 控制台启动 WebSocket 守护进程

```bash
yii websocket/start
```

`start` 命令用于启动一个守护进程，它可以处理各个 channel 的任务。

`start` 命令参数：

- --host, -h: 指定 WebSocket Server 的 host
- --port, -p: 指定 WebSocket Server 的端口号
- --worker_num, -w: 指定 worker 进程数
