<?php

namespace backend\module\teacher\controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use yii\db\Query;
use yii\web\Controller;
use TCPDF;

class PracticeController extends Controller
{
    public function actionIndex()
    {
        return array('data'=>[],'msg'=>'教师练习管理');
    }
    /*
     * 练习列表
     * 参数：用户id
     */
    public function actionPractice()
    {
        $request = \Yii::$app->request;
        $uid = $request->post('uid');
        $query = (new Query())
            ->select('*')
            ->from('prac')
            ->where(['auth'=>$uid])
            ->orderBy(['createtime'=>SORT_DESC])
            ->all();
        return array('data'=>$query,'msg'=>'用户创建的练习题目');
    }
    /*
     * 计算id
     */
    public function NumItem($pid)
    {
        return (new Query())
            ->select('*')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->max('id')+1;
    }
    /*
     * 添加练习：
     *     k     1：(预览)：手工选择各自题库中的题目组成一套试卷；
     *           2:加入试卷中
     */
    public function actionAddpractice()
    {
        set_time_limit(0);
        $request = \Yii::$app->request;
        $id = (new Query())
            ->select("*")
            ->from("prac")
            ->max('id')+1;
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
//                添加至问卷中
            $chooseList = $request->post('chooseList');
            for($i=0;$i<count($chooseList);$i++)
            {
                $c = (new Query())
                    ->select("*")
                    ->from('chooseq')
                    ->where(['cqid'=>(int)$chooseList[$i]])
                    ->one();
                $xc = $this->NumItem($id);
                $ins = \Yii::$app->db->createCommand()->insert('practail',
                    array('id'=>$xc,'pid'=>$id,'qid'=>$c['cqid'],'qtypeid'=>1,'status'=>1))->execute();
            }
            $fillList= $request->post('fillList');
            for($i=0;$i<count($fillList);$i++)
            {
                $c = (new Query())
                    ->select("*")
                    ->from('fillq')
                    ->where(['fqid'=>(int)$fillList[$i]])
                    ->one();
                $xf = $this->NumItem($id);
                $ins = \Yii::$app->db->createCommand()->insert('practail',
                    array('id'=>$xf,'pid'=>$id,'qid'=>$c['fqid'],'qtypeid'=>2,'status'=>1))->execute();
            }
            $judgeList= $request->post('judgeList');
            for($i=0;$i<count($judgeList);$i++)
            {
                $c = (new Query())
                    ->select("*")
                    ->from('judge')
                    ->where(['jqid'=>(int)$judgeList[$i]])
                    ->one();
                $xj = $this->NumItem($id);
                $ins = \Yii::$app->db->createCommand()->insert('practail',
                    array('id'=>$xj,'pid'=>$id,'qid'=>$c['jqid'],'qtypeid'=>5,'status'=>1))->execute();
            }
            $choosemList= $request->post('choosemList');
            for($i=0;$i<count($choosemList);$i++)
            {
                $c = (new Query())
                    ->select("*")
                    ->from('choosem')
                    ->where(['mqid'=>(int)$choosemList[$i]])
                    ->one();
                $xcm = $this->NumItem($id);
                $ins = \Yii::$app->db->createCommand()->insert('practail',
                    array('id'=>$xcm,'pid'=>$id,'qid'=>$c['mqid'],'qtypeid'=>4,'status'=>1))->execute();
            }
            $programList= $request->post('programList');
            for($i=0;$i<count($programList);$i++)
            {
                $c = (new Query())
                    ->select("*")
                    ->from('program')
                    ->where(['pqid'=>(int)$programList[$i]])
                    ->one();
                $xp = $this->NumItem($id);
                $ins = \Yii::$app->db->createCommand()->insert('practail',
                    array('id'=>$xp,'pid'=>$id,'qid'=>$c['pqid'],'qtypeid'=>3,'status'=>1))->execute();
            }
            $name = $request->post('name');
            $auth = $request->post('auth');
            $createtime = date('Y-m-d H:i:s',time());
            $insertp= \Yii::$app->db->createCommand()->insert('prac',array('id'=>$id,'name'=>$name,
                'createtime'=>$createtime,'auth'=>$auth,'status'=>1))->execute();
            if($insertp)
            {
                return array("data"=>[$insertp],"msg"=>"完成问卷试卷");
            }
            else{
                return array("data"=>[],"msg"=>"出现错误");
            }


        }
        else{
            return array("data"=>$k,"msg"=>"输入错误");
        }

    }
    /*
     * 搜索信息
     * 参数：name
     */
    public function actionSearchparctice()
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
     * 练习卷的信息
     */
    public function PracticeInfo($pid)
    {
        $query = (new Query())
            ->select('*')
            ->from('prac')
            ->where(['id'=>$pid])
            ->one();
        $queryDetail = (new Query())
            ->select('*')
            ->from('practail')
            ->where(['pid'=>$pid])
            ->all();
        $list = [];
        $n=0;
        for($i=0;$i<count($queryDetail);$i++)
        {
            $list[$n]['num']=$queryDetail[$i]['id'];
            switch ($queryDetail[$i]['qtypeid'])
            {
                case 1:{
//                    选择题
                    $c = (new Query())
                        ->select('*')
                        ->from('chooseq')
                        ->where(['cqid'=>$queryDetail[$i]['qid']])
                        ->one();
                    $list[$n]['item'] = $c['cqitem'];
                    $list[$n]['cho'] = $c['cqcho'];
                    $list[$n]['ans']=$c['cqans'];
                    $list[$n]['tail'] =$c['cqtail'];
                    $list[$n]['rem'] =$c['cqrem'];
                    $list[$n]['type']=1;
                    break;
                }
                case 2:{
//                    填空题
                    $f = (new Query())
                        ->select('*')
                        ->from('fillq')
                        ->where(['fqid'=>$queryDetail[$i]['qid']])
                        ->one();
                    $list[$n]['item'] = $f['fqitem'];
                    $list[$n]['ans']=$f['fqans'];
                    $list[$n]['tail'] =$f['fqtail'];
                    $list[$n]['rem'] =$f['fqrem'];
                    $list[$n]['type']=2;
                    break;
                }
                case 3:{
//                    程序题
                    $p = (new Query())
                        ->select('*')
                        ->from('program')
                        ->where(['pqid'=>$queryDetail[$i]['qid']])
                        ->one();
                    $list[$n]['item'] = $p['pqitem'];
                    $list[$n]['ans']=$p['pqans'];
                    $list[$n]['tail'] =$p['pqtail'];
                    $list[$n]['rem'] =$p['pqrem'];
                    $list[$n]['type']=3;
                    break;
                }
                case 4:{
//                    多选题
                    $cm = (new Query())
                        ->select('*')
                        ->from('choosem')
                        ->where(['mqid'=>$queryDetail[$i]['qid']])
                        ->one();
                    $list[$n]['item'] = $cm['mqitem'];
                    $list[$n]['cho'] = $cm['mqcho'];
                    $list[$n]['ans']=$cm['mqans'];
                    $list[$n]['tail'] =$cm['mqtail'];
                    $list[$n]['rem'] =$cm['mqrem'];
                    $list[$n]['type']=4;
                    break;
                }
                case 5:{
//                    判断题
                    $j = (new Query())
                        ->select('*')
                        ->from('judge')
                        ->where(['jqid'=>$queryDetail[$i]['qid']])
                        ->one();
                    $list[$n]['item'] = $j['jqitem'];
                    $list[$n]['ans']=$j['jqans'];
                    $list[$n]['tail'] =$j['jqtail'];
                    $list[$n]['rem'] =$j['jqrem'];
                    $list[$n]['type']=5;
                    break;
                }
                default:break;
            }
            $n++;
        }
        return array('data'=>[$query,$list],'msg'=>'练习的全部内容');
    }
    public function GetUserName($id)
    {
        return (new Query())
            ->select('*')
            ->from('user')
            ->where(['id'=>$id])
            ->one();
    }
    /*
     * 下载练习列表
     * 参数：练习id
     */
    public function actionDpractice()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $info= $this->PracticeInfo($pid);
        $query = $info['data'][0];
        $list = $info['data'][1];

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('zhouqing');
        $pdf->SetTitle($query['name']);
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
        // //问卷标题
        $pdf->Write(0, '《练习题：'.$query['name'].'》', '', 0, 'C', 1, 0, false, false, 0);
        $pdf->Write(0, $this->GetUserName($query['auth'])['username'].'  '.$query['createtime'].'    ', '', 0, 'R', 1, 0, false, false, 0);
        $pdf->Ln(10);
        foreach ($list as $key=>$value)
        {
            $flag[]=$value['num'];
        }
        array_multisort($flag,SORT_ASC,$list);
        for ($i=0; $i <count($list) ; $i++) {
            $pdf->SetFont('stsongstdlight', '', 15);
            if($list[$i]['type']==1)
            {
                $pdf->SetFont('stsongstdlight', 'B', 15);
                $item = '(选择题)第'.$list[$i]['num'].'题:'.$list[$i]['item'];
                $pdf->Write(0, $item, '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $pdf->SetFont('stsongstdlight', '', 15);
                $cho = explode('---',$list[$i]['cho']);
                $pdf->Write(0, $cho[0], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[1], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[2], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[3], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);

                $pdf->Write(0, '答案：'.$list[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '详解：'.$list[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '相关知识：'.$list[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
            }
            else if($list[$i]['type']==2)
            {
                $pdf->SetFont('stsongstdlight', 'B', 15);
                $pdf->Write(0, '(填空题)第'.$list[$i]['num'].'题:'.$list[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $pdf->SetFont('stsongstdlight', '', 15);
                $pdf->Write(0, '答案：'.$list[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '详解：'.$list[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '相关知识：'.$list[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
            }
            else if($list[$i]['type']==3)
            {
                $pdf->SetFont('stsongstdlight', 'B', 15);
                $pdf->Write(0, '(程序题)第'.$list[$i]['num'].'题:'.$list[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $pdf->SetFont('stsongstdlight', '', 15);
                $pdf->Write(0, '答案：'.$list[$i]['ans'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '详解：'.$list[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '相关知识：'.$list[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
            }
            else if($list[$i]['type']==4)
            {
                $pdf->SetFont('stsongstdlight', 'B', 15);
                $item = '(多选题)第'.$list[$i]['num'].'题:'.$list[$i]['item'];
                $pdf->Write(0, $item, '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $pdf->SetFont('stsongstdlight', '', 15);
                $cho = explode('---',$list[$i]['cho']);
                $pdf->Write(0, $cho[0], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[1], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[2], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, $cho[3], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $ans = explode('---',$list[$i]['ans']);
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
                $pdf->Write(0, '详解：'.$list[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '相关知识：'.$list[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
            }
            else if($list[$i]['type']==5)
            {
                $pdf->SetFont('stsongstdlight', 'B', 15);
                $pdf->Write(0, '(判断题)第'.$list[$i]['num'].'题:'.$list[$i]['item'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
                $pdf->SetFont('stsongstdlight', '', 15);
                if($list[$i]['ans']==1)
                {
                    $ans='正确';
                }
                else
                {
                    $ans="错误";
                }
                $pdf->Ln(5);
                $pdf->Write(0, '答案：'.$ans, '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '详解：'.$list[$i]['tail'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Write(0, '相关知识：'.$list[$i]['rem'], '', 0, 'L', true, 0, false, false, 0);
                $pdf->Ln(5);
            }
        }

        $title = $query['id'];
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
        return array('data'=>[$url,$query['name']],'msg'=>'练习题pdf下载');

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
     * 作者
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
     * 练习试题的详细信息
     * 参数：练习题id
     */
    public function actionViewpractice()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('id');
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
     * 下载答题信息
     * 大概信息：参数教师id.练习id
     */
    public function actionDpinfo()
    {
        $request = \Yii::$app->request;
        $pid = $request->post('pid');
        $uid = $request->post('uid');
        $query = (new Query())
            ->select('*')
            ->from('pracuser')
            ->where(['pid'=>$pid])
            ->andWhere(['uid'=>$uid])
            ->andWhere(['status'=>1])
            ->all();
        $list = [];
        for($i=0;$i<count($query);$i++)
        {
            $list[$i]['name'] = $this->Prac($pid)['name'];
            $list[$i]['num'] = '第'.$query[$i]['id'].'次';
            $list[$i]['auth'] = $this->User($query[$i]['uid'])['username'];
            $ctime = $query[$i]['ctime'];
            $list[$i]['grade'] = $query[$i]['grade'];
            $ctime = explode(':',$ctime);
            $ctime = $ctime[0]*60+$ctime[1]+$ctime[2]/60;
            $list[$i]['ctime'] =round($ctime,2).'分钟';
            $list[$i]['finitime'] = $query[$i]['fintime'];
            $list[$i]['status'] = $this->Status($query[$i]['status']);
        }
        $name =$this->Prac($pid)['name'];
        $name = str_replace('/','_',$name);
        $fileName = $name.'的作答信息';
        //表头
        $title = ['练习名称','作答次数','作答学生','得分','作答用时','完成时间','状态'];
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

}