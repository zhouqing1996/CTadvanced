<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Judge model
 *
 * @property integer $jqid
 * @property string $jqitem
 * @property string $jqans
 * @property string $jqtail
 * @property string $jqrem
 * @property string $jqstatus
 */
class Judge extends ActiveRecord
{
    public static function tableName()
    {
        return 'Judge';
    }
    public function rules()
    {
        return [
            [['jqid','jqstatus'],'required'],
            [['jqid','jqstatus','jqans'],'integer'],
            [['jqitem','jqtail','jqrem',],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'jqid'=>'Jqid',
            'jqitem'=>'Jqitem',
            'jqans'=>'Jqans',
            'jqtail'=>'Jqtail',
            'jqrem'=>'Jqrem',
            'jqstatus'=>'Jqtatus',
        ];
    }
}
