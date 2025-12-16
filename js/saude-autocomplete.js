(function() {
    const TEXTAREA_SELECTOR = 'textarea[data-saude-autocomplete="true"]';
    const MAX_SUGGESTIONS = 6;
    const MIN_CHARS = 3;
    const MIN_AUTOCORRECT = 4;
    const WORD_BOUNDARY = /[\s,.;:!?()\[\]{}"']/;
    const TOKEN_REGEX = /[A-Za-zÀ-ÿ]{4,}/g;

    function resolveBaseUrl() {
        if (window.BASE_URL) {
            return String(window.BASE_URL).replace(/\/+$/, '');
        }
        const baseTag = document.querySelector('base');
        if (baseTag && baseTag.href) {
            return baseTag.href.replace(/\/+$/, '');
        }
        return '';
    }

    const dictionaryLoader = {
        cache: null,
        promise: null,
        wordIndex: null,
        wordLookup: null,
        load() {
            if (this.cache) {
                return Promise.resolve(this.cache);
            }
            if (this.promise) {
                return this.promise;
            }
            const base = resolveBaseUrl();
            const url = base + '/data/saude_terms.json';
            this.promise = fetch(url, { cache: 'force-cache' })
                .then(resp => {
                    if (!resp.ok) {
                        throw new Error('Não foi possível carregar o dicionário.');
                    }
                    return resp.json();
                })
                .then(data => {
                    this.cache = Array.isArray(data) ? data : [];
                    this.wordIndex = null;
                    this.wordLookup = null;
                    this.prepareWordIndex();
                    return this.cache;
                })
                .catch(err => {
                    console.error('[saude-autocomplete] Falha ao carregar termos', err);
                    this.promise = null;
                    return [];
                });
            return this.promise;
        },
        prepareWordIndex() {
            if (this.wordIndex) {
                return this.wordIndex;
            }
            const terms = this.cache || [];
            const seen = new Map();
            const list = [];
            terms.forEach(term => {
                const parts = String(term).split(/[^0-9A-Za-zÀ-ÿ]+/);
                parts.forEach(piece => {
                    const trimmed = piece.trim();
                    if (trimmed.length < MIN_AUTOCORRECT) {
                        return;
                    }
                    const lower = trimmed.toLowerCase();
                    if (seen.has(lower)) {
                        return;
                    }
                    seen.set(lower, trimmed);
                    list.push({ original: trimmed, lower });
                });
            });
            this.wordLookup = seen;
            this.wordIndex = list;
            return list;
        },
        ready() {
            return this.load().then(() => {
                this.prepareWordIndex();
                return this;
            });
        },
        findClosest(word) {
            if (!word || word.length < MIN_AUTOCORRECT) {
                return null;
            }
            this.prepareWordIndex();
            const lower = word.toLowerCase();
            if (this.wordLookup && this.wordLookup.has(lower)) {
                return this.wordLookup.get(lower);
            }
            const threshold = distanceThreshold(word.length);
            let best = null;
            let bestDist = Infinity;
            for (let i = 0; i < this.wordIndex.length; i++) {
                const entry = this.wordIndex[i];
                if (entry.lower[0] !== lower[0]) continue;
                const lenDiff = Math.abs(entry.lower.length - lower.length);
                if (lenDiff > threshold) continue;
                const dist = levenshtein(lower, entry.lower);
                if (dist < bestDist && dist <= threshold) {
                    best = entry.original;
                    bestDist = dist;
                    if (dist === 0) break;
                }
            }
            return best;
        }
    };

    function distanceThreshold(len) {
        if (len <= 5) return 1;
        if (len <= 8) return 2;
        return 3;
    }

    function levenshtein(a, b) {
        const matrix = [];
        const lenA = a.length;
        const lenB = b.length;
        for (let i = 0; i <= lenB; i++) {
            matrix[i] = [i];
        }
        for (let j = 0; j <= lenA; j++) {
            matrix[0][j] = j;
        }
        for (let i = 1; i <= lenB; i++) {
            for (let j = 1; j <= lenA; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }
        return matrix[lenB][lenA];
    }

    function findToken(value, caret) {
        let start = caret;
        while (start > 0 && !WORD_BOUNDARY.test(value[start - 1])) {
            start--;
        }
        const token = value.slice(start, caret);
        return { start, end: caret, token };
    }

    function findPreviousToken(value, caret) {
        let end = caret;
        while (end > 0 && WORD_BOUNDARY.test(value[end - 1])) {
            end--;
        }
        let start = end;
        while (start > 0 && !WORD_BOUNDARY.test(value[start - 1])) {
            start--;
        }
        const token = value.slice(start, end);
        return { start, end, token };
    }

    function createSuggestionList(field) {
        const container = document.createElement('div');
        container.className = 'saude-suggestions d-none';
        field.parentNode.insertBefore(container, field.nextSibling);
        return container;
    }

    function renderSuggestions(field, listEl, suggestions) {
        listEl.innerHTML = '';
        if (!suggestions.length) {
            listEl.classList.add('d-none');
            return;
        }
        listEl.classList.remove('d-none');
        suggestions.forEach((term) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'saude-suggestions__item';
            button.textContent = term;
            button.addEventListener('mousedown', function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                insertSuggestion(field, term);
                renderSuggestions(field, listEl, []);
            });
            listEl.appendChild(button);
        });
    }

    function insertSuggestion(field, term) {
        const caret = field.selectionStart || 0;
        const { start, end } = findToken(field.value, caret);
        const before = field.value.slice(0, start);
        const after = field.value.slice(end);
        const needsSpace = before && !/\s$/.test(before);
        const newValue = before + (needsSpace ? ' ' : '') + term + ' ';
        const newCaret = newValue.length;
        field.value = newValue + after.replace(/^\s*/, '');
        field.focus();
        field.setSelectionRange(newCaret, newCaret);
        field.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function filterSuggestions(dict, token) {
        if (!token || token.length < MIN_CHARS) {
            return [];
        }
        const lower = token.toLowerCase();
        const starts = [];
        const contains = [];
        for (let i = 0; i < dict.length; i++) {
            const term = dict[i];
            const norm = term.toLowerCase();
            if (norm.startsWith(lower)) {
                starts.push(term);
            } else if (norm.includes(lower)) {
                contains.push(term);
            }
            if (starts.length >= MAX_SUGGESTIONS) {
                break;
            }
        }
        return (starts.concat(contains)).slice(0, MAX_SUGGESTIONS);
    }

    function autoCorrectLastWord(field) {
        dictionaryLoader.ready().then(() => {
            const caret = field.selectionStart ?? field.value.length;
            const { start, end, token } = findPreviousToken(field.value, caret);
            const normalized = token.trim();
            if (!normalized || normalized.length < MIN_AUTOCORRECT) {
                return;
            }
            const replacement = dictionaryLoader.findClosest(normalized);
            if (!replacement || replacement.toLowerCase() === normalized.toLowerCase()) {
                return;
            }
            replaceRange(field, start, end, replacement, caret);
        });
    }

    function autoCorrectEntireField(field) {
        dictionaryLoader.ready().then(() => {
            const value = field.value;
            let lastIndex = 0;
            let result = '';
            let changed = false;
            let match;
            while ((match = TOKEN_REGEX.exec(value)) !== null) {
                result += value.slice(lastIndex, match.index);
                const token = match[0];
                const replacement = dictionaryLoader.findClosest(token);
                if (replacement && replacement.toLowerCase() !== token.toLowerCase()) {
                    result += replacement;
                    changed = true;
                } else {
                    result += token;
                }
                lastIndex = match.index + token.length;
            }
            result += value.slice(lastIndex);
            if (changed) {
                const caret = field.selectionStart ?? result.length;
                field.value = result;
                const newCaret = Math.min(caret, result.length);
                field.setSelectionRange(newCaret, newCaret);
            }
        });
    }

    function replaceRange(field, start, end, replacement, currentCaret) {
        const before = field.value.slice(0, start);
        const after = field.value.slice(end);
        const newValue = before + replacement + after;
        const delta = replacement.length - (end - start);
        const caretPos = typeof currentCaret === 'number' ? Math.max(0, currentCaret + delta) : start + replacement.length;
        const scroll = field.scrollTop;
        field.value = newValue;
        field.scrollTop = scroll;
        field.setSelectionRange(caretPos, caretPos);
    }

    function attachAutocomplete(field) {
        let listEl = null;
        let dictionary = [];
        const ensureList = () => {
            if (!listEl) {
                listEl = createSuggestionList(field);
            }
            return listEl;
        };

        const updateSuggestions = () => {
            dictionaryLoader.load().then((dict) => {
                dictionary = dict;
                const caret = field.selectionStart || 0;
                const { token } = findToken(field.value, caret);
                const suggestions = filterSuggestions(dict, token.trim());
                renderSuggestions(field, ensureList(), suggestions);
            });
        };

        field.addEventListener('input', function(evt) {
            updateSuggestions();
            const lastChar = typeof evt.data === 'string'
                ? evt.data.slice(-1)
                : (field.value[(field.selectionStart || 0) - 1] || '');
            if (lastChar && WORD_BOUNDARY.test(lastChar)) {
                autoCorrectLastWord(field);
            }
        });
        field.addEventListener('focus', updateSuggestions);
        field.addEventListener('blur', function() {
            autoCorrectEntireField(field);
            setTimeout(() => {
                if (listEl) listEl.classList.add('d-none');
            }, 120);
        });
    }

    function init() {
        const fields = document.querySelectorAll(TEXTAREA_SELECTOR);
        fields.forEach((field) => {
            if (field.dataset.saudeAutocompleteReady === '1') {
                return;
            }
            field.dataset.saudeAutocompleteReady = '1';
            field.parentElement?.classList.add('saude-autocomplete-wrapper');
            attachAutocomplete(field);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
