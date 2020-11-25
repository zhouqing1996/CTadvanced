<?php

namespace backend\module\exam\controllers;

use backend\module\home\controllers\UserController;
use yii\web\Controller;
use yii\common\models\Exam;
use yii\common\models\Examtail;
use yii\common\models\Chooseq;
use yii\common\models\Fillq;
use yii\common\models\Programq;
use yii\common\models\User;
use yii\common\models\Program;
use yii\common\models\Judge;
use yii\common\models\Choosem;
use yii\web\Response;
use yii\web\Request;
use yii\db\Query;

/**
 * Default controller for the `exam` module
 */
class ExamController extends Controller
{

    public function actionQueryid($id)
    {
        $query = (new Query())
            ->select('username')
            ->from('user')
            ->where(['id'=>$id])
            ->one();
        return array("data"=>$query,"msg"=>"id为：".$id."用户信息");
    }
    public function actionTest()
    {
        $id=5;
        $queryChooseList = (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>1])
            ->andWhere(['exstatus'=>1])
            ->all();
        $ChooseList = array();
        for($i=0;$i<count($queryChooseList);$i++)
        {
            $ChooseList[$i] = (new Query())
                ->select('*')
                ->from('chooseq')
                ->where(['cqid'=>$queryChooseList[$i]])
                ->andWhere(['cqstatus'=>1])
                ->one();
        }
        return array("data"=>[$queryChooseList,$ChooseList],"msg"=>"s");

    }

    /*
     * 预览试卷
     */
    public function actionView()
    {
        $request = \Yii::$app->request;
        $id = $request->post('id');
        $query = (new Query())
            ->select("*")
            ->from('exam')
            ->where(['exid'=>$id])
            ->andWhere(['exstatus'=>1])
            ->one();
        $exname = $query['exname'];
        $auth = \backend\module\home\controllers\UserController::Queryid($query['auth']);
        $createTime = $query['createtime'];
        $queryChooseList = (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>1])
            ->andWhere(['exstatus'=>1])
            ->all();
        $ChooseList = array();
        for($i=0;$i<count($queryChooseList);$i++)
        {
            $ChooseList[$i] = (new Query())
                ->select('*')
                ->from('chooseq')
                ->where(['cqid'=>$queryChooseList[$i]])
                ->andWhere(['cqstatus'=>1])
                ->one();
        }
        $queryFillList = (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>2])
            ->andWhere(['exstatus'=>1])
            ->all();
        $FillList = array();
        for($i=0;$i<count($queryFillList);$i++)
        {
            $FillList[$i] = (new Query())
                ->select('*')
                ->from('fillq')
                ->where(['fqid'=>$queryFillList[$i]])
                ->andWhere(['fqstatus'=>1])
                ->one();
        }
        $queryProgramList = (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>3])
            ->andWhere(['exstatus'=>1])
            ->all();
        $ProgramList = array();
        for($i=0;$i<count($queryProgramList);$i++)
        {
            $ProgramList[$i] = (new Query())
                ->select('*')
                ->from('program')
                ->where(['pqid'=>$queryProgramList[$i]])
                ->andWhere(['pqstatus'=>1])
                ->one();
        }
        $queryjudgeList = (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>5])
            ->andWhere(['exstatus'=>1])
            ->all();
        $judgeList = array();
        for($i=0;$i<count($queryjudgeList);$i++)
        {
            $judgeList[$i] = (new Query())
                ->select('*')
                ->from('judge')
                ->where(['jqid'=>$queryjudgeList[$i]])
                ->andWhere(['jqstatus'=>1])
                ->one();
        }
        $queryChoosemList= (new Query())
            ->select('qid')
            ->from('examtail')
            ->where(['exid'=>$id])
            ->andWhere(['qtypeid'=>4])
            ->andWhere(['exstatus'=>1])
            ->all();
        $choosemList = array();
        for($i=0;$i<count($queryChoosemList);$i++)
        {
            $choosemList[$i] = (new Query())
                ->select('*')
                ->from('choosem')
                ->where(['mqid'=>$queryChoosemList[$i]])
                ->andWhere(['mqstatus'=>1])
                ->one();
        }
        return array("data"=>[$exname,$auth,$createTime,$ChooseList,$FillList,$ProgramList,$judgeList,$choosemList,$query],"msg"=>"预览试卷");
    }
    /*
     * 学生用户作答
     */
    public function actionUserans()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $uid = $request->post('uid');
        $Clist = $request->post('cList');
        $Flist = $request->post('fList');
        $Plist = $request->post('pList');
        $CMlist =$request->post('cmList');
        $Jlist = $request->post('jList');
        $ctime = $request->post('ctime');
        $query = (new Query())
            ->select("*")
            ->from("useranss")
            ->Where(['userid'=>$uid])
            ->andWhere(['exid'=>$eid])
            ->all();
        if($query){
            $id = (new Query())
                ->select("*")
                ->from("useranss")
                ->Where(['userid'=>$uid])
                ->andWhere(['exid'=>$eid])
                ->max('id');
//        作答次数
            $id = $id+1;
        }
        else{
            $id =1;
        }

        $finishtime = date('Y-m-d H:i:s',time());
        for($i=0;$i<count($Clist);$i++)
        {
            $cqid = $Clist[$i]['id'];
            $cqans = $Clist[$i]['ans'];
            $cqtypeid = 1;
            $sctime = $Clist[$i]['ctime'];

            $updatec = \Yii::$app->db->createCommand()->insert('userans',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'qid'=>$cqid,'qtypeid'=>$cqtypeid,
                    'ans'=>$cqans,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Flist);$i++)
        {
            $fqid = $Flist[$i]['id'];
            $fqans = $Flist[$i]['ans'];
            $fqtypeid = 2;
            $sctime = $Flist[$i]['ctime'];
            $updatef = \Yii::$app->db->createCommand()->insert('userans',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'qid'=>$fqid,'qtypeid'=>$fqtypeid,
                    'ans'=>$fqans,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Plist);$i++)
        {
            $pqid = $Plist[$i]['id'];
            $pqans = $Plist[$i]['ans'];
            $pqtypeid = 3;
            $sctime = $Plist[$i]['ctime'];
            $updatep = \Yii::$app->db->createCommand()->insert('userans',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'qid'=>$pqid,'qtypeid'=>$pqtypeid,
                    'ans'=>$pqans,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($CMlist);$i++)
        {
            $mqid = $CMlist[$i]['id'];
            $mqans = $CMlist[$i]['ans'];
            $mqtypeid = 4;
            $sctime = $CMlist[$i]['ctime'];
            $updatem = \Yii::$app->db->createCommand()->insert('userans',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'qid'=>$mqid,'qtypeid'=>$mqtypeid,
                    'ans'=>$mqans,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Jlist);$i++)
        {
            $jqid = $Jlist[$i]['id'];
            $jqans = $Jlist[$i]['ans'];
            $jqtypeid = 5;
            $sctime = $Jlist[$i]['ctime'];
            $updatef = \Yii::$app->db->createCommand()->insert('userans',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'qid'=>$jqid,'qtypeid'=>$jqtypeid,
                    'ans'=>$jqans,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        $n = $this->Checkans($id,$uid,$eid);
        if($n!=0)
        {
            $update = \Yii::$app->db->createCommand()->insert('useranss',
                array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'grade'=>$n,'finishtime'=>$finishtime, 'status'=>1,'ctime'=>$ctime))->execute();

            return array('data'=>[$update],"msg"=>$uid."作答".$eid."完成");
        }
    }
//    用户作答完成，实现用户答案与正确答案的匹配
//提供参数：用户id,试题exid,作答次数：num
    public function Checkans($num,$uid,$eid)
    {
        $query= (new Query())
            ->select('*')
            ->from('userans')
            ->where(['userid'=>$uid])
            ->andWhere(['exid'=>$eid])
            ->andWhere(['id'=>$num])
            ->all();
//        初始判分，每对一题，增加1
        $x = 0;

        if($query)
        {
            for($i=0;$i<count($query);$i++)
            {
//                不同的题型
                if($query[$i]['qtypeid']==1)
                {
//                    选择题
                    $qc = (new Query())
                        ->select('*')
                        ->from('chooseq')
                        ->where(['cqid'=>$query[$i]['qid']])
                        ->one();
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qc['cqans'])
                    {
                        $x = $x+1;
                        $gradec = 1;
                        if($nw = $qc['err']<=0)
                            $nw = 0;
                        else{
                            $nw = $qc['err']-1;
                        }
                    }
                    else{
//                        如果用户作答错误，在题库中错误系数err+1，用于推荐,正确则减一，如果为0 则不加不减
                        $nw = $qc['err']+1;
                        $gradec = 0;
//                        $updatec = \Yii::$app->db->createCommand()->update('userans',['grade'=>0],
//                            ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>1,'qid'=>$query[$i]['qid']])->execute();
                    }
                    $updatec = \Yii::$app->db->createCommand()->update('userans',['grade'=>$gradec],
                        ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>1,'qid'=>$query[$i]['qid']])->execute();
                    $update_c =  \Yii::$app->db->createCommand()->update('chooseq',['err'=>$nw],
                        ['cqid'=>$query[$i]['qid']])->execute();
                }
                else if($query[$i]['qtypeid']==2)
                {
//                    填空题
                    $qf = (new Query())
                        ->select('*')
                        ->from('fillq')
                        ->where(['fqid'=>$query[$i]['qid']])
                        ->one();
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qf['fqans'])
                    {
                        $x = $x+1;
                        $gradef =1;
                        if($nw = $qf['err']<=0)
                            $nw = 0;
                        else{
                            $nw = $qf['err']-1;
                        }
                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
                        $nw = $qf['err']+1;
                        $gradef =0;
                    }
                    $updatef = \Yii::$app->db->createCommand()->update('userans',['grade'=>$gradef],
                        ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>2,'qid'=>$query[$i]['qid']])->execute();
                    $update_f =  \Yii::$app->db->createCommand()->update('fillq',['err'=>$nw],
                        ['fqid'=>$query[$i]['qid']])->execute();
                }
                else if($query[$i]['qtypeid']==3)
                {
//                  程序题
                    $qp = (new Query())
                        ->select('*')
                        ->from('program')
                        ->where(['pqid'=>$query[$i]['qid']])
                        ->one();
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qp['pqans'])
                    {
                        $x = $x+1;
                        $gradep = 1;
                        if($nw = $qp['err']<=0)
                            $nw = 0;
                        else{
                            $nw = $qp['err']-1;
                        }
                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
                        $nw = $qp['err']+1;
                        $gradep = 0;
                    }
                    $updatep = \Yii::$app->db->createCommand()->update('userans',['grade'=>$gradep],
                        ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>3,'qid'=>$query[$i]['qid']])->execute();
                    $update_p =  \Yii::$app->db->createCommand()->update('program',['err'=>$nw],
                        ['pqid'=>$query[$i]['qid']])->execute();

                }
                else if($query[$i]['qtypeid']==4)
                {
//                    多选题
                    $qm= (new Query())
                        ->select('*')
                        ->from('choosem')
                        ->where(['mqid'=>$query[$i]['qid']])
                        ->one();
                    $s = $qm['mqans'];
                    $exp1 = explode('---',$s);
                    $exp2 = explode('---',$query[$i]['ans']);
                    sort($exp2);
                    sort($exp1);
                    if($exp1===$exp2)
                    {
                        $x = $x+1;
                        $mgrade = 1;
                        if($nw = $qm['err']<=0)
                            $nw = 0;
                        else{
                            $nw = $qm['err']-1;
                        }
                    }
                    else{
                        $mgrade = 0;
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
                        $nw = $qm['err']+1;
                    }
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    $updatem= \Yii::$app->db->createCommand()->update('userans',['grade'=>$mgrade],
                        ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>4,'qid'=>$query[$i]['qid']])->execute();
                    $update_m =  \Yii::$app->db->createCommand()->update('choosem',['err'=>$nw],
                        ['mqid'=>$query[$i]['qid']])->execute();
