<?php
	/**
	* 行为定义
	* usage：标签位 => ['行为1','行为2',..] 
	*
	*/
	return array( // 添加下面一行定义即可
		'app_init' => array(
			'Common\Behavior\InitHookBehavior',
		),
		'app_begin' => array(
			'Behavior\CheckLangBehavior',
			'Common\Behavior\UrldecodeGetBehavior'
		),
		'view_filter' => array(
			'Common\Behavior\TmplStripSpaceBehavior'
		),
		'admin_begin' => array(
			'Common\Behavior\AdminDefaultLangBehavior'
		)
	);
