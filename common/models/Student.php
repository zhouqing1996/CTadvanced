<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * student model
 *
 * @property integer $mqid
 * @property string $mqitem
 * @property string $mqcho
 * @property string $mqans
 * @property string $mqtail
 * @property string $mqrem
 * @property string $mqstatus
 */
class Student extends ActiveRecord
{
    public static function tableName()
    {
        return 'Student';
    }
    public function rules()
    {
        return [
            [['sid','status'],'required'],
            [['sid','tid','cid','status'],'integer'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'sid'=>'Sid',
            'cid'=>'Cid',
            'tid'=>'Tid',
            'status'=>'Status',
        ];
    }
}