//                    if($mgrade==1)
//                    {
//                        $x = $x+1;
//                        $updatem= \Yii::$app->db->createCommand()->update('userans',['grade'=>1],
//                            ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>4,'qid'=>$query[$i]['qid']])->execute();
//                    }
//                    else{
//                        $updatem = \Yii::$app->db->createCommand()->update('userans',['grade'=>0],
//                            ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>4,'qid'=>$query[$i]['qid']])->execute();
//                    }

                }
                else if($query[$i]['qtypeid']==5)
                {
//                  判断题
                    $qj = (new Query())
                        ->select('*')
                        ->from('judge')
                        ->where(['jqid'=>$query[$i]['qid']])
                        ->one();
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qj['jqans'])
                    {
                        $x = $x+1;
                        $gradej = 1;
                        if($nw = $qj['err']<=0)
                            $nw = 0;
                        else{
                            $nw = $qj['err']-1;
                        }

                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
                        $nw = $qj['err']+1;
                        $gradej = 0;
                    }
                    $updatej = \Yii::$app->db->createCommand()->update('userans',['grade'=>$gradej],
                        ['id'=>$num,'userid'=>$uid,'exid'=>$eid,'qtypeid'=>5,'qid'=>$query[$i]['qid']])->execute();
                    $update_j =  \Yii::$app->db->createCommand()->update('judge',['err'=>$nw],
                        ['jqid'=>$query[$i]['qid']])->execute();
                }
            }
            return $x;
