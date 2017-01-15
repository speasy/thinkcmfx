<?php
	/**
	 * 后台登录页面控制器
	 *
	 */
	namespace Admin\Controller;

	class PublicController extends BaseController {
		public function _initialize() {
			C(S('sp_dynamic_config'));//加载动态配置
		}
		
		//后台登陆界面
		public function login() {
			$admin_id=session('ADMIN_ID');
			if(!empty($admin_id)){//已经登录
				redirect(U("Admin/Index/index"));
			}else{
				$site_admin_url_password =C("SP_SITE_ADMIN_URL_PASSWORD");
				$upw=session("__SP_UPW__");
				if(!empty($site_admin_url_password) && $upw!=$site_admin_url_password){
					redirect(__ROOT__."/");
				}else{
					session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__",true);
					$this->display(":login");
				}
			}
		}
		
		//用户退出
		public function logout(){
			session('ADMIN_ID',null); 
			redirect(__ROOT__."/");
		}
		
		//处理用户登录
		public function dologin(){
			//判断登录页面是否正常显示
			if(!session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__")){
				$this->error('LOGIN_ERROR');
			}

			//验证数据提交方式
			if(!IS_POST) {
				$this->error(L('LOGIN_ERROR'));
			}

			//处理post请求数据
			$name = I("post.username");
			if(empty($name)){
				$this->error(L('USERNAME_OR_EMAIL_EMPTY'));
			}
			$pass = I("post.password");
			if(empty($pass)){
				$this->error(L('PASSWORD_REQUIRED'));
			}
			$verrify = I("post.verify");
			if(empty($verrify)){
				$this->error(L('CAPTCHA_REQUIRED'));
			}

			//验证码
			if(!sp_check_verify_code()){
				$this->error(L('CAPTCHA_NOT_RIGHT'));
			}else{
				$admin_user = D("Admin/AdminUsers");
				if(strpos($name,"@")>0){//邮箱登录
					$where['user_email']=$name;
				}else{//登录名登录
					$where['user_login']=$name;
				}
				
				$result = $admin_user->where($where)->find();

				//用户不存在
				empty($result) && $this->error(L('USERNAME_NOT_EXIST'));

				if(sp_compare_password($pass,$result['user_pass'])){//密码比对
					$role_user_model=M("RoleUser");
					//$role_user_join = C('DB_PREFIX').'role as b on a.role_id =b.id';
					
					$groups=$role_user_model->alias("a")->join(C('DB_PREFIX').'role as b on a.role_id =b.id')->where(array("user_id"=>$result["id"],"status"=>1))->getField("role_id",true);
					
					if( $result["id"]!=1 && ( empty($groups) || empty($result['user_status']) ) ){
						$this->error(L('USE_DISABLED'));
					}

					//登入成功页面跳转
					session('ADMIN_ID',$result["id"]);
					session('name',$result["user_login"]);

					//更新登录用户的登录IP和登录时间
					$admin_user->where(array('id'=>$result['id']))->setField([
						'last_login_ip'=>get_client_ip(0,true),
						'last_login_time'=>date('Y-m-d H:i:s'),
					]);

					cookie("admin_username",$name,3600*24*7);//cookie有效期7天
					$this->success(L('LOGIN_SUCCESS'),U("Admin/Index/index"));
				}else{
					$this->error(L('PASSWORD_NOT_RIGHT'));
				}
			}
		}
	}
