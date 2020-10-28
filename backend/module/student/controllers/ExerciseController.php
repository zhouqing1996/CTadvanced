<?php

namespace backend\module\student\controllers;

use yii\web\Controller;
use yii\db\Query;
use yii\common\Student;
use yii\common\Teacher;
use yii\common\Pratice;

class ExerciseController extends Controller
{
    /*
     * 查找对应的题目
     * flag:标志
     * 1：选择题
     * 2：填空题
     * 3：程序题
     * 4：判断题
     * 5：多选题
     */
    public function actionQueryquestion()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        if($flag==1)
        {
            $cid = $request->post('cqid');
            $queryc = (new Query())
                ->select('*')
                ->from('chooseq')
                ->Where(['cqid'=>$cid])
                ->all();
            return array("data"=>$queryc,"msg"=>"选择题".$cid);
        }
        else if($flag==2)
        {
            $fid = $request->post('fqid');
            $queryf= (new Query())
                ->select('*')
                ->from('fillq')
                ->Where(['fqid'=>$fid])
                ->all();
            return array("data"=>$queryf,"msg"=>"选择题".$fid);
        }
        else if($flag==3)
        {
            $pid = $request->post('pqid');
            $queryp = (new Query())
                ->select('*')
                ->from('program')
                ->Where(['pqid'=>$pid])
                ->all();
            return array("data"=>$queryp,"msg"=>"选择题".$pid);
        }
        else if($flag==4)
        {
            $jid = $request->post('jqid');
            $queryj = (new Query())
                ->select('*')
                ->from('judge')
                ->Where(['jqid'=>$jid])
                ->all();
            return array("data"=>$queryj,"msg"=>"选择题".$jid);
        }
        else if($flag==5)
        {
            $cmid = $request->post('mqid');
            $querym = (new Query())
                ->select('*')
                ->from('choosem')
                ->Where(['mqid'=>$cmid])
                ->all();
            return array("data"=>$querym,"msg"=>"选择题".$cmid);
        }
        else
        {
            return array("data"=>$flag,"msg"=>"输入错误");
        }
    }

    /*
     * 提交对应的题目答案
     * flag:标志
     * 1：选择题
     * 2：填空题
     * 3：程序题
     * 4：判断题
     * 5：多选题
     */
    public function actionSubmitanser()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        $uid = $request->post('uid');
        $qid = $request->post('qid');
        $ans = $request->post('ans');
        $ctime = $request->post('ctime');
        $finishtime = date('Y-m-d H:i:s',time());
        if($flag==1)
        {
            $query = (new Query())
                ->select('*')
                ->from('pratice')
                ->Where(['userid'=>$uid])
                ->andWhere(['qid'=>$qid])
                ->andWhere(['qtypeid'=>1])
                ->all();
            if($query)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('pratice')
                    ->Where(['userid'=>$uid])
                    ->andWhere(['qid'=>$qid])
                    ->andWhere(['qtypeid'=>1])
                    ->max('id');
                $id = $query+1;
            }
            else
            {
                $id = 1;
            }

            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>1,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime))->execute();
            if($insert)
            {
                return array("data"=>$insert,"msg"=>"练习选择题成功");
            }
            else{
                return array("data"=>$insert,"msg"=>"练习选择题失败");
            }
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select('*')
                ->from('pratice')
                ->Where(['userid'=>$uid])
                ->andWhere(['qid'=>$qid])
                ->andWhere(['qtypeid'=>2])
                ->all();
            if($query)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('pratice')
                    ->Where(['userid'=>$uid])
                    ->andWhere(['qid'=>$qid])
                    ->andWhere(['qtypeid'=>2])
                    ->max('id');
                $id = $query+1;
            }
            else
            {
                $id = 1;
            }

            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>2,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime))->execute();
            if($insert)
            {
                return array("data"=>$insert,"msg"=>"练习填空题成功");
            }
            else{
                return array("data"=>$insert,"msg"=>"练习填空题失败");
            }
        }
        else if($flag==3)
        {
            $query = (new Query())
                ->select('*')
                ->from('pratice')
                ->Where(['userid'=>$uid])
                ->andWhere(['qid'=>$qid])
                ->andWhere(['qtypeid'=>3])
                ->all();
            if($query)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('pratice')
                    ->Where(['userid'=>$uid])
                    ->andWhere(['qid'=>$qid])
                    ->andWhere(['qtypeid'=>3])
                    ->max('id');
                $id = $query+1;
            }
            else
            {
                $id = 1;
            }

            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>3,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime))->execute();
            if($insert)
            {
                return array("data"=>$insert,"msg"=>"练习程序题成功");
            }
            else{
                return array("data"=>$insert,"msg"=>"练习程序题失败");
            }
        }
        else if($flag==4)
        {
            $query = (new Query())
                ->select('*')
                ->from('pratice')
                ->Where(['userid'=>$uid])
                ->andWhere(['qid'=>$qid])
                ->andWhere(['qtypeid'=>5])
                ->all();
            if($query)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('pratice')
                    ->Where(['userid'=>$uid])
                    ->andWhere(['qid'=>$qid])
                    ->andWhere(['qtypeid'=>5])
                    ->max('id');
                $id = $query+1;
            }
            else
            {
                $id = 1;
            }

            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>5,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime))->execute();
            if($insert)
            {
                return array("data"=>$insert,"msg"=>"练习判断题成功");
            }
            else{
                return array("data"=>$insert,"msg"=>"练习判断题失败");
            }
        }
        else if($flag==5)
        {
            $query = (new Query())
                ->select('*')
                ->from('pratice')
                ->Where(['userid'=>$uid])
                ->andWhere(['qid'=>$qid])
                ->andWhere(['qtypeid'=>4])
                ->all();
            if($query)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('pratice')
                    ->Where(['userid'=>$uid])
                    ->andWhere(['qid'=>$qid])
                    ->andWhere(['qtypeid'=>4])
                    ->max('id');
                $id = $query+1;
            }
            else
            {
                $id = 1;
            }

            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>4,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime))->execute();
            if($insert)
            {
                return array("data"=>$insert,"msg"=>"练习多选题成功");
            }
            else{
                return array("data"=>$insert,"msg"=>"练习多选题失败");
            }
        }
        else
        {
            return array("data"=>$flag,"msg"=>"输入错误");
        }
    }
}