<?php
namespace common\models;
use yii\db\ActiveRecord;

/**
 * Userans model
 *
 * @property integer $userid
 * @property integer $exid
 * @property integer $qid
 * @property integer $qtypeid
 * @property string $ans
 * @property float $grade
 * @property string $finishtime
 * @property integer $status
 */
class Userans extends ActiveRecord
{
    public static function tableName()
    {
        return 'Userans';
    }
    public function rules()
    {
        return [
            [['userid','status','exid','qid','qtypeid'],'required'],
            [['userid','status','exid','qid','qtypeid'],'integer'],
            [['ans','finishtime'],'string'],
            [['grade'],'float'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'userid'=>'Userid',
            'exid'=>'Exid',
            'qid'=>'Qid',
            'qtypeid'=>'Qtypeid',
            'ans'=>'Ans',
            'grade'=>'Grade',
            'finishtime'=>'Finishtime',
            'status'=>'Status',
        ];
    }
}
