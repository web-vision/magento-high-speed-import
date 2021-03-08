$(function () {

    $(document).on('click', 'button.add', function() {
        var i = $(document).find('#js-mapping-wrap .js-mapping-field').size();

        var cloned = $('#js-mapping-default').clone(),
            clonedHtml = cloned.html().replace(/###COUNT###/g, i);

        $('#js-mapping-wrap').append(clonedHtml);

    }).on('click', 'button.remove', function(e) {
        e.preventDefault();
        $(this).parent().parent().remove();
    });

});