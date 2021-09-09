<?php
/**
 * Plugin Name: tencentcloud-captcha
 * Plugin URI:  https://wordpress.org/plugins/tencentcloud-captcha
 * Description: 通过腾讯云验证码提供立体、全面的人机验证。
 * Version: 1.0.1
 * Author: 腾讯云
 * Author URI: https://www.tencent.com/
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


define( 'TENCENT_WORDPRESS_CAPTCHA_VERSION', '1.0.2' );
define( 'TENCENT_WORDPRESS_CAPTCHA_DIR', plugin_dir_path( __FILE__ ) );
define( 'TENCNET_WORDPRESS_CAPTCHA_BASENAME', plugin_basename(__FILE__) );
define( 'TENCENT_WORDPRESS_CAPTCHA_JS_DIR', plugins_url( 'tencentcloud-captcha' ) . '/' . 'js' . '/' );
define( 'TENCENT_WORDPRESS_CAPTCHA_CSS_DIR', plugins_url( 'tencentcloud-captcha' ) . '/' . 'css' . '/' );
define( 'TENCENT_WORDPRESS_CAPTCHA_NAME', 'tencentcloud-captcha');
define( 'TENCENT_WORDPRESS_CAPTCHA_SHOW_NAME', 'tencentcloud-captcha');
defined('TENCENT_WORDPRESS_CAPTCHA_URL') or define('TENCENT_WORDPRESS_CAPTCHA_URL', plugins_url(TENCENT_WORDPRESS_CAPTCHA_NAME) . '/');
defined('TENCENT_WORDPRESS_CAPTCHA_LOGS') or define('TENCENT_WORDPRESS_CAPTCHA_LOGS', plugin_dir_path(__FILE__).'logs');

defined('TENCENT_WORDPRESS_PLUGINS_COMMON_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_URL', TENCENT_WORDPRESS_CAPTCHA_URL . 'common' . '/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL', TENCENT_WORDPRESS_PLUGINS_COMMON_URL . 'css' . '/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR', TENCENT_WORDPRESS_CAPTCHA_DIR . 'common' . '/');

require_once 'tencent-captch-debuger.php';
require_once 'TencentCloudCaptchaActions.php';
$TecentWordpressCaptchaActions = new TencentCloudCaptchaActions();

register_activation_hook(__FILE__, array($TecentWordpressCaptchaActions, 'tencentCaptchaActivatePlugin'));
register_deactivation_hook(__FILE__, array($TecentWordpressCaptchaActions, 'tencentCaptchaDeactivePlugin'));


add_action('admin_notices', array($TecentWordpressCaptchaActions, 'tencentCaptchaSetingNotice'));

//添加插件设置页面
add_action('admin_menu',  array($TecentWordpressCaptchaActions, 'tencentCaptchaPluginSettingPage'));
// 插件列表加入设置按钮
add_filter('plugin_action_links', array($TecentWordpressCaptchaActions, 'tencentCaptchaPluginSettingPageLinkButton'), 10, 2);

//添加注册表单
add_action('register_form',array($TecentWordpressCaptchaActions,'tencentCaptchaRegisterForm'));
//添加登录表单
add_action('login_form',array($TecentWordpressCaptchaActions,'tencentCaptchaLoginForm'));
//添加评论表单
add_filter('comment_form_submit_button',array($TecentWordpressCaptchaActions,'tencentCaptchaCommentForm'),10,1);
//添加忘记密码表单
add_action('lostpassword_form',array($TecentWordpressCaptchaActions,'tencentCaptchaLostpasswordForm'));
//添加登录时的钩子
add_action( 'authenticate', array($TecentWordpressCaptchaActions,'tencentCapthcaLoginCodeVerify'),101,3);
//添加注册时验证
add_action('register_post',array($TecentWordpressCaptchaActions,'tencentCaptchaRegisterCodeVerify'),10,3);
//添加忘记密码时验证
add_action('lostpassword_post',array($TecentWordpressCaptchaActions,'tencentCaptchaLostpasswordCodeVerify'),10);
//添加评论时验证
add_action('preprocess_comment',array($TecentWordpressCaptchaActions,'tencentCaptchaCommentCodeVerify'));

//添加腾讯云验证码配置保存
add_action('wp_ajax_update_codeVerify_settings', array($TecentWordpressCaptchaActions, 'tencentCaptchaUpdateCaptchaSettings'));

//验证码测试
add_action('wp_ajax_codeVerify_check', array($TecentWordpressCaptchaActions, 'tencentCaptchaCodeVerifyCheck'));

//删除调试日志
add_action('wp_ajax_delete_captcha_logfile', array($TecentWordpressCaptchaActions, 'tencentCaptchaDeleteLogfile'));

//js脚本引入
add_action( 'admin_enqueue_scripts', array($TecentWordpressCaptchaActions, 'tencentCaptchaLoadMyScriptEnqueue'));
add_action( 'login_enqueue_scripts', array($TecentWordpressCaptchaActions, 'tencentCaptchaLoadMyScriptEnqueue'));
add_action( 'wp_enqueue_scripts', array($TecentWordpressCaptchaActions, 'tencentCaptchaLoadScriptForPage'));
add_action( 'comment_form_before', array($TecentWordpressCaptchaActions, 'tencentCaptchaLoadMyScriptEnqueue'));

//插件中心初始化
add_action( 'init', array($TecentWordpressCaptchaActions, 'tencentCaptchaInit'));
