<?php

namespace backend\module\exam\controllers;

use phpDocumentor\Reflection\Types\This;
use yii\db\Exception;
use yii\web\Controller;
use yii\common\models\Exam;
use yii\common\models\Examtail;
use yii\common\models\Chooseq;
use yii\common\models\Fillq;
use yii\common\models\Programq;
use yii\web\Response;
use yii\web\Request;
use yii\db\Query;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use yii\behaviors\TimestampBehavior;

/**
 * Default controller for the `exam` module
 */
class IndexController extends Controller
{
    public function actionIndex()
    {
        return "exam-index"; // TODO: Change the autogenerated stub
    }
//    根据用户id获取用户名
    public function UserName($id)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->one();
    }
    /*
     * 查看全部的试卷
     * 标志：flag
     * 1：有效试卷
     * 2：已删除试卷
     * 3：所有试卷
     * 4:模糊查找
     * 5：学生查看试卷：只能查看他选择的导师的试卷   参数：sid
     */
    public function actionQueryexam()
    {
        $request=\Yii::$app->request;
        $flag = $request->post("flag");
        if($flag==1)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->where(['exstatus'=>1])
                ->orderBy(['exid'=>SORT_DESC])
                ->all();
            $list = [];
            for($i=0;$i<count($query);$i++)
            {
                $list[$i]['auth'] = $this->UserName($query[$i]['auth'])['username'];
                $list[$i]['exname'] = $query[$i]['exname'];
                $list[$i]['exid'] = $query[$i]['exid'];
                $list[$i]['createtime'] = $query[$i]['createtime'];
                $list[$i]['gdtime'] = $query[$i]['gdtime'];
                $list[$i]['exstatus'] = $query[$i]['exstatus'];
            }
            return array("data"=>$list,"msg"=>"有效试卷");
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->where(['exstatus'=>0])
                ->orderBy(['exid'=>SORT_DESC])
                ->all();
            $list=[];
            for($i=0;$i<count($query);$i++)
            {
                $list[$i]['auth'] = $this->UserName($query[$i]['auth'])['username'];
                $list[$i]['exname'] = $query[$i]['exname'];
                $list[$i]['exid'] = $query[$i]['exid'];
                $list[$i]['createtime'] = $query[$i]['createtime'];
                $list[$i]['gdtime'] = $query[$i]['gdtime'];
                $list[$i]['exstatus'] = $query[$i]['exstatus'];
            }
            return array("data"=>$list,"msg"=>"无效试卷");
        }
        else if($flag==3)
        {
            $query = (new Query())
                ->select('*')
                ->from('exam')
                ->orderBy(['exid'=>SORT_DESC])
                ->all();
            $list = [];
            for($i=0;$i<count($query);$i++)
            {
                $list[$i]['auth'] = $this->UserName($query[$i]['auth'])['username'];
                $list[$i]['exname'] = $query[$i]['exname'];
                $list[$i]['exid'] = $query[$i]['exid'];
                $list[$i]['createtime'] = $query[$i]['createtime'];
                $list[$i]['gdtime'] = $query[$i]['gdtime'];
                $list[$i]['exstatus'] = $query[$i]['exstatus'];
            }
            return array("data"=>$list,"msg"=>"全部试卷");
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
                ->orderBy(['exid'=>SORT_DESC])
                ->all();
            $list = [];
            for($i=0;$i<count($query);$i++)
            {
                $list[$i]['auth'] = $this->UserName($query[$i]['auth'])['username'];
                $list[$i]['exname'] = $query[$i]['exname'];
                $list[$i]['exid'] = $query[$i]['exid'];
                $list[$i]['createtime'] = $query[$i]['createtime'];
                $list[$i]['gdtime'] = $query[$i]['gdtime'];
                $list[$i]['exstatus'] = $query[$i]['exstatus'];
            }
            return array("data"=>$list,"msg"=>$name."全部试卷");
        }
        else if($flag==5)
        {
            $sid = $request->post('sid');
//            查找其导师
            $queryt = (new Query())
                ->select('*')
                ->from('student')
                ->where(['sid'=>$sid])
                ->all();
            $list = [];
            $k =0;
            for($i=0;$i<count($queryt);$i++)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('exam')
                    ->where(['exstatus'=>1])
                    ->andWhere(['auth'=>$queryt[$i]['tid']])
                    ->orderBy(['exid'=>SORT_DESC])
                    ->all();
                for($j=0;$j<count($query);$j++)
                {
                    $list[$k]['exid']=$query[$j]['exid'];
                    $list[$k]['exname']=$query[$j]['exname'];
                    $list[$k]['createtime']=$query[$j]['createtime'];
                    $list[$k]['auth']=$this->UserName($query[$j]['auth'])['username'];
                    $list[$k]['gdtime']=$query[$j]['gdtime'];
                    $list[$k]['exstatus']=$query[$j]['exstatus'];
                    $k++;
                }
            }
            return array("data"=>$list,"msg"=>"有效试卷");
        }
        else{
            return array("data"=>$flag,"msg"=>"输入错误");
        }

    }
    /*
     * 生成随机数函数:要求生成的随机数不重复
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
    public function actionTest()
    {
        $min = 1;
        $max = 20;
        $num =15;
        $arr = $this->Rand($min,$max,$num);
        return array("data"=>$arr,"msg"=>"产生的随机数");
    }
//    试卷中第几题
//参数：eid,
    public function NumItem($eid)
    {
        return (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid'=>$eid])
            ->max('id');
    }
    /*
     * 组卷：
     * 两种形式：自动组卷（随机）、人为选择组卷
     * 选择标志：flag
     * 1:自动组卷：设定选择题、填空题、程序题数量n,在各自题库中随机抽取组合成为一套试卷；
     * 参数：试卷名（exname）、选择题数（numc）、填空题数（numf）、程序题数（nump）
     * 2:人为选择组卷
     *     k     1：(预览)：手工选择各自题库中的题目组成一套试卷；
     *           2:加入试卷中
     *
     * 3:创建试卷（重新创建新的试卷，不同于从题库中选择。）
     *          该部分需要的前端获取用户添加的题目的详细信息，将用户添加的试卷信息添加至数据库中
     *          插入的数据有：题库信息、试卷信息
     *
     * 参数：
     */
    public function actionAddexam()
    {
        set_time_limit(0);
        $request = \Yii::$app->request;
        $flag = $request->post("flag");
        $id = (new Query())
            ->select("*")
            ->from("exam")
            ->max('exid');
        $id = $id+1;
        $exname = $request->post('exname');
        if($flag==1) {
            //自动组卷
            /*
             * 自动组卷中设定选择题，填空题，程序题的个数
             */
            $numc = $request->post('numc');
            $numf = $request->post('numf');
            $nump = $request->post('nump');
            $numj = $request->post('numj');
            $numcm = $request->post('numcm');
            $gdtime = $request->post('gdtime');

            //不考虑题库中题数目不足的情况
            //所有的题库表的id都是从1开始的，从中产生随机数id，插入到数据库中；
            $min = 1;
            //选择选择题
            $queryc = (new Query())
                ->select("*")
                ->from("chooseq")
                ->max('cqid');
            $arrc = $this->Rand($min, $queryc, $numc);

            for ($x = 0; $x < $numc; ) {
                $err = (new Query())
                    ->select('*')
                    ->from('chooseq')
                    ->where(['cqid'=>$arrc[$x]])
                    ->one();
                if($err['cqstatus']==1)
                {
//                    失效试题
                    $xc = $this->NumItem($id)+1;
                    $insertc = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $arrc[$x], 'qtypeid' => 1, 'exstatus' => 1,'id'=>$xc))->execute();
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
            $arrf = $this->Rand($min, $queryf, $numf);
            for ($x = 0; $x < $numf; ) {
                $err = (new Query())
                    ->select('*')
                    ->from('fillq')
                    ->where(['fqid'=>$arrf[$x]])
                    ->one();
                if($err['fqstatus']==1)
                {
                    $xf = $this->NumItem($id)+1;
                    $insertf= \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $arrf[$x], 'qtypeid' => 2, 'exstatus' => 1,'id'=>$xf))->execute();
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
            $arrp = $this->Rand($min, $queryp, $nump);
            for ($x = 0; $x < $nump; ) {
                $err = (new Query())
                    ->select('*')
                    ->from('program')
                    ->where(['pqid'=>$arrp[$x]])
                    ->one();
                if($err['pqstatus']==1)
                {
                    $xp = $this->NumItem($id)+1;
                    $insertp = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $arrp[$x], 'qtypeid' => 3, 'exstatus' => 1,'id'=>$xp))->execute();
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
            $arrj = $this->Rand($min, $queryj, $numj);
            for ($x = 0; $x < $numj; ) {
                $err = (new Query())
                    ->select('*')
                    ->from('judge')
                    ->where(['jqid'=>$arrj[$x]])
                    ->one();
                if($err['jqstatus']==1)
                {
                    $xj = $this->NumItem($id)+1;
                    $insertj = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $arrj[$x], 'qtypeid' => 5, 'exstatus' => 1,'id'=>$xj))->execute();
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
            $arrcm = $this->Rand($min, $querycm, $numcm);
            for ($x = 0; $x < $numcm; ) {
                $err = (new Query())
                    ->select('*')
                    ->from('choosem')
                    ->where(['mqid'=>$arrcm[$x]])
                    ->one();
                if($err['mqstatus']==1)
                {
                    $xcm = $this->NumItem($id)+1;
                    $insertcm = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $arrcm[$x], 'qtypeid' => 4, 'exstatus' => 1,'id'=>$xcm))->execute();
                    $x =$x+1;
                }
                else{
                    $x = $x;
                }
            }
            $auth = $request->post('auth');
            $createtime = date('Y-m-d H:i:s',time());
            $insertexam = \Yii::$app->db->createCommand()->insert('exam',array('exid'=>$id,'exname'=>$exname,
                'createtime'=>$createtime,'auth'=>$auth,'exstatus'=>1,'gdtime'=>$gdtime))->execute();
            if($insertexam)
            {
                return array("data"=>$insertexam,"msg"=>"自动组卷成功");
            }
            else
            {
                return array("data"=>$insertexam,"msg"=>"该试卷已创建");
            }
        }
        else if($flag==2)
        {
            //手动组卷
            $k = $request->post('k');
            if($k==1){
//                预览问卷
                $chooseList = $request->post('chooseList');
                $choose = array();
                for($i=0;$i<count($chooseList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('chooseq')
                        ->where(['cqid'=>$chooseList[$i]])
                        ->one();
                    array_push($choose,$c);
                }
                $fillList= $request->post('fillList');
                $fill = array();
                for($i=0;$i<count($fillList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('fillq')
                        ->where(['fqid'=>(int)$fillList[$i]])
                        ->one();
                    array_push($fill,$c);
                }
                $judgeList= $request->post('judgeList');
                $judge = array();
                for($i=0;$i<count($judgeList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('judge')
                        ->where(['jqid'=>(int)$judgeList[$i]])
                        ->one();
                    array_push($judge,$c);
                }
                $choosemList= $request->post('choosemList');
                $choosem = array();
                for($i=0;$i<count($choosemList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('choosem')
                        ->where(['mqid'=>(int)$choosemList[$i]])
                        ->one();
                    array_push($choosem,$c);
                }
                $programList= $request->post('programList');
                $program = array();
                for($i=0;$i<count($programList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('program')
                        ->where(['pqid'=>(int)$programList[$i]])
                        ->one();
                    array_push($program,$c);
                }
                return array("data"=>[$choose,$fill,$judge,$choosem,$program],"msg"=>"预览试卷");
            }
            else if($k==2)
            {
//                $id = (new Query())
//                    ->select("*")
//                    ->from("exam")
//                    ->max('exid');
//                $id = $id+1;
//                添加至问卷中
                $chooseList = $request->post('chooseList');
                for($i=0;$i<count($chooseList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('chooseq')
                        ->where(['cqid'=>$chooseList[$i]])
                        ->one();
                    $xc = $this->NumItem($id)+1;
                    $insertc = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $c['cqid'], 'qtypeid' => 1, 'exstatus' => 1,'id'=>$xc))->execute();
                }
                $fillList= $request->post('fillList');
                for($i=0;$i<count($fillList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('fillq')
                        ->where(['fqid'=>(int)$fillList[$i]])
                        ->one();
                    $xf = $this->NumItem($id)+1;
                    $insertf = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $c['fqid'], 'qtypeid' => 2, 'exstatus' => 1,'id'=>$xf))->execute();
                }
                $judgeList= $request->post('judgeList');
                for($i=0;$i<count($judgeList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('judge')
                        ->where(['jqid'=>(int)$judgeList[$i]])
                        ->one();
                    $xj = $this->NumItem($id)+1;
                    $insertj = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $c['jqid'], 'qtypeid' => 5, 'exstatus' => 1,'id'=>$xj))->execute();
                }
                $choosemList= $request->post('choosemList');
                for($i=0;$i<count($choosemList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('choosem')
                        ->where(['mqid'=>(int)$choosemList[$i]])
                        ->one();
                    $xcm = $this->NumItem($id)+1;
                    $insertm = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $c['mqid'], 'qtypeid' => 4, 'exstatus' => 1,'id'=>$xcm))->execute();
                }
                $programList= $request->post('programList');
                for($i=0;$i<count($programList);$i++)
                {
                    $c = (new Query())
                        ->select("*")
                        ->from('program')
                        ->where(['pqid'=>(int)$programList[$i]])
                        ->one();
                    $xp = $this->NumItem($id)+1;
                    $insertp = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $c['pqid'], 'qtypeid' => 3, 'exstatus' => 1,'id'=>$xp))->execute();
                }
                $exname = $request->post('exname');
                $auth = $request->post('auth');
                $gdtime = $request->post('gdtime');
                $createtime = date('Y-m-d H:i:s',time());
                $insertexam = \Yii::$app->db->createCommand()->insert('exam',array('exid'=>$id,'exname'=>$exname,
                    'createtime'=>$createtime,'auth'=>$auth,'exstatus'=>1,'gdtime'=>$gdtime))->execute();
                if($insertexam)
                {
                    return array("data"=>[$insertexam],"msg"=>"完成问卷试卷");
                }
                else{
                    return array("data"=>[],"msg"=>"出现错误");
                }


            }
            else{
                return array("data"=>$k,"msg"=>"输入错误");
            }

        }
        else if($flag==3)
        {
//            创建试卷
            $id = (new Query())
                ->select("*")
                ->from("exam")
                ->max('exid');
            $id = $id+1;
            $Clist = $request->post('CList');
            $Flist = $request->post('FList');
            $Plist = $request->post('PList');
            $CMlist =$request->post('CMList');
            $Jlist = $request->post('JList');
            $auth = $request->post('auth');
//            return array("data"=>[$Clist,$Flist],"msg"=>"测试");
            for($i=0;$i<count($Clist);$i++)
            {
                $op = $Clist[$i]['cqcho1'].'---'.$Clist[$i]['cqcho2'].'---'.$Clist[$i]['cqcho3'].'---'.$Clist[$i]['cqcho4'];
                $quesy = (new Query())
                    ->select("*")
                    ->from('chooseq')
                    ->where(['cqitem'=>$Clist[$i]['cqitem']])
                    ->andWhere(['cqtail'=>$Clist[$i]['cqtail']])
                    ->andWhere(['cqcho'=>$op])
                    ->andWhere(['cqrem'=>$Clist[$i]['cqrem']])
                    ->one();
                $xc = $this->NumItem($id)+1;
                if($quesy)
                {
                    $insertc = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $quesy['cqid'], 'qtypeid' => 1, 'exstatus' => 1,'id'=>$xc))->execute();
                }
                else{
                    $idc = (new Query())
                        ->select("*")
                        ->from('chooseq')
                        ->max('cqid');
                    $idc = $idc+1;
                    $updatec = \Yii::$app->db->createCommand()->insert('chooseq',
                        array('cqid'=>$idc,'cqitem'=>$Clist[$i]['cqitem'],'cqcho'=>$op,'cqans'=>$Clist[$i]['cqans'],'cqtail'=>$Clist[$i]['cqtail'],
                            'cqrem'=>$Clist[$i]['cqrem'],'cqstatus'=>1,'userid'=>$auth))->execute();
                    $insertc = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $idc, 'qtypeid' => 1, 'exstatus' => 1,'id'=>$xc))->execute();
                }
            }
            for($i=0;$i<count($Flist);$i++)
            {
                $q = (new Query())
                    ->select("*")
                    ->from('fillq')
                    ->where(['fqitem'=>$Flist[$i]['fitem']])
                    ->andWhere(['fqans'=>$Flist[$i]['fans']])
                    ->andWhere(['fqtail' => $Flist[$i]['ftail']])
                    ->andWhere(['fqrem' => $Flist[$i]['frem']])
                    ->one();
                $xf = $this->NumItem($id)+1;
                if($q)
                {
                    $insertf = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $q['fqid'], 'qtypeid' => 2, 'exstatus' => 1,'id'=>$xf))->execute();
                }
                else{
                    $idf = (new Query())
                        ->select("*")
                        ->from('fillq')
                        ->max('fqid');
                    $idf = $idf + 1;
                    $updatef = \Yii::$app->db->createCommand()->insert('fillq',
                        array('fqid' => $idf, 'fqitem' => $Flist[$i]['fitem'], 'fqans' => $Flist[$i]['fans'], 'fqtail' => $Flist[$i]['ftail'],
                            'fqrem' => $Flist[$i]['frem'], 'fqstatus' => 1,'userid'=>$auth))->execute();
                    $insertf = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $idf, 'qtypeid' => 2, 'exstatus' => 1,'id'=>$xf))->execute();
                }
            }
            for($i=0;$i<count($Jlist);$i++)
            {
                $ans = $Jlist[$i]['jans'];
                $q = (new Query())
                    ->select('*')
                    ->from('judge')
                    ->where(['jqitem' => $Jlist[$i]['jitem']])
                    ->andWhere(['jqans' => $ans])
                    ->andWhere(['jqtail' => $Jlist[$i]['jtail']])
                    ->andWhere(['jqrem' => $Jlist[$i]['jrem']])
                    ->one();
                $xj = $this->NumItem($id)+1;
                if($q)
                {
                    $insertj = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $q['jqid'], 'qtypeid' => 5, 'exstatus' => 1,'id'=>$xj))->execute();
                }
                else{
                    $idj = (new Query())
                        ->select("*")
                        ->from('judge')
                        ->max('jqid');
                    $idj = $idj + 1;
                    $updatej = \Yii::$app->db->createCommand()->insert('judge',
                        array('jqid' => $idj, 'jqitem' => $Jlist[$i]['jitem'], 'jqans' => $Jlist[$i]['jans'], 'jqtail' => $Jlist[$i]['jtail'],
                            'jqrem' => $Jlist[$i]['jrem'], 'jqstatus' => 1,'userid'=>$auth))->execute();
                    $insertj = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $idj, 'qtypeid' => 5, 'exstatus' => 1,'id'=>$xj))->execute();
                }

            }
            for($i=0;$i<count($Plist);$i++)
            {
                $q = (new Query())
                    ->select('*')
                    ->from('program')
                    ->where(['pqitem' => $Plist[$i]['pitem']])
                    ->andWhere(['pqans' => $Plist[$i]['pans']])
                    ->andWhere(['pqtail' => $Plist[$i]['ptail']])
                    ->andWhere(['pqrem' => $Plist[$i]['prem']])
                    ->one();
                $xp = $this->NumItem($id)+1;
                if($q)
                {
                    $insertp = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $q['pqid'], 'qtypeid' => 3, 'exstatus' => 1,'id'=>$xp))->execute();
                }
                else{
                    $idp = (new Query())
                        ->select("*")
                        ->from('program')
                        ->max('pqid');
                    $idp = $idp + 1;
                    $updatep = \Yii::$app->db->createCommand()->insert('program',
                        array('pqid' => $idp, 'pqitem' => $Plist[$i]['pitem'], 'pqans' => $Plist[$i]['pans'], 'pqtail' => $Plist[$i]['ptail'],
                            'pqrem' => $Plist[$i]['prem'], 'pqstatus' => 1,'userid'=>$auth))->execute();
                    $insertp = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $idp, 'qtypeid' => 3, 'exstatus' => 1,'id'=>$xp))->execute();
                }
            }
            for($i=0;$i<count($CMlist);$i++)
            {
                $op = $CMlist[$i]['mcho1'].'---'.$CMlist[$i]['mcho2'].'---'.$CMlist[$i]['mcho3'].'---'.$CMlist[$i]['mcho4'];
                $q = (new Query())
                    ->select("*")
                    ->from('choosem')
                    ->where(['mqitem'=>$CMlist[$i]['mitem']])
                    ->andWhere(['mqcho'=>$op])
                    ->andWhere(['mqans'=>$CMlist[$i]['mans']])
                    ->andWhere(['mqtail'=>$CMlist[$i]['mtail']])
                    ->andWhere(['mqrem'=>$CMlist[$i]['mrem']])
                    ->one();
                $xcm = $this->NumItem($id)+1;
                if($q)
                {
                    $insertm = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $q['mqid'], 'qtypeid' => 4, 'exstatus' => 1,'id'=>$xcm))->execute();
                }
                else{
                    $idcm = (new Query())
                        ->select("*")
                        ->from('choosem')
                        ->max('mqid');
                    $idcm = $idcm+1;

                    $updatecm = \Yii::$app->db->createCommand()->insert('choosem',
                        array('mqid'=>$idcm,'mqitem'=>$CMlist[$i]['mitem'],'mqcho'=>$op,'mqans'=>$CMlist[$i]['mans'],'mqtail'=>$CMlist[$i]['mtail'],
                            'mqrem'=>$CMlist[$i]['mrem'],'mqstatus'=>1,'userid'=>$auth))->execute();
                    $insertm = \Yii::$app->db->createCommand()->insert('examtail', array('exid' => $id,
                        'qid' => $idcm, 'qtypeid' => 4, 'exstatus' => 1,'id'=>$xcm))->execute();
                }
            }

            $createtime = date('Y-m-d H:i:s',time());
            $gdtime=$request->post('gdtime');
