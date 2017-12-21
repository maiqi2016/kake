<?php

namespace console\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ad".
 *
 * @property integer $id
 * @property integer $attachment_id
 * @property integer $type
 * @property integer $target
 * @property string  $url
 * @property string  $remark
 * @property string  $from
 * @property string  $to
 * @property integer $sort
 * @property string  $add_time
 * @property string  $update_time
 * @property integer $state
 */
class Ad extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->kake;
    }
}