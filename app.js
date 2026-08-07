document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-section');
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.target);
            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });
});

function startCountdown(elementId, seconds, formId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    let remaining = parseInt(seconds, 10);
    function formatTime(s) {
        const mm = Math.floor(s / 60).toString().padStart(2, '0');
        const ss = (s % 60).toString().padStart(2, '0');
        return mm + ':' + ss;
    }
    el.textContent = formatTime(Math.max(0, remaining));
    const interval = setInterval(() => {
        remaining -= 1;
        if (remaining <= 0) {
            el.textContent = '00:00';
            clearInterval(interval);
            // auto-submit form if provided
            if (formId) {
                const form = document.getElementById(formId);
                if (form) {
                    // prevent multiple submits
                    const submitButtons = form.querySelectorAll('button[type="submit"]');
                    submitButtons.forEach(b => b.disabled = true);
                    form.submit();
                }
            }
            return;
        }
        el.textContent = formatTime(remaining);
    }, 1000);
    return interval;
}

function startAutoSave(textareaId, storageKey, intervalSec) {
    const ta = document.getElementById(textareaId);
    if (!ta || !window.localStorage) return null;
    // restore
    try {
        const saved = localStorage.getItem(storageKey);
        if (saved) ta.value = saved;
    } catch (e) {}
    const saver = setInterval(() => {
        try { localStorage.setItem(storageKey, ta.value); } catch (e) {}
    }, (intervalSec || 10) * 1000);
    return saver;
}

function clearAutoSave(storageKey) {
    try { localStorage.removeItem(storageKey); } catch (e) {}
}

function showAutoSubmitModal(message) {
    let modal = document.getElementById('auto-submit-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'auto-submit-modal';
        modal.innerHTML = '<div class="card" style="max-width:500px;margin:40px auto;">'
            + '<h3>Auto-submit warning</h3>'
            + '<p id="auto-submit-message"></p>'
            + '<div style="text-align:right;"><button id="auto-submit-now" class="button">Submit now</button> <button id="auto-submit-dismiss">Dismiss</button></div>'
            + '</div>';
        document.body.appendChild(modal);
        document.getElementById('auto-submit-dismiss').addEventListener('click', function(){ modal.style.display = 'none'; });
    }
    document.getElementById('auto-submit-message').textContent = message;
    modal.style.display = 'block';
    return modal;
}

function attachPreAutoSubmitWarning(elementId, seconds, formId, warnSeconds) {
    // warnSeconds: seconds remaining to show modal
    const el = document.getElementById(elementId);
    if (!el) return;
    let remaining = parseInt(seconds, 10);
    warnSeconds = typeof warnSeconds === 'number' ? warnSeconds : 15;
    const check = setInterval(() => {
        remaining -= 1;
        if (remaining === warnSeconds) {
            showAutoSubmitModal('Your time is almost up. The attempt will auto-submit when time expires.');
        }
        if (remaining <= 0) clearInterval(check);
    }, 1000);
    return check;
}
