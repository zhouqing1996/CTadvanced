<?php

namespace backend\module\admin\controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii\db\Query;
use yii\web\Controller;
use TCPDF;

class PracticeController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>'sss','msg'=>'admin-practice');
    }
    /*
     * 用户信息
     */
    public function User($uid)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$uid])
            ->one();
    }
    /*
     * 获取练习列表
     */
    public function actionGetprac()
    {
        $query = (new Query())
            ->select('*')
            ->from('prac')
            ->orderBy(['createtime'=>SORT_DESC])
            ->all();
        for($i=0;$i<count($query);$i++)
        {
            $query[$i]['auth'] = $this->User($query[$i]['auth'])['username'];
        }
        return array('data'=>$query,'msg'=>'用户创建的练习题目');
    }
    /*
     * 搜索练习
     * 参数：搜索内容
     */
    public function actionSearchprac()
    {
        $request = \Yii::$app->request;
        $name = $request->post('name');
        $query = (new Query())
            ->select('*')
            ->from('prac')
            ->where(['or',
                ['like','name',$name],
                ['like','createtime',$name]])
            ->orderBy(['createtime'=>SORT_DESC])
            ->all();
        return array('data'=>$query,'msg'=>'搜索结果');
    }
    /*
     * 一份练习的详细内容
     */
    public function PracticeInfo($pid)
    {
        $query = (new Query())
            ->select('*')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->all();
        $list = [];
        $n=0;
        for($i=0;$i<count($query);$i++)
        {
            switch ($query[$i]['qtypeid'])
            {
                case 1:
                    $qc = (new Query())
                        ->select('*')
                        ->from('chooseq')
                        ->where(['cqid'=>$query[$i]['qid']])
                        ->one();
                    //第几题
                    $list[$n]['id'] =  $query[$i]['id'];
                    //题干
                    $list[$n]['item'] = $qc['cqitem'];
                    //选项
                    $list[$n]['cho'] = $qc['cqcho'];
                    //答案
                    $list[$n]['ans'] = $qc['cqans'];
                    //推荐
                    $list[$n]['rem'] =$qc['cqrem'];
                    //详情
                    $list[$n]['tail'] = $qc['cqtail'];
                    $list[$n]['type']=1;
                    $n++;
                    break;
                case 2:
                    $qc = (new Query())
                        ->select('*')
                        ->from('fillq')
                        ->where(['fqid'=>$query[$i]['qid']])
                        ->one();
                    //第几题
                    $list[$n]['id'] =  $query[$i]['id'];
                    //题干
                    $list[$n]['item'] = $qc['fqitem'];
                    //答案
                    $list[$n]['ans'] = $qc['fqans'];
                    //推荐
                    $list[$n]['rem'] =$qc['fqrem'];
                    //详情
                    $list[$n]['tail'] = $qc['fqtail'];
                    $list[$n]['type']=2;
                    $n++;
                    break;
                case 3:
                    $qc = (new Query())
                        ->select('*')
                        ->from('program')
                        ->where(['pqid'=>$query[$i]['qid']])
                        ->one();
                    //第几题
                    $list[$n]['id'] =  $query[$i]['id'];
                    //题干
                    $list[$n]['item'] = $qc['pqitem'];
                    //答案
                    $list[$n]['ans'] = $qc['pqans'];
                    //推荐
                    $list[$n]['rem'] =$qc['pqrem'];
                    //详情
                    $list[$n]['tail'] = $qc['pqtail'];
                    $list[$n]['type']=3;
                    $n++;
                    break;
                case 4:
                    $qc = (new Query())
                        ->select('*')
                        ->from('choosem')
                        ->where(['mqid'=>$query[$i]['qid']])
                        ->one();
                    //第几题
                    $list[$n]['id'] =  $query[$i]['id'];
                    //题干
                    $list[$n]['item'] = $qc['mqitem'];
                    //选项
                    $list[$n]['cho'] = $qc['mqcho'];
                    //答案
                    $list[$n]['ans'] = $qc['mqans'];
                    //推荐
                    $list[$n]['rem'] =$qc['mqrem'];
                    //详情
                    $list[$n]['tail'] = $qc['mqtail'];
                    $list[$n]['type']=4;
                    $n++;
                    break;
                case 5:
                    $qc = (new Query())
                        ->select('*')
                        ->from('judge')
                        ->where(['jqid'=>$query[$i]['qid']])
                        ->one();
                    //第几题
                    $list[$n]['id'] =  $query[$i]['id'];
                    //题干
                    $list[$n]['item'] = $qc['jqitem'];
                    //答案
                    $list[$n]['ans'] = $qc['jqans'];
                    //推荐
                    $list[$n]['rem'] =$qc['jqrem'];
                    //详情
                    $list[$n]['tail'] = $qc['jqtail'];
                    $list[$n]['type']=5;
                    $n++;
                    break;
            }
        }
        return array('data'=>$list,'msg'=>'练习详细信息');
    }
    /*
     * 下载练习内容
     * 管理员下载全部的练习内容
     * 思路：从表中查看目前存在的练习题目，再从练习中分别找出题目，进行打印
     */
    public function actionDownprac()
    {
        $query = (new Query)
            ->select('*')
            ->from('prac')
//            ->orderBy(['createtime'=>SORT_DESC])
            ->all();
        //写
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('zhouqing');
        $pdf->SetTitle('练习题库');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, PHP');
        // 设置字体
        $pdf->setFont('stsongstdlight', '', 14);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // 设置页眉和页脚信息
        $pdf->setPrintFooter(true);
        $pdf->setPrintHeader(true);
        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');
        // 设置间距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        $pdf->AddPage();

        //写
        for($j=0;$j<count($query);$j++)
        {
            // //问卷标题
            $pdf->Write(0, $query[$j]['id'].'.'.'《'.$query[$j]['name'].'》', '', 0, 'C', 1, 0, false, false, 0);
            $pdf->Write(0, $this->User($query[$j]['auth'])['username'].'  '.$query[$j]['createtime'], '', 0, 'R', 1, 0, false, false, 0);
            $pdf->Ln(10);
            //练习详细信息
            $pInfo = $this->PracticeInfo($query[$j]['id'])['data'];
//            foreach ($pInfo as $key=>$value)
//            {
//                $flag[]=$value['id'];
//            }
//            array_multisort($flag,SORT_ASC,$pInfo);
            for ($i=0; $i <count($pInfo) ; $i++) {
                $pdf->SetFont('stsongstdlight', '', 15);
                if($pInfo[$i]['type']==1)
                {
                    $pdf->SetFont('stsongstdlight', 'B', 15);
                    $item = '(选择题)第'.$pInfo[$i]['id'].'题:'.$pInfo[$i]['item'];
                    $pdf->Write(0, $item, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $pdf->SetFont('stsongstdlight', '', 15);
                    $cho = explode('---',$pInfo[$i]['cho']);
                    $pdf->Write(0, $cho[0], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[1], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[2], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[3], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);

                    $pdf->Write(0, '答案：'.$pInfo[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '详解：'.$pInfo[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '相关知识：'.$pInfo[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                }
                else if($pInfo[$i]['type']==2)
                {
                    $pdf->SetFont('stsongstdlight', 'B', 15);
                    $pdf->Write(0, '(填空题)第'.$pInfo[$i]['id'].'题:'.$pInfo[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $pdf->SetFont('stsongstdlight', '', 15);
                    $pdf->Write(0, '答案：'.$pInfo[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '详解：'.$pInfo[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '相关知识：'.$pInfo[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                }
                else if($pInfo[$i]['type']==3)
                {
                    $pdf->SetFont('stsongstdlight', 'B', 15);
                    $pdf->Write(0, '(程序题)第'.$pInfo[$i]['id'].'题:'.$pInfo[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $pdf->SetFont('stsongstdlight', '', 15);
                    $pdf->Write(0, '答案：'.$pInfo[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '详解：'.$pInfo[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '相关知识：'.$pInfo[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                }
                else if($pInfo[$i]['type']==4)
                {
                    $pdf->SetFont('stsongstdlight', 'B', 15);
                    $item = '(多选题)第'.$pInfo[$i]['id'].'题:'.$pInfo[$i]['item'];
                    $pdf->Write(0, $item, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $pdf->SetFont('stsongstdlight', '', 15);
                    $cho = explode('---',$pInfo[$i]['cho']);
                    $pdf->Write(0, $cho[0], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[1], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[2], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, $cho[3], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $ans = explode('---',$pInfo[$i]['ans']);
                    $anss='';
                    for($m=0;$m<count($ans);$m++)
                    {
                        if($anss=='')
                        {
                            $anss = $ans[$m];
                        }
                        else
                        {
                            $anss = $anss.'/'.$ans[$m];
                        }
                    }
                    $pdf->Write(0, '答案：'.$anss, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '详解：'.$pInfo[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '相关知识：'.$pInfo[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                }
                else if($pInfo[$i]['type']==5)
                {
                    $pdf->SetFont('stsongstdlight', 'B', 15);
                    $pdf->Write(0, '(判断题)第'.$pInfo[$i]['id'].'题:'.$pInfo[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                    $pdf->SetFont('stsongstdlight', '', 15);
                    if($pInfo[$i]['ans']==1)
                    {
                        $ans='正确';
                    }
                    else
                    {
                        $ans="错误";
                    }
                    $pdf->Ln(5);
                    $pdf->Write(0, '答案：'.$ans, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '详解：'.$pInfo[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Write(0, '相关知识：'.$pInfo[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                    $pdf->Ln(5);
                }
            }
        }
        $title = '练习题库';
        $path = \Yii::$app->basePath;
        $filePath = $path.'/files/download/';
        if(!is_dir($filePath))
        {
            mkdir(iconv('utf-8','GBK',$filePath),0777,true);
        }
        $path=$filePath.$title.'.pdf';
        ob_clean();
        $pdf->Output($path,'F');
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        return array('data'=>[$url],'msg'=>'试卷pdf下载');
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
     * 练习试题信息
     */
    public function Prac($pid)
    {
        return (new Query())
            ->select('*')
            ->from('prac')
            ->where(['id'=>$pid])
            ->one();
    }
    /*
     * 有效性
     */
    public function Status($s)
    {
        switch ($s)
        {
            case 1:
                return '有效';
            case 0:
                return '无效';
            default:
                return '未知';
        }
    }
    /*
     * 测试总分
     */
    public function Score($pid)
    {
        return (new Query())
            ->select('*')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->max('id');
    }
    /*
     * 下载作答信息
     * 管理员，可以下载全部的作答信息
     */
    public function actionDownpracinfo()
    {
        $query = (new Query())
            ->select('*')
            ->from('pracuser')
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['name'] = $this->Prac($query[$i]['pid'])['name'];
            $list[$i]['score'] = $this->Score($query[$i]['pid']);
            $list[$i]['auth'] = $this->User($query[$i]['uid'])['username'];
            $ctime = $query[$i]['ctime'];
            $list[$i]['grade'] = $query[$i]['grade'];
            $ctime = explode(':',$ctime);
            $ctime = $ctime[0]*60+$ctime[1]+$ctime[2]/60;
            $list[$i]['ctime'] =round($ctime,2).'分钟';
            $list[$i]['finitime'] = $query[$i]['fintime'];
            $list[$i]['status'] = $this->Status($query[$i]['status']);
        }
        $name ='全部';
        $name = str_replace('/','_',$name);
        $fileName = $name.'的作答信息';
        //表头
        $title = ['练习名称','总分','作答学生','得分','作答用时','完成时间','状态'];
        set_time_limit(0);
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        //设置表标题
        $worksheet->setTitle('练习作答信息');
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
        return array('data'=>$url,'msg'=>'练习作答信息');
    }
    /*
     * 删除
     * 参数：练习id
     * 标志：1：删除，2恢复，3彻底删除
     */
    public function actionDeleteprac()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $flag = $request->post('flag');
        switch ($flag)
        {
            case 1:
                $del = \Yii::$app->db->createCommand()->update('prac',['status'=>0],['id'=>$pid])->execute();
                return array('data'=>$del,'msg'=>'删除');
            case 2:
                $del = \Yii::$app->db->createCommand()->update('prac',['status'=>1],['id'=>$pid])->execute();
                return array('data'=>$del,'msg'=>'恢复');
            case 3:
                $del = \Yii::$app->db->createCommand()->delete('prac',['id'=>$pid])->execute();
                return array('data'=>$del,'msg'=>'彻底删除');
        }
    }
    /*
     * 判断正误
     */
    public function Grade($g)
    {
        switch ($g)
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
     * 题干信息
     */
    public function Item($type,$id)
    {
        switch ($type)
        {
            case 1:
                $q = (new Query())
                    ->select('*')
                    ->from('chooseq')
                    ->where(['cqid'=>$id])
                    ->one();
                return $q['cqitem'];
            case 2:
                $q = (new Query())
                    ->select('*')
                    ->from('fillq')
                    ->where(['fqid'=>$id])
                    ->one();
                return $q['fqitem'];
            case 3:
                $q = (new Query())
                    ->select('*')
                    ->from('program')
                    ->where(['pqid'=>$id])
                    ->one();
                return $q['pqitem'];
            case 4:
                $q = (new Query())
                    ->select('*')
                    ->from('choosem')
                    ->where(['mqid'=>$id])
                    ->one();
                return $q['mqitem'];
            case 5:
                $q = (new Query())
                    ->select('*')
                    ->from('judge')
                    ->where(['jqid'=>$id])
                    ->one();
                return $q['jqitem'];
            default:
                return '未知';
        }
    }
    /*
     * 下载练习信息
     * 参数：练习id,标记：1，总体信息，2，详细信息
     */
    public function actionDown()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $flag = $request->post('flag');
        switch ($flag)
        {
            case 1:
                $query = (new Query())
                    ->select('*')
                    ->from('pracuser')
                    ->where(['pid'=>$pid])
                    ->all();
                $list = [];
                for($i=0;$i<count($query);$i++)
                {
                    $info = $this->Prac($pid);
                    $list[$i]['name'] = $info['name'];
                    $list[$i]['auth'] =$this->User($info['auth'])['username'];
                    $list[$i]['score'] = $this->Score($pid);

//                    $list[$i]['createtime'] = $this->Prac($pid)['createtime'];
                    $list[$i]['stu'] =$this->User($query[$i]['uid'])['username'];
                    $list[$i]['grade'] = $query[$i]['grade'];
                    $list[$i]['ctime'] = $query[$i]['ctime'];
                    $list[$i]['fintime'] = $query[$i]['fintime'];
                    $list[$i]['status'] = $this->Status($query[$i]['status']);
                }
                $name ='练习总体信息';
                $name = str_replace('/','_',$name);
                $fileName = $name.'的作答信息';
                //表头
                $title = ['练习名称','练习作者','练习总分','作答学生','得分','作答用时','完成时间','状态'];
                set_time_limit(0);
                $spreadsheet = new Spreadsheet();
                $worksheet = $spreadsheet->getActiveSheet();
                //设置表标题
                $worksheet->setTitle('练习总体信息');
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
                return array('data'=>$url,'msg'=>'练习总体信息');
            case 2:
                $query = (new Query())
                    ->select('*')
                    ->from('pracusertail')
                    ->where(['pid'=>$pid])
                    ->all();
                $list = [];
                for($i=0;$i<count($query);$i++)
                {
                    $info = $this->Prac($pid);
                    $list[$i]['name'] = $info['name'];
                    $list[$i]['auth'] =$this->User($info['auth'])['username'];
                    $list[$i]['score'] = $this->Score($pid);
                    $list[$i]['stu'] =$this->User($query[$i]['uid'])['username'];
                    $list[$i]['item'] = $this->Item($query[$i]['qtypeid'],$query[$i]['qid']);
                    $list[$i]['ans'] = $query[$i]['ans'];
                    $list[$i]['grade'] = $this->Grade($query[$i]['grade']);
                    $list[$i]['ctime'] = $query[$i]['ctime'];
                    $list[$i]['fintime'] = $query[$i]['ftime'];
                    $list[$i]['status'] = $this->Status($query[$i]['status']);
                }
                $name ='练习详细信息';
                $name = str_replace('/','_',$name);
                $fileName = $name.'的作答信息';
                //表头
                $title = ['练习名称','练习作者','练习总分','作答学生','题干','答案','结果判断','作答用时','完成时间','状态'];
                set_time_limit(0);
                $spreadsheet = new Spreadsheet();
                $worksheet = $spreadsheet->getActiveSheet();
                //设置表标题
                $worksheet->setTitle('练习详细信息');
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
                return array('data'=>$url,'msg'=>'练习详细信息');
            default:
                return array('data'=>'','msg'=>'未知');

        }
    }
}