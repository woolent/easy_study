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
        $sql = 'select * from (select url.teacher_url,url.student_token,url.student_url,url.help_token,url.`status`,url.teacher_token,turl.teacher_id,url.id as url_id from url_home url LEFT JOIN teacher_url turl ON url.id=turl.url_id) t LEFT JOIN teacher ON t.teacher_id = teacher.id';
        $result = Db::query($sql);
//        $result = Db::table('url_home url,teacher,teacher_url turl');
        $this->assign('urls',$result);
        $this->assign('name',session('name'));

        //查询老师的信息
        $teacher = Db::name('teacher')->select();
        $this->assign('teacher',$teacher);
        //查询学生的信息
        $student = Db::name('student')->select();
        $this->assign('student',$student);
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
        if($teacher_url==''){
            $this->error('添加失败，教师url不能为空');
        }
        if($teacher_token==''){
            $this->error('添加失败，教师口令不能为空');
        }
        if($help_token==''){
            $this->error('添加失败，助教口令不能为空');
        }
        if($student_url==''){
            $this->error('添加失败，学生url不能为空');
        }
        if($student_token==''){
            $this->error('添加失败，学生口令不能为空');
        }
        if(Db::name('url_home')->insert(['id'=>$id,'teacher_url'=>$teacher_url,'teacher_token'=>$teacher_token,'help_token'=>$help_token,'student_url'=>$student_url,'student_token'=>$student_token,'status'=>'0'])){
            $this->success('添加成功','index/admin/index','','1');
        }else{
            $this->error('添加失败，请检查数据');
        }
    }
    //修改添加的url
    public function updateUrl(){
        $id = $_POST['url_id'];
        $teacher_url = $_POST['teacher_url'];
        $teacher_token = $_POST['teacher_token'];
        $help_token = $_POST['help_token'];
        $student_url = $_POST['student_url'];
        $student_token = $_POST['student_token'];
        if($teacher_url==''){
            $this->error('添加失败，教师url不能为空');
        }
        if($teacher_token==''){
            $this->error('添加失败，教师口令不能为空');
        }
        if($help_token==''){
            $this->error('添加失败，助教口令不能为空');
        }
        if($student_url==''){
            $this->error('添加失败，学生url不能为空');
        }
        if($student_token==''){
            $this->error('添加失败，学生口令不能为空');
        }
        $result = Db::name('url_home')->where('id',$id)->find();
        if($result['teacher_url']==$teacher_url && $result['teacher_token']==$teacher_token && $result['help_token']==$help_token && $result['student_url']==$student_url && $result['student_token']==$student_token){
            $this->success('未做修改','idnex/admin/index','','1');
        }else if(Db::name('url_home')->where('id',$id)->update(['teacher_url'=>$teacher_url,'teacher_token'=>$teacher_token,'help_token'=>$help_token,'student_url'=>$student_url,'student_token'=>$student_token])){
            $this->success('修改成功','index/admin/index','','1');
        }else{
            $this->error('添加失败，请检查数据');
        }
    }
    //删除url
    public function deleteUrl($id){
        if(Db::name('url_home')->where('id',$id)->find()['status']==1){
            $this->error('url正在使用，无法删除');
        }
        if(Db::name('url_home')->where('id',$id)->delete()){
            $this->success('删除成功','index/admin/index','','1');
        }
    }

    /**
     * 增加教师积分
     * @param $id
     */
    public function addTeacherPoint($id){
        //需要增加的积分
        $addPoint = $_POST['add_point'];
        if($addPoint<0){
            $this->error('输入积分不能为负');
        }else if($addPoint==0){
            $this->error('积分不能为零');
        }else{
            $teacher = Db::name('teacher')->where('id',$id)->find();
            $point = $teacher['point']+$addPoint;
            if(Db::name('teacher')->where('id',$id)->update(['point'=>$point])){
                $this->success('增加成功，用户需要重新登录以查看最新积分');
            }else{
                $this->error('增加失败，联系开发人员查看');
            }
        }
    }
    //减少教师积分
    public function reduceTeacherPoint($id){
        //需要扣除的积分
        $reducePoint = $_POST['reduce_point'];
        if($reducePoint<0){
            $this->error('输入积分不能为负');
        }else if($reducePoint==0){
            $this->error('积分不能为零');
        }else {
            $teacher = Db::name('teacher')->where('id',$id)->find();
            //教师拥有的积分
            $point = $teacher['point'];
            if($point<$reducePoint){
                $this->error('所需积分不足，请先充值');
            }else{
                $point = $point-$reducePoint;
                if(Db::name('teacher')->where('id',$id)->update(['point'=>$point])){
                    $this->success('扣除成功，用户需要重新登录以查看最新积分');
                }else{
                    $this->error('扣除失败，联系开发人员查看');
                }
            }
        }
    }
    //增加学生积分
    public function addStudentPoint($id){
        //需要增加的积分
        $addPoint = $_POST['add_point'];
        if($addPoint<0){
            $this->error('输入积分不能为负');
        }else if($addPoint==0){
            $this->error('积分不能为零');
        }else{
            $student = Db::name('student')->where('id',$id)->find();
            $point = $student['point']+$addPoint;
            if(Db::name('student')->where('id',$id)->update(['point'=>$point])){
                $this->success('增加成功，用户需要重新登录以查看最新积分');
            }else{
                $this->error('增加失败，联系开发人员查看');
            }
        }
    }
    //减少学生积分
    public function reduceStudentPoint($id){
        $reducePoint = $_POST['reduce_point'];
        if($reducePoint<0){
            $this->error('输入积分不能为负');
        }else if($reducePoint==0){
            $this->error('积分不能为零');
        }else {
            $student = Db::name('student')->where('id',$id)->find();
            //教师拥有的积分
            $point = $student['point'];
            if($point<$reducePoint){
                $this->error('所需积分不足，请先充值');
            }else{
                $point = $point-$reducePoint;
                if(Db::name('student')->where('id',$id)->update(['point'=>$point])){
                    $this->success('扣除成功，用户需要重新登录以查看最新积分');
                }else{
                    $this->error('扣除失败，联系开发人员查看');
                }
            }
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