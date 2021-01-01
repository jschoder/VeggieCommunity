vc.forum = {
    contextType: '',
    contextId: '',
    page: 0,
    updateTimestamp: 0,

    init: function(contextType, contextId, page, postingAllowed, updateTimestamp) {
        vc.forum.contextType = contextType;
        vc.forum.contextId = contextId;
        vc.forum.page = page;
        vc.forum.checkUpdatesTimestamp = updateTimestamp;
                
        $(document).ready(function() {
            $('.jsForum').on('click', '.jThread .threadArticle  .flag', {entityType: '%ENTITY_TYPE_FORUM_THREAD%'}, vc.forum.actions.flag);
            $('.jsForum').on('click', '.jThreadComment .flag', {entityType: '%ENTITY_TYPE_FORUM_COMMENT%'}, vc.forum.actions.flag);
            $('.jsForum').on('click', '.jComments .loadMoreComments a', vc.forum.actions.loadMoreComments);

            if (postingAllowed) {
                $('#newthread form').submit(vc.forum.addThread);
                $('.jThreads').on('submit' , '.newcomment form', vc.forum.addComment);
                
                $('.jsForum').on('click', '.jThread .threadArticle .subscribe', vc.forum.actions.subscribeThread);
                $('.jsForum').on('click', '.jThread .threadArticle .unsubscribe', vc.forum.actions.unsubscribeThread);
                $('.jsForum').on('click', '.jThread .threadArticle .edit', vc.forum.actions.editThread);
                $('.jsForum').on('submit' , '.jThread .threadForm form', vc.forum.actions.saveThreadEdit);
                $('.jsForum').on('click' , '.jThread .threadForm form .cancel', vc.forum.actions.cancelThreadEdit);
                $('.jsForum').on('click', '.jThread .threadArticle .delete', vc.forum.actions.deleteThread);
                $('.jsForum').on('click', '.jThread .threadArticle .hide', vc.forum.actions.hideThread);
                $('.jsForum').on('click', '.jThread .threadArticle .unflag', vc.forum.actions.unflag);
                $('.jsForum').on('click', '.jThread .threadArticle .jLike', {entityType: '%ENTITY_TYPE_FORUM_THREAD%', value: 1}, vc.forum.actions.like);
                $('.jsForum').on('click', '.jThread .threadArticle .jDislike', {entityType: '%ENTITY_TYPE_FORUM_THREAD%', value: -1}, vc.forum.actions.like);
                $('.jsForum').on('click', '.jThread .jAddComment', vc.forum.actions.addCommentForm);
                
                $('.jsForum').on('click', '.jThreadComment .edit', vc.forum.actions.editThreadComment);
                $('.jsForum').on('submit' , '.jThreadComment form', vc.forum.actions.saveCommentEdit);
                $('.jsForum').on('click' , '.jThreadComment form .cancel', vc.forum.actions.cancelCommentEdit);
                $('.jsForum').on('click', '.jThreadComment a.delete', vc.forum.actions.deleteThreadComment);
                $('.jsForum').on('click', '.jThreadComment a.unflag', vc.forum.actions.unflag);
                $('.jsForum').on('click', '.jThreadComment .jLike', {entityType: '%ENTITY_TYPE_FORUM_COMMENT%', value: 1}, vc.forum.actions.like);
                $('.jsForum').on('click', '.jThreadComment .jDislike', {entityType: '%ENTITY_TYPE_FORUM_COMMENT%', value: -1}, vc.forum.actions.like);

                vc.websocket.attach(
                    contextType,
                    contextId,
                    vc.forum.checkUpdates,
                    10000
                );
            }

            // Check for jump mark and load on demand
            if (window.location.hash != '' &&
                window.location.hash.indexOf('.') > -1) {
                var name = window.location.hash.substr(1);
                if ($('a[name="' + name + '"]').length == 0) {
                    var splitHash = name.split('.');
                    var threadElement = $('#thread-' + splitHash[0]);
                    if (threadElement.length > 0) {
                        vc.forum.actions.loadMoreCommentsByElement(
                            splitHash[0],
                            $('.jComments', threadElement),
                            $('.loadMoreComments', threadElement),
                            splitHash[1]
                        );
                    }
                }
            }
        });
    },

    addThread: function(event) {        
        event.preventDefault();
        var subjectField = $('input[name=subject]', event.target),
            pictureField = $('input[name=picture]', event.target),
            bodyField = $('textarea[name=body]', event.target),
            contextType = $('input[name=context_type]', event.target).val(),
            contextId = $('input[name=context_id]', event.target).val(),
            subject = subjectField.val().trim(),
            picture = pictureField.val().trim(),
            body = bodyField.val().trim();
        if(subject === '' && picture === '') {
            alert("%GETTEXT('forum.thread.missing.subject')%");
            return;
        }
        if (body === '' && picture === '') {
            alert("%GETTEXT('forum.thread.missing.body')%");
            return;
        }
        if (picture === '') {
            picture = null;
        }
        if (contextType !== '' && contextId !== '') {
            subjectField.prop('disabled', true);
            bodyField.prop('disabled', true);
            
            $('.loader', event.target).show();
            $('button.save', event.target).hide();
            $('button.picture', event.target).hide();
            $.post(
                '%PATH%forum/thread/add/',
                {'contextType':contextType, 'contextId':contextId, 'subject':subject, 'body':body, 'picture':picture},
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                        subjectField.val('');
                        pictureField.val('');
                        bodyField.val('');
                        bodyField.trigger('keyup');
                        $('.ajaxUpload img', event.target).remove();
                        $('.ajaxUploadPreview', event.target).empty();
                        vc.forum.checkUpdates();
                    } else if (data.message != undefined && data.message != '') {
                         alert(data.message);
                    } else {
                        alert("%GETTEXT('forum.thread.add.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('forum.thread.add.failed')%");
                }).always(function() {
                    subjectField.prop('disabled', false);
                    bodyField.prop('disabled', false);
            
                    $('button.save', event.target).show();
                    $('button.picture', event.target).show();
                    $('.loader', event.target).hide();
                });
        }
    },

    addComment: function(event) {
        event.preventDefault();
        var bodyField = $('textarea[name=body]', event.target),
            thread = $('input[name=thread]', event.target).val(),
            body = bodyField.val().trim();
        $('.loader', event.target).show();
        $('button.save', event.target).hide();
        if (body === '') {
            alert("%GETTEXT('forum.comment.missing.body')%");
            return;
        }
        if (thread !== '') {
            bodyField.prop('disabled', true);
            
            $.post(
                '%PATH%forum/comment/add/',
                {'thread':thread, 'body':body},
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                        bodyField.val('');
                        bodyField.trigger('keyup');
                        vc.forum.checkUpdates();
                    } else if (data.message != undefined && data.message != '') {
                         alert(data.message);
                    } else {
                        alert("%GETTEXT('forum.comment.add.failed')%");
                    }
                }).fail(function() {
                    alert("%GETTEXT('forum.comment.add.failed')%");
                }).always(function() {
                    bodyField.prop('disabled', false);
                    $('button.save', event.target).show();
                    $('.loader', event.target).hide();
                });
        }
    },

    updateInProgress: false,
    checkUpdates: function() {
        if (vc.forum.updateInProgress == false && vc.forum.checkUpdatesTimestamp !== null) {
            vc.forum.updateInProgress = true;
            $.get('%PATH%updates/',
                {
                    'contextType': vc.forum.contextType,
                    'contextId': vc.forum.contextId,
                    'entityTypes': [
                        '%ENTITY_TYPE_FORUM_THREAD%',
                        '%ENTITY_TYPE_FORUM_COMMENT%'
                    ],
                    'after': vc.forum.checkUpdatesTimestamp
                },
                function(data, textStatus, jqXHR) {
                    if (data.threads) {
                        if (vc.forum.page == 0) {
                            $.each(data.threads.add, function(index, thread) {
                                if ($('#thread-' + index + ' .threadArticle').length === 0) {
                                    var threadElement = $('.jThreads');
                                    threadElement.prepend(thread);
                                    vc.timeago.update(threadElement);
                                }
                            });
                        }
                        $.each(data.threads.edit, function(index, thread) {
                            $('#thread-' + index + ' .threadArticle').html(thread);
                            vc.timeago.update($('#thread-' + index + ' .threadArticle'));
                        });
                        $.each(data.threads.remove, function(index, thread) {
                            $('#thread-' + thread).remove();
                        });
                        vc.ui.updateUI();
                    }
                    if (data.comments) {
                        $.each(data.comments.add, function(thread, comment) {
                            var commentsElement = $('#thread-' + thread + ' .jComments');
                            commentsElement.append(comment);
                            vc.timeago.update(commentsElement);
                        });
                        $.each(data.comments.edit, function(index, comment) {
                            $('#thread-comment-' + index).replaceWith(comment);
                            vc.timeago.update($('#thread-comment-' + index));
                        });
                        $.each(data.comments.remove, function(index, comment) {
                            $('#thread-comment-' + comment).remove();
                        });
                    }
                    if (data.lastUpdate) {
                        vc.forum.checkUpdatesTimestamp = data.lastUpdate;
                    }
                }).always(function() {
                    vc.forum.updateInProgress = false;
                });
        }
    },

    actions: {
        subscribeThread: function(event) {
            event.preventDefault();
            var id = $(event.target).data('entityId'),
                parentThread = $(event.target).parents('.jThread');
            $.post('%PATH%subscription/add/',
                   {'entityType': '%ENTITY_TYPE_FORUM_THREAD%', 'entityId': id},
                   function(data, textStatus, jqXHR) {
                if (data.success) {
                    $(event.target).removeClass('subscribe');
                    $(event.target).addClass('unsubscribe');
                    $(event.target).text("%GETTEXT('forum.thread.unsubscribe')%");
                } else if(data.message) {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('forum.thread.subscribe.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('forum.thread.subscribe.failed')%");
            });
        },
        unsubscribeThread: function(event) {
            event.preventDefault();
            var id = $(event.target).data('entityId'),
                parentThread = $(event.target).parents('.jThread');
            $.post('%PATH%subscription/delete/',
                   {'entityType': '%ENTITY_TYPE_FORUM_THREAD%', 'entityId': id},
                   function(data, textStatus, jqXHR) {
                if (data.success) {
                    $(event.target).removeClass('unsubscribe');
                    $(event.target).addClass('subscribe');
                    $(event.target).text("%GETTEXT('forum.thread.subscribe')%");
                } else if(data.message) {
                    alert(data.message);
                } else {
                    alert("%GETTEXT('forum.thread.unsubscribe.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('forum.thread.unsubscribe.failed')%");
            });
        },
        editThread: function(event) {
            event.preventDefault();
            var id = $(event.target).data('entityId');
            $.get('%PATH%forum/thread/edit/', {'id': id}, function(data, textStatus, jqXHR) {
                var template = $('#jForumThreadEditTemplate').html(),
                    rendered = Mustache.to_html(template, data),
                    threadArticle = $('#thread-' + id + ' .threadArticle');
                $('#thread-' + id + ' .context').hide();
                threadArticle.hide();
                $(rendered).insertAfter(threadArticle);
                vc.ui.initAutoHeight($('#thread-' + id + ' textarea'));

            }) .fail(function() {
                alert("%GETTEXT('forum.thread.edit.failed')%");
            });
        },
        saveThreadEdit: function(event) {
            event.preventDefault();
            var id = $('input[name=id]', event.target).val(),
                subjectField = $('input[name=subject]', event.target),
                body = $('textarea[name=body]', event.target).val().trim();
            if (subjectField.length == 1) {
                var subject = subjectField.val().trim();
                if (subject === '') {
                    alert("%GETTEXT('forum.thread.missing.subject')%");
                    return;
                }
            } else {
                var subject = null;
            }
            if (body === '') {
                alert("%GETTEXT('forum.thread.missing.body')%");
                return;
            }
            if (id !== '') {
                $('.loader', event.target).show();
                $('button.save', event.target).hide();
                $.post(
                    '%PATH%forum/thread/edit/',
                    {'id':id, 'subject':subject, 'body':body},
                    function(data, textStatus, jqXHR) {
                        if (data != null && data.success == true) {
                            vc.forum.checkUpdates();
                            $('#thread-' + id + ' .threadForm').remove();
                            $('#thread-' + id + ' .threadArticle').show();
                            $('#thread-' + id + ' .context').show();
                        } else if (data.message != undefined) {
                            alert(data.message);
                        } else {
                            alert("%GETTEXT('forum.thread.edit.failed')%");
                        }
                    }).fail(function() {
                        alert("%GETTEXT('forum.thread.edit.failed')%");
                    }).always(function() {
                        $('button.save', event.target).show();
                        $('.loader', event.target).hide();
                    });
            }
        },
        cancelThreadEdit: function(event) {
            event.preventDefault();
            var editForm = $(event.target).parents('form');
            var id = $('input[name=id]', editForm).val();
            $('#thread-' + id + ' .threadForm').remove();
            $('#thread-' + id + ' .threadArticle').show();
            $('#thread-' + id + ' .context').show();
        },
        deleteThread: function(event) {
            event.preventDefault();
            $.post(
                '%PATH%forum/thread/delete/',
                {'id':$(event.target).data('entityId')},
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                        vc.forum.checkUpdates();
                    } else if (data.message != undefined) {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('forum.thread.delete.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('forum.thread.delete.failed')%");
                });
        },
        hideThread: function(event) {
            event.preventDefault();
            $.post(
                '%PATH%mysite/hidefeed/',
                {'id':$(event.target).data('entityId')},
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                        $('#thread-' + $(event.target).data('entityId')).remove();
                    } else if (data.message != undefined) {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('forum.thread.hide.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('forum.thread.hide.failed')%");
                });
        },
        editThreadComment: function(event) {
            event.preventDefault();
            var id = $(event.target).data('entityId'),
                template = $('#jForumCommentEditTemplate').html();
            $.get('%PATH%forum/comment/edit/', {'id': id}, function(data, textStatus, jqXHR) {
                if (data.id != undefined &&
                   data.body != undefined) {
                    $('#thread-comment-' + id + ' .context').hide();
                    var rendered = Mustache.to_html(template, data);
                    var threadArticleComment = $('#thread-comment-' + id + ' .threadCommentArticle');
                    threadArticleComment.hide();
                    $(rendered).insertAfter(threadArticleComment);
                    vc.ui.initAutoHeight($('#thread-comment-' + id + ' textarea'));
                } else {
                    alert("%GETTEXT('forum.thread.edit.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('forum.thread.edit.failed')%");
            });
        },
        saveCommentEdit: function(event) {
            event.preventDefault();
            var id = $('input[name=id]', event.target).val();
            var body = $('textarea[name=body]', event.target).val().trim();
            if (body === '') {
                alert("%GETTEXT('forum.comment.missing.body')%");
                return;
            }
            if (id !== '') {
                $('.loader', event.target).show();
                $('button.save', event.target).hide();
                $.post(
                    '%PATH%forum/comment/edit/',
                    {'id':id, 'body':body},
                    function(data, textStatus, jqXHR) {
                        if (data != null && data.success == true) {
                            vc.forum.checkUpdates();
                            $('#thread-comment-' + id + ' .threadCommentForm').remove();
                            $('#thread-comment-' + id + ' .threadCommentArticle').show();
                            $('#thread-comment-' + id + ' .context').show();
                        } else if (data.message != undefined) {
                            alert(data.message);
                        } else {
                            alert("%GETTEXT('forum.comment.edit.failed')%");
                        }
                    }).fail(function() {
                        alert("%GETTEXT('forum.comment.edit.failed')%");
                    }).always(function() {
                        $('button.save', event.target).show();
                        $('.loader', event.target).hide();
                    });
            }
        },
        cancelCommentEdit: function(event) {
            event.preventDefault();
            var editForm = $(event.target).parents('form');
            var id = $('input[name=id]', editForm).val();
            $('#thread-comment-' + id + ' .threadCommentForm').remove();
            $('#thread-comment-' + id + ' .threadCommentArticle').show();
            $('#thread-comment-' + id + ' .context').show();
        },
        deleteThreadComment: function(event) {
            event.preventDefault();
            $.post(
                '%PATH%forum/comment/delete/',
                {'id':$(event.target).data('entityId')},
                function(data, textStatus, jqXHR) {
                    if (data != null && data.success == true) {
                        vc.forum.checkUpdates();
                    } else if (data.message != undefined) {
                        alert(data.message);
                    } else {
                        alert("%GETTEXT('forum.comment.delete.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('forum.comment.delete.failed')%");
                });
        },
        flag: function(event) {
            event.preventDefault();
            if (confirm("%GETTEXT('group.confirm.flag')%")) {
                $.post(
                    '%PATH%flag/add/',
                    {
                        'entityType':event.data.entityType,
                        'entityId':$(event.target).data('entityId')
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.message != undefined) {
                            alert(data.message);
                        } else {
                            alert("%GETTEXT('flag.failed')%");
                        }

                    }).fail(function() {
                        alert("%GETTEXT('flag.failed')%");
                    });
            }
        },
        unflag: function(event) {
            event.preventDefault();
            if (confirm("%GETTEXT('group.confirm.unflag')%")) {
                $.post(
                    '%PATH%flag/unflag/',
                    {
                        'id':$(event.target).data('flagId')
                    },
                    function(data, textStatus, jqXHR) {
                        if (data.success == true) {
                            var parentComment = $(event.target).parents('.jThreadComment');
                            if (parentComment.length > 0) {
                                $('.flaggedThreadCommentArticle', parentComment).removeClass('flaggedThreadCommentArticle');
                            } else {
                                var parentThread = $(event.target).parents('.jThread');
                                $('.flaggedThreadArticle', parentThread).removeClass('flaggedThreadArticle');

                            }
                        } else {
                            alert("%GETTEXT('unflag.failed')%");
                        }

                    }).fail(function() {
                        alert("%GETTEXT('unflag.failed')%");
                    });
            }
        },
        like: function(event) {
            event.preventDefault();
            var target = $(event.target).closest('a');
            $.post(
                '%PATH%like/',
                {
                    'entityType':event.data.entityType,
                    'entityId':target.data('entityId'),
                    'value':event.data.value
                },
                function(data, textStatus, jqXHR) {
                    if (data.success == true) {
                        var actionsElement = target.closest('div');
                        $('span.i', $('a.jLike', actionsElement).next()).text('(' + data.likes + ')');
                        $('span.i', $('a.jDislike', actionsElement).next()).text('(' + data.dislikes + ')');
                    } else {
                        alert("%GETTEXT('like.failed')%");
                    }

                }).fail(function() {
                    alert("%GETTEXT('like.failed')%");
                });
        },
        addCommentForm: function(event) {
            event.preventDefault();
            var thread = $(event.target).closest('.jThread'),
                newCommentForm = $('.newcomment', thread);
            $('section.comments > aside > nav', thread).slideUp();
            newCommentForm.slideDown();
            $('textarea', newCommentForm).focus();
        },
        loadMoreComments: function(event) {
            event.preventDefault();
            vc.forum.actions.loadMoreCommentsByElement(
                $(event.target).data('threadId'),
                $(event.target).closest('.jComments'),
                $(event.target).closest('.loadMoreComments'),
                null
            );
        },
        loadMoreCommentsByElement: function(thread, jsCommentsElement, moreCommentsElement, loadTillComment) {
            var before = 0;
            $('.jThreadComment .jAgo', jsCommentsElement).each(function(index, element) {
                var time = $(element).data('ts');
                if (before > time || before == 0) {
                    before = time;
                }
            });
            $.get('%PATH%forum/comment/list/',
                  {
                      'thread': thread,
                      'before': before
                  },
                  function(data, textStatus, jqXHR) {
                if (data.comments) {
                    $.each(data.comments, function(index, comment) {
                        $(comment).insertAfter(moreCommentsElement);
                    });
                    vc.timeago.update(jsCommentsElement);
                    if (data.moreAvailable == false) {
                        moreCommentsElement.remove();
                    } else {
                        if (loadTillComment !== null) {
                            if ($('#thread-comment-' + loadTillComment).length == 0) {
                                vc.forum.actions.loadMoreCommentsByElement(
                                    thread,
                                    jsCommentsElement,
                                    moreCommentsElement,
                                    loadTillComment
                                );
                            }
                        }
                    }
                    if (loadTillComment !== null) {
                        var commentElement = $('#thread-comment-' + loadTillComment);
                        if (commentElement.length == 1) {
                            $('html, body').animate({
                                scrollTop: commentElement.offset().top
                            }, 2000);
                        }
                    }
                } else {
                    alert("%GETTEXT('forum.thread.loadMoreComments.failed')%");
                }
            }) .fail(function() {
                alert("%GETTEXT('forum.thread.loadMoreComments.failed')%");
            });

        }
    }
};