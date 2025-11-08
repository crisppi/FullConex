(function (w) {
    function mostrarMensagem(texto, cor) {
        const div = document.getElementById('mensagemStatus');
        if (!div) return;
        div.textContent = texto;
        div.style.backgroundColor = cor;
        div.style.color = 'white';
        div.style.display = 'block';
        setTimeout(() => { div.style.display = 'none'; }, 5000);
    }

    function bindPDFButtons() {
        const form = document.getElementById('form-capeante-rah');
        const btnPDF = document.getElementById('btnSalvarPDF');
        const btnMail = document.getElementById('btnEnviarEmail');
        if (!form) return;

        if (btnPDF) btnPDF.addEventListener('click', async (e) => {
            e.preventDefault();
            const idCapeante = form.querySelector('[name="id_capeante"]')?.value?.trim() || '';
            if (!idCapeante) {
                mostrarMensagem('Salve o capeante antes de gerar o PDF.', '#ffc107');
                return;
            }
            const formData = new FormData(form);
            formData.set('prefer_post', '1');
            formData.set('id_capeante', idCapeante);

            const btnOriginal = btnPDF.innerHTML;
            btnPDF.disabled = true;
            btnPDF.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Gerando...';
            try {
                const qs = new URLSearchParams({ id_capeante: idCapeante, download: '1' });
                const resp = await fetch('export_capeante_rah_pdf.php?' + qs.toString(), {
                    method: 'POST', body: formData, credentials: 'same-origin'
                });
                const contentType = resp.headers.get('content-type') || '';

                if (!resp.ok) {
                    const errTxt = contentType.includes('application/json') ? JSON.stringify(await resp.json()) : await resp.text();
                    throw new Error(errTxt || 'Falha desconhecida ao gerar PDF.');
                }
                if (contentType.includes('application/json')) {
                    const data = await resp.json();
                    if (!data.ok) throw new Error(data.error || 'Erro ao exportar.');
                    mostrarMensagem('PDF salvo em ' + (data.file_path || 'exports/'), '#198754');
                    return;
                }
                const blob = await resp.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url; link.download = 'RAH_Capeante_' + idCapeante + '.pdf';
                document.body.appendChild(link); link.click();
                setTimeout(() => { URL.revokeObjectURL(url); link.remove(); }, 1500);
            } catch (err) {
                console.error(err);
                mostrarMensagem('Falha ao gerar PDF: ' + (err.message || err), '#dc3545');
            } finally {
                btnPDF.disabled = false;
                btnPDF.innerHTML = btnOriginal;
            }
        });

        if (btnMail) btnMail.addEventListener('click', function () {
            const idCapeante = form.querySelector('[name="id_capeante"]')?.value || '';
            const idInternacao = form.querySelector('[name="fk_int_capeante"]')?.value || '';
            fetch('export_capeante_rah_pdf.php?id_capeante=' + idCapeante + '&fk_int_capeante=' + idInternacao);
            mostrarMensagem('Email enviado com sucesso!', '#0d6efd');
        });

        w.mostrarMensagem = mostrarMensagem; // opcional
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bindPDFButtons);
    else bindPDFButtons();
})(window);
