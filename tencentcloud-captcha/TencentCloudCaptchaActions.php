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
if (!is_file(TENCENT_WORDPRESS_CAPTCHA_DIR . 'vendor/autoload.php')) {
    CaptchaDebugLog::writeDebugLog('error', 'msg : ' . '缺少依赖文件，请先执行composer install', __FILE__, __LINE__);
    wp_die('缺少依赖文件，请先执行composer install', '缺少依赖文件', array('back_link' => true));
}
require_once 'vendor/autoload.php';
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Captcha\V20190722\CaptchaClient;
use TencentCloud\Captcha\V20190722\Models\DescribeCaptchaResultRequest;


class TencentCloudCaptchaActions
{
    const TENCENT_WORDPRESS_CAPTCHA_OPTIONS = 'tencent_wordpress_captcha_options';
    const TENCENT_WORDPRESS_CAPTCHA_LOGIN_NEED_CODE = 'login_need_code';
    const TENCENT_WORDPRESS_CAPTCHA_DEBUG_NEED_CODE = 'debug_need_code';
    const TENCENT_WORDPRESS_CAPTCHA_CODE_FREE = 'code_free';
    const TENCENT_WORDPRESS_CAPTCHA_APP_ID = 'captcha_app_id';
    const TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID = 'captcha_register_app_id';
    const TENCENT_WORDPRESS_CAPTCHA_REGISTER_NEED_CODE = 'register_need_code';
    const TENCENT_WORDPRESS_CAPTCHA_COMMENT_NEED_CODE = 'comment_need_code';
    const TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID = 'captcha_comment_app_id';
    const TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_NEED_CODE = 'lostpassword_need_code';
    const TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID = 'captcha_lostpassword_app_id';
    const TENCENT_WORDPRESS_CAPTCHA_SECRET_ID = 'secret_id';
    const TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY = 'secret_key';
    const TENCENT_WORDPRESS_CAPTCHA_APP_KEY = 'captcha_app_key';
    const TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY = 'captcha_register_app_key';
    const TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY = 'captcha_comment_app_key';
    const TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY = 'captcha_lostpassword_app_key';
    const TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM = 'secret_custom';
    const TENCENT_WORDPRESS_CAPTCHA_PLUGIN_TYPE = 'captcha';

    public static function tencentCaptchaSetingNotice()
    {
        if (isset($GLOBALS['_REQUEST']) && isset($GLOBALS['_REQUEST']['page'])
            && $GLOBALS['_REQUEST']['page'] === 'tencent_wordpress_plugin_captcha') {
            $captchaOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
            if (isset($captchaOptions['activation']) && $captchaOptions['activation'] === true) {
                $chosen = '腾讯云验证码插件启用生效中';
            } else {
                $chosen = '腾讯云验证码插件启用中';
            }
            echo '<div id="cos_message" class="updated notice is-dismissible" style="margin-bottom: 1%;margin-left:0%;">
                     <p>' . $chosen . '</p>
                 </div>';
        }
    }

