<?php

namespace backend\module\bank\controllers;

use yii\web\Controller;
use yii\common\models\Chooseq;
use yii\db\Query;
class TestController extends Controller
{
    public function actionTest()
    {
        $id="1";
        $query = (new Query())
            ->select('*')
            ->from('chooseq')
            ->where(['cqid'=>$id])
            ->andWhere(['cqstatus'=>1])
            ->one();
        $str = $query['cqcho'];
        $expl = explode('---',$str);
        $op = "xinde ";
        $expl[0]=$op;
        $nop = implode('---',$expl);
        return array("data"=>[$query,$str,$expl,$nop],"msg"=>"测试");
    }
}