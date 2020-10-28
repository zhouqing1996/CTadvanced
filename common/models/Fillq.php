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
 * @property integer $fqid
 * @property string $fqitem
 * @property string $fqans
 * @property string $fqtail
 * @property string $fqrem
 * @property string $fqstatus
 */
class Fillq extends ActiveRecord
{
    public static function tableName()
    {
        return 'Fillq';
    }
    public function rules()
    {
        return [
            [['fqid','fqstatus'],'required'],
            [['fqid','fqstatus'],'integer'],
            [['fqitem','fqtail','fqrem','fqans'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'fqid'=>'Fqid',
            'fqitem'=>'Fqitem',
            'fqans'=>'Fqans',
            'fqtail'=>'Fqtail',
            'fqrem'=>'Fqrem',
            'fqstatus'=>'Fqtatus',
        ];
    }
}
