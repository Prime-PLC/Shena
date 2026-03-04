<div id="member-help-widget" style="position: fixed; right: 20px; bottom: 20px; z-index: 1200; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
    <div id="member-help-options" style="display: none; flex-direction: column; gap: 8px;">
        <a href="/member/support" title="Support" style="width: 44px; height: 44px; border-radius: 50%; background: #7F20B0; color: #fff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px rgba(127, 32, 176, 0.32);">
            <i class="fas fa-life-ring"></i>
        </a>
        <a href="https://wa.me/254748585071" target="_blank" rel="noopener" title="Chat on WhatsApp" style="width: 44px; height: 44px; border-radius: 50%; background: #25D366; color: #fff; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px rgba(37, 211, 102, 0.32);">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    <button type="button" id="member-help-toggle" style="border: none; border-radius: 999px; background: #7F20B0; color: #fff; padding: 10px 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 16px rgba(127, 32, 176, 0.34);">
        <i class="fas fa-comments"></i>
        <span>Need help?</span>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('member-help-toggle');
    var options = document.getElementById('member-help-options');
    if (!toggle || !options) {
        return;
    }

    toggle.addEventListener('click', function () {
        var open = options.style.display === 'flex';
        options.style.display = open ? 'none' : 'flex';
        toggle.querySelector('span').textContent = open ? 'Need help?' : 'Close';
    });
});
</script>
