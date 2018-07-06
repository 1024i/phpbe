$(function () {

    $("#form-login").validate({
        rules: {
            username: {
                required: true
            },
            password: {
                required: true
            },
            captcha: {
                required: true
            }
        },
        messages: {
            username: {
                required: "请输入用户名！"
            },
            password: {
                required: "请输入密码！"
            },
            captcha: {
                required: "请输入验证码！"
            }
        },


        submitHandler: function (form) {

            var $submit = $(".btn-submit", $(form));
            var sValue = $submit.val();

            $submit.prop("disabled", true).val("处理中，请稍候...");

            $.ajax({
                type: "POST",
                url: Be::getRuntime()->getUrlRoot() + "/?controller=user&action=login_check",
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

