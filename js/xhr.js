function remotePostCall(path, errormessage, postData)
{
	$.post(
		'%PATH%' + path,
		postData,
		function(data, textStatus)
		{
			if(textStatus == "success") {
                result = jQuery.parseJSON(data);
                if(result != null && result.success != undefined) {
                    handlePostSuccess(path, result.message, postData, textStatus);
                } else {
                    alert(errormessage);
                }
			} else {
				alert(errormessage);
			}
		},
		"text");
}
function loadURL(id, url, onreadylistener)
{
	$("#" + id).load(url, null, onreadylistener);
}