//            array('data'=>$x,'msg'=>'判分完成');
        }
        else{
            return 0;
//            array('data'=>[],'msg'=>'没有找到该试卷');
        }

    }
//    获取试卷名字
    public function Examname($eid)
    {
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$eid])
            ->one();
        if($query)
        {
            return $query;
        }
        else{
            return array('data'=>[],'msg'=>'没有该试卷');
        }
    }
//    获取用户名
    public function Username($id)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->andWhere(['status'=>1])
            ->one();
    }
//    获取用户作答的所有信息
//提供参数 用户id:uid
    public function actionUserresult()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $query = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['userid'=>$uid])
            ->andWhere(['status'=>1])
            ->all();
        if($query)
        {
            for($i=0;$i<count($query);$i++)
            {
                $exname = $this->Examname($query[$i]['exid'])['exname'];
                $auth = $this->Username($this->Examname($query[$i]['exid']))['username'];
                $query[$i]['auth']=$auth;
                $query[$i]['exname'] = $exname;
            }
            return array('data'=>$query,'msg'=>'查找成功');
        }
        else{
            return array('data'=>[],'msg'=>'该用户没有作答');
        }
    }
//    用户作答试卷的模糊查找
//提供参数：搜索内容：name
//         用户id:uid
    public function actionUserqueryname()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $name = $request->post('name');
        $query = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['userid'=>$uid])
            ->andWhere(['status'=>1])
            ->all();
        $list = [];
        if($query)
        {
            for($i=0;$i<count($query);$i++)
            {
//                问卷标题
                $e = (new Query())
                    ->select('*')
                    ->from('exam')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andwhere(['or',
                        ['like','exname',$name],])
                    ->all();
                if($e)
                {
                    $exname = $this->Examname($query[$i]['exid']);
                    $query[$i]['exname'] = $exname;
                    array_push($query[$i],$list);
                }
//                题目
//                选择题
                $ec = (new Query())
                    ->select('*')
                    ->from('examtail')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andWhere(['qtypeid'=>1])
                    ->all();
                if($ec)
                {
                    for($j=0;$j<count($ec);$j++)
                    {
                        $qc = (new Query())
                            ->select("*")
                            ->from('chooseq')
                            ->where(['cqid'=>$ec[$j]['qid']])
                            ->andWhere(['or',
                                ['like','cqitem',$name],
                                ['like','cqcho',$name],
                            ])
                            ->all();
                        if($qc)
                        {
                            $exname = $this->Examname($query[$i]['exid']);
                            $query[$i]['exname'] = $exname;
                            array_push($query[$i],$list);
                        }
                    }
                }
//              填空题
                $ef = (new Query())
                    ->select('*')
                    ->from('examtail')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andWhere(['qtypeid'=>2])
                    ->all();
                if($ef)
                {
                    for($j=0;$j<count($ef);$j++)
                    {
                        $qf = (new Query())
                            ->select("*")
                            ->from('fillq')
                            ->where(['fqid'=>$ec[$j]['qid']])
                            ->andWhere(['or',
                                ['like','fqitem',$name],
                            ])
                            ->all();
                        if($qf)
                        {
                            $exname = $this->Examname($query[$i]['exid']);
                            $query[$i]['exname'] = $exname;
                            array_push($query[$i],$list);
                        }
                    }
                }
//               判断题
                $ej = (new Query())
                    ->select('*')
                    ->from('examtail')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andWhere(['qtypeid'=>5])
                    ->all();
                if($ej)
                {
                    for($j=0;$j<count($ej);$j++)
                    {
                        $qc = (new Query())
                            ->select("*")
                            ->from('judge')
                            ->where(['jqid'=>$ec[$j]['qid']])
                            ->andWhere(['or',
                                ['like','jqitem',$name],
                            ])
                            ->all();
                        if($qc)
                        {
                            $exname = $this->Examname($query[$i]['exid']);
                            $query[$i]['exname'] = $exname;
                            array_push($query[$i],$list);
                        }
                    }
                }
//                程序题
                $ep = (new Query())
                    ->select('*')
                    ->from('examtail')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andWhere(['qtypeid'=>3])
                    ->all();
                if($ep)
                {
                    for($j=0;$j<count($ep);$j++)
                    {
                        $qp = (new Query())
                            ->select("*")
                            ->from('program')
                            ->where(['pqid'=>$ec[$j]['qid']])
                            ->andWhere(['or',
                                ['like','pqitem',$name],
                            ])
                            ->all();
                        if($qp)
                        {
                            $exname = $this->Examname($query[$i]['exid']);
                            $query[$i]['exname'] = $exname;
                            array_push($query[$i],$list);
                        }
                    }
                }
//                多选题
                $em = (new Query())
                    ->select('*')
                    ->from('examtail')
                    ->where(['exid'=>$query[$i]['exid']])
                    ->andWhere(['qtypeid'=>4])
                    ->all();
                if($em)
                {
                    for($j=0;$j<count($em);$j++)
                    {
                        $qm = (new Query())
                            ->select("*")
                            ->from('choosem')
                            ->where(['mqid'=>$ec[$j]['qid']])
                            ->andWhere(['or',
                                ['like','mqitem',$name],
                                ['like','mqcho',$name],
                            ])
                            ->all();
                        if($qm)
                        {
                            $exname = $this->Examname($query[$i]['exid']);
                            $query[$i]['exname'] = $exname;
                            array_push($query[$i],$list);
                        }
                    }
                }
            }
            return array('data'=>$list,'msg'=>$name.'查找成功');
        }
        else{
            return array('data'=>[],'msg'=>'该用户没有作答');
        }
    }

