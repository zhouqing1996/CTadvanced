<?php

namespace backend\module\teacher\controllers;

use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use yii\web\Controller;
use yii\db\Query;
use yii\common\Student;
use yii\common\Teacher;
use function GuzzleHttp\Psr7\str;


/*
 * 教师对学生的相关管理
 */

class TeacherController extends Controller
{
    public function actionIndex()
    {
        return "教师管理";
    }
//    求平均数
    public function AvgNumber($list)
    {
        $sum =0;
        if($list==null)
        {
            return round(0,2);
        }
        for($i=0;$i<count($list);$i++)
        {
            $sum = $sum + $list[$i];
        }
        return round($sum/count($list),2);

    }
//    某次作答次数
    public function NUm($uid,$eid)
    {
        $query = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['userid' => $uid])
            ->andWhere(['exid'=>$eid])
            ->max('id');
        return $query;
    }
//    判断题目没有做或者做错的
//试卷详情，用户作答详情
    public function Useren($all,$user,$exid)
    {
        $userEN = [];
        $n=0;
        $userid = array_merge(array_unique(array_column($user,'userid')));
//        '12,13
//        return array('data'=>$userid,'msg'=>'用户作答错误或未做的题目');

        for ($l = 0; $l < count($userid); $l++) {
            $num = $this->NUm($userid[$l], $exid);
            //            选出同一个人在一份试卷的作答情况
            for ($x = 1; $x <= $num; $x++) {
                $query = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid' => $userid[$l]])
                    ->andWhere(['id' => $x])
                    ->andWhere(['exid' => $exid])
                    ->all();
                for ($j = 0; $j < count($all); $j++) {
                    $flag = true;
                    for ($m = 0; $m < count($query); $m++) {
                        $id = $query[$m]['id'];
                        $userid1 = $query[$m]['userid'];
                        $exid = $all[$j]['exid'];
                        $qid = $all[$j]['qid'];
                        $qtid = $all[$j]['qtypeid'];
                        $numa =$all[$j]['id'];
                        if ($query[$m]['qid'] === $all[$j]['qid'] && $query[$m]['qtypeid'] === $all[$j]['qtypeid']) {
                            $flag = false;
                            if ($query[$m]['grade'] == 0) {
                                $userEN[$n]['num']=$numa;
                                $userEN[$n]['id'] = $query[$m]['id'];
                                $userEN[$n]['userid'] = $query[$m]['userid'];
                                $userEN[$n]['exid'] = $query[$m]['exid'];
                                $userEN[$n]['qid'] = $query[$m]['qid'];
                                $userEN[$n]['qtypeid'] = $query[$m]['qtypeid'];
                                $userEN[$n]['ans'] = $query[$m]['ans'];
                                $userEN[$n]['grade'] = $query[$m]['grade'];
                                $userEN[$n]['ctime'] = $query[$m]['ctime'];
                                $userEN[$n]['finishtime'] = $query[$m]['finishtime'];
                                $n++;
                            }
                            break;
                        } else {
                            continue;
                        }
                    }
                    if ($flag) {
                        $userEN[$n]['num']=$numa;
                        $userEN[$n]['id'] = $id;
                        $userEN[$n]['userid'] = $userid1;
                        $userEN[$n]['exid'] = $exid;
                        $userEN[$n]['qid'] = $qid;
                        $userEN[$n]['qtypeid'] = $qtid;
                        $userEN[$n]['ans'] = '未作答';
                        $userEN[$n]['grade'] = '';
                        $userEN[$n]['ctime'] = '';
                        $userEN[$n]['finishtime'] = '';
                        $n++;
                    }
                }
            }
        }
        return array('data'=>$userEN,'msg'=>'用户作答错误或未做的题目');
    }
//    教师的某一试卷的数据分析
//参数：教师的auth,试卷eid
    public function actionTeacherfenxi()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $auth = $request->post('auth');
//        试卷的情况
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid' => $eid])
            ->one();
