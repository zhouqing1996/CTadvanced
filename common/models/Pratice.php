<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Pratice model
 *
 * @property integer $id
 * @property string $userid
 * @property string $qid
 * @property string $qtypeid
 * @property string $ans
 * @property string $finishtime
 * @property string $status
 */
class Pratice extends ActiveRecord
{
    public static function tableName()
    {
        return 'Pratice';
    }
    public function rules()
    {
        return [
            [['id','userid','qid','qtypeid','status'],'integer'],
            [['finishtime','ans'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'id'=>'ID',
            'userid'=>'Userid',
            'qid'=>'Qid',
            'qtypeid'=>'Qtypeid',
            'ans'=>'Ans',
            'finishtime'=>'Finishtime',
            'status'=>'Status',
        ];
    }
}
