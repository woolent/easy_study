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
class Teacher extends Controller{
    public function index(){
        $id = session('id');
        $teacher = Db::name('teacher')->where('id',$id)->find();

        $url =  session('url');
        $token = session('teacher_token');
        //查询最新的class数据
//        $class = Db::name('class_home')->where('teacher_id',$teacher['id'])->find();
        $sql = "select * from class_home where teacher_id ='".$teacher['id']."' order by update_time desc";
        $classes = Db::query($sql);
        if($classes){
            $this->assign('class',$classes[0]);
        }else{
            $this->assign('class','');
        }

        $this->assign('teacher',$teacher);
        $this->assign('url',$url);
        $this->assign('token',$token);
        return $this->fetch();
    }
    //修改个人信息
    public function updateInfo(){
        $id = $_POST['id'];
        $name = $_POST['name'];
        $real_name = $_POST['real_name'];
        //根据id查询数据库中的密码
        $password_old = Db::name('teacher')->where('id',$id)->find()['password'];
        $password_new = $_POST['password'];
        if($password_new!=$password_old){
            $password = md5($password_new);
            if(Db::name('teacher')->where('id',$id)->update(['name'=>$name,'real_name'=>$real_name,'password'=>$password])){
                $this->success('修改成功','index/teacher/index','','1');
            }else{
                $this->error('修改失败，请稍后再试','index/teacher/index','','1');
            }
        }else{
           if(Db::name('teacher')->where('id',$id)->update(['name'=>$name,'real_name'=>$real_name])){
               $this->success('修改成功','index/teacher/index','','1');
           }else{
               $this->error('修改失败，请稍后再试','index/teacher/index','','1');
           }
        }
    }
    //教师添加课程
    public function addClass(){
        $id = self::create_uuid();
        $classname = $_POST['class_name'];
        $start_time = $_POST['start_time'];
        $point = $_POST['point'];
        if($classname==''){
            $this->error('添加失败，课程名称不能为空','index/Teacher/index','','1');
        }
        if($start_time==''){
            $this->error('添加失败，开始时间不能为空','index/Teacher/index','','1');
        }
        if($point==''){
            $this->error('添加失败，所需积分不能为空','index/Teacher/index','','1');
        }
        if($point<0){
            $this->error('添加失败，积分不能为负','index/Teacher/index','','1');
        }
        $time = time();
        //登录的教师id
        $teacher_id = session('teacher')['id'];
        if(Db::name('class_home')->insert(['id'=>$id,'class_name'=>$classname,'start_time'=>$start_time,'teacher_id'=>$teacher_id,'point'=>$point,'update_time'=>$time])){
            $this->success('添加成功','index/Teacher/index','','1');
        }else{
            $this->error('添加失败','index/Teacher/index');
        }
    }
    //教师修改课程名称或时间
    public function updateClass(){
        $id = $_POST['id'];
        $class_name = $_POST['class_name'];
        $start_time = $_POST['start_time'];
        $point = $_POST['point'];
        if($class_name==''){
            $this->error('添加失败，课程名称不能为空','index/Teacher/index','','1');
        }
        if($start_time==''){
            $this->error('添加失败，开始时间不能为空','index/Teacher/index','','1');
        }
        if($point==''){
            $this->error('添加失败，所需积分不能为空','index/Teacher/index','','1');
        }
        if(Db::name('class_home')->where('id',$id)->update(['class_name'=>$class_name,'start_time'=>$start_time,'point'=>$point])){
            $this->success('添加成功','index/Teacher/index','','1');
        }else{
            $this->error('未做修改','index/Teacher/index','','1');
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