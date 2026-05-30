@props([
    'digits' => 6,
    'name' => 'code',
])

<div
    @focus-2fa-auth-code.window="$refs.input1?.focus()"
    @clear-2fa-auth-code.window="clearAll()"
    class="relative"
    x-data="{
        totalDigits: @js($digits),
        digitIndices: @js(range(1, $digits)),
        init() {
            $nextTick(() => {
                this.$refs.input1?.focus();
            });
        },
        getInput(index) {
            return this.$refs['input' + index];
        },
        setValue(index, value) {
            this.getInput(index).value = value;
        },
        getCode() {
            return this.digitIndices
                .map(i => this.getInput(i).value)
                .join('');
        },
        updateHiddenField() {
            this.$refs.code.value = this.getCode();
            this.$refs.code.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.code.dispatchEvent(new Event('change', { bubbles: true }));
        },
        handleNumberKey(index, key) {
            this.setValue(index, key);

            if (index < this.totalDigits) {
                this.getInput(index + 1).focus();
            }

            $nextTick(() => {
                this.updateHiddenField();
            });
        },
        handleBackspace(index) {
            const currentInput = this.getInput(index);

            if (currentInput.value !== '') {
                currentInput.value = '';
                this.updateHiddenField();
                return;
            }

            if (index <= 1) {
                return;
            }

            const previousInput = this.getInput(index - 1);

            previousInput.value = '';
            previousInput.focus();

            this.updateHiddenField();
        },
        handleKeyDown(index, event) {
            const key = event.key;

            if (/^[0-9]$/.test(key)) {
                event.preventDefault();
                this.handleNumberKey(index, key);
                return;
            }

            if (key === 'Backspace') {
                event.preventDefault();
                this.handleBackspace(index);
                return;
            }
        },
        handlePaste(event) {
            event.preventDefault();

            const pastedText = (event.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, this.totalDigits);
            
            this.digitIndices.forEach((index, i) => {
                if (numericOnly[i]) {
                    this.setValue(index, numericOnly[i]);
                }
            });

            this.updateHiddenField();
            
            const lastFilledIndex = Math.min(numericOnly.length, this.totalDigits);
            this.getInput(Math.min(lastFilledIndex + 1, this.totalDigits)).focus();
        },
        clearAll() {
            this.digitIndices.forEach(index => {
                this.setValue(index, '');
            });

            this.$refs.code.value = '';
            this.$refs.input1?.focus();
        }
    }"
>
    <div class="flex items-center justify-center gap-2 md:gap-3" dir="ltr">
        @for ($x = 1; $x <= $digits; $x++)
            <input
                x-ref="input{{ $x }}"
                type="text"
                inputmode="numeric"
                pattern="[0-9]"
                maxlength="1"
                autocomplete="one-time-code"
                @paste="handlePaste"
                @keydown="handleKeyDown({{ $x }}, $event)"
                @focus="$el.select()"
                @input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 1)"
                @class([
                    'flex size-12 items-center justify-center rounded-xl border-2 border-slate-200 bg-white text-center text-xl font-bold text-slate-900 shadow-sm transition-all outline-none',
                    'focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 hover:border-teal-500/50',
                    'dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:focus:border-teal-400 dark:focus:ring-teal-400/10',
                ])
            />
        @endfor
    </div>

    <input
        {{ $attributes->except(['digits']) }}
        type="hidden"
        x-ref="code"
        name="{{ $name }}"
        minlength="{{ $digits }}"
        maxlength="{{ $digits }}"
    />
</div>