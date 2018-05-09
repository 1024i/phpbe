$(function () {

    $("#form-user_profile_edit").validate({

        rules: {
            name: {
                maxlength: 60
            },
            phone: {
                maxlength: 20
            },
            mobile: {
                digits: true,
                minlength: 11,
                maxlength: 11
            },
            qq: {
                digits: true,
                minlength: 5,
                maxlength: 12
            }
        },

        messages: {
            name: {
                maxlength: "最多{0}个字符！"
            },
            phone: {
                maxlength: "最多{0}个字符！"
            },
            mobile: {
                digits: "请输入合法的手机号码！",
                minlength: "至少{0}个字符！",
                maxlength: "最多{0}个字符！"
            },
            qq: {
                digits: "请输入合法的QQ号码！",
                minlength: "至少{0}个字符！",
                maxlength: "最多{0}个字符！"
            }
        },

        submitHandler: function (form) {

            var $submit = $(".btn-submit", $(form));
            var sValue = $submit.val();

            $submit.prop("disabled", true).val("处理中，请稍候...");

            $.ajax({
                type: "POST",
                url: Be::getRuntime()->getUrlRoot() + "/?controller=user_profile&task=ajax_edit_save",
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