//        试卷的详细情况
        $querytail = (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid' => $eid])
            ->all();
//        用户作答的情况
        $userQuery = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['exid' => $eid])
            ->all();
        $userQuerytail = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['exid' => $eid])
            ->all();
        if($userQuery)
        {
            //        返回的整体信息列表
            $list = [];
//        平均分,平均用时；平均次数

            $grade = array_column($userQuery, 'grade');
            $time = array_column($userQuery, 'ctime');
//        分钟处理
            for ($i = 0; $i < count($time); $i++) {
                $arr = explode(':', $time[$i]);
                $time[$i] = round((int)$arr[0] * 60 + $arr[1] + $arr[2] / 60, 2);
            }
//        作答用户的id
            $list['userid'] = array_merge(array_unique(array_column($userQuery, 'userid')));
            $num = [];
            for($mm=0;$mm<count($list['userid']);$mm++)
            {
                $n = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid'=>$list['userid'][$mm]])
                    ->andWhere(['exid'=>$eid])
                    ->max('id');
                array_push($num,$n);
            }


            $list['avgGrade'] = $this->AvgNumber($grade);
            $list['avgTime'] = $this->AvgNumber($time);
            $list['avgNum'] = $this->AvgNumber($num);
//        作答用户数：用户id去重的计数
            $list['num'] = count(array_unique(array_column($userQuery, 'userid')));
//        试卷题目总数
            $list['exnum'] = count($querytail);
//        试卷的名字
            $list['exname'] = $query['exname'];
//        试卷的最高分
            $list['HeightScore'] = max(array_column($userQuery,'grade'));
//  试卷的最低分
            $list['LowScore'] = min(array_column($userQuery,'grade'));
//        用户作答次数的最大值
            $list['maxusernum'] = max($num);

//        作答用户的名字
            $list['username'] =[];
            for($i=0;$i<count($list['userid']);$i++)
            {
                $q = (new Query())
                    ->select('*')
                    ->from('user')
                    ->where(['id'=>$list['userid'][$i]])
                    ->one();
                $list['username'][$i]=$q['username'];
            }


//        return array('data'=>[$list,$userEN],'msg'=>'数据分析');
//        获取扇形图的数据：
//        用户作答中最大的作答次数的比列
            $userpie = [];
//        用户列表
//        $userList =array_merge($list['userid']);
            for($i=0;$i<count($list['userid']);$i++)
            {
                $userpie[$i]['id']=$list['userid'][$i];
                $userpie[$i]['num'] = (new Query())
                    ->select('*')
                    ->from('userans')
                    ->where(['userid'=>$list['userid'][$i]])
                    ->andWhere(['exid'=>$eid])
                    ->max('id');
            }
//        饼图需要的内容是：某个次数的人的数量
            $pie = array_column($userpie,'num');
            $pie = array_count_values($pie);
            $pieData = [];
            $i=0;
            foreach ($pie as $key=>$value)
            {
                $pieData[$i]['num'] = $key;
                $pieData[$i]['value']=$value;
                $i++;
            }
            //        用户做错或者没有做的题目
            $userEN= $this->Useren($querytail,$userQuerytail,$eid)['data'];
//        错题的频次列表
            $Err =array_column($userEN,'num');
//        return array('data'=>[$list,$userEN,$pieData,count($userEN)],'msg'=>'数据分析');
            $Err = array_count_values($Err);
            $ErrList = [];
            $j=0;
            foreach ($Err as $key=>$value)
            {
                $ErrList[$j]['Itemnum']=$key;
                $ErrList[$j]['num'] =$value;
                $j++;
            }
//        按照题目排序
            foreach ($ErrList as $key=>$value)
            {
                $flag[]=$value['Itemnum'];
            }
            array_multisort($flag,SORT_ASC,$ErrList);
        }
        else
        {
            //        返回的整体信息列表
            $list = [];
//        平均分,平均用时；平均次数

            $list['avgGrade'] = 0;
            $list['avgTime'] = 0;
            $list['avgNum'] = 0;
//        作答用户数：用户id去重的计数
            $list['num'] =0;
//        试卷题目总数
            $list['exnum'] = count($querytail);
//        试卷的名字
            $list['exname'] = $query['exname'];
//        试卷的最高分
            $list['HeightScore'] = 0;
//  试卷的最低分
            $list['LowScore'] = 0;
//        用户作答次数的最大值
            $list['maxusernum'] = 0;
//        作答用户的id
            $list['userid'] = '无';
//        作答用户的名字
            $list['username'] =[];
//        用户列表
//        饼图需要的内容是：某个次数的人的数量
            $pieData = [];
            //        用户做错或者没有做的题目
            $userEN= [];
            $ErrList = [];
        }
        return array('data'=>[$list,$userEN,$pieData,$ErrList],'msg'=>'数据分析');
    }
    /*
     * 查找用户名
     */
    public function GetUser($id)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->andWhere(['status'=>1])
            ->one();
    }
    /*
     * 教师的学生列表
     * 参数：教师id
     */
    public function actionStudentlist()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('student')
            ->where(['tid'=>$tid])
            ->andWhere(['status'=>1])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id']= $query[$i]['sid'];
            $student =  $this->GetUser($query[$i]['sid']);
            $list[$i]['name'] = $student['username'];
            $list[$i]['no'] = $student['no'];
        }
        return array('data'=>$list,'msg'=>'教师的学生用户');
    }
    /*
     * 退选某学生
     * 参数：教师、学生id
     */
    public function actionDelstudent()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $sid = $request->post('sid');
        $del = \Yii::$app->db->createCommand()->delete('student',['sid'=>$sid,'tid'=>$tid])->execute();
        if($del)
        {
            return array('data'=>$del,'msg'=>'success');
        }
        else
        {
            return array('data'=>$del,'msg'=>'退选失败');
        }
    }
    /*
     * 搜索学生
     * 参数：学生信息，姓名、学号
     * 教师id
     */
    public function actionSearchstudent()
    {
        $request = \Yii::$app->request;
        $name = $request->post('name');
        $tid = $request->post('tid');
        //学生信息查找
        $query_student = (new Query())
            ->select('*')
            ->from('user')
            ->where(['role'=>3])
            ->andWhere(['status'=>1])
            ->andWhere(['or',
                ['like','username',$name],
                ['like','no',$name]])
            ->all();
        if($query_student)
        {
            $list = [];
            $n=0;
            for($i=0;$i<count($query_student);$i++)
            {
                $query = (new Query())
                    ->select('*')
                    ->from('student')
                    ->where(['sid'=>$query_student[$i]['id']])
                    ->andWhere(['tid'=>$tid])
                    ->one();
                if($query)
                {
                    $list[$n]['id'] = $query_student[$i]['id'];
                    $list[$n]['name'] = $query_student[$i]['username'];
                    $list[$n]['no'] =$query_student[$i]['no'];
                    $n++;
                }
            }
            return array('data'=>$list,'msg'=>'搜索结果');
        }
        else
        {
            return array('data'=>$query_student,'msg'=>'没有找到这样的学生');
        }
    }
    /*
     * 可添加学生的列表
     * 参数：教师id,
     * 思路：将教师已有的学生的信息排除，剩余的学生就是可添加的学生
     */
    public function actionStudent()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        //学生表
        $query_s = (new Query())
            ->select('*')
            ->from('user')
            ->where(['role'=>3])
            ->andWhere(['status'=>1])
            ->all();
        $query_st = (new Query())
            ->select('*')
            ->from('student')
            ->where(['tid'=>$tid])
            ->andWhere(['status'=>1])
            ->all();
        $stu = array_merge(array_diff(array_column($query_s,'id'),array_column($query_st,'sid')));
        $list = [];
        for($i=0;$i<count($query_s);$i++)
        {
            for($j=0;$j<count($stu);$j++)
            {
                if($query_s[$i]['id'] == $stu[$j])
                {
                    $list[$j]['id'] =  $stu[$j];
                    $list[$j]['no'] = $query_s[$i]['no'];
                    $list[$j]['name'] =$query_s[$i]['username'];
                    $list[$j]['role'] = $query_s[$i]['role'];
                }
            }
        }
        return array('data'=>$list,'msg'=>'可添加的学生列表');
    }

    /*
     * 添加学生
     * 参数：学生列表 教师id
     */
    public function actionAddstudent()
    {
        $request = \Yii::$app->request;
        $stuList = $request->post('list');
        $tid =$request->post('tid');
        for($i=0;$i<count($stuList);$i++)
        {
            //插入其中
            $add = \Yii::$app->db->createCommand()->insert('student',
                array('sid'=>$stuList[$i],'tid'=>$tid,'status'=>1))->execute();
        }
        return array('data'=>[],'msg'=>'添加学生成功');
    }

    /*
         * * 分割文件
    　　 * 默认大小 2M=10485760/5
    　　 */
    public function file_split($file,$block_size=10485760/5)
    {
        clearstatcache();
        $block_info=array();
        $size=filesize($file);
        $i=0;
        while($size>0){
            $block_info[]=array(
                'size'=>($size>=$block_size?$block_size:$size),
                'file'=>str_replace('.txt', '',$file).'.'.($i++).'.txt');
            $size-=$block_size;
        }
        $fp   = fopen($file,"rb");
        foreach ($block_info as $bi) {
            $handle = fopen($bi['file'],"wb");
            fwrite($handle,fread($fp,$bi['size']));
            fclose($handle);
            unset($handle);
        }
        fclose ($fp);
        unset($fp);
    }
    /*
　　 * 合并文件
　　 * 如果合并后的文件为 CPCUxcp111.flv.0.esplit
　　 * 则 file=CPCUxcp111.flv，不包含.x.esplit后缀
　　 * save_file为另存为的文件名
　　 */
    public function file_combine($file,$save_file=''){
        $filename=basename($file);
        $filepath=dirname($file).'/';
        $block_info=array();
        for($i=0;;$i++){
            if(file_exists($file.'.'.$i.'.txt') && filesize($file.'.'.$i.'.txt')>0){
                $block_info[]=$file.'.'.$i.'.txt';
            }else{
                break;
            }
        }
        if($save_file){
            $fp   = fopen($save_file,"wb");
        }else{
            $fp   = fopen($file,"wb");
        }
        foreach ($block_info as $block_file) {
            $handle = fopen($block_file,"rb");
            fwrite($fp,fread($handle,filesize($block_file)));
            fclose($handle);
            unset($handle);
        }
        fclose ($fp);
        unset($fp);
    }
