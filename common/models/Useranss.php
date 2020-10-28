<?php
namespace common\models;
use yii\db\ActiveRecord;

/**
 * Useranss model
 *
 * @property integer $userid
 * @property integer $exid
 * @property float $grade
 * @property string $finishtime
 * @property integer $status
 */
class Useranss extends ActiveRecord
{
    public static function tableName()
    {
        return 'Useranss';
    }
    public function rules()
    {
        return [
            [['userid','status','exid'],'required'],
            [['userid','status','exid'],'integer'],
            [['finishtime'],'string'],
            [['grade'],'float'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'userid'=>'Userid',
            'exid'=>'Exid',
            'grade'=>'Grade',
            'finishtime'=>'Finishtime',
            'status'=>'Status',
        ];
    }
}
