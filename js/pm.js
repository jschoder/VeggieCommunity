var vc = vc || {};

vc.pm = {
    threads: {
        min: 0,
        max: 0,
        map: [],
        filter: {}
    },
    
    messages: {
        min: 0,
        max: 0,
        filter: {}
    },
    
    design: '',
    activeHash: '',
    activeThread: 0,
    filterOutgoing: 0,
    profiles: null,

    title: ['', ''],
    
    f: [],
    fT: [],
    
    drafts: [],
    
    loadingThreads: false,
    loadingMessages: false,

    init: function (design, defaultContact, filterOutgoing, threads) {
        vc.pm.design = design;
        vc.pm.activeHash = '#' + defaultContact;
        vc.pm.activeThread = defaultContact;
        vc.pm.filterOutgoing = filterOutgoing;
        vc.pm.f = {};
        
        if (typeof atob === 'function') {
            $.each(JSON.parse(atob('%FILTER_KEYWORDS%')), function(index, values) {
                vc.pm.f[index] = [];
                $.each(values, function() {
                    vc.pm.f[index].push(new RegExp('(^|[^\w])' + this + '($|[^\w])', 'i'));
                });     
            });
        }
        
        $(document).ready(function() {
            vc.pm.title = vc.ui.title.split('|');
            
            if(defaultContact == 0) {
                $('#inboxControl .recipients').hide();
                $('.jPmMessages .jLoading').hide();
            }

            /* Switching the active thread based on the hash */
            window.setInterval(function () {
                if (window.location.hash != vc.pm.activeHash &&
                    window.location.hash != '#' &&
                    window.location.hash != '') {
                    if ($('#sidebars').css('position') === 'absolute') {
                        $('#sidebars').hide();
                    }
                
                    vc.pm.activeHash = window.location.hash;
                    vc.pm.activeThread = window.location.hash.replace(/#/g, '');
                    $('.jThread').removeClass('active');
                    $('#jThread' + vc.pm.activeThread).addClass('active');
                    $('.jPmMessages .jLoading').show();
                    $('.jPmMessages .jMessage').remove();
                    $('.jPmMessages .draft').remove();
                    vc.pm.messages.min = 0;
                    vc.pm.messages.max = 0;
                    vc.pm.drafts = [];
                    vc.pm.loadingMessages = false;
                    vc.pm.loadPreviousMessages();
                }
            }, 500);

            /* Selecting the default thread based on hash or first available thread */
            if(window.location.hash != '' &&
               window.location.hash != '#') {
                vc.pm.activeHash = window.location.hash;
                vc.pm.activeThread = window.location.hash.replace(/#/g, '');

            } else if(threads.length > 0) {
                vc.pm.activeHash = '#' + threads[0].contact.id;
                vc.pm.activeThread = threads[0].contact.id;
            }

            vc.pm.updateScrollbars();
            $(window).resize(vc.pm.updateScrollbars);
            $('.jReplyForm textarea').on('change keyup, paste input', vc.pm.updateTextarea);

            $('.scrollbar').enscroll({
                showOnHover: true,
                scrollIncrement: 40, 
                verticalTrackClass: 'track',
                verticalHandleClass: 'handle',
                addPaddingToPane: false
            });
            
            $('#pmThreads form.threadFilter').on('submit', vc.pm.filterThreads);

            /* Filling the thread view */
            vc.pm.fillThreads(threads, true);
            /* Check if enough threads are visible to scroll another one */
            if($('.jPmThreads').height() < $('#inboxList').height()) {
                $('.jPmThreads .jLoading').hide();
            }
            if(vc.pm.activeThread > 0) {
                $('#jThread' + vc.pm.activeThread).addClass('active');
            }
            vc.pm.loadPreviousMessages();

            /* Auto reload threads */
            $('.jPmThreads').closest('.scrollbar').scroll(function(event) {
                var scrollArea = $('.jPmThreads'),
                    scrollBar = scrollArea.closest('.scrollbar');
                if(scrollArea.height() - (scrollBar.height() + scrollBar.scrollTop()) <= 25) {
                    vc.pm.loadNextThreads();
                }
            });

            /* Auto load messages */
            $('.jPmMessages').scroll(function(event) {
                var scrollBar = $('.jPmMessages');
                if(scrollBar.scrollTop() < 25) {
                    vc.pm.loadPreviousMessages();
                }
            });

            /* Setting up the actions */
            $('#inboxControl .actions').on('click', '.filter', vc.pm.actions.toggleFilter);
            $('#inboxControl .actions').on('click', '.block', vc.pm.actions.blockUsers);
            $('#inboxControl .actions').on('click', '.deleteConversation', vc.pm.actions.deleteConversation);

            $('#inboxControl .filterDialog form').on('submit', vc.pm.submitFilterForm);
            $('#inboxControl .filterDialog form button[type=\'reset\']').on('click', vc.pm.resetFilter);
            
            $('#inboxList .jNotifications').on('click', '.jClose', vc.pm.notifications.close);
            $('.jPmMessages').on('click', '.jMessage a.delete', vc.pm.actions.trashmail);
            $('.jPmMessages').on('click', '.jMessage a.flag', vc.pm.actions.markspam);
            
            $('#inboxList').on('click', '.draft .edit', vc.pm.draft.edit);
            $('#inboxList').on('click', '.draft .delete', vc.pm.draft.remove);
            
            $('#inboxList').on('click', '.undo', vc.pm.actions.undo);

            if (vc.pm.filterOutgoing) {
                $('.jReplyForm textarea').on('change keyup, paste input', vc.pm.updateWarning);
            }

            $('.jReplyForm').submit(vc.pm.submitForm);
            $('.jReplyForm .saveDraft').on('click', vc.pm.draft.add);

            vc.websocket.attach(
                '%ENTITY_TYPE_PM%',
                null,
                vc.pm.checkNewThreadsAndMessages,
                5000
            );
	});
    },
    
    updateTextarea: function(e) {
        if (localStorage) {
            var message = $(e.target).val();
            if (message === '') {
                localStorage.removeItem('pm.' + vc.pm.activeThread);
            } else {
                localStorage.setItem('pm.' + vc.pm.activeThread, message);
            }
        }
        vc.pm.updateScrollbars();
    },
    
    updateScrollbars: function() {
        $.each($('.scrollbar'), function() {
            var scrollbar = $(this),
                bodyW = scrollbar.closest('.bodyW'),
                scrollbarParent = scrollbar.parent(),
                height = $(bodyW).height(),
                inboxControl = $('#inboxControl');
            // Use the height of wrapping BodyW (which doesn't include header + footer)
            if (height !== null) {
                var main = $('main');
                if (main.length > 0) {
                    height -= main.innerHeight() - main.height();
                }
                if (scrollbarParent.attr('id') === 'inboxList') {
                    // Substract the inbox control which is located outside the parent to work with higher screens
                    // Don't substract if it is moved to the right side
                    if (inboxControl.css('float') === 'none') {
                        height -= inboxControl.outerHeight(true);
                    }
                }
                $.each(scrollbarParent.children(), function() {
                    var childElement = $(this);
                    // Substract all the heights of the different subcomponents unless they are 
                    // absolute positioned or the scrollbar itself. 
                    // (Presuming there is only one scrollbar per parent.)
                    if (!childElement.hasClass('scrollbar') &&
                        childElement.css('position') != 'absolute') {
                        height -= childElement.outerHeight(true);
                    }
                });
                $(scrollbar).css('height', height);
            }
        });
    },
    
    loadNextThreads: function() {
        if(vc.pm.loadingThreads == false) {
            vc.pm.loadingThreads = true;
            $.get('%PATH%pm/threads/', {'before': vc.pm.threads.min, 'f': vc.pm.threads.filter}, function(data, textStatus, jqXHR) {
                vc.pm.fillThreads(data.threads, true);
                if(data.isLast) {
                    $('.jPmThreads .jLoading').hide();
                }
            }) .fail(function(xhr, textStatus, errorThrown) {
                vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1001/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
            }).always(function() {
                vc.pm.loadingThreads = false;
            });
        }
    },
    
    filterThreads: function(event) {
        event.preventDefault();
        var nameFilter = $('input[name=namefilter]', event.target).val(),
            newFilter = $('input[name=newfilter]', event.target).is(':checked');
        vc.pm.threads.filter = {};
        if (nameFilter !== '') {
            vc.pm.threads.filter.name = nameFilter;
        }
        if (newFilter) {
            vc.pm.threads.filter.unread = true;
        }
        $('ul.jPmThreads .jThread').remove();
        vc.pm.threads.min = Math.ceil(Date.now() / 1000);
        vc.pm.threads.max = 0;
        vc.pm.threads.map = [];
        vc.pm.loadNextThreads();
    },

    fillThreads: function(threads, append) {
        var template = $('#jPmThreadsTemplate').html(),
            threadElement = $('.jPmThreads'),
            loadingElement = $('.jPmThreads .jLoading');
        $.each(threads, function(index, thread) {
            if(thread.contact.id == vc.pm.activeThread) {
                thread.activeThread = true;
            }
            $('#jThread' + thread.contact.id).remove();
            rendered = Mustache.to_html(template, thread);
            if(append) {
               loadingElement.before(rendered);
            } else {
                threadElement.prepend(rendered);
            }
            if(vc.pm.threads.min == 0 ||
               thread.created < vc.pm.threads.min) {
               vc.pm.threads.min = thread.created;
            }
            if(vc.pm.threads.max == 0 ||
               thread.created > vc.pm.threads.max) {
               vc.pm.threads.max = thread.created;
            }
            vc.pm.threads.map[thread.contact.id] = thread;
        });
        vc.timeago.update(threadElement);
    },

    loadPreviousMessages: function() {
        if(vc.pm.loadingMessages == false && vc.pm.activeThread > 0) {
            vc.pm.loadingMessages = true;
            $.get('%PATH%pm/messages/' + vc.pm.activeThread + '/',
                {'before': vc.pm.messages.min, 'f': vc.pm.messages.filter },
                function(data, textStatus, jqXHR) {
                    $('#jThread' + vc.pm.activeThread).removeClass('new');
                    vc.pm.profiles = data.profiles;
                    if(vc.pm.profiles[vc.pm.activeThread].isActive == false) {
                        $('.jReplyForm').hide();
                    } else {
                        if (data.blocked !== undefined) {
                            if (data.blocked === null) {
                                $('.jReplyForm').show();
                            } else {
                                vc.pm.notifications.add('warning', data.blocked);
                                $('.jReplyForm').hide();
                            }
                        }
                    }
                    
                    vc.pm.fillMessages(data.messages, (data.drafts === undefined ? [] : data.drafts), false);
                    if(data.isLast) {
                        $('.jPmMessages .jLoading').hide();
                    }
            }) .fail(function(xhr, textStatus, errorThrown) {
                vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1002/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
            }).always(function() {
                vc.pm.loadingMessages = false;
            });
        }
    },

    fillMessages: function(messages, drafts, append) {
        var actionsElement = $('#inboxControl .actions'),
            pdfExportElement = $('#inboxControl .actions .pdfExport'),
            conversationElement = $('.jConversation'),
            template = $('#jPmMessagesTemplate').html(),
            rendered = '',
            messagesElement = $('.jPmMessages'),
            loadingElement = $('.jPmMessages .jLoading'),
            firstFill = (vc.pm.messages.min == 0);

        actionsElement.data('contact', vc.pm.activeThread),
        conversationElement.html(vc.pm.profiles[vc.pm.activeThread].nickname);
        conversationElement.attr('href', '%PATH%user/view/' + vc.pm.activeThread + '/');
        // Plus is active if filterDialog is present
        if ($('#inboxControl .filterDialog').length > 0) {
            pdfExportElement.attr('href', '%PATH%pm/pdfexport/' + vc.pm.activeThread + '/');
        }
        $('#inboxControl .recipients').show();

        /* Rendering the messages */
        $.each(messages, function(index, message) {
            if(message.senderid == vc.pm.activeThread ||
               message.recipientid == vc.pm.activeThread) {
                $('#jMessage' + message.id).remove();
                message.sender = vc.pm.profiles[message.senderid];
                if(message.senderid == vc.pm.activeThread) {
                    message.received = true;
                }
                if(message.preFlag === null) {
                    message.preFlagMessage = null;
                } else {                    
                    if (message.preFlag === 'sx') {
                        message.preFlagMessage = "%GETTEXT('mailbox.warning.sex')%";
                    } else if (message.preFlag === 'insult') {
                        message.preFlagMessage = "%GETTEXT('mailbox.warning.insult')%";
                    } else {
                        message.preFlagMessage = null;
                    }
                }
                
                rendered = rendered + Mustache.to_html(template, message);
                if(vc.pm.messages.min == 0 ||
                   message.id < vc.pm.messages.min) {
                   vc.pm.messages.min = message.id;
                }
                if(vc.pm.messages.max == 0 ||
                   message.id > vc.pm.messages.max) {
                   vc.pm.messages.max = message.id;
                }
            }
        });
        
        $.each(drafts, function(id, body) {
            vc.pm.drafts[id] = body;
            $('.jPmMessages').append(Mustache.to_html(
                $('#jPmDraftTemplate').html(),
                { 
                    id: id,
                    type: 'store',
                    text: body 
                }
            ));
        });
        
        if (firstFill && localStorage) {
            var storedMessage = localStorage.getItem('pm.' + vc.pm.activeThread);
            if (storedMessage !== '' && storedMessage !== null) {
                rendered += Mustache.to_html(
                    $('#jPmDraftTemplate').html(),
                    { 
                        id: 'local',
                        type: 'local',
                        text: storedMessage 
                    }
                );
            }
        }
        
        if(append) {
            messagesElement.append(rendered);
        } else {
           loadingElement.after(rendered);
        }

        /* Scrolling to the right location */
        if(firstFill || append) {
            /* Scrolling to the bottom */
            var scrollBar = $('.jPmMessages'),
                scrollArea = $('.jPmMessages .jMessage, .jPmMessages .draft'),
                scrollAreaHeight = 0;
            $.each(scrollArea, function(index, message) {
                scrollAreaHeight = scrollAreaHeight + $(message).outerHeight(true);
            });
            scrollBar.scrollTop(scrollAreaHeight - scrollBar.height());
        } else {
            /* Scrolling to the same position as before */
            var scrollBar = $('.jPmMessages'),
                scrollPosition = scrollBar.scrollTop();
            $.each(messages, function(index, message) {
                scrollPosition = scrollPosition + $('#jMessage' + message.id).outerHeight(true);
            });
            scrollBar.scrollTop(scrollPosition);
        }
        vc.timeago.update(messagesElement);
        
        vc.ui.title = vc.pm.title[0] + '(' + vc.pm.profiles[vc.pm.activeThread].nickname + ') |' + vc.pm.title[1];
        vc.ui.updateTitle();
    },

    checkNewThreadsAndMessages: function() {
        $.get('%PATH%pm/threads/', 
              {'after': vc.pm.threads.max, 'f': vc.pm.threads.filter}, 
              function(data, textStatus, jqXHR) {
            if(data.threads.length > 0) {
                vc.pm.fillThreads(data.threads, false);
                var activeThreadUpdated = false;
                $.each(data.threads, function(index, thread) {
                   if(vc.pm.activeThread == thread.contact.id) {
                       activeThreadUpdated = true;
                   }
                });
                if(vc.pm.activeThread > 0 && activeThreadUpdated) {
                    $.get('%PATH%pm/messages/' + vc.pm.activeThread + '/',
                          {'after': vc.pm.messages.max, 'f': vc.pm.messages.filter },
                          function(data, textStatus, jqXHR) {
                        $('#jThread' + vc.pm.activeThread).removeClass('new');
                        vc.pm.profiles = data.profiles;
                        vc.pm.fillMessages(data.messages, [], true);
                    });
                }
            }
        });
    },
    
    submitFilterForm: function(event) {           
        event.preventDefault();
        var fromDateYearFilter = $('input[name="from[date][year]"]', event.target).val(),
            fromDateMonthFilter = $('input[name="from[date][month]"]', event.target).val(),
            fromDateDayFilter = $('input[name="from[date][day]"]', event.target).val(),
            fromTimeFilter = $('input[name="from[time]"]', event.target).val(),
            toDateYearFilter = $('input[name="to[date][year]"]', event.target).val(),
            toDateMonthFilter = $('input[name="to[date][month]"]', event.target).val(),
            toDateDayFilter = $('input[name="to[date][day]"]', event.target).val(),
            toTimeFilter = $('input[name="to[time]"]', event.target).val(),
            textFilter = $('input[name="textfilter"]', event.target).val(),
            filterActive = false;
        vc.pm.messages.filter = {};
        if (textFilter != '') {
            vc.pm.messages.filter.text = textFilter;
            filterActive = true;
        }
        try {
            if (fromDateYearFilter !== '' && fromDateMonthFilter !== '' && fromDateDayFilter !== '') {
                vc.pm.messages.filter.from = new Date(fromDateYearFilter, fromDateMonthFilter, fromDateDayFilter, 0, 0, 0, 0).getTime() / 1000;
                if (fromTimeFilter !== '') {
                    vc.pm.messages.filter.from += vc.date.parseTime(fromTimeFilter);
                }
                filterActive = true;
            }
            if (toDateYearFilter !== '' && toDateMonthFilter !== '' && toDateDayFilter !== '') {
                vc.pm.messages.filter.to = new Date(toDateYearFilter, toDateMonthFilter - 1, toDateDayFilter, 0, 0, 0, 0).getTime() / 1000;
                if (toTimeFilter !== '') {
                    vc.pm.messages.filter.to += vc.date.parseTime(toTimeFilter);
                }
                filterActive = true;
            }
            $('.jPmMessages .jLoading').show();
            $('.jPmMessages .jMessage').remove();
            $('.jPmMessages .draft').remove();
            vc.pm.messages.min = 0;
            vc.pm.messages.max = 0;
            vc.pm.loadingMessages = false;
            vc.pm.loadPreviousMessages();
            $('#inboxControl .filterDialog').hide();
            
            if (filterActive) {
                $('#inboxControl .actions a.filter').addClass('active');
            } else {
                $('#inboxControl .actions a.filter').removeClass('active');
            }
            
            
        } catch(exception) {
            vc.pm.notifications.add('error', "%GETTEXT('mailbox.filterfailed')%");
        }
    },
    
    resetFilter: function(event) {
        vc.pm.messages.filter = {};
        $('.jPmMessages .jLoading').show();
        $('.jPmMessages .jMessage').remove();
        $('.jPmMessages .draft').remove();
        vc.pm.messages.min = 0;
        vc.pm.messages.max = 0;
        vc.pm.loadingMessages = false;
        vc.pm.loadPreviousMessages();
        $('#inboxControl .actions a.filter').removeClass('active');
    },
    
    updateWarning: function (event) {
        var textarea = $('.jReplyForm textarea'),
            message = textarea.val().toLowerCase();
        $.each(vc.pm.f, function(type) {
            if (vc.pm.fT.indexOf(type) == -1) {
                $.each(this, function() {
                    if (message.match(this)) {
                        if (vc.pm.threads.map[vc.pm.activeThread].unfilter.indexOf(type) === -1) {
                            vc.pm.fT.push(type);
                            if (type === 'sx') {
                                vc.pm.notifications.add('warning', "%GETTEXT('mailbox.warning.sex')%");
                            } else if (type === 'insult') {
                                vc.pm.notifications.add('warning', "%GETTEXT('mailbox.warning.insult')%");
                            }
                        }
                    }
                });
            }
        });
    },

    submitForm: function(event) {
        event.preventDefault();
        var message = $('.jReplyForm textarea').val();
        if (message !== '') {
            vc.pm.sendMessage(message);
        }
    },
    
    sendMessage: function(message) {
        var textarea = $('.jReplyForm textarea'),
            submitButton = $('.jReplyForm button');
        textarea.prop('disabled', true);
        submitButton.hide();
        $('.jReplyForm .jLoading').show();
        textarea.trigger('keyup');
        $.post('%PATH%pm/add/',
               {'contact': vc.pm.activeThread,
                'message': message,
                'maxThread': vc.pm.threads.max,
                'maxMessage': vc.pm.messages.max },
        function(data, textStatus, jqXHR) {
            if(data.success == true) {
                if (localStorage) {
                    localStorage.removeItem('pm.' + vc.pm.activeThread);
                }
                textarea.val('');
                vc.pm.fillThreads(data.add.threads, false);
                vc.pm.fillMessages(data.add.messages, [], true);
            } else {
                vc.pm.notifications.add('error', data.message);
            }
        }) .fail(function(xhr, textStatus, errorThrown) {
            vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1003/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
        }).always(function() {
            textarea.prop('disabled', false);
            $('.jReplyForm .jLoading').hide();
            submitButton.show();
        });
    },

    notifications: {
        add: function(type, text, undo) {
            if (type == 'success') {
                type += ' notifySuccess';
            } else if (type == 'info') {
                type += ' notifyInfo';
            } else if (type == 'warning') {
                type += ' notifyWarn';
            } else if (type == 'error') {
                type += ' notifyError';
            }  
            var template = $('#jPmNotificationTemplate').html(),
                rendered = Mustache.to_html(
                    template, {
                        'type':type,
                        'text':text,
                        'undo':undo
                    }
                );
            $('#inboxList .jNotifications').append(rendered);
            $('#inboxList .jNotifications div:last').slideDown('slow');                
        },
        close: function(event) {
            $(event.target).parent('div').remove();
        }
    },

    actions: {
        blockUsers: function(event) {
            event.preventDefault();
            $.post(
                '%PATH%block/add/',
                {'profileid':vc.pm.activeThread},
                function(data, textStatus, jqXHR) {
                    if(data != null && data.success == true) {
                        vc.pm.notifications.add('info', "%GETTEXT('pm.block.success')%", 'block/' + vc.pm.activeThread);
                    } else if(data.message != undefined) {
                        vc.pm.notifications.add('error', data.message);
                    } else {
                        vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1012]");
                    }

                }).fail(function(xhr, textStatus, errorThrown) {
                    vc.pm.notifications.add("%GETTEXT('mailbox.remotecallfailed')% [#1013/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                });
        } ,
        
        deleteConversation: function(event) {
            event.preventDefault();
            if (confirm("%GETTEXT('pm.deleteConversation.confirm')%")) {
                var profileId = $(event.target).parents('.actions').data('contact');
                $.post(
                    '%PATH%pm/deleteall/',
                    {'profileId':profileId},
                    function(data, textStatus, jqXHR) {
                        if(data != null && data.success == true) {
                            if (localStorage) {
                                localStorage.removeItem('pm.' + vc.pm.activeThread);
                            }
                            $('#jThread' + profileId).remove();
                            $('.jPmMessages .jMessage').remove();
                            $('.jPmMessages .draft').remove();
                        } else if(data.message != undefined) {
                            vc.pm.notifications.add('error', data.message);
                        } else {
                            vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1004]");
                        }

                    }).fail(function(xhr, textStatus, errorThrown) {
                        vc.pm.notifications.add("%GETTEXT('mailbox.remotecallfailed')% [#1005/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                    });
            }
        },
        toggleFilter: function (event) {
            var filter = $('#inboxControl .filterDialog');
            if (filter.length > 0) {
                event.preventDefault();
                if (filter.is(':visible')) {
                    filter.slideUp('slow');
                } else {
                    filter.slideDown('slow');
                }
            }
        },
        markspam: function(event) {
            event.preventDefault();            
            var messageElement = $('#jMessage' + $(event.target).data('pmId'));
            if(confirm("%GETTEXT('mailbox.markspam.confirm')%")) {
                messageElement.slideUp('slow');
                mailids = new Array();
                mailids.push($(event.target).data('pmId'));
                $.post(
                    '%PATH%pm/flagspam/',
                    {'mails[]':mailids},
                    function(data, textStatus, jqXHR) {
                        if(data != null && data.success == true) {
                            /* Nothing to do */
                        } else if(data.message != undefined) {
                            vc.pm.notifications.add('error', data.message);
                            messageElement.show();
                        } else {
                            vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1006]");
                            messageElement.show();
                        }

                    }).fail(function(xhr, textStatus, errorThrown) {
                        vc.pm.notifications.add("%GETTEXT('mailbox.remotecallfailed')% [#1007/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                        messageElement.show();
                    });
                    
                if(vc.pm.activeThread && confirm("%GETTEXT('mailbox.markspam.block')%")) {
                    $.post(
                        '%PATH%block/add/',
                        {'profileid':vc.pm.activeThread},
                        function(data, textStatus, jqXHR) {
                            if(data != null && data.success == true) {
                                /* Nothing to do */
                            } else if(data.message != undefined) {
                                vc.pm.notifications.add('error', data.message);
                                messageElement.show();
                            } else {
                                vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1010]");
                                messageElement.show();
                            }

                        }).fail(function(xhr, textStatus, errorThrown) {
                            vc.pm.notifications.add("%GETTEXT('mailbox.remotecallfailed')% [#1011/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                            messageElement.show();
                        });
                }
            }
        },

        trashmail: function(event) {
            event.preventDefault();
            var messageElement = $('#jMessage' + $(event.target).data('pmId'));

            if(confirm("%GETTEXT('mailbox.trashmail.confirm')%")) {
                messageElement.slideUp('slow');

                mailids = new Array();
                mailids.push($(event.target).data('pmId'));
                $.post(
                    '%PATH%pm/delete/',
                    {'mails[]':mailids},
                    function(data, textStatus, jqXHR) {
                        if(data != null && data.success == true) {
                            /* Nothing to do */
                        } else if(data.message != undefined) {
                            vc.pm.notifications.add('error', data.message);
                            messageElement.show();
                        } else {
                            vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1008]");
                            messageElement.show();
                        }

                    }).fail(function(xhr, textStatus, errorThrown) {
                        vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1009/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                        messageElement.show();
                    });
            }
        },
        
        undo: function (event) {
            event.preventDefault();
            $.post(
                '%PATH%undo/' + $(this).data('action') + '/',
                {},
                function(data, textStatus, jqXHR) {
                    if(data != null && data.success == true) {
                        if(data.message != undefined) {
                            vc.pm.notifications.add('success', data.message);
                        }
                    } else if(data.message != undefined) {
                        vc.pm.notifications.add('error', data.message);
                    } else {
                        vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1017]");
                    }

                }).fail(function(xhr, textStatus, errorThrown) {
                    vc.pm.notifications.add("%GETTEXT('mailbox.remotecallfailed')% [#1018/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                });
        }
    },
    
    draft: {
        add: function(e) {
            e.preventDefault();
            var textarea = $('.jReplyForm textarea'),
                submitButton = $('.jReplyForm button'),
                message = textarea.val();
            if (message !== '') {
                textarea.prop('disabled', true);
                submitButton.hide();
                $('.jReplyForm .jLoading').show();
                textarea.trigger('keyup');
                $.post('%PATH%pm/draft/add/',
                       {'contact': vc.pm.activeThread,
                        'message': message },
                function(data, textStatus, jqXHR) {
                    if(data.success == true) {
                        textarea.val('');
                        $('.jPmMessages').append(Mustache.to_html(
                            $('#jPmDraftTemplate').html(),
                            { 
                                id: data.pmDraftId,
                                type: 'store',
                                text: message 
                            }
                        ));
                    } else {
                        vc.pm.notifications.add('error', data.message);
                    }
                }) .fail(function(xhr, textStatus, errorThrown) {
                    vc.pm.notifications.add('error', "%GETTEXT('mailbox.remotecallfailed')% [#1014/" + xhr.status + '/' + textStatus + '/' + errorThrown + ']');
                }).always(function() {
                    textarea.prop('disabled', false);
                    $('.jReplyForm .jLoading').hide();
                    submitButton.show();
                });
            }      
        },

        edit: function(e) {
            e.preventDefault();
            var draft = $(e.target).closest('.draft');
            if (draft.data('type') === 'local') {
                if (localStorage) {
                    $('.jReplyForm textarea').val(localStorage.getItem('pm.' + vc.pm.activeThread));
                }
            } else {
                $('.jReplyForm textarea').val(vc.pm.drafts[draft.data('id')]);
            }
        },

        remove: function(e) {
            e.preventDefault();
            var draft = $(e.target).closest('.draft');
            draft.remove();
            if (draft.data('type') === 'local') {
                if (localStorage) {
                    localStorage.removeItem('pm.' + vc.pm.activeThread);
                }
            } else {
                $.post(
                    '%PATH%pm/draft/delete/',
                    {'id':draft.data('id')},
                    function(data, textStatus, jqXHR) {
                        if(data != null && data.success == true) {
                            /* Nothing to do */
                        } else if(data.message != undefined) {
                            vc.pm.notifications.add('error', data.message);
                        } else {
                            vc.pm.notifications.add('error', "%GETTEXT('pm.draft.delete.error')% [#1015]");
                        }
                    }).fail(function(xhr, textStatus, errorThrown) {
                        vc.pm.notifications.add('error', "%GETTEXT('pm.draft.delete.error')% [#1016]");
                    });
            }
        }
    }
};