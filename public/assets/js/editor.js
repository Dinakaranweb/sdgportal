/* Rich text editing for the control panel.
   Any textarea carrying data-editor becomes a Summernote editor. Images are
   uploaded to the portal rather than inlined as data URIs, which keeps the
   saved markup small. */
(function () {
    'use strict';

    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.summernote) {
        return;
    }

    var $ = window.jQuery;
    var meta = function (name) {
        var tag = document.querySelector('meta[name="' + name + '"]');
        return tag ? tag.getAttribute('content') : null;
    };

    var uploadUrl = meta('editor-upload');
    var csrf = meta('csrf-token');

    var toolbar = [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'table', 'hr']],
        ['view', ['codeview', 'fullscreen', 'help']]
    ];

    var compactToolbar = [
        ['font', ['bold', 'italic', 'underline', 'clear']],
        ['para', ['ul', 'ol']],
        ['insert', ['link']],
        ['view', ['codeview']]
    ];

    function upload(editor, files) {
        if (!uploadUrl) {
            window.alert('Image uploading is not available on this screen.');
            return;
        }

        Array.prototype.forEach.call(files, function (file) {
            var payload = new FormData();
            payload.append('file', file);

            window.fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: payload,
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().then(function (body) {
                        return { ok: response.ok, body: body };
                    });
                })
                .then(function (result) {
                    if (result.ok && result.body.url) {
                        $(editor).summernote('insertImage', result.body.url, file.name);
                        return;
                    }

                    var message = result.body && result.body.message
                        ? result.body.message
                        : 'That image could not be uploaded.';
                    window.alert(message);
                })
                .catch(function () {
                    window.alert('That image could not be uploaded.');
                });
        });
    }

    /**
     * Turn one textarea into an editor. Safe to call more than once.
     */
    function build(area) {
        if (!area || area.getAttribute('data-editor-ready')) {
            return;
        }

        area.setAttribute('data-editor-ready', '1');

        var compact = area.getAttribute('data-editor') === 'compact';
        var height = parseInt(area.getAttribute('data-editor-height'), 10) || (compact ? 140 : 320);

        $(area).summernote({
            height: height,
            minHeight: compact ? 110 : 200,
            dialogsInBody: true,
            disableResizeEditor: false,
            toolbar: compact ? compactToolbar : toolbar,
            placeholder: area.getAttribute('placeholder') || '',
            styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote'],
            callbacks: {
                onImageUpload: function (files) {
                    upload(this, files);
                },
                onChange: function (contents) {
                    area.value = contents;
                },
                onBlur: function () {
                    area.value = $(area).summernote('code');
                }
            }
        });
    }

    function buildAll(scope) {
        (scope || document).querySelectorAll('textarea[data-editor]').forEach(build);
    }

    // Exposed so the repeater can light up rows added after page load.
    window.portalEditor = { build: build, buildAll: buildAll };

    buildAll(document);

    // Copy the editor contents back into the textarea before anything is sent.
    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form || typeof form.querySelectorAll !== 'function') {
            return;
        }

        form.querySelectorAll('textarea[data-editor-ready]').forEach(function (area) {
            try {
                var code = $(area).summernote('code');
                area.value = $(area).summernote('isEmpty') ? '' : code;
            } catch (error) {
                /* the editor was removed from the page; leave the raw value */
            }
        });
    }, true);
})();
