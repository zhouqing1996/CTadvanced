<?php

namespace backend\module\exam\controllers;

use phpDocumentor\Reflection\Types\This;
use yii\db\Exception;
use yii\db\Query;
use yii\web\Controller;
use yii\common\models\Exam;
use yii\common\models\Examtail;

/*
 * 教师对评测的操作
 */
class TeacherController extends Controller
{

    /*
     * 查看全部的试卷
     * 标志：flag
     * 1：有效试卷
     * 2：已删除试卷
     * 3：所有试卷
     * 4:模糊查找
     */
    public function actionQueryexamt()
    {
        $request=\Yii::$app->request;
        $flag = $request->post("flag");
        $tid = $request->post('tid');
        if($flag==1)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->where(['exstatus'=>1])
                ->andWhere(['auth'=>$tid])
                ->all();
            return array("data"=>$query,"msg"=>"有效试卷");
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->where(['exstatus'=>0])
                ->andWhere(['auth'=>$tid])
                ->all();
            return array("data"=>$query,"msg"=>"已删除试卷");
        }
        else if($flag==3)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->Where(['auth'=>$tid])
                ->all();
            return array("data"=>$query,"msg"=>"全部试卷");
        }
        else if($flag==4)
        {
            $name = $request->post('name');
            $query = (new Query())
                ->select("*")
                ->from('exam')
                ->where(['or',
                    ['like', 'exname', $name],
                    ['like', 'exid', $name],])
                ->andWhere(['auth'=>$tid])
                ->all();
            return array("data"=>$query,"msg"=>$name."全部试卷");
        }
        else{
            return array("data"=>$flag,"msg"=>"输入错误");
        }

    }
}