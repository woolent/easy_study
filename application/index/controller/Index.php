<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Teacher;
use think\Db;

class Index extends controller
{
    public function index()
    {
        //查询老师设置的课程信息和历史课程信息，传给页面显示出来
        if(session('name')){
            $this->assign('name',session('name'));
        }else{
            $this->assign('name','');
        }
        return view();
    }
    //管理员在教师表中，属于特殊的教师，不做单独设置
    public function teacher_login(){
        $role = $_POST['admin'];
            session('role',$role);
        if($_POST['admin']=='1'){
            $name = $_POST['admin_name'];
            $password = $_POST['admin_password'];
            $result = Teacher::get(['name'=>$name,'password'=>$password]);
            if($result){
                session('id',$result['id']);
                session('name',$name);
                session('real_name',$result['real_name']);
                session('point',$result['point']);
                $this->success('登陆成功','index','','1');
            }else{
                $this->error('登陆失败，用户名或密码错误');
            }
        }else{
            $name = $_POST['admin_name'];
            $password = md5($_POST['admin_password']);
            $result = Teacher::get(['name'=>$name,'password'=>$password]);
            if($result){
                session('id',$result['id']);
                session('name',$name);
                session('real_name',$result['real_name']);
                session('point',$result['point']);
                $url_id = Db::name('teacher_url')->where('teacher_id',$result['id'])->find()['url_id'];
                $url = Db::name('url_home')->where('id',$url_id)->find()['teacher_url'];
                $this->assign('teacher',$result);
                $this->assign('url',$url);
                return $this->fetch();
            }else{
                $this->error('登陆失败，用户名或密码错误');
            }
        }

    }
    //学生登陆
    public function student_login(){

    }
    //跳转教师注册页面
    public function teacher_to_register(){
        return $this->fetch();
    }
    //教师注册
    public function teacher_register(){
        $id = $this->create_uuid();
        $name = $_POST['name'];
        $real_name = $_POST['real_name'];
        $password = md5($_POST['password']);
        $result = Db::name('url_home')->where('status','0')->find();
        if(!$result){
            $this->error('教师url分配失败，请联系管理员再试');
        }
        //查询用户名不能重复
        if(Db::name('teacher')->where('name',$name)->find()){
            $this->error('用户名重复');
        }
        //教师获取对应的url
        $url = $result['teacher_url'];
        //将获取到的url的status改为1
        $re = Db::name('url_home')->where('id',$result['id'])->update(['status'=>'1']);
        //将url的id和teacher_id插入到教师和url关联表中
        $r_url = $this->create_uuid();
        $rela = Db::name('teacher_url')->insert(['id'=>$r_url,'teacher_id'=>$id,'url_id'=>$result['id']]);
        if(!$rela){
            $this->error('注册失败，未正确分配教师url');
        }
        if(Db::name('teacher')->insert(['id'=>$id,'name'=>$name,'real_name'=>$real_name,'password'=>$password,'point'=>'0'])){
            $this->success('注册成功','index');
        }else{
            $this->error('注册失败，请检查');
        }
    }
    //学生注册
    public function student_register(){

    }
    //判断身份（管理员1，教师0，学生2）
    public function role(){
        $role = session('role');
        if($role==0){
            //重定向到教师详情页面
            $this->redirect('teacher/index','','1','页面跳转中');
        }
        if($role==1){
            //重定向到管理员页面
            $this->redirect('admin/index','','1','页面跳转中');
        }
        if($role==2){
            //重定向到学生页面
            $this->redirect('student/index','','1','页面跳转中');
        }
    }
    //注销
    public function login_out(){
        session(null);
        return $this->success('成功退出','index','','1');
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
