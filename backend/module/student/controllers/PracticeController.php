<?php

namespace backend\module\student\controllers;

use yii\db\Query;
use yii\web\Controller;

class PracticeController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>[],'msg'=>'学生用户练习部分');
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
     * 个人练习信息
     * 参数;用户id
     * 得到的结果包括选择的导师的练习题目，和个人设置的练习题目
     */
    public function actionPracticelist()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        //数据列表
        $list = [];
        $n=0;

        //找对应的教师
        $queryTeacher = (new Query())
            ->select('*')
            ->from('student')
            ->where(['sid'=>$uid])
            ->andWhere(['status'=>1])
            ->all();
        $teacherlist = array_column($queryTeacher,'tid');
        for($i=0;$i<count($teacherlist);$i++)
        {
            $queryt = (new Query())
                ->select('*')
                ->from('prac')
                ->where(['auth'=>$teacherlist[$i]])
                ->andWhere(['status'=>1])
                ->orderBy(['createtime'=>SORT_DESC])
                ->all();
            for($j=0;$j<count($queryt);$j++)
            {
                $list[$n]['id'] = $queryt[$j]['id'];
                $list[$n]['name'] = $queryt[$j]['name'];
                $list[$n]['createtime'] = $queryt[$j]['createtime'];
                $list[$n]['auth'] = $this->User($queryt[$j]['auth'])['username'];
                $list[$n]['status'] = $queryt[$j]['status'];
                $n++;
            }
        }
        //个人创建的练习题
        $queryU = (new Query())
            ->select('*')
            ->from('prac')
            ->where(['auth'=>$uid])
            ->orderBy(['createtime'=>SORT_DESC])
            ->all();
        for($i=0;$i<count($queryU);$i++)
        {
            $list[$n]['id'] = $queryU[$i]['id'];
            $list[$n]['name'] = $queryU[$i]['name'];
            $list[$n]['createtime'] = $queryU[$i]['createtime'];
            $list[$n]['auth'] = $this->User($queryU[$i]['auth'])['username'];
            $list[$n]['status'] = $queryU[$i]['status'];
            $n++;
        }
        return array('data'=>$list,'msg'=>'练习题列表');
    }
    /*
     * 随机数
     */
    public function Rand($min,$max,$num)
    {
        $count =0;
        $result = array();
        while ($count<$num)
        {
            $result[]=mt_rand($min,$max);
            $result =array_flip(array_flip($result));
            $count = count($result);
        }
        //打乱数组，重新赋予数组的新的下标
        shuffle($result);
        return $result;
    }
    /*
     * 练习序号
     */
    public function NumPID()
    {
        return (new Query())
            ->select('*')
            ->from('prac')
            ->max('id')+1;
    }
