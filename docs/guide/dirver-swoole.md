# Swoole 驱动

驱动程序使用 Swoole 的 WebSocket。

您需要安装 [Swoole](https://www.swoole.com/) 扩展到你的php中。

## 配置实例

```php
return [
	'bootstrap' => [
		'websocket',
	],
	'compoents' => [
		'websocket' => [
			'class' => '\yiiplus\websocket\swoole\WebSocket',
			'host' => '127.0.0.1',
			'port' => 9501,
			'channels' => [
				...
			],
		],
	],
];
```
