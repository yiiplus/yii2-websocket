# 注册组件

如果要使用这个扩展必须向下面这样配置它

```php
return [
	'bootstrap' => [
		'websocket', // 把这个组件注册到控制台
	],
	'components' => [
		'websocket' => [
			'class' => '\yiiplus\websocket\<dirver>\WebSocket',
			'host' => '127.0.0.1',
			'port' => 9501,
			'channel' => [
				... // 用于配置 channel 对应的执行类
			],
		]
	],
];
```

可用的驱动程序列表及其配置文档在[README](README.md)目录中。