$(document).ready(function() {
            // Theme toggle logic
            const themeToggleBtn = document.getElementById('theme-toggle');
            const themeToggleSun = document.getElementById('theme-toggle-sun');
            const themeToggleMoon = document.getElementById('theme-toggle-moon');

            if (document.documentElement.classList.contains('dark')) {
                themeToggleSun.classList.remove('hidden');
            } else {
                themeToggleMoon.classList.remove('hidden');
            }

            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                    themeToggleSun.classList.add('hidden');
                    themeToggleMoon.classList.remove('hidden');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggleMoon.classList.add('hidden');
                    themeToggleSun.classList.remove('hidden');
                }
            });

            // Banner Slideshow Logic
            const slides = $('.carousel-slide');
            const dots = $('.carousel-dot');
            let currentSlide = 0;
            let slideInterval = null;

            function showSlide(index) {
                if (slides.length === 0) return;
                slides.removeClass('opacity-100 z-10').addClass('opacity-0 z-0');
                dots.removeClass('bg-white w-6').addClass('bg-white/40 w-2');

                currentSlide = (index + slides.length) % slides.length;

                $(slides[currentSlide]).removeClass('opacity-0 z-0').addClass('opacity-100 z-10');
                $(dots[currentSlide]).removeClass('bg-white/40 w-2').addClass('bg-white w-6');
            }

            function startSlideShow() {
                slideInterval = setInterval(function() {
                    showSlide(currentSlide + 1);
                }, 5000);
            }

            function stopSlideShow() {
                clearInterval(slideInterval);
            }

            $('#prevSlide').on('click', function() {
                stopSlideShow();
                showSlide(currentSlide - 1);
                startSlideShow();
            });

            $('#nextSlide').on('click', function() {
                stopSlideShow();
                showSlide(currentSlide + 1);
                startSlideShow();
            });

            $('.carousel-dot').on('click', function() {
                const idx = parseInt($(this).data('index'));
                stopSlideShow();
                showSlide(idx);
                startSlideShow();
            });

            if (slides.length > 0) {
                startSlideShow();
            }

            // AJAX Catalog Reload Function
            function loadCatalog(page = 1, category = null, query = null) {
                if (category === null) category = $('#selected-category-input').val();
                if (query === null) query = $('#product-search').val();

                const url = `index.php?page=home&ajax=1&p=${page}&cat=${encodeURIComponent(category)}&q=${encodeURIComponent(query)}`;

                // Show loading state by opacity
                $('#catalog-products-container').addClass('opacity-50 pointer-events-none transition duration-150');

                $.getJSON(url, function(data) {
                    // Update DOM
                    $('#filter-bubbles-container').html(data.bubbles);
                    $('#catalog-products-container').html(data.grid).removeClass('opacity-50 pointer-events-none');
                    $('#catalog-pagination-container').html(data.pagination);

                    // Update category dropdown display text & value
                    $('#category-dropdown-btn span').text(data.cat_name);
                    $('#selected-category-input').val(data.active_cat);

                    // Update dropdown list items active state
                    $('.category-dropdown-item').removeClass('bg-primary/5 text-primary dark:bg-primary/20 dark:text-white').find('svg').remove();
                    const activeItem = $(`.category-dropdown-item[data-value="${data.active_cat}"]`);
                    activeItem.addClass('bg-primary/5 text-primary dark:bg-primary/20 dark:text-white');
                    activeItem.append(`
                        <svg class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    `);

                    // Update search input if it was changed from bubble clear
                    if (query !== $('#product-search').val()) {
                        $('#product-search').val(query);
                    }

                    // Update address bar history
                    const nextURL = `index.php?page=home&p=${page}&cat=${encodeURIComponent(category)}&q=${encodeURIComponent(query)}`;
                    window.history.pushState({ path: nextURL }, '', nextURL);
                }).fail(function() {
                    $('#catalog-products-container').removeClass('opacity-50 pointer-events-none');
                    showToast('Gagal memuat produk.', 'error');
                });
            }

            // Custom Category Dropdown Toggle & Selection Logic
            const dropdownBtn = $('#category-dropdown-btn');
            const dropdownMenu = $('#category-dropdown-menu');
            const dropdownArrow = $('#category-dropdown-arrow');
            const categoryInput = $('#selected-category-input');

            if (dropdownBtn.length && dropdownMenu.length) {
                dropdownBtn.on('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.toggleClass('hidden');
                    dropdownArrow.toggleClass('rotate-180');
                });

                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#category-dropdown-wrapper').length) {
                        dropdownMenu.addClass('hidden');
                        dropdownArrow.removeClass('rotate-180');
                    }
                });

                $('.category-dropdown-item').on('click', function() {
                    const val = $(this).data('value');
                    categoryInput.val(val);
                    dropdownMenu.addClass('hidden');
                    dropdownArrow.removeClass('rotate-180');
                    loadCatalog(1, val, null);
                });
            }

            // Intercept search form submit & live input search with debounce
            const searchForm = $('#product-search').closest('form');
            if (searchForm.length) {
                searchForm.on('submit', function(e) {
                    e.preventDefault();
                    loadCatalog(1);
                });
            }

            let searchTimeout = null;
            $('#product-search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    loadCatalog(1);
                }, 300);
            });

            // Delegate Filter Bubbles Click Events
            $(document).on('click', '[data-action="clear-cat"]', function(e) {
                e.preventDefault();
                loadCatalog(1, 'all', null);
            });

            $(document).on('click', '[data-action="clear-q"]', function(e) {
                e.preventDefault();
                loadCatalog(1, null, '');
            });

            $(document).on('click', '[data-action="clear-all"]', function(e) {
                e.preventDefault();
                loadCatalog(1, 'all', '');
            });

            // Delegate Pagination Click Events
            $(document).on('click', '#catalog-pagination-container a[data-page]', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                loadCatalog(page);
                // Scroll smoothly to catalog top
                const target = $("#products");
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 400);
                }
            });

            // Toast helper
            function showToast(message, type = 'success') {
                const toast = $('<div class="fixed bottom-6 right-6 px-5 py-3 rounded-2xl text-white font-bold text-xs shadow-2xl flex items-center space-x-2.5 transition-all duration-300 transform translate-y-10 opacity-0 z-50"></div>');

                if (type === 'success') {
                    toast.addClass('bg-emerald-600');
                    toast.html(`
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>${message}</span>
                    `);
                } else {
                    toast.addClass('bg-rose-600');
                    toast.html(`
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>${message}</span>
                    `);
                }

                $('body').append(toast);

                // Animate entrance
                setTimeout(() => {
                    toast.removeClass('translate-y-10 opacity-0');
                }, 10);

                // Animate exit and remove
                setTimeout(() => {
                    toast.addClass('translate-y-10 opacity-0');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }, 3000);
            }

            // AJAX add to cart (delegated for dynamically loaded cards)
            $(document).on('submit', '.add-to-cart-form', function(e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type="submit"]');

                if (btn.prop('disabled')) return;

                btn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
                const btnSpan = btn.find('span');
                const originalText = btnSpan.text();
                btnSpan.text('Memproses...');

                const formData = form.serialize() + '&ajax=1';

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status === 'success') {
                            // Update navbar count
                            const badge = $('#cart-badge');
                            if (badge.length) {
                                badge.text(data.cart_count).removeClass('hidden');
                            } else {
                                const newBadge = $('<span id="cart-badge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-md shadow-primary/20 font-sans"></span>');
                                newBadge.text(data.cart_count);
                                $('#cart-link').append(newBadge);
                            }
                        } else {
                            showToast(data.message, 'error');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan saat menambahkan produk.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                        btnSpan.text(originalText);
                    }
                });
            });
        });
