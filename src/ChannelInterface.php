<?php
/**
 * yiiplus/yii2-websocket
 *
 * @category  PHP
 * @package   Yii2
 * @copyright 2018-2019 YiiPlus Ltd
 * @license   https://github.com/yiiplus/yii2-websocket/licence.txt Apache 2.0
 * @link      http://www.yiiplus.com
 */

namespace yiiplus\websocket;

/**
 * Channel Interface. 
 *
 * @author gengxiankun@126.com
 * @since 1.0.0
 */
interface ChannelInterface
{
    /**
     * 处理该 channel 的 WebSocket 信息
     *
     * @param integer $fd   客户端连接描述符
     * @param object  $data 客户端发送的服务器的消息内容
     */
    public function execute($fd, $data);

    /**
     * 客户端断开连接触发此方法
     *
     * @param integer $fd 客户端连接描述符
     */
    public function close($fd);
}
