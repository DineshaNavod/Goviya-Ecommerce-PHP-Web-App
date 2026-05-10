/* goviya.lk | main.js — v5 FINAL */
(function() {
'use strict';

function getSiteUrl() {
    var meta = document.querySelector('meta[name="site-url"]');
    if (meta && meta.content) return meta.content.replace(/\/$/, '');
    var parts = window.location.pathname.split('/');
    return window.location.origin + (parts[1] ? '/' + parts[1] : '');
}

function getCsrf() {
    var meta = document.querySelector('meta[name="csrf"]');
    return meta ? meta.content : '';
}

function showToast(msg, type) {
    type = type || 'info';
    var colors = { success: '#2d6a4f', error: '#c62828', info: '#0077b6' };
    var wrap = document.getElementById('goviya-toast-wrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'goviya-toast-wrap';
        wrap.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:999999;display:flex;flex-direction:column;gap:10px;pointer-events:none';
        document.body.appendChild(wrap);
    }
    var toast = document.createElement('div');
    toast.innerHTML = msg;
    toast.style.cssText = 'background:' + (colors[type]||colors.info) + ';color:#fff;'
        + 'padding:14px 22px;border-radius:10px;font-size:15px;font-family:sans-serif;'
        + 'box-shadow:0 4px 20px rgba(0,0,0,.35);min-width:200px;max-width:320px;'
        + 'opacity:0;transition:opacity 0.3s ease;';
    wrap.appendChild(toast);
    // force reflow then fade in
    void toast.offsetWidth;
    toast.style.opacity = '1';
    setTimeout(function() {
        toast.style.opacity = '0';
        setTimeout(function() { toast.parentNode && toast.parentNode.removeChild(toast); }, 350);
    }, 3000);
}

function updateCartBadge(count) {
    var badges = document.querySelectorAll('.cart-badge');
    if (badges.length > 0) {
        badges.forEach(function(el) {
            el.textContent = count;
            el.style.display = count > 0 ? 'flex' : 'none';
        });
    } else if (count > 0) {
        var links = document.querySelectorAll('.cart-icon-wrap');
        links.forEach(function(link) {
            var badge = document.createElement('span');
            badge.className = 'cart-badge';
            badge.textContent = count;
            link.appendChild(badge);
        });
    }
}

// ── ADD TO CART ───────────────────────────────
document.addEventListener('click', function(e) {
    var btn = e.target.closest ? e.target.closest('.btn-add-cart') : null;
    if (!btn) {
        // fallback for older browsers
        var el = e.target;
        while (el && el !== document) {
            if (el.classList && el.classList.contains('btn-add-cart')) { btn = el; break; }
            el = el.parentNode;
        }
    }
    if (!btn || btn.disabled) return;
    e.preventDefault();

    var productId = btn.getAttribute('data-product-id');
    if (!productId) return;

    var originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.innerHTML = 'Adding…';

    var body = 'action=add&product_id=' + encodeURIComponent(productId)
             + '&csrf_token=' + encodeURIComponent(getCsrf());

    var xhr = new XMLHttpRequest();
    xhr.open('POST', getSiteUrl() + '/pages/cart_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        var raw = xhr.responseText;
        var data;
        try { data = JSON.parse(raw); } catch(err) {
            console.error('Cart response not JSON:', raw.substring(0, 300));
            showToast('Server error. See F12 console.', 'error');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            btn.style.opacity = '1';
            return;
        }
        if (data.success) {
            updateCartBadge(data.cartCount);
            showToast('&#10003; Added to cart!', 'success');
            btn.innerHTML = '&#10003; Added!';
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                btn.style.opacity = '1';
            }, 1800);
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    };
    xhr.send(body);
});

// ── UPDATE QUANTITY ───────────────────────────
document.addEventListener('change', function(e) {
    var input = e.target;
    if (!input.classList.contains('qty-input')) return;
    var productId = input.getAttribute('data-product-id');
    var qty = parseInt(input.value);
    if (!productId || isNaN(qty) || qty < 1) return;

    var body = 'action=update&product_id=' + encodeURIComponent(productId)
             + '&quantity=' + qty + '&csrf_token=' + encodeURIComponent(getCsrf());
    var xhr = new XMLHttpRequest();
    xhr.open('POST', getSiteUrl() + '/pages/cart_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.success) location.reload();
        } catch(e) {}
    };
    xhr.send(body);
});

// ── REMOVE FROM CART ──────────────────────────
document.addEventListener('click', function(e) {
    var btn = null;
    var el = e.target;
    while (el && el !== document) {
        if (el.classList && el.classList.contains('btn-remove-cart')) { btn = el; break; }
        el = el.parentNode;
    }
    if (!btn) return;
    if (!confirm('Remove this item from cart?')) return;
    var productId = btn.getAttribute('data-product-id');

    var body = 'action=remove&product_id=' + encodeURIComponent(productId)
             + '&csrf_token=' + encodeURIComponent(getCsrf());
    var xhr = new XMLHttpRequest();
    xhr.open('POST', getSiteUrl() + '/pages/cart_action.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.success) location.reload();
        } catch(e) {}
    };
    xhr.send(body);
});

// ── PAYMENT METHOD SELECT ─────────────────────
document.querySelectorAll('.payment-option').forEach(function(opt) {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(function(o) {
            o.classList.remove('selected');
        });
        opt.classList.add('selected');
        var radio = opt.querySelector('input[type=radio]');
        if (radio) radio.checked = true;
        var cardFields = document.getElementById('card-fields');
        if (cardFields) {
            cardFields.style.display = opt.getAttribute('data-method') === 'card' ? 'block' : 'none';
        }
    });
});

// ── ADMIN CONFIRM DELETES ─────────────────────
document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (!confirm(el.getAttribute('data-confirm') || 'Are you sure?')) e.preventDefault();
    });
});

})(); // end IIFE — no global variable conflicts possible
