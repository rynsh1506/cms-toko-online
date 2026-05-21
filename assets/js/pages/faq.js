// Simple Accordion Script
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach(item => {
            const btn = item.querySelector('.faq-btn');
            btn.addEventListener('click', () => {
                const isActive = item.classList.contains('faq-active');
                // Tutup semua
                faqItems.forEach(i => i.classList.remove('faq-active'));
                // Buka yang di-klik jika sebelumnya tidak aktif
                if (!isActive) {
                    item.classList.add('faq-active');
                }
            });
        });
