<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Programq model
 *
 * @property integer $pqid
 * @property integer $pqtitle
 * @property string $pqback  题目背景
 * @property string $pqdescri 题目描述
 * @property string $pqinputB 输入格式
 * @property string $pqoutputB 输出格式
 * @property string $pqcase 输入输出样例
 * @property string $pqother 说明
 * @property string $pqauth 题目提供者
 * @property string $pqlabel 题目标签
 * @property string $pqrem 相关推荐
 * @property string $pqstatus 状态
 * @property string $pqans 答案
 */
class Programq extends ActiveRecord
{
    public static function tableName()
    {
        return 'Programq';
    }
    public function rules()
    {
        return [
            [['pqid','pqstatus'],'required'],
            [['pqid','pqauth','pqstatus'],'integer'],
            [['pqtitle','pqback','pqdescri','pqinputB','pqoutputB',
                'pqcase','pqother','pqlabel','pqrem','pqans'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'pqid'=>'Pqid',
            'pqtitle'=>'Pqtitle',
            'pqback'=>'Pqback',
            'pqdescri'=>'Pqdescri',
            'pqinputB'=>'PqinputB',
            'pqoutputB'=>'PqoutputB',
            'pqcase'=>'Pqcase',
            'pqother'=>'Pqother',
            'pqauth'=>'Pqauth',
            'pqlabel'=>'Pqlabel',
            'pqrem'=>'Pqrem',
            'pqstatus'=>'Pqstatus',
            'pqans'=>'Pqans',
        ];
    }
}
