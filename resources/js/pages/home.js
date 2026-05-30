document.addEventListener('DOMContentLoaded', () => {
    const revealEls = document.querySelectorAll('.reveal');
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) e.target.classList.add('active');
        });
    }, { threshold: 0.1 });
    revealEls.forEach(el => revealObs.observe(el));

    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = Number(counter.getAttribute('data-target') || 0);
            const count = Number(counter.innerText || 0);
            const step = Math.max(1, Math.ceil(target / 100));
            if (count < target) {
                counter.innerText = String(Math.min(target, count + step));
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = String(target);
            }
        };

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCount();
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        obs.observe(counter);
    });
});
