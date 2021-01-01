vc.favorites = {
    add: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        $.post(
            '%PATH%favorite/add/',
            {
                'profileid': link.data('userId')
            },
            function(data, textStatus, jqXHR) {
                if (data.success == true) {
                    link.removeClass('addFavorite');
                    link.addClass('deleteFavorite');
                    if (link.attr('title')){
                        link.attr('title', "%GETTEXT('mysite.friends.deletefavorite')%");
                    }
                    $('span', link).html("%GETTEXT('mysite.friends.deletefavorite')%");
                } else if (data.message !== '') {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('profile.remotecallfailed')%");
                }
            }).fail(function() {
                alert("%GETTEXT('profile.remotecallfailed')%");
            });
    },
    remove: function(event) {
        event.preventDefault();
        var link = $(event.target).closest('a');
        if(confirm("%GETTEXT('mysite.friends.deletefavorite.confirm')%")) {
            $.post(
                '%PATH%favorite/delete/',
                {
                    'profileid': link.data('userId')
                },
                function(data, textStatus, jqXHR) {
                    if (data.success == true) {
                        link.removeClass('deleteFavorite');
                        link.addClass('addFavorite');
                        if (link.attr('title')){
                            link.attr('title', "%GETTEXT('profile.addfavorite')%");
                        }
                        $('span', link).html("%GETTEXT('profile.addfavorite')%");
			$('#favoriteProfilebox' + link.data('userId')).remove();
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