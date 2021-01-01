var vc = vc || {};

vc.timeago = {
    
    strings: {
        seconds: "%GETTEXT('timeago.seconds')%",
        minute: "%GETTEXT('timeago.minute')%",
        minutes: "%GETTEXT('timeago.minutes')%",
        hour: "%GETTEXT('timeago.hour')%",
        hours: "%GETTEXT('timeago.hours')%",
        day: "%GETTEXT('timeago.day')%",
        days: "%GETTEXT('timeago.days')%",
        date: "%GETTEXT('timeago.date')%"
    },
    
    init: function() {
        window.setInterval(function () {
            vc.timeago.update();
        }, 15000);
        vc.timeago.update(null);
    },
    
    update: function(parent) {
        
        $('span.jAgo', parent).each(function(index, value) {
            
            var element = $(value),
                nowDate = new Date();
                now = nowDate.getTime();
                difference = now - ($(element).data('ts') * 1000);
                
            var fullDate = "%GETTEXT('timeago.full')%",
                then = new Date();
            then.setTime($(element).data('ts') * 1000);
            
            var month = then.getMonth() + 1;
            if(month < 10) {
                month = '0' + month;
            }
            var day = then.getDate();
            if(day < 10) {
                day = '0' + day;
            }
            var hours = then.getHours();
            if(hours < 10) {
                hours = '0' + hours;
            }
            var minutes = then.getMinutes();
            if(minutes < 10) {
                minutes = '0' + minutes;
            }
            var seconds = then.getSeconds();
            if(seconds < 10) {
                seconds = '0' + seconds;
            }
            fullDate = fullDate.replace(/Y/, then.getFullYear())
                               .replace(/m/, month)
                               .replace(/d/, day)
                               .replace(/H/, hours)
                               .replace(/i/, minutes)
                               .replace(/s/, seconds);
            element.attr('title', fullDate);
            
            if(difference > 604800000) { /* More than a week */
                text = vc.timeago.strings.date
                               .replace(/Y/, then.getFullYear())
                               .replace(/m/, month)
                               .replace(/d/, day)
                               .replace(/H/, hours)
                               .replace(/i/, minutes)
                               .replace(/s/, seconds);
            } else if(difference > 172800000) { /* More than a day */
                text = vc.timeago.strings.days;
                text = text.replace(/%d/, Math.round(difference / 86400000));
            } else if(difference > 86400000) { /* More than 24 hours */
                text = vc.timeago.strings.day;
            } else if(difference > 7200000) { /* More than a hour */
                text = vc.timeago.strings.hours;
                text = text.replace(/%d/, Math.round(difference / 3600000));
            } else if(difference > 3300000) { /* More than a 55 minutes */
                text = vc.timeago.strings.hour;
            } else if(difference > 90000) { /* More than a 90 seconds */
                text = vc.timeago.strings.minutes;
                text = text.replace(/%d/, Math.round(difference / 60000));
            } else if(difference > 50000) { /* More than 50 seconds */
                text = vc.timeago.strings.minute;
            } else { /* Less than 50 seconds */
                text = vc.timeago.strings.seconds;
            } 
            element.text(text);
        });
    }
};

$(document).ready(function() {
    vc.timeago.init();
});