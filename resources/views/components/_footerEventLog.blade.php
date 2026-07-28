{{--
    Footer event log

    The component deliberately has no dependency on UIkit, Echo, CRUD or Datatables.
    Applications can configure it before this layout is rendered with
    window.footerEventLogOptions = { timeout: 5500, maxVisible: 4, maxHistory: 20 }.
--}}
<style data-uikittemplate-footer-event-log-styles>
    #uikittemplate-footer-event-log {
        --uikittemplate-footer-log-background: #1e1e1e;
        --uikittemplate-footer-log-surface: #2a2a2a;
        --uikittemplate-footer-log-text: #f4f4f4;
        --uikittemplate-footer-log-muted: #c5c5c5;
        --uikittemplate-footer-log-info: #74c7ec;
        --uikittemplate-footer-log-success: #66d9a5;
        --uikittemplate-footer-log-warning: #ffd166;
        --uikittemplate-footer-log-error: #ff7b7b;
        --uikittemplate-footer-log-right: 1rem;
        --uikittemplate-footer-log-bottom: 1rem;
        position: fixed;
        z-index: 990;
        right: var(--uikittemplate-footer-log-right);
        bottom: var(--uikittemplate-footer-log-bottom);
        width: min(25rem, calc(100vw - var(--uikittemplate-footer-log-right) - var(--uikittemplate-footer-log-right)));
        color: var(--uikittemplate-footer-log-text);
        font: 0.8125rem/1.35 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        pointer-events: none;
    }

    #uikittemplate-footer-event-log button {
        font: inherit;
    }

    .uikittemplate-footer-event-log__toolbar,
    .uikittemplate-footer-event-log__panel,
    .uikittemplate-footer-event-log__events {
        pointer-events: auto;
    }

    .uikittemplate-footer-event-log__events {
        display: grid;
        gap: 0.5rem;
        margin: 0 0 0.5rem;
        padding: 0;
        list-style: none;
    }

    .uikittemplate-footer-event-log__events:empty {
        margin-bottom: 0;
    }

    .uikittemplate-footer-event-log__entry {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.25rem 0.625rem;
        align-items: baseline;
        padding: 0.625rem 0.75rem;
        overflow-wrap: anywhere;
        border-left: 3px solid var(--uikittemplate-footer-log-info);
        border-radius: 3px;
        background: var(--uikittemplate-footer-log-background);
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.24);
        animation: uikittemplate-footer-log-in 160ms ease-out both;
    }

    .uikittemplate-footer-event-log__entry.is-leaving {
        animation: uikittemplate-footer-log-out 160ms ease-in both;
    }

    .uikittemplate-footer-event-log__entry--success { border-left-color: var(--uikittemplate-footer-log-success); }
    .uikittemplate-footer-event-log__entry--warning { border-left-color: var(--uikittemplate-footer-log-warning); }
    .uikittemplate-footer-event-log__entry--error { border-left-color: var(--uikittemplate-footer-log-error); }

    .uikittemplate-footer-event-log__time,
    .uikittemplate-footer-event-log__source {
        color: var(--uikittemplate-footer-log-muted);
        font-size: 0.75rem;
    }

    .uikittemplate-footer-event-log__source {
        justify-self: end;
    }

    .uikittemplate-footer-event-log__message {
        grid-column: 1 / -1;
    }

    .uikittemplate-footer-event-log__toolbar {
        display: none;
        align-items: center;
        justify-content: space-between;
        min-height: 2rem;
        padding: 0 0.5rem 0 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 3px;
        background: var(--uikittemplate-footer-log-background);
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.24);
    }

    .uikittemplate-footer-event-log__toggle,
    .uikittemplate-footer-event-log__clear {
        min-height: 2rem;
        border: 0;
        color: var(--uikittemplate-footer-log-text);
        background: transparent;
        cursor: pointer;
    }

    .uikittemplate-footer-event-log__toggle {
        flex: 1;
        padding: 0 0.5rem 0 0;
        text-align: left;
    }

    .uikittemplate-footer-event-log__clear {
        padding: 0 0.25rem;
        color: var(--uikittemplate-footer-log-muted);
    }

    .uikittemplate-footer-event-log__toggle:hover,
    .uikittemplate-footer-event-log__toggle:focus-visible,
    .uikittemplate-footer-event-log__clear:hover,
    .uikittemplate-footer-event-log__clear:focus-visible {
        color: #ffffff;
        outline: 2px solid var(--uikittemplate-footer-log-info);
        outline-offset: -2px;
    }

    .uikittemplate-footer-event-log__panel {
        max-height: min(16rem, 45vh);
        margin-bottom: 0.5rem;
        overflow: auto;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 3px;
        background: var(--uikittemplate-footer-log-surface);
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.24);
    }

    .uikittemplate-footer-event-log__history {
        display: grid;
        gap: 1px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .uikittemplate-footer-event-log__history .uikittemplate-footer-event-log__entry {
        border-radius: 0;
        box-shadow: none;
        animation: none;
    }

    @keyframes uikittemplate-footer-log-in {
        from { opacity: 0; transform: translateY(0.5rem); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes uikittemplate-footer-log-out {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(0.5rem); }
    }

    @media (prefers-reduced-motion: reduce) {
        .uikittemplate-footer-event-log__entry,
        .uikittemplate-footer-event-log__entry.is-leaving {
            animation: none;
        }
    }

    @media (max-width: 639px) {
        #uikittemplate-footer-event-log {
            --uikittemplate-footer-log-right: 0.5rem;
            --uikittemplate-footer-log-bottom: 0.5rem;
        }
    }
</style>

<section id="uikittemplate-footer-event-log" aria-label="Console eventi">
    <ol class="uikittemplate-footer-event-log__events" aria-live="polite" aria-relevant="additions" aria-atomic="false"></ol>

    <div id="uikittemplate-footer-event-log-history" class="uikittemplate-footer-event-log__panel" hidden>
        <ol class="uikittemplate-footer-event-log__history" aria-label="Cronologia eventi della pagina"></ol>
    </div>

    <div class="uikittemplate-footer-event-log__toolbar">
        <button type="button" class="uikittemplate-footer-event-log__toggle" aria-expanded="false" aria-controls="uikittemplate-footer-event-log-history" aria-label="Espandi console eventi">
            Eventi
        </button>
        <button type="button" class="uikittemplate-footer-event-log__clear" aria-label="Nascondi le notifiche visibili" title="Nascondi le notifiche visibili">×</button>
    </div>
</section>

<script>
    (function (window, document) {
        'use strict';

        if (window.__uikittemplateFooterEventLogInitialized) {
            return;
        }

        window.__uikittemplateFooterEventLogInitialized = true;

        var root = document.getElementById('uikittemplate-footer-event-log');
        var visibleEvents = root.querySelector('.uikittemplate-footer-event-log__events');
        var panel = root.querySelector('.uikittemplate-footer-event-log__panel');
        var historyList = root.querySelector('.uikittemplate-footer-event-log__history');
        var toggle = root.querySelector('.uikittemplate-footer-event-log__toggle');
        var clear = root.querySelector('.uikittemplate-footer-event-log__clear');
        var levels = ['info', 'success', 'warning', 'error'];
        var configuredOptions = window.footerEventLogOptions || {};
        var options = {
            timeout: positiveInteger(configuredOptions.timeout, 5500),
            maxVisible: positiveInteger(configuredOptions.maxVisible, 4),
            maxHistory: positiveInteger(configuredOptions.maxHistory, 20)
        };
        var history = [];
        var activeEntries = {};
        var activeOrder = [];
        var nextId = 1;

        function positiveInteger(value, fallback) {
            value = Number(value);

            return Number.isFinite(value) && value > 0 ? Math.floor(value) : fallback;
        }

        function normalise(entry) {
            if (!entry || entry.message === undefined || entry.message === null) {
                return null;
            }

            var message = String(entry.message).trim();
            if (!message) {
                return null;
            }

            var level = String(entry.level || 'info').toLowerCase();
            if (levels.indexOf(level) === -1) {
                level = 'info';
            }

            var source = entry.source === undefined || entry.source === null ? '' : String(entry.source).trim();

            return {
                id: nextId++,
                message: message,
                level: level,
                source: source,
                timestamp: new Date()
            };
        }

        function formatTime(timestamp) {
            return timestamp.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        function createEntry(entry) {
            var item = document.createElement('li');
            item.className = 'uikittemplate-footer-event-log__entry uikittemplate-footer-event-log__entry--' + entry.level;
            item.dataset.footerEventLogId = entry.id;

            var time = document.createElement('time');
            time.className = 'uikittemplate-footer-event-log__time';
            time.dateTime = entry.timestamp.toISOString();
            time.textContent = formatTime(entry.timestamp);
            item.appendChild(time);

            if (entry.source) {
                var source = document.createElement('span');
                source.className = 'uikittemplate-footer-event-log__source';
                source.textContent = entry.source;
                item.appendChild(source);
            }

            var message = document.createElement('span');
            message.className = 'uikittemplate-footer-event-log__message';
            message.textContent = entry.message;
            item.appendChild(message);

            return item;
        }

        function renderHistory() {
            while (historyList.firstChild) {
                historyList.removeChild(historyList.firstChild);
            }

            history.forEach(function (entry) {
                historyList.appendChild(createEntry(entry));
            });
        }

        function removeFromActiveOrder(id) {
            activeOrder = activeOrder.filter(function (entryId) {
                return entryId !== id;
            });
        }

        function dismiss(id, animate) {
            var active = activeEntries[id];
            if (!active) {
                return;
            }

            window.clearTimeout(active.timeout);
            delete activeEntries[id];
            removeFromActiveOrder(id);

            if (!animate) {
                active.node.remove();
                return;
            }

            active.node.classList.add('is-leaving');
            window.setTimeout(function () {
                active.node.remove();
            }, 170);
        }

        function showTransient(entry) {
            while (activeOrder.length >= options.maxVisible) {
                dismiss(activeOrder[0], false);
            }

            var node = createEntry(entry);
            visibleEvents.appendChild(node);
            activeOrder.push(entry.id);
            activeEntries[entry.id] = {
                node: node,
                timeout: window.setTimeout(function () {
                    dismiss(entry.id, true);
                }, options.timeout)
            };
        }

        function push(entry) {
            var normalisedEntry = normalise(entry);
            if (!normalisedEntry) {
                return null;
            }

            // `data` is deliberately not stored or rendered: event payloads may contain sensitive values.
            history.unshift(normalisedEntry);
            history = history.slice(0, options.maxHistory);
            renderHistory();
            showTransient(normalisedEntry);

            return {
                message: normalisedEntry.message,
                level: normalisedEntry.level,
                source: normalisedEntry.source,
                timestamp: normalisedEntry.timestamp.toISOString()
            };
        }

        function setExpanded(expanded) {
            panel.hidden = !expanded;
            toggle.setAttribute('aria-expanded', String(expanded));
            toggle.setAttribute('aria-label', expanded ? 'Riduci console eventi' : 'Espandi console eventi');
        }

        function clearVisible() {
            activeOrder.slice().forEach(function (id) {
                dismiss(id, false);
            });
        }

        var api = window.footerEventLog || {};
        api.push = push;
        api.clearVisible = clearVisible;
        api.expand = function () { setExpanded(true); };
        api.collapse = function () { setExpanded(false); };
        api.history = function () {
            return history.map(function (entry) {
                return {
                    message: entry.message,
                    level: entry.level,
                    source: entry.source,
                    timestamp: entry.timestamp.toISOString()
                };
            });
        };
        api.options = options;
        window.footerEventLog = api;

        // Datatables uses this optional hook for CRUD broadcasts. Keep an
        // application-provided implementation intact and map the hook to the
        // documented API only when no logger has already claimed that name.
        if (typeof window.addFooterLog !== 'function') {
            window.addFooterLog = function (message, context) {
                context = context || {};

                return push({
                    message: message,
                    level: context.level || 'info',
                    source: context.source || '',
                    data: context
                });
            };
        }

        toggle.addEventListener('click', function () {
            setExpanded(panel.hidden);
        });

        clear.addEventListener('click', clearVisible);

        window.addEventListener('uikittemplate:footer-log', function (event) {
            push(event.detail || {});
        });
    }(window, document));
</script>
