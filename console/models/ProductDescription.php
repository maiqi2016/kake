<?php

namespace console\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "product_description".
 *
 * @property integer $id
 * @property string  $cost
 * @property string  $recommend
 * @property string  $use
 * @property string  $back
 */
class ProductDescription extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product_description';
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->kake;
    }
}