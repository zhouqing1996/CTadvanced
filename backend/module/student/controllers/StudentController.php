<?php
namespace backend\module\student\controllers;

use yii\web\Controller;
use yii\db\Query;
use yii\common\Student;
use yii\common\Teacher;
use yii\common\Pratice;

class StudentController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>'sss','msg'=>'学生数据分析');
    }

//    学生选择老师
//参数：sid tid
    public function actionChoosest()
    {
        $request =  \Yii::$app->request;
        $sid = $request->post('sid');
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('student')
            ->where(['sid'=>$sid])
            ->andWhere(['tid'=>$tid])
            ->one();
        if($query)
        {
            return array('data'=>$query,'msg'=>'已选择该导师！');
        }
//        $num =$query+1;
        $insertU = \Yii::$app->db->createCommand()->insert('student',array('sid'=>$sid,'tid'=>$tid,'status'=>1))->execute();
        if($insertU)
        {
            return array('data'=>$insertU,'msg'=>'选择成功！');
        }
        else{
            return array('data'=>$insertU,'msg'=>'选择失败！');
        }
    }
//    学生的导师列表
//参数:学生id
    public function actionStlist()
    {
        $request = \Yii::$app->request;
        $sid = $request->post('sid');
        $query = (new Query())
            ->select('*')
            ->from('student')
            ->where(['sid'=>$sid])
            ->all();
        if($query)
        {
            $list = [];
            for($i=0;$i<count($query);$i++){
                $queryt = (new Query())
                    ->select('*')
                    ->from('user')
                    ->where(['id'=>$query[$i]['tid']])
                    ->one();
                $list[$i]['tid']=$queryt['id'];
                $list[$i]['tno']=$queryt['no'];
                $list[$i]['tname']=$queryt['username'];
            }
            return array('data'=>$list,'msg'=>'查找学生导师信息');
        }
        else
        {
            return array('data'=>$query,'msg'=>'学生导师信息为空！');
        }

    }

//    学生某一张试卷的数据分析：雷达图数据
// 参数：用户id、试卷id、答题次数
//uid eid num
    public function actionRadar()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $eid = $request->post('eid');
        $num = $request->post('num');
        $query =(new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid'=>$eid])
            ->all();
        //       做数据的统计，分别设置不同题型，统计题型数据
        $list = ['nc'=>0,'nf'=>0,'np'=>0,'nj'=>0,'nm'=>0];
        for($i=0;$i<count($query);$i++)
        {
            $type = $query[$i]['qtypeid'];
            switch ($type){
                case 1:$list['nc'] = $list['nc']+1;break;
                case 2:$list['nf'] = $list['nf']+1;break;
                case 3:$list['np'] = $list['np']+1;break;
                case 4:$list['nm'] = $list['nm']+1;break;
                case 5:$list['nj'] = $list['nj']+1;break;
                default:break;
            }
        }
//        用户答题的统计结果，计算用户答对的数目
        $queryu = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['id'=>$num])
            ->andWhere(['userid'=>$uid])
            ->andWhere(['exid'=>$eid])
            ->all();
        $listu = ['nc'=>0,'nf'=>0,'np'=>0,'nj'=>0,'nm'=>0];
        for($i=0;$i<count($queryu);$i++)
        {
            $typeu = $queryu[$i]['qtypeid'];
            switch ($typeu){
                case 1:
                    {
//                        答对才加1
                        if($queryu[$i]['grade']==1)
                        $listu['nc'] = $listu['nc']+1;
                        break;
                    }
                case 2:{
//                        答对才加1
                    if($queryu[$i]['grade']==1)
                        $listu['nf'] = $listu['nf']+1;
                    break;
                }
                case 3:{
//                        答对才加1
                    if($queryu[$i]['grade']==1)
                        $listu['np'] = $listu['np']+1;
                    break;
                }
                case 4:{
//                        答对才加1
                    if($queryu[$i]['grade']==1)
                        $listu['nm'] = $listu['nm']+1;
                    break;
                }
                case 5:{
//                        答对才加1
                    if($queryu[$i]['grade']==1)
                        $listu['nj'] = $listu['nj']+1;
                    break;
                }
                default:break;
            }
        }
        return array('data'=>[$list,$listu,$query,$queryu],'msg'=>'雷达图数据');
    }
}