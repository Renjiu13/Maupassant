/**
 * Comment Enhancements
 * Provides AJAX submission, validation, and improved UX for comments
 */
(function($) {
    'use strict';

    const CommentEnhancements = {
        init: function() {
            this.setupAjaxComments();
            this.setupFormValidation();
            this.setupCharacterCounter();
            this.setupCommentAnimations();
            this.setupHoneypot();
            this.setupLazyLoadComments();
        },

        /**
         * AJAX comment submission
         */
        setupAjaxComments: function() {
            const form = $('#comment-form');
            if (!form.length) return;

            form.on('submit', function(e) {
                e.preventDefault();
                
                const submitButton = form.find('button[type="submit"]');
                const originalText = submitButton.text();
                
                // Validate honeypot
                if ($('#comment-honeypot').val() !== '') {
                    return false;
                }
                
                // Show loading state
                submitButton.prop('disabled', true).text('提交中...');
                CommentEnhancements.removeMessages();
                
                $.ajax({
                    type: 'POST',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        const $response = $(response);
                        const $newComment = $response.find('#comments .comment-list > li:last-child');
                        
                        if ($newComment.length) {
                            // Add new comment with animation
                            const commentList = $('.comment-list');
                            if (commentList.length) {
                                $newComment.hide().appendTo(commentList).fadeIn(600);
                            }
                            
                            // Reset form
                            form[0].reset();
                            $('#comment').trigger('input'); // Update character counter
                            
                            // Show success message
                            CommentEnhancements.showMessage('success', '评论提交成功！');
                            
                            // Scroll to new comment
                            $('html, body').animate({
                                scrollTop: $newComment.offset().top - 100
                            }, 500);
                        } else {
                            CommentEnhancements.showMessage('info', '评论已提交，等待审核。');
                            form[0].reset();
                            $('#comment').trigger('input');
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = '评论提交失败，请重试。';
                        if (xhr.responseText) {
                            const $error = $(xhr.responseText);
                            const errorText = $error.find('.wp-die-message').text();
                            if (errorText) errorMsg = errorText;
                        }
                        CommentEnhancements.showMessage('error', errorMsg);
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).text(originalText);
                    }
                });
            });
        },

        /**
         * Real-time form validation
         */
        setupFormValidation: function() {
            const form = $('#comment-form');
            if (!form.length) return;

            // Email validation
            const emailInput = $('#email');
            if (emailInput.length) {
                emailInput.on('blur', function() {
                    const email = $(this).val();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (email && !emailRegex.test(email)) {
                        CommentEnhancements.showFieldError($(this), '请输入有效的邮箱地址');
                    } else {
                        CommentEnhancements.removeFieldError($(this));
                    }
                });
            }

            // Comment content validation
            const commentTextarea = $('#comment');
            if (commentTextarea.length) {
                commentTextarea.on('blur', function() {
                    const content = $(this).val().trim();
                    
                    if (content.length === 0) {
                        CommentEnhancements.showFieldError($(this), '评论内容不能为空');
                    } else if (content.length < 5) {
                        CommentEnhancements.showFieldError($(this), '评论内容至少需要5个字符');
                    } else {
                        CommentEnhancements.removeFieldError($(this));
                    }
                });
            }

            // Name validation
            const authorInput = $('#author');
            if (authorInput.length) {
                authorInput.on('blur', function() {
                    const name = $(this).val().trim();
                    
                    if (name.length === 0) {
                        CommentEnhancements.showFieldError($(this), '请输入您的姓名');
                    } else {
                        CommentEnhancements.removeFieldError($(this));
                    }
                });
            }
        },

        /**
         * Character counter for comment textarea
         */
        setupCharacterCounter: function() {
            const textarea = $('#comment');
            if (!textarea.length) return;

            const maxLength = 1000;
            const counter = $('<div class="comment-char-counter"></div>');
            textarea.after(counter);

            const updateCounter = function() {
                const length = textarea.val().length;
                const remaining = maxLength - length;
                counter.text(length + ' / ' + maxLength + ' 字符');
                
                if (remaining < 100) {
                    counter.addClass('warning');
                } else {
                    counter.removeClass('warning');
                }
                
                if (remaining < 0) {
                    counter.addClass('error');
                } else {
                    counter.removeClass('error');
                }
            };

            textarea.on('input', updateCounter);
            updateCounter();
        },

        /**
         * Smooth animations for comment interactions
         */
        setupCommentAnimations: function() {
            // Animate reply links
            $('.comment-list').on('click', '.comment-reply-link', function(e) {
                const targetForm = $('#respond');
                if (targetForm.length) {
                    setTimeout(function() {
                        targetForm.hide().fadeIn(400);
                        $('#comment').focus();
                    }, 100);
                }
            });

            // Animate cancel reply
            $('#cancel-comment-reply-link').on('click', function() {
                const targetForm = $('#respond');
                if (targetForm.length) {
                    targetForm.fadeOut(200, function() {
                        $(this).fadeIn(200);
                    });
                }
            });
        },

        /**
         * Honeypot spam prevention
         */
        setupHoneypot: function() {
            const form = $('#comment-form');
            if (!form.length) return;

            const honeypot = $('<input type="text" name="comment-honeypot" id="comment-honeypot" style="position:absolute;left:-9999px;width:1px;height:1px;" tabindex="-1" autocomplete="off">');
            form.prepend(honeypot);
        },

        /**
         * Lazy load comments for better performance
         */
        setupLazyLoadComments: function() {
            const commentList = $('.comment-list');
            if (!commentList.length) return;

            const comments = commentList.find('> li.comment');
            if (comments.length <= 10) return; // Only lazy load if more than 10 comments

            // Hide comments beyond the first 10
            comments.slice(10).hide().addClass('lazy-comment');

            // Add load more button
            const loadMoreBtn = $('<button class="load-more-comments">加载更多评论</button>');
            commentList.after(loadMoreBtn);

            let currentIndex = 10;
            const loadPerClick = 10;

            loadMoreBtn.on('click', function() {
                const toLoad = $('.lazy-comment:hidden').slice(0, loadPerClick);
                toLoad.fadeIn(400).removeClass('lazy-comment');
                
                currentIndex += loadPerClick;
                
                if ($('.lazy-comment:hidden').length === 0) {
                    loadMoreBtn.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
        },

        /**
         * Show validation message
         */
        showMessage: function(type, message) {
            this.removeMessages();
            
            const messageDiv = $('<div class="comment-message comment-message-' + type + '">' + message + '</div>');
            $('#comment-form').before(messageDiv);
            
            messageDiv.hide().slideDown(300);
            
            if (type === 'success' || type === 'info') {
                setTimeout(function() {
                    messageDiv.slideUp(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        },

        /**
         * Remove all messages
         */
        removeMessages: function() {
            $('.comment-message').slideUp(200, function() {
                $(this).remove();
            });
        },

        /**
         * Show field error
         */
        showFieldError: function(field, message) {
            this.removeFieldError(field);
            
            field.addClass('error');
            const errorDiv = $('<div class="field-error">' + message + '</div>');
            field.after(errorDiv);
        },

        /**
         * Remove field error
         */
        removeFieldError: function(field) {
            field.removeClass('error');
            field.next('.field-error').remove();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CommentEnhancements.init();
    });

})(jQuery);
