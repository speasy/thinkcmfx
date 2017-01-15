<?php
	namespace Common\Controller;
	use Think\Controller;

	class CommonBaseController extends Controller {
		public function _initialize() {
			$this->assign([
				'waitSecond'=>3,
				'js_debug'=>APP_DEBUG ? '?v='.time() : '',
			]);
		}



		/**
		 * 重写Think\Controller中的ajaxReturn
		 * Ajax方式返回数据到客户端
		* @access protected
		* @param mixed $data 要返回的数据
		* @param String $type AJAX返回数据格式
		* @return void
		*/
		protected function ajaxReturn($data, $type = '',$json_option=0) {
			
			$data['referer'] = $data['url'] ? $data['url'] : "";
			$data['state']   = !empty($data['status']) ? "success" : "fail";
			
			if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
			switch (strtoupper($type)){
				case 'JSON' :
					// 返回JSON数据格式到客户端 包含状态信息
					header('Content-Type:application/json; charset=utf-8');
					exit(json_encode($data,$json_option));
				case 'XML'  :
					// 返回xml格式数据
					header('Content-Type:text/xml; charset=utf-8');
					exit(xml_encode($data));
				case 'JSONP':
					// 返回JSON数据格式到客户端 包含状态信息
					header('Content-Type:application/json; charset=utf-8');
					$handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
					exit($handler.'('.json_encode($data,$json_option).');');
				case 'EVAL' :
					// 返回可执行的js脚本
					header('Content-Type:text/html; charset=utf-8');
					exit($data);
				case 'AJAX_UPLOAD':
					// 返回JSON数据格式到客户端 包含状态信息
					header('Content-Type:text/html; charset=utf-8');
					exit(json_encode($data,$json_option));
				default :
					// 用于扩展其他返回格式数据
					Hook::listen('ajax_return',$data);
			}
			
		}
		
		/**
		* 
		* @param number $totalSize 总数
		* @param number $pageSize  总页数
		* @param number $currentPage 当前页
		* @param number $listRows 每页显示条数
		* @param string $pageParam 分页参数
		* @param string $pageLink 分页链接
		* @param string $static 是否为静态链接
		 */

		protected function page($total_size = 1, $page_size = 0, $current_page = 1, $listRows = 6, $pageParam = '', $pageLink = '', $static = false) {
			if ($page_size == 0) {
				$page_size = C("PAGE_LISTROWS");
			}

			if (empty($pageParam)) {
				$pageParam = C("VAR_PAGE");
			}

			$page = new \Page($total_size, $page_size, $current_page, $listRows, $pageParam, $pageLink, $static);
			$page->SetPager('Admin', '{first}{prev}&nbsp;{liststart}{list}&nbsp;{next}{last}<span>共{recordcount}条数据</span>', array("listlong" => "4", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
			return $page;
		}


		//空操作
		public function _empty() {
			$this->error('该页面不存在！');
		}
		
		/**
		* 检查操作频率
		* @param int $duration 距离最后一次操作的时长
		*/
		protected function check_last_action($duration){
			
			$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
			$time=time();
			
			$session_last_action=session('last_action');
			if(!empty($session_last_action['action']) && $action==$session_last_action['action']){
				$mduration=$time-$session_last_action['time'];
				if($duration>$mduration){
					$this->error("您的操作太过频繁，请稍后再试~~~");
				}else{
					session('last_action.time',$time);
				}
			}else{
				session('last_action.action',$action);
				session('last_action.time',$time);
			}
		}
		
		/**
		* 模板主题设置
		* @access protected
		* @param string $theme 模版主题
		* @return Action
		*/
		public function theme($theme){
			$this->theme=$theme;
			return $this;
		}

			/**
		* 消息提示
		* @param type $message
		* @param type $jumpUrl
		* @param type $ajax
		*/
		public function success($message = '', $jumpUrl = '', $ajax = false) {
			parent::success($message, $jumpUrl, $ajax);
		}

		/**
		* 模板显示
		* @param type $templateFile 指定要调用的模板文件
		* @param type $charset 输出编码
		* @param type $contentType 输出类型
		* @param string $content 输出内容
		* 此方法作用在于实现后台模板直接存放在各自项目目录下。例如Admin项目的后台模板，直接存放在Admin/Tpl/目录下
		*/
		public function display($templateFile = '', $charset = '', $contentType = '', $content = '', $prefix = '') {
			parent::display($this->parseTemplate($templateFile), $charset, $contentType,$content,$prefix);
		}

		/**
		* 获取输出页面内容
		* 调用内置的模板引擎fetch方法，
		* @access protected
		* @param string $templateFile 指定要调用的模板文件
		* 默认为空 由系统自动定位模板文件
		* @param string $content 模板输出内容
		* @param string $prefix 模板缓存前缀*
		* @return string
		*/
		public function fetch($templateFile='',$content='',$prefix=''){
			$templateFile = empty($content)?$this->parseTemplate($templateFile):'';
			return parent::fetch($templateFile,$content,$prefix);
		}

		/**
		* 自动定位模板文件
		* @access protected
		* @param string $template 模板文件规则
		* @return string
		*/
		public function parseTemplate($template='') {
			$tmpl_path=C("SP_ADMIN_TMPL_PATH");
			define("SP_TMPL_PATH", $tmpl_path);
			if($this->theme) { // 指定模板主题
				$theme = $this->theme;
			}else{
				// 获取当前主题名称
				$theme      =    C('SP_ADMIN_DEFAULT_THEME');
			}

			if(is_file($template)) {
				// 获取当前主题的模版路径
				define('THEME_PATH',   $tmpl_path.$theme."/");
				return $template;
			}
			$depr       =   C('TMPL_FILE_DEPR');
			$template   =   str_replace(':', $depr, $template);

			// 获取当前模块
			$module   =  MODULE_NAME."/";
			if(strpos($template,'@')){ // 跨模块调用模版文件
				list($module,$template)  =   explode('@',$template);
			}

			$module =$module."/";

			// 获取当前主题的模版路径
			define('THEME_PATH',   $tmpl_path.$theme."/");

			// 分析模板文件规则
			if('' == $template) {
				// 如果模板文件名为空 按照默认规则定位
				$template = CONTROLLER_NAME . $depr . ACTION_NAME;
			}elseif(false === strpos($template, '/')){
				$template = CONTROLLER_NAME . $depr . $template;
			}

			$cdn_settings=sp_get_option('cdn_settings');
			if(!empty($cdn_settings['cdn_static_root'])){
				$cdn_static_root=rtrim($cdn_settings['cdn_static_root'],'/');
				C("TMPL_PARSE_STRING.__TMPL__",$cdn_static_root."/".THEME_PATH);
				C("TMPL_PARSE_STRING.__PUBLIC__",$cdn_static_root."/public");
				C("TMPL_PARSE_STRING.__WEB_ROOT__",$cdn_static_root);
			}else{
				C("TMPL_PARSE_STRING.__TMPL__",__ROOT__."/".THEME_PATH);
			}
			

			C('SP_VIEW_PATH',$tmpl_path);
			C('DEFAULT_THEME',$theme);
			define("SP_CURRENT_THEME", $theme);

			$file = sp_add_template_file_suffix(THEME_PATH.$module.$template);
			$file= str_replace("//",'/',$file);
			if(!file_exists_case($file)) E(L('_TEMPLATE_NOT_EXIST_').':'.$file);
			return $file;
		}

		/**
		* 排序 排序字段为listorders数组 POST 排序字段为：listorder或者自定义字段
		* @param mixed $model 需要排序的模型类
		* @param string $custom_field 自定义排序字段 默认为listorder,可以改为自己的排序字段
		*/
		protected function _listorders($model,$custom_field='') {
			if (!is_object($model)) {
				return false;
			}
			$field=empty($custom_field)&&is_string($custom_field)?'listorder':$custom_field;
			$pk = $model->getPk(); //获取主键名称
			$ids = $_POST['listorders'];
			foreach ($ids as $key => $r) {
				$data[$field] = $r;
				$model->where(array($pk => $key))->save($data);
			}
			return true;
		}
	}
