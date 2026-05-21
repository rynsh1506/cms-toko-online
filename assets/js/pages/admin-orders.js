const orderModal = document.getElementById('orderModal');
    const modalOrderTitle = document.getElementById('modalOrderTitle');
    const modalBuyerName = document.getElementById('modalBuyerName');
    const modalBuyerPhone = document.getElementById('modalBuyerPhone');
    const modalBuyerAddress = document.getElementById('modalBuyerAddress');
    const modalPaymentBank = document.getElementById('modalPaymentBank');
    const modalPaymentNumber = document.getElementById('modalPaymentNumber');
    const modalPaymentName = document.getElementById('modalPaymentName');
    const modalProductsList = document.getElementById('modalProductsList');
    const statusOrderId = document.getElementById('statusOrderId');
    const statusSelect = document.getElementById('statusSelect');
    const statusBadgeContainer = document.getElementById('statusBadgeContainer');

    function openDetailModal(order, items) {
        modalOrderTitle.innerText = "Detail Order #" + order.id;

        modalBuyerName.innerText = order.customer_name;
        modalBuyerPhone.innerText = order.customer_phone;
        modalBuyerAddress.innerText = order.customer_address;

        if (order.bank_name) {
            modalPaymentBank.innerText = order.bank_name;
            modalPaymentNumber.innerText = order.account_number;
            modalPaymentName.innerText = order.account_name;
        } else {
            modalPaymentBank.innerText = "Tidak ada info bank";
            modalPaymentNumber.innerText = "-";
            modalPaymentName.innerText = "-";
        }

        statusOrderId.value = order.id;
        statusSelect.value = order.status;

        // Render status badge
        renderBadge(order.status);

        // Render product items
        let itemsHtml = '';
        let total_subtotal = 0;

        items.forEach(item => {
            let itemPrice = parseFloat(item.price);
            let itemQty = parseInt(item.quantity);
            let itemSubtotal = itemPrice * itemQty;
            total_subtotal += itemSubtotal;

            itemsHtml += `
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                    <td class="p-3 pl-4 font-bold text-slate-800 dark:text-slate-200">${item.product_name}</td>
                    <td class="p-3 text-center text-slate-600 dark:text-slate-400 font-mono font-bold">${itemQty}</td>
                    <td class="p-3 text-right text-slate-600 dark:text-slate-400 font-mono">Rp ${itemPrice.toLocaleString('id-ID')}</td>
                    <td class="p-3 text-right pr-4 font-extrabold text-slate-800 dark:text-white font-mono">Rp ${itemSubtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });

        // Add pricing summaries
        let uniqueCode = parseInt(order.unique_code);
        let grandTotal = total_subtotal + uniqueCode;

        itemsHtml += `
            <tr class="bg-slate-50/30 dark:bg-slate-800/10">
                <td colspan="3" class="p-3 text-right font-bold text-slate-500 dark:text-slate-400">Subtotal:</td>
                <td class="p-3 text-right pr-4 font-bold text-slate-800 dark:text-white font-mono">Rp ${total_subtotal.toLocaleString('id-ID')}</td>
            </tr>
            <tr class="bg-slate-50/30 dark:bg-slate-800/10">
                <td colspan="3" class="p-3 text-right font-bold text-slate-500 dark:text-slate-400">Kode Unik Transfer:</td>
                <td class="p-3 text-right pr-4 font-bold text-amber-600 dark:text-amber-500 font-mono">+Rp ${uniqueCode.toLocaleString('id-ID')}</td>
            </tr>
            <tr class="bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                <td colspan="3" class="p-3 text-right font-extrabold text-slate-800 dark:text-white text-sm">Total Pembayaran:</td>
                <td class="p-3 text-right pr-4 font-extrabold text-indigo-600 dark:text-indigo-400 text-sm font-mono">Rp ${grandTotal.toLocaleString('id-ID')}</td>
            </tr>
        `;

        modalProductsList.innerHTML = itemsHtml;
        orderModal.classList.remove('hidden');
    }

    function renderBadge(status) {
        let badgeHtml = '';
        if (status === 'pending') {
            badgeHtml = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Pending</span>';
        } else if (status === 'paid') {
            badgeHtml = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Paid (Telah Dibayar)</span>';
        } else if (status === 'shipped') {
            badgeHtml = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Shipped (Dikirim)</span>';
        } else if (status === 'done') {
            badgeHtml = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">Done (Selesai)</span>';
        } else if (status === 'cancelled') {
            badgeHtml = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Cancelled (Dibatalkan)</span>';
        }
        statusBadgeContainer.innerHTML = badgeHtml;
    }

    function closeOrderModal() {
        orderModal.classList.add('hidden');
    }

    $(document).ready(function() {
        // AJAX Submit Status Update Form
        $('#statusForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const orderId = $('#statusOrderId').val();
            const newStatus = $('#statusSelect').val();

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize() + '&ajax=1',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update badge in modal
                        renderBadge(response.status);

                        // Update badge on background list table
                        const orderRow = $('.order-row[data-id="' + orderId + '"]');
                        let tableBadge = '';
                        if (response.status === 'pending') {
                            tableBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">Pending</span>';
                        } else if (response.status === 'paid') {
                            tableBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400">Paid</span>';
                        } else if (response.status === 'shipped') {
                            tableBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400">Shipped</span>';
                        } else if (response.status === 'done') {
                            tableBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">Done</span>';
                        } else if (response.status === 'cancelled') {
                            tableBadge = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">Cancelled</span>';
                        }
                        orderRow.find('.status-cell').html(tableBadge);

                        // Show SweetAlert Toast
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#4f46e5'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem saat memperbarui status order.',
                        icon: 'error',
                        confirmButtonColor: '#4f46e5'
                    });
                }
            });
        });
    });
