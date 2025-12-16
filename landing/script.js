(function () {
    const highlight = document.querySelector('.hero__emphasis');
    const phrases = ['inteligente', 'segura', 'colaborativa', 'previsível'];
    let index = 0;

    function cycleHighlight() {
        if (!highlight) return;
        index = (index + 1) % phrases.length;
        highlight.classList.add('is-fading');
        setTimeout(() => {
            highlight.textContent = phrases[index];
            highlight.classList.remove('is-fading');
        }, 250);
    }

    if (highlight) {
        setInterval(cycleHighlight, 4000);
    }

    document.querySelectorAll('[data-scroll]')
        .forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.querySelector(btn.dataset.scroll);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

    const cards = document.querySelectorAll('[data-hover]');
    cards.forEach(card => {
        card.addEventListener('mousemove', (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * 4;
            const rotateY = ((x - centerX) / centerX) * -4;
            card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
})();
