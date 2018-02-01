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
        $sql = 'SELECT ss.id,ss.class_name,ss.teacher_id,ss.start_time,ss.point,ss.update_time,ss.url_id,ss.teacher_url,ss.student_token,ss.student_url,ss.help_token,ss.teacher_token,teacher.`name`,teacher.real_name FROM (SELECT cal.id,cal.class_name,cal.teacher_id,cal.start_time,cal.point,cal.update_time,cal.url_id,url.teacher_url,url.student_token,url.student_url,url.help_token,url.teacher_token FROM (SELECT class.id,class.class_name,class.teacher_id,class.start_time,class.point,class.update_time,turl.url_id FROM (select * from class_home where update_time in (select max(update_time) from class_home GROUP BY teacher_id)) as class LEFT JOIN teacher_url turl ON class.teacher_id = turl.teacher_id) cal'
                .' LEFT JOIN url_home url ON cal.url_id = url.id) ss LEFT JOIN teacher ON ss.teacher_id = teacher.id';
        $result = Db::query($sql);
        $this->assign('data',$result);

        if(session('name')){
            $role = session('role');
            $id = session('id');
            $class = Db::name('student_url')->where('student_id',$id)->select();
            $array = array();
            foreach($class as $cla){
                array_push($array,$cla['class_id']);
            }
            if($role==2){
                $student = Db::name('student')->where('id',$id)->find();
                $this->assign('student',$student);
            }
            $this->assign('myclass',$array);
            $this->assign('id',$id);
            $this->assign('role',$role);
            $this->assign('name',session('name'));
        }else{
            $this->assign('role','');
            $this->assign('name','');
        }
        return $this->fetch();
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
                //获取教师对应的url
                $url_id = Db::name('teacher_url')->where('teacher_id',$result['id'])->find()['url_id'];
                $url = Db::name('url_home')->where('id',$url_id)->find();
                session('teacher',$result);
                session('url',$url['teacher_url']);
                session('teacher_token',$url['teacher_token']);
                //获取教师设置的课程信息
                $class = Db::name('class_home')->where('teacher_id',$result['id'])->find();
                if($class){
                    session('class',$class);
                    $this->assign('class',$class);
                }else{
                    session('class','');
                    $this->assign('class',$class);
                }
                $this->assign('teacher',$result);
                $this->assign('url',$url['teacher_url']);
                $this->assign('token',$url['teacher_token']);
                return $this->fetch();
            }else{
                $this->error('登陆失败，用户名或密码错误');
            }
        }

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
        $password = $_POST['password'];
        if($name==''){
            $this->error('注册失败，用户名不能为空','index/index/index','','1');
        }
        if($real_name==''){
            $this->error('注册失败，真实姓名不能为空','index/index/index','','1');
        }
        if($password==''){
            $this->error('注册失败，密码不能为空','index/index/index','','1');
        }
        $password = md5($password);
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