//            $gdtime='120';
            $insertexam = \Yii::$app->db->createCommand()->insert('exam',array('exid'=>$id,'exname'=>$exname,
                'createtime'=>$createtime,'auth'=>$auth,'exstatus'=>1,'gdtime'=>$gdtime))->execute();
            if($insertexam)
            {
                return array("data"=>[$insertexam],"msg"=>"完成创建试卷");
            }
            else{
                return array("data"=>[],"msg"=>"出现错误");
            }
        }
        else{
            return array("data"=>$flag,"msg"=>"输入错误");
        }

    }
    /*
     * 删除试卷：永久删除和暂时删除
     * 选择标志：flag
     * 1:暂时删除
     * 2：永久删除
     * 试卷删除时需要更新试卷表和试卷详情表
     * 参数：试卷id(exid)
     */
    public function actionDeleteexam()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        $id =$request->post('exid');
        if($flag==1)
        {
            $query = (new Query())
                ->select("*")
                ->from('exam')
                ->where(['exid'=>$id])
                ->andWhere(['exstatus'=>1])
                ->one();
            //暂时删除
            if($query)
            {
                $update1 = \Yii::$app->db->createCommand()->update('exam',['exstatus'=>0],"exid={$id}")->execute();
                $update2 = \Yii::$app->db->createCommand()->update('examtail',['exstatus'=>0],"exid={$id}")->execute();
                if($update1 and $update2)
                {
                    return array("data"=>[$flag,$update1,$update2],"msg"=>"删除成功");
                }
                else{
                    return array("data"=>[$flag,$update1,$update2],"msg"=>"已删除，请勿重复删除");
                }
            }
            else{
                return array("data"=>$flag,"msg"=>"未找到该试卷");
            }
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select("*")
                ->from('exam')
                ->where(['exid'=>$id])
                ->andWhere(['exstatus'=>0])
                ->one();
            //永久删除
            if($query)
            {
                $delete1 = \Yii::$app->db->createCommand()->delete('exam',['exid'=>$id])->execute();
                $delete2 = \Yii::$app->db->createCommand()->delete('examtail',['exid'=>$id])->execute();
                if($delete1 and $delete2)
                {
                    return array("data"=>[$flag,$delete1,$delete2],"msg"=>"完全删除成功");
                }
                else{
                    return array("data"=>[$flag,$delete1,$delete2],"msg"=>"已完全删除，请勿重复删除");
                }
            }
            else{
                return array("data"=>$flag,"msg"=>"未找到该试卷");
            }
        }
        else{
            return array("data"=>$flag,"msg"=>"输入错误");
        }
    }
    /*
     * 修改
     * 1：状态
     */
    public function actionChangestatus(){
        $request = \Yii::$app->request;
        $id = $request->post('exid');
        $query = (new Query())
            ->select("*")
            ->from('exam')
            ->where(['exid'=>$id])
            ->andWhere(['exstatus'=>0])
            ->one();
        if($query)
        {
            $update2 = \Yii::$app->db->createCommand()->update('examtail',['exstatus'=>1],"exid={$id}")->execute();
            $update1 = \Yii::$app->db->createCommand()->update('exam',['exstatus'=>1],"exid={$id}")->execute();
            return array("data"=>$query,"msg"=>"修改状态成功");
        }
        else{
            return array("data"=>$id,"msg"=>"未找到");
        }
    }
    /*
     * 查看试卷的创建人
     */
    public function ExamInfo($eid)
    {
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$eid])
            ->one();
        return array('data'=>$query,'msg'=>'试卷信息');
    }
    /*
     * 个人作答情况查看：
     * 参数：用户id :uid
     */
    public function actionUserresult()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
//        $uid = 13;
        $query = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['userid'=>$uid])
            ->all();
        if($query)
        {
            $eid = array_merge(array_unique(array_column($query,'exid')));
            $list = [];
            for($i=0;$i<count($eid);$i++)
            {
                $query1 = (new Query())
                    ->select('*')
                    ->from('useranss')
                    ->where(['userid'=>$uid])
                    ->andWhere(['exid'=>$eid[$i]])
                    ->all();

                $list[$i]['exid'] = $eid[$i];
                //                作答次数
                $list[$i]['num'] = count($query1);
                //                最高分
                $list[$i]['score'] = max(array_column($query1,'grade'));
                //                最后完成时间
                $list[$i]['lastTime'] = max(array_column($query1,'finishtime'));
                $li = $this->ExamInfo($eid)['data'];
                $list[$i]['exname'] = $li['exname'];
                $list[$i]['createtime'] = $li['createtime'];
                $list[$i]['auth'] = $this->UserName($li['auth'])['username'];
                $list[$i]['gdtime'] = $li['gdtime'];

            }
            return array('data'=>$list,'msg'=>'用户作答情况');
        }
        else
        {
            return array('data'=>$uid,'msg'=>'该用户暂无作答情况');
        }
    }

}