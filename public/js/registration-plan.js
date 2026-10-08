/* Shared public/admin/agent tier-first registration picker. */
(function () {
    const tier = document.querySelector('select[name="platinum_opt_in"]');
    if (!tier) return;
    const form = tier.closest('form');
    const plans = window.shenaRegistrationPlans || {};
    const principal = form.querySelector('select[name="package"], select[name="package_id"]');
    const radios = Array.from(form.querySelectorAll('input[type="radio"][name="package"]'));
    const money = amount => Number(amount).toLocaleString('en-KE');
    function amount(key, productTier = tier.value) {
        const plan = plans[key];
        if (!plan || !['0', '1'].includes(productTier)) return null;
        const price = productTier === '1' ? plan.platinum : plan.basic;
        return price == null || Number(price) <= 0 ? null : Number(price);
    }
    function selectPrices(select, productTier = tier.value) {
        for (const option of select.options) {
            if (!option.value) { option.textContent = 'Select a ' + (productTier === '1' ? 'Platinum' : 'Basic') + ' package'; continue; }
            const price = amount(option.value, productTier);
            option.disabled = price === null;
            option.hidden = price === null;
            if (option.selected && option.disabled) select.value = '';
            if (price !== null) option.textContent = plans[option.value].name + ' - KES ' + money(price) + '/month';
        }
        select.disabled = !['0', '1'].includes(productTier);
    }
    function refresh() {
        const selectedTier = ['0', '1'].includes(tier.value);
        const label = tier.value === '1' ? 'Platinum' : 'Basic';
        if (principal) selectPrices(principal);
        for (const radio of radios) {
            const price = amount(radio.value);
            const card = radio.closest('.package-option');
            radio.disabled = price === null;
            if (radio.disabled) radio.checked = false;
            if (card) {
                card.hidden = price === null;
                const priceElement = card.querySelector('.package-price');
                if (priceElement && price !== null) priceElement.textContent = 'SHENA ' + label + ' - KES ' + money(price) + '/month';
            }
        }
        const key = principal ? principal.value : radios.find(r => r.checked)?.value;
        let total = amount(key), valid = total !== null;
        let corporateCount = 0;
        for (const select of form.querySelectorAll('.corporate-package, [data-corporate-package]')) {
            corporateCount++;
            selectPrices(select, '0');
            const price = amount(select.value, '0');
            valid = valid && price !== null;
            total = (total || 0) + (price || 0);
            const row = select.closest('.corporate-row, .corporate-line-row');
            const display = row?.querySelector('.corporate-amount');
            if (display) display.textContent = price === null ? 'Select package' : 'KES ' + money(price);
            const dob = row?.querySelector('input[type="date"]');
            if (dob) dob.required = false;
        }
        const summary = document.getElementById('productSummary') || document.getElementById('corporateTotalPreview');
        if (summary) summary.textContent = !selectedTier ? 'Choose Basic or Platinum first.' : !valid
            ? 'Choose a valid principal package and a Basic package for each corporate member.'
            : 'Principal: SHENA ' + label + (corporateCount ? '; corporate members: Basic' : '') + '. Total: KES ' + money(total) + '/month.';
        for (const id of ['platinumTierPanel', 'agentPlatinumPanel']) {
            const panel = document.getElementById(id);
            if (panel) panel.style.display = tier.value === '1' ? '' : 'none';
        }
        for (const id of ['platinumOptInPrice', 'agentPlatinumPrice']) {
            const priceElement = document.getElementById(id);
            if (priceElement) priceElement.textContent = amount(key) === null ? 'Select package' : 'KES ' + money(amount(key));
        }
        return selectedTier && valid;
    }
    window.ShenaRegistrationPlan = { refresh };
    form.addEventListener('change', refresh);
    form.addEventListener('submit', function (event) {
        if (!refresh()) {
            event.preventDefault(); event.stopImmediatePropagation(); form.reportValidity();
        }
    }, true);
    refresh();
})();
