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
        if($list==null)
        {
            return round(0,2);
        }
        for($i=0;$i<count($list);$i++)
        {
            $sum = $sum + $list[$i];
        }
        return round($sum/count($list),2);

    }
//    某次作答次数
    public function NUm($uid,$eid)
    {
        $query = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['userid' => $uid])
            ->andWhere(['exid'=>$eid])
            ->max('id');
        return $query;
    }
//    判断题目没有做或者做错的
//试卷详情，用户作答详情
    public function Useren($all,$user,$exid)
    {
        $userEN = [];
        $n=0;
        $userid = array_merge(array_unique(array_column($user,'userid')));
//        '12,13
//        return array('data'=>$userid,'msg'=>'用户作答错误或未做的题目');

        for ($l = 0; $l < count($userid); $l++) {
            $num = $this->NUm($userid[$l], $exid);
//                return array('data'=>$userid[$l],'msg'=>'sss');
            //            选出同一个人在一份试卷的作答情况
            for ($x = 1; $x <= $num; $x++) {
//                    return array('data'=>[$userid[$l],$exid,$x],'msg'=>'333');
                $query = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid' => $userid[$l]])
                    ->andWhere(['id' => $x])
                    ->andWhere(['exid' => $exid])
                    ->all();
//                    return array('data'=>[$query,$all],'msg'=>'333');
                for ($j = 0; $j < count($all); $j++) {
                    $flag = true;
                    for ($m = 0; $m < count($query); $m++) {
                        $id = $query[$m]['id'];
                        $userid1 = $query[$m]['userid'];
                        $exid = $all[$j]['exid'];
                        $qid = $all[$j]['qid'];
                        $qtid = $all[$j]['qtypeid'];
                        $numa =$all[$j]['id'];
                        if ($query[$m]['qid'] === $all[$j]['qid'] && $query[$m]['qtypeid'] === $all[$j]['qtypeid']) {
                            $flag = false;
                            if ($query[$m]['grade'] == 0) {
                                $userEN[$n]['num']=$numa;
                                $userEN[$n]['id'] = $query[$m]['id'];
                                $userEN[$n]['userid'] = $query[$m]['userid'];
                                $userEN[$n]['exid'] = $query[$m]['exid'];
                                $userEN[$n]['qid'] = $query[$m]['qid'];
                                $userEN[$n]['qtypeid'] = $query[$m]['qtypeid'];
                                $userEN[$n]['ans'] = $query[$m]['ans'];
                                $userEN[$n]['grade'] = $query[$m]['grade'];
                                $userEN[$n]['ctime'] = $query[$m]['ctime'];
                                $userEN[$n]['finishtime'] = $query[$m]['finishtime'];
                                $n++;
                            }
                            break;
                        } else {
                            continue;
                        }
                    }
                    if ($flag) {
                        $userEN[$n]['num']=$numa;
                        $userEN[$n]['id'] = $id;
                        $userEN[$n]['userid'] = $userid1;
                        $userEN[$n]['exid'] = $exid;
                        $userEN[$n]['qid'] = $qid;
                        $userEN[$n]['qtypeid'] = $qtid;
                        $userEN[$n]['ans'] = '未作答';
                        $userEN[$n]['grade'] = '';
                        $userEN[$n]['ctime'] = '';
                        $userEN[$n]['finishtime'] = '';
                        $n++;
                    }
                }
            }
        }
        return array('data'=>$userEN,'msg'=>'用户作答错误或未做的题目');
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
            ->where(['exid' => $eid])
            ->one();
//        试卷的详细情况
        $querytail = (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid' => $eid])
            ->all();
//        用户作答的情况
        $userQuery = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['exid' => $eid])
            ->all();
        $userQuerytail = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['exid' => $eid])
            ->all();
        if($userQuery)
        {
            //        返回的整体信息列表
            $list = [];
//        平均分,平均用时；平均次数

            $grade = array_column($userQuery, 'grade');
            $time = array_column($userQuery, 'ctime');
//        分钟处理
            for ($i = 0; $i < count($time); $i++) {
                $arr = explode(':', $time[$i]);
                $time[$i] = round((int)$arr[0] * 60 + $arr[1] + $arr[2] / 60, 2);
            }
//        作答用户的id
            $list['userid'] = array_merge(array_unique(array_column($userQuery, 'userid')));
            $num = [];
            for($mm=0;$mm<count($list['userid']);$mm++)
            {
                $n = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid'=>$list['userid'][$mm]])
                    ->andWhere(['exid'=>$eid])
                    ->max('id');
                array_push($num,$n);
            }


            $list['avgGrade'] = $this->AvgNumber($grade);
            $list['avgTime'] = $this->AvgNumber($time);
            $list['avgNum'] = $this->AvgNumber($num);
//        作答用户数：用户id去重的计数
            $list['num'] = count(array_unique(array_column($userQuery, 'userid')));
//        试卷题目总数
            $list['exnum'] = count($querytail);
//        试卷的名字
            $list['exname'] = $query['exname'];
//        试卷的最高分
            $list['HeightScore'] = max(array_column($userQuery,'grade'));
//  试卷的最低分
            $list['LowScore'] = min(array_column($userQuery,'grade'));
//        用户作答次数的最大值
            $list['maxusernum'] = max($num);

//        作答用户的名字
            $list['username'] =[];
            for($i=0;$i<count($list['userid']);$i++)
            {
                $q = (new Query())
                    ->select('*')
                    ->from('user')
                    ->where(['id'=>$list['userid'][$i]])
                    ->one();
                $list['username'][$i]=$q['username'];
            }


//        return array('data'=>[$list,$userEN],'msg'=>'数据分析');
//        获取扇形图的数据：
//        用户作答中最大的作答次数的比列
            $userpie = [];
//        用户列表
//        $userList =array_merge($list['userid']);
            for($i=0;$i<count($list['userid']);$i++)
            {
                $userpie[$i]['id']=$list['userid'][$i];
                $userpie[$i]['num'] = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid'=>$list['userid'][$i]])
                    ->andWhere(['exid'=>$eid])
                    ->max('id');
            }
//        饼图需要的内容是：某个次数的人的数量
            $pie = array_column($userpie,'num');
            $pie = array_count_values($pie);
            $pieData = [];
            $i=0;
            foreach ($pie as $key=>$value)
            {
                $pieData[$i]['num'] = $key;
                $pieData[$i]['value']=$value;
                $i++;
            }
            //        用户做错或者没有做的题目
            $userEN= $this->Useren($querytail,$userQuerytail,$eid)['data'];
//        错题的频次列表
            $Err =array_column($userEN,'num');
//        return array('data'=>[$list,$userEN,$pieData,count($userEN)],'msg'=>'数据分析');
            $Err = array_count_values($Err);
            $ErrList = [];
            $j=0;
            foreach ($Err as $key=>$value)
            {
                $ErrList[$j]['Itemnum']=$key;
                $ErrList[$j]['num'] =$value;
                $j++;
            }
//        按照题目排序
            foreach ($ErrList as $key=>$value)
            {
                $flag[]=$value['Itemnum'];
            }
            array_multisort($flag,SORT_ASC,$ErrList);
        }
        else
        {
            //        返回的整体信息列表
            $list = [];
//        平均分,平均用时；平均次数

            $list['avgGrade'] = 0;
            $list['avgTime'] = 0;
            $list['avgNum'] = 0;
//        作答用户数：用户id去重的计数
            $list['num'] =0;
//        试卷题目总数
            $list['exnum'] = count($querytail);
//        试卷的名字
            $list['exname'] = $query['exname'];
//        试卷的最高分
            $list['HeightScore'] = 0;
//  试卷的最低分
            $list['LowScore'] = 0;
//        用户作答次数的最大值
            $list['maxusernum'] = 0;
//        作答用户的id
            $list['userid'] = '无';
//        作答用户的名字
            $list['username'] =[];
//        用户列表
//        饼图需要的内容是：某个次数的人的数量
            $pieData = [];
            //        用户做错或者没有做的题目
            $userEN= [];
            $ErrList = [];
        }
        return array('data'=>[$list,$userEN,$pieData,$ErrList],'msg'=>'数据分析');
    }
}