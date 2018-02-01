<?php
/**
 * Created by PhpStorm.
 * User: mubin
 * Date: 2018/1/29
 * Time: 上午12:50
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
class Admin extends Controller{
    //管理员信息页
    public function index(){
        //读取url表中所有的url数据
        $sql = 'select * from (select url.teacher_url,url.student_token,url.student_url,url.help_token,url.`status`,url.teacher_token,turl.teacher_id,turl.url_id from url_home url LEFT JOIN teacher_url turl ON url.id=turl.url_id) t LEFT JOIN teacher ON t.teacher_id = teacher.id';
        $result = Db::query($sql);
//        $result = Db::table('url_home url,teacher,teacher_url turl');
        $this->assign('urls',$result);
        $this->assign('name',session('name'));
        return $this->fetch();
    }
    //添加url
    public function addUrl(){
        $id = self::create_uuid();
        $teacher_url = $_POST['teacher_url'];
        $teacher_token = $_POST['teacher_token'];
        $help_token = $_POST['help_token'];
        $student_url = $_POST['student_url'];
        $student_token = $_POST['student_token'];
        if(Db::name('url_home')->insert(['id'=>$id,'teacher_url'=>$teacher_url,'teacher_token'=>$teacher_token,'help_token'=>$help_token,'student_url'=>$student_url,'student_token'=>$student_token,'status'=>'0'])){
            $this->success('添加成功','index/admin/index','','1');
        }else{
            $this->error('添加失败，请检查数据');
        }
    }
    //增加教师积分
    public function addTeacherPoint(){
        $addPoint = $_POST['add_teacher_point'];

    }
    //减少教师积分
    public function reduceTeacherPoint(){

    }
    //增加学生积分
    public function addStudentPoint(){

    }
    //减少学生积分
    public function reduceStudentPoint(){

    }
    //随机生成id
    public static function create_uuid($prefix = ""){    //可以指定前缀
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $prefix . $uuid;
    }
}