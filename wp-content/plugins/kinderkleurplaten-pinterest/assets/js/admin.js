/**
 * Kinderkleurplaten Pinterest Admin JavaScript
 */

(function($) {
    'use strict';

    // ==========================================================================
    // Namespace
    // ==========================================================================

    window.KK_Pinterest = window.KK_Pinterest || {};

    // ==========================================================================
    // Utilities
    // ==========================================================================

    KK_Pinterest.utils = {
        showNotice: function($container, message, type, dismissible) {
            type = type || 'info';
            dismissible = dismissible !== false;

            var $notice = $('<div class="notice notice-' + type + (dismissible ? ' is-dismissible' : '') + '"><p>' + message + '</p></div>');

            if (dismissible) {
                $notice.on('click', '.notice-dismiss', function() {
                    $notice.fadeOut(function() { $(this).remove(); });
                });
            }

            $container.prepend($notice);
            return $notice;
        },

        showSpinner: function($btn) {
            $btn.prop('disabled', true).addClass('updating');
            $btn.siblings('.spinner').addClass('is-active');
        },

        hideSpinner: function($btn) {
            $btn.prop('disabled', false).removeClass('updating');
            $btn.siblings('.spinner').removeClass('is-active');
        },

        ajax: function(action, data, success, error) {
            // Convert string data (from form.serialize()) to object so we can add properties
            if (typeof data === 'string') {
                var obj = {};
                var pairs = data.split('&');
                for (var i = 0; i < pairs.length; i++) {
                    var pair = pairs[i].split('=', 2);
                    if (pair.length === 2) {
                        obj[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
                    }
                }
                data = obj;
            }
            data = data || {};
            data.action = 'kk_pinterest_' + action;
            data.nonce = kk_pinterest_vars.nonce;

            $.post(kk_pinterest_vars.ajax_url, data)
                .done(function(response) {
                    if (response.success) {
                        success && success(response.data);
                    } else {
                        error && error(response.data.message || 'Unknown error');
                    }
                })
                .fail(function(xhr) {
                    var msg = 'AJAX error';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        msg = xhr.responseJSON.data.message;
                    }
                    error && error(msg);
                });
        },

        confirm: function(message, callback) {
            if (confirm(message)) {
                callback && callback();
            }
        },

        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },

        formatDate: function(dateString) {
            var date = new Date(dateString);
            return date.toLocaleString();
        }
    };

    // ==========================================================================
    // Modal Manager
    // ==========================================================================

    KK_Pinterest.Modal = {
        open: function($modal) {
            $modal.fadeIn(200);
            $('body').addClass('modal-open');
        },

        close: function($modal) {
            $modal.fadeOut(200);
            $('body').removeClass('modal-open');
        },

        init: function() {
            $(document).on('click', '.kk-modal-overlay, .kk-modal-close, .kk-modal-cancel', function() {
                var $modal = $(this).closest('.kk-modal');
                KK_Pinterest.Modal.close($modal);
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.kk-modal:visible').each(function() {
                        KK_Pinterest.Modal.close($(this));
                    });
                }
            });
        }
    };

    // ==========================================================================
    // Board Management
    // ==========================================================================

    KK_Pinterest.Boards = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Create board button
            $('#kk-create-board-btn').on('click', function() {
                self.openBoardModal();
            });

            // Sync boards button
            $('#kk-sync-boards-btn').on('click', function() {
                self.syncBoards();
            });

            // Edit board buttons
            $(document).on('click', '.kk-edit-board', function() {
                var $btn = $(this);
                self.openBoardModal({
                    id: $btn.data('board-id'),
                    name: $btn.data('board-name'),
                    description: $btn.data('board-description'),
                    privacy: $btn.data('board-privacy')
                });
            });

            // View sections
            $(document).on('click', '.kk-view-sections', function() {
                var boardId = $(this).data('board-id');
                self.openSectionsModal(boardId);
            });

            // Delete board
            $(document).on('click', '.kk-delete-board', function() {
                var $btn = $(this);
                var boardId = $btn.data('board-id');
                var boardName = $btn.data('board-name');

                KK_Pinterest.utils.confirm(
                    kk_pinterest_vars.i18n.confirm_delete_board.replace('%s', boardName),
                    function() { self.deleteBoard(boardId); }
                );
            });

            // Set default board
            $(document).on('click', '.kk-set-default', function() {
                var boardId = $(this).data('board-id');
                self.setDefaultBoard(boardId);
            });

            // Board form submit
            $('#kk-board-form').on('submit', function(e) {
                e.preventDefault();
                self.saveBoard($(this));
            });

            // Create section form
            $('#kk-create-section-form').on('submit', function(e) {
                e.preventDefault();
                self.createSection($(this));
            });
        },

        openBoardModal: function(board) {
            var $modal = $('#kk-board-modal');
            var $form = $('#kk-board-form');
            var $title = $('#kk-board-modal-title');

            $form[0].reset();
            $('#kk-board-form-id').val('');

            if (board) {
                $title.text(kk_pinterest_vars.i18n.edit_board);
                $('#kk-board-form-id').val(board.id);
                $('#kk-board-name').val(board.name);
                $('#kk-board-description').val(board.description);
                $('#kk-board-privacy').val(board.privacy);
            } else {
                $title.text(kk_pinterest_vars.i18n.create_board);
            }

            KK_Pinterest.Modal.open($modal);
        },

        saveBoard: function($form) {
            var self = this;
            var boardId = $('#kk-board-form-id').val();
            var data = {
                name: $('#kk-board-name').val(),
                description: $('#kk-board-description').val(),
                privacy: $('#kk-board-privacy').val()
            };

            var $submitBtn = $form.find('button[type="submit"]');
            KK_Pinterest.utils.showSpinner($submitBtn);

            if (boardId) {
                data.board_id = boardId;
                KK_Pinterest.utils.ajax('update_board', data, function() {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.board_updated, 'success');
                    KK_Pinterest.Modal.close($('#kk-board-modal'));
                    location.reload();
                }, function(msg) {
                    KK_Pinterest.utils.showNotice($modal.find('.kk-modal-body'), msg, 'error');
                    KK_Pinterest.utils.hideSpinner($submitBtn);
                });
            } else {
                KK_Pinterest.utils.ajax('create_board', data, function(result) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.board_created, 'success');
                    KK_Pinterest.Modal.close($('#kk-board-modal'));
                    location.reload();
                }, function(msg) {
                    KK_Pinterest.utils.showNotice($modal.find('.kk-modal-body'), msg, 'error');
                    KK_Pinterest.utils.hideSpinner($submitBtn);
                });
            }
        },

        deleteBoard: function(boardId) {
            var self = this;
            KK_Pinterest.utils.ajax('delete_board', { board_id: boardId }, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.board_deleted, 'success');
                location.reload();
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
            });
        },

        setDefaultBoard: function(boardId) {
            var self = this;
            KK_Pinterest.utils.ajax('set_default_board', { board_id: boardId }, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.default_board_set, 'success');
                location.reload();
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
            });
        },

        syncBoards: function() {
            var $btn = $('#kk-sync-boards-btn');
            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('sync_boards', {}, function(result) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.boards_synced.replace('%d', result.count), 'success');
                location.reload();
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        openSectionsModal: function(boardId) {
            var $modal = $('#kk-sections-modal');
            $('#kk-section-board-id').val(boardId);
            $('#kk-create-section-form')[0].reset();
            $('#kk-sections-list').html('<p class="description">' + kk_pinterest_vars.i18n.loading + '</p>');

            KK_Pinterest.Modal.open($modal);
            this.loadSections(boardId);
        },

        loadSections: function(boardId) {
            var self = this;
            KK_Pinterest.utils.ajax('get_board_sections', { board_id: boardId }, function(result) {
                var $list = $('#kk-sections-list');
                if (result.sections && result.sections.length) {
                    var html = '';
                    $.each(result.sections, function(i, section) {
                        html += '<div class="kk-section-item">' +
                            '<div class="kk-section-info">' +
                            '<h5>' + $('<div>').text(section.title).html() + '</h5>' +
                            '<span class="description">ID: ' + section.id + '</span>' +
                            '</div>' +
                            '</div>';
                    });
                    $list.html(html);
                } else {
                    $list.html('<p class="description">' + kk_pinterest_vars.i18n.no_sections + '</p>');
                }
            }, function(msg) {
                $('#kk-sections-list').html('<p class="description" style="color: #dc3232;">' + msg + '</p>');
            });
        },

        createSection: function($form) {
            var self = this;
            var boardId = $('#kk-section-board-id').val();
            var title = $form.find('input[name="title"]').val();

            var $submitBtn = $form.find('button[type="submit"]');
            KK_Pinterest.utils.showSpinner($submitBtn);

            KK_Pinterest.utils.ajax('create_board_section', {
                board_id: boardId,
                title: title
            }, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.section_created, 'success');
                $form[0].reset();
                self.loadSections(boardId);
                KK_Pinterest.utils.hideSpinner($submitBtn);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($modal.find('.kk-modal-body'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($submitBtn);
            });
        }
    };

    // ==========================================================================
    // Pin Publishing
    // ==========================================================================

    KK_Pinterest.Publish = {
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initPreview();
        },

        bindEvents: function() {
            var self = this;

            // Manual pin form
            $('#kk-manual-pin-form').on('submit', function(e) {
                e.preventDefault();
                self.publishManualPin($(this));
            });

            // Post pin form
            $('#kk-post-pin-form').on('submit', function(e) {
                e.preventDefault();
                self.publishFromPost($(this));
            });

            // Bulk pin form
            $('#kk-bulk-pin-form').on('submit', function(e) {
                e.preventDefault();
                self.publishBulk($(this));
            });

            // Post search
            $('#kk-post-search').on('input', KK_Pinterest.utils.debounce(function() {
                self.searchPosts($(this).val(), '#kk-post-search-results', false);
            }, 300));

            $('#kk-bulk-post-search').on('input', KK_Pinterest.utils.debounce(function() {
                self.searchPosts($(this).val(), '#kk-bulk-post-results', true);
            }, 300));

            // Clear selections
            $('#kk-clear-post').on('click', function() {
                self.clearPostSelection();
            });

            $('#kk-clear-bulk').on('click', function() {
                self.clearBulkSelection();
            });

            // Board change -> load sections
            $('#kk-pin-board, #kk-post-pin-board, #kk-bulk-board').on('change', function() {
                var boardId = $(this).val();
                var targetId = $(this).attr('id').replace('-board', '-section');
                self.loadBoardSections(boardId, targetId);
            });
        },

        initTabs: function() {
            $('.kk-publish-tabs .nav-tab').on('click', function(e) {
                e.preventDefault();
                var $tab = $(this);
                var target = $tab.data('tab');

                $('.kk-publish-tabs .nav-tab').removeClass('nav-tab-active').attr('aria-selected', 'false');
                $tab.addClass('nav-tab-active').attr('aria-selected', 'true');

                $('.kk-tab-panel').removeClass('kk-tab-active').attr('role', 'tabpanel').hide();
                $('#kk-tab-' + target).addClass('kk-tab-active').show();
            });
        },

        initPreview: function() {
            $('#kk-pin-image, #kk-pin-title, #kk-pin-description').on('input', function() {
                self.updatePreview();
            });
        },

        updatePreview: function() {
            var imageUrl = $('#kk-pin-image').val();
            var title = $('#kk-pin-title').val();
            var description = $('#kk-pin-description').val();

            if (imageUrl || title || description) {
                $('#kk-preview-image').attr('src', imageUrl);
                $('#kk-preview-title').text(title || kk_pinterest_vars.i18n.no_title);
                $('#kk-preview-description').text(description || kk_pinterest_vars.i18n.no_description);
                $('#kk-pin-preview').show();
            } else {
                $('#kk-pin-preview').hide();
            }
        },

        searchPosts: function(query, resultsSelector, isBulk) {
            if (query.length < 2) {
                $(resultsSelector).hide().empty();
                return;
            }

            var $results = $(resultsSelector);
            $results.show().html('<p class="description" style="padding: 10px;">' + kk_pinterest_vars.i18n.searching + '</p>');

            $.post(kk_pinterest_vars.ajax_url, {
                action: 'kk_pinterest_get_posts',
                nonce: kk_pinterest_vars.nonce,
                search: query,
                per_page: isBulk ? 50 : 20
            }, function(response) {
                if (response.success && response.data.posts.length) {
                    var html = '';
                    $.each(response.data.posts, function(i, post) {
                        var thumb = post.thumbnail || kk_pinterest_vars.default_placeholder;
                        var checkbox = isBulk
                            ? '<input type="checkbox" name="post_ids[]" value="' + post.id + '" data-title="' + $('<div>').text(post.title).html() + '" data-thumb="' + thumb + '">'
                            : '<input type="radio" name="post_id" value="' + post.id + '" data-title="' + $('<div>').text(post.title).html() + '" data-thumb="' + thumb + '" data-excerpt="' + $('<div>').text(post.excerpt).html() + '" data-link="' + post.permalink + '" data-image="' + post.image_url + '">';

                        html += '<div class="' + (isBulk ? 'kk-bulk-post-result-item' : 'kk-post-result-item') + '">' +
                            checkbox +
                            '<img src="' + thumb + '" class="kk-post-result-thumb" alt="">' +
                            '<div class="kk-post-result-info">' +
                            '<div class="kk-post-result-title">' + $('<div>').text(post.title).html() + '</div>' +
                            '<div class="kk-post-result-excerpt">' + $('<div>').text(post.excerpt).html() + '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    $results.html(html);

                    // Bind click events
                    if (isBulk) {
                        $results.find('input[type="checkbox"]').on('change', function() {
                            self.toggleBulkPostSelection(this);
                        });
                    } else {
                        $results.find('input[type="radio"]').on('change', function() {
                            self.selectPost(this);
                        });
                    }
                } else {
                    $results.html('<p class="description" style="padding: 10px;">' + kk_pinterest_vars.i18n.no_posts_found + '</p>');
                }
            });
        },

        selectPost: function(radio) {
            var $radio = $(radio);
            var postId = $radio.val();

            $('#kk-selected-post-id').val(postId);
            $('#kk-selected-title').text($radio.data('title'));
            $('#kk-selected-excerpt').text($radio.data('excerpt'));
            $('#kk-selected-thumb').attr('src', $radio.data('thumb')).show();

            $('#kk-post-pin-title').val($radio.data('title'));
            $('#kk-post-pin-description').val($radio.data('excerpt'));
            $('#kk-post-pin-link').val($radio.data('link'));
            $('#kk-post-pin-image').val($radio.data('image'));
            $('#kk-post-pin-alt').val($radio.data('title'));

            $('#kk-selected-post').show();
            $('#kk-post-search-results').hide();
            $('#kk-post-search').val('');
            $('#kk-publish-post-btn').prop('disabled', false);
        },

        clearPostSelection: function() {
            $('#kk-selected-post').hide();
            $('#kk-selected-post-id').val('');
            $('#kk-post-pin-title').val('');
            $('#kk-post-pin-description').val('');
            $('#kk-post-pin-link').val('');
            $('#kk-post-pin-image').val('');
            $('#kk-post-pin-alt').val('');
            $('#kk-post-search').val('');
            $('#kk-publish-post-btn').prop('disabled', true);
        },

        toggleBulkPostSelection: function(checkbox) {
            var $checkbox = $(checkbox);
            var postId = $checkbox.val();
            var $list = $('#kk-bulk-selected-list');
            var $count = $('#kk-bulk-count');

            if ($checkbox.is(':checked')) {
                var itemHtml = '<div class="kk-bulk-selected-item" data-post-id="' + postId + '">' +
                    '<img src="' + $checkbox.data('thumb') + '" class="kk-bulk-selected-thumb" alt="">' +
                    '<span>' + $checkbox.data('title') + '</span>' +
                    '<button type="button" class="button button-small kk-remove-bulk-item" style="margin-left: auto;" data-post-id="' + postId + '">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>' +
                    '</div>';
                $list.append(itemHtml);
            } else {
                $list.find('[data-post-id="' + postId + '"]').remove();
            }

            var count = $list.find('.kk-bulk-selected-item').length;
            $count.text(count);
            $('#kk-bulk-selected').toggle(count > 0);
            $('#kk-publish-bulk-btn').prop('disabled', count === 0);
        },

        clearBulkSelection: function() {
            $('#kk-bulk-post-results input[type="checkbox"]').prop('checked', false);
            $('#kk-bulk-selected-list').empty();
            $('#kk-bulk-count').text('0');
            $('#kk-bulk-selected').hide();
            $('#kk-publish-bulk-btn').prop('disabled', true);
            $('#kk-bulk-post-search').val('');
            $('#kk-bulk-post-results').hide();
        },

        loadBoardSections: function(boardId, targetSelector) {
            var $target = $(targetSelector);
            $target.html('<option value="">' + kk_pinterest_vars.i18n.loading_sections + '</option>').prop('disabled', true);

            if (!boardId) {
                $target.html('<option value="">' + kk_pinterest_vars.i18n.no_section + '</option>').prop('disabled', false);
                return;
            }

            KK_Pinterest.utils.ajax('get_board_sections', { board_id: boardId }, function(result) {
                var html = '<option value="">' + kk_pinterest_vars.i18n.no_section + '</option>';
                if (result.sections && result.sections.length) {
                    $.each(result.sections, function(i, section) {
                        html += '<option value="' + section.id + '">' + $('<div>').text(section.title).html() + '</option>';
                    });
                }
                $target.html(html).prop('disabled', false);
            }, function() {
                $target.html('<option value="">' + kk_pinterest_vars.i18n.error_loading_sections + '</option>').prop('disabled', false);
            });
        },

        publishManualPin: function($form) {
            var self = this;
            var $btn = $('#kk-publish-manual-btn');
            var data = $form.serialize();

            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('publish_pin', data, function(result) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.pin_published, 'success');
                $form[0].reset();
                $('#kk-pin-preview').hide();
                self.updatePreview();
                KK_Pinterest.utils.hideSpinner($btn);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        publishFromPost: function($form) {
            var self = this;
            var $btn = $('#kk-publish-post-btn');
            var postId = $('#kk-selected-post-id').val();

            if (!postId) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.no_post_selected, 'error');
                return;
            }

            var data = $form.serialize() + '&post_id=' + postId;

            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('publish_from_post', data, function(result) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.pin_published, 'success');
                self.clearPostSelection();
                KK_Pinterest.utils.hideSpinner($btn);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        publishBulk: function($form) {
            var self = this;
            var $btn = $('#kk-publish-bulk-btn');
            var $modal = $('#kk-bulk-progress-modal');
            var $progressFill = $('#kk-bulk-progress-fill');
            var $progressText = $('#kk-bulk-progress-text');
            var $progressLog = $('#kk-bulk-progress-log');

            var postIds = [];
            $('#kk-bulk-selected-list .kk-bulk-selected-item').each(function() {
                postIds.push($(this).data('post-id'));
            });

            if (!postIds.length) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.no_posts_selected, 'error');
                return;
            }

            var data = $form.serialize() + '&post_ids=' + postIds.join(',');

            KK_Pinterest.Modal.open($modal);
            $progressFill.css('width', '0%');
            $progressText.text(kk_pinterest_vars.i18n.starting);
            $progressLog.empty();

            KK_Pinterest.utils.ajax('bulk_publish', data, function(result) {
                var results = result.results || [];
                var total = results.length;
                var successCount = 0;
                var failCount = 0;

                $.each(results, function(i, r) {
                    var percent = Math.round(((i + 1) / total) * 100);
                    $progressFill.css('width', percent + '%');
                    $progressText.text(kk_pinterest_vars.i18n.publishing.replace('%d', i + 1).replace('%total%', total));

                    var $logItem = $('<li class="' + (r.success ? 'success' : 'error') + '"></li>');
                    var icon = r.success ? '✓' : '✗';
                    var title = r.post_title || ('Post ' + r.post_id);
                    $logItem.html(icon + ' ' + title + (r.error ? ': ' + r.error : ''));
                    $progressLog.append($logItem);
                    $progressLog.scrollTop($progressLog[0].scrollHeight);

                    if (r.success) successCount++; else failCount++;
                });

                $progressText.text(kk_pinterest_vars.i18n.completed.replace('%success%', successCount).replace('%failed%', failCount));

                setTimeout(function() {
                    KK_Pinterest.Modal.close($modal);
                    self.clearBulkSelection();
                }, 3000);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.Modal.close($modal);
            });
        }
    };

    // ==========================================================================
    // History
    // ==========================================================================

    KK_Pinterest.History = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Clear history
            $('#kk-clear-history-btn, #kk-clear-history-tool').on('click', function() {
                var msg = $(this).data('confirm') || kk_pinterest_vars.i18n.confirm_clear_history;
                KK_Pinterest.utils.confirm(msg, function() {
                    self.clearHistory();
                });
            });

            // Delete history item
            $(document).on('click', '.kk-delete-history-item', function() {
                var id = $(this).data('id');
                var $row = $(this).closest('tr');

                KK_Pinterest.utils.confirm(kk_pinterest_vars.i18n.confirm_delete_item, function() {
                    self.deleteItem(id, $row);
                });
            });
        },

        clearHistory: function() {
            KK_Pinterest.utils.ajax('clear_history', {}, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.history_cleared, 'success');
                location.reload();
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
            });
        },

        deleteItem: function(id, $row) {
            KK_Pinterest.utils.ajax('delete_history_item', { id: id }, function() {
                $row.fadeOut(function() {
                    $(this).remove();
                });
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.item_deleted, 'success');
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
            });
        }
    };

    // ==========================================================================
    // Settings
    // ==========================================================================

    KK_Pinterest.Settings = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Save credentials
            $('#kk-credentials-form').on('submit', function(e) {
                e.preventDefault();
                self.saveCredentials($(this));
            });

            // Save defaults
            $('#kk-defaults-form').on('submit', function(e) {
                e.preventDefault();
                self.saveDefaults($(this));
            });

            // Save advanced
            $('#kk-advanced-form').on('submit', function(e) {
                e.preventDefault();
                self.saveAdvanced($(this));
            });

            // Connect to Pinterest
            $('#kk-connect-btn').on('click', function() {
                window.location.href = kk_pinterest_vars.auth_url;
            });

            // Refresh token
            $('#kk-refresh-token-btn').on('click', function() {
                self.refreshToken($(this));
            });

            // Test connection
            $('#kk-test-connection-btn').on('click', function() {
                self.testConnection($(this));
            });

            // Test OAuth Configuration
            $('#kk-test-oauth-btn').on('click', function() {
                self.testOAuth($(this));
            });

            // View Debug Log
            $('#kk-view-debug-log').on('click', function() {
                self.toggleDebugLog($(this));
            });

            // Refresh Debug Log
            $(document).on('click', '#kk-refresh-debug-log', function() {
                self.loadDebugLog($(this));
            });

            // Disconnect
            $('#kk-disconnect-btn').on('click', function() {
                KK_Pinterest.utils.confirm(kk_pinterest_vars.i18n.confirm_disconnect, function() {
                    self.disconnect($(this));
                });
            });

            // Sync boards tool
            $('#kk-sync-boards-tool').on('click', function() {
                var $btn = $(this);
                KK_Pinterest.utils.showSpinner($btn);

                KK_Pinterest.utils.ajax('sync_boards', {}, function(result) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.boards_synced.replace('%d', result.count), 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                }, function(msg) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                    KK_Pinterest.utils.hideSpinner($btn);
                });
            });

            // Reset plugin
            $('#kk-reset-plugin').on('click', function() {
                var msg = $(this).data('confirm') || kk_pinterest_vars.i18n.confirm_reset;
                KK_Pinterest.utils.confirm(msg, function() {
                    self.resetPlugin();
                });
            });

            // Copy callback URL
            $('.kk-copy-callback').on('click', function() {
                var text = $(this).data('clipboard-text');
                navigator.clipboard.writeText(text).then(function() {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.copied, 'success');
                });
            });
        },

        saveCredentials: function($form) {
            var self = this;
            var $btn = $('#kk-save-credentials');
            var $container = $('.kk-pinterest-wrap');

            // Build data object explicitly to avoid string/object issues
            var data = {
                app_id: $('#kk-app-id').val(),
                app_secret: $('#kk-app-secret').val(),
                default_image: $('#kk-default-image').val(),
            };

            // Validate required fields
            if (!data.app_id || !data.app_secret) {
                KK_Pinterest.utils.showNotice($container, kk_pinterest_vars.i18n.error_missing_credentials, 'error');
                return;
            }

            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('save_settings', data, function() {
                KK_Pinterest.utils.showNotice($container, kk_pinterest_vars.i18n.credentials_saved, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($container, msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        saveDefaults: function($form) {
            var $btn = $('#kk-save-defaults');
            var data = $form.serialize();

            KK_Pinterest.utils.showSpinner($btn);

            // Use save_settings action with default_image
            var defaultImage = $('#kk-default-image').val();
            var autoPublish = $('#kk-auto-publish').is(':checked') ? 1 : 0;

            KK_Pinterest.utils.ajax('save_settings', {
                default_image: defaultImage,
                auto_publish: autoPublish
            }, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.settings_saved, 'success');
                KK_Pinterest.utils.hideSpinner($btn);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        saveAdvanced: function($form) {
            var $btn = $('#kk-save-advanced');
            var data = {
                history_retention: $('#kk-history-retention').val(),
                debug_logging: $('#kk-debug-logging').is(':checked') ? 1 : 0,
                request_timeout: $('#kk-request-timeout').val()
            };

            KK_Pinterest.utils.showSpinner($btn);

            $.post(kk_pinterest_vars.ajax_url, {
                action: 'kk_pinterest_save_advanced',
                nonce: kk_pinterest_vars.nonce,
                settings: data
            }, function(response) {
                if (response.success) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.settings_saved, 'success');
                } else {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), response.data.message, 'error');
                }
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        refreshToken: function($btn) {
            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('refresh_token', {}, function() {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.token_refreshed, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        testConnection: function($btn) {
            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('test_connection', {}, function(result) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), result.message || kk_pinterest_vars.i18n.connection_ok, 'success');
                setTimeout(function() { location.reload(); }, 1000);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        testOAuth: function($btn) {
            var self = this;
            KK_Pinterest.utils.showSpinner($btn);

            KK_Pinterest.utils.ajax('test_oauth', {}, function(result) {
                var $container = $('#kk-oauth-diagnostics');
                var html = '<div class="kk-diagnostics-result">';
                html += '<h4>OAuth Diagnostics</h4>';
                html += '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
                html += '<thead><tr><th>Setting</th><th>Value</th></tr></thead><tbody>';
                
                $.each(result, function(key, value) {
                    var displayValue = value;
                    if (typeof value === 'boolean') {
                        displayValue = value ? 'Yes' : 'No';
                    } else if (typeof value === 'object' && value !== null) {
                        displayValue = JSON.stringify(value, null, 2);
                    } else if (value === '' || value === null || value === undefined) {
                        displayValue = '<em>(empty)</em>';
                    } else if (key === 'auth_url' && value) {
                        displayValue = '<a href="' + value + '" target="_blank">' + value.substring(0, 80) + '...</a>';
                    } else if (key === 'redirect_uri' && value) {
                        displayValue = '<code>' + value + '</code>';
                    }
                    html += '<tr><td><strong>' + key + '</strong></td><td>' + displayValue + '</td></tr>';
                });
                
                html += '</tbody></table>';
                
                // Add helpful hints
                html += '<div class="kk-diagnostics-hints" style="margin-top: 15px;">';
                html += '<h5>Common Issues:</h5><ul>';
                
                if (!result.is_configured) {
                    html += '<li style="color: #dc3232;">App credentials not configured. Save your App ID and App Secret first.</li>';
                }
                if (!result.app_secret_set) {
                    html += '<li style="color: #dc3232;">App Secret is empty. This must be set for OAuth to work.</li>';
                }
                if (!result.is_authenticated && result.is_configured) {
                    html += '<li>Not connected. Click "Connect to Pinterest" to start OAuth flow.</li>';
                }
                if (result.token_expires_at && result.token_expires_at < Math.floor(Date.now() / 1000)) {
                    html += '<li style="color: #dc3232;">Access token has expired. Click "Refresh Token" or reconnect.</li>';
                }
                
                html += '<li>Verify the redirect URI in Pinterest Developer App matches exactly: <code>' + result.redirect_uri + '</code></li>';
                html += '<li>Ensure Pinterest App has scopes: boards:read, boards:write, pins:read, pins:write, user_accounts:read</li>';
                html += '<li>If app is in "Trial" mode, it may have rate limits or restrictions.</li>';
                html += '</ul></div>';
                
                html += '</div>';
                
                $container.html(html).show();
                KK_Pinterest.utils.hideSpinner($btn);
            }, function(msg) {
                KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), msg, 'error');
                KK_Pinterest.utils.hideSpinner($btn);
            });
        },

        toggleDebugLog: function($btn) {
            var $logContainer = $('#kk-debug-log');
            var isVisible = $logContainer.is(':visible');
            
            if (isVisible) {
                $logContainer.hide();
            } else {
                $logContainer.show();
                this.loadDebugLog($btn);
            }
        },

        loadDebugLog: function($btn) {
            var self = this;
            var $output = $('#kk-debug-log .kk-log-output');
            
            KK_Pinterest.utils.ajax('get_debug_log', {}, function(result) {
                $output.text(result.log || 'No log entries found');
            }, function(msg) {
                $output.text('Error loading log: ' + msg);
            });
        },

        disconnect: function($btn) {
            KK_Pinterest.utils.showSpinner($btn);

            // Delete the access token
            $.post(kk_pinterest_vars.ajax_url, {
                action: 'kk_pinterest_disconnect',
                nonce: kk_pinterest_vars.nonce
            }, function(response) {
                if (response.success) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.disconnected, 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), response.data.message, 'error');
                    KK_Pinterest.utils.hideSpinner($btn);
                }
            });
        },

        resetPlugin: function() {
            $.post(kk_pinterest_vars.ajax_url, {
                action: 'kk_pinterest_reset_plugin',
                nonce: kk_pinterest_vars.nonce
            }, function(response) {
                if (response.success) {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), kk_pinterest_vars.i18n.plugin_reset, 'success');
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    KK_Pinterest.utils.showNotice($('.kk-pinterest-wrap'), response.data.message, 'error');
                }
            });
        }
    };

    // ==========================================================================
    // Document Ready
    // ==========================================================================

    $(document).ready(function() {
        // Initialize modal system
        KK_Pinterest.Modal.init();

        // Initialize components based on current page
        var page = kk_pinterest_vars.current_page;

        if (page === 'kinderkleurplaten-pinterest-boards') {
            KK_Pinterest.Boards.init();
        } else if (page === 'kinderkleurplaten-pinterest-publish') {
            KK_Pinterest.Publish.init();
        } else if (page === 'kinderkleurplaten-pinterest-history') {
            KK_Pinterest.History.init();
        } else if (page === 'kinderkleurplaten-pinterest-settings') {
            KK_Pinterest.Settings.init();
        }

        // Global: dismissible notices
        $(document).on('click', '.notice-dismiss', function() {
            $(this).closest('.notice').fadeOut(function() { $(this).remove(); });
        });

        // Global AJAX error handler for unhandled jQuery AJAX errors
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            if (settings.url && settings.url.indexOf('admin-ajax.php') !== -1 &&
                settings.data && settings.data.indexOf('kk_pinterest') !== -1) {
                console.error('KK Pinterest AJAX Error:', thrownError, settings.data);
                var $container = $('.kk-pinterest-wrap');
                if ($container.length) {
                    KK_Pinterest.utils.showNotice($container, kk_pinterest_vars.i18n.ajax_error, 'error');
                }
            }
        });
    });

})(jQuery);