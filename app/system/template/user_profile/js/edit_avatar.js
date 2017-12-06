function deleteAvatar() {
    $.ajax({
        type: "GET",
        url: URL_ROOT + "/?controller=user_profile&task=ajax_delete_avatar",
        dataType: "json",
        success: function (json) {
            alert(json.message);
        },
        error: function () {
            alert("服务器错误！")
        }
    });
}