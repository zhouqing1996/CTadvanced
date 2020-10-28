<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Examtail model
 *
 * @property integer $exid
 * @property string $qid
 * @property string $qtypeid
 * @property string $exstatus
 */
class Examtail extends ActiveRecord
{
    public static function tableName()
    {
        return 'Examtail';
    }
    public function rules()
    {
        return [
            [['exid','exstatus','qid','qtypeid'],'required'],
            [['exid','exstatus','qid','qtypeid'],'integer'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'exid'=>'Exid',
            'qid'=>'Qid',
            'qtypeid'=>'Qtypeid',
            'exstatus'=>'Exstatus',
        ];
    }
}
