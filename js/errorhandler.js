window.onerror = function(msg, url, line)
{
    $.post(
        '/api/javascript/log/',
        {
            url: url,
            msg: msg,
            line: line
        }
    );
};