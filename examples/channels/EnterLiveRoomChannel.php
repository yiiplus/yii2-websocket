<?php
namespace channels;

use Yii;
use models\LiveRoom;
use yii\base\BaseObject;
use yiiplus\websocket\ChannelInterface;

class EnterLiveRoomChannel extends BaseObject implements ChannelInterface
{
    /**
     * 进入直播间绑定关联关系
     *
     * @param interge $fd   客户端ID
     * @param string  $data 客户端数据
     *
     * @return null
     */
    public function execute($fd, $data)
    {
        $liveRoomModel = new LiveRoom();

        $liveRoomModel->id = $fd;
        $liveRoomModel->room_id = $data->room_id;
        $liveRoomModel->uid = $data->uid;

        $liveRoomModel->save();
    }

    public function close($fd)
    {
        return;
    }
}
