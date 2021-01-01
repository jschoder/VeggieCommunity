/* :TODO: migrator2 - cleanup? */
/* :TODO: pack into namespace */

var vc = vc || {};

vc.switchVisibility = function(id) {
	element = $('#' + id);

	if(element != null)
	{
		if(element.is(':visible'))  {
			element.hide();
		} else {
			element.show();
		}
	}
};


function printEditRegionCombo()
{
	regionElement = $('#region');
	if(regionElement.length != 0)
	{
		regionElement.empty();
		var country = $('#country').val();
		var selectedRegions = regionArray[country];
		if(selectedRegions != undefined)
		{
			regionElement.append('<option value="">' + noStatementOption + '</option>');
			for(var i=0; i<selectedRegions.length; i++)
			{
				if(selectedRegions[i] == defaultRegion)
				{
					regionElement.append('<option value="' + selectedRegions[i] + '" selected="selected">' + selectedRegions[i] + '</option>');
				}
				else
				{
					regionElement.append('<option value="' + selectedRegions[i] + '">' + selectedRegions[i] + '</option>');
				}
			}
		}
	}
}
function printSearchRegionCombo()
{
	var countryElements = $('input[name=\'country[]\']');
	var selectedCountries = new Array();
	for(var i=0; i<countryElements.length; i++) {
		regionGroup = $('#regiongroup' + countryElements[i].value);
		if(regionGroup.length) {
			if(countryElements[i].checked) {
				selectedCountries.push(countryElements[i].value);
			}
			regionGroup.hide();
		}
	}

	for(var i=0; i<selectedCountries.length; i++) {
		$('#regiongroup' + selectedCountries[i]).show();
	}
}
function hasValue(valueArray, value)
{
	for(var i=0; i<valueArray.length; i++)
	{
		if(valueArray[i]==value)
		{
			return true;
		}
	}
	return false;
}
function invertCheckboxSelection(elementname)
{
	elements = document.getElementsByName(elementname);
	for(i=0; i<elements.length; i++)
	{
		elements[i].checked=!elements[i].checked;
	}
}
/* edit-only code */
function updateFileUploads()
{
	var picCount=0;
	for(i=0;i<existingpics.length;i++) {

		if(!document.getElementById('Pic' + existingpics[i] + 'Delete').checked) {
			picCount++;
		}
	}
	for(newPic=1;newPic<=8;newPic++) {
		if(newPic<=(8-picCount)) {
			$('#newPic' + newPic + 'Row').show();

		} else {
			$('#newPic' + newPic + 'Row').hide();
			/* :TODO: reset doesn't work correctly in IE7 */
			document.getElementById('newPic' + newPic).value='';
		}
	}
}
function updateFieldCount(id, maxlength)
{
	textElement = document.getElementById(id);
	labelElement = document.getElementById(id + 'count');
	text = textElement.value;
	labelElement.innerHTML = text.length + ' / ' + maxlength;
}

/***************************************************************************************************
 *
 * Setting the timeZoneOffset
 *
 **************************************************************************************************/
if(!document.cookie || document.cookie.indexOf('TZoff'))
{
    document.cookie = 'TZoff=' + new Date().getTimezoneOffset() + ';Path=/;' + document.cookie;
}

/***************************************************************************************************
 *
 * face
 *
 **************************************************************************************************/
var visiblePopups = new Array();
function switchPopup(popupid, url)
{
	popup = document.getElementById(popupid);
	if(visiblePopups[popupid]==null)
	{
		visiblePopups[popupid] = 'true';
		popup.innerHTML = '<div class="jLoading"></div>';
		popup.className = 'popup-active';
		loadURL(popupid, '%PATH%' + url, false);
	}
	else if(visiblePopups[popupid]=='false')
	{
		visiblePopups[popupid] = 'true';
		popup.className = 'popup-active';
	}
	else
	{
		visiblePopups[popupid] = 'false';
		popup.className = 'popup';
	}
}
var visibleSidebarBlocks = new Array();
function switchSidebarBlock(elementid, url)
{
	var blockElement = document.getElementById(elementid);
	var blockHeader = document.getElementById(elementid + '-header');
	if(visibleSidebarBlocks[elementid]==null)
	{
		visibleSidebarBlocks[elementid] = 'true';
		blockElement.innerHTML = '<div class="jLoading"></div>';
		blockHeader.className = 'openslide';
		$(blockElement).show();
		loadURL(elementid, '%PATH%' + url, false);
	}
	else if(visibleSidebarBlocks[elementid]=='false')
	{
		visibleSidebarBlocks[elementid] = 'true';
		blockHeader.className = 'openslide';
		$(blockElement).show();
	}
	else
	{
		visibleSidebarBlocks[elementid] = 'false';
		blockHeader.className = 'hiddenslide';
		$(blockElement).hide();
	}
}
function loadPollForm(poll_id)
{
	loadURL('poll' + poll_id, '%PATH%poll/view/' + poll_id + '/form', false);
	return false;
}
function loadPollResult(poll_id)
{
	loadURL('poll' + poll_id, '%PATH%poll/view/' + poll_id + '/result', false);
	return false;
}


