<?php

namespace console\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property string  $deep_path
 * @property string  $filename
 * @property string  $add_time
 * @property string  $update_time
 * @property integer $state
 */
class Attachment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attachment';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->kake;
    }
}
