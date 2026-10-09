/* Control panel behaviour: navigation drawer, slug suggestions, colour inputs,
   repeatable rows, the media picker and delete confirmation. */
(function () {
    'use strict';

    var body = document.body;

    /* ---- navigation drawer ------------------------------------------- */

    var burger = document.querySelector('[data-ad-burger]');

    if (burger) {
        burger.addEventListener('click', function () {
            body.classList.toggle('ad-open');
        });
    }

    document.addEventListener('click', function (event) {
        if (body.classList.contains('ad-open') &&
            !event.target.closest('.ad-nav') &&
            !event.target.closest('[data-ad-burger]')) {
            body.classList.remove('ad-open');
        }
    });

    /* ---- slug suggestion -------------------------------------------- */

    function slugify(value) {
        return value
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 80);
    }

    document.querySelectorAll('[data-slug-source]').forEach(function (source) {
        var target = document.querySelector(source.getAttribute('data-slug-source'));

        if (!target) {
            return;
        }

        var locked = target.value.trim() !== '';

        target.addEventListener('input', function () {
            locked = target.value.trim() !== '';
        });

        source.addEventListener('input', function () {
            if (!locked) {
                target.value = slugify(source.value);
            }
        });
    });

    /* ---- colour inputs keep the swatch and the hex field in step ----- */

    document.querySelectorAll('[data-colour-pair]').forEach(function (wrapper) {
        var swatch = wrapper.querySelector('input[type=color]');
        var text = wrapper.querySelector('input[type=text]');

        if (!swatch || !text) {
            return;
        }

        swatch.addEventListener('input', function () {
            text.value = swatch.value;
        });

        text.addEventListener('input', function () {
            var value = text.value.trim();
            if (/^#([0-9a-f]{6})$/i.test(value)) {
                swatch.value = value;
            }
        });
    });

    /* ---- repeatable rows -------------------------------------------- */

    function renumber(container) {
        var prefix = container.getAttribute('data-repeater');

        container.querySelectorAll('[data-rep-row]').forEach(function (row, index) {
            row.querySelectorAll('[data-rep-field]').forEach(function (input) {
                var field = input.getAttribute('data-rep-field');
                input.name = prefix + '[' + index + '][' + field + ']';
            });

            var counter = row.querySelector('[data-rep-index]');
            if (counter) {
                counter.textContent = index + 1;
            }
        });
    }

    document.querySelectorAll('[data-repeater]').forEach(function (container) {
        var template = container.querySelector('[data-rep-template]');

        container.addEventListener('click', function (event) {
            var addButton = event.target.closest('[data-rep-add]');
            var removeButton = event.target.closest('[data-rep-remove]');
            var upButton = event.target.closest('[data-rep-up]');
            var downButton = event.target.closest('[data-rep-down]');

            if (addButton && template) {
                event.preventDefault();
                var markup = template.innerHTML || template.textContent;
                var holder = document.createElement('div');
                holder.innerHTML = markup.trim();
                var fresh = holder.firstElementChild;
                container.querySelector('[data-rep-rows]').appendChild(fresh);
                renumber(container);

                // Rows added after page load still need their editors built.
                if (window.portalEditor) {
                    window.portalEditor.buildAll(fresh);
                }

                var first = fresh.querySelector('input, textarea, select');
                if (first) {
                    first.focus();
                }
            }

            if (removeButton) {
                event.preventDefault();
                var row = removeButton.closest('[data-rep-row]');
                if (row && window.confirm('Remove this row?')) {
                    row.remove();
                    renumber(container);
                }
            }

            if (upButton) {
                event.preventDefault();
                var current = upButton.closest('[data-rep-row]');
                var previous = current && current.previousElementSibling;
                if (previous) {
                    current.parentNode.insertBefore(current, previous);
                    renumber(container);
                }
            }

            if (downButton) {
                event.preventDefault();
                var node = downButton.closest('[data-rep-row]');
                var following = node && node.nextElementSibling;
                if (following) {
                    node.parentNode.insertBefore(following, node);
                    renumber(container);
                }
            }
        });
    });

    /* ---- simple table builder ---------------------------------------- */

    document.querySelectorAll('[data-grid-table]').forEach(function (grid) {
        grid.addEventListener('click', function (event) {
            var addRow = event.target.closest('[data-gt-add-row]');
            var addCol = event.target.closest('[data-gt-add-col]');
            var dropRow = event.target.closest('[data-gt-drop-row]');

            if (addRow) {
                event.preventDefault();
                var rows = grid.querySelectorAll('[data-gt-row]');
                var last = rows[rows.length - 1];
                if (!last) {
                    return;
                }
                var clone = last.cloneNode(true);
                clone.querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
                last.parentNode.appendChild(clone);
                reindexGrid(grid);
            }

            if (addCol) {
                event.preventDefault();
                grid.querySelectorAll('[data-gt-row], [data-gt-head]').forEach(function (row) {
                    var inputs = row.querySelectorAll('input');
                    if (!inputs.length) {
                        return;
                    }
                    var clone = inputs[inputs.length - 1].cloneNode(true);
                    clone.value = '';
                    inputs[inputs.length - 1].parentNode.insertBefore(clone, row.querySelector('[data-gt-drop-row]'));
                });
                reindexGrid(grid);
            }

            if (dropRow) {
                event.preventDefault();
                var target = dropRow.closest('[data-gt-row]');
                if (target && grid.querySelectorAll('[data-gt-row]').length > 1) {
                    target.remove();
                    reindexGrid(grid);
                }
            }
        });
    });

    function reindexGrid(grid) {
        var head = grid.querySelector('[data-gt-head]');

        if (head) {
            head.querySelectorAll('input').forEach(function (input, index) {
                input.name = 'headers[' + index + ']';
            });
        }

        grid.querySelectorAll('[data-gt-row]').forEach(function (row, rowIndex) {
            row.querySelectorAll('input').forEach(function (input, cellIndex) {
                input.name = 'table_rows[' + rowIndex + '][' + cellIndex + ']';
            });
        });
    }

    /* ---- media picker ------------------------------------------------ */

    document.querySelectorAll('[data-media-field]').forEach(function (field) {
        var hidden = field.querySelector('[data-media-path]');
        var preview = field.querySelector('[data-media-preview]');
        var caption = field.querySelector('[data-media-caption]');
        var upload = field.querySelector('input[type=file]');
        var remover = field.querySelector('[data-media-remove]');

        field.querySelectorAll('[data-media-pick]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                var path = button.getAttribute('data-media-pick');
                var url = button.getAttribute('data-media-url');

                if (hidden) {
                    hidden.value = path;
                }
                if (preview) {
                    preview.innerHTML = '<img src="' + url + '" alt="">';
                }
                if (caption) {
                    caption.innerHTML = '<code>' + path + '</code>';
                }
                if (upload) {
                    upload.value = '';
                }
                if (remover) {
                    remover.checked = false;
                }
            });
        });

        if (upload && preview) {
            upload.addEventListener('change', function () {
                var file = upload.files && upload.files[0];
                if (!file) {
                    return;
                }
                if (hidden) {
                    hidden.value = '';
                }
                if (remover) {
                    remover.checked = false;
                }
                if (file.type.indexOf('image/') === 0) {
                    var reader = new FileReader();
                    reader.onload = function (event) {
                        preview.innerHTML = '<img src="' + event.target.result + '" alt="">';
                    };
                    reader.readAsDataURL(file);
                }
                if (caption) {
                    caption.textContent = file.name;
                }
            });
        }
    });

    /* ---- destructive actions ask first ------------------------------- */

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    /* ---- copy helpers ------------------------------------------------ */

    document.querySelectorAll('[data-copy-field]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.querySelector(button.getAttribute('data-copy-field'));
            if (!input) {
                return;
            }
            input.select();
            try {
                document.execCommand('copy');
                var label = button.textContent;
                button.textContent = 'Copied';
                window.setTimeout(function () {
                    button.textContent = label;
                }, 1600);
            } catch (error) {
                /* clipboard unavailable; the field is selected for a manual copy */
            }
        });
    });

    /* ---- reordering lists ------------------------------------------- */

    document.querySelectorAll('[data-order-list]').forEach(function (list) {
        list.addEventListener('click', function (event) {
            var up = event.target.closest('[data-order-up]');
            var down = event.target.closest('[data-order-down]');

            if (!up && !down) {
                return;
            }

            event.preventDefault();
            var row = (up || down).closest('[data-order-row]');
            if (!row) {
                return;
            }

            var sibling = up ? row.previousElementSibling : row.nextElementSibling;
            if (!sibling || !sibling.hasAttribute('data-order-row')) {
                return;
            }

            if (up) {
                row.parentNode.insertBefore(row, sibling);
            } else {
                row.parentNode.insertBefore(sibling, row);
            }

            var form = list.closest('form') || document.querySelector(list.getAttribute('data-order-list'));
            if (form) {
                form.querySelectorAll('[data-order-input]').forEach(function (node) {
                    node.remove();
                });

                list.querySelectorAll('[data-order-row]').forEach(function (node) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order[]';
                    input.value = node.getAttribute('data-order-row');
                    input.setAttribute('data-order-input', '');
                    form.appendChild(input);
                });

                var save = form.querySelector('[data-order-save]');
                if (save) {
                    save.removeAttribute('disabled');
                }
            }
        });
    });
})();
