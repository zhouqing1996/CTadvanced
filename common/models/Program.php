<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Fillq model
 *
 * @property integer $pqid
 * @property string $pqitem
 * @property string $pqans
 * @property string $pqtail
 * @property string $pqrem
 * @property string $pqstatus
 */
class Program extends ActiveRecord
{
    public static function tableName()
    {
        return 'Program';
    }
    public function rules()
    {
        return [
            [['pqid','pqstatus'],'required'],
            [['pqid','pqstatus'],'integer'],
            [['pqitem','pqtail','pqrem','pqans'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'pqid'=>'Pqid',
            'pqitem'=>'Pqitem',
            'pqans'=>'Pqans',
            'pqtail'=>'Pqtail',
            'pqrem'=>'Pqrem',
            'pqstatus'=>'Pqtatus',
        ];
    }
}
