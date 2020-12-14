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
if (!current_user_can('manage_options')) {
    wp_die('Insufficient privileges!');
}
$ajaxUrl = admin_url('admin-ajax.php');
$codeVerifySettings = get_option('tencent_wordpress_captcha_options');
$secretCustom = $codeVerifySettings['secret_custom']?:'1';
$secretID = '';
$secretKey = '';
if ($secretCustom == '1'){
    $commonOptions = get_option('tencent_wordpress_common_options');
    $secretID = esc_attr($commonOptions['secret_id']) ?: '';
    $secretKey = esc_attr($commonOptions['secret_key']) ?: '';
}else{
    $secretID = esc_attr($codeVerifySettings['secret_id']) ?: '';
    $secretKey = esc_attr($codeVerifySettings['secret_key']) ?: '';
}

$codeAppId = esc_attr($codeVerifySettings['captcha_app_id']) ?: '';
$codeSecretKey = esc_attr($codeVerifySettings['captcha_app_key']) ?: '';
$commentCode = esc_attr($codeVerifySettings['comment_need_code'])?:'2';
$registerCode = esc_attr($codeVerifySettings['register_need_code'])?:'2';
$loginCode = esc_attr($codeVerifySettings['login_need_code'])?:'2';
$lostpasswordCode = esc_attr($codeVerifySettings['lostpassword_need_code'])?:'2';