    /**
     * 登录表单增加验证码
     */
    public function tencentCaptchaLoginForm()
    {

        $captchaOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        $loginNeedCode = $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOGIN_NEED_CODE];
        if ($loginNeedCode == '2') {
            $captchaAppId = '';
            $codeFree = $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
            if ($codeFree == '1') {
                $captchaAppId = $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            } else {
                $captchaAppId = sanitize_text_field($captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID])
                    ?: $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            }
            echo '<p>
            <label for="codeVerifyButton">我不是人机</label>
            <input type="button" name="codeVerifyButton" id="codeVerifyButton" data-appid="' . $captchaAppId . '" class="button" value="验证" style="width: 100%;margin-bottom: 16px;height:40px;">
             <input type="button" id="codePassButton" disabled="disabled" style="background-color: green;color: white;width: 100%;margin-bottom: 16px;height:40px" value="已通过验证"  >
            <input type="hidden" id="codeVerifyTicket" name="codeVerifyTicket" value="">
            <input type="hidden" id="codeVerifyRandstr" name="codeVerifyRandstr" value="">
            </p>';
        }

    }

    /**
     * 注册表单增加验证码
     */
    public function tencentCaptchaRegisterForm()
    {

        $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);

        $registerNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_NEED_CODE];
        if ($registerNeedCode == '2') {
            $codAppId = '';
            $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
            if ($codeFree == '1') {
                $codAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            } else {
                $codAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            }
            echo '<p>
            <label for="codeVerifyButton">我不是人机</label>
            <input type="button" name="codeVerifyButton" id="codeVerifyButton" data-appid="' . $codAppId . '" class="button" value="验证" style="width: 100%;margin-bottom: 16px;height:40px;">
            <input type="button" id="codePassButton" disabled="disabled" style="background-color: green;color: white;width: 100%;margin-bottom: 16px;height:40px" value="已通过验证"  >
            <input type="hidden" id="codeVerifyTicket" name="codeVerifyTicket" value="">
            <input type="hidden" id="codeVerifyRandstr" name="codeVerifyRandstr" value="">
            </p>';
        }

    }


    /**
     * 评论表单增加验证码
     * @param $submitButton 评论按钮HTML
     * @return string
     */
    public function tencentCaptchaCommentForm($submitButton)
    {
        $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        $commentNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_NEED_CODE];
        if ($commentNeedCode == '2') {
            $codAppId = '';
            $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
            if ($codeFree == '1') {
                $codAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            } else {
                $codAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            }
            $submitButton = '<p>
            <input type="button" name="codeVerifyCommentButton" id="codeVerifyButton" data-appid="' . $codAppId . '" class="button" value="人机验证" >
            <input type="button" id="codePassButton" disabled="disabled" style="background-color: green;color: white" value="已通过验证"  >
            <input type="hidden" id="codeVerifyTicket" name="codeVerifyTicket" value="">
            <input type="hidden" id="codeVerifyRandstr" name="codeVerifyRandstr" value=""></p>' . $submitButton;
            return $submitButton;
        } else {
            return $submitButton;
        }
    }

    /**
     * 找回密码增加验证码字段
     */
    public function tencentCaptchaLostpasswordForm()
    {
        $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        $lostpasswordNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_NEED_CODE];
        if ($lostpasswordNeedCode == '2') {
            $codAppId = '';
            $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
            if ($codeFree == '1') {
                $codAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            } else {
                $codAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
            }
            echo '<p>
            <label for="codeVerifyButton">我不是人机</label>
            <input type="button" name="codeVerifyButton" id="codeVerifyButton" data-appid="' . $codAppId . '" class="button" value="验证" style="width: 100%;margin-bottom: 16px;height:40px;">
            <input type="button" id="codePassButton" disabled="disabled" style="background-color: green;color: white;width: 100%;margin-bottom: 16px;height:40px" value="已通过验证"  >
            <input type="hidden" id="codeVerifyTicket" name="codeVerifyTicket" value="">
            <input type="hidden" id="codeVerifyRandstr" name="codeVerifyRandstr" value="">
            </p>';
        }
    }

    /**
     * 插件菜单设置
     */
    public function tencentCaptchaPluginSettingPage()
    {
        TencentWordpressPluginsSettingActions::AddTencentWordpressCommonSettingPage();
        $pagehook = add_submenu_page('TencentWordpressPluginsCommonSettingPage', '验证码', '验证码', 'manage_options', 'tencent_wordpress_plugin_captcha', array('TencentCloudCaptchaActions', 'tencentCaptchaSettingPage'));
        add_action('admin_print_styles-' . $pagehook, array(new TencentCloudCaptchaActions(), 'tencentCaptchaLoadCssForPage'));
    }

    /**
     * 插件配置信息操作页面
     */
    public static function tencentCaptchaSettingPage()
    {
        include TENCENT_WORDPRESS_CAPTCHA_DIR . 'tencentcloud-captcha-setting-page.php';
    }

    /**
     * 添加设置按钮
     * @param $links
     * @param $file
     * @return mixed
     */
    public function tencentCaptchaPluginSettingPageLinkButton($links, $file)
    {
        if ($file == plugin_basename(TENCENT_WORDPRESS_CAPTCHA_DIR . 'tencentcloud-captcha.php')) {
            $links[] = '<a href="admin.php?page=tencent_wordpress_plugin_captcha">设置</a>';
        }

        return $links;
    }

    /**
     * 在文章页面加载JS脚本
     */
    public function tencentCaptchaLoadScriptForPage()
    {
        if (is_single() || is_paged()) {
            wp_register_script('codeVerify_front_user_script', TENCENT_WORDPRESS_CAPTCHA_JS_DIR . 'tencent_cloud_captcha_user.js', array('jquery'), '2.1', true);
            wp_enqueue_script('codeVerify_front_user_script');
            wp_register_script('TCaptcha', 'https://ssl.captcha.qq.com/TCaptcha.js', array('jquery'), '2.1', true);
            wp_enqueue_script('TCaptcha');
        }
    }

    public function tencentCaptchaLoadCssForPage()
    {
        wp_enqueue_style('codeVerify_admin_css', TENCENT_WORDPRESS_CAPTCHA_CSS_DIR . 'bootstrap.min.css');
    }

    /**
     * 加载js脚本
     */
    public function tencentCaptchaLoadMyScriptEnqueue()
    {
        wp_register_script('codeVerify_front_user_script', TENCENT_WORDPRESS_CAPTCHA_JS_DIR . 'tencent_cloud_captcha_user.js', array('jquery'), '2.1', true);
        wp_enqueue_script('codeVerify_front_user_script');
        wp_register_script('codeVerify_back_admin_script', TENCENT_WORDPRESS_CAPTCHA_JS_DIR . 'tencent_cloud_captcha_admin.js', array('jquery'), '2.1', true);
        wp_enqueue_script('codeVerify_back_admin_script');
        wp_register_script('TCaptcha', 'https://ssl.captcha.qq.com/TCaptcha.js', array('jquery'), '2.1', true);
        wp_enqueue_script('TCaptcha');

    }


    /**
     * @param $secretID 腾讯云密钥ID
     * @param $secretKey 腾讯云密钥Key
     * @param $codeAppId 验证码通用APPID
     * @param $codeSecretKey 验证码通用APPKey
     * @param $codeFree 是否自定义业务场景
     * @param $registerAppId 注册场景应用APPID
     * @param $registerAppKey 注册场景应用APPKey
     * @param $commentAppId 评论场景应用APPID
     * @param $commentAppKey 评论场景应用APPKey
     * @param $secretCustom 自定义密钥
     * @return bool|string
     */
    public static function tencentCaptchaCheckMustParams($secretID, $secretKey, $codeAppId, $codeSecretKey,
                                                         $codeFree, $registerAppId, $registerAppKey,
                                                         $commentAppId, $commentAppKey, $lostpasswordAppId,
                                                         $lostpasswordAppKey, $secretCustom)
    {
        if ($secretCustom == '2') {
            if (empty($secretID)) {
                return 'Secret Id未填写.';
            }
            if (empty($secretKey)) {
                return 'Secret key未填写.';
            }
        }
        if (empty($codeAppId)) {
            return 'Captcha App Id未填写.';
        }
        if (empty($codeSecretKey)) {
            return 'Captcha App Secret Key未填写.';
        }
        if ($codeFree == '2') {
            if ((empty($registerAppId) && !empty($registerAppKey)) || (!empty($registerAppId) && empty($registerAppKey))) {
                return '注册场景应用APP ID和应用Secret Key需要同时填写.';
            }
            if ((empty($commentAppId) && !empty($commentAppKey)) || (!empty($commentAppId) && empty($commentAppKey))) {
                return '评论场景应用APP ID和应用Secret Key需要同时填写.';
            }

            if ((empty($lostpasswordAppId) && !empty($lostpasswordAppKey)) || (!empty($lostpasswordAppId) && empty($lostpasswordAppKey))) {
                return '找回密码场景应用APP ID和应用Secret Key需要同时填写.';
            }
        }
        return true;
    }

    /**
     * 保存插件配置
     */
    public function tencentCaptchaUpdateCaptchaSettings()
    {
        if (!current_user_can('manage_options')) {
            CaptchaDebugLog::writeDebugLog('error', 'msg : 当前用户无权限!', __FILE__, __LINE__);
            wp_send_json_error(array('msg' => '当前用户无权限.'));
        }
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID] = sanitize_text_field($_POST['secret_id']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY] = sanitize_text_field($_POST['secret_key']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID] = sanitize_text_field($_POST['codeVerify_option_codeAppId']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY] = sanitize_text_field($_POST['codeVerify_option_codeSecretKey']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_NEED_CODE] = sanitize_text_field($_POST['registerNeedCode']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_NEED_CODE] = sanitize_text_field($_POST['commentNeedCode']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOGIN_NEED_CODE] = sanitize_text_field($_POST['loginNeedCode']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_DEBUG_NEED_CODE] = sanitize_text_field($_POST['debugNeedCode']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_NEED_CODE] = sanitize_text_field($_POST['lostpasswordNeedCode']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE] = sanitize_text_field($_POST['codeFree']);
        $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM] = sanitize_text_field($_POST['secretCustom']);
        $CodeVerifySettings['activation'] = true;
        if ($CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE] == 2) {
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID] = sanitize_text_field($_POST['registerCodeAppId']);
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY] = sanitize_text_field($_POST['registerCodeKey']);
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID] = sanitize_text_field($_POST['commentCodeAppId']);
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY] = sanitize_text_field($_POST['commentCodeKey']);
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID] = sanitize_text_field($_POST['lostpasswordCodeAppId']);
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY] = sanitize_text_field($_POST['lostpasswordCodeKey']);
        } else {
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY] = '';
        }

        if ($CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM] == '1') {
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID] = '';
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY] = '';
        }


        $checkResult = self::tencentCaptchaCheckMustParams($CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID],
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID],
            $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY]
            , $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY], $CodeVerifySettings[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM]);
        if ($checkResult !== true) {
            CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $checkResult, __FILE__, __LINE__);
            wp_send_json_error(array('msg' => $checkResult));
        }
        update_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS, $CodeVerifySettings, true);
        //发送用户体验数据
        $staticData = self::getTencentCloudWordPressStaticData('save_config');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
        wp_send_json_success(array('msg' => '保存成功'));

    }

    /**
     * 登录时验证
     * @param $users 用户
     * @return WP_Error 验证错误
     */
    public function tencentCapthcaLoginCodeVerify($users)
    {
        if (!empty($_POST)) {
            $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
            $loginNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOGIN_NEED_CODE];
            if ($loginNeedCode == '2') {
                $ticket = sanitize_text_field($_POST['codeVerifyTicket']);
                $randStr = sanitize_text_field($_POST['codeVerifyRandstr']);
                if (empty($ticket) || empty($randStr)) {
                    CaptchaDebugLog::writeDebugLog('error', 'msg : ticket or randStr is null!', __FILE__, __LINE__);
                    return new WP_Error(
                        'invalid_CodeVerify',
                        __('未通过人机验证.')
                    );
                }
                $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
                $codeAppId = '';
                $codeAppKey = '';
                if ($codeFree == '2') {
                    $codeAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                } else {
                    $codeAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                }
                $verifyCode = self::verifyCodeReal($ticket, $randStr, $codeAppId, $codeAppKey);
                if (!$verifyCode || $verifyCode['CaptchaCode'] != 1) {
                    $errormessage = '未通过人机验证.';
                    if (isset($verifyCode['errorMessage']) && !empty($verifyCode['errorMessage'])) {
                        $errormessage = $errormessage . $verifyCode['errorMessage'];
                    }
                    CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $errormessage, __FILE__, __LINE__);
                    return new WP_Error(
                        'invalid_CodeVerify',
                        __('未通过人机验证.')
                    );;
                }
                return $users;
            } else {
                return $users;
            }
        }
        return $users;
    }


    /**
     * 注册时验证码验证
     * @param $login 用户名
     * @param $email 用户邮箱
     * @param $errors 异常
     * @return mixed
     */
    public function tencentCaptchaRegisterCodeVerify($login, $email, $errors)
    {
        if (!empty($_POST)) {
            $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
            $registerNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_NEED_CODE];
            if ($registerNeedCode == '2') {
                $ticket = sanitize_text_field($_POST['codeVerifyTicket']);
                $randStr = sanitize_text_field($_POST['codeVerifyRandstr']);
                if (empty($ticket) || empty($randStr)) {
                    CaptchaDebugLog::writeDebugLog('error', 'msg : ticket or randStr is null!', __FILE__, __LINE__);
                    $errors->add('未通过人机验证.', __('未通过人机验证.', 'wpcaptchadomain'));
                    return $errors;
                }

                $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
                $codeAppId = '';
                $codeAppKey = '';
                if ($codeFree == '2') {
                    $codeAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                } else {
                    $codeAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                }
                $verifyCode = self::verifyCodeReal($ticket, $randStr, $codeAppId, $codeAppKey);
                if (!$verifyCode || $verifyCode['CaptchaCode'] != 1) {
                    $errormessage = '未通过人机验证.';
                    if (isset($verifyCode['errorMessage']) && !empty($verifyCode['errorMessage'])) {
                        $errormessage = $errormessage . $verifyCode['errorMessage'];
                    }
                    CaptchaDebugLog::writeDebugLog('error', 'msg : 未通过人机验证.' . $errormessage, __FILE__, __LINE__);
                    $errors->add('未通过人机验证.', __($errormessage, 'wpcaptchadomain'));
                    return $errors;
                }
            }
        }

    }

    /**
     * 忘记密码时验证码验证
     *
     */
    public function tencentCaptchaLostpasswordCodeVerify()
    {
        if (!empty($_POST)) {
            $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
            $lostpasswordNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_NEED_CODE];
            if ($lostpasswordNeedCode == '2') {
                $ticket = sanitize_text_field($_POST['codeVerifyTicket']);
                $randStr = sanitize_text_field($_POST['codeVerifyRandstr']);
                if (empty($ticket) || empty($randStr)) {
                    CaptchaDebugLog::writeDebugLog('error', 'msg : ticket or randStr is null!', __FILE__, __LINE__);
                    $error = new WP_Error(
                        'invalid_CodeVerify',
                        __('未通过人机验证.')
                    );
                    wp_die($error, '未通过人机验证.', array('back_link' => true));
                }
                $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
                $codeAppId = '';
                $codeAppKey = '';
                if ($codeFree == '2') {
                    $codeAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                } else {
                    $codeAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                    $codeAppKey = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
                }
                $verifyCode = self::verifyCodeReal($ticket, $randStr, $codeAppId, $codeAppKey);
                if (!$verifyCode || $verifyCode['CaptchaCode'] != 1) {
                    $errormessage = '未通过人机验证.';
                    if (isset($verifyCode['errorMessage']) && !empty($verifyCode['errorMessage'])) {
                        $errormessage = $errormessage . $verifyCode['errorMessage'];
                    }
                    $error = new WP_Error(
                        'invalid_CodeVerify',
                        __('未通过人机验证.')
                    );
                    CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $errormessage, __FILE__, __LINE__);
                    wp_die($error, '未通过人机验证.', array('back_link' => true));
                }
            }
        }

    }

    /**
     * 评论时验证码验证
     * @param $comment 评论信息
     * @return mixed
     */
    public function tencentCaptchaCommentCodeVerify($comment)
    {
        $user = wp_get_current_user();
        // 管理员后台回复评论时无需验证
        $allowed_roles = array('editor', 'administrator', 'author');
        if (array_intersect($allowed_roles, $user->roles)) {
            return $comment;
        }
        $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        $commentNeedCode = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_NEED_CODE];
        if ($commentNeedCode == '2') {
            $ticket = sanitize_text_field($_POST['codeVerifyTicket']);
            $randStr = sanitize_text_field($_POST['codeVerifyRandstr']);
            if (empty($ticket) || empty($randStr)) {
                $error = new WP_Error(
                    'need_authenticated_code',
                    __('请先进行人机验证.')
                );
                CaptchaDebugLog::writeDebugLog('error', 'msg : ticket or randStr is null!', __FILE__, __LINE__);
                wp_die($error, '验证码不能为空', array('back_link' => true));

            }
            $codeFree = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE];
            $codeAppId = '';
            $codeAppKey = '';
            if ($codeFree == '2') {
                $codeAppId = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                $codeAppKey = sanitize_text_field($CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY]) ?: $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
            } else {
                $codeAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
                $codeAppKey = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
            }
            $verifyCode = self::verifyCodeReal($ticket, $randStr, $codeAppId, $codeAppKey);
            if (!$verifyCode || $verifyCode['CaptchaCode'] != 1) {
                $errormessage = '验证码验证失败.';
                if (isset($verifyCode['errorMessage']) && !empty($verifyCode['errorMessage'])) {
                    $errormessage = $errormessage . $verifyCode['errorMessage'];
                }
                $error = new WP_Error(
                    'authenticated_fail',
                    __('验证码验证失败.')
                );
                CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $errormessage, __FILE__, __LINE__);
                wp_die($error, '验证码验证失败,请重新验证', array('back_link' => true));
            }
            return $comment;
        } else {
            return $comment;
        }
    }

    /**
     * 验证码服务端验证
     * @param $secretID 腾讯云密钥ID
     * @param $secretKey 腾讯云密钥Key
     * @param $ticket 用户验证票据
     * @param $randStr 用户验证时随机字符串
     * @param $codeAppId 验证码应用ID
     * @param $codeSecretKey 验证码应用蜜月
     * @return array|mixed
     */
    public static function verifyCodeReal($ticket, $randStr, $codeAppId, $codeSecretKey)
    {
        try {
            $secretID = self::getSecretID();
            $secretKey = self::getSecretKey();
            $remoteIp = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
            $cred = new Credential($secretID, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("captcha.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CaptchaClient($cred, "", $clientProfile);
            $req = new DescribeCaptchaResultRequest();
            $params = array('CaptchaType' => 9, 'Ticket' => $ticket, 'Randstr' => $randStr, 'CaptchaAppId' => intval($codeAppId), 'AppSecretKey' => $codeSecretKey, 'UserIp' => $remoteIp);
            $req->fromJsonString(json_encode($params));
            $resp = $client->DescribeCaptchaResult($req);
            return json_decode($resp->toJsonString(), JSON_OBJECT_AS_ARRAY);
        } catch (TencentCloudSDKException $e) {
            CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * 获取SecrtId
     * @return mixed
     */
    private static function getSecretID()
    {
        $tecentCaptchaOptinos = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        if (sanitize_text_field($tecentCaptchaOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM]) == '2') {
            return $tecentCaptchaOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID];
        } else {
            $commonOptinos = get_option('tencent_wordpress_common_options');
            if (isset($commonOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID])){
                return $commonOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID];
            }
            CaptchaDebugLog::writeDebugLog('error', 'msg : SecretID is null', __FILE__, __LINE__);
            return '';
        }

    }

    /**
     * 获取SecrtKey
     * @return mixed
     */
    private static function getSecretKey()
    {
        $tecentCaptchaOptinos = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        if (sanitize_text_field($tecentCaptchaOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM]) == '2') {
            return $tecentCaptchaOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY];
        } else {
            $commonOptinos = get_option('tencent_wordpress_common_options');
            if (isset($commonOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY])){
                return $commonOptinos[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY];
            }
            CaptchaDebugLog::writeDebugLog('error', 'msg : SecretKey is null', __FILE__, __LINE__);
            return '';
        }

    }

    /**
     * 开启插件
     */
    public static function tencentCaptchaActivatePlugin()
    {
        $initOptions = array(
            'activation' => false,
            self::TENCENT_WORDPRESS_CAPTCHA_SECRET_ID => "",
            self::TENCENT_WORDPRESS_CAPTCHA_SECRET_KEY => "",
            self::TENCENT_WORDPRESS_CAPTCHA_APP_ID => '',
            self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY => '',
            self::TENCENT_WORDPRESS_CAPTCHA_LOGIN_NEED_CODE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_NEED_CODE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_DEBUG_NEED_CODE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_NEED_CODE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_NEED_CODE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID => '',
            self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_KEY => '',
            self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID => '',
            self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_KEY => '',
            self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID => '',
            self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_KEY => '',
            self::TENCENT_WORDPRESS_CAPTCHA_CODE_FREE => '',
            self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM => ''
        );
        $captchaOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        if (empty($captchaOptions)) {
            add_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS, $initOptions);
        } else {
            $captchaOptions = array_merge($initOptions, $captchaOptions);
            update_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS, $captchaOptions);
        }

        $plugin = array(
            'plugin_name' => TENCENT_WORDPRESS_CAPTCHA_SHOW_NAME,
            'nick_name' => '腾讯云验证码（CAPTCHA）插件',
            'plugin_dir' => 'tencentcloud-captcha/tencentcloud-captcha.php',
            'href' => 'admin.php?page=tencent_wordpress_plugin_captcha',
            'activation' => 'true',
            'status' => 'true',
            'download_url' => ''
        );
        TencentWordpressPluginsSettingActions::prepareTencentWordressPluginsDB($plugin);

        // 第一次开启插件则生成一个全站唯一的站点id，保存在公共的option中
        TencentWordpressPluginsSettingActions::setWordPressSiteID();
        //发送用户体验数据
        $staticData = self::getTencentCloudWordPressStaticData('activate', '', '', '', '');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
    }

    /**
     * 禁止插件
     */
    public static function tencentCaptchaDeactivePlugin()
    {
        $captchaOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        if (!empty($captchaOptions) && isset($captchaOptions['activation'])) {
            $captchaOptions['activation'] = false;
            update_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS, $captchaOptions);
        }
        TencentWordpressPluginsSettingActions::disableTencentWordpressPlugin(TENCENT_WORDPRESS_CAPTCHA_SHOW_NAME);
        $staticData = self::getTencentCloudWordPressStaticData('deactivate', '', '', '', '');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($staticData);
    }

    /**
     * 插件初始化
     */
    public static function tencentCaptchaInit()
    {
        if (class_exists('TencentWordpressPluginsSettingActions')) {
            TencentWordpressPluginsSettingActions::init();
        }

    }

    public static function getTencentCloudWordPressStaticData($action)
    {
        $siteId = TencentWordpressPluginsSettingActions::getWordPressSiteID();
        $siteUrl = TencentWordpressPluginsSettingActions::getWordPressSiteUrl();
        $siteApp = TencentWordpressPluginsSettingActions::getWordPressSiteApp();
        $staticData['action'] = $action;
        $staticData['plugin_type'] = 'captcha';
        $staticData['data'] = array(
            'site_id' => $siteId,
            'site_url' => $siteUrl,
            'site_app' => $siteApp
        );

        $common_option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        $captchaOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        if ($captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM] == '2' && isset($captchaOptions['secret_id']) && isset($captchaOptions['secret_key'])) {
            $secretId = $captchaOptions['secret_id'];
            $secretKey = $captchaOptions['secret_key'];
        } elseif ($common_option['site_report_on'] === true && isset($common_option['secret_id']) && isset($common_option['secret_key'])) {
            $secretId = $common_option['secret_id'];
            $secretKey = $common_option['secret_key'];
        }
        $staticData['data']['uin'] = TencentWordpressPluginsSettingActions::getUserUinBySecret($secretId, $secretKey);

        $staticData['data']['cust_sec_on'] = $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_SECRET_CUSTOM] == '2' ? 1 : 2;
        $others = array(
            'captcha_appid' => $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID],
            'captcha_appid_login' => $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_REGISTER_APP_ID],
            'captcha_appid_comment' => $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_COMMENT_APP_ID],
            'captcha_appid_pwd' => $captchaOptions[self::TENCENT_WORDPRESS_CAPTCHA_LOSTPASSWORD_APP_ID],
        );
        $staticData['data']['others'] = json_encode($others);
        return $staticData;
    }

    /**
     *  配置页面测试验证码功能，先保存配置再测试
     * @throws Exception
     */
    public static function tencentCaptchaCodeVerifyCheck()
    {
        $CodeVerifyOptions = get_option(self::TENCENT_WORDPRESS_CAPTCHA_OPTIONS);
        $codeAppId = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_ID];
        $codeAppKey = $CodeVerifyOptions[self::TENCENT_WORDPRESS_CAPTCHA_APP_KEY];
        $ticket = sanitize_text_field($_POST['codeVerifyTicketCheck']);
        $randStr = sanitize_text_field($_POST['codeVerifyRandstrCheck']);

        if (empty($ticket) || empty($randStr) || empty($codeAppId) || empty($codeAppKey)) {
            $errormessage = 'ticket(' . $ticket . ') or randStr(' . $randStr .
                ') or AppId(' . $codeAppId . ') or AppKey(' . $codeAppKey . ') is null';

            CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $errormessage, __FILE__, __LINE__);
            wp_send_json_error(array('msg' => $errormessage));
        }

        try {
            $verifyCode = self::verifyCodeReal($ticket, $randStr, $codeAppId, $codeAppKey);
            if (!$verifyCode || isset($verifyCode['CaptchaCode']) && $verifyCode['CaptchaCode'] != 1) {
                $errormessage = '验证码验证失败.';
                if (!empty($verifyCode['CaptchaMsg'])) {
                    $errormessage = $errormessage . $verifyCode['CaptchaMsg'];
                }
                CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $errormessage, __FILE__, __LINE__);
                wp_send_json_error(array('msg' => $errormessage));
            }
            wp_send_json_success(array('msg' => '验证成功'));
        } catch (\Exception $e) {
            CaptchaDebugLog::writeDebugLog('error', 'msg : ' . $e->getMessage(), __FILE__, __LINE__);
            wp_send_json_error(array('msg' => $e->getMessage()));
        }
    }

    public static function tencentCaptchaDeleteLogfile()
    {
        if (!file_exists(TENCENT_WORDPRESS_CAPTCHA_LOGS)) {
            wp_send_json_success();
        }

        self::removeDir(TENCENT_WORDPRESS_CAPTCHA_LOGS);
        wp_send_json_success();
    }

    /**
     *  迭代删除目录及目录中的自文件
     *
     * @param string $dir
     * @access public
     * @return bool
     * @throws Exception
     */
    public static function removeDir($dir)
    {
        if (empty($dir)) {
            return true;
        }

        $dir = realpath($dir) . '/';
        if ($dir == '/') {
            CosDebugLog::writeDebugLog('notice', 'msg : can not remove root dir', __FILE__, __LINE__);
            return false;
        }

        if (!is_writable($dir)) {
            CosDebugLog::writeDebugLog('notice', 'msg : no delete permission ', __FILE__, __LINE__);
            return false;
        }

        if (!is_dir($dir)) {
            return true;
        }

        $entries = scandir($dir);
        foreach ($entries as $entry) {
            if ($entry == '.' or $entry == '..')
                continue;

            $fullEntry = $dir . $entry;
            if (is_file($fullEntry)) {
                @unlink($fullEntry);
            } else {
                return self::removeDir($fullEntry);
            }
        }
        if (!@rmdir($dir)) {
            CosDebugLog::writeDebugLog('notice', 'msg : remove log dir failed', __FILE__, __LINE__);
            return false;
        }
        return true;
    }
}
