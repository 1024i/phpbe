$(function () {

    $("#form-user_profile_edit_password").validate({

        rules: {
            password: {
                required: true
            },
            password1: {
                required: true,
                minlength: 6
            },
            password2: {
                required: true,
                equalTo: "#password1"
            }
        },

        messages: {
            password: {
                required: "请输入当前密码！"
            },
            password1: {
                required: "新密码不能为空！",
                minlength: "密码至少{0}个字符！"
            },
            password2: {
                required: "确认新密码不能为空！",
                equalTo: "两次输入的密码不匹配！"
            }
        },

        submitHandler: function (form) {

            var $submit = $(".btn-submit", $(form));
            var sValue = $submit.val();

            $submit.prop("disabled", true).val("处理中，请稍候...");

            $.ajax({
                type: "POST",
                url: Be::getRuntime()->getUrlRoot() + "/?controller=user_profile&task=ajax_edit_password_save",
                data: $(form).serialize(),
                dataType: "json",
                success: function (json) {

                    $submit.prop("disabled", false).val(sValue);

                    alert(json.message);
                },
                error: function () {
                    alert("服务器错误！")
                }
            });

        }
    });
})