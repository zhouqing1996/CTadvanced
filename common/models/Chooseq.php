<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Chooseq model
 *
 * @property integer $cqid
 * @property string $cqitem
 * @property string $cqcho
 * @property string $cqans
 * @property string $cqtail
 * @property string $cqrem
 * @property string $cqstatus
 */
class Chooseq extends ActiveRecord
{
    public static function tableName()
    {
        return 'Chooseq';
    }
    public function rules()
    {
        return [
            [['cqid','status'],'required'],
            [['cqid','status'],'integer'],
            [['cqans'],'string','max'=>255],
            [['cqitem','cqcho','cqtail','cqrem'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'cqid'=>'Cqid',
            'cqitem'=>'Cqitem',
            'cqcho'=>'Cqcho',
            'cqans'=>'Cqans',
            'cqtail'=>'Cqtail',
            'cqrem'=>'Cqrem',
            'cqstatus'=>'Cqtatus',
        ];
    }
}