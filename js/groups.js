var vc = vc || {};

vc.groups = vc.groups || {};

vc.groups.header = {
    init: function() {
        $(document).ready(function() {
            $('body').on('submit', 'form.jLeave', vc.groups.header.leaveGroupConfirm);
            $('body').on('submit', 'form.jCancel', vc.groups.header.cancelRequestConfirm);
            $('body').on('click', '.jInvite', vc.groups.header.openInvitationDialog);
            $('body').on('click', '.jDeleteGroup', vc.groups.header.deleteGroup);
            $('body').on('click', '.deleteGroupDialog button.cancel', vc.groups.header.closeDeleteGroup);
            $('body').on('submit', '.inviteMemberDialog form', vc.groups.header.inviteMembers);
        });
    },

    leaveGroupConfirm: function(event) {
        if (!confirm("%GETTEXT('group.detail.leave.confirm')%")) {
            event.preventDefault();
        }
    },
    cancelRequestConfirm: function(event) {
        if (!confirm("%GETTEXT('group.detail.cancelRequest.confirm')%")) {
            event.preventDefault();
        }
    },
    openInvitationDialog: function(event) {
        event.preventDefault();
        var groupId = $(event.target).data('groupId');
        var $dialog = $('<div data-group-id="' + groupId + '"></div>')
            .load('%PATH%groups/invitation/add/' + groupId)
            .dialog({
                dialogClass: 'inviteMemberDialog',
                title: "%GETTEXT('group.invitation.title')%",
                width: Math.min(480, $(window).width() * 0.95),
                position: { at: 'center top' },
                close: function(event, ui) {
                    $($dialog).remove();
                }
            });
    },
    inviteMembers: function(event) {
        event.preventDefault();
        var profileIdElements = $('input[name=\'profileId[]\']:checked', event.target);
        if (profileIdElements.length == 0) {
            alert("%GETTEXT('group.invitation.emptyProfileId')%");
        }
        var profileIds = new Array();
        profileIdElements.each(function(index, element) {
            profileIds.push($(element).val());
        });
        $.post(
            '%PATH%groups/invitation/add/',
            {
                'groupId': $(event.target).parent('.ui-dialog-content').data('groupId'),
                'profileId': profileIds,
                'comment': $('textarea', event.target).val()
            },
            function(data, textStatus, jqXHR) {
                if (data != null && data.success == true) {
                    $(event.target).parent('.ui-dialog-content').dialog('close');
                    alert("%GETTEXT('group.invitation.success')%");
                } else {
                    alert("%GETTEXT('group.invitation.failed')%");
                }

            }).fail(function() {
                alert("%GETTEXT('group.invitation.failed')%");
            });
    },
    deleteGroup: function(event) {
        event.preventDefault();
        $('#deleteGroupDialog').dialog({
            dialogClass: 'deleteGroupDialog',
            title: "%GETTEXT('group.detail.deletegroup')%",
            width: Math.min(480, $(window).width() * 0.95)
        });
    },
    closeDeleteGroup: function(event) {
        event.preventDefault();
        $('#deleteGroupDialog').dialog('close');
    }
};
vc.groups.info = {
    init: function() {
        vc.groups.header.init();
        $(document).ready(function() {
            $('body').on('click', '.jUnconfirmedMembers button', vc.groups.info.actions.handleUnconfirmed);
            $('.jMembers').on('click', '.remove', vc.groups.info.actions.remove);
            $('.jMembers').on('click', '.ban', vc.groups.info.actions.ban);
            $('.jMembers').on('click', '.modAdd', {'role': 1, confirm: "%GETTEXT('group.confirm.role.add.mod')%"}, vc.groups.info.actions.role.add);
            $('.jMembers').on('click', '.modRemove', {'role': 1, confirm: "%GETTEXT('group.confirm.role.remove.mod')%"}, vc.groups.info.actions.role.remove);
            $('.jMembers').on('click', '.adminAdd', {'role': 2, confirm: "%GETTEXT('group.confirm.role.add.admin')%"}, vc.groups.info.actions.role.add);
            $('.jMembers').on('click', '.adminRemove', {'role': 2, confirm: "%GETTEXT('group.confirm.role.remove.admin')%"}, vc.groups.info.actions.role.remove);
        });
    },

    actions: {
        handleUnconfirmed: function(event) {
            event.preventDefault();
            var target = $(event.target);
            if (target.data('action').length == 0 || 
                target.data('groupId').length == 0 || 
                target.data('userId').length == 0) {
                alert("%GETTEXT('group.members.unconfirmed.failed')%");
                return;
            }
            $.post(
                '%PATH%groups/members/handle/',
                {
                    'action': target.data('action'),
                    'groupId': target.data('groupId'),
                    'profileId': target.data('userId')
                },
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                       $('#unconfirmed-member-' + data.user).remove();
                    } else if (data.message != undefined) {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('group.members.unconfirmed.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('group.members.unconfirmed.failed')%");
                });
        },
        remove: function(event) {
            event.preventDefault();
            var target = $(event.target);
            if (target.data('groupId').length == 0 || target.data('userId').length == 0) {
                alert("%GETTEXT('group.members.action.failed')%");
                return;
            }
            if (confirm("%GETTEXT('group.confirm.removeMember')%")) {
                $.post(
                    '%PATH%groups/members/remove/',
                    {
                        'groupId': target.data('groupId'),
                        'profileId': target.data('userId'),
                        'ban': 0
                    },
                    function(data, textStatus, jqXHR) {
                        if (data != null && data.success == true) {
                           $('#groupMemberProfilebox' + target.data('userId')).remove();
                        } else if (data.message != undefined) {
                            alert(data.message);
                        } else {
                            alert("%GETTEXT('group.members.action.failed')%");
                        }
                    }).fail(function() {
                        alert("%GETTEXT('group.members.action.failed')%");
                    });
            }
        },
        ban: function(event) {
            event.preventDefault();
            var target = $(event.target);
            if (target.data('groupId').length == 0 || target.data('userId').length == 0) {
                alert("%GETTEXT('group.members.action.failed')%");
                return;
            }
            if (confirm("%GETTEXT('group.confirm.banMember')%")) {
                $.post(
                    '%PATH%groups/members/remove/',
                    {
                        'groupId': target.data('groupId'),
                        'profileId': target.data('userId'),
                        'ban': 1
                    },
                    function(data, textStatus, jqXHR) {
                        if (data != null && data.success == true) {
                           $('#groupMemberProfilebox' + target.data('userId')).remove();
                        } else if (data.message != undefined) {
                            alert(data.message);
                        } else {
                            alert("%GETTEXT('group.members.action.failed')%");
                        }
                    }).fail(function() {
                        alert("%GETTEXT('group.members.action.failed')%");
                    });
            }
        },

        role: {
            add: function(event) {
                event.preventDefault();
                var target = $(event.target);
                if (target.data('groupId').length == 0 || target.data('userId').length == 0) {
                    alert("%GETTEXT('group.members.action.failed')%");
                    return;
                }
                if (confirm(event.data.confirm)) {
                    $.post(
                        '%PATH%groups/members/role/',
                        {
                            'groupId': target.data('groupId'),
                            'profileId': target.data('userId'),
                            'role': event.data.role,
                            'action': 'add'
                        },
                        function(data, textStatus, jqXHR) {
                            if (data != null && data.success == true) {
                               $('#groupMemberProfilebox' + target.data('userId')).replaceWith(data.profilebox);
                            } else if (data.message != undefined) {
                                alert(data.message);
                            } else {
                                alert("%GETTEXT('group.members.action.failed')%");
                            }
                        }).fail(function() {
                            alert("%GETTEXT('group.members.action.failed')%");
                        });
                }
            },
            remove: function(event) {
                event.preventDefault();
                var target = $(event.target);
                if (target.data('groupId').length == 0 || target.data('userId').length == 0) {
                    alert("%GETTEXT('group.members.action.failed')%");
                    return;
                }
                if (confirm(event.data.confirm)) {
                    $.post(
                        '%PATH%groups/members/role/',
                        {
                            'groupId': target.data('groupId'),
                            'profileId': target.data('userId'),
                            'role': event.data.role,
                            'action': 'remove'
                        },
                        function(data, textStatus, jqXHR) {
                            if (data != null && data.success == true) {
                               $('#groupMemberProfilebox' + target.data('userId')).replaceWith(data.profilebox);

                            } else if (data.message != undefined) {
                                alert(data.message);
                            } else {
                                alert("%GETTEXT('group.members.action.failed')%");
                            }
                        }).fail(function() {
                            alert("%GETTEXT('group.members.action.failed')%");
                        });
                }
            }
        }
    }
};
vc.groups.forum = {    
    init: function(forumId, page, postingAllowed, updateTimestamp) {
        vc.groups.header.init();
        vc.forum.init('%ENTITY_TYPE_GROUP_FORUM%', forumId, page, postingAllowed, updateTimestamp);
        
        if (postingAllowed) {
            $('body').on('click', 'a.subscribeForum', vc.groups.forum.actions.subscribeForum);
            $('body').on('click', 'a.unsubscribeForum', vc.groups.forum.actions.unsubscribeForum);
        }
    },
    
    actions: {
        subscribeForum: function(event) {
            event.preventDefault();
            var id = $(event.target).data('forumId');
            $.post('%PATH%subscription/add/',
                   {'entityType': '%ENTITY_TYPE_GROUP_FORUM%', 'entityId': id},
                   function(data, textStatus, jqXHR) {
                if (data.success) {
                    $('a.subscribeForum[data-forum-id=' + id + ']').hide();
                    $('a.unsubscribeForum[data-forum-id=' + id + ']').show();
                } else if(data.message) {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('group.forum.subscribe.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('group.forum.subscribe.failed')%");
            });
        },
        unsubscribeForum: function(event) {
            event.preventDefault();
            var id = $(event.target).data('forumId'),
                parentThread = $(event.target).parents('.jThread');
            $.post('%PATH%subscription/delete/',
                   {'entityType': '%ENTITY_TYPE_GROUP_FORUM%', 'entityId': id},
                   function(data, textStatus, jqXHR) {
                if (data.success) {
                    $('a.unsubscribeForum[data-forum-id=' + id + ']').hide();
                    $('a.subscribeForum[data-forum-id=' + id + ']').show();
                } else if(data.message) {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('group.forum.unsubscribe.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('group.forum.unsubscribe.failed')%");
            });
        }
    }
};
vc.groups.settings = {
    init: function() {
        vc.groups.header.init();
    }
 };