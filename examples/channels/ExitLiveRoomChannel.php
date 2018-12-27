<?php
namespace channels;

use models\LiveRoom;
use yii\base\BaseObject;
use yiiplus\websocket\ChannelInterface;

class ExitLiveRoomChannel extends BaseObject implements ChannelInterface
{
    public function execute($fd, $data) {
        return;
    }

    /**
     * 退出直播间解绑关联关系
     *
     * @param interge $fd 客户端ID
     *
     * @return null
     */
    public function close($fd)
    {
        LiveRoom::deleteAll(['id' => $fd]);
    }
}
