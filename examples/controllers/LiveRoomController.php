<?php

namespace examples\controllers;

use Yii;

class LiveRoomController extends \api\modules\Controller
{
    /**
     * 将消息推送到指定直播间直播间
     *
     * @return bool
     */
    public function actionPushMessage()
    {
        Yii::$app->websocket->send([
            'channel' => 'push',
            'room_id' => Yii::$app->request->get('room_id', 1),
            'message' => Yii::$app->request->get('message', '用户 Gunn 送给主播 象拔河 一架飞机！')
        ]);

        return true;
    }
}