//试卷中第几题
//参数：eid,
    public function NumItem($pid)
    {
        return (new Query())
            ->select('*')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->max('id')+1;
    }
    /*
     * 个人创建练习题
     *参数：用户id
     * 数据list
     */
    public function actionAddpractice()
    {
        set_time_limit(0);
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $list = $request->post('list');
        $nc = $list['NumC'];
        $nf = $list['NumF'];
        $np = $list['NumP'];
        $nj = $list['NumJ'];
        $ncm = $list['NumCM'];
        $name = $list['name'];
        $pid = $this->NumPID();
        /*
         * 设定选择题，填空题，程序题的个数
         */

        //不考虑题库中题数目不足的情况
        //所有的题库表的id都是从1开始的，从中产生随机数id，插入到数据库中；
        $min = 1;
        //选择选择题
        $queryc = (new Query())
            ->select("*")
            ->from("chooseq")
            ->max('cqid');
        $arrc = $this->Rand($min, $queryc, $nc);

        for ($x = 0; $x < $nc; ) {
            $err = (new Query())
                ->select('*')
                ->from('chooseq')
                ->where(['cqid'=>$arrc[$x]])
                ->one();
            if($err['cqstatus']==1)
            {
//                    失效试题
                $xc = $this->NumItem($pid);
                $insertc = \Yii::$app->db->createCommand()->insert('practail', array('pid' => $pid,
                    'qid' => $arrc[$x], 'qtypeid' => 1, 'status' => 1,'id'=>$xc))->execute();
                $x =$x+1;
            }
            else{
                $x = $x;
            }
        }
        $queryf = (new Query())
            ->select("*")
            ->from("fillq")
            ->max('fqid');
        $arrf = $this->Rand($min, $queryf, $nf);
        for ($x = 0; $x < $nf; ) {
            $err = (new Query())
                ->select('*')
                ->from('fillq')
                ->where(['fqid'=>$arrf[$x]])
                ->one();
            if($err['fqstatus']==1)
            {
                $xf = $this->NumItem($pid);
                $insertf= \Yii::$app->db->createCommand()->insert('practail', array('pid' => $pid,
                    'qid' => $arrf[$x], 'qtypeid' => 2, 'status' => 1,'id'=>$xf))->execute();
                $x =$x+1;
            }
            else{
                $x = $x;
            }
        }
        $queryp = (new Query())
            ->select("*")
            ->from("program")
            ->max('pqid');
        $arrp = $this->Rand($min, $queryp, $np);
        for ($x = 0; $x < $np; ) {
            $err = (new Query())
                ->select('*')
                ->from('program')
                ->where(['pqid'=>$arrp[$x]])
                ->one();
            if($err['pqstatus']==1)
            {
                $xp = $this->NumItem($pid);
                $insertp = \Yii::$app->db->createCommand()->insert('practail', array('pid' => $pid,
                    'qid' => $arrp[$x], 'qtypeid' => 3, 'status' => 1,'id'=>$xp))->execute();
                $x =$x+1;
            }
            else{
                $x = $x;
            }
        }
        $queryj = (new Query())
            ->select("*")
            ->from("judge")
            ->max('jqid');
        $arrj = $this->Rand($min, $queryj, $nj);
        for ($x = 0; $x < $nj; ) {
            $err = (new Query())
                ->select('*')
                ->from('judge')
                ->where(['jqid'=>$arrj[$x]])
                ->one();
            if($err['jqstatus']==1)
            {
                $xj = $this->NumItem($pid);
                $insertj = \Yii::$app->db->createCommand()->insert('practail', array('pid' => $pid,
                    'qid' => $arrj[$x], 'qtypeid' => 5, 'status' => 1,'id'=>$xj))->execute();
                $x =$x+1;
            }
            else{
                $x = $x;
            }
        }
        $querycm = (new Query())
            ->select("*")
            ->from("choosem")
            ->max('mqid');
        $arrcm = $this->Rand($min, $querycm, $ncm);
        for ($x = 0; $x < $ncm; ) {
            $err = (new Query())
                ->select('*')
                ->from('choosem')
                ->where(['mqid'=>$arrcm[$x]])
                ->one();
            if($err['mqstatus']==1)
            {
                $xcm = $this->NumItem($pid);
                $insertcm = \Yii::$app->db->createCommand()->insert('practail', array('pid' => $pid,
                    'qid' => $arrcm[$x], 'qtypeid' => 4, 'status' => 1,'id'=>$xcm))->execute();
                $x =$x+1;
            }
            else{
                $x = $x;
            }
        }
        $createtime = date('Y-m-d H:i:s',time());
        $insertexam = \Yii::$app->db->createCommand()->insert('prac',array('id'=>$pid,'name'=>$name,
            'createtime'=>$createtime,'auth'=>$uid,'status'=>1))->execute();
        if($insertexam)
        {
            return array("data"=>$insertexam,"msg"=>"自动组卷成功");
        }
        else
        {
            return array("data"=>$insertexam,"msg"=>"该试卷已创建");
        }
    }
    /*
     * 查看练习情况
     * 参数：练习题id
     */
    public function actionViewpractice()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $query = (new Query())
            ->select("*")
            ->from('prac')
            ->where(['id'=>$pid])
            ->andWhere(['status'=>1])
            ->one();
        $exname = $query['name'];
        $auth = $this->User($query['auth'])['username'];
        $createTime = $query['createtime'];
        $queryChooseList = (new Query())
            ->select('qid')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->andWhere(['qtypeid'=>1])
            ->andWhere(['status'=>1])
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
            ->from('practail')
            ->where(['pid'=>$pid])
            ->andWhere(['qtypeid'=>2])
            ->andWhere(['status'=>1])
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
            ->from('practail')
            ->where(['pid'=>$pid])
            ->andWhere(['qtypeid'=>3])
            ->andWhere(['status'=>1])
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
            ->from('practail')
            ->where(['pid'=>$pid])
            ->andWhere(['qtypeid'=>5])
            ->andWhere(['status'=>1])
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
            ->from('practail')
            ->where(['pid'=>$pid])
            ->andWhere(['qtypeid'=>4])
            ->andWhere(['status'=>1])
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
        return array("data"=>[$exname,$auth,$createTime,$ChooseList,$FillList,$ProgramList,$judgeList,$choosemList,$query],"msg"=>"预览练习");
    }
    //    用户作答完成，实现用户答案与正确答案的匹配
