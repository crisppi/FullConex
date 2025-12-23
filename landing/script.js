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

    const aiCards = document.querySelectorAll('.ai-card');
    aiCards.forEach((card) => {
        card.addEventListener('mousemove', (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            card.style.setProperty('--pointer-x', `${x}px`);
            card.style.setProperty('--pointer-y', `${y}px`);
            card.classList.add('is-active');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('is-active');
        });
    });

    const pipelineSteps = document.querySelectorAll('.ai-diagram__step');
    if (pipelineSteps.length) {
        const reveal = (step) => step.classList.add('is-visible');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        reveal(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.35 });
            pipelineSteps.forEach((step, index) => {
                step.style.transitionDelay = `${index * 0.12}s`;
                observer.observe(step);
            });
        } else {
            pipelineSteps.forEach((step) => reveal(step));
        }
    }
})();
