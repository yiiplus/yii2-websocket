<?php
namespace channels;

use models\LiveRoom;
use yii\base\BaseObject;
use yiiplus\websocket\ChannelInterface;

class PushChannel extends BaseObject implements ChannelInterface
{
    /**
     * 发送消息到指定直播间
     *
     * @param interge $fd   客户端ID
     * @param string  $data 客户端数据
     *
     * @return array
     */
    public function execute($fd, $data)
    {
        $liveRoomIds = LiveRoom::find()->where(['room_id' => $data->room_id])->column('id');

        return [
            $liveRoomIds,
            $data->message
        ];
    }

    public function close($fd)
    {
        return;
    }
}