//提供参数：用户id,试题exid,作答次数：num
    public function Checkans($num,$uid,$pid)
    {
        $query= (new Query())
            ->select('*')
            ->from('pracusertail')
            ->where(['uid'=>$uid])
            ->andWhere(['pid'=>$pid])
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
//                    查找书籍
//                    $bookRem = $this->Book($qc['cqrem']);
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qc['cqans'])
                    {
                        $x = $x+1;
                        $gradec = 1;
//                        if($nw = $qc['err']<=0)
//                            $nw = 0;
//                        else{
//                            $nw = $qc['err']-1;
//                        }
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            if($bookRem[$k]['err']>0)
//                            {
//                                $errb = $bookRem[$k]['err']-1;
//                            }
//                            else
//                            {
//                                $errb = 0;
//                            }
//                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb],
//                                ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    else{
//                        如果用户作答错误，在题库中错误系数err+1，用于推荐,正确则减一，如果为0 则不加不减
//                        $nw = $qc['err']+1;
                        $gradec = 0;
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            $errb = $bookRem[$k]['err']+1;
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    $updatec = \Yii::$app->db->createCommand()->update('pracusertail',['grade'=>$gradec],
                        ['id'=>$num,'uid'=>$uid,'pid'=>$pid,'qtypeid'=>1,'qid'=>$query[$i]['qid']])->execute();
//                    $update_c =  \Yii::$app->db->createCommand()->update('chooseq',['err'=>$nw], ['cqid'=>$query[$i]['qid']])->execute();

                }
                else if($query[$i]['qtypeid']==2)
                {
//                    填空题
                    $qf = (new Query())
                        ->select('*')
                        ->from('fillq')
                        ->where(['fqid'=>$query[$i]['qid']])
                        ->one();
//                    $bookRem = $this->Book($qf['fqrem']);
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qf['fqans'])
                    {
                        $x = $x+1;
                        $gradef =1;
//                        if($nw = $qf['err']<=0)
//                            $nw = 0;
//                        else{
//                            $nw = $qf['err']-1;
//                        }
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            if($bookRem[$k]['err']>0)
//                            {
//                                $errb = $bookRem[$k]['err']-1;
//                            }
//                            else
//                            {
//                                $errb = 0;
//                            }
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
//                        $nw = $qf['err']+1;
                        $gradef =0;
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            $errb = $bookRem[$k]['err']+1;
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    $updatef = \Yii::$app->db->createCommand()->update('pracusertail',['grade'=>$gradef],
                        ['id'=>$num,'uid'=>$uid,'pid'=>$pid,'qtypeid'=>2,'qid'=>$query[$i]['qid']])->execute();
//                    $update_f =  \Yii::$app->db->createCommand()->update('fillq',['err'=>$nw], ['fqid'=>$query[$i]['qid']])->execute();
                }
                else if($query[$i]['qtypeid']==3)
                {
//                  程序题
                    $qp = (new Query())
                        ->select('*')
                        ->from('program')
                        ->where(['pqid'=>$query[$i]['qid']])
                        ->one();
//                    $bookRem = $this->Book($qp['pqrem']);
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qp['pqans'])
                    {
                        $x = $x+1;
                        $gradep = 1;
//                        if($nw = $qp['err']<=0)
//                            $nw = 0;
//                        else{
//                            $nw = $qp['err']-1;
//                        }
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            if($bookRem[$k]['err']>0)
//                            {
//                                $errb = $bookRem[$k]['err']-1;
//                            }
//                            else
//                            {
//                                $errb = 0;
//                            }
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
//                        $nw = $qp['err']+1;
                        $gradep = 0;
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            $errb = $bookRem[$k]['err']+1;
//                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb],
//                                ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    $updatep = \Yii::$app->db->createCommand()->update('pracusertail',['grade'=>$gradep],
                        ['id'=>$num,'uid'=>$uid,'pid'=>$pid,'qtypeid'=>3,'qid'=>$query[$i]['qid']])->execute();
