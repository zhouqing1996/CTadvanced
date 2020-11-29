<?php

namespace backend\module\exam\controllers;
use TCPDF;
use yii\db\Query;
use \yii\web\Controller;


//class MYPDF extends TCPDF {
//
//    //Page header
//    public function Header() {
//        $this->SetFont('stsongstdlight','',10);
//        $this->Write(10,'试卷详细信息','',false,'C');
//        $this->Ln(20);
//    }
//
//    // Page footer
//    public function Footer() {
//        $this->SetY(-15);
//        $this->SetFont('stsongstdlight','',10);
//        $this->Cell(0,10,'第'.$this->PageNo().'页',0,0,'R');
//    }
//}

class DownloadController extends Controller
{

    public function actionIndex()
    {
        return array('data'=>'sss','msg'=>'pdf输出');
    }
//    试卷信息
//参数：eid
    public function Exam($eid)
    {
        $query = (new Query())
            ->select('*')
            ->from('exam')
            ->where(['exid'=>$eid])
            ->one();
        $queryDetail = (new Query())
            ->select('*')
            ->from('examtail')
            ->where(['exid'=>$eid])
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
        return array('data'=>[$query,$list],'msg'=>'试卷的全部内容');
    }

//    试卷下载
//参数：试卷信息数据
    public function actionDfile()
    {
        $request = \Yii::$app->request;
        $eid = $request->post('eid');
        $x = $this->Exam($eid);
        $query = $x['data'][0];
        $list = $x['data'][1];

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('zhouqing');
        $pdf->SetTitle($query['exname']);
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
        $pdf->Write(0, $query['exname'], '', 0, 'C', 1, 0, false, false, 0);
        $pdf->Write(0, $query['gdtime'].'分钟', '', 0, 'R', 1, 0, false, false, 0);
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

        $title = $query['exid'];
        $path = \Yii::$app->basePath;
        $filePath = $path.'/files/';
        if(!is_dir($filePath))
        {
            mkdir(iconv('utf-8','GBK',$filePath),0777,true);
        }
        $path=$filePath.$title.'.pdf';
        ob_clean();
        $pdf->Output($path,'F');
        $url=explode('ComputeThinking',$path);
        $url='http://127.0.0.1/ComputeThinking'.$url[1];
        return array('data'=>[$url,$query['exname']],'msg'=>'试卷pdf下载');
    }
}
