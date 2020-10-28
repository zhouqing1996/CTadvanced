<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * course model
 *
 * @property integer $cid
 * @property string $cname
 * @property string $status
 */
class Course extends ActiveRecord
{
    public static function tableName()
    {
        return 'Course';
    }
    public function rules()
    {
        return [
            [['cid','status'],'required'],
            [['cid','status'],'integer'],
            [['cname'],'string',max=>255],
        ];
    }
    public function attributeLabels()
    {
        return [
            'cid'=>'Cid',
            'cname'=>'Cname',
            'status'=>'Status',
        ];
    }
}