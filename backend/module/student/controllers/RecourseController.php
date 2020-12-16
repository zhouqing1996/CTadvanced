<?php
namespace backend\module\student\controllers;

use yii\db\Query;
use yii\web\Controller;

class RecourseController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>'shuju','msg'=>'学生资源学习部分');
    }
    /*
     * 学生的教师列表
     */
    public function STlist($id)
    {
        return (new Query())
            ->select('*')
            ->from('student')
            ->where(['sid'=>$id])
            ->andWhere(['status'=>1])
            ->all();
    }
    /*
     * 用户信息
     */
    public function User($id)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->one();
    }
    /*
     * 学生资源列表
     */
    public function actionRecourselist()
    {
        $request = \Yii::$app->request;
        $sid = $request->post('sid');
        $tid = $this->STlist($sid);
        $tidList = array_merge(array_column($tid,'tid'));
        $list = [];
        $n =0;
        for($i=0;$i<count($tidList);$i++)
        {
            $query = (new Query())
                ->select('*')
                ->from('teacher')
                ->where(['tid'=>$tidList[$i]])
                ->andWhere(['status'=>1])
                ->orderBy(['ctime'=>SORT_DESC])
                ->all();
            for ($j=0;$j<count($query);$j++)
            {
                $list[$n]['id']= $query[$j]['id'];
                $list[$n]['tid'] = $this->User($query[$j]['tid'])['username'];
                $list[$n]['dir'] = $query[$j]['dir'];
                $list[$n]['ctime'] = $query[$j]['ctime'];
                $list[$n]['name'] = $query[$j]['name'];
                $type = explode('.',$list[$n]['name']);
                $list[$n]['type'] = $type[1];
                $n++;
            }
        }
        return array('data'=>$list,'msg'=>'学生可学习资源');
    }
    /*
     * 搜索
     */
    public function actionSearchre()
    {
        $request = \Yii::$app->request;
        $sid = $request->post('sid');
        $name = $request->post('name');
        $tid = $this->STlist($sid);
        $tidList = array_merge(array_column($tid,'tid'));
        $list = [];
        $n =0;
        for($i=0;$i<count($tidList);$i++)
        {
            $query = (new Query())
                ->select('*')
                ->from('teacher')
                ->where(['tid'=>$tidList[$i]])
                ->andWhere(['or',
                    ['like','dir',$name],
                    ['like','ctime',$name],
                    ['like','name',$name]])
                ->orderBy(['ctime'=>SORT_DESC])
                ->andWhere(['status'=>1])
                ->all();
            for ($j=0;$j<count($query);$j++)
            {
                $list[$n]['id']= $query[$j]['id'];
                $list[$n]['tid'] = $this->User($query[$j]['tid'])['username'];
                $list[$n]['dir'] = $query[$j]['dir'];
                $list[$n]['ctime'] = $query[$j]['ctime'];
                $list[$n]['name'] = $query[$j]['name'];
                $type = explode('.',$list[$n]['name']);
                $list[$n]['type'] = $type[1];
                $n++;
            }
            //搜索教师名字
            $queryT = (new Query())
                ->select('*')
                ->from('user')
                ->where(['id'=>$tidList[$i]])
                ->andWhere(['or',
                    ['like','username',$name]])
                ->one();
            if($queryT)
            {
                $query1 =(new Query())
                    ->select('*')
                    ->from('teacher')
                    ->where(['tid'=>$tidList[$i]])
                    ->orderBy(['ctime'=>SORT_DESC])
                    ->all();
                for ($j=0;$j<count($query1);$j++)
                {
                    $flag =true;
                    for($k=0;$k<count($list);$k++)
                    {
                        if($query1[$j]['id'] == $list[$k]['id'])
                        {
                            //是否已存在
                            $flag = false;
                            break;
                        }
                    }
                    if($flag)
                    {
                        $list[$n]['id']= $query1[$j]['id'];
                        $list[$n]['tid'] = $this->User($query1[$j]['tid'])['username'];
                        $list[$n]['dir'] = $query1[$j]['dir'];
                        $list[$n]['ctime'] = $query1[$j]['ctime'];
                        $list[$n]['name'] = $query1[$j]['name'];
                        $type1 = explode('.',$list[$n]['name']);
                        $list[$n]['type'] = $type1[1];
                        $n++;
                    }
                }
            }
        }
        return array('data'=>$list,'msg'=>'学生搜索学习资源');
    }
}
