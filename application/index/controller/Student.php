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
use app\index\model\Student as stu;
use app\index\model\StudentUrl;

class Student extends Controller{
    public function index(){
        $this->redirect('index/index');
    }
    public function login(){
        $name = $_POST['name'];
        $password = md5($_POST['password']);
        $role = $_POST['admin'];
        $result = stu::get(['name'=>$name,'password'=>$password]);
        if($result){
            session('id',$result['id']);
            session('name',$name);
            session('real_name',$result['real_name']);
            session('student',$result);
            session('role',$role);

            $student_id = $result['id'];
            $class = Db::name('student_url')->where('student_id',$student_id)->select();
            $array = array();
            foreach($class as $cla){
                array_push($array,$cla['class_id']);
            }
            session('myclass',$array);
            $this->success('登录成功','index','','1');
        }else{
            $this->error('登录失败，用户名或密码错误');
        }

    }
    //跳转注册页面
    public function register(){
        return view();
    }
    //注册
    public function registerAction(){
        $name = $_POST['name'];
        $real_name = $_POST['real_name'];
        $password = $_POST['password'];
        if(Db::name('student')->where('name',$name)->find()){
            $this->error('注册失败，用户名重复','index/student/register','','1');
        }
        if($name==''){
            $this->error('注册失败，用户名不能为空','index/student/register','','1');
        }
        if($real_name==''){
            $this->error('注册失败，真实姓名不能为空','index/student/register','','1');
        }
        if($password==''){
            $this->error('注册失败，密码不能为空','index/student/register','','1');
        }
        $id = self::create_uuid();
        $password = md5($password);
        if(Db::name('student')->insert(['id'=>$id,'name'=>$name,'real_name'=>$real_name,'password'=>$password,'point'=>'0'])){
            $this->success('注册成功','index/index/index','','1');
        }else{
            $this->error('注册失败','index/student/register');
        }
    }

    /**
     * @param $point 课程需要的积分
     * @param $class_id 课程id
     * @param $teacher_id 授课教师id
     */
    public function pay($point,$class_id,$teacher_id){
        //学生现有的积分
        $student_point = session('student')['point'];
        //学生剩余积分
        if($student_point<$point){
            $this->error('剩余积分不足，请联系客服充值','index/student/index','','1');
        }
        if($point==0){
            //获取到学生url
            $url_id = Db::name('teacher_url')->where('teacher_id',$teacher_id)->find()['url_id'];
            $url = Db::name('url_home')->where('id',$url_id)->find();
            //学生姓名
            $name = session('name');
            $real_name = session('student')['real_name'];
            $redirect_url = $url['student_url'].'visible=true&nickname='.$real_name.'&token='.$url['student_token'];
            $this->redirect($redirect_url);
        }else{
            $student_new_point = $student_point-$point;
            //更新学生数据
            $student_id = session('id');

            if(!Db::name('student')->where('id',$student_id)->update(['point'=>$student_new_point])){
                $this->error('积分扣除失败,请稍后再试','index/student/index','','1');
            }
            //记录学生课程信息
            $surl_id = self::create_uuid();
            if(!Db::name('student_url')->insert(['id'=>$surl_id,'student_id'=>$student_id,'class_id'=>$class_id])){
                $this->error('课程记录添加失败，请联系管理员','index/student/index','','1');
            }
            //教师现有的积分
            $teacher_point = Db::name('teacher')->where('id',$teacher_id)->find()['point'];
            $teacher_new_point = $teacher_point+$point;
            if(!Db::name('teacher')->where('id',$teacher_id)->update(['point'=>$teacher_new_point])){
                $this->error('教师积分添加失败，请联系管理员','index/student/index','','1');
            }
            //获取到学生url
            $url_id = Db::name('teacher_url')->where('teacher_id',$teacher_id)->find()['url_id'];
            $url = Db::name('url_home')->where('id',$url_id)->find();
            //学生姓名
            $name = session('name');
            $real_name = session('student')['real_name'];
            $redirect_url = $url['student_url'].'visible=true&nickname='.$real_name.'&token='.$url['student_token'];
            $this->redirect($redirect_url);
        }
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