//    // 文件上传
    public function actionUploadfile()
    {
        $path = \Yii::$app->basePath;
        $file=$_FILES["file"];
        $fileName = $_POST['filename'];
        //切分文件放置位置
        $file_split = $path.'/files/uploads/split/';
        if(!is_dir($file_split))
        {
            mkdir(iconv('utf-8','GBK',$file_split),0777,true);
        }
        move_uploaded_file($_FILES["file"]["tmp_name"],$file_split.$fileName);
        $fileName = explode('.',$fileName);
        $fileName = $fileName[0].'.'.$fileName[1];
        $split_path = $file_split.$fileName;
        return array('data'=>[$file,$fileName,$split_path],'msg'=>'sss');
    }
    /*
     * 删除文件夹
     */
    public function delDir($directory){
        if(file_exists($directory)){
            //判断目录是否存在，如果不存在rmdir()函数会出错
            if($dir_handle=@opendir($directory)){
                //打开目录返回目录资源，并判断是否成功
                while($filename=readdir($dir_handle)){
                    //遍历目录，读出目录中的文件或文件夹
                    if($filename!='.' && $filename!='..'){
                        //一定要排除两个特殊的目录
                        $subFile=$directory."/".$filename;
                        //将目录下的文件与当前目录相连
                        if(is_dir($subFile)){
                            //如果是目录条件则成了
                            delDir($subFile);
                            //递归调用自己删除子目录
                        }
                        if(is_file($subFile)){
                            //如果是文件条件则成立
                            unlink($subFile);
                            //直接删除这个文件
                        }
                    }
                }
                closedir($dir_handle);//关闭目录资源
                rmdir($directory);//删除空目录
            }
        }
    }
    public function actionMerge()
    {
        $request = \Yii::$app->request;
        $fileName = $request->post('filename');
//        return array('data'=>$fileName,'msg'=>'ssss');
        $path = \Yii::$app->basePath;
        $path = $path.'/files/uploads/';
        $split_path = $path.'split/';
//        return array('data'=>$split_path,'msg'=>'sss');
        $split_path2 = str_replace('\\','/',$split_path);
        chmod($split_path2,0777);
        $split_path1 = $split_path.$fileName;
        $ctime = date("YmdHis");
        $fileName1 =explode('.',$fileName);
//        return array('data'=>$fileName,'msg'=>'ssss');
        $merge_path = $path.$fileName1[0].$ctime.'.'.$fileName1[1];
        //合并文件
        $split_path1 = str_replace('\\','/',$split_path1);
        $merge_path = str_replace('\\','/',$merge_path);
//        return array('data'=>$split_path,'msg'=>'ssss');
        $this->file_combine($split_path1,$merge_path);
        //删除临时文件
        $this->delDir($split_path);
        $rPath = explode('ComputeThinking',$merge_path);
        $rPath='http://127.0.0.1/ComputeThinking'.$rPath[1];
        return array('data'=>[$fileName,$rPath],'msg'=>'ssss');
    }
    public function actionSp()
    {
        //文件分割
        $this->file_split('F:/app.mp4');
        $this->file_combine('F:/app.mp4','F:/ss.mp4');
        return array('data'=>[],'msg'=>'分割视频');
    }
    // 文件上传