//    获取用户作答的某一次测试的详细结果
//      提供参数：uid:用户id,eid:试卷id,测试次数：num
    public function actionGetuserdetail()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $eid = $request->post('eid');
        $num = $request->post('num');
//        先查找该试卷包含的问题题目
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$eid])
            ->one();
//        全部的内容：题目内容，用户做答情况
//        item:题干
//        cho:选项
//        ans:答案
//        rem:相关知识
//        tail:详解
//        uans:用户作答答案
//        flag:正误标志
        if ($query)
        {
            //            查找该试题的全部题目
            $querys = (new Query())
                ->select('*')
                ->from('examtail')
                ->where(['exid'=>$eid])
                ->all();
//            该试题的试题数
            $exNum = count($querys);
            $Num = (new Query())
                ->select('*')
                ->from('useranss')
                ->where(['id'=>$num])
                ->andWhere(['userid'=>$uid])
                ->andWhere(['exid'=>$eid])
                ->one();
//            答对试题数
            $corNum = $Num['grade'];
            $list = [];
            $j =0;
            for($i=0;$i<count($querys);$i++)
            {
//                不同题目进行判别
//                选择题
                if($querys[$i]['qtypeid']==1)
                {
                    $qc = (new Query())
                        ->select('*')
                        ->from('chooseq')
                        ->where(['cqid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['type']= 1;
                    $list[$j]['item'] = $qc['cqitem'];
                    $list[$j]['cho'] = $qc['cqcho'];
                    $list[$j]['ans'] =$qc['cqans'];
                    $list[$j]['rem'] = $qc['cqrem'];
                    $list[$j]['tail'] = $qc['cqtail'];
                    $c = (new Query())
                        ->select('*')
                        ->from('userans')
                        ->where(['exid'=>$eid])
                        ->andWhere(['userid'=>$uid])
                        ->andWhere(['id'=>$num])
                        ->andWhere(['qtypeid'=>1])
                        ->andWhere(['qid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['uans'] = $c['ans'];
                    $list[$j]['flag'] = $c['grade'];
                    $j = $j+1;
                }
//                填空题
                else if($querys[$i]['qtypeid']==2)
                {
                    $qf = (new Query())
                        ->select('*')
                        ->from('fillq')
                        ->where(['fqid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['type']= 2;
                    $list[$j]['item'] = $qf['fqitem'];
                    $list[$j]['ans'] =$qf['fqans'];
                    $list[$j]['rem'] = $qf['fqrem'];
                    $list[$j]['tail'] = $qf['fqtail'];
                    $f = (new Query())
                        ->select('*')
                        ->from('userans')
                        ->where(['exid'=>$eid])
                        ->andWhere(['userid'=>$uid])
                        ->andWhere(['id'=>$num])
                        ->andWhere(['qtypeid'=>2])
                        ->andWhere(['qid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['uans'] = $f['ans'];
                    $list[$j]['flag'] = $f['grade'];
                    $j = $j+1;
                }
//                程序题
                else if($querys[$i]['qtypeid']==3)
                {
                    $qp = (new Query())
                        ->select('*')
                        ->from('program')
                        ->where(['pqid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['type']= 3;
                    $list[$j]['item'] = $qp['pqitem'];
                    $list[$j]['ans'] =$qp['pqans'];
                    $list[$j]['rem'] = $qp['pqrem'];
                    $list[$j]['tail'] = $qp['pqtail'];
                    $p = (new Query())
                        ->select('*')
                        ->from('userans')
                        ->where(['exid'=>$eid])
                        ->andWhere(['userid'=>$uid])
                        ->andWhere(['id'=>$num])
                        ->andWhere(['qtypeid'=>3])
                        ->andWhere(['qid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['uans'] = $p['ans'];
                    $list[$j]['flag'] = $p['grade'];
                    $j = $j+1;
                }
//                多选题
                else if($querys[$i]['qtypeid']==4)
                {
                    $qm = (new Query())
                        ->select('*')
                        ->from('choosem')
                        ->where(['mqid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['type']= 4;
                    $list[$j]['item'] = $qm['mqitem'];
                    $list[$j]['cho'] = $qm['mqcho'];
                    $list[$j]['ans'] =$qm['mqans'];
                    $list[$j]['rem'] = $qm['mqrem'];
                    $list[$j]['tail'] = $qm['mqtail'];
                    $m = (new Query())
                        ->select('*')
                        ->from('userans')
                        ->where(['exid'=>$eid])
                        ->andWhere(['userid'=>$uid])
                        ->andWhere(['id'=>$num])
                        ->andWhere(['qtypeid'=>4])
                        ->andWhere(['qid'=>$querys[$i]['qid']])
                        ->one();
                    if($m)
                    {
                        $list[$j]['uans'] = $m['ans'];
                        $list[$j]['flag'] = $m['grade'];
                        $j = $j+1;
                    }
                    else{
                        $list[$j]['uans'] = '';
                        $list[$j]['flag'] = '';
                        $j = $j+1;
                    }
                }
//                判断题
                else if($querys[$i]['qtypeid']==5)
                {
                    $qj = (new Query())
                        ->select('*')
                        ->from('judge')
                        ->where(['jqid'=>$querys[$i]['qid']])
                        ->one();
                    $list[$j]['type']= 5;
                    $list[$j]['item'] = $qj['jqitem'];
                    $list[$j]['ans'] =$qj['jqans'];
                    $list[$j]['rem'] = $qj['jqrem'];
                    $list[$j]['tail'] = $qj['jqtail'];
                    $jc= (new Query())
                        ->select('*')
                        ->from('userans')
                        ->where(['exid'=>$eid])
                        ->andWhere(['userid'=>$uid])
                        ->andWhere(['id'=>$num])
                        ->andWhere(['qtypeid'=>5])
                        ->andWhere(['qid'=>$querys[$i]['qid']])
                        ->one();
                    if($jc)
                    {
                        $list[$j]['uans'] = $jc['ans'];
                        $list[$j]['flag'] = $jc['grade'];
                        $j = $j+1;
                    }
                    else{
                        $list[$j]['uans'] = '';
                        $list[$j]['flag'] = '';
                        $j = $j+1;
                    }

                }
            }
            return array('data'=>[$list,$query,$exNum,$corNum],'msg'=>'获取成功');
        }
        else{
            return array('data'=>[],'msg'=>'没有该试卷');
        }
    }
//    用户作答后的试题推荐，先查看用户作答过程中做错的题目和未做的题目，
//再根据用户的作答情况推荐数据库中其他相似的题目，基于内容的推荐
//参数：测试卷编号eid，用户编号uid，测试的次数num
    public function UserANS($eid,$uid,$num)
    {
        //        试卷的全部试题
        $all = (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid'=>$eid])
            ->andWhere(['exstatus'=>1])
            ->all();
        //       用户作答的答案
        $userlist = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['exid'=>$eid])
            ->andWhere(['userid'=>$uid])
            ->andWhere(['id'=>$num])
            ->andWhere(['status'=>1])
            ->all();
        //用户做错的题目或者未做的题目
        $list = [];
        $n=0;
        for($i=0;$i<count($all);$i++)
        {
//            判断是否作答,true表示没有作答
            $flag = true;
            for($j=0;$j<count($userlist);$j++)
            {
                if($all[$i]['qid']==$userlist[$j]['qid'] && $all[$i]['qtypeid']==$userlist[$j]['qtypeid'])
                {
                    $flag = false;
                    if($userlist[$j]['grade']==0)
                    {
//                        做错的题目
                        $list[$n]['num']=$userlist[$j]['id'];
                        $list[$n]['exid']=$userlist[$j]['exid'];
                        $list[$n]['qid']=$userlist[$j]['qid'];
                        $list[$n]['qtypeid']=$userlist[$j]['qtypeid'];
                        $list[$n]['ans'] = $userlist[$j]['ans'];
                        $list[$n]['grade']=$userlist[$j]['grade'];
                        $list[$n]['ctime']=$userlist[$j]['ctime'];
                        $list[$n]['finishtime'] = $userlist[$j]['finishtime'];
                        $n++;
                    }
                    break;
                }
                else{
                    continue;
                }
            }
            if($flag)
            {
                //            未做的题目
                $list[$n]['num']=$num;
                $list[$n]['exid']=$all[$i]['exid'];
                $list[$n]['qid']=$all[$i]['qid'];
                $list[$n]['qtypeid']=$all[$i]['qtypeid'];
                $list[$n]['ans'] = '';
                $list[$n]['grade']='未作答';
                $list[$n]['ctime']='';
                $list[$n]['finishtime'] = '';
                $n++;
            }
        }
        return array('data'=>$list,'msg'=>'用户的答题中未做或者做错的题目');
    }
//    对应知识点
    public function Rem($list)
    {
        $rem =[];
        for($i=0;$i<count($list);$i++)
        {
            switch ($list[$i]['qtypeid'])
            {
                case 1:
                {
                    $qc = (new Query())
                        ->from('chooseq')
                        ->where(['cqid'=>$list[$i]['qid']])
                        ->one();
                    array_push($rem,$qc['cqrem']);
                    break;
                }
                case 2:
                {
                    $qf = (new Query())
                        ->from('fillq')
                        ->where(['fqid'=>$list[$i]['qid']])
                        ->one();
                    array_push($rem,$qf['fqrem']);
                    break;
                }
                case 3:
                {
                    $qp = (new Query())
                        ->from('program')
                        ->where(['pqid'=>$list[$i]['qid']])
                        ->one();
                    array_push($rem,$qp['pqrem']);
                    break;
                }
                case 4:
                {
                    $qm = (new Query())
                        ->from('choosem')
                        ->where(['mqid'=>$list[$i]['qid']])
                        ->one();
                    array_push($rem,$qm['mqrem']);
                    break;
                }
                case 5:
                {
                    $qj = (new Query())
                        ->from('judge')
                        ->where(['jqid'=>$list[$i]['qid']])
                        ->one();
                    array_push($rem,$qj['jqrem']);
                    break;
                }
                default:
                    break;
            }
        }
        $rem = array_merge(array_unique($rem));
        return array('data'=>$rem,'msg'=>'用户做错题目的知识点');
    }
//    相关知识查找
    public function MoHuSearch($name)
    {
        $list = [];
        $qc = (new Query())
            ->select('*')
            ->from('chooseq')
            ->where(['or',['like','cqrem',$name]])
            ->andWhere(['cqstatus'=>1])
            ->all();
        for($i=0;$i<count($qc);$i++)
        {
            $li['id']=$qc[$i]['cqid'];
            $li['item'] = $qc[$i]['cqitem'];
            $li['typeid']=1;
            $li['err']=$qc[$i]['err'];
            array_push($list,$li);
        }
        $qf = (new Query())
            ->select('*')
            ->from('fillq')
            ->where(['or',['like','fqrem',$name]])
            ->andWhere(['fqstatus'=>1])
            ->all();
        for($i=0;$i<count($qf);$i++)
        {
            $li['id']=$qf[$i]['fqid'];
            $li['item'] = $qf[$i]['fqitem'];
            $li['typeid']=2;
            $li['err']=$qf[$i]['err'];
            array_push($list,$li);
        }
        $qp = (new Query())
            ->select('*')
            ->from('program')
            ->where(['or',['like','pqrem',$name]])
            ->andWhere(['pqstatus'=>1])
            ->all();
        for($i=0;$i<count($qp);$i++)
        {
            $li['id']=$qp[$i]['pqid'];
            $li['item'] = $qp[$i]['pqitem'];
            $li['typeid']=3;
            $li['err']=$qp[$i]['err'];
            array_push($list,$li);
        }
        $qm = (new Query())
            ->select('*')
            ->from('choosem')
            ->where(['or',['like','mqrem',$name]])
            ->andWhere(['mqstatus'=>1])
            ->all();
        for($i=0;$i<count($qm);$i++)
        {
            $li['id']=$qm[$i]['mqid'];
            $li['item'] = $qm[$i]['mqitem'];
            $li['typeid']=4;
            $li['err']=$qm[$i]['err'];
            array_push($list,$li);
        }
        $qj = (new Query())
            ->select('*')
            ->from('judge')
            ->where(['or',['like','jqrem',$name]])
            ->andWhere(['jqstatus'=>1])
            ->all();
        for($i=0;$i<count($qj);$i++)
        {
            $li['id']=$qj[$i]['jqid'];
            $li['item'] = $qj[$i]['jqitem'];
            $li['typeid']=5;
            $li['err']=$qj[$i]['err'];
            array_push($list,$li);
        }
        $list = array_merge($list);
        return array('data'=>$list,'msg'=>'相似知识点的集合');

    }
//    推荐
    public function actionRecommd()
    {
        $request = \Yii::$app->request;
//        $eid = $request->post('eid');
//        $uid = $request->post('uid');
//        $num = $request->post('num');
        $eid=4;
        $uid=13;
        $num=2;
        $list = $this->UserANS($eid,$uid,$num)['data'];
        $rem = $this->Rem($list)['data'];
//        推荐列表
        $ReList = [];
        for($i=0;$i<count($rem);$i++)
        {
            $mon = $this->MoHuSearch($rem[$i])['data'];
            for($j=0;$j<count($mon);$j++)
            {
                array_push($ReList,$mon[$j]);
            }
        }
//        按照err指标排序
        foreach ($ReList as $key=>$value)
        {
            $flag[]=$value['err'];
        }
//        降序排列
        array_multisort($flag,SORT_DESC,$ReList);
//        只取前五条数据推荐
        $ReList = array_slice($ReList,0,5);
        return array('data'=>$ReList,'msg'=>'推荐题目');
    }
//  书籍推荐

}