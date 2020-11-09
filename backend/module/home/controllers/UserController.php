<?php
namespace backend\module\home\controllers;

use common\models\Exam;
use yii\db\Exception;
use yii\web\Controller;
use yii\common\models\User;
use yii\web\Response;
use yii\web\Request;
use yii\db\Query;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\filters\Cors;
use yii\behaviors\TimestampBehavior;
use backend\module\home\controllers\IndexController;
use yii\data\Pagination;

/**
 * Default controller for the `home` module
 */
class UserController extends Controller
{
    public function actionIndex()
    {
        return "user"; // TODO: Change the autogenerated stub
    }

    //用户管理，用于普通人身份
    //设置功能：修改密码、返回个人信息

    /*
     * 修改密码：参数（用户id/用户名）
     */
    public function actionChangepassword()
    {
//        $request = \Yii::$app->request;
//        $username = $request->post('username');
//        $userid = $request->post('userid');
//        两者知道一个即可
        $userid = "2";
        $password="zhouqing";

        $query = (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$userid])
            ->andWhere(['status'=>1])
            ->one();
        if($query)
        {
            $passwordD = \backend\module\home\controllers\IndexController::PasswordDecry($query['password']);
            if($password == $passwordD)
            {
                return array("data"=>[$passwordD,$password],"msg"=>"和原始密码一致");
            }
            else{
                $passwordE = \backend\module\home\controllers\IndexController::PasswordEncry($password);
                $update=\Yii::$app->db->createCommand()->update('user',['password'=>$passwordE],"id={$userid}")->execute();
                if($update)
                {
                    return array("data"=>[$update,$passwordE,$password],"msg"=>"修改密码成功");
                }
                else{
                    return array("data"=>[$update,$password,$passwordE],"msg"=>"修改密码失败");
                }
            }
        }
        else{
            return array("data"=>$query,"msg"=>"未找到该用户");
        }
    }
    /*
     * 返回个人信息：参数（用户id或者用户名）
     */
    public function actionGetuser()
    {
//        $request = \Yii::$app->request;
//        $username = $request->post('username');
//        $userid = $request->post('userid');
        $userid = "2";
        $query = (new Query())
            ->select("*")
            ->from('user')
            ->where(['id'=>$userid])
            ->andWhere(['status'=>1])
            ->one();
        if($query)
        {
            return array("data"=>$query,"msg"=>"查找到该用户信息");
        }
        else
        {
            return array("data"=>$query,"msg"=>"未找到该用户");
        }
    }


    //用户管理，用于管理员身份
    //设置功能：查看所有用户信息(所有信息、管理层信息、普通用户信息)、添加用户、修改（用户名、密码）、删除用户（暂时删除或者永久删除）、变更身份

    /*
     * 按照用户名查找用户
     */
    public function actionQueryname()
    {
        $request = \Yii::$app->request;
        $name = $request->post('name');
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->Where(['username'=> $name])
            ->all();
        return array("data"=>$query,"msg"=>$name."的信息");
    }

    /*
    *查找所有用户，
     * 标志：flag
     * 1:有效的用户
     * 2:所有的用户
     * 3:无效的用户
     * 4:模糊查找
     * 5:教师
     * 6：学生
     * 显示用户信息时，不保存用户的密码信息，仅显示用户名、角色
     */
    public function actionQuery()
    {
        $request = \Yii::$app->request;
        $flag = $request->post('flag');
        if($flag==1)
        {
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['status'=> 1])
                ->all();
            return array("data"=>$query,"msg"=>"所有有效的用户");
        }
        else if($flag==2)
        {
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->all();
            return array("data"=>$query,"msg"=>"所有用户");
        }
        else if($flag==3)
        {
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['status'=> 0])
                ->all();
            return array("data"=>$query,"msg"=>"所有无效的用户");
        }
        else if($flag==4)
        {
            $name = $request->post('name');
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->where(['or',
                    ['like', 'username', $name],
                    ['like', 'id', $name],
                ])
                ->all();
            return array("data"=>$query,"msg"=>$name."用户");
        }
        else if($flag==5)
        {
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['role'=> 2])
                ->all();
            return array("data"=>$query,"msg"=>"所有教师用户");
        }
        else if($flag==6)
        {
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['role'=> 3])
                ->all();
            return array("data"=>$query,"msg"=>"所有学生用户");
        }
        else{
            return array("data"=>[$flag,'0'],"msg"=>"输入错误");
        }
    }
    /*
     * 查看管理层信息
     */
    public function actionQueryrole2()
    {
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->where(['role'=>2])
            ->andWhere(['status'=> 1])
            ->all();
        return array("data"=>$query,"msg"=>"管理层用户信息");
    }
    /*
     * 查看普通用户信息
     */
    public function actionQueryrole3()
    {
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->where(['role'=>3])
            ->andWhere(['status'=> 1])
            ->all();
        return array("data"=>$query,"msg"=>"普通用户信息");
    }
    /*
     * 按照id查找人
     */
    public function Queryid($id)
    {
//        $request =\Yii::$app->request;
//        $id = $request->post('id');
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->one();
        return array("data"=>$query,"msg"=>"id为：".$id."用户信息");
    }
    /*
     * 添加用户
     * 其中用户id、用户名、password、role、status是必不可少的内容
     * id为关键码，不可重复
     * 其中默认密码为123456、角色为3普通用户（可前端设计下拉框选择添加用户的身份）
     */
    public function actionAdduser()
    {
        $request = \Yii::$app->request;
        $username = $request->post('addname');
        $password = $request->post('addpwd');
        $passwordE = \backend\module\home\controllers\IndexController::PasswordEncry($password);
        $role = $request->post('addrole');
        $status = $request->post('addstatus');
        $userid = (new Query())
            ->select('*')
            ->from('user')
            ->max('id');
        $id = $userid +1;
        //如果用户名已存在判断该用户已经添加过了，不能添加
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->Where(['username'=> $username])
            ->andWhere(['role'=>$role])
            ->one();
        if($query){
            return array("data"=>[$query],"msg"=>"该用户名已存在");
        }
        $insertU = \Yii::$app->db->createCommand()->insert('user',array('id'=>$id,'username'=>$username,'password'=>$passwordE,'role'=>$role,
            'status'=>$status))->execute();
        if($insertU)
        {
            return array("data"=>[$username,$id],"msg"=>"用户添加成功");
        }
        else
        {
            return array("data"=>[$username,$id],"msg"=>"用户添加失败");
        }
    }
    /*
     * 用户删除，删除分两种：暂时删除的和永久删除
     * 数据库中的数据很多时候不是直接删除，而是将其身份隐藏，是为了数据保持一定的有效期.即为暂时删除，
     * 标志：flag
     * 1:暂时删除
     * 2：永久删除
     */
    public function actionDeleteuser()
    {
        $request=\Yii::$app->request;
        $flag =$request->post('flag');
        $userid=$request->post("userid");
        if($flag==1)
        {
            //暂时删除
            $query = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['id'=>$userid])
                ->andWhere(['status'=>1])
                ->one();
            if($query)
            {
                $updateU = \Yii::$app->db->createCommand()->update('user', ['status' => 0], "id={$userid}")->execute();
                if($updateU)
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户已删除");
                }
                else
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户没有删除成功");
                }
            }
            else{
                return array("data"=>[],"msg"=>"没有找到该用户");
            }
        }
        else if($flag==2)
        {
            //永久删除
            $querys = (new Query())
                ->select('*')
                ->from('user')
                ->Where(['id'=>$userid])
                ->one();
            if($querys)
            {
                $updateUs = \Yii::$app->db->createCommand()->delete('user',['id'=>$userid])->execute();
                if($updateUs)
                {
                    return array("data"=>[$querys,$updateUs],"msg"=>"该用户已永久删除");
                }
                else
                {
                    return array("data"=>[$querys,$updateUs],"msg"=>"该用户没有永久删除成功");
                }
            }
            else{
                return array("data"=>[],"msg"=>"没有找到该用户");
            }
        }
        else{
            return array("data"=>[],"msg"=>"输入错误");
        }
    }

    /*
     * 修改用户信息
     * 修改某部分的内容：
     * 标志：flag
     * 1:用户名
     * 2：角色
     * 3：密码
     * 4：状态
     */
    public function actionChangeuser()
    {
        $request = \Yii::$app->request;
        $userid = $request->post('userid');
        $flag = $request->post('flag');
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->Where(['id'=>$userid])
            ->one();
        if($query)
        {
            if($flag==1)
            {
                //修改用户名
                $username = $request->post('username');
                $query1 = (new Query())
                    ->select('*')
                    ->from('user')
                    ->Where(['username'=>$username])
                    ->one();
                if($query1)
                {
                    return array("data"=>[$query,$query1],"msg"=>"已有该用户名用户存在");
                }
                else{
                    $updateU = \Yii::$app->db->createCommand()->update('user', ['username'=>$username], "id={$userid}")->execute();
                    if($updateU)
                    {
                        return array("data"=>[$query,$updateU],"msg"=>"该用户名修改成功");
                    }
                    else
                    {
                        return array("data"=>[$query,$updateU],"msg"=>"该用户名已经修改");
                    }
                }

            }
            else if($flag==2)
            {
                //修改角色
                $role = $request->post('role');
                $updateU = \Yii::$app->db->createCommand()->update('user', ['role'=>$role], "id={$userid}")->execute();
                if($updateU)
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户角色修改成功");
                }
                else
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户角色已经修改");
                }
            }
            else if($flag==3)
            {
                //修改密码
                $password =$request->post('password');
                $passwordD = \backend\module\home\controllers\IndexController::PasswordEncry($password);
                $updateU = \Yii::$app->db->createCommand()->update('user', ['password'=>$passwordD], "id={$userid}")->execute();
                if($updateU)
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户密码修改成功");
                }
                else
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户密码已经修改");
                }
            }
            else if($flag==4)
            {
                //修改状态
                //只有被删除的用户才能修改状态
                $updateU = \Yii::$app->db->createCommand()->update('user', ['status'=>1], "id={$userid}")->execute();
                if($updateU)
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户状态修改成功");
                }
                else
                {
                    return array("data"=>[$query,$updateU],"msg"=>"该用户状态已经修改");
                }
            }
            else{
                return array("data"=>[$flag,$userid],"msg"=>"输入错误");
            }
        }
        else{
            return array("data"=>[$flag,$userid],"msg"=>"没找到该用户");
        }
    }
    public function actionImportexcel()
    {
        $request = \Yii::$app->request;
        $data = $request->post('data');
        $data = json_decode($data,true);
        for($i=0;$i<count($data);$i++)
        {
            $name = isset($data[$i]['name'])?$data[$i]['name']:"";
            $password = isset($data[$i]['password'])?$data[$i]['password']:"";
            $passwordE = \backend\module\home\controllers\IndexController::PasswordEncry($password);
            $role = isset($data[$i]['role'])?$data[$i]['role']:"";
            $query = (new Query())
                ->select("*")
                ->from('user')
                ->where(['username'=>$name])
                ->andWhere(['role'=>$role])
                ->one();
            $query2 = (new Query())
                ->select("*")
                ->from('user')
                ->max('id');
            $id = $query2+1;
            if($query == null)
            {
                $insertU = \Yii::$app->db->createCommand()->insert('user',array('id'=>$id,'username'=>$name,
                    'password'=>$passwordE,'role'=>$role,'status'=>1))->execute();
            }
        }
        return array("data"=>$data,"msg"=>"导入成功");
    }
}