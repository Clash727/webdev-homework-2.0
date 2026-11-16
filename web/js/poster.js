/* 用途：海报内容滚动入场。作者：Clash727（W09） */
(() => {
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('poster-reveal');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.section-block, .team-card').forEach(element => observer.observe(element));
})();
