# 控制台执行

控制台用于启动 WebSocket 守护进程和查看 channel

## 启动 WebSocket 守护进程

```bash
yii websocket/start
```

`start` 命令用于启动一个守护进程，它可以处理各个 channel 的任务。

`start` 命令参数：

- --host, -h: 指定 WebSocket Server 的 host
- --port, -p: 指定 WebSocket Server 的端口号

## 查看 channel 列表

```bash
yii websocket/list
```