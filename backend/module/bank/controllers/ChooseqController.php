<?php

namespace backend\module\bank\controllers;

use yii\web\Controller;
use yii\common\models\Chooseq;
use yii\db\Query;

/**
 * Default controller for the `bank` module
 * 选择题：固定模式，一个题干，四个选项，设置四选一
 */
class ChooseqController extends Controller
{
    public function actionIndex()
    {
        return "bank-chooseq"; // TODO: Change the autogenerated stub
    }
    /*
     * 选择题
     */

    /**
     * Chooseq model
     * @property integer $cqid
     * @property string $cqitem
     * @property string $cqcho
     * @property string $cqans
     * @property string $cqtail
     * @property string $cqrem
     * @property string $cqstatus
     */
    /*
     * 查找全部的选择题
     * 标志：flag
     * 1:全部的选择题
     * 2：有效的选择题
     * 3：模糊查找某题
     * 4：无效的选择题
     */
    public function actionQuerychoose()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        if($flag==1)
        {
            $query = (new Query())
                ->select("*")
                ->from('chooseq')
                ->all();
            return array("data"=>$query,"msg"=>"全部的选择题");
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select("*")
                ->from('chooseq')
                ->where(['cqstatus'=>1])
                ->all();
            return array("data"=>$query,"msg"=>"有效的选择题");
        }
        else if($flag==3)
        {
            $name = $request->post('name');
            $query = (new Query())
                ->select("*")
                ->from('chooseq')
                ->where(['or',
                    ['like','cqitem',$name],
                    ['like','cqcho',$name],
                    ['like','cqtail',$name],
                    ['like','cqrem',$name],
                    ])
                ->all();
            return array("data"=>$query,"msg"=>$name."选择题");
        }
        else if($flag==4){
            $query = (new Query())
                ->select("*")
                ->from('chooseq')
                ->where(['cqstatus'=>0])
                ->all();
            return array("data"=>$query,"msg"=>"无效的选择题");
        }
        else{
            return array("data"=>$flag,"msg"=>"输入错误");
        }
    }
    /*
     * 增加选择题：参数(题干、选项、答案、详解、相关知识)
     * 选择：设置为四个选项，固定的模式
     */
    public function actionAddchoose()
    {
        $id = (new Query())
            ->select("*")
            ->from('chooseq')
            ->max('cqid');
        $id = $id+1;
        $request = \Yii::$app->request;
        $item = $request->post('qitem');
        $op1 = $request->post('op1');
        $op2 = $request->post('op2');
        $op3 = $request->post('op3');
        $op4 = $request->post('op4');
        $op = $op1.'---'.$op2.'---'.$op3.'---'.$op4;
        $ans = $request->post('ans');
        $tail = $request->post('tail');
        $rem=$request->post('rem');
        $auth = $request->post('auth');
        $query = (new Query())
            ->select('*')
            ->from('chooseq')
            ->where(['cqitem'=>$item])
            ->andWhere(['cqcho'=>$op])
            ->one();
        if($query)
        {
            return array("data"=>$query,"msg"=>"该题已在题库中，请勿重复添加");
        }
        else
        {
            $updatec = \Yii::$app->db->createCommand()->insert('chooseq',
                array('cqid'=>$id,'cqitem'=>$item,'cqcho'=>$op,'cqans'=>$ans,'cqtail'=>$tail,
                    'cqrem'=>$rem,'cqstatus'=>1,'userid'=>$auth))->execute();
            if($updatec)
            {
                return array("data"=>$updatec,"msg"=>"插入选择题成功");
            }
            else{
                return array("data"=>$updatec,"msg"=>"插入失败，该题已插入");
            }
        }
    }

    /*
     * 删除选择题：一个函数实现
     * 给出标志：flag
     * 1:暂时删除
     * 2：永久删除
     * 实际的修改需删除变量
     */
    public function actionDelete()
    {
        $request = \Yii::$app->request;
        $id = $request->post('cid');
        $auth = $request->post('auth');
        $query = (new Query())
            ->select('*')
            ->from('chooseq')
            ->where(['cqid'=>$id])
            ->one();
        if($query)
        {
            $flag = $request->post('flag');
            if($flag==1)
            {
             //暂时删除
                $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqstatus'=>0,'userid'=>$auth],"cqid={$id}")->execute();
                if($updatec)
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题删除成功");
                }
                else
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题已删除，不用重复删除");
                }
            }
            else if($flag==2)
            {
                //永久删除
                $updatec = \Yii::$app->db->createCommand()->delete('chooseq',['cqid'=>$id])->execute();
                if($updatec)
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题永久删除成功");
                }
                else
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题已永久删除，不用重复删除");
                }
            }
            else{
                return array("data"=>[$query,$flag],"msg"=>"输入错误");
            }

        }
        else
        {
            return array("data"=>$query,"msg"=>"没有找到该选择题");
        }
    }

    /*
     * 修改选择题相关内容：一个函数实现
     * 给出标志：flag
     * 1:题干
     * 2：选项=>第i个选项
     * 3：正确选项
     * 4：详解
     * 5：相关知识推荐
     * 6：状态
     * 实际的修改需删除变量
     */
    public function actionChange()
    {
        $request = \Yii::$app->request;
        $id = $request->post('cid');
        $auth = $request->post('auth');
        $query = (new Query())
            ->select('*')
            ->from('chooseq')
            ->where(['cqid'=>$id])
            ->one();
        if($query)
        {
            $flag = $request->post('flag');
            if($flag==1)
            {
                //题干
                $item = $request->post('item');
                if($item==$query['cqitem']){
                    return array("data"=>[$query,$item],"msg"=>"两次题干一致，不能修改");
                }
                else{
                    $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqitem'=>$item,'userid'=>$auth],"cqid={$id}")->execute();
                    if($updatec)
                    {
                        return array("data"=>[$query,$item,$updatec],"msg"=>"该选择题题干修改成功");
                    }
                    else
                    {
                        return array("data"=>[$query,$item,$updatec],"msg"=>"该选择题题干已修改，不用重复修改");
                    }
                }
            }
            else if ($flag==2)
            {
                $top = $request->post('top');
                //选项
                if($top==1)
                {
                    $op1= $request->post('op1');
                    $str = $query['cqcho'];
                    $expl = explode('---',$str);
                    $expl[0]=$op1;
                    $op = implode('---',$expl);
                    if($op==$query['cqcho']){
                        return array("data"=>[$query,$op],"msg"=>"两次选项一致，不能修改");
                    }
                    else{
                        $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqcho'=>$op,'userid'=>$auth],"cqid={$id}")->execute();
                        if($updatec)
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题选项1修改成功");
                        }
                        else
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题1选项已修改，不用重复修改");
                        }
                    }
                }
                else if($top==2)
                {

                    $op2= $request->post('op2');
                    $str = $query['cqcho'];
                    $expl = explode('---',$str);
                    $expl[1]=$op2;
                    $op = implode('---',$expl);
                    if($op==$query['cqcho']){
                        return array("data"=>[$query,$op],"msg"=>"两次选项一致，不能修改");
                    }
                    else{
                        $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqcho'=>$op,'userid'=>$auth],"cqid={$id}")->execute();
                        if($updatec)
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题选项2修改成功");
                        }
                        else
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题2选项已修改，不用重复修改");
                        }
                    }

                }
                else if($top==3)
                {
                    $op3 = $request->post('op3');
                    $str = $query['cqcho'];
                    $expl = explode('---',$str);
                    $expl[2]=$op3;
                    $op = implode('---',$expl);
                    if($op==$query['cqcho']){
                        return array("data"=>[$query,$op],"msg"=>"两次选项一致，不能修改");
                    }
                    else{
                        $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqcho'=>$op,'userid'=>$auth],"cqid={$id}")->execute();
                        if($updatec)
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题选项3修改成功");
                        }
                        else
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题3选项已修改，不用重复修改");
                        }
                    }

                }
                else if($top==4)
                {
                    $op4= $request->post('op4');
                    $str = $query['cqcho'];
                    $expl = explode('---',$str);
                    $expl[3]=$op4;
                    $op = implode('---',$expl);
                    if($op==$query['cqcho']){
                        return array("data"=>[$query,$op],"msg"=>"两次选项一致，不能修改");
                    }
                    else{
                        $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqcho'=>$op,'userid'=>$auth],"cqid={$id}")->execute();
                        if($updatec)
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题选项4修改成功");
                        }
                        else
                        {
                            return array("data"=>[$query,$op,$updatec],"msg"=>"该选择题4选项已修改，不用重复修改");
                        }
                    }
                }
                else{
                    return array("data"=>[$query,$top],"msg"=>"输入错误");
                }
            }
            else if($flag==3)
            {
                //正确答案
                $ans = $request->post('ans');
                if($ans==$query['cqans']){
                    return array("data"=>[$query,$ans],"msg"=>"两次答案一致，不能修改");
                }
                else{
                    $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqans'=>$ans,'userid'=>$auth],"cqid={$id}")->execute();
                    if($updatec)
                    {
                        return array("data"=>[$query,$ans,$updatec],"msg"=>"该选择题答案修改成功");
                    }
                    else
                    {
                        return array("data"=>[$query,$ans,$updatec],"msg"=>"该选择题答案已修改，不用重复修改");
                    }
                }
            }
            else if($flag==4)
            {
                //详解
                $tail = $request->post('tail');
                if($tail==$query['cqtail']){
                    return array("data"=>[$query,$tail],"msg"=>"两次详解一致，不能修改");
                }
                else{
                    $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqtail'=>$tail,'userid'=>$auth],"cqid={$id}")->execute();
                    if($updatec)
                    {
                        return array("data"=>[$query,$tail,$updatec],"msg"=>"该选择题详解修改成功");
                    }
                    else
                    {
                        return array("data"=>[$query,$tail,$updatec],"msg"=>"该选择题详解已修改，不用重复修改");
                    }
                }
            }
            else if($flag==5)
            {
                //相关知识
                $rem = $request->post('rem');
                if($rem==$query['cqrem']){
                    return array("data"=>[$query,$rem],"msg"=>"两次相关知识一致，不能修改");
                }
                else{
                    $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqrem'=>$rem,'userid'=>$auth],"cqid={$id}")->execute();
                    if($updatec)
                    {
                        return array("data"=>[$query,$rem,$updatec],"msg"=>"该选择题相关知识修改成功");
                    }
                    else
                    {
                        return array("data"=>[$query,$rem,$updatec],"msg"=>"该选择题相关知识已修改，不用重复修改");
                    }
                }
            }
            else if($flag==6)
            {
//                状态
                $updatec = \Yii::$app->db->createCommand()->update('chooseq',['cqstatus'=>1,'userid'=>$auth],"cqid={$id}")->execute();
                if($updatec)
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题状态修改成功");
                }
                else
                {
                    return array("data"=>[$query,$updatec],"msg"=>"该选择题状态已修改，不用重复修改");
                }
            }
            else{
                return array("data"=>$query,"msg"=>"输入错误");
            }
        }
        else{
            return array("data"=>$query,"msg"=>"未查找到该选择题");
        }
    }
    public function actionImportexcel()
    {
        $request = \Yii::$app->request;
        $data = $request->post('data');
        $data = json_decode($data,true);
        for($i=0;$i<count($data);$i++)
        {
            $item= isset($data[$i]['item'])?$data[$i]['item']:"";
            $op1 = isset($data[$i]['op1'])?$data[$i]['op1']:"";
            $op2 = isset($data[$i]['op2'])?$data[$i]['op2']:"";
            $op3 = isset($data[$i]['op3'])?$data[$i]['op3']:"";
            $op4 = isset($data[$i]['op4'])?$data[$i]['op4']:"";
            $op = $op1.'---'.$op2.'---'.$op3.'---'.$op4;
            $ans= isset($data[$i]['ans'])?$data[$i]['ans']:"";
            $tail = isset($data[$i]['tail'])?$data[$i]['tail']:"";
            $rem = isset($data[$i]['rem'])?$data[$i]['rem']:"";
            $auth = isset($data[$i]['auth'])?$data[$i]['auth']:"";
            $query = (new Query())
                ->select('*')
                ->from('chooseq')
                ->where(['cqitem'=>$item])
                ->andWhere(['cqcho'=>$op])
                ->one();
            $id = (new Query())
                ->select("*")
                ->from('chooseq')
                ->where(['cqstatus'=>1])
                ->max('cqid');
            $id = $id+1;
            if($query == null)
            {
                $updatec = \Yii::$app->db->createCommand()->insert('chooseq',
                    array('cqid'=>$id,'cqitem'=>$item,'cqcho'=>$op,'cqans'=>$ans,'cqtail'=>$tail,
                        'cqrem'=>$rem,'cqstatus'=>1,'userid'=>$auth))->execute();
            }
        }
        return array("data"=>$data,"msg"=>"导入成功");
    }
}