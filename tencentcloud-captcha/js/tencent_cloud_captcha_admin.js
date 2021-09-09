/**
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
jQuery(function ($) {
   //获取自定义场景的按钮值
   var codefreeshow = $('#codeVerify-option-codeFree').val();
   //判断是否默认勾选，如果勾选隐藏表单否则展示
   if (codefreeshow !=2){
      $('#codeVerifyFree').hide();
   }else {
      $('#codeVerifyFree').show();
   }
   //获取表单的提交url
   var ajaxUrl = $("#tencnetcloud-codeVerify-setting-form").data("ajax-url")
   //插件设置保存按钮点击事件
   $('#tencnetcloud-codeVerify-setting-update-button').click(function () {
      var secretCustom = $("#codeVerify-option-secret-custom").is(":checked")?2:1;
      var secretID = $("#codeVerify-option-secret-id").val()
      var secretKey = $("#codeVerify-option-secret-key").val()

      var codeAppId = $("#codeVerify-option_codeAppId").val();
      var codeSecretKey = $("#codeVerify-option_codeSecretKey").val();

      var registerCode = $("#codeVerify-option-register").is(":checked")?2:1;;
      var commentCode = $("#codeVerify-option-comment").is(":checked")?2:1;;
      var loginCode = $("#codeVerify-option-login").is(":checked")?2:1;;
      var lostpasswordCode = $("#codeVerify-option-lostpassword").is(":checked")?2:1;;

      var debugCode = $("#codeVerify-option-debug").is(":checked")?2:1;;
      var codeFree = $("#codeVerify-option-codeFree").is(":checked")?2:1;;

      var registerCodeAppId = $("#codeVerify_option_registerAPPID").val();
      var registerCodeKey = $("#codeVerify_option_registerAPPKey").val();
      var commentCodeAppId = $("#codeVerify_option_commentAPPID").val();
      var commentCodeKey = $("#codeVerify_option_commentAPPKey").val();
      var lostpasswordCodeAppId = $("#codeVerify_option_lostpasswordAPPID").val();
      var lostpasswordCodeKey = $("#codeVerify_option_lostpasswordAPPKey").val();

      $.ajax({
         type: "post",
         url: ajaxUrl,
         dataType:"json",
         data: {
            action: "update_codeVerify_settings",
            secretCustom:secretCustom,
            secret_id: secretID,
            secret_key: secretKey,
            codeVerify_option_codeAppId: codeAppId,
            codeVerify_option_codeSecretKey: codeSecretKey,
            registerNeedCode: registerCode,
            commentNeedCode: commentCode,
            loginNeedCode:loginCode,
            lostpasswordNeedCode:lostpasswordCode,
            debugNeedCode:debugCode,
            codeFree: codeFree,
            registerCodeAppId: registerCodeAppId,
            registerCodeKey: registerCodeKey,
            commentCodeAppId: commentCodeAppId,
            commentCodeKey:commentCodeKey,
            lostpasswordCodeAppId:lostpasswordCodeAppId,
            lostpasswordCodeKey:lostpasswordCodeKey,
         },
         success: function(response) {
            showAjaxReturnMsg(response.data.msg,response.success)
            if (response.success){
               setTimeout(function(){
                  window.location.reload();//刷新当前页面.
               },2000)
            }
         }
      });
   });

   //验证码测试按钮的点击事件
   $('#codeVerifyButtonCheck').click(function () {
      //重置ticket和随机字符串的值
      $('#codeVerifyTicketCheck').val('');
      $('#codeVerifyRandstrCheck').val('');
      //初始化验证码
      var captcha1 = new TencentCaptcha($('#codeVerifyButtonCheck').attr('data-appid'), function (res) {
         //判断是否验证成功
         if (res.ret == 0) {
            //将返回的ticket赋值给表单
            $('#codeVerifyTicketCheck').val(res.ticket);
            $('#codeVerifyRandstrCheck').val(res.randstr);
            //隐藏验证按钮
            $('#codeVerifyButtonCheck').hide();
            //展示通过按钮
            $('#codePassButtonNext').show();
         }
      });
      //显示验证码
      captcha1.show();
   });

   $('#codePassButtonNext').click(function () {
      var codeVerifyTicketCheck = $("#codeVerifyTicketCheck").val();
      var codeVerifyRandstrCheck = $("#codeVerifyRandstrCheck").val();
      var codeAppId = $("#codeVerify-option_codeAppId").val();
      var codeSecretKey = $("#codeVerify-option_codeSecretKey").val();

      $.ajax({
         type: "post",
         url: ajaxUrl,
         dataType:"json",
         data: {
            action: "codeVerify_check",
            codeVerifyTicketCheck:codeVerifyTicketCheck,
            codeVerifyRandstrCheck: codeVerifyRandstrCheck,
            codeAppId: codeAppId,
            codeSecretKey: codeSecretKey
         },
         success: function(response) {
            console.log(response);
            if (response.success === true) {
               //隐藏验证按钮
               $('#codePassButtonNext').hide();
               //展示通过按钮
               $('#codePassButtonOk').show();
            } else {
               //隐藏验证按钮
               $('#codePassButtonNext').hide();
               //展示通过按钮
               $('#codeVerifyButtonCheck').show();
               $('#span_captch_verify')[0].innerHTML = response.data.msg;
            }

         }
      });
   });


   //展示异步返回消息
   function showAjaxReturnMsg(msg,success) {
      var parent = $('#codeVerify_show-ajax-return-msg').parent();
      if (!success) {
         parent.removeClass('alert-success');
         parent.hasClass('alert-danger') || parent.addClass('alert-danger');
      } else {
         parent.removeClass('alert-danger');
         parent.hasClass('alert-success') || parent.addClass('alert-success');
      }
      $('#codeVerify_show-ajax-return-msg').text(msg);
      parent.show();
      goToTheTop();
   }
   //获取元素的上级
   function goToTheTop() {
      $('html ,body').animate({scrollTop: 0}, 330);
   }
   //异步消息关闭按钮
   $('#codeVerify_close-ajax-return-msg').click(function () {
      $(this).parent().hide();
   });
   //自定义业务按钮点击事件
   $("#codeVerify-option-codeFree").click(function () {
      if (this.checked==true){
         $('#codeVerifyFree').show();
      }else{
         $('#codeVerifyFree').hide();
      }
   });
   //自定义密钥点击事件
   $("#codeVerify-option-secret-custom").click(function () {
      if (this.checked==true){
         $('#codeVerify-option-secret-id').removeAttr('disabled');
         $('#codeVerify-option-secret-key').removeAttr('disabled');
      }else{
         $('#codeVerify-option-secret-id').attr('disabled',"disabled");
         $('#codeVerify-option-secret-key').attr('disabled',"disabled");
      }

   });
   //secretId展示按钮点击事件
   $('#codeVerify_secret_id_type_exchange').click(function () {
      change_type($('#codeVerify-option-secret-id'), $(this));
   });
   //secretKey展示按钮点击事件
   $('#codeVerify_secret_key_type_exchange').click(function () {
      change_type($('#codeVerify-option-secret-key'), $(this));
   });
   //将隐藏的值展示出来
   function change_type(input_element, span_eye) {
      if(input_element[0].type === 'password') {
         input_element[0].type = 'text';
         span_eye.addClass('dashicons-visibility').removeClass('shicons-hidden');
      } else {
         input_element[0].type = 'password';
         span_eye.addClass('shicons-hiddenda').removeClass('dashicons-visibility');
      }
   }

   $('#button_delete_logfile').click(function () {
      $.ajax({
         type: "post",
         url: ajaxUrl,
         dataType: "json",
         data: {
            action: "delete_captcha_logfile"
         },
         success: function (response) {
            if (response.success) {
               $('#span_delete_logfile')[0].innerHTML = "删除成功！";
               $('#span_delete_logfile').show().delay(5000).fadeOut();
            }
         }
      });
   });

   $('#codeVerify-option-debug').change(function () {
      if ($('#codeVerify-option-debug')[0].checked) {
         $('#tr_delete_logfile').css('display', '');
      } else {
         $('#tr_delete_logfile').css('display', 'none');
      }
   });

});