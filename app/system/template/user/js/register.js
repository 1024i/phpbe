function checkTermsAndConditions() {
    $("#form-register .btn-submit").prop("disabled", !$("#terms_and_conditions").prop("checked"));
}

$(function () {

    checkTermsAndConditions();
    $("#terms_and_conditions").click(checkTermsAndConditions);

    $("#form-register").validate({
        rules: {
            username: {
                required: true,
                minlength: 4,
                maxlength: 40
            },
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            },
            password2: {
                required: true,
                equalTo: "#middle-password"
            },
            captcha: {
                required: true
            }
        },
        messages: {
            username: {
                required: "用户名不能为空！",
                minlength: "用户名至少{0}个字符！",
                maxlength: "用户名至多{0}个字符！"
            },
            email: {
                required: "邮箱不能为空！",
                email: "非法的邮箱格式！"
            },
            password: {
                required: "密码不能为空！",
                minlength: "密码至少{0}个字符！"
            },
            password2: {
                required: "确认密码不能为空！",
                equalTo: "两次输入的密码不匹配！"
            },
            captcha: {
                required: "验证码不能为空！"
            }
        },

        submitHandler: function (form) {

            var $submit = $(".btn-submit", $(form));
            var sValue = $submit.val();

            $submit.prop("disabled", true).val("处理中，请稍候...");

            $.ajax({
                type: "POST",
                url: Be::getRuntime()->getUrlRoot() + "/?controller=user&action=ajax_register_save",
                data: $(form).serialize(),
                dataType: "json",
                success: function (json) {

                    $submit.prop("disabled", false).val(sValue);

                    if (json.error == "0") {
                        window.location.href = json.redirect_url;
                    }
                    else {
                        alert(json.message);
                    }
                },
                error: function () {
                    alert("服务器错误！")
                }
            });


        }
    });
});