$codeFree = esc_attr($codeVerifySettings['code_free'])?:'1';
$registerCodeAPPID = esc_attr($codeVerifySettings['captcha_register_app_id'])?:'';
$registerCodeKey = esc_attr($codeVerifySettings['captcha_register_app_key'])?:'';
$commentCodeAPPID = esc_attr($codeVerifySettings['captcha_comment_app_id'])?:'';
$commentCodeKey = esc_attr($codeVerifySettings['captcha_comment_app_key'])?:'';
$lostpasswordCodeAPPID = esc_attr($codeVerifySettings['captcha_lostpassword_app_id'])?:'';
$lostpasswordCodeKey = esc_attr($codeVerifySettings['captcha_lostpassword_app_key'])?:'';
   ?>

    <style type="text/css">
        .dashicons {
            vertical-align: middle;
            position: relative;
            right: 30px;
        }
    </style>
    <div id="message" class="updated notice is-dismissible" style="margin-bottom: 1%;margin-left:0%;"><p>腾讯云验证码插件启用生效中。</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button></div>
    <div class="row">
    <div class="col-lg-12">
        <div class="page-header ">
            <h1 id="forms">腾讯云验证码（CAPTCHA）插件</h1>
        </div>
        <p>在登录、注册、找回密码、评论等场景下，增加人机校验</p>
    </div>
    </div>

    <div class="wrap">
        <div class="alert alert-dismissible alert-success" style="display: none;">
            <button type="button" id="codeVerify_close-ajax-return-msg" class="close" data-dismiss="alert">&times;</button>
            <div id="codeVerify_show-ajax-return-msg">操作成功.</div>
        </div>
        <div id="post-body">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="javascript:void(0);" id="sub-tab-settings">插件配置</a>
                </li>
            </ul>
            <div class="txc-container">
                <div class="txc-grid">
                    <form method="post" id="tencnetcloud-codeVerify-setting-form" action=""
                          data-ajax-url="<?php echo $ajaxUrl ?>">
                        <div id="group-settings" class="group" style="display: block;">
                            <div class="postbox">
                                <div class="inside">
                                    <table class="form-table">
                                        <tbody>
                                        <tr>
                                            <th scope="row"><label><h5>自定义密钥</h5></label></th>
                                            <td>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="codeVerify-option-secret-custom" value="<?php echo $secretCustom; ?>" <?php if ($secretCustom === '2') {echo 'checked';} ?>>
                                            <label class="custom-control-label" for="codeVerify-option-secret-custom" disabled="disabled">为该插件配置于不同于全局腾讯云密钥的单独密钥</label>
                                        </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="codeVerify-option-secret-id"><h5>SecretId</h5></label></th>
                                            <td><input type="password" autocomplete="off" value="<?php echo $secretID; ?>"
                                                       id="codeVerify-option-secret-id" size="50" <?php if ($secretCustom == '1') {echo 'disabled ="disabled"';} ?>>
                                                <span id="codeVerify_secret_id_type_exchange" class="dashicons dashicons-hidden"></span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="codeVerify-option-secret-key"><h5>SecretKey</h5></label></th>
                                            <td><input type="password" autocomplete="off"
                                                       value="<?php echo $secretKey; ?>"
                                                       id="codeVerify-option-secret-key" size="50" <?php if ($secretCustom == '1') {echo 'disabled ="disabled"';} ?>>
                                                <span id="codeVerify_secret_key_type_exchange" class="dashicons dashicons-hidden"></span>
                                                <p class="description">访问 <a href="https://console.qcloud.com/cam/capi" target="_blank">密钥管理</a>获取
                                                    SecretId和SecretKey或通过"新建密钥"创建密钥串</p>
                                            </td>
                                        </tr>


                                        <tr>
                                            <th scope="row"><label for="codeVerify-option_codeAppId"><h5>CaptchaAppId</h5></label></th>
                                            <td><input type="text" name="codeVerify-option_codeAppId" autocomplete="off" value="<?php echo $codeAppId; ?>"
                                                       id="codeVerify-option_codeAppId" size="30">
                                                <p class="description">验证码通用的AppId</p></td>
                                        </tr>
                                        <tr>
                                            <th scope="row"><label for="codeVerify-option_codeSecretKey"><h5>CaptchaAppSecretKey</h5></label></th>
                                            <td><input type="password" name="codeVerify-option_codeSecretKey" autocomplete="off" value="<?php echo $codeSecretKey; ?>"
                                                       id="codeVerify-option_codeSecretKey" size="30">
                                                <p class="description">访问<a href="https://console.cloud.tencent.com/captcha" target="_blank">CaptchaAppId列表</a>获取
                                                    CaptchaAppId和CaptchaAppSecretKey或通过"新建验证"创建CaptchaAppId
                                                </p>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th scope="row">
                                                <label for="codeVerify-option-sign">验证码启用场景</label></th>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="codeVerify-option-login" <?php if ($loginCode === '2') {echo 'checked';} ?>  >
                                                    <label class="custom-control-label" for="codeVerify-option-login">是否开启登录验证</label>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="codeVerify-option-register" <?php if ($registerCode === '2') {echo 'checked';} ?> >
                                                    <label class="custom-control-label" for="codeVerify-option-register">是否开启注册验证</label>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="codeVerify-option-lostpassword" <?php if ($lostpasswordCode === '2') {echo 'checked';} ?>  >
                                                    <label class="custom-control-label" for="codeVerify-option-lostpassword">是否开启找回密码验证</label>
                                                </div>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="codeVerify-option-comment" <?php if ($commentCode === '2') {echo 'checked';} ?>  >
                                                    <label class="custom-control-label" for="codeVerify-option-comment">是否开启评论验证</label>
                                                </div>
                                        </tr>

                                        <tr>
                                            <th scope="row">
                                                <label for="codeVerify-option-codeFree">自定义业务场景</label></th>
                                            <td>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="codeVerify-option-codeFree" value="<?php echo $codeFree?>" <?php if ($codeFree === '2') {echo 'checked';} ?> >
                                                    <label class="custom-control-label" for="codeVerify-option-codeFree">是否自定义业务场景</label>
                                                </div>

                                            </td>

                                        </tr>
                                        </tbody>
                                    </table>
                                    <table id="codeVerifyFree" class="table table-hover hidden">
                                        <thead>
                                            <tr>
                                                <th>业务场景</th>
                                                <th>应用AppId</th>
                                                <th>应用AppSecretKey</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <tr>
                                            <td>登录/注册场景</td>
                                            <td>
                                                <input type="text" name="codeVerify_option_registerAPPID" autocomplete="off" placeholder="为空则使用通用AppId" value="<?php echo $registerCodeAPPID; ?>"
                                                       id="codeVerify_option_registerAPPID" size="30">
                                            </td>
                                            <td>
                                                <input type="password" name="codeVerify_option_registerAPPKey" autocomplete="off" placeholder="为空则使用通用AppSecretKey" value="<?php echo $registerCodeKey; ?>"
                                                       id="codeVerify_option_registerAPPKey" size="30">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>评论场景</td>
                                            <td>
                                                <input type="text" name="codeVerify_option_commentAPPID" autocomplete="off" placeholder="为空则使用通用AppId" value="<?php echo $commentCodeAPPID; ?>"
                                                       id="codeVerify_option_commentAPPID" size="30">
                                            </td>
                                            <td>
                                                <input type="password" name="codeVerify_option_commentAPPKey" autocomplete="off" placeholder="为空则使用通用AppSecretKey" value="<?php echo $commentCodeKey; ?>"
                                                       id="codeVerify_option_commentAPPKey" size="30">
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>找回密码场景</td>
                                            <td>
                                                <input type="text" name="codeVerify_option_lostpasswordAPPID" autocomplete="off" placeholder="为空则使用通用AppId" value="<?php echo $lostpasswordCodeAPPID; ?>"
                                                       id="codeVerify_option_lostpasswordAPPID" size="30">
                                            </td>
                                            <td>
                                                <input type="password" name="codeVerify_option_lostpasswordAPPKey" autocomplete="off" placeholder="为空则使用通用AppSecretKey" value="<?php echo $lostpasswordCodeKey; ?>"
                                                       id="codeVerify_option_lostpasswordAPPKey" size="30">
                                            </td>
                                        </tr>


                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>


                </div>

                <button type="button" id="tencnetcloud-codeVerify-setting-update-button" class="btn btn-primary">保存设置</button>

                </form>
            </div>
        </div>
        <div style="text-align: center;padding-top:56px">
            <a href="https://openapp.qq.com/docs/Wordpress/captcha.html" target="_blank">文档中心</a> | <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-captcha" target="_blank">GitHub</a> | <a href="https://da.do/y0rp" target="_blank">意见反馈</a>
        </div>
    </div>
