<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Qtype model
 *
 * @property integer $typeid
 * @property string $typename
 */
class Qtype extends ActiveRecord
{
    public static function tableName()
    {
        return 'qtype';
    }
    public function rules()
    {
        return [
            [['typeid','typename'],'required'],
            [['typeid'],'integer'],
            [['typename'],'string','max'=>255],
        ];
    }
    public function attributeLabels()
    {
        return [
            'typeid' => 'TypeID',
            'typename' => 'Typename',
        ];
    }
}