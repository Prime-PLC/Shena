<div id="floating-help-widget" style="position: fixed; right: 20px; bottom: 20px; z-index: 1100; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
    <div id="help-options" style="display: none; flex-direction: column; gap: 8px;">
        <a href="tel:+254748585067" title="Call Us" style="width: 46px; height: 46px; border-radius: 50%; background: #7F3D9E; color: #fff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(127, 61, 158, 0.35);">
            <i class="fas fa-phone"></i>
        </a>
        <a href="https://wa.me/254748585071" target="_blank" rel="noopener" title="WhatsApp" style="width: 46px; height: 46px; border-radius: 50%; background: #25D366; color: #fff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(37, 211, 102, 0.35);">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <button type="button" id="toggle-help-widget" aria-label="Need help" style="border: 0; border-radius: 999px; background: #7F3D9E; color: #fff; padding: 12px 14px; font-weight: 600; box-shadow: 0 8px 20px rgba(127, 61, 158, 0.35); display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-comments"></i>
        <span>Need help?</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggleButton = document.getElementById('toggle-help-widget');
    var options = document.getElementById('help-options');

    if (!toggleButton || !options) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        var open = options.style.display === 'flex';
        options.style.display = open ? 'none' : 'flex';
        toggleButton.querySelector('span').textContent = open ? 'Need help?' : 'Close';
    });
});
</script>