//                    $update_p =  \Yii::$app->db->createCommand()->update('program',['err'=>$nw], ['pqid'=>$query[$i]['qid']])->execute();

                }
                else if($query[$i]['qtypeid']==4)
                {
//                    多选题
                    $qm= (new Query())
                        ->select('*')
                        ->from('choosem')
                        ->where(['mqid'=>$query[$i]['qid']])
                        ->one();
//                    $bookRem = $this->Book($qm['mqrem']);
                    $s = $qm['mqans'];
                    $exp1 = explode('---',$s);
                    $exp2 = explode('---',$query[$i]['ans']);
                    sort($exp2);
                    sort($exp1);
                    if($exp1===$exp2)
                    {
                        $x = $x+1;
                        $mgrade = 1;
//                        if($nw = $qm['err']<=0)
//                            $nw = 0;
//                        else{
//                            $nw = $qm['err']-1;
//                        }
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            if($bookRem[$k]['err']>0)
//                            {
//                                $errb = $bookRem[$k]['err']-1;
//                            }
//                            else
//                            {
//                                $errb = 0;
//                            }
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    else{
                        $mgrade = 0;
                        //                        如果用户作答错误，在题库中错误系数err+1，用于推荐
//                        $nw = $qm['err']+1;
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            $errb = $bookRem[$k]['err']+1;
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    $updatem= \Yii::$app->db->createCommand()->update('pracusertail',['grade'=>$mgrade],
                        ['id'=>$num,'uid'=>$uid,'pid'=>$pid,'qtypeid'=>4,'qid'=>$query[$i]['qid']])->execute();
//                    $update_m =  \Yii::$app->db->createCommand()->update('choosem',['err'=>$nw], ['mqid'=>$query[$i]['qid']])->execute();
                }
                else if($query[$i]['qtypeid']==5)
                {
//                  判断题
                    $qj = (new Query())
                        ->select('*')
                        ->from('judge')
                        ->where(['jqid'=>$query[$i]['qid']])
                        ->one();
//                    $bookRem = $this->Book($qj['jqrem']);
//                    匹配答案，如果答案正确则返回值为1，错误为0
                    if($query[$i]['ans']==$qj['jqans'])
                    {
                        $x = $x+1;
                        $gradej = 1;
//                        if($nw = $qj['err']<=0)
//                            $nw = 0;
//                        else{
//                            $nw = $qj['err']-1;
//                        }
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            if($bookRem[$k]['err']>0)
//                            {
//                                $errb = $bookRem[$k]['err']-1;
//                            }
//                            else
//                            {
//                                $errb = 0;
//                            }
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    else{
                        //                        如果用户作答错误，在题库中错误系数err+0.1，用于推荐
//                        $nw = $qj['err']+1;
                        $gradej = 0;
//                        for($k=0;$k<count($bookRem);$k++)
//                        {
//                            $errb = $bookRem[$k]['err']+1;
////                            $update_book =  \Yii::$app->db->createCommand()->update('bookitem',['err'=>$errb], ['id'=>$bookRem[$k]['id']])->execute();
//                        }
                    }
                    $updatej = \Yii::$app->db->createCommand()->update('pracusertail',['grade'=>$gradej],
                        ['id'=>$num,'uid'=>$uid,'pid'=>$pid,'qtypeid'=>5,'qid'=>$query[$i]['qid']])->execute();
//                    $update_j =  \Yii::$app->db->createCommand()->update('judge',['err'=>$nw], ['jqid'=>$query[$i]['qid']])->execute();
                }
            }
            return $x;
        }
        else{
            return 0;
        }

    }
    /*
     * 练习题匹配答案
     * 参数：
     * 用户id
     * 练习题id
     * 各个题型的答案
     */
    public function actionPracticecheck()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $uid = $request->post('uid');
        $Clist = $request->post('cList');
        $Flist = $request->post('fList');
        $Plist = $request->post('pList');
        $CMlist =$request->post('cmList');
        $Jlist = $request->post('jList');
        $ctime = $request->post('ctime');
        $finishtime = date('Y-m-d H:i:s',time());
        $query = (new Query())
            ->select("*")
            ->from("pracuser")
            ->Where(['uid'=>$uid])
            ->andWhere(['pid'=>$pid])
            ->all();
        if($query){
            //        作答次数
            $id = (new Query())
                ->select("*")
                ->from("pracuser")
                ->Where(['uid'=>$uid])
                ->andWhere(['pid'=>$pid])
                ->max('id')+1;
        }
        else{
            $id =1;
        }
        for($i=0;$i<count($Clist);$i++)
        {
            $cqid = $Clist[$i]['id'];
            $cqans = $Clist[$i]['ans'];
            $cqtypeid = 1;
            $sctime = $Clist[$i]['ctime'];

            $updatec = \Yii::$app->db->createCommand()->insert('pracusertail',
                array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'qid'=>$cqid,'qtypeid'=>$cqtypeid,
                    'ans'=>$cqans,'grade'=>'','ftime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Flist);$i++)
        {
            $fqid = $Flist[$i]['id'];
            $fqans = $Flist[$i]['ans'];
            $fqtypeid = 2;
            $sctime = $Flist[$i]['ctime'];
            $updatef = \Yii::$app->db->createCommand()->insert('pracusertail',
                array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'qid'=>$fqid,'qtypeid'=>$fqtypeid,
                    'ans'=>$fqans,'grade'=>'','ftime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Plist);$i++)
        {
            $pqid = $Plist[$i]['id'];
            $pqans = $Plist[$i]['ans'];
            $pqtypeid = 3;
            $sctime = $Plist[$i]['ctime'];
            $updatep = \Yii::$app->db->createCommand()->insert('pracusertail',
                array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'qid'=>$pqid,'qtypeid'=>$pqtypeid,
                    'ans'=>$pqans,'grade'=>'','ftime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($CMlist);$i++)
        {
            $mqid = $CMlist[$i]['id'];
            $mqans = $CMlist[$i]['ans'];
            $mqtypeid = 4;
            $sctime = $CMlist[$i]['ctime'];
            $updatem = \Yii::$app->db->createCommand()->insert('pracusertail',
                array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'qid'=>$mqid,'qtypeid'=>$mqtypeid,
                    'ans'=>$mqans,'grade'=>'','ftime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        for($i=0;$i<count($Jlist);$i++)
        {
            $jqid = $Jlist[$i]['id'];
            $jqans = $Jlist[$i]['ans'];
            $jqtypeid = 5;
            $sctime = $Jlist[$i]['ctime'];
            $updatef = \Yii::$app->db->createCommand()->insert('pracusertail',
                array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'qid'=>$jqid,'qtypeid'=>$jqtypeid,
                    'ans'=>$jqans,'grade'=>'','ftime'=>$finishtime, 'status'=>1,'ctime'=>$sctime))->execute();
        }
        $n = $this->Checkans($id,$uid,$pid);
        $update = \Yii::$app->db->createCommand()->insert('pracuser',
            array('id'=>$id,'uid'=>$uid,'pid'=>$pid,'grade'=>$n,'fintime'=>$finishtime, 'status'=>1,'ctime'=>$ctime))->execute();
        return array('data'=>$id,"msg"=>$uid."作答".$pid."完成");
    }
    /*
     * 练习雷达图
     */
// 参数：用户id、试卷id、答题次数
//uid eid num
    public function actionRadar()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $pid = $request->post('pid');
        $num = $request->post('num');
        $query =(new Query())
            ->select('*')
            ->from('pracusertail')
            ->where(['pid'=>$pid])
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
            ->from('pracusertail')
            ->where(['id'=>$num])
            ->andWhere(['uid'=>$uid])
            ->andWhere(['pid'=>$pid])
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
    //    获取用户作答的某一次测试的详细结果
//      提供参数：uid:用户id,eid:试卷id,测试次数：num
    public function actionGetuserdetail()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $pid = $request->post('pid');
        $num = $request->post('num');
//        先查找该试卷包含的问题题目
        $query = (new Query())
            ->select('*')
            ->from('prac')
            ->where(['id'=>$pid])
            ->one();
        $query['auth'] = $this->User($query['auth'])['username'];
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
                ->from('practail')
                ->where(['pid'=>$pid])
                ->all();
//            该试题的试题数
            $exNum = count($querys);
            $Num = (new Query())
                ->select('*')
                ->from('pracuser')
                ->where(['id'=>$num])
                ->andWhere(['uid'=>$uid])
                ->andWhere(['pid'=>$pid])
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
                        ->from('pracusertail')
                        ->where(['pid'=>$pid])
                        ->andWhere(['uid'=>$uid])
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
                        ->from('pracusertail')
                        ->where(['pid'=>$pid])
                        ->andWhere(['uid'=>$uid])
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
                        ->from('pracusertail')
                        ->where(['pid'=>$pid])
                        ->andWhere(['uid'=>$uid])
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
                        ->from('pracusertail')
                        ->where(['pid'=>$pid])
                        ->andWhere(['uid'=>$uid])
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
                        ->from('pracusertail')
                        ->where(['pid'=>$pid])
                        ->andWhere(['uid'=>$uid])
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
}