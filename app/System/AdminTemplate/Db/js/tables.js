$(function () {

    $("#apps li").click(function () {

        var $this = $(this);
        var sUrl = $this.data("url");

        $.ajax({
            url: sUrl,
            success: function (sHtml) {
                $("#tables").html(sHtml);
            }
        });
    });

    $("#tables li").click(function () {
        var $this = $(this);
        var sUrl = $this.data("url");

        $.ajax({
            url: sUrl,
            success: function (sHtml) {
                $("#table-config").html(sHtml);
            }
        });
    });


    $("#table-config form").submit(function () {

    });

});
