<?php

namespace backend\module\home\controllers;

use yii\db\Query;
use yii\web\Controller;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;



class ExportController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>'导出','msg'=>'数据excel导出');
    }
    /*
     * 解密
     */
    public function PasswordDecry($password,$encryptedData="zhouqing")
    {
        $de = \Yii::$app->getSecurity()->decryptByPassword(base64_decode($password),$encryptedData);
        return $de;
    }
    /*
     * 角色文字转化
     */
    public function RoleName($role)
    {
        switch ($role)
        {
            case 1:
                return '管理员';
            case 2:
                return '教师';
            case 3:
                return '学生';
            default:
                return '未知';
        }
    }
    /*
     * 状态转换
     */
    public function StatusName($status)
    {
        switch ($status)
        {
            case 1:
                return '有效';
            case 0:
                return '无效';
            default:
                return '未知';
        }
    }
    /**
     * 输出到浏览器(需要设置header头)
     * @param string $fileName 文件名
     * @param string $fileType 文件类型
     */
    function excelBrowserExport($fileName, $fileType) {

        //文件名称校验
        if(!$fileName) {
            trigger_error('文件名不能为空', E_USER_ERROR);
        }

        //Excel文件类型校验
        $type = ['Excel2007', 'Xlsx', 'Excel5', 'xls'];
        if(!in_array($fileType, $type)) {
            trigger_error('未知文件类型', E_USER_ERROR);
        }

        if($fileType == 'Excel2007' || $fileType == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
            header('Cache-Control: max-age=0');
        } else { //Excel5
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
            header('Cache-Control: max-age=0');
        }
    }
    /*
     * 导出用户表
     * 密码解密，角色文字显示
     * 参数：文件名
     */
    public function actionExportuser()
    {
        $query = (new Query())
            ->select('*')
            ->from('user')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id']= $query[$i]['id'];
            $list[$i]['no']= $query[$i]['no'];
            $list[$i]['username']= $query[$i]['username'];
            $list[$i]['password']= $this->PasswordDecry($query[$i]['password']);
            $list[$i]['role']= $this->RoleName($query[$i]['role']);
            $list[$i]['status']= $this->StatusName($query[$i]['status']);
        }
        $fileName = '用户表';
        //表头
        $title = ['序号','工号','用户名','密码','角色','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('用户表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'用户表导出');
    }
    /*
     * 用户姓名，根据id
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
     * 题型输出
     */
    public function Bank($id,$type)
    {
        switch ($type)
        {
            case 1:
                $query = (new Query())
                    ->select('*')
                    ->from('chooseq')
                    ->where(['cqid'=>$id])
                    ->one();
                $list['id'] = $query['cqid'];
                $list['item'] = $query['cqitem'];
                return array('data'=>['单选题',$list],'msg'=>'单选题信息');
            case 2:
                $query = (new Query())
                    ->select('*')
                    ->from('fillq')
                    ->where(['fqid'=>$id])
                    ->one();
                $list['id'] = $query['fqid'];
                $list['item'] = $query['fqitem'];
                return array('data'=>['填空题',$list],'msg'=>'填空题信息');
            case 3:
                $query = (new Query())
                    ->select('*')
                    ->from('program')
                    ->where(['pqid'=>$id])
                    ->one();
                $list['id'] = $query['pqid'];
                $list['item'] = $query['pqitem'];
                return array('data'=>['程序题',$list],'msg'=>'程序题信息');
            case 4:
                $query = (new Query())
                    ->select('*')
                    ->from('choosem')
                    ->where(['mqid'=>$id])
                    ->one();
                $list['id'] = $query['mqid'];
                $list['item'] = $query['mqitem'];
                return array('data'=>['多选题',$list],'msg'=>'多选题信息');
            case 5:
                $query = (new Query())
                    ->select('*')
                    ->from('judge')
                    ->where(['jqid'=>$id])
                    ->one();
                $list['id'] = $query['jqid'];
                $list['item'] = $query['jqitem'];
                return array('data'=>['判断题',$list],'msg'=>'判断题信息');
            default:
                $list['id'] = '';
                $list['item'] = '';
                return array('data'=>['未知',$list],'msg'=>'未知');
        }
    }
    /*
     * 回答正误判断
     */
    public function Flag($flag)
    {
        switch ($flag)
        {
            case 0:
                return '错误';
            case 1:
                return '正确';
            default:
                return '未知';
        }
    }
    /*
     * 判断题答案
     */
    public function Judge($judge)
    {
        switch ($judge)
        {
            case 1:
                return '正确';
            case 0:
                return '错误';
            default:
                return '未知';
        }
    }
    /*
     * 试卷名称
     */
    public function Exam($id)
    {
        return (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$id])
            ->one();
    }
    /*
     * 书籍信息
     */
    public function Book($id)
    {
        return (new Query())
            ->select('*')
            ->from('book')
            ->where(['bookid'=>$id])
            ->one();
    }
    /*
     * 导出选择题
     * 将选项分割
     */
    public function actionExportchoose()
    {
        $query = (new Query())
            ->select('*')
            ->from('chooseq')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['cqid'] = $query[$i]['cqid'];
            $list[$i]['cqitem'] = $query[$i]['cqitem'];
            $cho = explode('---',$query[$i]['cqcho']);
            $list[$i]['cho1'] = $cho[0];
            $list[$i]['cho2'] = $cho[1];
            $list[$i]['cho3'] = $cho[2];
            $list[$i]['cho4'] = $cho[3];
            $list[$i]['cqans'] = $query[$i]['cqans'];
            $list[$i]['cqtail'] = $query[$i]['cqtail'];
            $list[$i]['cqrem']=$query[$i]['cqrem'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['cqstatus'] = $this->StatusName($query[$i]['cqstatus']);
        }
        $fileName = '单选题表';
        //表头
        $title = ['序号','题干','选项一','选项二','选项三','选项四','答案','详解','推荐','题目作者','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('单选题表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'单选择题导出');
    }
    /*
     * 填空题导出
     */
    public function actionExportfill()
    {
        $query = (new Query())
            ->select('*')
            ->from('fillq')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['fqid'] = $query[$i]['fqid'];
            $list[$i]['fqitem'] = $query[$i]['fqitem'];
            $list[$i]['fqans'] = $query[$i]['fqans'];
            $list[$i]['fqtail'] = $query[$i]['fqtail'];
            $list[$i]['fqrem']=$query[$i]['fqrem'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['fqstatus'] = $this->StatusName($query[$i]['fqstatus']);
        }
        $fileName = '填空表';
        //表头
        $title = ['序号','题干','答案','详解','推荐','题目作者','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('单选题表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'填空题导出');
    }
    /*
     * 程序题导出
     */
    public function actionExportprogram()
    {
        $query = (new Query())
            ->select('*')
            ->from('program')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['pqid'] = $query[$i]['pqid'];
            $list[$i]['pqitem'] = $query[$i]['pqitem'];
            $list[$i]['pqans'] = $query[$i]['pqans'];
            $list[$i]['pqtail'] = $query[$i]['pqtail'];
            $list[$i]['pqrem']=$query[$i]['pqrem'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['pqstatus'] = $this->StatusName($query[$i]['pqstatus']);
        }
        $fileName = '程序题表';
        //表头
        $title = ['序号','题干','答案','详解','推荐','题目作者','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('填空题表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'填空题导出');
    }
    /*
     * 多选题导出
     */
    public function actionExportchoosem()
    {
        $query = (new Query())
            ->select('*')
            ->from('choosem')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['mqid'] = $query[$i]['mqid'];
            $list[$i]['mqitem'] = $query[$i]['mqitem'];
            $cho = explode('---',$query[$i]['mqcho']);
            $list[$i]['cho1'] = $cho[0];
            $list[$i]['cho2'] = $cho[1];
            $list[$i]['cho3'] = $cho[2];
            $list[$i]['cho4'] = $cho[3];
            $ans = explode('---',$query[$i]['mqans']);
            $list[$i]['mqans'] = implode('、',$ans);
            $list[$i]['mqtail'] = $query[$i]['mqtail'];
            $list[$i]['mqrem']=$query[$i]['mqrem'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['mqstatus'] = $this->StatusName($query[$i]['mqstatus']);
        }
        $fileName = '多选题';
        //表头
        $title = ['序号','题干','选项一','选项二','选项三','选项四','答案','详解','推荐','题目作者','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('单选题表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'多选题导出');
    }

    /*
     * 判断题导出
     */
    public function actionExportjudge()
    {
        $query = (new Query())
            ->select('*')
            ->from('judge')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['jqid'] = $query[$i]['jqid'];
            $list[$i]['jqitem'] = $query[$i]['jqitem'];
            $list[$i]['jqans'] = $this->Judge($query[$i]['jqans']);
            $list[$i]['jqtail'] = $query[$i]['jqtail'];
            $list[$i]['jqrem']=$query[$i]['jqrem'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['jqstatus'] = $this->StatusName($query[$i]['jqstatus']);
        }
        $fileName = '判断题表';
        //表头
        $title = ['序号','题干','答案','详解','推荐','题目作者','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('判断题表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'判断题表');
    }
    /*
     * 图书导出
     */
    public function actionExportbook()
    {
        $query = (new Query())
            ->select('*')
            ->from('book')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['bookid'] = $query[$i]['bookid'];
            $list[$i]['publish'] = $query[$i]['publish'];
            $list[$i]['bookname'] = $query[$i]['bookname'];
            $list[$i]['author'] =$query[$i]['author'];
            $list[$i]['about'] = $query[$i]['about'];
            $list[$i]['auth'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['status'] = $this->StatusName($query[$i]['status']);
        }
        $fileName = '书籍表';
        //表头
        $title = ['序号','出版社','书名','作者','关于','录入者','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('书籍表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'书籍表');
    }
    /*
     * 导出试卷
     */
    public function actionExaminfo()
    {
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['exid'] = $query[$i]['exid'];
            $list[$i]['exname'] = $query[$i]['exname'];
            $list[$i]['createtime'] = $query[$i]['createtime'];
            $list[$i]['auth'] = $this->User($query[$i]['auth'])['username'];
            $list[$i]['gdtime'] = $query[$i]['gdtime'].'分钟';
            $list[$i]['exstatus'] = $this->StatusName($query[$i]['exstatus']);
        }
        $fileName = '试卷信息';
        //表头
        $title = ['试卷序号','试卷名称','创建时间','创建作者','规定时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('试卷信息');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'试卷信息');
    }

    /*
     * 导出某试卷中的作答信息
     * 参数：试卷编号
     */
    public function actionExamone()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $query = (new Query())
            ->select('*')
            ->from('useranss')
            ->where(['exid'=>$eid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id'] = '第'.$query[$i]['id'].'次';
            $list[$i]['userid'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['exname'] = $this->Exam($query[$i]['exid'])['exname'];
            $list[$i]['grade'] = $query[$i]['grade'];
            $ctime = $query[$i]['ctime'];
            $ctime = explode(':',$ctime);
            $ctime = $ctime[0]*60+$ctime[1]+$ctime[2]/60;
            $list[$i]['ctime'] =round($ctime,2).'分钟';
//            $list[$i]['ctime'] = $query[$i]['ctime'];
            $list[$i]['finishtime'] = $query[$i]['finishtime'];
            $list[$i]['status'] = $this->StatusName($query[$i]['status']);
        }
        $exname = $this->Exam($eid)['exname'];
        $exname = str_replace('/','_',$exname);
        $fileName = $exname.'的作答信息';
        //表头
        $title = ['作答次数','作答学生','试卷名称','得分','作答用时','完成时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('作答信息');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>'作答信息');
    }

    /*
     * 导出某一个人的详细作答信息
     * 参数：试卷id,用户id
     */
    public function actionExportuserinfo()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $uid = $request->post('uid');
        $query = (new Query())
            ->select('*')
            ->from('userans')
            ->where(['exid'=>$eid])
            ->andWhere(['userid'=>$uid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id'] = '第'.$query[$i]['id'].'次';
            $list[$i]['userid'] = $this->User($query[$i]['userid'])['username'];
            $list[$i]['exname'] = $this->Exam($query[$i]['exid'])['exname'];
            $data = $this->Bank($query[$i]['qid'],$query[$i]['qtypeid'])['data'];
            $list[$i]['qtype'] = $data[0];
            $list[$i]['qitem'] = $data[1]['item'];
            if($query[$i]['qtypeid']==5)
            {
                $list[$i]['ans'] = $this->Judge($query[$i]['ans']);
            }
            else
            {
                $list[$i]['ans'] = $query[$i]['ans'];
            }
            $list[$i]['grade'] = $this->Flag($query[$i]['grade']);
            $ctime = $query[$i]['ctime'];
            $ctime = explode(':',$ctime);
            $ctime = $ctime[0]*60+$ctime[1]+$ctime[2]/60;
            $list[$i]['ctime'] =round($ctime,2).'分钟';
//            $list[$i]['ctime'] = $query[$i]['ctime'];
            $list[$i]['finishtime'] = $query[$i]['finishtime'];
            $list[$i]['status'] = $this->StatusName($query[$i]['status']);
        }
        $username = $this->User($uid)['username'];
        $exname = $this->Exam($eid)['exname'];
        $exname = str_replace('/','_',$exname);
        $fileName = $username.'详细作答'.$exname.'数据';
        //表头
        $title = ['作答次数','姓名','试卷名称','题目题干','题目类型','作答答案','判题正误','作答时间','完成时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('详细作答情况');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
    /*
     * 教师导出学生列表
     * 参数：教师id
     */
    public function actionTeacherstu()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('student')
            ->where(['tid'=>$tid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['num'] = $i+1;
            $stu = $this->User($query[$i]['sid']);
            $list[$i]['no'] = $stu['no'];
            $list[$i]['username'] = $stu['username'];
            $list[$i]['status'] = $this->StatusName($stu['status']);
        }
        $tname = $this->User($tid)['username'];
        $fileName = $tname.'的学生列表';
        //表头
        $title = ['序号','学生学号','学生姓名','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('学生列表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
    /*
     * 教师资源列表导出
     * 参数：教师id
     */
    public function actionTeacherreco()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('teacher')
            ->where(['tid'=>$tid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['num'] = $i+1;
            $list[$i]['dir'] = $query[$i]['dir'];
            $list[$i]['name'] = $query[$i]['name'];
            $list[$i]['status'] = $this->StatusName($query[$i]['status']);
        }
        $tname = $this->User($tid)['username'];
        $fileName = $tname.'的资源列表';
        //表头
        $title = ['序号','资源路径','资源名称','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('学生列表');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
    /*
     * 教师导出自己创建的试卷
     * 参数:教师id
     */
    public function actionTeacherexaminfo()
    {
        $request = \Yii::$app->request;
        $tid = $request->post('tid');
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['auth'=>$tid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['exid'] = $query[$i]['exid'];
            $list[$i]['exname'] = $query[$i]['exname'];
            $list[$i]['createtime'] = $query[$i]['createtime'];
            $list[$i]['auth'] = $this->User($query[$i]['auth'])['username'];
            $list[$i]['gdtime'] = $query[$i]['gdtime'].'分钟';
            $list[$i]['exstatus'] = $this->StatusName($query[$i]['exstatus']);
        }
        $tname = $this->User($tid)['username'];
        $fileName = $tname.'的试卷信息';
        //表头
        $title = ['试卷序号','试卷名称','创建时间','创建作者','规定时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('试卷信息');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
    /*
     * 导出学生的练习情况（管理员）
     */
    public function actionStudentpra()
    {
        $query = (new Query())
            ->select('*')
            ->from('pratice')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id'] = '第'.$query[$i]['id'].'次';
            $stu = $this->User($query[$i]['userid']);
            $list[$i]['no'] = $stu['no'];
            $list[$i]['username'] = $stu['username'];
            $ex =$this->Bank($query[$i]['qid'],$query[$i]['qtypeid'])['data'];
            $list[$i]['type'] = $ex[0];
            $list[$i]['item'] = $ex[1]['item'];
            if($query[$i]['qtypeid']==5)
            {
                $list[$i]['ans'] = $this->Judge($query[$i]['ans']);
            }
            else
            {
                $list[$i]['ans'] = $query[$i]['ans'];
            }
            $list[$i]['grade'] =$this->Flag($query[$i]['grade']);
            $ctime = $query[$i]['ctime'];
            $ctime = explode(':',$ctime);
            $ctime = $ctime[0]*60+$ctime[1]+$ctime[2]/60;
            $list[$i]['ctime'] =round($ctime,2).'分钟';
            $list[$i]['finishtime'] = $query[$i]['finishtime'];
            $list[$i]['status'] = $this->StatusName($query[$i]['status']);
        }
        $fileName = '学生练习数据';
        //表头
        $title = ['作答次数','学生学号','学生姓名','题目题干','题目类型','作答答案','判题正误','作答时间','完成时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('学生练习数据');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
    /*
     * 导出一本书的信息
     * 参数：书籍id
     */
    public function actionBookitem()
    {
        $request = \Yii::$app->request;
        $bid = $request->post('bid');
        $query = (new Query())
            ->select('*')
            ->from('bookitem')
            ->where(['bookid'=>$bid])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['id'] = $query[$i]['id'];
            $list[$i]['bookitem'] = $query[$i]['bookitem'];
            $list[$i]['bookrem'] = $query[$i]['bookrem'];
            $list[$i]['err'] = $query[$i]['err'];
            $list[$i]['status'] = $this->StatusName($query[$i]['bstatus']);
        }
        $bookname = $this->Book($bid)['bookname'];
        $fileName = $bookname.'详细信息';
        //表头
        $title = ['序号','章节','知识点','权重','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('书籍详细信息');
        //表头
        foreach ($title as $key =>$value) {
            $worksheet->setCellValueByColumnAndRow($key+1,1,$value);
        }
        //从第二行开始插入数据
        $row =2;
        foreach ($list as $item)
        {
            $column = 1;
            foreach ($item as $value)
            {
                $worksheet->setCellValueByColumnAndRow($column,$row,$value);
                $column++;
            }
            $row++;
        }
        ob_clean();
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet,'Xlsx');
        $this->excelBrowserExport($fileName,'Xlsx');
        $path = \Yii::$app->basePath;
        $xlsxPath = $path.'/files/xlsx/';
        if(!is_dir($xlsxPath))
        {
            mkdir(iconv('utf-8','GBK',$xlsxPath),0777,true);
        }
        $path=$xlsxPath.$fileName.'.xlsx';
        ob_clean();
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        return array('data'=>$url,'msg'=>$fileName);
    }
}