/***************************************************************************************************
 *
 * mysite.general
 *
 **************************************************************************************************/
function addActivity()
{
    var messageField = $('#activitymessage'),
        message = messageField.val(),
        form = messageField.closest('form');
    if(message.length <= 500) {
        $('.jLoading', form).show();
        $('.save', form).hide();
        remotePostCall(
            'activity/add/',
            "%GETTEXT('activity.remotecallfailed')%",
            {'message': message});
    } else {
        alert("%GETTEXT('activity.toolongmessage')%");
    }
}
function deleteActivity(id)
{
	if(confirm("%GETTEXT('activity.delete.confirm')%")) {
        remotePostCall(
            'activity/delete/',
            "%GETTEXT('activity.remotecallfailed')%",
            {'id': id});
    }
}

var visitorDisplayCount = 24;
function showVisitors()
{
	tabContent = document.getElementById('visitors');
	tabContent.innerHTML = '<div class="jLoading"></div>';
	loadURL('visitors', '%PATH%mysite/visitors/' + visitorDisplayCount, function(responseText, textStatus, XMLHttpRequest)
            {
                if(textStatus != "success") {
                    document.getElementById('visitors').innerHTML="%GETTEXT('mysite.visitors.loadingfailed')%";
                } else {
                    if(visitorDisplayCount == '%LAST_VISITOR_XS%') {
                        visitorDisplayCount = '%LAST_VISITOR_XL%';
                    } else {
                        visitorDisplayCount = '%LAST_VISITOR_XS%';
                    }
                }
            });
}
function showSearchResult(contentIDs, urls)
{
	if(contentIDs.length > 0 && urls.length > 0)
	{
		contentID = contentIDs.shift();
		url = urls.shift();
		loadURL('search' + contentID + 'Content', url, function(responseText, textStatus, XMLHttpRequest)
			{
				if(textStatus != "success")
				{
					document.getElementById('search' + contentID + 'Content').innerHTML="%GETTEXT('mysite.savedsearchs.loadingfailed')%";
				}
				showSearchResult(contentIDs, urls);
			});
	}
}
function deleteSavedSearch(searchid)
{
	if(confirm("%GETTEXT('mysite.savedsearch.deletesearch.confirm')%"))
	{
		remotePostCall(
            'user/result/delete/',
			"%GETTEXT('friendinbox.remotecallfailed')%",
			{'searchid':searchid});
	}
}

/***************************************************************************************************
 *
 * profile
 *
 **************************************************************************************************/
function blockUser(profileid)
{
	if(confirm("%GETTEXT('profile.blockprofile.confirm')%"))
	{
		remotePostCall(
            'block/add/',
			"%GETTEXT('profile.remotecallfailed')%",
			{'profileid': profileid});
	}
}

/***************************************************************************************************
 *
 * search
 *
 **************************************************************************************************/
slidesVisibility=new Array();
hobbiesLoaded = false;
function setSlideVisible(menuid)
{
	if(isSlideVisible(menuid))
	{
		collapseSlide(menuid);
	}
	else
	{
		expandSlide(menuid);
	}
}
function isSlideVisible(menuid)
{
	arrayid = '.' + menuid;
	if(slidesVisibility[arrayid]=='true' || slidesVisibility[arrayid]==null)
	{
		return true;
	}
	else
	{
		return false;
	}
}
function collapseSlide(menuid)
{
	arrayid = '.' + menuid;
	headerElement = document.getElementById('slide-header-' + menuid);
	contentElement = document.getElementById('slide-content-' + menuid);
	if(headerElement != null && contentElement != null)
	{
		headerElement.className='hiddenslide';
		$(contentElement).hide();
		slidesVisibility[arrayid] = 'false';
	}
}
function expandSlide(menuid)
{
	arrayid = '.' + menuid;
	headerElement = document.getElementById('slide-header-' + menuid);
	contentElement = document.getElementById('slide-content-' + menuid);
	if(headerElement != null && contentElement != null)
	{
		headerElement.className='openslide';
		$(contentElement).show();
		slidesVisibility[arrayid] = 'true';
	}
}
function selectAll(field)
{
	allelement = document.getElementById(field + '.all').checked;
	/* alert('selet ' + field); */
	elements = document.getElementsByName(field + '[]');
	for(i=0; i<elements.length; i++)
	{
		elements[i].checked = false;
	}
}
function deselectAll(field)
{
	document.getElementById(field + '.all').checked = false
}

/***************************************************************************************************
 *
 * Different responses including profilebox (friend.add / favorite.add)
 *
 **************************************************************************************************/
function handlePostSuccess(path, message, postData, httpstatus)
{
    if(httpstatus == "success")
    {
        if(path == 'user/result/delete')
        {
                $('#search' + postData['searchid']).hide();
        }
    }
    if(message.trim() != '')
    {
            alert(message);
    }
}

var vc = vc || {};
vc.date = {
    parseTime: function(time) {
        var timeArray = time.split(':');
        if (timeArray.length == 2) {
            return (timeArray[0] * 3600) + (timeArray[1] * 60);
        } else {
            return 0;
        }
    }
};
