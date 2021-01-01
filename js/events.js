var vc = vc || {};

vc.events = {
    init: function() {
        $(document).ready(function() {
            $('body').on('click', '.jInvite', vc.events.openInvitationDialog);
            $('body').on('click', '.jParticipation', vc.events.participateButton);
            $('body').on('click', '.ctabar a', vc.events.participateCta);
            $('body').on('submit', '.inviteMemberDialog form', vc.events.inviteMembers);
        });
    },
    
    openInvitationDialog: function(event) {
        event.preventDefault();
        var target = $(event.target).closest('a');
        var eventId = target.data('eventId');
        var $dialog = $('<div data-event-id="' + eventId + '"></div>')
            .load('%PATH%events/invitation/add/' + eventId)
            .dialog({
                dialogClass: 'inviteMemberDialog',
                title: "%GETTEXT('event.invitation.title')%",
                width: Math.min(480, $(window).width() * 0.95),
                position: { at: 'center top' },
                close: function(event, ui) {
                    $($dialog).remove();
                }
            });
    },
    
    participateButton: function(event) {
        event.preventDefault();
        var target = $(event.target).closest('a');
        vc.events.participate(
            target.data('id'),
            target.data('degree')
        );
    },
    
    participateCta: function(event) {
        event.preventDefault();
        var ctabar = $(event.target).closest('.ctabar');
        vc.events.participate(
            ctabar.data('id'),
            $('select[name="degree"]', ctabar).val()
        );
    },
    
    participate: function(hashId, degree) {
        $.post(
            '%PATH%events/participate/',
            {
                'id': hashId,
                'degree': degree
            },
            function(data, textStatus, jqXHR) {
                if (data != null && data.success == true) {
                    var link = $('a[data-degree="' + degree + '"]');
                    
                    $('.show-participate').removeClass('marked');
                    $('.jParticipation').removeClass('marked');
                    link.addClass('marked');
                    $('.show-participate', link.closest('.context')).addClass('marked');
                    
                    /* Highlighting the button of the currently selected option */
                    $('.jParticipation').addClass('secondary');
                    link.removeClass('secondary');
                    
                    /* Selecting the default option */
                    $('.ctabar select').val(degree);
                    
                } else {
                    alert("%GETTEXT('event.participate.failed')%");
                }
            }).fail(function() {
                alert("%GETTEXT('event.participate.failed')%");
            });
    },
    
    inviteMembers: function(event) {
        event.preventDefault();
        var profileIdElements = $('input[name=\'profileId[]\']:checked', event.target);
        if (profileIdElements.length == 0) {
            alert("%GETTEXT('event.invitation.emptyProfileId')%");
        }
        var profileIds = new Array();
        profileIdElements.each(function(index, element) {
            profileIds.push($(element).val());
        });
        $.post(
            '%PATH%events/invitation/add/',
            {
                'eventId': $(event.target).parent('.ui-dialog-content').data('eventId'),
                'profileId': profileIds
            },
            function(data, textStatus, jqXHR) {
                if (data != null && data.success == true) {
                    $(event.target).parent('.ui-dialog-content').dialog('close');
                    alert("%GETTEXT('group.invitation.success')%");
                } else {
                    alert("%GETTEXT('event.invitation.failed')%");
                }

            }).fail(function() {
                alert("%GETTEXT('event.invitation.failed')%");
            });
    }
};