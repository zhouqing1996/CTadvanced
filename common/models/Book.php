<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Book model
 *
 * @property integer $bookid
 * @property string $bookname
 * @property string $publish
 * @property string $author
 * @property string $about
 * @property integer $status
 */
class Book extends ActiveRecord
{
    public static function tableName()
    {
        return 'book';
    }
    public function rules()
    {
        return [
            [['bookid','bookname','status'],'required'],
            [['bookid','status'],'integer'],
            [['bookname','publish','author'],'string','max'=>255],
            [['about'],'text'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'bookid' => 'BookID',
            'bookname' => 'Bookname',
            'publish' => 'Publish',
            'author' => 'Author',
            'about' => 'About',
            'status' => 'Status',
        ];
    }
}