<?php
// Formulário de edição do RAH: reutiliza o form de cadastro original
// e injeta os valores atuais logo após o include.
$__rahEditPayload = $rahEditData ?? [];
include __DIR__ . '/form_cad_capeante_rah.php';

$rahJson = json_encode(
    $__rahEditPayload,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
);
$rahJsonB64 = base64_encode($rahJson ?: '{}');

?>
<script>
  (function () {
    let data = {};
    try {
      data = JSON.parse(atob('<?= htmlspecialchars($rahJsonB64, ENT_QUOTES, 'UTF-8') ?>'));
    } catch (e) {
      console.error('[RAH][populate] Falha ao decodificar payload', e);
      data = {};
    }

    if (!data || typeof data !== 'object') return;

    const populate = () => {
      const form = document.getElementById('form-capeante-rah');
      if (!form) return;

      const formatMoney = (val) => {
        if (val === null || typeof val === 'undefined' || val === '') return '';
        const str = String(val).trim();
        const R = window.RAH;

        // Valores gravados pelo backend vêm no formato "1234.56".
        if (/^-?\d+(\.\d+)?$/.test(str)) {
          const parsed = parseFloat(str);
          if (isFinite(parsed) && R && typeof R.floatToMoney === 'function') {
            return R.floatToMoney(parsed);
          }
          return str;
        }

        if (!R || typeof R.moneyToFloat !== 'function' || typeof R.floatToMoney !== 'function') {
          return str;
        }
        const num = R.moneyToFloat(str);
        return R.floatToMoney(num);
      };

      const setValue = (name, value) => {
        const el = form.querySelector('[name=\"' + name + '\"]');
        if (!el || value === null || typeof value === 'undefined') return;

        let finalValue = value;
        if (el.classList.contains('dinheiro')) {
          finalValue = formatMoney(value);
        }

        const tag = el.tagName.toLowerCase();
        if (el.type === 'checkbox') {
          el.checked = value === true || value === 's' || value === '1' || value === 1;
        } else if (el.type === 'radio') {
          const radios = form.querySelectorAll('input[type=\"radio\"][name=\"' + name + '\"]');
          radios.forEach((radio) => {
            radio.checked = String(radio.value) === String(value);
          });
        } else if (tag === 'select') {
          el.value = finalValue;
          if (window.jQuery && typeof window.jQuery(el).selectpicker === 'function') {
            window.jQuery(el).selectpicker('val', finalValue).trigger('change');
          }
        } else {
          el.value = finalValue;
        }

        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
      };

      const ensureHidden = (name) => {
        let input = form.querySelector('[name=\"' + name + '\"]');
        if (!input) {
          input = document.createElement('input');
          input.type = 'hidden';
          input.name = name;
          form.appendChild(input);
        }
        return input;
      };

      ensureHidden('type').value = 'update';
      if (data.header && data.header.id_valor) {
        ensureHidden('id_valor').value = data.header.id_valor;
      }

      ['capeante', 'ap', 'uti', 'cc', 'diar', 'outros'].forEach((section) => {
        if (!data[section]) return;
        Object.entries(data[section]).forEach(function ([key, value]) {
          if (typeof value === 'object' && value !== null) return;
          setValue(key, value);
        });
      });
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', populate);
    } else {
      setTimeout(populate, 0);
    }
  })();
</script>
