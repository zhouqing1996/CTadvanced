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
        $update = \Yii::$app->db->createCommand()->insert('useranss',
            array('id'=>$id,'userid'=>$uid,'exid'=>$eid,'grade'=>'','finishtime'=>$finishtime, 'status'=>1,'ctime'=>$ctime))->execute();

        return array('data'=>[$update],"msg"=>$uid."作答".$eid."完成");
    }
}