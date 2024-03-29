<?php
/*
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
if(!defined('WP_UNINSTALL_PLUGIN')){
    exit;
}
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR', plugin_dir_path(__FILE__)  . 'common' . DIRECTORY_SEPARATOR);
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';
defined('TENCENT_WORDPRESS_PLUGINS_SHOW_NAME')||define( 'TENCENT_WORDPRESS_PLUGINS_SHOW_NAME', 'tencentcloud-plugin-captcha');
defined('TENCENT_WORDPRESS_CAPTCHA_DIR')||define( 'TENCENT_WORDPRESS_CAPTCHA_DIR', plugin_dir_path( __FILE__ ) );
require_once TENCENT_WORDPRESS_CAPTCHA_DIR . 'TencentCloudCaptchaActions.php';
//删除公共配置中本插件的信息
TencentWordpressPluginsSettingActions::deleteTencentWordpressPlugin(TENCENT_WORDPRESS_PLUGINS_SHOW_NAME);
//获取到插件的配置信息，如果存在则删除
if (get_option('tencent_wordpress_captcha_options')) {
    delete_option( 'tencent_wordpress_captcha_options' );
}
$staticData = TencentCloudCaptchaActions::getTencentCloudWordPressStaticData('deactivate');
TencentWordpressPluginsSettingActions::SendUserExperienceInfo($staticData);
