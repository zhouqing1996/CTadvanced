<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Exam model
 *
 * @property integer $exid
 * @property string $exname
 * @property string $createtime
 * @property string $ayth
 * @property string $exstatus
 */
class Exam extends ActiveRecord
{
    public static function tableName()
    {
        return 'Exam';
    }
    public function rules()
    {
        return [
            [['exid','exname','exstatus','createtime'],'required'],
            [['exid','exstatus','auth'],'integer'],
            [['createtime','exname'],'string',max=>255],
        ];
    }
    public function attributeLabels()
    {
        return [
            'exid'=>'Exid',
            'exname'=>'Exname',
            'createtime'=>'Createtime',
            'auth'=>'Auth',
            'exstatus'=>'Exstatus',
        ];
    }
}
