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
 * @property integer $mqid
 * @property string $mqitem
 * @property string $mqcho
 * @property string $mqans
 * @property string $mqtail
 * @property string $mqrem
 * @property string $mqstatus
 */
class Choosem extends ActiveRecord
{
    public static function tableName()
    {
        return 'Choosem';
    }
    public function rules()
    {
        return [
            [['mqid','mqstatus'],'required'],
            [['mqid','mqstatus'],'integer'],
            [['mqitem','mqcho','mqtail','mqrem','mqans'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'mqid'=>'Mqid',
            'mqitem'=>'Mqitem',
            'mqcho'=>'Mqcho',
            'mqans'=>'Mqans',
            'mqtail'=>'Mqtail',
            'mqrem'=>'Mqrem',
            'mqstatus'=>'Mqtatus',
        ];
    }
}