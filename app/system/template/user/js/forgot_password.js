$(function () {
    $("#form-forgot_password").validate({
        rules: {
            username: {
                required: true
            }
        },
        messages: {
            username: {
                required: "用户名不能为空！"
            }
        },

        submitHandler: function (form) {

            var $submit = $(".btn-submit", $(form));
            var sValue = $submit.val();

            $submit.prop("disabled", true).val("处理中，请稍候...");

            $.ajax({
                type: "POST",
                url: Be::getRuntime()->getUrlRoot() + "/?controller=user&action=ajax_forgot_password_save",
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
});