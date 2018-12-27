<?php
namespace models;

use yii\redis\ActiveRecord;

class LiveRoom extends ActiveRecord
{
    /**
     * 主键 默认为 id
     *
     * @return array|string[]
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * LiveRoom 属性列表
     *
     * @return array
     */
    public function attributes()
    {
        return ['id', 'room_id', 'uid', 'created_at', 'updated_at'];
    }
}