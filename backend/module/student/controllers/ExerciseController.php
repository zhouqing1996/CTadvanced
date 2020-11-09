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
                $c = count($query);
                $id = $c+1;
            }
            else
            {
                $id = 1;
            }
            $cc = (new Query())
                ->select('*')
                ->from('chooseq')
                ->where(['cqid'=>$qid])
                ->one();
            if($cc['cqans']==$ans[0]['ans'])
            {
                $cgrade =1;
            }
            else{
                $cgrade =0;
            }
            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>1,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime,'grade'=>$cgrade))->execute();
            if($insert)
            {
                return array("data"=>$cgrade,"msg"=>"练习选择题成功");
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
                $fm = count($query);
                $id = $fm+1;
            }
            else
            {
                $id = 1;
            }
            $ff = (new Query())
                ->select('*')
                ->from('fillq')
                ->where(['fqid'=>$qid])
                ->one();
            if($ff['fqans']==$ans[0]['ans'])
            {
                $fgrade =1;
            }
            else{
                $fgrade =0;
            }
            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>2,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime,'grade'=>$fgrade))->execute();
            if($insert)
            {
                return array("data"=>$fgrade,"msg"=>"练习填空题成功");
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
                $pm = count($query);
                $id = $pm+1;
            }
            else
            {
                $id = 1;
            }
            $pp = (new Query())
                ->select('*')
                ->from('program')
                ->where(['pqid'=>$qid])
                ->one();
            if($pp['pqans']==$ans[0]['ans'])
            {
                $pgrade =1;
             }
            else{
                $pgrade =0;
            }
            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>3,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime,'grade'=>$pgrade))->execute();
            if($insert)
            {
                return array("data"=>$pgrade,"msg"=>"练习程序题成功");
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
                $Jm = count($query);
                $id = $Jm+1;
            }
            else
            {
                $id = 1;
            }
            //            判断答案是否正确
            $jj = (new Query())
                ->select('*')
                ->from('judge')
                ->where(['jqid'=>$qid])
                ->one();
            if($jj['jqans']==$ans[0]['ans'])
            {
                $jgrade = 1;
            }
            else{
                $jgrade=0;
            }
            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>5,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime,'grade'=>$jgrade))->execute();
            if($insert)
            {
                return array("data"=>$jgrade,"msg"=>"练习判断题成功");
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
                $n = count($query);
                $id = $n+1;
            }
            else
            {
                $id = 1;
            }
//            判断答案是否正确
            $querym = (new Query())
                ->select('*')
                ->from('choosem')
                ->where(['mqid'=>$qid])
                ->one();
            $s = $querym['mqans'];
            $exp1 = explode('---',$s);
            $exp2 = explode('---',$ans[0]['ans']);
            sort($exp2);
            sort($exp1);
            if($exp1===$exp2)
            {
                $mgrade = 1;
            }
            else{
                $mgrade = 0;
            }
            $insert= \Yii::$app->db->createCommand()->insert('pratice',array('id'=>$id,'userid'=>$uid,
                'qid'=>$qid,'qtypeid'=>4,'ans'=>$ans[0]['ans'],'finishtime'=>$finishtime,'status'=>1,'ctime'=>$ctime,'grade'=>$mgrade))->execute();
            if($insert)
            {
                return array("data"=>$mgrade,"msg"=>"练习多选题成功");
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