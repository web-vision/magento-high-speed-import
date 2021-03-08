$(function () {
    var $addEmailField = $('#addEmail'),
        $addXmlToCsvField = $('#addSimpleXmlToCsv'),
        $addFTPDownload = $('#addFTPDownload'),
        $addHeadline = $('#addHeadline');

    if ($addEmailField.length) {
        $addEmailField.off('click').on('click', function() {
            var $selectFieldGroup = $(this).data('class');

            if ($(document).find('#AppendScripts .AppendScript.EmailScript').length === 0) {
                var $cloned = $('#' + $selectFieldGroup).clone(),
                    clonedHtml = '<div class="form-group AppendScript EmailScript">' + $cloned.html() + '</div>';

                $('#AppendScripts').append(clonedHtml);
            }
        });
    }

    if ($addXmlToCsvField.length) {
        $addXmlToCsvField.off('click').on('click', function() {
            var $selectFieldGroup = $(this).data('class');

            if ($(document).find('#AppendScripts .AppendScript.SimpleXmlToCsvScript').length === 0) {
                var $cloned = $('#' + $selectFieldGroup).clone(),
                    clonedHtml = '<div class="form-group AppendScript SimpleXmlToCsvScript">' + $cloned.html() + '</div>';

                $('#AppendScripts').append(clonedHtml);
            }
        });
    }

    if ($addFTPDownload.length) {
        $addFTPDownload.off('click').on('click', function() {
            var $selectFieldGroup = $(this).data('class');

            if ($(document).find('#AppendScripts .AppendScript.FTPDownloadScript').length === 0) {
                var $cloned = $('#' + $selectFieldGroup).clone(),
                    clonedHtml = '<div class="form-group AppendScript FTPDownloadScript">' + $cloned.html() + '</div>';

                $('#AppendScripts').append(clonedHtml);
            }
        });
    }

    if ($addHeadline.length) {
        $addHeadline.off('click').on('click', function() {
            var $selectFieldGroup = $(this).data('class');

            if ($(document).find('#AppendScripts .AppendScript.HeadlineScript').length === 0) {
                var $cloned = $('#' + $selectFieldGroup).clone(),
                    clonedHtml = '<div class="form-group AppendScript HeadlineScript">' + $cloned.html() + '</div>';

                $('#AppendScripts').append(clonedHtml);
            }
        });
    }

    $(document).off('click').on('click', '#AppendScripts .AppendScript button', function () {
        $(this).parents('.AppendScript').remove();

        return false;
    });
});
