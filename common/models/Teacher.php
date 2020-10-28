<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * teacher model
 *
 * @property integer $tid
 * @property integer $cid
 * @property integer $status
 */
class Teacher extends ActiveRecord
{
    public static function tableName()
    {
        return 'Teacher';
    }
    public function rules()
    {
        return [
            [['tid','status'],'required'],
            [['tid','cid','status'],'integer'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'tid'=>"Tid",
            'cid'=>'Cid',
            'status'=>'Status',
        ];
    }
}