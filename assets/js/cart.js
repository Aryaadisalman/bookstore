/**
 * assets/js/cart.js
 * JavaScript sederhana untuk interaksi keranjang
 */

document.addEventListener('DOMContentLoaded', function () {
    // Update total harga ketika quantity berubah
    var qtyInputs = document.querySelectorAll('.qty-input');
    qtyInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            var bookId = this.dataset.bookId;
            var newQty = this.value;
            updateCartTotal(bookId, newQty);
        });
    });

    // Hapus item dari keranjang
    var removeButtons = document.querySelectorAll('.remove-from-cart');
    removeButtons.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var bookId = this.dataset.bookId;
            removeFromCart(bookId);
        });
    });
});

function updateCartTotal(bookId, quantity) {
    fetch('cart_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'book_id=' + encodeURIComponent(bookId) + '&quantity=' + encodeURIComponent(quantity)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
}

function removeFromCart(bookId) {
    if (confirm('Apakah Anda yakin ingin menghapus buku ini dari keranjang?')) {
        fetch('cart_remove.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'book_id=' + encodeURIComponent(bookId)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
    }
}
