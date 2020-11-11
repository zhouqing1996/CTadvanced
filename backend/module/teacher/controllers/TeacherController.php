<?php

namespace backend\module\teacher\controllers;

use yii\web\Controller;
use yii\db\Query;
use yii\common\Student;
use yii\common\Teacher;


/*
 * 教师对学生的相关管理
 */

class TeacherController extends Controller
{
    public function actionIndex()
    {
        return "教师管理";
    }
//    求平均数
    public function AvgNumber($list)
    {
        $sum =0;
        for($i=0;$i<count($list);$i++)
        {
            $sum = $sum + $list[$i];
        }
        return round($sum/count($list),2);

    }
//    教师的某一试卷的数据分析
//参数：教师的auth,试卷eid
    public function actionTeacherfenxi()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $auth = $request->post('auth');
//        试卷的情况
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$eid])
            ->one();
//        试卷的详细情况
        $querytail = (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid'=>$eid])
            ->all();
//        用户作答的情况
        $userQuery = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['exid'=>$eid])
            ->all();
        $userQuerytail = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['exid'=>$eid])
            ->all();
//        返回的整体信息列表
        $list = [];
//        平均分,平均用时；平均次数

        $grade = array_column($userQuery,'grade');
        $time = array_column($userQuery,'ctime');
//        分钟处理
        for($i=0;$i<count($time);$i++)
        {
            $arr = explode(':',$time[$i]);
            $time[$i] = round((int)$arr[0]*60+$arr[1]+$arr[2]/60,2);
        }
        $num = array_column($userQuery,'id');

        $list['avgGrade']= $this->AvgNumber($grade);
        $list['avgTime'] = $this->AvgNumber($time);
        $list['avgNum'] = $this->AvgNumber($num);
//        作答用户数：用户id去重的计数
        $list['num'] = count(array_unique(array_column($userQuery,'userid')));
//        试卷题目总数
        $list['exnum'] = count($querytail);

//        用户作答次数的最大值
        $list['maxusernum'] = max($num);
//        作答用户的id
        $list['userid'] = array_unique(array_column($userQuery,'userid'));
        
        return array('data'=>[$list],'msg'=>'数据分析');
//        获取扇形图的数据：
//        用户作答中最大的作答次数的比列
        $userpie = [];
        for($i=0;$i<count($list['userid']);$i++)
        {
            $userpie[$i]['userid']=$list['userid'][$i];
            $userpie[$i]['num'] = (new Query())
                ->select('*')
                ->from('userans')
                ->where(['userid'=>$list['userid'][$i]])
                ->max('id');
        }

        return array('data'=>[$list,$userpie],'msg'=>'数据分析');

    }
}