//    public function actionUploadfile()
//    {
//        set_time_limit(0);
//        $path = \Yii::$app->basePath;
//        $filePath = $path.'/files/uploads/';
//        if(!is_dir($filePath))
//        {
//            mkdir(iconv('utf-8','GBK',$filePath),0777,true);
//        }
////        $this->file_split($_FILES["file"]);
////        return array('data'=>$_FILES["file"]['tmp_name'],'msg'=>'sss');
//        $filename=$_FILES["file"]["name"];
//        $fileName1 =explode('\\',$filename);
//        $fileName1 = $fileName1[count($fileName1)-1];
//        $fileName1 =explode('.',$fileName1);
//        $fileName1 =$fileName1[0];
//        $fileArr = explode('.',$filename);
//        $tempName=$fileName1.date("YmdHis").".".$fileArr[1];
//        move_uploaded_file($_FILES["file"]["tmp_name"], $filePath.$tempName);
//        $file = array('filename' => 'filename','dir'=>'filedir' );
//        $file['filename'] = $filename;
//        $url=explode('ComputeThinking',$filePath.$tempName);
//        $url='http://127.0.0.1/ComputeThinking'.$url[1];
//        $file['dir'] = $url;
//        return array("data"=>[$tempName,$file['filename'],$file['dir'],$_FILES['file']['tmp_name']],"msg"=>"上传成功");
//    }
    /*
     * 资源列表id
     */
    public function GetRecourseID()
    {
        return (new Query())
            ->select('*')
            ->from('teacher')
            ->max('id')+1;
    }
    /*
     * 上传资源
     * 参数：文件名、文件路径(列表)，用户id(教师)
     */
    public function actionRecourse()
    {
        $request = \Yii::$app->request;
//        $fileName = $request->post('fileName');
//        $fileDir = $request->post('fileDir');
        $fileList = $request->post('rList');
        $tid = $request->post('tid');
        $ctime = date('Y-m-d H:i:s',time());
        for($i=0;$i<count($fileList);$i++)
        {
            //插入数据
            $add = \Yii::$app->db->createCommand()->insert('teacher',
            array('id'=>$this->GetRecourseID(),'tid'=>$tid,'name'=>$fileList[$i]['fileName'],'dir'=>$fileList[$i]['fileDir'],'ctime'=>$ctime,'status'=>1))->execute();
        }
        return array('data'=>[],'msg'=>'上传资源成功');
    }
    /*
     * 资源列表
     * 参数：用户id(教师)
     */
    public function actionRecourselist()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('teacher')
            ->where(['tid'=>$tid])
            ->andWhere(['status'=>1])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id'] = $query[$i]['id'];
            $list[$i]['tid'] = $query[$i]['tid'];
            $list[$i]['dir'] = $query[$i]['dir'];
            $list[$i]['name'] = $query[$i]['name'];
            $type = explode('.',$list[$i]['name']);
            $list[$i]['type'] = $type[1];
            $ctime = explode(' ',$query[$i]['ctime']);
            $list[$i]['ctime'] = $ctime[0];
            $list[$i]['status'] = $query[$i]['status'];
        }
        return array('data'=>$list,'msg'=>'资源列表');
    }
    /*
     * 搜索资源
     * 参数：name,tid
     */
    public function actionSearchrecourse()
    {
        $request = \Yii::$app->request;
        $name = $request->post('name');
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('teacher')
            ->where(['tid'=>$tid])
            ->andWhere(['status'=>1])
            ->andWhere(['or',
                ['like','name',$name],
                ['like','dir',$name],
                ['like','ctime',$name]])
            ->all();
        return array('data'=>$query,'msg'=>'搜索资源结果');
    }
}