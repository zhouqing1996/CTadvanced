<?php

namespace backend\module\teacher\controllers;

use yii\web\Controller;
use yii\db\Query;
use yii\common\Student;
use yii\common\Teacher;


/*
 * 教师对学生的相关管理
 */

class TeacherController extends Controller
{
    public function actionIndex()
    {
        return "教师管理";
    }
}