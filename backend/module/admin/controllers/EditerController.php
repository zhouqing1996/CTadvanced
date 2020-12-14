<?php

namespace backend\module\admin\controllers;

use yii\web\Controller;
header("Access-Control-Allow-Origin: *");
file_get_contents("php://input");
class EditerController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>[],'msg'=>'编辑器内容');
    }
    /*
     * 图片上传，富文本编辑器
     */
    public function actionUploadimage()
    {
        $path = \Yii::$app->basePath;
        //文件放置位置
        $file_path = $path.'/files/images/';
        if(!is_dir($file_path))
        {
            mkdir(iconv('utf-8','GBK',$file_path),0777,true);
        }
        $filename=$_FILES['file']['name'];
        $fileName1 =explode('\\',$filename);
        $fileName1 = $fileName1[count($fileName1)-1];
        $fileName1 =explode('.',$fileName1);
        $fileName1 =$fileName1[0];
        $fileArr = explode('/',$_FILES['file']['type']);
        $tempName=$fileName1.date("YmdHis").".".$fileArr[1];
        move_uploaded_file($_FILES["file"]["tmp_name"], $file_path.$tempName);
        $url=explode('ComputeThinking',$file_path.$tempName);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        return array('data'=>[$url],'msg'=>'图片上传');
    }
}
