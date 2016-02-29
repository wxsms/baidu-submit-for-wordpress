$(function () {
    var urlTable = $('#url-table'),
        submitBtn = $('#submit'),
        log = $('#log'),
        checkAll = $('input[value=checkAll]');

    checkAll.click(function () {
        var $this = $(this);
        console.log($this.is(':checked'));
        $this.parents('table').find('tbody').find('input[type=checkbox]').prop('checked', $this.is(':checked'));
    }).click();

    submitBtn.click(function () {
        var checkboxes = urlTable.find('tbody input[type=checkbox]:checked');
        var urls = [];
        for (var i = 0; i < checkboxes.length; i++) {
            urls.push(checkboxes[i].value);
        }
        if (urls.length > 0) {
            submitBtn.prop('disabled', true);
            $.post('index.php', {
                urls: urls
            }, function (data) {
                log.html(log.html() + '<br/>' + '[INFO]' + new Date().toLocaleString() + ': ' + data);
                submitBtn.prop('disabled', false);
            });
        }
    })
});