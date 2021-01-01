vc.ui = {
    title: '',
    titleCount: 0,
    passwords: [],

    init: function() {
        vc.ui.title = window.document.title;
        vc.ui.updateTitle();

        var newMessages = $('.jNewMessages'),
            openFriendRequests = $('.jOpenFriendRequests'),
            groupNotifications = $('.jGroupNotifications');
    
        $.datepicker.regional['de'] = {
            clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
            closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
            prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
            nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
            currentText: 'heute', currentStatus: '',
            monthNames: ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
            monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
            monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
            weekHeader: 'Wo', weekStatus: 'Woche des Monats',
            dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
            dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
            dateFormat: 'dd.mm.yy', firstDay: 1, 
            initStatus: 'Wähle ein Datum', isRTL: false
        };
        $.datepicker.setDefaults(
            $.extend(
                {
                    dateFormat:"%GETTEXT('javascript.ui.datepicker.format')%",
                    changeMonth: true,
                    changeYear: true
                },
                $.datepicker.regional["%GETTEXT('javascript.ui.datepicker.locale')%"]
            )
        );


        if(newMessages.length > 0 || openFriendRequests.length > 0 || groupNotifications.length > 0) {
            if (vc.websocket.key != null) {
                vc.websocket.attach(
                    '%ENTITY_TYPE_STATUS%',
                    null,
                    vc.ui.updateAccountStatus,
                    12000
                );
            }
        }
        vc.ui.updateUI();
        vc.ui.initJsTabs();
        vc.ui.initForms();
        
        $('body').on('click', 'a.jMenu', function(event) {
            event.preventDefault();
            $('#sidebars').toggle();
        });
        
        // :TODO: kill with lemongras
        $('#notify').on('mouseenter', '.popup.jHoverTrigger', vc.ui.loadReloadSubmenu);
        
        $('body').on('focus', '#notify .popup .jTrigger', vc.ui.closeMenu);
        $('body').on('focus', '.popup .jTrigger', vc.ui.loadReloadSubmenu);
        $('body').on('mousedown', '.popup .jTrigger', vc.ui.unfocus);
        $('body').on('click', '.notifyNews .close', vc.ui.notifyClose);
        $('body').on('click', 'nav.actionBar', vc.ui.toggleActionBar);
        $('body').on('change', 'select[data-filter-field]', vc.ui.filterSelectTrigger);
        $('body').on('click', '.jDeleteColor', vc.ui.deleteColor);
        
        $('body').on('click', '#blockTip nav .prev', {direction: -1}, vc.ui.changeTip);
        $('body').on('click', '#blockTip nav .next', {direction: 1}, vc.ui.changeTip);
        
        $('body').on('click', '.ajaxUpload .delete', vc.fileupload.remove);
        $('body').on('click', '.jFileuploadTrigger', vc.fileupload.trigger);
        $('body').on('click', '.ajaxUpload', vc.fileupload.open);
        $('body').on('click', '.ajaxUpload img', vc.fileupload.open);
        $('body').on('change', '.ajaxUploadSelect', vc.fileupload.upload);

        $('body').on('focus', 'form .location > input', vc.ui.form.location.focus);
        $('body').on('click', vc.ui.form.location.closePopup);
        $('body').on('click', 'form .location .addresspopup', vc.ui.form.location.clickPopup);
        $('body').on('change', 'form input[type=checkbox].jAutoSubmit', vc.ui.form.autoSubmit);
        $('body').on('focusout', 'form input[type=text].jAutoSubmit', vc.ui.form.autoSubmit);
        
        $('body').on('click', 'a.addFavorite', vc.favorites.add);
        $('body').on('click', 'a.deleteFavorite', vc.favorites.remove);
        
        $('body').on('click', 'a.addFriend', vc.friends.add);
        $('body').on('click', 'a.cancelFriend', vc.friends.cancel);
        $('body').on('click', 'a.confirmFriend', vc.friends.confirm);
        $('body').on('click', 'a.denyFriend', vc.friends.deny);
        $('body').on('click', 'a.deleteFriend', vc.friends.remove);
        
        $('body').on('click', 'a.jSet', vc.ui.set);
        
        $('body').on('click', 'a.jSubmit', vc.ui.confirmForm);
        $('body .cookies').on('click', '.close', vc.ui.cookieConfirm);
       
        $('body').on('keyup', '.datepicker .day,.datepicker .month,.datepicker .year', vc.ui.updateDateFocus);
        $('body').on('paste', '.datepicker .day,.datepicker .month,.datepicker .year', vc.ui.updateDateFocus);
        $('body').on('change', '.datepicker .day,.datepicker .month,.datepicker .year', vc.ui.updateDateFocus);
        $('body').on('input', '.datepicker .day,.datepicker .month,.datepicker .year', vc.ui.updateDateFocus);
        
        $('body .cookies').on('click', '.close', vc.ui.cookieConfirm);
        
        vc.ui.collapse.init();
    },

    updateAccountStatus: function() {
        var newMessagesWrapper = $('.jNewMessages'),
            newMessages = $('.jNewMessages span'),
            modsWrapper = $('.jMods'),
            mods = $('.jMods span'),
            openFriendRequestsWrapper = $('.jOpenFriendRequests'),
            openFriendRequests = $('.jOpenFriendRequests span'),
            eventNotificationsWrapper = $('.jEventNotifications'),
            eventNotifications = $('.jEventNotifications span'),
            groupNotificationsWrapper = $('.jGroupNotifications'),
            groupNotifications = $('.jGroupNotifications span'),
            ticketNotificationsWrapper = $('.jTicketNotifications'),
            ticketNotifications = $('.jTicketNotifications span'),
            ticketLink = $('a', ticketNotificationsWrapper.parent());
        $.get('%PATH%account/status/', [], function(data, textStatus, jqXHR) {
            var totalCount = 0;
            if(data.messages == undefined || data.messages == 0) {
                newMessagesWrapper.hide();
            } else {
                totalCount = totalCount + data.messages;
                newMessages.text(data.messages);
                newMessagesWrapper.show();
            }
            if(data.friends == undefined || data.friends == 0) {
                openFriendRequestsWrapper.hide();
            } else {
                totalCount = totalCount + data.friends;
                openFriendRequests.text(data.friends);
                openFriendRequestsWrapper.show();
            }
            if(data.friends == undefined || data.friends == 0) {
                openFriendRequestsWrapper.hide();
            } else {
                totalCount = totalCount + data.friends;
                openFriendRequests.text(data.friends);
                openFriendRequestsWrapper.show();
            }
            if(data.events == undefined || data.events == 0) {
                eventNotificationsWrapper.hide();
            } else {
                totalCount = totalCount + data.events;
                eventNotifications.text(data.events);
                eventNotificationsWrapper.show();
            }
            if(data.groups == undefined || data.groups == 0) {
                groupNotificationsWrapper.hide();
            } else {
                totalCount = totalCount + data.groups;
                groupNotifications.text(data.groups);
                groupNotificationsWrapper.show();
            }
            
            if(data.tickets == undefined || data.tickets == 0) {
                ticketLink.attr('href', ticketLink.data('inactive'));
                ticketNotificationsWrapper.hide();
            } else {
                ticketLink.attr('href', ticketLink.data('active'));
                totalCount = totalCount + data.tickets;
                ticketNotifications.text(data.tickets);
                ticketNotificationsWrapper.show();
            }
            if(data.mod !== undefined) {
                var modCount = data.mod.tickets + data.mod.real + data.mod.spam + data.mod.groups;
                if(modCount == 0) {
                    modsWrapper.hide();
                } else {
                    totalCount = totalCount + modCount;
                    mods.text(data.mod.tickets + '|' + (data.mod.real + data.mod.spam + data.mod.groups));
                    modsWrapper.show();
                }
                $('.jModTickets span').text(data.mod.tickets);
                $('.jModPms span').text(data.mod.pm);
                $('.jModReals span').text(data.mod.real);
                $('.jModSpam span').text(data.mod.spam);
                $('.jModFlag span').text(data.mod.flag);
                $('.jModGroups span').text(data.mod.groups);
                $('.jModPicsUnchecked span').text(data.mod.picsUnchecked);
                $('.jModPicsPrewarned span').text(data.mod.picsPrewarned);
                $('.jModToldAFriend span').text(data.mod.toldafriend);
            }
            vc.ui.titleCount = totalCount;
            vc.ui.updateTitle();
        });
    },
    
    updateTitle: function() {
        $(document).ready(function() {
            if(vc.ui.titleCount == 0) {
                window.document.title = vc.ui.title;
            } else {
                window.document.title = '(' + vc.ui.titleCount + ') ' + vc.ui.title;
            }
        });
    },

    initJsTabs: function() {
        $('.jTabs').each(function(index, tabParent) {
            $('a', tabParent).bind('click', function(event) {
                event.preventDefault();

                /* Hide everything first */
                $('a', tabParent).each(function(index, tab) {
                    $('#' + $(tab).data('tab')).hide();
                    $(tab).removeClass('active');
                });

                /* Display the new tab */
                var newTab = $('#' + $(event.target).data('tab'));
                newTab.show();
                /* Updating the height of all selected tabs */
                $('textarea', newTab).trigger('input');
                $(event.target).addClass('active');
            });
        });
    },

    updateUI: function() {
	$('a.jZoom').fancybox({
            'titlePosition': 'over',
            'transitionIn': 'elastic',
            'transitionOut': 'elastic',
            'hideOnContentClick': true,
            'padding': 0,
            helpers: {
                overlay: {
                    locked: false
                }
            },
            'beforeShow': function () {
                /* Disable right click */
                $.fancybox.wrap.bind('contextmenu', function (e) {
                    return false;
                });
                /* Disable drag */
                $.fancybox.wrap.bind('dragstart', function (e) {
                    return false;
                });
            }
        });
        
        vc.ui.initAutoHeight($('textarea.jAutoHeight'));
        
        $('.datepicker').each(function(index, datepicker) {
            var element = $(datepicker),
                start = new Date(),
                end = new Date();
            start.setTime(start.getTime() + element.data('date-start') * 86400000);
            end.setTime(end.getTime() + element.data('date-end') * 86400000);
            $('input[type=text]', datepicker).datepicker({
                minDate: element.data('date-start'), 
                maxDate: element.data('date-end'),
                yearRange: start.getFullYear() + ':' + end.getFullYear(),
                onSelect: function(dateText, inst) {
                    var datepicker = inst.input.closest('.datepicker');
                    $('.day', datepicker).val(inst.selectedDay);
                    $('.month', datepicker).val(inst.selectedMonth + 1);
                    $('.year', datepicker).val(inst.selectedYear);
                }
            });
        });
        $('.clockpicker').clockpicker({
            placement: 'top',
            autoclose: true
        });
        $('.colorpicker').minicolors({ 
            letterCase: 'uppercase'
        });
    },
    
    updateDateFocus: function(e) {
        var field = $(this),
            datepicker = field.closest('.datepicker');
        if (field.val() == '' && e.keyCode == 8) {
            if (field.hasClass('month')) {
                $('.day', datepicker).focus();
            } else if (field.hasClass('year')) {
                $('.month', datepicker).focus();
            }
        } else {
            if (field.hasClass('day') && field.val() > 3) {
                $('.month', datepicker).focus();
            } else if (field.hasClass('month') && field.val() > 1) {
                $('.year', datepicker).focus();
            }
        }
    },

    initForms: function() {
        $.each($('select[data-filter-field]'), function(index, element) {
            vc.ui.filterSelectUpdate($(element));
        });
        $.each($('.rows'), function(index, multiple) {
            $('input[name="' + $(multiple).data('delete') + '"]').on('change', function(event) {
                vc.ui.updateFormsMultiple($(event.target).parents('.rows'));
            });
            vc.ui.updateFormsMultiple(multiple);
        });
        $('.dnd').sortable({
            axis: 'y',
            handle: '.sorter',
            create: vc.ui.sortableUpdated,
            update: vc.ui.sortableUpdated
        });
        var passwordFields = $('input[name="password[0]"]');
        if (passwordFields.length) {
            $.get('/files/passwords.json',
                {},
                function(data, textStatus, jqXHR) {
                    vc.ui.passwords = data;
                });
            passwordFields.on('change keyup, paste input', vc.ui.updatePasswordStrength);
            passwordFields.trigger('keyup', [13]); 
        }
    },
    
    initAutoHeight: function(elements) {        
        $.each(elements, function(index, element) {
            var offset = element.offsetHeight - element.clientHeight;
            $(element).on('change keyup, paste input', function() { 
                vc.ui.updateAutoHeight(this, offset); 
            }).removeClass('jAutoHeight');

            vc.ui.updateAutoHeight(this, offset);
        });
    },
    
    updateAutoHeight: function(el, offset) {
        var element = $(el),
            newHeight = el.scrollHeight + offset + 'px';
        if (element.val() === '') {
            element.css('height', 'auto');
            
        } else if (element.css('height') !== newHeight) {
            element.css('height', 'auto').css('height', newHeight);
        }
    },

    updateFormsMultiple: function(multiple) {
        var deleteElementName = $(multiple).data('delete');
            existingElements = $('input[name="' + deleteElementName + '"]', multiple).length,
            deletedElements = $('input[name="' + deleteElementName + '"]:checked', multiple).length,
            remainingElements = existingElements - deletedElements,
            newElements = $('.jNewMultiple', multiple),
            displayNewElements = newElements.length - remainingElements;
        $.each(newElements, function(index, newRow) {
            if (index < displayNewElements) {
                $(newRow).show();
            } else {
                $(newRow).hide();
            }
        });
    },
    
    sortableUpdated: function(event, ui) {
        $.each($('.row', $(event.target).parent()), function(index, row) {
            $('input[type="hidden"].jWeight', row).val(index + 1);
        });
    },
    
    updatePasswordStrength: function(event) {
        var passwordField = $(event.target),
            form = passwordField.closest('form'),
            password = passwordField.val(),
            passwordStrength = $('.passwordStrength', passwordField.closest('li')),
            passwordStrengthBar = $('.bar div', passwordStrength),
            passwordStrengthVerdict = $('.verdict', passwordStrength),
            passwordChecklist = $('ul', passwordStrength);
        // Triple check doesn't work correctly
        if (password === '') {
            if (passwordStrength.is(':visible')) {
                passwordStrength.slideUp('slow');
            }
            
        } else {    
            var strength = vc.ui.evaluatePasswordStrength(
                $('input[name="nickname"]', form).val(),
                $('input[name="email"]', form).val(),
                $('input[name="city"]', form).val(),
                password,
                passwordStrength.data('specialchars')
            );
            passwordStrengthBar.attr('class', strength.indicatorClass);
            passwordStrengthBar.css('width', Math.max(20, strength.strength) + '%');
            passwordStrengthVerdict.text(strength.indicatorText);

            if (strength.message.length === 0) {
                passwordChecklist.hide();
                passwordChecklist.html(''); 
            } else {
                passwordChecklist.html('<li>' + strength.message.join('</li><li>') + '</li>');
                passwordChecklist.show();
            }

            if (!passwordStrength.is(':visible')) {
                passwordStrength.slideDown('slow');
            }
        }
    },
    
    /* Based on Drupal 8 user.js. Big changes.. Don't simply copy this! */
    evaluatePasswordStrength: function (username, email, city, password, specialchars) {
        password = password.trim();
        var indicatorText;
        var indicatorClass;
        var weaknesses = 0;
        var strength = 100;
        var msg = [];

        var hasLowercase = /[a-z]/.test(password);
        var hasUppercase = /[A-Z]/.test(password);
        var hasNumbers = /[0-9]/.test(password);
        var hasPunctuation = /[^a-zA-Z0-9]/.test(password);
        
        var passwordInBlacklist = false;
        $.each(vc.ui.passwords, function() {
            if (password == this) {
                passwordInBlacklist = true;
                return false;
            }
        });
        if (passwordInBlacklist) {
            msg.push("%GETTEXT('form.passwordStrength.tip.custom.blacklist')%");
            // Blacklisted passwords are always very weak.
            strength = 5;
        } 
        
        // Check if password is the same as the username, email or
        var passwordLowerCase = password.toLowerCase(),
            username = username.toLowerCase(),
            email = email.toLowerCase(),
            city = city.toLowerCase();
        
        if (password !== '' && (
                passwordLowerCase === username ||
                username.indexOf(passwordLowerCase) !== -1
            )) {
            msg.push("%GETTEXT('form.passwordStrength.tip.custom.username')%");
            // Passwords the same as username are always very weak.
            strength = 5;
        } else if (email !== '' && (
                passwordLowerCase === email ||
                email.indexOf(passwordLowerCase) !== -1
            )) {
            msg.push("%GETTEXT('form.passwordStrength.tip.custom.email')%");
            // Passwords the same as email are always very weak.
            strength = 5;
        } else if (city !== '' && (
                passwordLowerCase === city ||
                city.indexOf(passwordLowerCase) !== -1 ||
                passwordLowerCase.indexOf(city) !== -1
            )) {
            msg.push("%GETTEXT('form.passwordStrength.tip.custom.city')%");
            // Passwords the same as city are always very weak.
            strength = 5;
        }

        if (password.length < 5) {
            // Passwords below 5 chars are alway weak
            msg.push("%GETTEXT('form.passwordStrength.tip.length')%");
            strength = 5;
        } else if (password.length < 12) {
            // Lose 5 points for every character less than 12
            msg.push("%GETTEXT('form.passwordStrength.tip.length')%");
            strength -= ((12 - password.length) * 5);
        }

        // Count weaknesses.
        if (!hasLowercase) {
            msg.push("%GETTEXT('form.passwordStrength.tip.lowercase')%");
            weaknesses++;
        }
        if (!hasUppercase) {
            msg.push("%GETTEXT('form.passwordStrength.tip.uppercase')%");
            weaknesses++;
        }
        if (!hasNumbers) {
            msg.push("%GETTEXT('form.passwordStrength.tip.numbers')%");
            weaknesses++;
        }
        if (!hasPunctuation) {
            msg.push(
                "%GETTEXT('form.passwordStrength.tip.specialchars')%" + 
                '<span class="specialchars">' + specialchars + '</span>'
            );
            weaknesses++;
        }

        // Apply penalty for each weakness (balanced against length penalty).
        switch (weaknesses) {
            case 1:
                strength -= 12.5;
                break;
            case 2:
                strength -= 25;
                break;
            case 3:
                strength -= 40;
                break;
            case 4:
                strength -= 40;
                break;
        }


        // Based on the strength, work out what text should be shown by the
        // password strength meter.
        if (strength < 60) {
            indicatorText = "%GETTEXT('form.passwordStrength.weak')%";
            indicatorClass = 'weak';
        }
        else if (strength < 70) {
            indicatorText = "%GETTEXT('form.passwordStrength.fair')%";
            indicatorClass = 'fair';
        }
        else if (strength < 80) {
            indicatorText = "%GETTEXT('form.passwordStrength.good')%";
            indicatorClass = 'good';
        }
        else if (strength < 100) {
            indicatorText = "%GETTEXT('form.passwordStrength.strong')%";
            indicatorClass = 'strong';
        }
        else if (strength === 100) {
            indicatorText = "%GETTEXT('form.passwordStrength.verystrong')%";
            indicatorClass = 'verystrong';
        }
 
        return {
            strength: strength,
            message: msg,
            indicatorText: indicatorText,
            indicatorClass: indicatorClass
        };
    },
    
    closeMenu: function(event) {
        if ($('#sidebars').css('position') === 'absolute') {
            $('#sidebars').hide();
        }
    },

    loadReloadSubmenu: function(event) {
        $.each($('.jReload', $(event.target).parent()), function(index, element) {
            var reloadElement = $(element),
                url = reloadElement.data('url');
                reloadElement.html("<li class=\"jLoading\">%GETTEXT('js.loading')%</li>");
                $.get(url,
                    {},
                    function(data, textStatus, jqXHR) {
                        reloadElement.html(data);
                        vc.timeago.update(reloadElement);
                    }) .fail(function() {
                        reloadElement.html("<li class=\"failed\"><p>%GETTEXT('menu.reload.failed')%</p></li>");
                    });
        });
    },
    
    toggleActionBar: function(event) {
        var bar = $(event.target);
        if (bar.prop('tagName') == 'NAV') {
            event.preventDefault();
            if (bar.hasClass('expanded')) {
                bar.removeClass('expanded');
            } else {
                bar.addClass('expanded');
            }
            $('a span', bar).animate({width:'toggle'},1000);
        }
    },
    
    notifyClose: function(event) {
        event.preventDefault();
	$.post(
            $(event.target).data('url'),
            [],
            function(data, textStatus)
            {
                if (data.success) {
                    $(event.target).closest('div').hide();
                }
            }
	);
    },
    
    unfocus: function(event) {
        var trigger = $(event.target);
        if (trigger.is(':focus')) {
            event.preventDefault();
            trigger.blur();
        }
    },
    
    filterSelectTrigger: function(event) {
        vc.ui.filterSelectUpdate($(event.target));
    },
    
    deleteColor: function(event) {
        event.preventDefault();
        $('input[name="colors[' + $(event.target).data('color') + ']"]').minicolors('value', '');
    },
    
    changeTip: function(event) {
        event.preventDefault();
        
        var jsTip = $('.jsTip', $(event.target).closest('#blockTip')),
            newIndex = jsTip.data('current') + event.data.direction;
        if (newIndex < 0) {
            newIndex = vc.block.tips.length - 1;
        } else if (newIndex >= vc.block.tips.length) {
            newIndex = 0;
        }
        
        $('.index', jsTip).text(newIndex + 1);
        $('.body', jsTip).text(vc.block.tips[newIndex]);
        jsTip.data('current', newIndex);
    },
    
    filterSelectUpdate: function(select) {
        var selectedCountry = $('option:selected', select).data('filter-group'),
            filterField = $('select[name="' + select.data('filter-field') + '"]', select.closest('form')),
            optgroups = $('optgroup', filterField);

        if (selectedCountry == undefined) {
            filterField.val('');
        } else {
            var activeGroupId = '#' + filterField.attr('id') + '-g-' + selectedCountry,
                activeGroup = $(activeGroupId, filterField);        
            optgroups = optgroups.not(activeGroupId);
            activeGroup.show();
            // Reset option to empty if the currently selected region is not available
            if (activeGroup.has('option[value="' + filterField.val() + '"]').length == 0) {
                filterField.val('');
            }
        }
        optgroups.hide();
    },
    
    set: function(event) {
        event.preventDefault();
        var target = $(event.target);
        $.post(
            '%PATH%account/set/' + target.data('set') + '/',
            { },
            function(data, textStatus, jqXHR) {
                
                var hideElementSelector = target.data('hide'),
                    hideElementMessage = target.data('hide-message');
                if (hideElementSelector !== undefined) {
                    var hideElement = target.closest(hideElementSelector);                    
                    if (hideElement.length == 1) {
                        hideElement.hide();
                        if (hideElementMessage !== undefined) {
                            alert(hideElementMessage);
                        }
                    }
                }
                
                // Special behavior for notifyNews
                var notifyNewsElement = target.closest('.notifyNews');
                if (notifyNewsElement.length == 1) {
                    $('.close', notifyNewsElement).trigger('click');
                }
            });
    },
    
    // Messing with code scanners
    printMil: function(e, end, top, start) {
        var t = end + '.' + top + '">' + start;
        t = 'lto:' + start + "@" + t;
        t = t + "@" + end + '.' + top;
        t = t + "</a>";
        t = '<a href="mai' + t;
        $('.' + e).html(t);
    },
    
    openAll: function() {
        if (confirm("%GETTEXT('result.openall.confirm')%") == true) {
            for(var i = 0; i < vc.ui.profilesToOpen.length; i++) {
                window.open('%PATH%user/view/' + vc.ui.profilesToOpen[i] + '/');
            } 
        }
    },
    
    confirmForm: function(e) {
        e.preventDefault();
        $(e.target).closest('form').submit();
    },
    
    collapse: {
        init: function() {
            if (localStorage) {
                var collapseMenuStorage = localStorage.getItem('collapseMenu');
                if (collapseMenuStorage !== null && collapseMenuStorage !== '') {
                    $.each(collapseMenuStorage.split(','), function(index, value) {
                        if (value !== '') {
                            $('#' + value).hide();
                            $('.collapsible[data-area="' + value + '"]').removeClass('collapsible').addClass('collapsed');
                        }
                    });
                }
                
                var collapseBlockStorage = localStorage.getItem('collapseBlock');
                if (collapseBlockStorage !== null && collapseBlockStorage !== '') {
                    $.each(collapseBlockStorage.split(','), function(index, value) {
                        if (value !== '') {
                            $('#' + value).removeClass('collapsible').addClass('collapsed');
                        }
                    });
                }
            }
            
            $('body').on('click', '.collapsible > h2, .collapsible > h3', {store: false}, vc.ui.collapse.collapse);
            $('body').on('click', '.block.collapsible h3',  {store: true}, vc.ui.collapse.collapse);
            $('body').on('click', '.collapsed > h2, .collapsed > h3',  {store: false}, vc.ui.collapse.expand);
            $('body').on('click', '.block.collapsed h3',  {store: true}, vc.ui.collapse.expand);
            $('body').on('click', '#userNav nav .collapsible, #userNav nav .collapsed', vc.ui.collapse.toggleCollapse);
        },
    
        collapse: function(event) {
            var block = $(event.target).closest('.collapsible'),
                content = $('> div', block),
                id = block.attr('id');
            content.slideUp('fast');
            block.addClass('collapsed');
            block.removeClass('collapsible');
            if (id && event.data.store) {
                vc.localStorage.add('collapseBlock', id);
            }
        },

        expand: function(event) {
            var block = $(event.target).closest('.collapsed'),
                content = $('> div', block),
                id = block.attr('id');
            content.slideDown('fast');
            block.addClass('collapsible');
            block.removeClass('collapsed');
            if (id && event.data.store) {
                vc.localStorage.remove('collapseBlock', id);
            }
        },

        toggleCollapse: function(event) {
            var element = $(event.target),
                areaId = element.data('area'),
                area = $('#' + areaId);
            if (element.hasClass('collapsed')) {
                area.slideDown('fast');
                element.addClass('collapsible');
                element.removeClass('collapsed');
                vc.localStorage.remove('collapseMenu', element.data('area'));

            } else {
                area.slideUp('fast');
                element.addClass('collapsed');
                element.removeClass('collapsible');
                vc.localStorage.add('collapseMenu', element.data('area'));
            }
        }
    },
    
    form: {
        autoSubmit: function(event) {
            $(event.target).closest('form').submit();
        },
        
        location: {
            focus: function(event) {
                var locationElement = $(event.target).closest('.location'),
                    addressPopup = $('.addresspopup', locationElement),
                    formCaption = $('.addresspicker', locationElement),
                    popupCaption = $('#' + formCaption.attr('id') + '-caption');
            
                if (!addressPopup.is(':visible')) {
                    popupCaption.val(formCaption.val());
                    formCaption.val('');
                    formCaption.prop('readonly', true);
                    addressPopup.slideDown();
                }
            },
            
            clickPopup: function(event) {
                event.stopPropagation();
            },
            
            closePopup: function(event) {
                if($(event.target).closest('.location').length == 0) {
                    var locationElement = $('.addresspopup:visible').closest('.location');
                    if (locationElement.length > 0) {
                        var addressPopup = $('.addresspopup', locationElement),
                            formCaption = $('.addresspicker', locationElement),
                            popupCaption = $('#' + formCaption.attr('id') + '-caption');
                        if (addressPopup.is(':visible')) {
                            formCaption.val(popupCaption.val());
                            popupCaption.val('');
                            formCaption.prop('readonly', false);
                            addressPopup.slideUp();
                        }
                    }
                }
            }
        }
    },
    
    cookieConfirm: function(event) {
        event.preventDefault();
        $('.cookies').slideUp('slow');
        var expiry = new Date(new Date().getTime() + 31536000000); // 1 year
        document.cookie='cc=1; path=/; expires=' + expiry.toGMTString();
    }
};

$(document).ready(function() {
    vc.ui.init();
});
