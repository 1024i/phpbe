function deleteAvatar() {
    $.ajax({
        type: "GET",
        url: Be::getRuntime()->getUrlRoot() + "/?controller=user_profile&action=ajax_delete_avatar",
        dataType: "json",
        success: function (json) {
            alert(json.message);
        },
        error: function () {
            alert("服务器错误！")
        }
    });
}