<?php
	/**
	* 后台Controller
	*/
	namespace Admin\Controller;
	use Common\Controller\CommonBaseController;

	class BaseController extends CommonBaseController {
		public function _initialize(){
			parent::_initialize();
			define("TMPL_PATH", C("SP_ADMIN_TMPL_PATH"));
			
			//暂时取消后台多语言
			$this->load_app_admin_menu_lang();
			
			$session_admin_id=session('ADMIN_ID');
			
			if(!empty($session_admin_id)){
				$users_obj= M("Users");
				$user=$users_obj->where(array('id'=>$session_admin_id))->find();
				if(!$this->check_access($session_admin_id)){
					$this->error("您没有访问权限！");
				}
				$this->assign("admin",$user);
			}else{
				
				if(IS_AJAX){
					$this->error("您还没有登录！",U("admin/public/login"));
				}else{
					header("Location:".U("admin/public/login"));
					exit();
				}

			}
		}


		public function __construct() {
			hook('admin_begin');
			$admintpl_path=C("SP_ADMIN_TMPL_PATH").C("SP_ADMIN_DEFAULT_THEME")."/";
			C("TMPL_ACTION_SUCCESS",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_SUCCESS"));
			C("TMPL_ACTION_ERROR",$admintpl_path.C("SP_ADMIN_TMPL_ACTION_ERROR"));
			parent::__construct();
			$time=time();
			$this->assign("js_debug",APP_DEBUG?"?v=$time":"");
		}

	

		/**
		* 初始化后台菜单
		*/
		public function initMenu() {
			$Menu = F("Menu");
			if (!$Menu) {
				$Menu=D("Common/Menu")->menu_cache();
			}
			return $Menu;
		}
	

		/**
		*  检查后台用户访问权限
		* @param int $uid 后台用户id
		* @return boolean 检查通过返回true
		*/
		private function check_access($uid){
			//如果用户角色是1，则无需判断
			if($uid == 1){
				return true;
			}

			$rule=MODULE_NAME.CONTROLLER_NAME.ACTION_NAME;
			$no_need_check_rules=array("AdminIndexindex","AdminMainindex");

			if( !in_array($rule,$no_need_check_rules) ){
				return sp_auth_check($uid);
			}else{
				return true;
			}
		}

		/**
		* 加载后台用户语言包
		*/
		private function load_app_admin_menu_lang(){
			$default_lang=C('DEFAULT_LANG');
			$langSet=C('ADMIN_LANG_SWITCH_ON',null,false)?LANG_SET:$default_lang;
			if($default_lang!=$langSet){
				$admin_menu_lang_file=SPAPP.MODULE_NAME."/Lang/".$langSet."/admin_menu.php";
			}else{
				$admin_menu_lang_file=SITE_PATH."data/lang/".MODULE_NAME."/Lang/$langSet/admin_menu.php";
				if(!file_exists_case($admin_menu_lang_file)){
					$admin_menu_lang_file=SPAPP.MODULE_NAME."/Lang/".$langSet."/admin_menu.php";
				}
			}
			if(is_file($admin_menu_lang_file)){
				$lang=include $admin_menu_lang_file;
				L($lang);
			}
		}
	}
