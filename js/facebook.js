vc.fb = {
    init: function() {
        $(document).ready(function() {
            try {
                FB.init({
                      appId      : "%FACEBOOK_APP%",
                      status     : true, /* check login status */
                      cookie     : true, /* enable cookies to allow the server to access the session */
                      xfbml      : true  /* parse XFBML */
                });
            } catch (e) {
                $('.jLoginLoading').hide();
                $('.jLoadFail').show();
            }
        });
    },

    login: function() {
        $(document).ready(function() {
            FB.getLoginStatus(function(response) {
                if (response.status == 'connected') {
                    vc.fb.connect(response.authResponse);
                } else {
                    FB.login(function(response){
                        if (response.status == 'connected') {
                            vc.fb.connect(response.authResponse);
                        } else {
                            $('.jLoginLoading').hide();
                            $('.jLoginFailed').show();
                            window.history.back();
                        }
                    });
                }
            }, {
                scope: 'public_profile,email,user_birthday'
            });
        });
    },
    
    connect: function (authResponse) {
        $.post(
            '%PATH%fb/tokenlogin/',
            { 
                'accessToken': authResponse.accessToken
            },
            function(data, textStatus, jqXHR) {
                if (data.success) {
                    /* Login successful */
                    window.document.location = '%PATH%mysite/';
                    
                } else {
                    /* Prefill form with all fields */
                    $('form#jsFbSignup input[name="fb_user_id"]').val(authResponse.userID);
                    $('form#jsFbSignup input[name="fb_access_token"]').val(authResponse.accessToken);
                    $('form#jsFbSignup').submit();
                }
            });
    }
};
