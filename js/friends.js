vc.friends = {
    add: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        if(confirm("%GETTEXT('profile.friend.confirm')%")) {
            $.post(
                '%PATH%friend/add/',
                {
                    'profileid': link.data('userId')
                },
                function(data, textStatus, jqXHR) {
                    if (data.success == true) {
                        link.removeClass('addFriend');
                        link.addClass('cancelFriend');
                        $('span', link).html("%GETTEXT('profile.friend.cancel')%");
                        link.prop('title', "%GETTEXT('profile.friend.cancel')% %GETTEXT('profile.friend.title')%");
                    } else if (data.message !== '') {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('profile.remotecallfailed')%");
                    }
                }).fail(function() {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                });
        }
    },
    cancel: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        $.post(
            '%PATH%friend/delete/',
            {
                'profileid': link.data('userId')
            },
            function(data, textStatus, jqXHR) {
                if (data.success == true) {
                    link.removeClass('cancelFriend'); 
                    link.addClass('addFriend');
                    $('span', link).html("%GETTEXT('profile.addfriend')%");
                    link.prop('title', "%GETTEXT('profile.addfriend')% %GETTEXT('profile.friend.title')%");
                    $('#friendProfilebox' + link.data('userId')).remove();
                } else if (data.message !== '') {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                }
            }).fail(function() {
                alert("%GETTEXT('profile.remotecallfailed')%");
            });
    },
    confirm: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        $.post(
            '%PATH%friend/accept/',
            {
                'profileid': link.data('userId')
            },
            function(data, textStatus, jqXHR) {
                if (data.success == true) {
                    link.removeClass('confirmFriend');
                    link.addClass('deleteFriend');
                    $('span', link).html("%GETTEXT('mysite.friends.deletefriend')%");
                    link.prop('title', "%GETTEXT('profile.friends.deletefriend')% %GETTEXT('profile.friend.title')%");
                    $('.denyFriend', link.parent()).remove();
                    $('#friendProfilebox' + link.data('userId')).remove();
                    if ($('#friendInbox li').length === 0) {
                        $('#friendInbox li').hide();
                    }
                    vc.ui.updateAccountStatus();
                } else if (data.message !== '') {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                }
            }).fail(function() {
                alert("%GETTEXT('profile.remotecallfailed')%");
            });
    },
    deny: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        $.post(
            '%PATH%friend/deny/',
            {
                'profileid': link.data('userId')
            },
            function(data, textStatus, jqXHR) {
                if (data.success == true) {
                    link.removeClass('denyFriend');
                    link.addClass('addFriend');
                    $('span', link).html("%GETTEXT('profile.addfriend')%");
                    link.prop('title', "%GETTEXT('profile.addfriend')% %GETTEXT('profile.friend.title')%");
                    $('.confirmFriend', link.parent()).remove();
                    $('#friendProfilebox' + link.data('userId')).remove();
                    if ($('#friendInbox li').length === 0) {
                        $('#friendInbox li').hide();
                    }
                    vc.ui.updateAccountStatus();

                } else if (data.message !== '') {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                }
            }).fail(function() {
                alert("%GETTEXT('profile.remotecallfailed')%");
            });
    },
    showOnChange: function(profileid) {
        text = document.getElementById(profileid + 'comment').value;
        if(text == comments[profileid]) {
                $('#' + profileid + 'save').hide();
        } else {
                $('#' + profileid + 'save').show();
        }
    },
    remove: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        if(confirm("%GETTEXT('mysite.friends.deletefriend.confirm')%")) {
            $.post(
                '%PATH%friend/delete/',
                {
                    'profileid': link.data('userId')
                },
                function(data, textStatus, jqXHR) {
                    if (data.success == true) {
                        link.removeClass('deleteFriend');
                        link.addClass('addFriend');
                        $('span', link).html("%GETTEXT('profile.addfriend')%");
                        link.prop('title', "%GETTEXT('profile.addfriend')% %GETTEXT('profile.friend.title')%");
                        $('#friendProfilebox' + link.data('userId')).remove();
                    } else if (data.message !== '') {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('profile.remotecallfailed')%");
                    }
                }).fail(function() {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                });
        }
    }
};
